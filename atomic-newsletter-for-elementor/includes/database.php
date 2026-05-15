<?php
/**
 * Database operations for Atomic Newsletter For Elementor plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ALNC_Database {

	const TABLE_NAME        = 'atomic_emails';
	const DB_VERSION        = '1.1';
	const DB_VERSION_OPTION = 'alnc_db_version';

	/**
	 * Maximum number of IDs per DELETE IN() batch.
	 * Prevents MySQL max_allowed_packet errors on large bulk deletes.
	 */
	const DELETE_CHUNK_SIZE = 500;

	/**
	 * Rows per SELECT batch when streaming CSV export.
	 * Keeps PHP memory usage flat regardless of subscriber count.
	 */
	const EXPORT_CHUNK_SIZE = 1000;

	/**
	 * Return the table name for the current site.
	 * Uses get_blog_prefix() so each subsite in a Multisite network
	 * gets its own isolated subscriber table.
	 *
	 * @return string
	 */
	private static function table_name() {
		global $wpdb;
		return $wpdb->get_blog_prefix() . self::TABLE_NAME;
	}

	/**
	 * Schedule a migration check on plugins_loaded if the stored DB
	 * version is outdated. Avoids a SHOW TABLES query on every page load.
	 */
	public static function init() {
		if ( get_option( self::DB_VERSION_OPTION ) !== self::DB_VERSION ) {
			add_action( 'plugins_loaded', array( __CLASS__, 'run_migrations' ), 5 );
		}
	}

	/**
	 * Create the subscribers table on plugin activation.
	 */
	public static function create_table() {
		global $wpdb;

		$table_name      = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			email varchar(100) NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY email (email)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
	}

	/**
	 * Run DB migrations when the stored version number changes.
	 * Deduplicates existing rows and ensures the UNIQUE index exists.
	 */
	public static function run_migrations() {
		global $wpdb;

		$table_name     = self::table_name();
		$safe_table     = esc_sql( $table_name );

		self::create_table();

		// Remove duplicate rows — keep the earliest entry per email.
		$wpdb->query( "DELETE t1 FROM {$safe_table} t1 INNER JOIN {$safe_table} t2 WHERE t1.id > t2.id AND t1.email = t2.email" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

		// Add UNIQUE index if it is not already present.
		$index_exists = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT COUNT(1) FROM INFORMATION_SCHEMA.STATISTICS WHERE table_schema = DATABASE() AND table_name = %s AND index_name = 'email'",
				$table_name
			)
		); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( ! $index_exists ) {
			$wpdb->query( "ALTER TABLE {$safe_table} ADD UNIQUE KEY email (email)" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
		}

		update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
	}

	/**
	 * Insert a subscriber email. INSERT IGNORE silently skips duplicates.
	 *
	 * @param  string $email
	 * @return int|false  Number of rows affected, or false on error.
	 */
	public static function insert_email( $email ) {
		global $wpdb;

		$email = sanitize_email( $email );

		if ( empty( $email ) || ! is_email( $email ) ) {
			return false;
		}

		return $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				'INSERT IGNORE INTO ' . self::table_name() . ' (email, date) VALUES (%s, %s)', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$email,
				current_time( 'mysql', true )
			)
		);
	}

	/**
	 * Get a paginated page of subscribers.
	 * Always uses LIMIT + OFFSET — never loads the full table.
	 *
	 * @param  string $search  Optional email search filter.
	 * @param  int    $limit   Rows per page.
	 * @param  int    $offset  Row offset.
	 * @return array
	 */
	public static function get_subscribers( $search = '', $limit = 20, $offset = 0 ) {
		global $wpdb;

		$table_name   = self::table_name();
		$prepare_args = array();
		$query        = "SELECT * FROM {$table_name}";

		if ( ! empty( $search ) ) {
			$query         .= ' WHERE email LIKE %s';
			$prepare_args[] = '%' . $wpdb->esc_like( $search ) . '%';
		}

		$query         .= ' ORDER BY date DESC LIMIT %d OFFSET %d';
		$prepare_args[] = intval( $limit );
		$prepare_args[] = intval( $offset );

		return $wpdb->get_results( $wpdb->prepare( $query, $prepare_args ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter
	}

	/**
	 * Count subscribers with an optional search filter.
	 *
	 * @param  string $search
	 * @return int
	 */
	public static function get_subscriber_count( $search = '' ) {
		global $wpdb;

		$table_name = self::table_name();

		if ( ! empty( $search ) ) {
			return (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table_name} WHERE email LIKE %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					'%' . $wpdb->esc_like( $search ) . '%'
				)
			);
		}

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Delete a single subscriber by ID.
	 *
	 * @param  int $id
	 * @return int|false
	 */
	public static function delete_subscriber( $id ) {
		global $wpdb;

		return $wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			self::table_name(),
			array( 'id' => intval( $id ) ),
			array( '%d' )
		);
	}

	/**
	 * Delete subscribers whose email matches a search term.
	 * Requires a non-empty search to prevent accidental full-table wipe.
	 *
	 * @param  string $search
	 * @return int|false
	 */
	public static function delete_subscribers_by_search( $search ) {
		global $wpdb;

		$search = sanitize_text_field( $search );

		if ( empty( $search ) ) {
			return false;
		}

		return $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				'DELETE FROM ' . self::table_name() . ' WHERE email LIKE %s', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				'%' . $wpdb->esc_like( $search ) . '%'
			)
		);
	}

	/**
	 * Delete multiple subscribers by ID array.
	 * Processed in batches of DELETE_CHUNK_SIZE to stay within
	 * MySQL's max_allowed_packet limit.
	 *
	 * @param  int[] $ids
	 * @return int   Total rows deleted.
	 */
	public static function delete_selected_subscribers( $ids ) {
		global $wpdb;

		if ( empty( $ids ) || ! is_array( $ids ) ) {
			return 0;
		}

		$ids   = array_map( 'intval', $ids );
		$total = 0;

		foreach ( array_chunk( $ids, self::DELETE_CHUNK_SIZE ) as $chunk ) {
			$placeholders = implode( ',', array_fill( 0, count( $chunk ), '%d' ) );
			$safe_table   = esc_sql( self::table_name() );
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$sql    = "DELETE FROM {$safe_table} WHERE id IN ({$placeholders})";
			$result = $wpdb->query( $wpdb->prepare( $sql, $chunk ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( false !== $result ) {
				$total += $result;
			}
		}

		return $total;
	}

	/**
	 * Stream all subscribers in fixed-size chunks for memory-efficient CSV export.
	 * Never loads the full table into PHP memory regardless of subscriber count.
	 *
	 * Usage:
	 *   foreach ( ALNC_Database::get_subscribers_chunked() as $row ) { ... }
	 *
	 * @return Generator
	 */
	public static function get_subscribers_chunked() {
		$offset = 0;

		do {
			$rows = self::get_subscribers( '', self::EXPORT_CHUNK_SIZE, $offset );

			foreach ( $rows as $row ) {
				yield $row;
			}

			$offset += self::EXPORT_CHUNK_SIZE;
		} while ( count( $rows ) === self::EXPORT_CHUNK_SIZE );
	}
}

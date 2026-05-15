<?php
/**
 * Uninstall routine for Atomic Newsletter For Elementor.
 *
 * Runs automatically when the plugin is deleted from the WordPress admin.
 * Removes the database table and all plugin options.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// W3: prefix variable with plugin prefix to avoid global namespace collision.
// W4: esc_sql() applied to table name before interpolation.
$alnc_table_name = esc_sql( $wpdb->get_blog_prefix() . 'atomic_emails' );

// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
$wpdb->query( "DROP TABLE IF EXISTS {$alnc_table_name}" );

delete_option( 'alnc_db_version' );

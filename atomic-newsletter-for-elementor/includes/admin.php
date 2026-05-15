<?php
/**
 * Admin interface for Atomic Newsletter For Elementor plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ALNC_Admin {

	const PER_PAGE = 20;

	public static function init() {
		add_action( 'admin_menu',                                  array( __CLASS__, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts',                       array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'admin_post_alnc_export_subscribers',          array( __CLASS__, 'export_subscribers' ) );
		add_action( 'admin_post_alnc_delete_subscriber',           array( __CLASS__, 'delete_subscriber' ) );
		add_action( 'admin_post_alnc_bulk_delete_subscribers',     array( __CLASS__, 'bulk_delete_subscribers' ) );
		add_action( 'admin_post_alnc_delete_selected_subscribers', array( __CLASS__, 'delete_selected_subscribers' ) );
		add_action( 'wp_ajax_alnc_delete_subscriber_ajax',         array( __CLASS__, 'delete_subscriber_ajax' ) );
	}

	/* ── SCRIPTS ── */

	/**
	 * Enqueue admin JS only on the plugin's own admin page.
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public static function enqueue_scripts( $hook ) {
		if ( 'toplevel_page_subscriber-list' !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'alnc-admin',
			ALNC_PLUGIN_URL . 'assets/js/admin.js',
			array(),
			ALNC_VERSION,
			true
		);

		wp_localize_script(
			'alnc-admin',
			'alncAdmin',
			array(
				'selectOne'             => __( 'Select at least one subscriber.', 'atomic-newsletter-for-elementor' ),
				'confirmDeleteSelected' => __( 'Delete selected subscribers? This cannot be undone.', 'atomic-newsletter-for-elementor' ),
				'confirmDeleteAll'      => __( 'Delete all filtered subscribers? This cannot be undone.', 'atomic-newsletter-for-elementor' ),
				'confirmDelete'         => __( 'Delete this subscriber?', 'atomic-newsletter-for-elementor' ),
			)
		);
	}

	/* ── MENU ── */

	public static function add_admin_menu() {
		add_menu_page(
			__( 'Atomic Newsletter', 'atomic-newsletter-for-elementor' ),
			__( 'Subscribers', 'atomic-newsletter-for-elementor' ),
			'manage_options',
			'subscriber-list',
			array( __CLASS__, 'display_subscribers' ),
			'dashicons-groups'
		);

		add_submenu_page(
			'subscriber-list',
			__( 'Subscribers', 'atomic-newsletter-for-elementor' ),
			__( 'Subscribers', 'atomic-newsletter-for-elementor' ),
			'manage_options',
			'subscriber-list',
			array( __CLASS__, 'display_subscribers' )
		);

		add_submenu_page(
			'subscriber-list',
			__( 'How to Use', 'atomic-newsletter-for-elementor' ),
			__( 'How to Use', 'atomic-newsletter-for-elementor' ),
			'manage_options',
			'alnc-how-to-use',
			array( __CLASS__, 'display_how_to_use' )
		);
	}

	/* ── SUBSCRIBER LIST PAGE ── */

	public static function display_subscribers() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$search       = sanitize_text_field( wp_unslash( $_GET['alnc_search'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$total_count  = ALNC_Database::get_subscriber_count( $search );
		$per_page     = self::PER_PAGE;
		$current_page = max( 1, absint( $_GET['paged'] ?? 1 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$total_pages  = max( 1, (int) ceil( $total_count / $per_page ) );
		$current_page = min( $current_page, $total_pages );
		$offset       = ( $current_page - 1 ) * $per_page;
		$items        = ALNC_Database::get_subscribers( $search, $per_page, $offset );

		echo '<div class="wrap"><h1>' . esc_html__( 'Newsletter Subscribers', 'atomic-newsletter-for-elementor' ) . '</h1>';

		// Admin notices.
		if ( ! empty( $_GET['export_error'] ) && sanitize_key( $_GET['export_error'] ) === 'no_data' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<div class="notice notice-error is-dismissible"><p>'
				. esc_html__( 'No subscribers found to export.', 'atomic-newsletter-for-elementor' )
				. '</p></div>';
		}

		// Search form.
		echo '<form method="get" action="" style="margin-bottom:16px;display:flex;gap:10px;align-items:center;">';
		echo '<input type="hidden" name="page" value="subscriber-list">';
		echo '<input type="text" name="alnc_search" placeholder="'
			. esc_attr__( 'Search emails…', 'atomic-newsletter-for-elementor' )
			. '" value="' . esc_attr( $search ) . '" style="padding:5px 10px;width:260px;">';
		echo '<button type="submit" class="button">' . esc_html__( 'Search', 'atomic-newsletter-for-elementor' ) . '</button>';
		if ( ! empty( $search ) ) {
			echo '<a href="' . esc_url( admin_url( 'admin.php?page=subscriber-list' ) ) . '" class="button">'
				. esc_html__( 'Clear', 'atomic-newsletter-for-elementor' ) . '</a>';
		}
		echo '</form>';

		// Action toolbar.
		echo '<div style="margin-bottom:12px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">';

		// Export CSV button.
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="alnc_export_subscribers">';
		wp_nonce_field( 'alnc_export_subscribers_action', 'alnc_export_nonce' );
		echo '<button type="submit" class="button button-primary">'
			. esc_html__( 'Export CSV', 'atomic-newsletter-for-elementor' ) . '</button>';
		echo '</form>';

		// Bulk delete filtered button (only when a search filter is active).
		if ( $total_count > 0 && ! empty( $search ) ) {
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
			echo '<input type="hidden" name="action" value="alnc_bulk_delete_subscribers">';
			wp_nonce_field( 'alnc_bulk_delete_action', 'alnc_bulk_delete_nonce' );
			echo '<input type="hidden" name="alnc_search" value="' . esc_attr( $search ) . '">';
			echo '<button type="submit" class="button button-secondary" id="alnc-bulk-delete-btn"'
				. ' data-confirm="' . esc_attr__( 'Delete all filtered subscribers? This cannot be undone.', 'atomic-newsletter-for-elementor' ) . '">'
				. esc_html__( 'Delete All Filtered', 'atomic-newsletter-for-elementor' ) . '</button>';
			echo '</form>';
		}

		echo '</div>';

		// Count + pagination summary.
		echo '<p><strong>';
		echo sprintf(
			/* translators: %d: number of subscribers */
			esc_html__( 'Total: %d subscribers', 'atomic-newsletter-for-elementor' ),
			intval( $total_count )
		);
		if ( $total_pages > 1 ) {
			echo ' | ' . sprintf(
				/* translators: 1: current page number, 2: total pages */
				esc_html__( 'Page %1$d of %2$d', 'atomic-newsletter-for-elementor' ),
				intval( $current_page ),
				intval( $total_pages )
			);
		}
		echo '</strong></p>';

		// Subscriber table — wrapped in a form for bulk checkbox delete.
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" id="alnc-subscribers-form">';
		echo '<input type="hidden" name="action" value="alnc_delete_selected_subscribers">';
		wp_nonce_field( 'alnc_delete_selected_action', 'alnc_delete_selected_nonce' );

		// Delete Selected button — inside the form so submission works in all browsers.
		echo '<div style="margin-bottom:10px;">';
		echo '<button type="submit" class="button button-secondary" id="alnc-delete-selected-btn" style="display:none;">'
			. esc_html__( 'Delete Selected', 'atomic-newsletter-for-elementor' ) . '</button>';
		echo '</div>';

		echo '<table class="wp-list-table widefat fixed striped"><thead><tr>'
			. '<th style="width:30px;"><input type="checkbox" id="alnc-select-all"></th>'
			. '<th style="width:60px;">' . esc_html__( 'S.No.', 'atomic-newsletter-for-elementor' ) . '</th>'
			. '<th>' . esc_html__( 'Email', 'atomic-newsletter-for-elementor' ) . '</th>'
			. '<th style="width:180px;">' . esc_html__( 'Date', 'atomic-newsletter-for-elementor' ) . '</th>'
			. '<th style="width:100px;">' . esc_html__( 'Action', 'atomic-newsletter-for-elementor' ) . '</th>'
			. '</tr></thead><tbody>';

		if ( $items ) {
			// Serial number counts down from the last item on this page.
			$serial = $total_count - $offset;
			foreach ( $items as $item ) {
				$delete_url = wp_nonce_url(
					admin_url( 'admin-post.php?action=alnc_delete_subscriber&subscriber_id=' . absint( $item->id ) ),
					'alnc_delete_subscriber_nonce'
				);
				echo '<tr>'
					. '<td><input type="checkbox" name="alnc_subscriber_ids[]" value="' . esc_attr( $item->id ) . '"></td>'
					. '<td>' . esc_html( $serial-- ) . '</td>'
					. '<td><strong>' . esc_html( $item->email ) . '</strong></td>'
					. '<td>' . esc_html( $item->date ) . '</td>'
					. '<td>'
					. '<a href="' . esc_url( $delete_url ) . '" class="button button-small">'
					. esc_html__( 'Delete', 'atomic-newsletter-for-elementor' ) . '</a>'
					. '</td>'
					. '</tr>';
			}
		} else {
			echo '<tr><td colspan="5">' . esc_html__( 'No subscribers found.', 'atomic-newsletter-for-elementor' ) . '</td></tr>';
		}

		echo '</tbody></table></form>';

		if ( $total_pages > 1 ) {
			self::render_pagination( $current_page, $total_pages, $search );
		}

		echo '</div>'; // .wrap
	}

	/**
	 * Render prev / page-numbers / next pagination links.
	 *
	 * @param int    $current_page
	 * @param int    $total_pages
	 * @param string $search
	 */
	private static function render_pagination( $current_page, $total_pages, $search = '' ) {
		$base_url = admin_url( 'admin.php?page=subscriber-list' );
		if ( ! empty( $search ) ) {
			$base_url = add_query_arg( 'alnc_search', rawurlencode( $search ), $base_url );
		}

		echo '<div class="tablenav bottom"><div class="tablenav-pages" style="margin:8px 0;">';

		if ( $current_page > 1 ) {
			echo '<a class="button" href="'
				. esc_url( add_query_arg( 'paged', $current_page - 1, $base_url ) )
				. '">&laquo; ' . esc_html__( 'Previous', 'atomic-newsletter-for-elementor' ) . '</a> ';
		}

		$start = max( 1, $current_page - 2 );
		$end   = min( $total_pages, $current_page + 2 );

		if ( $start > 1 ) {
			echo '<a class="button" href="' . esc_url( add_query_arg( 'paged', 1, $base_url ) ) . '">1</a> ';
			if ( $start > 2 ) {
				echo '<span style="padding:0 4px;">&hellip;</span>';
			}
		}

		for ( $i = $start; $i <= $end; $i++ ) {
			if ( $i === $current_page ) {
				echo '<span class="button button-primary" style="cursor:default;">' . esc_html( $i ) . '</span> ';
			} else {
				echo '<a class="button" href="' . esc_url( add_query_arg( 'paged', $i, $base_url ) ) . '">'
					. esc_html( $i ) . '</a> ';
			}
		}

		if ( $end < $total_pages ) {
			if ( $end < $total_pages - 1 ) {
				echo '<span style="padding:0 4px;">&hellip;</span>';
			}
			echo '<a class="button" href="' . esc_url( add_query_arg( 'paged', $total_pages, $base_url ) ) . '">'
				. esc_html( $total_pages ) . '</a> ';
		}

		if ( $current_page < $total_pages ) {
			echo '<a class="button" href="'
				. esc_url( add_query_arg( 'paged', $current_page + 1, $base_url ) )
				. '">' . esc_html__( 'Next', 'atomic-newsletter-for-elementor' ) . ' &raquo;</a>';
		}

		echo '</div></div>';
	}

	/* ── EXPORT (CSV — chunked streaming) ── */

	public static function export_subscribers() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'atomic-newsletter-for-elementor' ) );
		}

		if ( empty( $_POST['alnc_export_nonce'] )
			|| ! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['alnc_export_nonce'] ) ),
				'alnc_export_subscribers_action'
			)
		) {
			wp_die( esc_html__( 'Security check failed.', 'atomic-newsletter-for-elementor' ) );
		}

		if ( ! ALNC_Database::get_subscriber_count() ) {
			wp_safe_redirect( add_query_arg(
				array( 'page' => 'subscriber-list', 'export_error' => 'no_data' ),
				admin_url( 'admin.php' )
			) );
			exit;
		}

		if ( ob_get_level() ) {
			ob_end_clean();
		}

		$filename = 'newsletter-subscribers-' . gmdate( 'Y-m-d' ) . '.csv';
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

		fputcsv( $output, array(
			__( 'ID', 'atomic-newsletter-for-elementor' ),
			__( 'Email', 'atomic-newsletter-for-elementor' ),
			__( 'Date', 'atomic-newsletter-for-elementor' ),
		) );

		foreach ( ALNC_Database::get_subscribers_chunked() as $item ) {
			fputcsv( $output, array( $item->id, $item->email, $item->date ) );
		}

		fclose( $output ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}

	/* ── DELETE HANDLERS ── */

	public static function delete_subscriber() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'atomic-newsletter-for-elementor' ) );
		}

		$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ?? '' ) );
		if ( ! wp_verify_nonce( $nonce, 'alnc_delete_subscriber_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'atomic-newsletter-for-elementor' ) );
		}

		$subscriber_id = absint( $_REQUEST['subscriber_id'] ?? 0 );
		if ( $subscriber_id <= 0 ) {
			wp_die( esc_html__( 'Invalid subscriber ID.', 'atomic-newsletter-for-elementor' ) );
		}

		ALNC_Database::delete_subscriber( $subscriber_id );

		wp_safe_redirect( add_query_arg( 'page', 'subscriber-list', admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function bulk_delete_subscribers() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'atomic-newsletter-for-elementor' ) );
		}

		if ( empty( $_POST['alnc_bulk_delete_nonce'] )
			|| ! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['alnc_bulk_delete_nonce'] ) ),
				'alnc_bulk_delete_action'
			)
		) {
			wp_die( esc_html__( 'Security check failed.', 'atomic-newsletter-for-elementor' ) );
		}

		$search = sanitize_text_field( wp_unslash( $_POST['alnc_search'] ?? '' ) );
		ALNC_Database::delete_subscribers_by_search( $search );

		wp_safe_redirect( add_query_arg( 'page', 'subscriber-list', admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function delete_subscriber_ajax() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Not allowed.', 'atomic-newsletter-for-elementor' ) );
		}

		if ( empty( $_POST['nonce'] )
			|| ! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['nonce'] ) ),
				'alnc_delete_subscriber_nonce'
			)
		) {
			wp_send_json_error( __( 'Security check failed.', 'atomic-newsletter-for-elementor' ) );
		}

		$subscriber_id = absint( $_POST['subscriber_id'] ?? 0 );
		if ( $subscriber_id <= 0 ) {
			wp_send_json_error( __( 'Invalid subscriber ID.', 'atomic-newsletter-for-elementor' ) );
		}

		ALNC_Database::delete_subscriber( $subscriber_id );
		wp_send_json_success( __( 'Subscriber deleted.', 'atomic-newsletter-for-elementor' ) );
	}

	public static function delete_selected_subscribers() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'atomic-newsletter-for-elementor' ) );
		}

		if ( empty( $_POST['alnc_delete_selected_nonce'] )
			|| ! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['alnc_delete_selected_nonce'] ) ),
				'alnc_delete_selected_action'
			)
		) {
			wp_die( esc_html__( 'Security check failed.', 'atomic-newsletter-for-elementor' ) );
		}

		if ( empty( $_POST['alnc_subscriber_ids'] ) || ! is_array( $_POST['alnc_subscriber_ids'] ) ) {
			wp_die( esc_html__( 'No subscribers selected.', 'atomic-newsletter-for-elementor' ) );
		}

		$ids = array_map( 'intval', wp_unslash( $_POST['alnc_subscriber_ids'] ) );
		ALNC_Database::delete_selected_subscribers( $ids );

		wp_safe_redirect( add_query_arg( 'page', 'subscriber-list', admin_url( 'admin.php' ) ) );
		exit;
	}

	/* ── HOW TO USE PAGE ── */

	public static function display_how_to_use() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$steps = array(
			array(
				'number' => '1',
				'title'  => __( 'Install Required Plugins', 'atomic-newsletter-for-elementor' ),
				'desc'   => __( 'Make sure these two free plugins are installed and active:', 'atomic-newsletter-for-elementor' ),
				'items'  => array(
					'<a href="https://wordpress.org/plugins/elementor/" target="_blank">Elementor</a> &mdash; ' . __( 'free page builder', 'atomic-newsletter-for-elementor' ),
					'<a href="https://proelements.org/" target="_blank">Pro Elements</a> &mdash; ' . __( 'free Elementor Pro alternative (provides Atomic Forms)', 'atomic-newsletter-for-elementor' ),
				),
			),
			array(
				'number' => '2',
				'title'  => __( 'Create a New Page', 'atomic-newsletter-for-elementor' ),
				'desc'   => __( 'Go to Pages &rarr; Add New, then click &ldquo;Edit with Elementor&rdquo;.', 'atomic-newsletter-for-elementor' ),
				'items'  => array(),
			),
			array(
				'number' => '3',
				'title'  => __( 'Add the Atomic Form Widget', 'atomic-newsletter-for-elementor' ),
				'desc'   => __( 'In the Elementor search bar type &ldquo;Atomic Form&rdquo; and drag the widget onto your page.', 'atomic-newsletter-for-elementor' ),
				'items'  => array(),
			),
			array(
				'number' => '4',
				'title'  => __( 'Set Up the Email Field Only', 'atomic-newsletter-for-elementor' ),
				'desc'   => __( 'Inside the Atomic Form widget:', 'atomic-newsletter-for-elementor' ),
				'items'  => array(
					__( 'Click <strong>+ Add Item</strong> to add a form field', 'atomic-newsletter-for-elementor' ),
					__( 'Set field <strong>Type</strong> to <strong>Email</strong>', 'atomic-newsletter-for-elementor' ),
					__( 'Set <strong>Label</strong> to <em>Email</em> or <em>Your Email</em>', 'atomic-newsletter-for-elementor' ),
					__( 'Set <strong>Placeholder</strong> to e.g. <em>Enter your email address</em>', 'atomic-newsletter-for-elementor' ),
					__( 'Mark the field as <strong>Required</strong>', 'atomic-newsletter-for-elementor' ),
					__( 'Add a <strong>Submit</strong> button field &mdash; label it <em>Subscribe</em>', 'atomic-newsletter-for-elementor' ),
					__( 'Remove any other fields you do not need', 'atomic-newsletter-for-elementor' ),
				),
			),
			array(
				'number' => '5',
				'title'  => __( 'Publish the Page', 'atomic-newsletter-for-elementor' ),
				'desc'   => __( 'Click <strong>Publish</strong> in Elementor. Your subscribe form is now live.', 'atomic-newsletter-for-elementor' ),
				'items'  => array(),
			),
			array(
				'number' => '6',
				'title'  => __( 'View Collected Emails', 'atomic-newsletter-for-elementor' ),
				'desc'   => __( 'Every time someone submits the form, their email is saved automatically. Go to <strong>Subscribers</strong> in this menu to view, search, delete, or export your list.', 'atomic-newsletter-for-elementor' ),
				'items'  => array(),
			),
		);

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'How to Use', 'atomic-newsletter-for-elementor' ) . '</h1>';
		echo '<p style="color:#666;margin-bottom:24px;">' . esc_html__( 'Follow these steps to start collecting newsletter subscribers using an Elementor Atomic Form.', 'atomic-newsletter-for-elementor' ) . '</p>';

		echo '<div style="max-width:760px;">';

		foreach ( $steps as $step ) {
			echo '<div style="display:flex;gap:16px;margin-bottom:24px;background:#fff;border:1px solid #e0e0e0;border-radius:8px;padding:20px 24px;">';

			// Step number circle.
			echo '<div style="flex-shrink:0;width:36px;height:36px;border-radius:50%;background:#2271b1;color:#fff;'
				. 'display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:600;">';
			echo esc_html( $step['number'] );
			echo '</div>';

			// Step content.
			echo '<div style="flex:1;">';
			echo '<h3 style="margin:0 0 6px;font-size:15px;">' . esc_html( $step['title'] ) . '</h3>';
			echo '<p style="margin:0 0 10px;color:#444;">' . wp_kses( $step['desc'], array( 'strong' => array(), 'em' => array(), 'a' => array( 'href' => array(), 'target' => array() ) ) ) . '</p>';

			if ( ! empty( $step['items'] ) ) {
				echo '<ul style="margin:0;padding-left:20px;">';
				foreach ( $step['items'] as $item ) {
					echo '<li style="margin-bottom:6px;color:#444;">' . wp_kses( $item, array( 'strong' => array(), 'em' => array(), 'a' => array( 'href' => array(), 'target' => array() ) ) ) . '</li>';
				}
				echo '</ul>';
			}

			echo '</div>'; // step content
			echo '</div>'; // step row
		}

		// Tip box.
		echo '<div style="background:#f0f6fc;border-left:4px solid #2271b1;padding:14px 18px;border-radius:0 6px 6px 0;margin-top:8px;">';
		echo '<strong>' . esc_html__( 'Tip:', 'atomic-newsletter-for-elementor' ) . '</strong> ';
		echo esc_html__( 'The plugin automatically detects the email field by its type or label. No extra configuration needed — just publish the form and it works.', 'atomic-newsletter-for-elementor' );
		echo '</div>';

		echo '</div>'; // max-width wrapper
		echo '</div>'; // .wrap
	}

}
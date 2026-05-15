<?php
/**
 * Plugin Name: Atomic Newsletter For Elementor
 * Description: Captures subscriber emails from Elementor Atomic Forms (and Pro Elements) and displays them in your WordPress admin. Export to CSV — free, no license required.
 * Version: 1.0.3
 * Author: MongSingHai
 * Text Domain: atomic-newsletter-for-elementor
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'ALNC_VERSION',         '1.0.3' );
define( 'ALNC_PLUGIN_DIR',      plugin_dir_path( __FILE__ ) );
define( 'ALNC_PLUGIN_URL',      plugin_dir_url( __FILE__ ) );
define( 'ALNC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'ALNC_TEXT_DOMAIN',     'atomic-newsletter-for-elementor' );

// Load all classes.
require_once ALNC_PLUGIN_DIR . 'includes/database.php';
require_once ALNC_PLUGIN_DIR . 'includes/email-validation.php';
require_once ALNC_PLUGIN_DIR . 'includes/admin.php';
require_once ALNC_PLUGIN_DIR . 'includes/form-handler.php';

// Create DB table on first activation.
register_activation_hook( __FILE__, array( 'ALNC_Database', 'create_table' ) );

/**
 * Main plugin bootstrap class.
 */
class ALNC_Plugin {

	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'load_textdomain' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'check_dependencies' ) );
		add_action( 'plugins_loaded', array( 'ALNC_Database',    'init' ) );
		add_action( 'plugins_loaded', array( 'ALNC_Admin',        'init' ) );
		add_action( 'plugins_loaded', array( 'ALNC_Form_Handler', 'init' ), 20 );
	}

	public static function load_textdomain() {
		// WordPress.org automatically loads translations since WP 4.6.
		// No manual load_plugin_textdomain() call needed.
	}

	public static function check_dependencies() {
		if ( ! self::is_elementor_active() ) {
			add_action( 'admin_notices', array( __CLASS__, 'notice_missing_elementor' ) );
		}
	}

	/**
	 * Check if Elementor Pro or a compatible alternative (e.g. Pro Elements) is active.
	 * Pro Elements (https://proelements.org/) is a free plugin that provides
	 * Elementor Pro features including Atomic Forms — fully supported by this plugin.
	 *
	 * @return bool
	 */
	private static function is_elementor_active() {
		return defined( 'ELEMENTOR_PRO_VERSION' )
			|| defined( 'PRO_ELEMENTS_VERSION' )
			|| class_exists( 'ElementorPro\\Modules\\AtomicForm\\Atomic_Form_Controller' )
			|| class_exists( 'ElementorPro\\Modules\\Forms\\Classes\\Ajax_Handler' )
			|| class_exists( 'ProElements\\Modules\\AtomicForm\\Atomic_Form_Controller' );
	}

	public static function notice_missing_elementor() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		echo '<div class="notice notice-warning is-dismissible"><p>'
			. esc_html__( 'Atomic Newsletter For Elementor requires Elementor Pro (or the free Pro Elements alternative) with Forms support.', 'atomic-newsletter-for-elementor' )
			. '</p></div>';
	}
}

ALNC_Plugin::init();

<?php
/**
 * Plugin Name:       e-Governance Complaint Portal
 * Plugin URI:        https://github.com/subhansh/e-governance-complaint-portal
 * Description:       A comprehensive complaint management system for government departments with role-based access control, SLA tracking, and Ultimate Member integration.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            subhansh
 * Author URI:        https://yourwebsite.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       e-governance-complaint-portal
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Current plugin version.
 */
define( 'EGCP_VERSION', '1.0.0' );

/**
 * Plugin directory path.
 */
define( 'EGCP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'EGCP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 */
define( 'EGCP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_egcp() {
    require_once EGCP_PLUGIN_DIR . 'includes/class-egcp-activator.php';
    EGCP_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_egcp() {
    require_once EGCP_PLUGIN_DIR . 'includes/class-egcp-deactivator.php';
    EGCP_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_egcp' );
register_deactivation_hook( __FILE__, 'deactivate_egcp' );

/**
 * Autoloader for plugin classes.
 */
spl_autoload_register( function ( $class ) {
    // Only autoload classes with EGCP_ prefix
    if ( strpos( $class, 'EGCP_' ) !== 0 ) {
        return;
    }

    // Convert class name to file name
    $class_file = 'class-' . strtolower( str_replace( '_', '-', $class ) ) . '.php';

    // Check in includes directory
    $includes_path = EGCP_PLUGIN_DIR . 'includes/' . $class_file;
    if ( file_exists( $includes_path ) ) {
        require_once $includes_path;
        return;
    }

    // Check in admin directory
    $admin_path = EGCP_PLUGIN_DIR . 'admin/' . $class_file;
    if ( file_exists( $admin_path ) ) {
        require_once $admin_path;
        return;
    }

    // Check in public directory
    $public_path = EGCP_PLUGIN_DIR . 'public/' . $class_file;
    if ( file_exists( $public_path ) ) {
        require_once $public_path;
    }
} );

/**
 * Main plugin class.
 */
class E_Governance_Complaint_Portal {

    /**
     * The single instance of the class.
     *
     * @var E_Governance_Complaint_Portal
     */
    protected static $instance = null;

    /**
     * Main instance.
     *
     * @return E_Governance_Complaint_Portal
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks.
     */
    private function init_hooks() {
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
    }

    /**
     * Load plugin textdomain for translations.
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'e-governance-complaint-portal',
            false,
            dirname( EGCP_PLUGIN_BASENAME ) . '/languages'
        );
    }

    /**
     * Initialize plugin components.
     */
    public function init() {
        // Initialize admin menu
        if ( is_admin() ) {
            new EGCP_Admin_Menu();
        }

        // Initialize shortcodes
        new EGCP_Shortcodes();

        // Initialize Ultimate Member integration if UM is active
        if ( class_exists( 'UM' ) ) {
            new EGCP_UM_Integration();
        }

        // Run SLA checker daily
        if ( ! wp_next_scheduled( 'egcp_check_sla' ) ) {
            wp_schedule_event( time(), 'daily', 'egcp_check_sla' );
        }
        add_action( 'egcp_check_sla', array( 'EGCP_SLA', 'check_overdue_complaints' ) );
    }

    /**
     * Enqueue admin assets (CSS & JS).
     */
    public function enqueue_admin_assets( $hook ) {
        // Only load on our plugin pages
        if ( strpos( $hook, 'egcp' ) === false && strpos( $hook, 'e-governance' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'egcp-admin-style',
            EGCP_PLUGIN_URL . 'assets/css/admin-style.css',
            array(),
            EGCP_VERSION
        );

        wp_enqueue_script(
            'egcp-admin-script',
            EGCP_PLUGIN_URL . 'assets/js/admin-script.js',
            array( 'jquery' ),
            EGCP_VERSION,
            true
        );

        // Localize script for AJAX
        wp_localize_script( 'egcp-admin-script', 'egcpAdmin', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'egcp_admin_nonce' ),
        ) );
    }

    /**
     * Enqueue public assets (CSS & JS).
     */
    public function enqueue_public_assets() {
        wp_enqueue_style(
            'egcp-public-style',
            EGCP_PLUGIN_URL . 'assets/css/public-style.css',
            array(),
            EGCP_VERSION
        );

        wp_enqueue_script(
            'egcp-public-script',
            EGCP_PLUGIN_URL . 'assets/js/public-script.js',
            array(),
            EGCP_VERSION,
            true
        );

        // Localize script for AJAX
        wp_localize_script( 'egcp-public-script', 'egcpPublic', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'egcp_public_nonce' ),
            'maxImages' => 5,
            'maxFileSize' => 2 * 1024 * 1024, // 2MB in bytes
        ) );
    }
}

/**
 * Initialize the plugin.
 */
function egcp() {
    return E_Governance_Complaint_Portal::instance();
}

// Start the plugin
egcp();
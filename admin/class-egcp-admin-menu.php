<?php
/**
 * Admin Menu for e-Governance Complaint Portal.
 *
 * Registers admin menu pages and handles routing.
 *
 * @package    E_Governance_Complaint_Portal
 * @subpackage Admin
 */

class EGCP_Admin_Menu {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menus' ) );
    }

    /**
     * Register admin menu pages.
     */
    public function register_menus() {
        // Determine which menu to show based on user role
        $user = wp_get_current_user();

        if ( current_user_can( 'manage_complaints' ) ) {
            // Admin users see Control Room
            $this->register_admin_menus();
        } elseif ( EGCP_Roles::is_officer( get_current_user_id() ) ) {
            // Officers see their department dashboard
            $this->register_officer_menus();
        }
    }

    /**
     * Register admin-level menus (for administrators).
     */
    private function register_admin_menus() {
        // Main menu
        add_menu_page(
            __( 'e-Governance Complaints', 'e-governance-complaint-portal' ),
            __( 'Complaints', 'e-governance-complaint-portal' ),
            'manage_complaints',
            'egcp-control-room',
            array( $this, 'render_control_room' ),
            'dashicons-megaphone',
            30
        );

        // Control Room (same as main)
        add_submenu_page(
            'egcp-control-room',
            __( 'Control Room', 'e-governance-complaint-portal' ),
            __( 'Control Room', 'e-governance-complaint-portal' ),
            'manage_complaints',
            'egcp-control-room',
            array( $this, 'render_control_room' )
        );

        // All Complaints
        add_submenu_page(
            'egcp-control-room',
            __( 'All Complaints', 'e-governance-complaint-portal' ),
            __( 'All Complaints', 'e-governance-complaint-portal' ),
            'manage_complaints',
            'egcp-all-complaints',
            array( $this, 'render_all_complaints' )
        );

        // Officers Management
        add_submenu_page(
            'egcp-control-room',
            __( 'Manage Officers', 'e-governance-complaint-portal' ),
            __( 'Officers', 'e-governance-complaint-portal' ),
            'assign_officer_roles',
            'egcp-officers',
            array( $this, 'render_officers' )
        );

        // Settings
        add_submenu_page(
            'egcp-control-room',
            __( 'Settings', 'e-governance-complaint-portal' ),
            __( 'Settings', 'e-governance-complaint-portal' ),
            'manage_options',
            'egcp-settings',
            array( $this, 'render_settings' )
        );
    }

    /**
     * Register officer-level menus.
     */
    private function register_officer_menus() {
        // Officer Dashboard
        add_menu_page(
            __( 'My Department', 'e-governance-complaint-portal' ),
            __( 'My Department', 'e-governance-complaint-portal' ),
            'read_complaints',
            'egcp-officer-dashboard',
            array( $this, 'render_officer_dashboard' ),
            'dashicons-clipboard',
            30
        );
    }

    /**
     * Render Control Room page (Admin).
     */
    public function render_control_room() {
        if ( ! current_user_can( 'manage_complaints' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'e-governance-complaint-portal' ) );
        }

        $control_room = new EGCP_Control_Room();
        $control_room->render();
    }

    /**
     * Render All Complaints page (Admin).
     */
    public function render_all_complaints() {
        if ( ! current_user_can( 'manage_complaints' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'e-governance-complaint-portal' ) );
        }

        include EGCP_PLUGIN_DIR . 'admin/views/all-complaints.php';
    }

    /**
     * Render Officers Management page (Admin).
     */
    public function render_officers() {
        if ( ! current_user_can( 'assign_officer_roles' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'e-governance-complaint-portal' ) );
        }

        include EGCP_PLUGIN_DIR . 'admin/views/officers.php';
    }

    /**
     * Render Settings page (Admin).
     */
    public function render_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'e-governance-complaint-portal' ) );
        }

        include EGCP_PLUGIN_DIR . 'admin/views/settings.php';
    }

    /**
     * Render Officer Dashboard (Officer).
     */
    public function render_officer_dashboard() {
        if ( ! current_user_can( 'read_complaints' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'e-governance-complaint-portal' ) );
        }

        $officer_dashboard = new EGCP_Officer_Dashboard();
        $officer_dashboard->render();
    }
}
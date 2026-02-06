<?php
/**
 * Plugin Activation Handler.
 *
 * Runs during plugin activation to set up database, roles, and default settings.
 *
 * @package    E_Governance_Complaint_Portal
 * @subpackage Includes
 */

class EGCP_Activator {

    /**
     * Activate the plugin.
     *
     * - Creates database tables
     * - Adds custom user roles
     * - Sets default options
     * - Flushes rewrite rules
     */
    public static function activate() {
        // Create database tables
        EGCP_Database::create_tables();

        // Add custom user roles and capabilities
        EGCP_Roles::add_roles();

        // Set default plugin options
        self::set_default_options();

        // Create uploads directory for complaint images
        self::create_upload_directory();

        // Flush rewrite rules (for any custom post types or endpoints)
        flush_rewrite_rules();

        // Store activation timestamp
        update_option( 'egcp_activated_at', current_time( 'mysql' ) );
    }

    /**
     * Set default plugin options.
     */
    private static function set_default_options() {
        $default_options = array(
            // General Settings
            'egcp_enable_submissions'     => 'yes',
            'egcp_allowed_departments'    => array( 'health', 'water', 'electricity' ),

            // SLA Settings (in days)
            'egcp_sla_health'             => 7,
            'egcp_sla_water'              => 15,
            'egcp_sla_electricity'        => 10,

            // Email Notifications
            'egcp_notify_citizen_status'  => 'yes',
            'egcp_notify_officer_assign'  => 'yes',
            'egcp_admin_email_escalation' => get_option( 'admin_email' ),

            // Image Upload Settings
            'egcp_max_images'             => 5,
            'egcp_max_file_size'          => 2097152, // 2MB in bytes
            'egcp_allowed_formats'        => array( 'jpg', 'jpeg', 'png', 'webp' ),

            // Status Options
            'egcp_status_options'         => array(
                'pending'      => __( 'Pending Review', 'e-governance-complaint-portal' ),
                'approved'     => __( 'Approved', 'e-governance-complaint-portal' ),
                'in_progress'  => __( 'In Progress', 'e-governance-complaint-portal' ),
                'resolved'     => __( 'Resolved', 'e-governance-complaint-portal' ),
                'rejected'     => __( 'Rejected', 'e-governance-complaint-portal' ),
                'escalated'    => __( 'Escalated', 'e-governance-complaint-portal' ),
            ),

            // Priority Options
            'egcp_priority_options'       => array(
                'low'    => __( 'Low', 'e-governance-complaint-portal' ),
                'medium' => __( 'Medium', 'e-governance-complaint-portal' ),
                'high'   => __( 'High', 'e-governance-complaint-portal' ),
            ),

            // Indian States (for dropdown)
            'egcp_states'                 => array(
                'Andhra Pradesh',
                'Arunachal Pradesh',
                'Assam',
                'Bihar',
                'Chhattisgarh',
                'Goa',
                'Gujarat',
                'Haryana',
                'Himachal Pradesh',
                'Jharkhand',
                'Karnataka',
                'Kerala',
                'Madhya Pradesh',
                'Maharashtra',
                'Manipur',
                'Meghalaya',
                'Mizoram',
                'Nagaland',
                'Odisha',
                'Punjab',
                'Rajasthan',
                'Sikkim',
                'Tamil Nadu',
                'Telangana',
                'Tripura',
                'Uttar Pradesh',
                'Uttarakhand',
                'West Bengal',
                'Andaman and Nicobar Islands',
                'Chandigarh',
                'Dadra and Nagar Haveli and Daman and Diu',
                'Delhi',
                'Jammu and Kashmir',
                'Ladakh',
                'Lakshadweep',
                'Puducherry',
            ),
        );

        foreach ( $default_options as $key => $value ) {
            // Only set if option doesn't already exist
            if ( false === get_option( $key ) ) {
                add_option( $key, $value );
            }
        }
    }

    /**
     * Create upload directory for complaint images.
     */
    private static function create_upload_directory() {
        $upload_dir = wp_upload_dir();
        $egcp_dir   = $upload_dir['basedir'] . '/egcp-complaints';

        if ( ! file_exists( $egcp_dir ) ) {
            wp_mkdir_p( $egcp_dir );

            // Create .htaccess to protect direct access (optional security)
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "<Files *.php>\n";
            $htaccess_content .= "deny from all\n";
            $htaccess_content .= "</Files>\n";

            file_put_contents( $egcp_dir . '/.htaccess', $htaccess_content );

            // Create blank index.php for security
            file_put_contents( $egcp_dir . '/index.php', '<?php // Silence is golden.' );
        }
    }
}
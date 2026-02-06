<?php
/**
 * Database operations for e-Governance Complaint Portal.
 *
 * Creates and manages custom database tables.
 *
 * @package    E_Governance_Complaint_Portal
 * @subpackage Includes
 */

class EGCP_Database {

    /**
     * Create all plugin tables.
     */
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table names
        $complaints_table = $wpdb->prefix . 'egcp_complaints';
        $replies_table    = $wpdb->prefix . 'egcp_replies';
        $status_table     = $wpdb->prefix . 'egcp_status_history';

        // SQL for complaints table
        $sql_complaints = "CREATE TABLE $complaints_table (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            grievance_id varchar(50) NOT NULL,
            citizen_id bigint(20) UNSIGNED NOT NULL,
            title varchar(200) NOT NULL,
            description text NOT NULL,
            department varchar(50) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            priority varchar(20) DEFAULT 'medium',
            state varchar(100) NOT NULL,
            district varchar(100) NOT NULL,
            ward varchar(100) DEFAULT NULL,
            pincode varchar(10) NOT NULL,
            images text DEFAULT NULL,
            sla_deadline datetime DEFAULT NULL,
            sla_status varchar(20) DEFAULT 'within_sla',
            assigned_officer bigint(20) UNSIGNED DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY grievance_id (grievance_id),
            KEY citizen_id (citizen_id),
            KEY department (department),
            KEY status (status),
            KEY sla_status (sla_status),
            KEY assigned_officer (assigned_officer)
        ) $charset_collate;";

        // SQL for replies table
        $sql_replies = "CREATE TABLE $replies_table (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            complaint_id bigint(20) UNSIGNED NOT NULL,
            user_id bigint(20) UNSIGNED NOT NULL,
            reply_type varchar(20) NOT NULL,
            message text NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY complaint_id (complaint_id),
            KEY user_id (user_id),
            KEY reply_type (reply_type)
        ) $charset_collate;";

        // SQL for status history table
        $sql_status = "CREATE TABLE $status_table (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            complaint_id bigint(20) UNSIGNED NOT NULL,
            changed_by bigint(20) UNSIGNED NOT NULL,
            old_status varchar(20) NOT NULL,
            new_status varchar(20) NOT NULL,
            remarks text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY complaint_id (complaint_id),
            KEY changed_by (changed_by)
        ) $charset_collate;";

        // Include WordPress upgrade functions
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Execute table creation
        dbDelta( $sql_complaints );
        dbDelta( $sql_replies );
        dbDelta( $sql_status );

        // Store database version
        update_option( 'egcp_db_version', EGCP_VERSION );
    }

    /**
     * Drop all plugin tables (used on uninstall).
     */
    public static function drop_tables() {
        global $wpdb;

        $tables = array(
            $wpdb->prefix . 'egcp_complaints',
            $wpdb->prefix . 'egcp_replies',
            $wpdb->prefix . 'egcp_status_history',
        );

        foreach ( $tables as $table ) {
            $wpdb->query( "DROP TABLE IF EXISTS $table" );
        }

        delete_option( 'egcp_db_version' );
    }

    /**
     * Check if database needs update.
     *
     * @return bool
     */
    public static function needs_update() {
        $current_version = get_option( 'egcp_db_version', '0' );
        return version_compare( $current_version, EGCP_VERSION, '<' );
    }
}
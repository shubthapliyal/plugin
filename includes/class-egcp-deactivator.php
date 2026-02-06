<?php
/**
 * Plugin Deactivation Handler.
 *
 * Runs during plugin deactivation.
 * Note: This does NOT delete data - only removes scheduled events.
 *
 * @package    E_Governance_Complaint_Portal
 * @subpackage Includes
 */

class EGCP_Deactivator {

    /**
     * Deactivate the plugin.
     *
     * - Clears scheduled cron jobs
     * - Flushes rewrite rules
     * - Does NOT delete database tables or user roles (preserved for reactivation)
     */
    public static function deactivate() {
        // Clear scheduled SLA checker
        $timestamp = wp_next_scheduled( 'egcp_check_sla' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'egcp_check_sla' );
        }

        // Flush rewrite rules
        flush_rewrite_rules();

        // Store deactivation timestamp
        update_option( 'egcp_deactivated_at', current_time( 'mysql' ) );
    }

    /**
     * Complete uninstall (called from uninstall.php).
     *
     * WARNING: This deletes ALL plugin data permanently.
     * - Drops database tables
     * - Removes user roles
     * - Deletes all options
     * - Removes uploaded files
     */
    public static function uninstall() {
        // Check if user has permission
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        // Drop database tables
        EGCP_Database::drop_tables();

        // Remove custom roles
        EGCP_Roles::remove_roles();

        // Delete all plugin options
        self::delete_options();

        // Remove uploaded files directory
        self::delete_upload_directory();
    }

    /**
     * Delete all plugin options from database.
     */
    private static function delete_options() {
        global $wpdb;

        // Delete all options starting with 'egcp_'
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'egcp_%'" );

        // Clear option cache
        wp_cache_flush();
    }

    /**
     * Delete upload directory and all files.
     */
    private static function delete_upload_directory() {
        $upload_dir = wp_upload_dir();
        $egcp_dir   = $upload_dir['basedir'] . '/egcp-complaints';

        if ( file_exists( $egcp_dir ) ) {
            self::recursive_delete( $egcp_dir );
        }
    }

    /**
     * Recursively delete directory and contents.
     *
     * @param string $dir Directory path.
     */
    private static function recursive_delete( $dir ) {
        if ( ! is_dir( $dir ) ) {
            return;
        }

        $files = array_diff( scandir( $dir ), array( '.', '..' ) );

        foreach ( $files as $file ) {
            $path = $dir . '/' . $file;
            if ( is_dir( $path ) ) {
                self::recursive_delete( $path );
            } else {
                unlink( $path );
            }
        }

        rmdir( $dir );
    }
}
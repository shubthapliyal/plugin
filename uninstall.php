<?php
/**
 * Plugin Uninstall Script.
 *
 * Fires when the plugin is uninstalled via WordPress admin.
 * Removes all plugin data including database tables, options, roles, and files.
 *
 * @package E_Governance_Complaint_Portal
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Load plugin dependencies
require_once plugin_dir_path( __FILE__ ) . 'includes/class-egcp-database.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-egcp-roles.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-egcp-deactivator.php';

// Run complete uninstall
EGCP_Deactivator::uninstall();
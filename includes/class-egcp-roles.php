<?php
/**
 * User Roles Management for e-Governance Complaint Portal.
 *
 * Creates custom roles: health_officer, water_officer, electricity_officer
 *
 * @package    E_Governance_Complaint_Portal
 * @subpackage Includes
 */

class EGCP_Roles {

    /**
     * Add custom roles and capabilities.
     */
    public static function add_roles() {
        // Define officer capabilities
        $officer_capabilities = array(
            'read'                    => true,  // Basic WordPress capability
            'read_complaints'         => true,  // View complaints
            'update_complaint_status' => true,  // Change complaint status
            'reply_to_complaint'      => true,  // Add officer replies
            'view_sla_timer'          => true,  // See SLA information
        );

        // Add Health Officer role
        add_role(
            'health_officer',
            __( 'Health Officer', 'e-governance-complaint-portal' ),
            $officer_capabilities
        );

        // Add Water Officer role
        add_role(
            'water_officer',
            __( 'Water Officer', 'e-governance-complaint-portal' ),
            $officer_capabilities
        );

        // Add Electricity Officer role
        add_role(
            'electricity_officer',
            __( 'Electricity Officer', 'e-governance-complaint-portal' ),
            $officer_capabilities
        );

        // Add capabilities to Administrator
        $admin_role = get_role( 'administrator' );
        if ( $admin_role ) {
            $admin_role->add_cap( 'manage_complaints' );
            $admin_role->add_cap( 'assign_complaints' );
            $admin_role->add_cap( 'delete_complaints' );
            $admin_role->add_cap( 'export_complaints' );
            $admin_role->add_cap( 'view_control_room' );
            $admin_role->add_cap( 'assign_officer_roles' );
        }
    }

    /**
     * Remove custom roles and capabilities.
     */
    public static function remove_roles() {
        // Remove custom roles
        remove_role( 'health_officer' );
        remove_role( 'water_officer' );
        remove_role( 'electricity_officer' );

        // Remove custom capabilities from administrator
        $admin_role = get_role( 'administrator' );
        if ( $admin_role ) {
            $admin_role->remove_cap( 'manage_complaints' );
            $admin_role->remove_cap( 'assign_complaints' );
            $admin_role->remove_cap( 'delete_complaints' );
            $admin_role->remove_cap( 'export_complaints' );
            $admin_role->remove_cap( 'view_control_room' );
            $admin_role->remove_cap( 'assign_officer_roles' );
        }
    }

    /**
     * Get department from user role.
     *
     * @param int $user_id User ID.
     * @return array Array of departments the user is officer for.
     */
    public static function get_user_departments( $user_id ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return array();
        }

        $departments = array();
        $roles       = $user->roles;

        if ( in_array( 'health_officer', $roles, true ) ) {
            $departments[] = 'health';
        }
        if ( in_array( 'water_officer', $roles, true ) ) {
            $departments[] = 'water';
        }
        if ( in_array( 'electricity_officer', $roles, true ) ) {
            $departments[] = 'electricity';
        }

        return $departments;
    }

    /**
     * Check if user is an officer.
     *
     * @param int $user_id User ID.
     * @return bool
     */
    public static function is_officer( $user_id ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return false;
        }

        $officer_roles = array( 'health_officer', 'water_officer', 'electricity_officer' );
        return ! empty( array_intersect( $officer_roles, $user->roles ) );
    }

    /**
     * Get all officers for a specific department.
     *
     * @param string $department Department name.
     * @return array Array of user IDs.
     */
    public static function get_department_officers( $department ) {
        $role_map = array(
            'health'      => 'health_officer',
            'water'       => 'water_officer',
            'electricity' => 'electricity_officer',
        );

        if ( ! isset( $role_map[ $department ] ) ) {
            return array();
        }

        $users = get_users( array(
            'role'   => $role_map[ $department ],
            'fields' => 'ID',
        ) );

        return $users;
    }

    /**
     * Assign officer role to a user (admin only).
     *
     * @param int    $user_id User ID.
     * @param string $department Department name.
     * @return bool|WP_Error
     */
    public static function assign_officer_role( $user_id, $department ) {
        // Check if current user has permission
        if ( ! current_user_can( 'assign_officer_roles' ) ) {
            return new WP_Error( 'permission_denied', __( 'You do not have permission to assign officer roles.', 'e-governance-complaint-portal' ) );
        }

        $role_map = array(
            'health'      => 'health_officer',
            'water'       => 'water_officer',
            'electricity' => 'electricity_officer',
        );

        if ( ! isset( $role_map[ $department ] ) ) {
            return new WP_Error( 'invalid_department', __( 'Invalid department specified.', 'e-governance-complaint-portal' ) );
        }

        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return new WP_Error( 'invalid_user', __( 'User not found.', 'e-governance-complaint-portal' ) );
        }

        // Add the role (users can have multiple roles)
        $user->add_role( $role_map[ $department ] );

        return true;
    }

    /**
     * Remove officer role from a user (admin only).
     *
     * @param int    $user_id User ID.
     * @param string $department Department name.
     * @return bool|WP_Error
     */
    public static function remove_officer_role( $user_id, $department ) {
        // Check if current user has permission
        if ( ! current_user_can( 'assign_officer_roles' ) ) {
            return new WP_Error( 'permission_denied', __( 'You do not have permission to remove officer roles.', 'e-governance-complaint-portal' ) );
        }

        $role_map = array(
            'health'      => 'health_officer',
            'water'       => 'water_officer',
            'electricity' => 'electricity_officer',
        );

        if ( ! isset( $role_map[ $department ] ) ) {
            return new WP_Error( 'invalid_department', __( 'Invalid department specified.', 'e-governance-complaint-portal' ) );
        }

        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return new WP_Error( 'invalid_user', __( 'User not found.', 'e-governance-complaint-portal' ) );
        }

        // Remove the role
        $user->remove_role( $role_map[ $department ] );

        return true;
    }
}
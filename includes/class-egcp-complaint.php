<?php
/**
 * Complaint CRUD Operations.
 *
 * Handles Create, Read, Update, Delete operations for complaints.
 *
 * @package    E_Governance_Complaint_Portal
 * @subpackage Includes
 */

class EGCP_Complaint {

    /**
     * Create a new complaint.
     *
     * @param array $data Complaint data.
     * @return int|WP_Error Complaint ID on success, WP_Error on failure.
     */
    public static function create( $data ) {
        global $wpdb;

        // Validate required fields
        $required = array( 'title', 'description', 'department', 'state', 'district', 'pincode' );
        foreach ( $required as $field ) {
            if ( empty( $data[ $field ] ) ) {
                return new WP_Error( 'missing_field', sprintf( __( 'Field %s is required.', 'e-governance-complaint-portal' ), $field ) );
            }
        }

        // Get citizen ID (current user)
        $citizen_id = get_current_user_id();
        if ( ! $citizen_id ) {
            return new WP_Error( 'not_logged_in', __( 'You must be logged in to submit a complaint.', 'e-governance-complaint-portal' ) );
        }

        // Generate unique Grievance ID
        $grievance_id = self::generate_grievance_id( $data['department'] );

        // Calculate SLA deadline
        $created_at   = current_time( 'mysql' );
        $sla_deadline = EGCP_SLA::calculate_deadline( $data['department'], $created_at );

        // Prepare data for insertion
        $insert_data = array(
            'grievance_id'  => $grievance_id,
            'citizen_id'    => $citizen_id,
            'title'         => sanitize_text_field( $data['title'] ),
            'description'   => sanitize_textarea_field( $data['description'] ),
            'department'    => sanitize_text_field( $data['department'] ),
            'status'        => 'pending',
            'priority'      => isset( $data['priority'] ) ? sanitize_text_field( $data['priority'] ) : 'medium',
            'state'         => sanitize_text_field( $data['state'] ),
            'district'      => sanitize_text_field( $data['district'] ),
            'ward'          => isset( $data['ward'] ) ? sanitize_text_field( $data['ward'] ) : '',
            'pincode'       => sanitize_text_field( $data['pincode'] ),
            'images'        => isset( $data['images'] ) ? wp_json_encode( $data['images'] ) : null,
            'sla_deadline'  => $sla_deadline,
            'sla_status'    => 'within_sla',
            'created_at'    => $created_at,
        );

        $table = $wpdb->prefix . 'egcp_complaints';

        // Insert complaint
        $inserted = $wpdb->insert( $table, $insert_data );

        if ( false === $inserted ) {
            return new WP_Error( 'db_error', __( 'Failed to create complaint.', 'e-governance-complaint-portal' ) );
        }

        $complaint_id = $wpdb->insert_id;

        // Log initial status
        self::log_status_change( $complaint_id, '', 'pending', __( 'Complaint submitted', 'e-governance-complaint-portal' ) );

        return $complaint_id;
    }

    /**
     * Generate unique Grievance ID.
     *
     * Format: EGCP-DEPT-YYYYMMDD-XXXX
     * Example: EGCP-HLTH-20260206-0001
     *
     * @param string $department Department name.
     * @return string Grievance ID.
     */
    private static function generate_grievance_id( $department ) {
        global $wpdb;

        $dept_codes = array(
            'health'      => 'HLTH',
            'water'       => 'WATR',
            'electricity' => 'ELEC',
        );

        $dept_code = isset( $dept_codes[ $department ] ) ? $dept_codes[ $department ] : 'MISC';
        $date      = date( 'Ymd' );

        // Get today's count for this department
        $table = $wpdb->prefix . 'egcp_complaints';
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE department = %s AND DATE(created_at) = CURDATE()",
                $department
            )
        );

        $sequence = str_pad( $count + 1, 4, '0', STR_PAD_LEFT );

        return "EGCP-{$dept_code}-{$date}-{$sequence}";
    }

    /**
     * Get complaint by ID.
     *
     * @param int $complaint_id Complaint ID.
     * @return object|null Complaint object or null if not found.
     */
    public static function get( $complaint_id ) {
        global $wpdb;

        $table = $wpdb->prefix . 'egcp_complaints';

        $complaint = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $complaint_id )
        );

        if ( $complaint && $complaint->images ) {
            $complaint->images = json_decode( $complaint->images, true );
        }

        return $complaint;
    }

    /**
     * Get complaint by Grievance ID.
     *
     * @param string $grievance_id Grievance ID.
     * @return object|null Complaint object or null if not found.
     */
    public static function get_by_grievance_id( $grievance_id ) {
        global $wpdb;

        $table = $wpdb->prefix . 'egcp_complaints';

        $complaint = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE grievance_id = %s", $grievance_id )
        );

        if ( $complaint && $complaint->images ) {
            $complaint->images = json_decode( $complaint->images, true );
        }

        return $complaint;
    }

    /**
     * Get complaints with filters and pagination.
     *
     * @param array $args Query arguments.
     * @return array Array of complaints.
     */
    public static function query( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'citizen_id'  => null,
            'department'  => null,
            'status'      => null,
            'sla_status'  => null,
            'officer_id'  => null,
            'per_page'    => 20,
            'page'        => 1,
            'orderby'     => 'created_at',
            'order'       => 'DESC',
        );

        $args = wp_parse_args( $args, $defaults );

        $table  = $wpdb->prefix . 'egcp_complaints';
        $where  = array( '1=1' );
        $params = array();

        // Build WHERE clauses
        if ( $args['citizen_id'] ) {
            $where[]  = 'citizen_id = %d';
            $params[] = $args['citizen_id'];
        }

        if ( $args['department'] ) {
            $where[]  = 'department = %s';
            $params[] = $args['department'];
        }

        if ( $args['status'] ) {
            $where[]  = 'status = %s';
            $params[] = $args['status'];
        }

        if ( $args['sla_status'] ) {
            $where[]  = 'sla_status = %s';
            $params[] = $args['sla_status'];
        }

        if ( $args['officer_id'] ) {
            $where[]  = 'assigned_officer = %d';
            $params[] = $args['officer_id'];
        }

        $where_sql = implode( ' AND ', $where );

        // Pagination
        $offset = ( $args['page'] - 1 ) * $args['per_page'];
        $limit  = $args['per_page'];

        // Get total count
        $count_sql = "SELECT COUNT(*) FROM $table WHERE $where_sql";
        $total     = $wpdb->get_var( empty( $params ) ? $count_sql : $wpdb->prepare( $count_sql, $params ) );

        // Get results
        $orderby   = sanitize_sql_orderby( $args['orderby'] . ' ' . $args['order'] );
        $query_sql = "SELECT * FROM $table WHERE $where_sql ORDER BY $orderby LIMIT %d OFFSET %d";

        $params[] = $limit;
        $params[] = $offset;

        $complaints = $wpdb->get_results( $wpdb->prepare( $query_sql, $params ) );

        // Decode images JSON
        foreach ( $complaints as &$complaint ) {
            if ( $complaint->images ) {
                $complaint->images = json_decode( $complaint->images, true );
            }
        }

        return array(
            'complaints' => $complaints,
            'total'      => (int) $total,
            'pages'      => ceil( $total / $args['per_page'] ),
        );
    }

    /**
     * Update complaint.
     *
     * @param int   $complaint_id Complaint ID.
     * @param array $data Data to update.
     * @return bool|WP_Error
     */
    public static function update( $complaint_id, $data ) {
        global $wpdb;

        $table = $wpdb->prefix . 'egcp_complaints';

        // Sanitize data
        $update_data = array();

        if ( isset( $data['status'] ) ) {
            $update_data['status'] = sanitize_text_field( $data['status'] );
        }

        if ( isset( $data['priority'] ) ) {
            $update_data['priority'] = sanitize_text_field( $data['priority'] );
        }

        if ( isset( $data['assigned_officer'] ) ) {
            $update_data['assigned_officer'] = (int) $data['assigned_officer'];
        }

        if ( empty( $update_data ) ) {
            return new WP_Error( 'no_data', __( 'No data to update.', 'e-governance-complaint-portal' ) );
        }

        // Get old values for logging
        $old_complaint = self::get( $complaint_id );

        // Update complaint
        $updated = $wpdb->update(
            $table,
            $update_data,
            array( 'id' => $complaint_id ),
            null,
            array( '%d' )
        );

        if ( false === $updated ) {
            return new WP_Error( 'update_failed', __( 'Failed to update complaint.', 'e-governance-complaint-portal' ) );
        }

        // Log status change if status was updated
        if ( isset( $data['status'] ) && $old_complaint->status !== $data['status'] ) {
            self::log_status_change(
                $complaint_id,
                $old_complaint->status,
                $data['status'],
                isset( $data['remarks'] ) ? $data['remarks'] : ''
            );
        }

        return true;
    }

    /**
     * Delete complaint (admin only).
     *
     * @param int $complaint_id Complaint ID.
     * @return bool|WP_Error
     */
    public static function delete( $complaint_id ) {
        if ( ! current_user_can( 'delete_complaints' ) ) {
            return new WP_Error( 'permission_denied', __( 'You do not have permission to delete complaints.', 'e-governance-complaint-portal' ) );
        }

        global $wpdb;

        // Delete complaint
        $table = $wpdb->prefix . 'egcp_complaints';
        $wpdb->delete( $table, array( 'id' => $complaint_id ), array( '%d' ) );

        // Delete replies
        $replies_table = $wpdb->prefix . 'egcp_replies';
        $wpdb->delete( $replies_table, array( 'complaint_id' => $complaint_id ), array( '%d' ) );

        // Delete status history
        $history_table = $wpdb->prefix . 'egcp_status_history';
        $wpdb->delete( $history_table, array( 'complaint_id' => $complaint_id ), array( '%d' ) );

        return true;
    }

    /**
     * Add reply to complaint.
     *
     * @param int    $complaint_id Complaint ID.
     * @param string $message Reply message.
     * @param string $reply_type Reply type (officer|admin).
     * @return int|WP_Error Reply ID on success, WP_Error on failure.
     */
    public static function add_reply( $complaint_id, $message, $reply_type = 'officer' ) {
        global $wpdb;

        $user_id = get_current_user_id();

        if ( ! $user_id ) {
            return new WP_Error( 'not_logged_in', __( 'You must be logged in to reply.', 'e-governance-complaint-portal' ) );
        }

        // Validate reply type
        if ( 'officer' === $reply_type && ! current_user_can( 'reply_to_complaint' ) ) {
            return new WP_Error( 'permission_denied', __( 'You do not have permission to add officer replies.', 'e-governance-complaint-portal' ) );
        }

        if ( 'admin' === $reply_type && ! current_user_can( 'manage_complaints' ) ) {
            return new WP_Error( 'permission_denied', __( 'You do not have permission to add admin replies.', 'e-governance-complaint-portal' ) );
        }

        $table = $wpdb->prefix . 'egcp_replies';

        $inserted = $wpdb->insert(
            $table,
            array(
                'complaint_id' => $complaint_id,
                'user_id'      => $user_id,
                'reply_type'   => $reply_type,
                'message'      => sanitize_textarea_field( $message ),
            ),
            array( '%d', '%d', '%s', '%s' )
        );

        if ( false === $inserted ) {
            return new WP_Error( 'insert_failed', __( 'Failed to add reply.', 'e-governance-complaint-portal' ) );
        }

        return $wpdb->insert_id;
    }

    /**
     * Get replies for a complaint.
     *
     * @param int $complaint_id Complaint ID.
     * @return array Array of reply objects.
     */
    public static function get_replies( $complaint_id ) {
        global $wpdb;

        $table = $wpdb->prefix . 'egcp_replies';

        $replies = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE complaint_id = %d ORDER BY created_at ASC",
                $complaint_id
            )
        );

        return $replies;
    }

    /**
     * Log status change in history table.
     *
     * @param int    $complaint_id Complaint ID.
     * @param string $old_status Old status.
     * @param string $new_status New status.
     * @param string $remarks Optional remarks.
     */
    private static function log_status_change( $complaint_id, $old_status, $new_status, $remarks = '' ) {
        global $wpdb;

        $table = $wpdb->prefix . 'egcp_status_history';

        $wpdb->insert(
            $table,
            array(
                'complaint_id' => $complaint_id,
                'changed_by'   => get_current_user_id(),
                'old_status'   => $old_status,
                'new_status'   => $new_status,
                'remarks'      => sanitize_textarea_field( $remarks ),
            ),
            array( '%d', '%d', '%s', '%s', '%s' )
        );
    }

    /**
     * Get status history for a complaint.
     *
     * @param int $complaint_id Complaint ID.
     * @return array Array of status history objects.
     */
    public static function get_status_history( $complaint_id ) {
        global $wpdb;

        $table = $wpdb->prefix . 'egcp_status_history';

        $history = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE complaint_id = %d ORDER BY created_at ASC",
                $complaint_id
            )
        );

        return $history;
    }
}
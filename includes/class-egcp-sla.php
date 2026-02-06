<?php
/**
 * SLA (Service Level Agreement) Management.
 *
 * Handles SLA deadline calculations and overdue complaint checks.
 *
 * @package    E_Governance_Complaint_Portal
 * @subpackage Includes
 */

class EGCP_SLA {

    /**
     * Calculate SLA deadline for a complaint.
     *
     * @param string $department Department name (health|water|electricity).
     * @param string $created_at Complaint creation datetime.
     * @return string|false SLA deadline datetime or false on error.
     */
    public static function calculate_deadline( $department, $created_at ) {
        // Get SLA days based on department
        $sla_days = self::get_sla_days( $department );

        if ( ! $sla_days ) {
            return false;
        }

        // Calculate deadline (creation date + SLA days)
        $deadline = date( 'Y-m-d H:i:s', strtotime( $created_at . ' +' . $sla_days . ' days' ) );

        return $deadline;
    }

    /**
     * Get SLA days for a department.
     *
     * @param string $department Department name.
     * @return int|false SLA days or false if invalid department.
     */
    public static function get_sla_days( $department ) {
        $sla_map = array(
            'health'      => get_option( 'egcp_sla_health', 7 ),
            'water'       => get_option( 'egcp_sla_water', 15 ),
            'electricity' => get_option( 'egcp_sla_electricity', 10 ),
        );

        return isset( $sla_map[ $department ] ) ? (int) $sla_map[ $department ] : false;
    }

    /**
     * Check if complaint is overdue.
     *
     * @param string $sla_deadline SLA deadline datetime.
     * @param string $status Current complaint status.
     * @return bool True if overdue, false otherwise.
     */
    public static function is_overdue( $sla_deadline, $status ) {
        // Resolved complaints are not overdue
        if ( 'resolved' === $status ) {
            return false;
        }

        // Compare deadline with current time
        $current_time = current_time( 'mysql' );
        return ( $sla_deadline < $current_time );
    }

    /**
     * Get remaining time until deadline.
     *
     * @param string $sla_deadline SLA deadline datetime.
     * @return array Array with days, hours, minutes remaining (can be negative if overdue).
     */
    public static function get_remaining_time( $sla_deadline ) {
        $current_time = current_time( 'timestamp' );
        $deadline     = strtotime( $sla_deadline );
        $difference   = $deadline - $current_time;

        $days    = floor( $difference / ( 60 * 60 * 24 ) );
        $hours   = floor( ( $difference % ( 60 * 60 * 24 ) ) / ( 60 * 60 ) );
        $minutes = floor( ( $difference % ( 60 * 60 ) ) / 60 );

        return array(
            'total_seconds' => $difference,
            'days'          => $days,
            'hours'         => $hours,
            'minutes'       => $minutes,
            'is_overdue'    => $difference < 0,
        );
    }

    /**
     * Format SLA status for display.
     *
     * @param string $sla_deadline SLA deadline datetime.
     * @param string $status Current complaint status.
     * @return string HTML formatted SLA status.
     */
    public static function format_sla_display( $sla_deadline, $status ) {
        if ( 'resolved' === $status ) {
            return '<span class="egcp-sla-badge egcp-sla-resolved">' . esc_html__( 'Resolved', 'e-governance-complaint-portal' ) . '</span>';
        }

        $remaining = self::get_remaining_time( $sla_deadline );

        if ( $remaining['is_overdue'] ) {
            $overdue_days = abs( $remaining['days'] );
            return sprintf(
                '<span class="egcp-sla-badge egcp-sla-overdue">%s</span>',
                sprintf(
                    esc_html__( 'Overdue by %d days', 'e-governance-complaint-portal' ),
                    $overdue_days
                )
            );
        } else {
            if ( $remaining['days'] > 0 ) {
                return sprintf(
                    '<span class="egcp-sla-badge egcp-sla-within">%s</span>',
                    sprintf(
                        esc_html__( '%d days remaining', 'e-governance-complaint-portal' ),
                        $remaining['days']
                    )
                );
            } else {
                return sprintf(
                    '<span class="egcp-sla-badge egcp-sla-urgent">%s</span>',
                    sprintf(
                        esc_html__( '%d hours remaining', 'e-governance-complaint-portal' ),
                        $remaining['hours']
                    )
                );
            }
        }
    }

    /**
     * Daily cron job to check and update overdue complaints.
     *
     * This runs automatically via WordPress cron.
     */
    public static function check_overdue_complaints() {
        global $wpdb;

        $table = $wpdb->prefix . 'egcp_complaints';

        // Find all complaints that are past deadline and not resolved
        $current_time = current_time( 'mysql' );

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE $table 
                SET sla_status = 'overdue' 
                WHERE sla_deadline < %s 
                AND status != 'resolved' 
                AND sla_status != 'overdue'",
                $current_time
            )
        );

        // Log the check
        error_log( 'EGCP: SLA overdue check completed at ' . $current_time );
    }

    /**
     * Get SLA statistics for dashboard.
     *
     * @param string $department Optional department filter.
     * @return array SLA statistics.
     */
    public static function get_sla_stats( $department = '' ) {
        global $wpdb;

        $table = $wpdb->prefix . 'egcp_complaints';

        $where = "status != 'resolved'";
        $params = array();

        if ( $department ) {
            $where .= " AND department = %s";
            $params[] = $department;
        }

        // Total active complaints
        $total = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE $where",
                $params
            )
        );

        // Within SLA
        $within_sla = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE $where AND sla_status = 'within_sla'",
                $params
            )
        );

        // Overdue
        $overdue = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE $where AND sla_status = 'overdue'",
                $params
            )
        );

        // Calculate compliance percentage
        $compliance = $total > 0 ? ( $within_sla / $total ) * 100 : 100;

        return array(
            'total'       => (int) $total,
            'within_sla'  => (int) $within_sla,
            'overdue'     => (int) $overdue,
            'compliance'  => round( $compliance, 2 ),
        );
    }

    /**
     * Extend SLA deadline (admin only).
     *
     * @param int    $complaint_id Complaint ID.
     * @param int    $additional_days Additional days to add.
     * @param string $reason Reason for extension.
     * @return bool|WP_Error
     */
    public static function extend_deadline( $complaint_id, $additional_days, $reason = '' ) {
        if ( ! current_user_can( 'manage_complaints' ) ) {
            return new WP_Error( 'permission_denied', __( 'You do not have permission to extend SLA.', 'e-governance-complaint-portal' ) );
        }

        global $wpdb;
        $table = $wpdb->prefix . 'egcp_complaints';

        // Get current deadline
        $complaint = $wpdb->get_row(
            $wpdb->prepare( "SELECT sla_deadline FROM $table WHERE id = %d", $complaint_id )
        );

        if ( ! $complaint ) {
            return new WP_Error( 'invalid_complaint', __( 'Complaint not found.', 'e-governance-complaint-portal' ) );
        }

        // Calculate new deadline
        $new_deadline = date( 'Y-m-d H:i:s', strtotime( $complaint->sla_deadline . ' +' . $additional_days . ' days' ) );

        // Update deadline
        $updated = $wpdb->update(
            $table,
            array(
                'sla_deadline' => $new_deadline,
                'sla_status'   => 'within_sla', // Reset to within SLA
            ),
            array( 'id' => $complaint_id ),
            array( '%s', '%s' ),
            array( '%d' )
        );

        if ( false === $updated ) {
            return new WP_Error( 'update_failed', __( 'Failed to extend SLA deadline.', 'e-governance-complaint-portal' ) );
        }

        // Log the extension in status history
        $history_table = $wpdb->prefix . 'egcp_status_history';
        $wpdb->insert(
            $history_table,
            array(
                'complaint_id' => $complaint_id,
                'changed_by'   => get_current_user_id(),
                'old_status'   => 'sla_extended',
                'new_status'   => 'sla_extended',
                'remarks'      => sprintf(
                    __( 'SLA extended by %d days. Reason: %s', 'e-governance-complaint-portal' ),
                    $additional_days,
                    $reason
                ),
            ),
            array( '%d', '%d', '%s', '%s', '%s' )
        );

        return true;
    }
}
<?php
/**
 * Admin Control Room for e-Governance Complaint Portal.
 *
 * Main admin dashboard with metrics, analytics, and complaint management.
 *
 * @package    E_Governance_Complaint_Portal
 * @subpackage Admin
 */

class EGCP_Control_Room {

    /**
     * Render the Control Room dashboard.
     */
    public function render() {
        // Handle AJAX actions
        $this->handle_actions();

        // Get dashboard statistics
        $stats = $this->get_dashboard_stats();

        // Get recent complaints
        $recent_complaints = $this->get_recent_complaints();

        // Include the view
        include EGCP_PLUGIN_DIR . 'admin/views/control-room.php';
    }

    /**
     * Get dashboard statistics.
     *
     * @return array Dashboard stats.
     */
    private function get_dashboard_stats() {
        global $wpdb;

        $table = $wpdb->prefix . 'egcp_complaints';

        // Total complaints
        $total = $wpdb->get_var( "SELECT COUNT(*) FROM $table" );

        // Pending review (status = pending)
        $pending = $wpdb->get_var(
            $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE status = %s", 'pending' )
        );

        // In progress
        $in_progress = $wpdb->get_var(
            $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE status = %s", 'in_progress' )
        );

        // Resolved
        $resolved = $wpdb->get_var(
            $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE status = %s", 'resolved' )
        );

        // Overdue (SLA breached)
        $overdue = $wpdb->get_var(
            $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE sla_status = %s AND status != %s", 'overdue', 'resolved' )
        );

        // Rejected
        $rejected = $wpdb->get_var(
            $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE status = %s", 'rejected' )
        );

        // Escalated
        $escalated = $wpdb->get_var(
            $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE status = %s", 'escalated' )
        );

        // Complaints by department
        $by_department = $wpdb->get_results(
            "SELECT department, COUNT(*) as count 
            FROM $table 
            GROUP BY department"
        );

        $dept_stats = array(
            'health'      => 0,
            'water'       => 0,
            'electricity' => 0,
        );

        foreach ( $by_department as $dept ) {
            if ( isset( $dept_stats[ $dept->department ] ) ) {
                $dept_stats[ $dept->department ] = (int) $dept->count;
            }
        }

        // Monthly trend (last 6 months)
        $monthly_trend = $wpdb->get_results(
            "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as count
            FROM $table
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY month
            ORDER BY month ASC"
        );

        // SLA compliance by department
        $sla_compliance = array();
        foreach ( array( 'health', 'water', 'electricity' ) as $dept ) {
            $sla_compliance[ $dept ] = EGCP_SLA::get_sla_stats( $dept );
        }

        return array(
            'total'          => (int) $total,
            'pending'        => (int) $pending,
            'in_progress'    => (int) $in_progress,
            'resolved'       => (int) $resolved,
            'overdue'        => (int) $overdue,
            'rejected'       => (int) $rejected,
            'escalated'      => (int) $escalated,
            'by_department'  => $dept_stats,
            'monthly_trend'  => $monthly_trend,
            'sla_compliance' => $sla_compliance,
        );
    }

    /**
     * Get recent complaints for quick view.
     *
     * @param int $limit Number of complaints to fetch.
     * @return array Recent complaints.
     */
    private function get_recent_complaints( $limit = 10 ) {
        $result = EGCP_Complaint::query( array(
            'per_page' => $limit,
            'page'     => 1,
            'orderby'  => 'created_at',
            'order'    => 'DESC',
        ) );

        return $result['complaints'];
    }

    /**
     * Handle admin actions (assign officer, update status, etc.).
     */
    private function handle_actions() {
        // Check if action is set
        if ( ! isset( $_POST['egcp_action'] ) ) {
            return;
        }

        // Verify nonce
        if ( ! isset( $_POST['egcp_admin_nonce'] ) || ! wp_verify_nonce( $_POST['egcp_admin_nonce'], 'egcp_admin_action' ) ) {
            wp_die( esc_html__( 'Security check failed.', 'e-governance-complaint-portal' ) );
        }

        $action = sanitize_text_field( $_POST['egcp_action'] );

        switch ( $action ) {
            case 'assign_officer':
                $this->assign_officer();
                break;

            case 'update_status':
                $this->update_status();
                break;

            case 'add_admin_reply':
                $this->add_admin_reply();
                break;

            case 'export_csv':
                $this->export_csv();
                break;
        }
    }

    /**
     * Assign complaint to an officer.
     */
    private function assign_officer() {
        if ( ! current_user_can( 'assign_complaints' ) ) {
            wp_die( esc_html__( 'You do not have permission to assign complaints.', 'e-governance-complaint-portal' ) );
        }

        $complaint_id = isset( $_POST['complaint_id'] ) ? (int) $_POST['complaint_id'] : 0;
        $officer_id   = isset( $_POST['officer_id'] ) ? (int) $_POST['officer_id'] : 0;

        if ( ! $complaint_id || ! $officer_id ) {
            $this->add_admin_notice( 'error', __( 'Invalid complaint or officer ID.', 'e-governance-complaint-portal' ) );
            return;
        }

        $result = EGCP_Complaint::update( $complaint_id, array(
            'assigned_officer' => $officer_id,
            'status'           => 'approved', // Auto-approve when assigning
            'remarks'          => __( 'Assigned to officer', 'e-governance-complaint-portal' ),
        ) );

        if ( is_wp_error( $result ) ) {
            $this->add_admin_notice( 'error', $result->get_error_message() );
        } else {
            $this->add_admin_notice( 'success', __( 'Officer assigned successfully.', 'e-governance-complaint-portal' ) );
        }
    }

    /**
     * Update complaint status.
     */
    private function update_status() {
        if ( ! current_user_can( 'manage_complaints' ) ) {
            wp_die( esc_html__( 'You do not have permission to update complaint status.', 'e-governance-complaint-portal' ) );
        }

        $complaint_id = isset( $_POST['complaint_id'] ) ? (int) $_POST['complaint_id'] : 0;
        $new_status   = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';
        $remarks      = isset( $_POST['remarks'] ) ? sanitize_textarea_field( $_POST['remarks'] ) : '';

        if ( ! $complaint_id || ! $new_status ) {
            $this->add_admin_notice( 'error', __( 'Invalid complaint ID or status.', 'e-governance-complaint-portal' ) );
            return;
        }

        $result = EGCP_Complaint::update( $complaint_id, array(
            'status'  => $new_status,
            'remarks' => $remarks,
        ) );

        if ( is_wp_error( $result ) ) {
            $this->add_admin_notice( 'error', $result->get_error_message() );
        } else {
            $this->add_admin_notice( 'success', __( 'Status updated successfully.', 'e-governance-complaint-portal' ) );
        }
    }

    /**
     * Add admin reply to complaint.
     */
    private function add_admin_reply() {
        if ( ! current_user_can( 'manage_complaints' ) ) {
            wp_die( esc_html__( 'You do not have permission to add admin replies.', 'e-governance-complaint-portal' ) );
        }

        $complaint_id = isset( $_POST['complaint_id'] ) ? (int) $_POST['complaint_id'] : 0;
        $message      = isset( $_POST['message'] ) ? sanitize_textarea_field( $_POST['message'] ) : '';

        if ( ! $complaint_id || empty( $message ) ) {
            $this->add_admin_notice( 'error', __( 'Invalid complaint ID or empty message.', 'e-governance-complaint-portal' ) );
            return;
        }

        $result = EGCP_Complaint::add_reply( $complaint_id, $message, 'admin' );

        if ( is_wp_error( $result ) ) {
            $this->add_admin_notice( 'error', $result->get_error_message() );
        } else {
            $this->add_admin_notice( 'success', __( 'Admin reply added successfully.', 'e-governance-complaint-portal' ) );
        }
    }

    /**
     * Export complaints to CSV.
     */
    private function export_csv() {
        if ( ! current_user_can( 'export_complaints' ) ) {
            wp_die( esc_html__( 'You do not have permission to export complaints.', 'e-governance-complaint-portal' ) );
        }

        global $wpdb;

        $table = $wpdb->prefix . 'egcp_complaints';

        // Build query with filters
        $where = '1=1';
        $params = array();

        if ( ! empty( $_POST['filter_department'] ) ) {
            $where .= ' AND department = %s';
            $params[] = sanitize_text_field( $_POST['filter_department'] );
        }

        if ( ! empty( $_POST['filter_status'] ) ) {
            $where .= ' AND status = %s';
            $params[] = sanitize_text_field( $_POST['filter_status'] );
        }

        if ( ! empty( $_POST['filter_date_from'] ) ) {
            $where .= ' AND DATE(created_at) >= %s';
            $params[] = sanitize_text_field( $_POST['filter_date_from'] );
        }

        if ( ! empty( $_POST['filter_date_to'] ) ) {
            $where .= ' AND DATE(created_at) <= %s';
            $params[] = sanitize_text_field( $_POST['filter_date_to'] );
        }

        $query = "SELECT * FROM $table WHERE $where ORDER BY created_at DESC";

        $complaints = empty( $params ) 
            ? $wpdb->get_results( $query ) 
            : $wpdb->get_results( $wpdb->prepare( $query, $params ) );

        // Generate CSV
        $filename = 'complaints_export_' . date( 'Y-m-d_H-i-s' ) . '.csv';

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );

        $output = fopen( 'php://output', 'w' );

        // CSV Headers
        fputcsv( $output, array(
            'Grievance ID',
            'Title',
            'Department',
            'Status',
            'Date Submitted',
            'Citizen Name',
            'Citizen Email',
            'Complaint Description',
            'State',
            'District',
            'Ward',
            'Pincode',
            'SLA Status',
            'Resolved Date',
        ) );

        // CSV Rows
        foreach ( $complaints as $complaint ) {
            $citizen = get_userdata( $complaint->citizen_id );

            $resolved_date = '';
            if ( 'resolved' === $complaint->status ) {
                $resolved_date = $complaint->updated_at;
            }

            fputcsv( $output, array(
                $complaint->grievance_id,
                $complaint->title,
                ucfirst( $complaint->department ),
                ucfirst( str_replace( '_', ' ', $complaint->status ) ),
                $complaint->created_at,
                $citizen ? $citizen->display_name : 'Unknown',
                $citizen ? $citizen->user_email : '',
                $complaint->description,
                $complaint->state,
                $complaint->district,
                $complaint->ward,
                $complaint->pincode,
                ucfirst( str_replace( '_', ' ', $complaint->sla_status ) ),
                $resolved_date,
            ) );
        }

        fclose( $output );
        exit;
    }

    /**
     * Add admin notice.
     *
     * @param string $type    Notice type (success|error|warning|info).
     * @param string $message Notice message.
     */
    private function add_admin_notice( $type, $message ) {
        set_transient( 'egcp_admin_notice', array(
            'type'    => $type,
            'message' => $message,
        ), 30 );
    }

    /**
     * Display admin notice.
     */
    public static function display_admin_notice() {
        $notice = get_transient( 'egcp_admin_notice' );

        if ( $notice ) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr( $notice['type'] ),
                esc_html( $notice['message'] )
            );

            delete_transient( 'egcp_admin_notice' );
        }
    }
}

// Hook to display admin notices
add_action( 'admin_notices', array( 'EGCP_Control_Room', 'display_admin_notice' ) );
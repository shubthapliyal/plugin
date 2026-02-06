<?php
/**
 * Officer Dashboard for e-Governance Complaint Portal.
 *
 * Department-specific dashboard for officers to manage complaints.
 *
 * @package    E_Governance_Complaint_Portal
 * @subpackage Admin
 */

class EGCP_Officer_Dashboard {

    /**
     * Current officer's user ID.
     *
     * @var int
     */
    private $officer_id;

    /**
     * Officer's departments.
     *
     * @var array
     */
    private $departments;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->officer_id  = get_current_user_id();
        $this->departments = EGCP_Roles::get_user_departments( $this->officer_id );
    }

    /**
     * Render the Officer Dashboard.
     */
    public function render() {
        // Handle AJAX actions
        $this->handle_actions();

        // Get dashboard statistics
        $stats = $this->get_officer_stats();

        // Get assigned complaints
        $complaints = $this->get_assigned_complaints();

        // Include the view
        include EGCP_PLUGIN_DIR . 'admin/views/officer-dashboard.php';
    }

    /**
     * Get officer-specific statistics.
     *
     * @return array Officer stats.
     */
    private function get_officer_stats() {
        global $wpdb;

        $table = $wpdb->prefix . 'egcp_complaints';

        $stats = array();

        foreach ( $this->departments as $dept ) {
            // Total assigned to this officer in this department
            $total = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $table WHERE department = %s AND assigned_officer = %d",
                    $dept,
                    $this->officer_id
                )
            );

            // Pending action (approved or in_progress)
            $pending_action = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $table 
                    WHERE department = %s 
                    AND assigned_officer = %d 
                    AND status IN ('approved', 'in_progress')",
                    $dept,
                    $this->officer_id
                )
            );

            // Overdue
            $overdue = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $table 
                    WHERE department = %s 
                    AND assigned_officer = %d 
                    AND sla_status = 'overdue' 
                    AND status != 'resolved'",
                    $dept,
                    $this->officer_id
                )
            );

            // Resolved this month
            $resolved_month = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $table 
                    WHERE department = %s 
                    AND assigned_officer = %d 
                    AND status = 'resolved' 
                    AND MONTH(updated_at) = MONTH(CURRENT_DATE()) 
                    AND YEAR(updated_at) = YEAR(CURRENT_DATE())",
                    $dept,
                    $this->officer_id
                )
            );

            // SLA compliance
            $sla_stats = EGCP_SLA::get_sla_stats( $dept );

            $stats[ $dept ] = array(
                'total'          => (int) $total,
                'pending_action' => (int) $pending_action,
                'overdue'        => (int) $overdue,
                'resolved_month' => (int) $resolved_month,
                'sla_compliance' => $sla_stats['compliance'],
            );
        }

        return $stats;
    }

    /**
     * Get complaints assigned to this officer.
     *
     * @return array Complaints.
     */
    private function get_assigned_complaints() {
        $page   = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
        $status = isset( $_GET['filter_status'] ) ? sanitize_text_field( $_GET['filter_status'] ) : '';

        $args = array(
            'officer_id' => $this->officer_id,
            'per_page'   => 20,
            'page'       => $page,
        );

        if ( $status ) {
            $args['status'] = $status;
        }

        // If officer has multiple departments, get complaints from all
        if ( count( $this->departments ) === 1 ) {
            $args['department'] = $this->departments[0];
        }

        return EGCP_Complaint::query( $args );
    }

    /**
     * Handle officer actions.
     */
    private function handle_actions() {
        // Check if action is set
        if ( ! isset( $_POST['egcp_officer_action'] ) ) {
            return;
        }

        // Verify nonce
        if ( ! isset( $_POST['egcp_officer_nonce'] ) || ! wp_verify_nonce( $_POST['egcp_officer_nonce'], 'egcp_officer_action' ) ) {
            wp_die( esc_html__( 'Security check failed.', 'e-governance-complaint-portal' ) );
        }

        $action = sanitize_text_field( $_POST['egcp_officer_action'] );

        switch ( $action ) {
            case 'update_status':
                $this->update_complaint_status();
                break;

            case 'add_reply':
                $this->add_officer_reply();
                break;
        }
    }

    /**
     * Update complaint status (officer).
     */
    private function update_complaint_status() {
        if ( ! current_user_can( 'update_complaint_status' ) ) {
            wp_die( esc_html__( 'You do not have permission to update complaint status.', 'e-governance-complaint-portal' ) );
        }

        $complaint_id = isset( $_POST['complaint_id'] ) ? (int) $_POST['complaint_id'] : 0;
        $new_status   = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';
        $remarks      = isset( $_POST['remarks'] ) ? sanitize_textarea_field( $_POST['remarks'] ) : '';

        if ( ! $complaint_id || ! $new_status ) {
            $this->add_officer_notice( 'error', __( 'Invalid complaint ID or status.', 'e-governance-complaint-portal' ) );
            return;
        }

        // Verify this complaint is assigned to this officer
        $complaint = EGCP_Complaint::get( $complaint_id );

        if ( ! $complaint || (int) $complaint->assigned_officer !== $this->officer_id ) {
            $this->add_officer_notice( 'error', __( 'You do not have permission to update this complaint.', 'e-governance-complaint-portal' ) );
            return;
        }

        // Officers can only set: in_progress, resolved, escalated
        $allowed_statuses = array( 'in_progress', 'resolved', 'escalated' );

        if ( ! in_array( $new_status, $allowed_statuses, true ) ) {
            $this->add_officer_notice( 'error', __( 'Invalid status. Officers can only set: In Progress, Resolved, or Escalated.', 'e-governance-complaint-portal' ) );
            return;
        }

        $result = EGCP_Complaint::update( $complaint_id, array(
            'status'  => $new_status,
            'remarks' => $remarks,
        ) );

        if ( is_wp_error( $result ) ) {
            $this->add_officer_notice( 'error', $result->get_error_message() );
        } else {
            $this->add_officer_notice( 'success', __( 'Status updated successfully.', 'e-governance-complaint-portal' ) );
        }
    }

    /**
     * Add officer reply to complaint.
     */
    private function add_officer_reply() {
        if ( ! current_user_can( 'reply_to_complaint' ) ) {
            wp_die( esc_html__( 'You do not have permission to add replies.', 'e-governance-complaint-portal' ) );
        }

        $complaint_id = isset( $_POST['complaint_id'] ) ? (int) $_POST['complaint_id'] : 0;
        $message      = isset( $_POST['message'] ) ? sanitize_textarea_field( $_POST['message'] ) : '';

        if ( ! $complaint_id || empty( $message ) ) {
            $this->add_officer_notice( 'error', __( 'Invalid complaint ID or empty message.', 'e-governance-complaint-portal' ) );
            return;
        }

        // Verify this complaint is assigned to this officer
        $complaint = EGCP_Complaint::get( $complaint_id );

        if ( ! $complaint || (int) $complaint->assigned_officer !== $this->officer_id ) {
            $this->add_officer_notice( 'error', __( 'You do not have permission to reply to this complaint.', 'e-governance-complaint-portal' ) );
            return;
        }

        $result = EGCP_Complaint::add_reply( $complaint_id, $message, 'officer' );

        if ( is_wp_error( $result ) ) {
            $this->add_officer_notice( 'error', $result->get_error_message() );
        } else {
            $this->add_officer_notice( 'success', __( 'Reply added successfully.', 'e-governance-complaint-portal' ) );
        }
    }

    /**
     * Add officer notice.
     *
     * @param string $type    Notice type.
     * @param string $message Notice message.
     */
    private function add_officer_notice( $type, $message ) {
        set_transient( 'egcp_officer_notice', array(
            'type'    => $type,
            'message' => $message,
        ), 30 );
    }

    /**
     * Display officer notice.
     */
    public static function display_officer_notice() {
        $notice = get_transient( 'egcp_officer_notice' );

        if ( $notice ) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr( $notice['type'] ),
                esc_html( $notice['message'] )
            );

            delete_transient( 'egcp_officer_notice' );
        }
    }
}

// Hook to display officer notices
add_action( 'admin_notices', array( 'EGCP_Officer_Dashboard', 'display_officer_notice' ) );
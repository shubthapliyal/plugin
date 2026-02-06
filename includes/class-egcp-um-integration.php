<?php
/**
 * Ultimate Member Integration for e-Governance Complaint Portal.
 *
 * Adds custom tabs to UM profile for complaint management.
 *
 * @package    E_Governance_Complaint_Portal
 * @subpackage Includes
 */

class EGCP_UM_Integration {

    /**
     * Constructor.
     */
    public function __construct() {
        // Add custom tabs to Ultimate Member
        add_filter( 'um_profile_tabs', array( $this, 'add_profile_tabs' ), 1000 );
        add_filter( 'um_user_profile_tabs', array( $this, 'add_profile_tabs' ), 1000 );

        // Register tab content
        add_action( 'um_profile_content_egcp_file_complaint', array( $this, 'tab_file_complaint' ) );
        add_action( 'um_profile_content_egcp_my_complaints', array( $this, 'tab_my_complaints' ) );
        add_action( 'um_profile_content_egcp_tracking', array( $this, 'tab_tracking' ) );
        add_action( 'um_profile_content_egcp_replies', array( $this, 'tab_replies' ) );
        add_action( 'um_profile_content_egcp_helpdesk', array( $this, 'tab_helpdesk' ) );
    }

    /**
     * Add custom tabs to UM profile.
     *
     * @param array $tabs Existing tabs.
     * @return array Modified tabs.
     */
    public function add_profile_tabs( $tabs ) {
        // Only show tabs on user's own profile
        if ( ! um_is_myprofile() ) {
            return $tabs;
        }

        $tabs['egcp_file_complaint'] = array(
            'name'   => __( 'File New Complaint', 'e-governance-complaint-portal' ),
            'icon'   => 'um-faicon-plus-circle',
            'custom' => true,
        );

        $tabs['egcp_my_complaints'] = array(
            'name'   => __( 'My Complaints', 'e-governance-complaint-portal' ),
            'icon'   => 'um-faicon-list',
            'custom' => true,
        );

        $tabs['egcp_tracking'] = array(
            'name'   => __( 'Complaint Tracking', 'e-governance-complaint-portal' ),
            'icon'   => 'um-faicon-search',
            'custom' => true,
        );

        $tabs['egcp_replies'] = array(
            'name'   => __( 'Officer Replies', 'e-governance-complaint-portal' ),
            'icon'   => 'um-faicon-comments',
            'custom' => true,
        );

        $tabs['egcp_helpdesk'] = array(
            'name'   => __( 'Helpdesk', 'e-governance-complaint-portal' ),
            'icon'   => 'um-faicon-question-circle',
            'custom' => true,
        );

        return $tabs;
    }

    /**
     * Tab: File New Complaint.
     */
    public function tab_file_complaint() {
        echo do_shortcode( '[egcp_complaint_form]' );
    }

    /**
     * Tab: My Complaints.
     */
    public function tab_my_complaints() {
        $user_id = um_profile_id();

        if ( ! $user_id ) {
            echo '<p>' . esc_html__( 'Error: Unable to load user data.', 'e-governance-complaint-portal' ) . '</p>';
            return;
        }

        $page   = isset( $_GET['complaint_page'] ) ? max( 1, (int) $_GET['complaint_page'] ) : 1;
        $status = isset( $_GET['filter_status'] ) ? sanitize_text_field( $_GET['filter_status'] ) : '';

        $args = array(
            'citizen_id' => $user_id,
            'per_page'   => 10,
            'page'       => $page,
        );

        if ( $status ) {
            $args['status'] = $status;
        }

        $result = EGCP_Complaint::query( $args );

        include EGCP_PLUGIN_DIR . 'public/views/my-complaints.php';
    }

    /**
     * Tab: Complaint Tracking.
     */
    public function tab_tracking() {
        echo do_shortcode( '[egcp_track_complaint]' );
    }

    /**
     * Tab: Officer Replies.
     */
    public function tab_replies() {
        $user_id = um_profile_id();

        if ( ! $user_id ) {
            echo '<p>' . esc_html__( 'Error: Unable to load user data.', 'e-governance-complaint-portal' ) . '</p>';
            return;
        }

        // Get all complaints with replies
        $result = EGCP_Complaint::query( array(
            'citizen_id' => $user_id,
            'per_page'   => 50,
            'page'       => 1,
        ) );

        $complaints_with_replies = array();

        foreach ( $result['complaints'] as $complaint ) {
            $replies = EGCP_Complaint::get_replies( $complaint->id );

            if ( ! empty( $replies ) ) {
                $complaints_with_replies[] = array(
                    'complaint' => $complaint,
                    'replies'   => $replies,
                );
            }
        }

        include EGCP_PLUGIN_DIR . 'public/views/replies.php';
    }

    /**
     * Tab: Helpdesk.
     */
    public function tab_helpdesk() {
        include EGCP_PLUGIN_DIR . 'public/views/helpdesk.php';
    }
}
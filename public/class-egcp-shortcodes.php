<?php
/**
 * Shortcodes for e-Governance Complaint Portal.
 *
 * Registers all public shortcodes.
 *
 * @package    E_Governance_Complaint_Portal
 * @subpackage Public
 */

class EGCP_Shortcodes {

    /**
     * Constructor.
     */
    public function __construct() {
        add_shortcode( 'egcp_complaint_form', array( $this, 'complaint_form' ) );
        add_shortcode( 'egcp_my_complaints', array( $this, 'my_complaints' ) );
        add_shortcode( 'egcp_track_complaint', array( $this, 'track_complaint' ) );

        // Register AJAX handlers
        add_action( 'wp_ajax_egcp_submit_complaint', array( $this, 'ajax_submit_complaint' ) );
        add_action( 'wp_ajax_egcp_upload_image', array( $this, 'ajax_upload_image' ) );
    }

    /**
     * Complaint submission form shortcode.
     *
     * Usage: [egcp_complaint_form]
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function complaint_form( $atts ) {
        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            return '<div class="egcp-notice egcp-notice-warning">' .
                   esc_html__( 'You must be logged in to submit a complaint.', 'e-governance-complaint-portal' ) .
                   ' <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">' . esc_html__( 'Log in', 'e-governance-complaint-portal' ) . '</a>' .
                   '</div>';
        }

        // Check if submissions are enabled
        if ( 'yes' !== get_option( 'egcp_enable_submissions', 'yes' ) ) {
            return '<div class="egcp-notice egcp-notice-error">' .
                   esc_html__( 'Complaint submissions are currently disabled.', 'e-governance-complaint-portal' ) .
                   '</div>';
        }

        ob_start();
        include EGCP_PLUGIN_DIR . 'public/views/complaint-form.php';
        return ob_get_clean();
    }

    /**
     * My complaints list shortcode.
     *
     * Usage: [egcp_my_complaints]
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function my_complaints( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<div class="egcp-notice egcp-notice-warning">' .
                   esc_html__( 'You must be logged in to view your complaints.', 'e-governance-complaint-portal' ) .
                   '</div>';
        }

        $atts = shortcode_atts( array(
            'per_page' => 10,
            'status'   => '',
        ), $atts );

        $citizen_id = get_current_user_id();

        $args = array(
            'citizen_id' => $citizen_id,
            'per_page'   => (int) $atts['per_page'],
            'page'       => isset( $_GET['complaint_page'] ) ? max( 1, (int) $_GET['complaint_page'] ) : 1,
        );

        if ( ! empty( $atts['status'] ) ) {
            $args['status'] = sanitize_text_field( $atts['status'] );
        }

        $result = EGCP_Complaint::query( $args );

        ob_start();
        include EGCP_PLUGIN_DIR . 'public/views/my-complaints.php';
        return ob_get_clean();
    }

    /**
     * Track complaint by Grievance ID shortcode.
     *
     * Usage: [egcp_track_complaint]
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function track_complaint( $atts ) {
        ob_start();

        $grievance_id = isset( $_GET['grievance_id'] ) ? sanitize_text_field( $_GET['grievance_id'] ) : '';

        if ( $grievance_id ) {
            $complaint = EGCP_Complaint::get_by_grievance_id( $grievance_id );

            if ( $complaint ) {
                // Check if user has permission to view
                $can_view = false;

                if ( is_user_logged_in() ) {
                    $user_id = get_current_user_id();
                    $can_view = (
                        $complaint->citizen_id == $user_id ||
                        current_user_can( 'manage_complaints' ) ||
                        EGCP_Roles::is_officer( $user_id )
                    );
                }

                if ( $can_view ) {
                    include EGCP_PLUGIN_DIR . 'public/views/complaint-details.php';
                } else {
                    echo '<div class="egcp-notice egcp-notice-error">' .
                         esc_html__( 'You do not have permission to view this complaint.', 'e-governance-complaint-portal' ) .
                         '</div>';
                }
            } else {
                echo '<div class="egcp-notice egcp-notice-error">' .
                     esc_html__( 'Complaint not found.', 'e-governance-complaint-portal' ) .
                     '</div>';
            }
        } else {
            // Show search form
            ?>
            <div class="egcp-track-form">
                <h3><?php esc_html_e( 'Track Your Complaint', 'e-governance-complaint-portal' ); ?></h3>
                <form method="get">
                    <label for="grievance_id"><?php esc_html_e( 'Enter Grievance ID:', 'e-governance-complaint-portal' ); ?></label>
                    <input type="text" name="grievance_id" id="grievance_id" placeholder="EGCP-HLTH-20260206-0001" required>
                    <button type="submit" class="egcp-btn egcp-btn-primary"><?php esc_html_e( 'Track', 'e-governance-complaint-portal' ); ?></button>
                </form>
            </div>
            <?php
        }

        return ob_get_clean();
    }

    /**
     * AJAX: Submit complaint.
     */
    public function ajax_submit_complaint() {
        // Verify nonce
        check_ajax_referer( 'egcp_public_nonce', 'nonce' );

        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array(
                'message' => __( 'You must be logged in to submit a complaint.', 'e-governance-complaint-portal' ),
            ) );
        }

        // Sanitize and validate input
        $data = array(
            'title'       => isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '',
            'description' => isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '',
            'department'  => isset( $_POST['department'] ) ? sanitize_text_field( $_POST['department'] ) : '',
            'state'       => isset( $_POST['state'] ) ? sanitize_text_field( $_POST['state'] ) : '',
            'district'    => isset( $_POST['district'] ) ? sanitize_text_field( $_POST['district'] ) : '',
            'ward'        => isset( $_POST['ward'] ) ? sanitize_text_field( $_POST['ward'] ) : '',
            'pincode'     => isset( $_POST['pincode'] ) ? sanitize_text_field( $_POST['pincode'] ) : '',
            'images'      => isset( $_POST['images'] ) ? array_map( 'esc_url_raw', $_POST['images'] ) : array(),
        );

        // Validate
        if ( strlen( $data['title'] ) < 10 || strlen( $data['title'] ) > 200 ) {
            wp_send_json_error( array(
                'message' => __( 'Title must be between 10 and 200 characters.', 'e-governance-complaint-portal' ),
            ) );
        }

        if ( strlen( $data['description'] ) < 50 ) {
            wp_send_json_error( array(
                'message' => __( 'Description must be at least 50 characters.', 'e-governance-complaint-portal' ),
            ) );
        }

        if ( ! in_array( $data['department'], array( 'health', 'water', 'electricity' ), true ) ) {
            wp_send_json_error( array(
                'message' => __( 'Invalid department selected.', 'e-governance-complaint-portal' ),
            ) );
        }

        if ( ! preg_match( '/^\d{6}$/', $data['pincode'] ) ) {
            wp_send_json_error( array(
                'message' => __( 'Pincode must be 6 digits.', 'e-governance-complaint-portal' ),
            ) );
        }

        // Create complaint
        $complaint_id = EGCP_Complaint::create( $data );

        if ( is_wp_error( $complaint_id ) ) {
            wp_send_json_error( array(
                'message' => $complaint_id->get_error_message(),
            ) );
        }

        // Get the created complaint
        $complaint = EGCP_Complaint::get( $complaint_id );

        wp_send_json_success( array(
            'message'      => __( 'Complaint submitted successfully!', 'e-governance-complaint-portal' ),
            'grievance_id' => $complaint->grievance_id,
            'complaint_id' => $complaint_id,
        ) );
    }

    /**
     * AJAX: Upload complaint image.
     */
    public function ajax_upload_image() {
        // Verify nonce
        check_ajax_referer( 'egcp_public_nonce', 'nonce' );

        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array(
                'message' => __( 'You must be logged in to upload images.', 'e-governance-complaint-portal' ),
            ) );
        }

        // Check if file was uploaded
        if ( ! isset( $_FILES['file'] ) ) {
            wp_send_json_error( array(
                'message' => __( 'No file uploaded.', 'e-governance-complaint-portal' ),
            ) );
        }

        // Validate file
        $allowed_types = array( 'image/jpeg', 'image/jpg', 'image/png', 'image/webp' );
        $file_type     = $_FILES['file']['type'];

        if ( ! in_array( $file_type, $allowed_types, true ) ) {
            wp_send_json_error( array(
                'message' => __( 'Invalid file type. Only JPG, PNG, and WebP are allowed.', 'e-governance-complaint-portal' ),
            ) );
        }

        $max_size = get_option( 'egcp_max_file_size', 2097152 ); // 2MB default

        if ( $_FILES['file']['size'] > $max_size ) {
            wp_send_json_error( array(
                'message' => sprintf( __( 'File size exceeds %s MB.', 'e-governance-complaint-portal' ), $max_size / 1048576 ),
            ) );
        }

        // Handle upload
        require_once ABSPATH . 'wp-admin/includes/file.php';

        $upload_overrides = array(
            'test_form' => false,
        );

        $uploaded_file = wp_handle_upload( $_FILES['file'], $upload_overrides );

        if ( isset( $uploaded_file['error'] ) ) {
            wp_send_json_error( array(
                'message' => $uploaded_file['error'],
            ) );
        }

        wp_send_json_success( array(
            'url' => $uploaded_file['url'],
        ) );
    }
}
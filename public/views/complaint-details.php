<?php
/**
 * Complaint Details View.
 *
 * Displays detailed information about a specific complaint.
 *
 * @package    E_Governance_Complaint_Portal
 * @subpackage Public/Views
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$citizen = get_userdata( $complaint->citizen_id );
$replies = EGCP_Complaint::get_replies( $complaint->id );
$history = EGCP_Complaint::get_status_history( $complaint->id );

$status_labels = array(
    'pending'      => __( 'Pending Review', 'e-governance-complaint-portal' ),
    'approved'     => __( 'Approved', 'e-governance-complaint-portal' ),
    'in_progress'  => __( 'In Progress', 'e-governance-complaint-portal' ),
    'resolved'     => __( 'Resolved', 'e-governance-complaint-portal' ),
    'rejected'     => __( 'Rejected', 'e-governance-complaint-portal' ),
    'escalated'    => __( 'Escalated', 'e-governance-complaint-portal' ),
);

$dept_labels = array(
    'health'      => __( 'Health Department', 'e-governance-complaint-portal' ),
    'water'       => __( 'Water Department', 'e-governance-complaint-portal' ),
    'electricity' => __( 'Electricity Department', 'e-governance-complaint-portal' ),
);
?>

<div class="egcp-complaint-details-wrapper">
    <div class="egcp-details-header">
        <h2><?php esc_html_e( 'Complaint Details', 'e-governance-complaint-portal' ); ?></h2>
        <p class="egcp-grievance-id-display">
            <?php esc_html_e( 'Grievance ID:', 'e-governance-complaint-portal' ); ?>
            <strong><?php echo esc_html( $complaint->grievance_id ); ?></strong>
        </p>
    </div>

    <div class="egcp-details-grid">
        <!-- Main Information -->
        <div class="egcp-details-main">
            <div class="egcp-info-card">
                <h3><?php echo esc_html( $complaint->title ); ?></h3>
                
                <div class="egcp-info-row">
                    <span class="egcp-info-label"><?php esc_html_e( 'Status:', 'e-governance-complaint-portal' ); ?></span>
                    <span class="egcp-status-badge egcp-status-<?php echo esc_attr( $complaint->status ); ?>">
                        <?php echo esc_html( $status_labels[ $complaint->status ] ); ?>
                    </span>
                </div>

                <div class="egcp-info-row">
                    <span class="egcp-info-label"><?php esc_html_e( 'Department:', 'e-governance-complaint-portal' ); ?></span>
                    <span><?php echo esc_html( $dept_labels[ $complaint->department ] ); ?></span>
                </div>

                <div class="egcp-info-row">
                    <span class="egcp-info-label"><?php esc_html_e( 'SLA Status:', 'e-governance-complaint-portal' ); ?></span>
                    <?php echo EGCP_SLA::format_sla_display( $complaint->sla_deadline, $complaint->status ); ?>
                </div>

                <div class="egcp-info-row">
                    <span class="egcp-info-label"><?php esc_html_e( 'Submitted:', 'e-governance-complaint-portal' ); ?></span>
                    <span><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $complaint->created_at ) ) ); ?></span>
                </div>

                <hr>

                <h4><?php esc_html_e( 'Description', 'e-governance-complaint-portal' ); ?></h4>
                <p><?php echo esc_html( $complaint->description ); ?></p>

                <h4><?php esc_html_e( 'Location', 'e-governance-complaint-portal' ); ?></h4>
                <p>
                    <?php echo esc_html( $complaint->district . ', ' . $complaint->state ); ?>
                    <?php if ( $complaint->ward ) : ?>
                        <br><?php esc_html_e( 'Ward:', 'e-governance-complaint-portal' ); ?> <?php echo esc_html( $complaint->ward ); ?>
                    <?php endif; ?>
                    <br><?php esc_html_e( 'Pincode:', 'e-governance-complaint-portal' ); ?> <?php echo esc_html( $complaint->pincode ); ?>
                </p>

                <?php if ( $complaint->images && is_array( $complaint->images ) && ! empty( $complaint->images ) ) : ?>
                    <h4><?php esc_html_e( 'Attached Images', 'e-governance-complaint-portal' ); ?></h4>
                    <div class="egcp-complaint-images">
                        <?php foreach ( $complaint->images as $image_url ) : ?>
                            <a href="<?php echo esc_url( $image_url ); ?>" target="_blank">
                                <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php esc_attr_e( 'Complaint Image', 'e-governance-complaint-portal' ); ?>">
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="egcp-details-sidebar">
            <!-- Replies -->
            <?php if ( ! empty( $replies ) ) : ?>
                <div class="egcp-info-card">
                    <h4><?php esc_html_e( 'Official Responses', 'e-governance-complaint-portal' ); ?></h4>
                    <?php foreach ( $replies as $reply ) : ?>
                        <div class="egcp-reply-item egcp-reply-<?php echo esc_attr( $reply->reply_type ); ?>">
                            <div class="egcp-reply-header">
                                <strong>
                                    <?php 
                                    if ( 'admin' === $reply->reply_type ) {
                                        esc_html_e( 'Official Reply', 'e-governance-complaint-portal' );
                                    } else {
                                        esc_html_e( 'Officer Reply', 'e-governance-complaint-portal' );
                                    }
                                    ?>
                                </strong>
                                <span class="egcp-reply-date">
                                    <?php echo esc_html( date_i18n( 'M j, Y', strtotime( $reply->created_at ) ) ); ?>
                                </span>
                            </div>
                            <p><?php echo esc_html( $reply->message ); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Status History -->
            <?php if ( ! empty( $history ) ) : ?>
                <div class="egcp-info-card">
                    <h4><?php esc_html_e( 'Timeline', 'e-governance-complaint-portal' ); ?></h4>
                    <div class="egcp-timeline">
                        <?php foreach ( $history as $entry ) : ?>
                            <div class="egcp-timeline-item">
                                <div class="egcp-timeline-date">
                                    <?php echo esc_html( date_i18n( 'M j', strtotime( $entry->created_at ) ) ); ?>
                                </div>
                                <div class="egcp-timeline-content">
                                    <strong>
                                        <?php echo esc_html( ucfirst( str_replace( '_', ' ', $entry->new_status ) ) ); ?>
                                    </strong>
                                    <?php if ( $entry->remarks ) : ?>
                                        <p><?php echo esc_html( $entry->remarks ); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
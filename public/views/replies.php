<?php
/**
 * Officer Replies View.
 *
 * Displays all complaints with officer/admin replies for the citizen.
 *
 * @package    E_Governance_Complaint_Portal
 * @subpackage Public/Views
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="egcp-replies-wrapper">
    <div class="egcp-page-header">
        <h2><?php esc_html_e( 'Officer Replies', 'e-governance-complaint-portal' ); ?></h2>
        <p><?php esc_html_e( 'View all responses to your complaints.', 'e-governance-complaint-portal' ); ?></p>
    </div>

    <?php if ( ! empty( $complaints_with_replies ) ) : ?>
        <div class="egcp-replies-list">
            <?php foreach ( $complaints_with_replies as $item ) : ?>
                <?php $complaint = $item['complaint']; ?>
                <?php $replies = $item['replies']; ?>

                <div class="egcp-reply-card">
                    <div class="egcp-reply-card-header">
                        <h3><?php echo esc_html( $complaint->title ); ?></h3>
                        <span class="egcp-grievance-id"><?php echo esc_html( $complaint->grievance_id ); ?></span>
                    </div>

                    <div class="egcp-reply-card-body">
                        <?php foreach ( $replies as $reply ) : ?>
                            <?php $reply_user = get_userdata( $reply->user_id ); ?>
                            <div class="egcp-reply-message egcp-reply-<?php echo esc_attr( $reply->reply_type ); ?>">
                                <div class="egcp-reply-meta">
                                    <span class="egcp-reply-type-badge">
                                        <?php 
                                        if ( 'admin' === $reply->reply_type ) {
                                            esc_html_e( 'Official Reply', 'e-governance-complaint-portal' );
                                        } else {
                                            esc_html_e( 'Officer Reply', 'e-governance-complaint-portal' );
                                        }
                                        ?>
                                    </span>
                                    <span class="egcp-reply-date">
                                        <?php echo esc_html( date_i18n( 'M j, Y g:i A', strtotime( $reply->created_at ) ) ); ?>
                                    </span>
                                </div>
                                <p class="egcp-reply-text"><?php echo esc_html( $reply->message ); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="egcp-notice egcp-notice-info">
            <p><?php esc_html_e( 'No replies yet. When an officer or admin responds to your complaints, they will appear here.', 'e-governance-complaint-portal' ); ?></p>
        </div>
    <?php endif; ?>
</div>
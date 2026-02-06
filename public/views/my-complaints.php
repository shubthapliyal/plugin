<?php
/**
 * My Complaints View.
 *
 * Displays citizen's submitted complaints with status and replies.
 *
 * @package    E_Governance_Complaint_Portal
 * @subpackage Public/Views
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Status labels
$status_labels = array(
    'pending'      => __( 'Pending Review', 'e-governance-complaint-portal' ),
    'approved'     => __( 'Approved', 'e-governance-complaint-portal' ),
    'in_progress'  => __( 'In Progress', 'e-governance-complaint-portal' ),
    'resolved'     => __( 'Resolved', 'e-governance-complaint-portal' ),
    'rejected'     => __( 'Rejected', 'e-governance-complaint-portal' ),
    'escalated'    => __( 'Escalated', 'e-governance-complaint-portal' ),
);

$dept_labels = array(
    'health'      => __( 'Health', 'e-governance-complaint-portal' ),
    'water'       => __( 'Water', 'e-governance-complaint-portal' ),
    'electricity' => __( 'Electricity', 'e-governance-complaint-portal' ),
);
?>

<div class="egcp-my-complaints-wrapper">
    <div class="egcp-page-header">
        <h2><?php esc_html_e( 'My Complaints', 'e-governance-complaint-portal' ); ?></h2>
        <p><?php esc_html_e( 'View and track all your submitted complaints.', 'e-governance-complaint-portal' ); ?></p>
    </div>

    <!-- Filter Bar -->
    <div class="egcp-filter-bar">
        <form method="get">
            <?php if ( isset( $_GET['page'] ) ) : ?>
                <input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>">
            <?php endif; ?>

            <label for="filter_status"><?php esc_html_e( 'Filter by Status:', 'e-governance-complaint-portal' ); ?></label>
            <select name="filter_status" id="filter_status" onchange="this.form.submit()">
                <option value=""><?php esc_html_e( 'All Statuses', 'e-governance-complaint-portal' ); ?></option>
                <?php foreach ( $status_labels as $status => $label ) : ?>
                    <option value="<?php echo esc_attr( $status ); ?>" <?php selected( isset( $_GET['filter_status'] ) ? $_GET['filter_status'] : '', $status ); ?>>
                        <?php echo esc_html( $label ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- Complaints List -->
    <?php if ( ! empty( $result['complaints'] ) ) : ?>
        <div class="egcp-complaints-list">
            <?php foreach ( $result['complaints'] as $complaint ) : ?>
                <div class="egcp-complaint-card">
                    <div class="egcp-complaint-header">
                        <div class="egcp-complaint-id">
                            <strong><?php esc_html_e( 'Grievance ID:', 'e-governance-complaint-portal' ); ?></strong>
                            <span class="egcp-grievance-id"><?php echo esc_html( $complaint->grievance_id ); ?></span>
                        </div>
                        <div class="egcp-complaint-date">
                            <?php echo esc_html( date_i18n( 'M j, Y', strtotime( $complaint->created_at ) ) ); ?>
                        </div>
                    </div>

                    <h3 class="egcp-complaint-title"><?php echo esc_html( $complaint->title ); ?></h3>

                    <div class="egcp-complaint-meta">
                        <span class="egcp-meta-item">
                            <strong><?php esc_html_e( 'Department:', 'e-governance-complaint-portal' ); ?></strong>
                            <?php echo esc_html( $dept_labels[ $complaint->department ] ); ?>
                        </span>
                        <span class="egcp-meta-item">
                            <strong><?php esc_html_e( 'Location:', 'e-governance-complaint-portal' ); ?></strong>
                            <?php echo esc_html( $complaint->district . ', ' . $complaint->state ); ?>
                        </span>
                    </div>

                    <div class="egcp-complaint-status-row">
                        <div class="egcp-status-item">
                            <strong><?php esc_html_e( 'Status:', 'e-governance-complaint-portal' ); ?></strong>
                            <span class="egcp-status-badge egcp-status-<?php echo esc_attr( $complaint->status ); ?>">
                                <?php echo esc_html( $status_labels[ $complaint->status ] ); ?>
                            </span>
                        </div>

                        <div class="egcp-sla-item">
                            <strong><?php esc_html_e( 'SLA Status:', 'e-governance-complaint-portal' ); ?></strong>
                            <?php echo EGCP_SLA::format_sla_display( $complaint->sla_deadline, $complaint->status ); ?>
                        </div>
                    </div>

                    <!-- Replies Section -->
                    <?php
                    $replies = EGCP_Complaint::get_replies( $complaint->id );
                    if ( ! empty( $replies ) ) :
                    ?>
                        <div class="egcp-complaint-replies">
                            <h4><?php esc_html_e( 'Responses', 'e-governance-complaint-portal' ); ?></h4>
                            <?php foreach ( $replies as $reply ) : ?>
                                <?php $reply_user = get_userdata( $reply->user_id ); ?>
                                <div class="egcp-reply-item egcp-reply-<?php echo esc_attr( $reply->reply_type ); ?>">
                                    <div class="egcp-reply-header">
                                        <span class="egcp-reply-type">
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
                                    <p class="egcp-reply-message"><?php echo esc_html( $reply->message ); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="egcp-complaint-actions">
                        <button type="button" class="egcp-btn egcp-btn-small egcp-toggle-details" data-complaint-id="<?php echo esc_attr( $complaint->id ); ?>">
                            <?php esc_html_e( 'View Full Details', 'e-governance-complaint-portal' ); ?>
                        </button>
                    </div>

                    <!-- Hidden Details Section -->
                    <div class="egcp-complaint-full-details" id="details-<?php echo esc_attr( $complaint->id ); ?>" style="display: none;">
                        <div class="egcp-details-content">
                            <h4><?php esc_html_e( 'Full Description', 'e-governance-complaint-portal' ); ?></h4>
                            <p><?php echo esc_html( $complaint->description ); ?></p>

                            <?php if ( $complaint->ward ) : ?>
                                <p><strong><?php esc_html_e( 'Ward/Area:', 'e-governance-complaint-portal' ); ?></strong> <?php echo esc_html( $complaint->ward ); ?></p>
                            <?php endif; ?>

                            <p><strong><?php esc_html_e( 'Pincode:', 'e-governance-complaint-portal' ); ?></strong> <?php echo esc_html( $complaint->pincode ); ?></p>

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

                            <!-- Status History -->
                            <?php
                            $history = EGCP_Complaint::get_status_history( $complaint->id );
                            if ( ! empty( $history ) ) :
                            ?>
                                <h4><?php esc_html_e( 'Status History', 'e-governance-complaint-portal' ); ?></h4>
                                <table class="egcp-status-history">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e( 'Date', 'e-governance-complaint-portal' ); ?></th>
                                            <th><?php esc_html_e( 'Status Change', 'e-governance-complaint-portal' ); ?></th>
                                            <th><?php esc_html_e( 'Remarks', 'e-governance-complaint-portal' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ( $history as $entry ) : ?>
                                            <tr>
                                                <td><?php echo esc_html( date_i18n( 'M j, Y g:i A', strtotime( $entry->created_at ) ) ); ?></td>
                                                <td>
                                                    <?php
                                                    if ( $entry->old_status ) {
                                                        echo esc_html( ucfirst( str_replace( '_', ' ', $entry->old_status ) ) );
                                                        echo ' → ';
                                                    }
                                                    echo esc_html( ucfirst( str_replace( '_', ' ', $entry->new_status ) ) );
                                                    ?>
                                                </td>
                                                <td><?php echo esc_html( $entry->remarks ); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ( $result['pages'] > 1 ) : ?>
            <div class="egcp-pagination">
                <?php
                $current_page = isset( $_GET['complaint_page'] ) ? max( 1, (int) $_GET['complaint_page'] ) : 1;
                $base_url = remove_query_arg( 'complaint_page' );

                for ( $i = 1; $i <= $result['pages']; $i++ ) {
                    $class = ( $i === $current_page ) ? 'egcp-page-current' : '';
                    printf(
                        '<a href="%s" class="egcp-page-link %s">%d</a>',
                        esc_url( add_query_arg( 'complaint_page', $i, $base_url ) ),
                        esc_attr( $class ),
                        $i
                    );
                }
                ?>
            </div>
        <?php endif; ?>

    <?php else : ?>
        <div class="egcp-notice egcp-notice-info">
            <p><?php esc_html_e( 'You haven\'t submitted any complaints yet.', 'e-governance-complaint-portal' ); ?></p>
            <p>
                <a href="<?php echo esc_url( add_query_arg( 'um_tab', 'egcp_file_complaint' ) ); ?>" class="egcp-btn egcp-btn-primary">
                    <?php esc_html_e( 'File Your First Complaint', 'e-governance-complaint-portal' ); ?>
                </a>
            </p>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.egcp-toggle-details');
    
    toggleButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const complaintId = this.getAttribute('data-complaint-id');
            const detailsDiv = document.getElementById('details-' + complaintId);
            
            if (detailsDiv.style.display === 'none') {
                detailsDiv.style.display = 'block';
                this.textContent = '<?php esc_html_e( 'Hide Details', 'e-governance-complaint-portal' ); ?>';
            } else {
                detailsDiv.style.display = 'none';
                this.textContent = '<?php esc_html_e( 'View Full Details', 'e-governance-complaint-portal' ); ?>';
            }
        });
    });
});
</script>
<?php
/**
 * Officer Dashboard View.
 *
 * Department-specific dashboard for officers.
 *
 * @package    E_Governance_Complaint_Portal
 * @subpackage Admin/Views
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$dept_labels = array(
    'health'      => __( 'Health Department', 'e-governance-complaint-portal' ),
    'water'       => __( 'Water Department', 'e-governance-complaint-portal' ),
    'electricity' => __( 'Electricity Department', 'e-governance-complaint-portal' ),
);
?>

<div class="wrap egcp-officer-dashboard">
    <h1 class="wp-heading-inline">
        <?php esc_html_e( 'Department Officer Dashboard', 'e-governance-complaint-portal' ); ?>
    </h1>

    <hr class="wp-header-end">

    <!-- Department Stats -->
    <?php foreach ( $this->departments as $dept ) : ?>
        <?php if ( isset( $stats[ $dept ] ) ) : ?>
            <div class="egcp-dept-section">
                <h2><?php echo esc_html( $dept_labels[ $dept ] ); ?></h2>

                <div class="egcp-officer-stats">
                    <div class="egcp-stat-card">
                        <h3><?php echo esc_html( number_format( $stats[ $dept ]['total'] ) ); ?></h3>
                        <p><?php esc_html_e( 'Total Assigned', 'e-governance-complaint-portal' ); ?></p>
                    </div>

                    <div class="egcp-stat-card egcp-stat-pending">
                        <h3><?php echo esc_html( number_format( $stats[ $dept ]['pending_action'] ) ); ?></h3>
                        <p><?php esc_html_e( 'Pending Action', 'e-governance-complaint-portal' ); ?></p>
                    </div>

                    <div class="egcp-stat-card egcp-stat-overdue">
                        <h3><?php echo esc_html( number_format( $stats[ $dept ]['overdue'] ) ); ?></h3>
                        <p><?php esc_html_e( 'Overdue', 'e-governance-complaint-portal' ); ?></p>
                    </div>

                    <div class="egcp-stat-card egcp-stat-resolved">
                        <h3><?php echo esc_html( number_format( $stats[ $dept ]['resolved_month'] ) ); ?></h3>
                        <p><?php esc_html_e( 'Resolved This Month', 'e-governance-complaint-portal' ); ?></p>
                    </div>

                    <div class="egcp-stat-card egcp-stat-sla">
                        <h3><?php echo esc_html( $stats[ $dept ]['sla_compliance'] ); ?>%</h3>
                        <p><?php esc_html_e( 'SLA Compliance', 'e-governance-complaint-portal' ); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- Filter Bar -->
    <div class="egcp-filter-bar">
        <form method="get">
            <input type="hidden" name="page" value="egcp-officer-dashboard">

            <select name="filter_status" onchange="this.form.submit()">
                <option value=""><?php esc_html_e( 'All Statuses', 'e-governance-complaint-portal' ); ?></option>
                <option value="approved" <?php selected( isset( $_GET['filter_status'] ) ? $_GET['filter_status'] : '', 'approved' ); ?>>
                    <?php esc_html_e( 'Approved', 'e-governance-complaint-portal' ); ?>
                </option>
                <option value="in_progress" <?php selected( isset( $_GET['filter_status'] ) ? $_GET['filter_status'] : '', 'in_progress' ); ?>>
                    <?php esc_html_e( 'In Progress', 'e-governance-complaint-portal' ); ?>
                </option>
                <option value="resolved" <?php selected( isset( $_GET['filter_status'] ) ? $_GET['filter_status'] : '', 'resolved' ); ?>>
                    <?php esc_html_e( 'Resolved', 'e-governance-complaint-portal' ); ?>
                </option>
                <option value="escalated" <?php selected( isset( $_GET['filter_status'] ) ? $_GET['filter_status'] : '', 'escalated' ); ?>>
                    <?php esc_html_e( 'Escalated', 'e-governance-complaint-portal' ); ?>
                </option>
            </select>
        </form>
    </div>

    <!-- Complaints Table -->
    <div class="egcp-section">
        <h2><?php esc_html_e( 'Assigned Complaints', 'e-governance-complaint-portal' ); ?></h2>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Grievance ID', 'e-governance-complaint-portal' ); ?></th>
                    <th><?php esc_html_e( 'Title', 'e-governance-complaint-portal' ); ?></th>
                    <th><?php esc_html_e( 'Location', 'e-governance-complaint-portal' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'e-governance-complaint-portal' ); ?></th>
                    <th><?php esc_html_e( 'SLA Timer', 'e-governance-complaint-portal' ); ?></th>
                    <th><?php esc_html_e( 'Submitted', 'e-governance-complaint-portal' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'e-governance-complaint-portal' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $complaints['complaints'] ) ) : ?>
                    <?php foreach ( $complaints['complaints'] as $complaint ) : ?>
                        <tr>
                            <td><strong><?php echo esc_html( $complaint->grievance_id ); ?></strong></td>
                            <td><?php echo esc_html( wp_trim_words( $complaint->title, 10 ) ); ?></td>
                            <td><?php echo esc_html( $complaint->district . ', ' . $complaint->state ); ?></td>
                            <td>
                                <span class="egcp-status-badge egcp-status-<?php echo esc_attr( $complaint->status ); ?>">
                                    <?php echo esc_html( ucfirst( str_replace( '_', ' ', $complaint->status ) ) ); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo EGCP_SLA::format_sla_display( $complaint->sla_deadline, $complaint->status ); ?>
                            </td>
                            <td><?php echo esc_html( date_i18n( 'M j, Y', strtotime( $complaint->created_at ) ) ); ?></td>
                            <td>
                                <button type="button" class="button button-small egcp-view-complaint" data-complaint-id="<?php echo esc_attr( $complaint->id ); ?>">
                                    <?php esc_html_e( 'View Details', 'e-governance-complaint-portal' ); ?>
                                </button>
                            </td>
                        </tr>

                        <!-- Expandable Details Row -->
                        <tr class="egcp-complaint-details" id="complaint-details-<?php echo esc_attr( $complaint->id ); ?>" style="display: none;">
                            <td colspan="7">
                                <div class="egcp-details-container">
                                    <div class="egcp-details-main">
                                        <h3><?php echo esc_html( $complaint->title ); ?></h3>
                                        <p><strong><?php esc_html_e( 'Description:', 'e-governance-complaint-portal' ); ?></strong></p>
                                        <p><?php echo esc_html( $complaint->description ); ?></p>

                                        <?php if ( $complaint->images ) : ?>
                                            <p><strong><?php esc_html_e( 'Attachments:', 'e-governance-complaint-portal' ); ?></strong></p>
                                            <div class="egcp-complaint-images">
                                                <?php foreach ( $complaint->images as $image_url ) : ?>
                                                    <a href="<?php echo esc_url( $image_url ); ?>" target="_blank">
                                                        <img src="<?php echo esc_url( $image_url ); ?>" alt="" style="max-width: 100px; margin-right: 10px;">
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Replies Section -->
                                        <?php
                                        $replies = EGCP_Complaint::get_replies( $complaint->id );
                                        if ( ! empty( $replies ) ) :
                                        ?>
                                            <h4><?php esc_html_e( 'Conversation History', 'e-governance-complaint-portal' ); ?></h4>
                                            <div class="egcp-replies">
                                                <?php foreach ( $replies as $reply ) : ?>
                                                    <?php $reply_user = get_userdata( $reply->user_id ); ?>
                                                    <div class="egcp-reply egcp-reply-<?php echo esc_attr( $reply->reply_type ); ?>">
                                                        <strong>
                                                            <?php echo esc_html( $reply_user->display_name ); ?>
                                                            (<?php echo esc_html( ucfirst( $reply->reply_type ) ); ?>)
                                                        </strong>
                                                        <span class="egcp-reply-date"><?php echo esc_html( date_i18n( 'M j, Y g:i A', strtotime( $reply->created_at ) ) ); ?></span>
                                                        <p><?php echo esc_html( $reply->message ); ?></p>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Action Forms -->
                                    <div class="egcp-details-actions">
                                        <!-- Update Status Form -->
                                        <div class="egcp-action-box">
                                            <h4><?php esc_html_e( 'Update Status', 'e-governance-complaint-portal' ); ?></h4>
                                            <form method="post">
                                                <?php wp_nonce_field( 'egcp_officer_action', 'egcp_officer_nonce' ); ?>
                                                <input type="hidden" name="egcp_officer_action" value="update_status">
                                                <input type="hidden" name="complaint_id" value="<?php echo esc_attr( $complaint->id ); ?>">

                                                <p>
                                                    <label><?php esc_html_e( 'New Status:', 'e-governance-complaint-portal' ); ?></label>
                                                    <select name="status" required>
                                                        <option value="in_progress"><?php esc_html_e( 'In Progress', 'e-governance-complaint-portal' ); ?></option>
                                                        <option value="resolved"><?php esc_html_e( 'Resolved', 'e-governance-complaint-portal' ); ?></option>
                                                        <option value="escalated"><?php esc_html_e( 'Escalated', 'e-governance-complaint-portal' ); ?></option>
                                                    </select>
                                                </p>

                                                <p>
                                                    <label><?php esc_html_e( 'Remarks:', 'e-governance-complaint-portal' ); ?></label>
                                                    <textarea name="remarks" rows="3"></textarea>
                                                </p>

                                                <button type="submit" class="button button-primary">
                                                    <?php esc_html_e( 'Update Status', 'e-governance-complaint-portal' ); ?>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Add Reply Form -->
                                        <div class="egcp-action-box">
                                            <h4><?php esc_html_e( 'Add Officer Reply', 'e-governance-complaint-portal' ); ?></h4>
                                            <form method="post">
                                                <?php wp_nonce_field( 'egcp_officer_action', 'egcp_officer_nonce' ); ?>
                                                <input type="hidden" name="egcp_officer_action" value="add_reply">
                                                <input type="hidden" name="complaint_id" value="<?php echo esc_attr( $complaint->id ); ?>">

                                                <p>
                                                    <label><?php esc_html_e( 'Message:', 'e-governance-complaint-portal' ); ?></label>
                                                    <textarea name="message" rows="4" required placeholder="<?php esc_attr_e( 'Internal/technical notes for this complaint...', 'e-governance-complaint-portal' ); ?>"></textarea>
                                                </p>

                                                <button type="submit" class="button button-primary">
                                                    <?php esc_html_e( 'Add Reply', 'e-governance-complaint-portal' ); ?>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">
                            <?php esc_html_e( 'No complaints assigned to you yet.', 'e-governance-complaint-portal' ); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ( $complaints['pages'] > 1 ) : ?>
            <div class="tablenav">
                <div class="tablenav-pages">
                    <?php
                    $current_page = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;

                    echo paginate_links( array(
                        'base'      => add_query_arg( 'paged', '%#%' ),
                        'format'    => '',
                        'prev_text' => __( '&laquo;', 'e-governance-complaint-portal' ),
                        'next_text' => __( '&raquo;', 'e-governance-complaint-portal' ),
                        'total'     => $complaints['pages'],
                        'current'   => $current_page,
                    ) );
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.egcp-view-complaint').on('click', function() {
        var complaintId = $(this).data('complaint-id');
        $('#complaint-details-' + complaintId).toggle();
    });
});
</script>
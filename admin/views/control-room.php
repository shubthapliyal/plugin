<?php
/**
 * Admin Control Room View.
 *
 * Dashboard with metrics, analytics, and complaint management.
 *
 * @package    E_Governance_Complaint_Portal
 * @subpackage Admin/Views
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get department labels
$dept_labels = array(
    'health'      => __( 'Health', 'e-governance-complaint-portal' ),
    'water'       => __( 'Water', 'e-governance-complaint-portal' ),
    'electricity' => __( 'Electricity', 'e-governance-complaint-portal' ),
);

// Get status labels
$status_labels = get_option( 'egcp_status_options', array() );
?>

<div class="wrap egcp-control-room">
    <h1 class="wp-heading-inline">
        <?php esc_html_e( 'Grievance Control Room', 'e-governance-complaint-portal' ); ?>
    </h1>

    <hr class="wp-header-end">

    <!-- Dashboard Statistics -->
    <div class="egcp-stats-grid">
        <div class="egcp-stat-card egcp-stat-total">
            <div class="egcp-stat-icon">📊</div>
            <div class="egcp-stat-content">
                <h3><?php echo esc_html( number_format( $stats['total'] ) ); ?></h3>
                <p><?php esc_html_e( 'Total Complaints', 'e-governance-complaint-portal' ); ?></p>
            </div>
        </div>

        <div class="egcp-stat-card egcp-stat-pending">
            <div class="egcp-stat-icon">⏳</div>
            <div class="egcp-stat-content">
                <h3><?php echo esc_html( number_format( $stats['pending'] ) ); ?></h3>
                <p><?php esc_html_e( 'Pending Review', 'e-governance-complaint-portal' ); ?></p>
            </div>
        </div>

        <div class="egcp-stat-card egcp-stat-progress">
            <div class="egcp-stat-icon">🔄</div>
            <div class="egcp-stat-content">
                <h3><?php echo esc_html( number_format( $stats['in_progress'] ) ); ?></h3>
                <p><?php esc_html_e( 'In Progress', 'e-governance-complaint-portal' ); ?></p>
            </div>
        </div>

        <div class="egcp-stat-card egcp-stat-resolved">
            <div class="egcp-stat-icon">✅</div>
            <div class="egcp-stat-content">
                <h3><?php echo esc_html( number_format( $stats['resolved'] ) ); ?></h3>
                <p><?php esc_html_e( 'Resolved', 'e-governance-complaint-portal' ); ?></p>
            </div>
        </div>

        <div class="egcp-stat-card egcp-stat-overdue">
            <div class="egcp-stat-icon">⚠️</div>
            <div class="egcp-stat-content">
                <h3><?php echo esc_html( number_format( $stats['overdue'] ) ); ?></h3>
                <p><?php esc_html_e( 'Overdue (SLA Breached)', 'e-governance-complaint-portal' ); ?></p>
            </div>
        </div>

        <div class="egcp-stat-card egcp-stat-escalated">
            <div class="egcp-stat-icon">🚨</div>
            <div class="egcp-stat-content">
                <h3><?php echo esc_html( number_format( $stats['escalated'] ) ); ?></h3>
                <p><?php esc_html_e( 'Escalated', 'e-governance-complaint-portal' ); ?></p>
            </div>
        </div>
    </div>

    <!-- Department Statistics -->
    <div class="egcp-section">
        <h2><?php esc_html_e( 'Complaints by Department', 'e-governance-complaint-portal' ); ?></h2>

        <div class="egcp-dept-stats">
            <?php foreach ( $stats['by_department'] as $dept => $count ) : ?>
                <div class="egcp-dept-card">
                    <h3><?php echo esc_html( $dept_labels[ $dept ] ); ?></h3>
                    <p class="egcp-dept-count"><?php echo esc_html( number_format( $count ) ); ?></p>
                    
                    <?php if ( isset( $stats['sla_compliance'][ $dept ] ) ) : ?>
                        <p class="egcp-dept-sla">
                            <?php 
                            printf( 
                                esc_html__( 'SLA Compliance: %s%%', 'e-governance-complaint-portal' ),
                                esc_html( $stats['sla_compliance'][ $dept ]['compliance'] )
                            ); 
                            ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Export Section -->
    <div class="egcp-section egcp-export-section">
        <h2><?php esc_html_e( 'Export Complaints (CSV)', 'e-governance-complaint-portal' ); ?></h2>
        
        <form method="post" class="egcp-export-form">
            <?php wp_nonce_field( 'egcp_admin_action', 'egcp_admin_nonce' ); ?>
            <input type="hidden" name="egcp_action" value="export_csv">

            <div class="egcp-export-filters">
                <div class="egcp-filter-group">
                    <label><?php esc_html_e( 'Department:', 'e-governance-complaint-portal' ); ?></label>
                    <select name="filter_department">
                        <option value=""><?php esc_html_e( 'All Departments', 'e-governance-complaint-portal' ); ?></option>
                        <?php foreach ( $dept_labels as $dept => $label ) : ?>
                            <option value="<?php echo esc_attr( $dept ); ?>"><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="egcp-filter-group">
                    <label><?php esc_html_e( 'Status:', 'e-governance-complaint-portal' ); ?></label>
                    <select name="filter_status">
                        <option value=""><?php esc_html_e( 'All Statuses', 'e-governance-complaint-portal' ); ?></option>
                        <?php foreach ( $status_labels as $status => $label ) : ?>
                            <option value="<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="egcp-filter-group">
                    <label><?php esc_html_e( 'Date From:', 'e-governance-complaint-portal' ); ?></label>
                    <input type="date" name="filter_date_from">
                </div>

                <div class="egcp-filter-group">
                    <label><?php esc_html_e( 'Date To:', 'e-governance-complaint-portal' ); ?></label>
                    <input type="date" name="filter_date_to">
                </div>
            </div>

            <button type="submit" class="button button-primary">
                <?php esc_html_e( 'Export to CSV', 'e-governance-complaint-portal' ); ?>
            </button>
        </form>
    </div>

    <!-- Recent Complaints -->
    <div class="egcp-section">
        <h2><?php esc_html_e( 'Recent Complaints', 'e-governance-complaint-portal' ); ?></h2>

        <table class="wp-list-table widefat fixed striped egcp-complaints-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Grievance ID', 'e-governance-complaint-portal' ); ?></th>
                    <th><?php esc_html_e( 'Title', 'e-governance-complaint-portal' ); ?></th>
                    <th><?php esc_html_e( 'Department', 'e-governance-complaint-portal' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'e-governance-complaint-portal' ); ?></th>
                    <th><?php esc_html_e( 'SLA', 'e-governance-complaint-portal' ); ?></th>
                    <th><?php esc_html_e( 'Submitted', 'e-governance-complaint-portal' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'e-governance-complaint-portal' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $recent_complaints ) ) : ?>
                    <?php foreach ( $recent_complaints as $complaint ) : ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html( $complaint->grievance_id ); ?></strong>
                            </td>
                            <td><?php echo esc_html( $complaint->title ); ?></td>
                            <td>
                                <span class="egcp-badge egcp-badge-dept-<?php echo esc_attr( $complaint->department ); ?>">
                                    <?php echo esc_html( $dept_labels[ $complaint->department ] ); ?>
                                </span>
                            </td>
                            <td>
                                <span class="egcp-status-badge egcp-status-<?php echo esc_attr( $complaint->status ); ?>">
                                    <?php echo esc_html( ucfirst( str_replace( '_', ' ', $complaint->status ) ) ); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo EGCP_SLA::format_sla_display( $complaint->sla_deadline, $complaint->status ); ?>
                            </td>
                            <td>
                                <?php echo esc_html( date_i18n( 'M j, Y', strtotime( $complaint->created_at ) ) ); ?>
                            </td>
                            <td>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=egcp-all-complaints&complaint_id=' . $complaint->id ) ); ?>" class="button button-small">
                                    <?php esc_html_e( 'View', 'e-governance-complaint-portal' ); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">
                            <?php esc_html_e( 'No complaints found.', 'e-governance-complaint-portal' ); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <p style="text-align: center; margin-top: 20px;">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=egcp-all-complaints' ) ); ?>" class="button button-primary button-large">
                <?php esc_html_e( 'View All Complaints', 'e-governance-complaint-portal' ); ?>
            </a>
        </p>
    </div>
</div>
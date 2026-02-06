<?php
/**
 * Helpdesk View.
 *
 * FAQs and instructions for citizens using the complaint portal.
 *
 * @package    E_Governance_Complaint_Portal
 * @subpackage Public/Views
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="egcp-helpdesk-wrapper">
    <div class="egcp-page-header">
        <h2><?php esc_html_e( 'Helpdesk & FAQs', 'e-governance-complaint-portal' ); ?></h2>
        <p><?php esc_html_e( 'Find answers to common questions about filing and tracking complaints.', 'e-governance-complaint-portal' ); ?></p>
    </div>

    <div class="egcp-faq-sections">
        <!-- Getting Started -->
        <div class="egcp-faq-section">
            <h3><?php esc_html_e( 'Getting Started', 'e-governance-complaint-portal' ); ?></h3>

            <div class="egcp-faq-item">
                <h4><?php esc_html_e( 'How do I file a complaint?', 'e-governance-complaint-portal' ); ?></h4>
                <p><?php esc_html_e( 'Click on the "File New Complaint" tab, fill out the required information including title, description, department, and location details. You can also attach up to 5 images to support your complaint.', 'e-governance-complaint-portal' ); ?></p>
            </div>

            <div class="egcp-faq-item">
                <h4><?php esc_html_e( 'What information do I need to provide?', 'e-governance-complaint-portal' ); ?></h4>
                <p><?php esc_html_e( 'You must provide: complaint title (10-200 characters), detailed description (minimum 50 characters), department (Health/Water/Electricity), state, district, and 6-digit pincode. Ward/area and images are optional.', 'e-governance-complaint-portal' ); ?></p>
            </div>

            <div class="egcp-faq-item">
                <h4><?php esc_html_e( 'What is a Grievance ID?', 'e-governance-complaint-portal' ); ?></h4>
                <p><?php esc_html_e( 'Every complaint receives a unique Grievance ID (e.g., EGCP-HLTH-20260206-0001) when submitted. Use this ID to track your complaint status.', 'e-governance-complaint-portal' ); ?></p>
            </div>
        </div>

        <!-- Tracking & Status -->
        <div class="egcp-faq-section">
            <h3><?php esc_html_e( 'Tracking & Status', 'e-governance-complaint-portal' ); ?></h3>

            <div class="egcp-faq-item">
                <h4><?php esc_html_e( 'What do the different statuses mean?', 'e-governance-complaint-portal' ); ?></h4>
                <ul>
                    <li><strong><?php esc_html_e( 'Pending Review:', 'e-governance-complaint-portal' ); ?></strong> <?php esc_html_e( 'Your complaint is awaiting admin review.', 'e-governance-complaint-portal' ); ?></li>
                    <li><strong><?php esc_html_e( 'Approved:', 'e-governance-complaint-portal' ); ?></strong> <?php esc_html_e( 'Admin has approved and assigned your complaint to an officer.', 'e-governance-complaint-portal' ); ?></li>
                    <li><strong><?php esc_html_e( 'In Progress:', 'e-governance-complaint-portal' ); ?></strong> <?php esc_html_e( 'Officer is actively working on your complaint.', 'e-governance-complaint-portal' ); ?></li>
                    <li><strong><?php esc_html_e( 'Resolved:', 'e-governance-complaint-portal' ); ?></strong> <?php esc_html_e( 'Your complaint has been resolved.', 'e-governance-complaint-portal' ); ?></li>
                    <li><strong><?php esc_html_e( 'Rejected:', 'e-governance-complaint-portal' ); ?></strong> <?php esc_html_e( 'Complaint was rejected (check admin reply for reason).', 'e-governance-complaint-portal' ); ?></li>
                    <li><strong><?php esc_html_e( 'Escalated:', 'e-governance-complaint-portal' ); ?></strong> <?php esc_html_e( 'Complaint escalated to higher authority.', 'e-governance-complaint-portal' ); ?></li>
                </ul>
            </div>

            <div class="egcp-faq-item">
                <h4><?php esc_html_e( 'What is SLA status?', 'e-governance-complaint-portal' ); ?></h4>
                <p><?php esc_html_e( 'SLA (Service Level Agreement) indicates the deadline for resolving your complaint. Different departments have different SLA periods:', 'e-governance-complaint-portal' ); ?></p>
                <ul>
                    <li><?php esc_html_e( 'Health: 7 days', 'e-governance-complaint-portal' ); ?></li>
                    <li><?php esc_html_e( 'Water: 15 days', 'e-governance-complaint-portal' ); ?></li>
                    <li><?php esc_html_e( 'Electricity: 10 days', 'e-governance-complaint-portal' ); ?></li>
                </ul>
                <p><?php esc_html_e( 'If the complaint is not resolved within the SLA period, it will be marked as "Overdue".', 'e-governance-complaint-portal' ); ?></p>
            </div>

            <div class="egcp-faq-item">
                <h4><?php esc_html_e( 'How can I track my complaint?', 'e-governance-complaint-portal' ); ?></h4>
                <p><?php esc_html_e( 'Use the "Complaint Tracking" tab and enter your Grievance ID. You can also view all your complaints in the "My Complaints" tab.', 'e-governance-complaint-portal' ); ?></p>
            </div>
        </div>

        <!-- Responses & Communication -->
        <div class="egcp-faq-section">
            <h3><?php esc_html_e( 'Responses & Communication', 'e-governance-complaint-portal' ); ?></h3>

            <div class="egcp-faq-item">
                <h4><?php esc_html_e( 'What is the difference between Officer Reply and Admin Reply?', 'e-governance-complaint-portal' ); ?></h4>
                <p><strong><?php esc_html_e( 'Officer Reply:', 'e-governance-complaint-portal' ); ?></strong> <?php esc_html_e( 'Technical or internal notes from the department officer handling your case.', 'e-governance-complaint-portal' ); ?></p>
                <p><strong><?php esc_html_e( 'Admin Reply:', 'e-governance-complaint-portal' ); ?></strong> <?php esc_html_e( 'Official response from the government authority.', 'e-governance-complaint-portal' ); ?></p>
            </div>

            <div class="egcp-faq-item">
                <h4><?php esc_html_e( 'Will I be notified when my complaint status changes?', 'e-governance-complaint-portal' ); ?></h4>
                <p><?php esc_html_e( 'Check the "Officer Replies" tab regularly to see any updates or responses to your complaints. Email notifications may be enabled by the administrator.', 'e-governance-complaint-portal' ); ?></p>
            </div>
        </div>

        <!-- Technical Issues -->
        <div class="egcp-faq-section">
            <h3><?php esc_html_e( 'Technical Issues', 'e-governance-complaint-portal' ); ?></h3>

            <div class="egcp-faq-item">
                <h4><?php esc_html_e( 'I can\'t upload images. What should I do?', 'e-governance-complaint-portal' ); ?></h4>
                <p><?php esc_html_e( 'Ensure your images meet these requirements:', 'e-governance-complaint-portal' ); ?></p>
                <ul>
                    <li><?php esc_html_e( 'Format: JPG, PNG, or WebP only', 'e-governance-complaint-portal' ); ?></li>
                    <li><?php esc_html_e( 'Size: Maximum 2MB per image', 'e-governance-complaint-portal' ); ?></li>
                    <li><?php esc_html_e( 'Maximum 5 images per complaint', 'e-governance-complaint-portal' ); ?></li>
                </ul>
            </div>

            <div class="egcp-faq-item">
                <h4><?php esc_html_e( 'My complaint submission failed. What happened?', 'e-governance-complaint-portal' ); ?></h4>
                <p><?php esc_html_e( 'Common reasons include:', 'e-governance-complaint-portal' ); ?></p>
                <ul>
                    <li><?php esc_html_e( 'Title too short (minimum 10 characters)', 'e-governance-complaint-portal' ); ?></li>
                    <li><?php esc_html_e( 'Description too short (minimum 50 characters)', 'e-governance-complaint-portal' ); ?></li>
                    <li><?php esc_html_e( 'Invalid pincode (must be 6 digits)', 'e-governance-complaint-portal' ); ?></li>
                    <li><?php esc_html_e( 'Missing required fields', 'e-governance-complaint-portal' ); ?></li>
                </ul>
            </div>
        </div>

        <!-- Contact Support -->
        <div class="egcp-faq-section">
            <h3><?php esc_html_e( 'Need More Help?', 'e-governance-complaint-portal' ); ?></h3>

            <div class="egcp-contact-box">
                <p><?php esc_html_e( 'If you couldn\'t find the answer to your question, please contact our support team:', 'e-governance-complaint-portal' ); ?></p>
                <p>
                    <strong><?php esc_html_e( 'Email:', 'e-governance-complaint-portal' ); ?></strong>
                    <?php echo esc_html( get_option( 'admin_email' ) ); ?>
                </p>
            </div>
        </div>
    </div>
</div>
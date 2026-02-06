<?php
/**
 * Complaint Submission Form View.
 *
 * Frontend form for citizens to submit complaints.
 *
 * @package    E_Governance_Complaint_Portal
 * @subpackage Public/Views
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get states from options
$states      = get_option( 'egcp_states', array() );
$departments = get_option( 'egcp_allowed_departments', array( 'health', 'water', 'electricity' ) );
$max_images  = get_option( 'egcp_max_images', 5 );

// Department labels
$dept_labels = array(
    'health'      => __( 'Health Department', 'e-governance-complaint-portal' ),
    'water'       => __( 'Water Department', 'e-governance-complaint-portal' ),
    'electricity' => __( 'Electricity Department', 'e-governance-complaint-portal' ),
);
?>

<div class="egcp-complaint-form-wrapper">
    <div class="egcp-form-header">
        <h2><?php esc_html_e( 'File New Complaint', 'e-governance-complaint-portal' ); ?></h2>
        <p class="egcp-form-description">
            <?php esc_html_e( 'Please fill out the form below to submit your complaint. All fields marked with * are required.', 'e-governance-complaint-portal' ); ?>
        </p>
    </div>

    <form id="egcp-complaint-form" class="egcp-form" enctype="multipart/form-data">
        <?php wp_nonce_field( 'egcp_public_nonce', 'egcp_nonce' ); ?>

        <!-- Success Message (hidden by default) -->
        <div id="egcp-success-message" class="egcp-notice egcp-notice-success" style="display: none;">
            <p>
                <strong><?php esc_html_e( 'Complaint Submitted Successfully!', 'e-governance-complaint-portal' ); ?></strong><br>
                <?php esc_html_e( 'Your Grievance ID:', 'e-governance-complaint-portal' ); ?> 
                <span id="egcp-grievance-id" class="egcp-grievance-id"></span>
            </p>
            <p><?php esc_html_e( 'You can track your complaint status using this Grievance ID.', 'e-governance-complaint-portal' ); ?></p>
        </div>

        <!-- Error Message (hidden by default) -->
        <div id="egcp-error-message" class="egcp-notice egcp-notice-error" style="display: none;">
            <p id="egcp-error-text"></p>
        </div>

        <!-- Complaint Details Section -->
        <div class="egcp-form-section">
            <h3><?php esc_html_e( 'Complaint Details', 'e-governance-complaint-portal' ); ?></h3>

            <div class="egcp-form-row">
                <label for="complaint-title">
                    <?php esc_html_e( 'Complaint Title', 'e-governance-complaint-portal' ); ?> <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    id="complaint-title" 
                    name="title" 
                    required 
                    minlength="10" 
                    maxlength="200"
                    placeholder="<?php esc_attr_e( 'Brief summary of your complaint (10-200 characters)', 'e-governance-complaint-portal' ); ?>"
                >
                <small class="egcp-help-text">
                    <?php esc_html_e( 'Example: "Broken water pipe on Main Street" or "Dengue outbreak in Ward 5"', 'e-governance-complaint-portal' ); ?>
                </small>
            </div>

            <div class="egcp-form-row">
                <label for="complaint-description">
                    <?php esc_html_e( 'Detailed Description', 'e-governance-complaint-portal' ); ?> <span class="required">*</span>
                </label>
                <textarea 
                    id="complaint-description" 
                    name="description" 
                    required 
                    rows="6"
                    minlength="50"
                    placeholder="<?php esc_attr_e( 'Provide complete details about your complaint (minimum 50 characters)', 'e-governance-complaint-portal' ); ?>"
                ></textarea>
                <small class="egcp-help-text">
                    <?php esc_html_e( 'Include as much detail as possible to help us address your complaint efficiently.', 'e-governance-complaint-portal' ); ?>
                </small>
            </div>

            <div class="egcp-form-row">
                <label for="complaint-department">
                    <?php esc_html_e( 'Department', 'e-governance-complaint-portal' ); ?> <span class="required">*</span>
                </label>
                <select id="complaint-department" name="department" required>
                    <option value=""><?php esc_html_e( '-- Select Department --', 'e-governance-complaint-portal' ); ?></option>
                    <?php foreach ( $departments as $dept ) : ?>
                        <?php if ( isset( $dept_labels[ $dept ] ) ) : ?>
                            <option value="<?php echo esc_attr( $dept ); ?>">
                                <?php echo esc_html( $dept_labels[ $dept ] ); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Location Section -->
        <div class="egcp-form-section">
            <h3><?php esc_html_e( 'Location Details', 'e-governance-complaint-portal' ); ?></h3>

            <div class="egcp-form-row egcp-form-row-half">
                <div class="egcp-form-col">
                    <label for="complaint-state">
                        <?php esc_html_e( 'State', 'e-governance-complaint-portal' ); ?> <span class="required">*</span>
                    </label>
                    <select id="complaint-state" name="state" required>
                        <option value=""><?php esc_html_e( '-- Select State --', 'e-governance-complaint-portal' ); ?></option>
                        <?php foreach ( $states as $state ) : ?>
                            <option value="<?php echo esc_attr( $state ); ?>"><?php echo esc_html( $state ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="egcp-form-col">
                    <label for="complaint-district">
                        <?php esc_html_e( 'District', 'e-governance-complaint-portal' ); ?> <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="complaint-district" 
                        name="district" 
                        required
                        placeholder="<?php esc_attr_e( 'Enter district name', 'e-governance-complaint-portal' ); ?>"
                    >
                </div>
            </div>

            <div class="egcp-form-row egcp-form-row-half">
                <div class="egcp-form-col">
                    <label for="complaint-ward">
                        <?php esc_html_e( 'Ward / Area', 'e-governance-complaint-portal' ); ?>
                    </label>
                    <input 
                        type="text" 
                        id="complaint-ward" 
                        name="ward"
                        placeholder="<?php esc_attr_e( 'Ward number or area name (optional)', 'e-governance-complaint-portal' ); ?>"
                    >
                </div>

                <div class="egcp-form-col">
                    <label for="complaint-pincode">
                        <?php esc_html_e( 'Pincode', 'e-governance-complaint-portal' ); ?> <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="complaint-pincode" 
                        name="pincode" 
                        required
                        pattern="\d{6}"
                        maxlength="6"
                        placeholder="<?php esc_attr_e( '6-digit pincode', 'e-governance-complaint-portal' ); ?>"
                    >
                </div>
            </div>
        </div>

        <!-- Image Upload Section -->
        <div class="egcp-form-section">
            <h3><?php esc_html_e( 'Supporting Images', 'e-governance-complaint-portal' ); ?> 
                <small>(<?php esc_html_e( 'Optional', 'e-governance-complaint-portal' ); ?>)</small>
            </h3>

            <div class="egcp-form-row">
                <label for="complaint-images">
                    <?php esc_html_e( 'Upload Images', 'e-governance-complaint-portal' ); ?>
                </label>
                <input 
                    type="file" 
                    id="complaint-images" 
                    accept="image/jpeg,image/jpg,image/png,image/webp"
                    multiple
                >
                <small class="egcp-help-text">
                    <?php 
                    printf( 
                        esc_html__( 'You can upload up to %d images. Accepted formats: JPG, PNG, WebP. Max size: 2MB per image.', 'e-governance-complaint-portal' ),
                        $max_images 
                    ); 
                    ?>
                </small>
            </div>

            <div id="egcp-image-preview" class="egcp-image-preview"></div>
            <input type="hidden" id="egcp-uploaded-images" name="images[]" value="">
        </div>

        <!-- Submit Button -->
        <div class="egcp-form-actions">
            <button type="submit" id="egcp-submit-btn" class="egcp-btn egcp-btn-primary egcp-btn-large">
                <span class="egcp-btn-text"><?php esc_html_e( 'Submit Complaint', 'e-governance-complaint-portal' ); ?></span>
                <span class="egcp-btn-loader" style="display: none;">
                    <?php esc_html_e( 'Submitting...', 'e-governance-complaint-portal' ); ?>
                </span>
            </button>
            <button type="reset" class="egcp-btn egcp-btn-secondary egcp-btn-large">
                <?php esc_html_e( 'Clear Form', 'e-governance-complaint-portal' ); ?>
            </button>
        </div>
    </form>
</div>
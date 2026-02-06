/**
 * Public JavaScript for e-Governance Complaint Portal.
 *
 * Handles complaint form submission, image uploads, and AJAX interactions.
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        
        // Complaint Form
        const complaintForm = document.getElementById('egcp-complaint-form');
        if (complaintForm) {
            initComplaintForm();
        }

    });

    /**
     * Initialize complaint form functionality.
     */
    function initComplaintForm() {
        const form = document.getElementById('egcp-complaint-form');
        const submitBtn = document.getElementById('egcp-submit-btn');
        const imageInput = document.getElementById('complaint-images');
        const imagePreview = document.getElementById('egcp-image-preview');
        const successMessage = document.getElementById('egcp-success-message');
        const errorMessage = document.getElementById('egcp-error-message');

        let uploadedImages = [];

        // Handle image selection
        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                handleImageUpload(e.target.files);
            });
        }

        // Handle form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitComplaint();
        });

        // Handle form reset
        form.addEventListener('reset', function() {
            uploadedImages = [];
            imagePreview.innerHTML = '';
            hideMessages();
        });

        /**
         * Handle image file upload.
         */
        function handleImageUpload(files) {
            const maxImages = parseInt(egcpPublic.maxImages) || 5;
            const maxFileSize = parseInt(egcpPublic.maxFileSize) || 2097152; // 2MB

            if (uploadedImages.length + files.length > maxImages) {
                showError('You can only upload up to ' + maxImages + ' images.');
                return;
            }

            Array.from(files).forEach(function(file) {
                // Validate file type
                if (!file.type.match(/image\/(jpeg|jpg|png|webp)/)) {
                    showError('Invalid file type: ' + file.name + '. Only JPG, PNG, and WebP are allowed.');
                    return;
                }

                // Validate file size
                if (file.size > maxFileSize) {
                    showError('File too large: ' + file.name + '. Maximum size is 2MB.');
                    return;
                }

                // Upload via AJAX
                uploadImage(file);
            });
        }

        /**
         * Upload single image via AJAX.
         */
        function uploadImage(file) {
            const formData = new FormData();
            formData.append('action', 'egcp_upload_image');
            formData.append('nonce', egcpPublic.nonce);
            formData.append('file', file);

            // Show uploading status
            const tempPreview = createImagePreview(file, 'uploading');
            imagePreview.appendChild(tempPreview);

            fetch(egcpPublic.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    uploadedImages.push(data.data.url);
                    tempPreview.classList.remove('uploading');
                    tempPreview.classList.add('uploaded');
                    tempPreview.dataset.url = data.data.url;
                } else {
                    imagePreview.removeChild(tempPreview);
                    showError(data.data.message || 'Failed to upload image.');
                }
            })
            .catch(error => {
                imagePreview.removeChild(tempPreview);
                showError('Upload error: ' + error.message);
            });
        }

        /**
         * Create image preview element.
         */
        function createImagePreview(file, status) {
            const preview = document.createElement('div');
            preview.className = 'egcp-image-item ' + status;

            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = file.name;

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'egcp-remove-image';
            removeBtn.innerHTML = '&times;';
            removeBtn.addEventListener('click', function() {
                const url = preview.dataset.url;
                if (url) {
                    uploadedImages = uploadedImages.filter(u => u !== url);
                }
                imagePreview.removeChild(preview);
            });

            preview.appendChild(img);
            preview.appendChild(removeBtn);

            return preview;
        }

        /**
         * Submit complaint form.
         */
        function submitComplaint() {
            hideMessages();

            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.querySelector('.egcp-btn-text').style.display = 'none';
            submitBtn.querySelector('.egcp-btn-loader').style.display = 'inline';

            // Prepare form data
            const formData = new FormData(form);
            formData.append('action', 'egcp_submit_complaint');
            
            // Add uploaded images
            formData.delete('images[]');
            uploadedImages.forEach(function(url) {
                formData.append('images[]', url);
            });

            // Submit via AJAX
            fetch(egcpPublic.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data.data.grievance_id);
                    form.reset();
                    uploadedImages = [];
                    imagePreview.innerHTML = '';
                    
                    // Scroll to success message
                    successMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    showError(data.data.message || 'Failed to submit complaint.');
                }
            })
            .catch(error => {
                showError('Submission error: ' + error.message);
            })
            .finally(() => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.querySelector('.egcp-btn-text').style.display = 'inline';
                submitBtn.querySelector('.egcp-btn-loader').style.display = 'none';
            });
        }

        /**
         * Show success message.
         */
        function showSuccess(grievanceId) {
            successMessage.style.display = 'block';
            document.getElementById('egcp-grievance-id').textContent = grievanceId;
            errorMessage.style.display = 'none';
        }

        /**
         * Show error message.
         */
        function showError(message) {
            errorMessage.style.display = 'block';
            document.getElementById('egcp-error-text').textContent = message;
            successMessage.style.display = 'none';
        }

        /**
         * Hide all messages.
         */
        function hideMessages() {
            successMessage.style.display = 'none';
            errorMessage.style.display = 'none';
        }
    }

})();
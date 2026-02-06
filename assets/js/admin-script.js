/**
 * Admin JavaScript for e-Governance Complaint Portal.
 *
 * Handles admin interactions, AJAX operations, and UI enhancements.
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Initialize components
        initComplaintDetails();
        initQuickActions();
        initDataTables();

    });

    /**
     * Initialize complaint details toggle.
     */
    function initComplaintDetails() {
        $('.egcp-view-complaint').on('click', function() {
            var complaintId = $(this).data('complaint-id');
            $('#complaint-details-' + complaintId).slideToggle();
            
            var btnText = $(this).text();
            if (btnText.indexOf('View') !== -1) {
                $(this).text('Hide Details');
            } else {
                $(this).text('View Details');
            }
        });
    }

    /**
     * Initialize quick actions (assign officer, update status).
     */
    function initQuickActions() {
        // Assign officer modal/inline form
        $('.egcp-assign-officer-btn').on('click', function(e) {
            e.preventDefault();
            var complaintId = $(this).data('complaint-id');
            // Show assign officer form
            $('#assign-officer-form-' + complaintId).slideToggle();
        });

        // Update status confirmation
        $('form[name="update-status-form"]').on('submit', function(e) {
            var status = $(this).find('select[name="status"]').val();
            var statusText = $(this).find('select[name="status"] option:selected').text();
            
            if (!confirm('Are you sure you want to change status to: ' + statusText + '?')) {
                e.preventDefault();
                return false;
            }
        });
    }

    /**
     * Initialize DataTables for complaint lists (if needed).
     */
    function initDataTables() {
        if ($.fn.DataTable) {
            $('.egcp-complaints-table').DataTable({
                pageLength: 25,
                order: [[5, 'desc']], // Order by submitted date
                columnDefs: [
                    { orderable: false, targets: -1 } // Disable sorting on actions column
                ]
            });
        }
    }

    /**
     * AJAX: Delete complaint (admin only).
     */
    function deleteComplaint(complaintId) {
        if (!confirm('Are you sure you want to delete this complaint? This action cannot be undone.')) {
            return;
        }

        $.ajax({
            url: egcpAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'egcp_delete_complaint',
                nonce: egcpAdmin.nonce,
                complaint_id: complaintId
            },
            success: function(response) {
                if (response.success) {
                    alert('Complaint deleted successfully.');
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    }

    // Expose delete function globally
    window.egcpDeleteComplaint = deleteComplaint;

})(jQuery);
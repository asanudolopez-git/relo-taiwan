jQuery(document).ready(function($) {
    // This file can be used for any JavaScript functionality needed for the lease summary admin
    // Currently, there are no dynamic interactions required, but this file is included for future use
    
    // Example: If we need to add dynamic behavior when selecting a client lease
    $('#client_lease_id').on('change', function() {
        const leaseId = $(this).val();
        if (leaseId) {
            console.log('Selected lease ID: ' + leaseId);
            // Additional functionality can be added here
        }
    });
});

/**
 * Departure Service Admin JavaScript
 * Dynamically loads and displays detailed property info based on the selected lease
 */
jQuery(document).ready(function ($) {
    console.log('Departure Service Admin JS loaded');
    
    // Wait a bit for DOM to be fully ready
    setTimeout(function() {
        // Cache DOM elements
        const $leaseSelect = $('#client_lease_id');
        const $propertyDisplay = $('#property_display');
        const $propertyHidden = $('#property_id_hidden');
        
        console.log('Elements found:', {
            leaseSelect: $leaseSelect.length,
            propertyDisplay: $propertyDisplay.length,
            propertyHidden: $propertyHidden.length
        });
        
        // Debug: log all select elements to see what's available
        console.log('All select elements:', $('select').map(function() { return this.id; }).get());
        console.log('All textarea elements:', $('textarea').map(function() { return this.id; }).get());
        console.log('All hidden elements:', $('input[type="hidden"]').map(function() { return this.id; }).get());

        /**
         * Fetch property details for a given lease ID via AJAX
         * @param {string|number} leaseId
         */
        function loadPropertyDetails(leaseId) {
            console.log('loadPropertyDetails called with leaseId:', leaseId);
            
            // Clear fields if no lease selected
            if (!leaseId) {
                $propertyDisplay.val('');
                $propertyHidden.val('');
                return;
            }

            // Optional: visual cue while loading
            $propertyDisplay.val('Loading property details…');

            console.log('Making AJAX request with data:', {
                action: 'get_lease_property_details',
                lease_id: leaseId,
                nonce: departure_service_data.nonce,
                url: departure_service_data.ajax_url
            });

            $.ajax({
                url: departure_service_data.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_lease_property_details',
                    lease_id: leaseId,
                    nonce: departure_service_data.nonce
                },
                success: function (response) {
                    console.log('AJAX response:', response);
                    if (response.success) {
                        $propertyDisplay.val(response.data.details);
                        $propertyHidden.val(response.data.property_id);
                        console.log('Property details updated successfully');
                    } else {
                        console.error('Property details error:', response.data.message);
                        $propertyDisplay.val('No property details found for this lease.');
                        $propertyHidden.val('');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error:', {xhr, status, error});
                    $propertyDisplay.val('Error fetching property details.');
                    $propertyHidden.val('');
                }
            });
        }

        // Handle lease selection change
        $leaseSelect.on('change', function () {
            console.log('Lease selection changed to:', $(this).val());
            loadPropertyDetails($(this).val());
        });

        // Initial load (e.g., when editing an existing post)
        if ($leaseSelect.val()) {
            console.log('Initial lease value found:', $leaseSelect.val());
            loadPropertyDetails($leaseSelect.val());
        }
    }, 500); // Wait 500ms for DOM to be ready
});

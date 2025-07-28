/**
 * Client Lease Admin JavaScript
 * Handles dynamic loading of properties based on client selection
 */
jQuery(document).ready(function($) {
    // Cache DOM elements
    const $clientSelect = $('#client_id');
    const $propertySelect = $('#property_id');
    
    // If we have a client selected on page load, store it
    let initialClientId = $clientSelect.val();
    let initialPropertyId = $('#property_id_hidden').val();
    
    // Function to load properties for a client
    function loadPropertiesForClient(clientId, callback) {
        if (!clientId) {
            $propertySelect.html('<option value="">Select Client First</option>');
            $propertySelect.prop('disabled', true);
            return;
        }
        
        $propertySelect.prop('disabled', true);
        $propertySelect.html('<option value="">Loading properties...</option>');
        
        $.ajax({
            url: client_lease_data.ajax_url,
            type: 'POST',
            data: {
                action: 'load_client_properties',
                client_id: clientId,
                nonce: client_lease_data.nonce
            },
            success: function(response) {
                if (response.success) {
                    $propertySelect.html(response.data.options);
                    $propertySelect.prop('disabled', false);
                    
                    // If we have a callback (for initial load), execute it
                    if (typeof callback === 'function') {
                        callback();
                    }
                } else {
                    $propertySelect.html('<option value="">Error loading properties</option>');
                    console.error('Error loading properties:', response.data.message);
                }
            },
            error: function() {
                $propertySelect.html('<option value="">Error loading properties</option>');
                console.error('AJAX error when loading properties');
            }
        });
    }
    
    // Handle client selection change
    $clientSelect.on('change', function() {
        const clientId = $(this).val();
        loadPropertiesForClient(clientId);
    });
    
    // Handle property selection change - update hidden input
    $propertySelect.on('change', function() {
        const propertyId = $(this).val();
        $('#property_id_hidden').val(propertyId);
        console.log('Property selected:', propertyId, 'Hidden input updated');
    });
    
    // On page load, if we have a client selected, load its properties
    if (initialClientId) {
        loadPropertiesForClient(initialClientId, function() {
            // After loading properties, select the previously saved property
            if (initialPropertyId) {
                console.log('Attempting to select property:', initialPropertyId);
                
                // Add a small delay to ensure DOM is fully updated
                setTimeout(function() {
                    $propertySelect.val(initialPropertyId);
                    
                    // Double-check if the value was set correctly
                    if ($propertySelect.val() !== initialPropertyId) {
                        console.warn('Property selection failed. Available options:', $propertySelect.find('option').map(function() { return this.value; }).get());
                        console.warn('Trying to select:', initialPropertyId, 'Type:', typeof initialPropertyId);
                    } else {
                        console.log('Property selected successfully:', $propertySelect.val());
                    }
                }, 100);
            }
        });
    } else {
        // Disable property select until a client is chosen
        $propertySelect.prop('disabled', true);
    }
    
    // Add a message to guide the user
    $clientSelect.closest('tr').after(
        '<tr class="client-property-hint"><td colspan="3">' +
        '<div class="notice notice-info inline" style="margin: 5px 0 15px; padding: 8px 12px;">' +
        '<p><strong>Note:</strong> Select a client first to see their properties from house lists.</p>' +
        '</div></td></tr>'
    );
});

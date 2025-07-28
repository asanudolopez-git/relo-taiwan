/**
 * Departure Service Admin JavaScript
 * Dynamically loads and displays detailed property info based on the selected client
 */
jQuery(document).ready(function ($) {
    // Cache DOM elements
    const $clientSelect = $('#customer_id');
    const $propertyDisplay = $('#property_display');
    const $propertyHidden = $('#property_id_hidden');

    /**
     * Fetch property details for a given client ID via AJAX
     * @param {string|number} clientId
     */
    function loadPropertyDetails(clientId) {
        // Clear fields if no client selected
        if (!clientId) {
            $propertyDisplay.val('');
            $propertyHidden.val('');
            return;
        }

        // Optional: visual cue while loading
        $propertyDisplay.val('Loading property details…');

        $.ajax({
            url: departure_service_data.ajax_url,
            type: 'POST',
            data: {
                action: 'get_property_details',
                client_id: clientId,
                nonce: departure_service_data.nonce
            },
            success: function (response) {
                if (response.success) {
                    $propertyDisplay.val(response.data.details);
                    $propertyHidden.val(response.data.property_id);
                } else {
                    console.error('Property details error:', response.data.message);
                    $propertyDisplay.val('No property details found for this client.');
                    $propertyHidden.val('');
                }
            },
            error: function () {
                console.error('AJAX error while retrieving property details');
                $propertyDisplay.val('Error fetching property details.');
                $propertyHidden.val('');
            }
        });
    }

    // Handle client selection change
    $clientSelect.on('change', function () {
        loadPropertyDetails($(this).val());
    });

    // Initial load (e.g., when editing an existing post)
    if ($clientSelect.val()) {
        loadPropertyDetails($clientSelect.val());
    }
});

jQuery(document).ready(function($) {
    // Toggle property details
    $('.toggle-details').on('click', function() {
        var propertyId = $(this).data('property-id');
        var detailsDiv = $('#property-details-' + propertyId);
        var button = $(this);
        
        detailsDiv.slideToggle(200, function() {
            button.text(detailsDiv.is(':visible') ? 'Less Info' : 'More Info');
        });
    });
    
    // Update property list when customer changes
    $('#selected_customer').on('change', function() {
        var customerId = $(this).val();
        
        if (!customerId) {
            $('.matching-properties-list').parent().html('<p>Please select a customer first to see matching properties.</p>');
            return;
        }
        
        $('.matching-properties-list').parent().html('<p>Loading matching properties...</p>');
        
        var data = {
            'action': 'get_matching_properties',
            'customer_id': customerId,
            'nonce': houses_admin.nonce
        };
        
        $.post(ajaxurl, data, function(response) {
            if (response.success) {
                $('.matching-properties-list').parent().html(response.data);
                // Reinitialize toggle buttons
                $('.toggle-details').on('click', function() {
                    var propertyId = $(this).data('property-id');
                    var detailsDiv = $('#property-details-' + propertyId);
                    var button = $(this);
                    
                    detailsDiv.slideToggle(200, function() {
                        button.text(detailsDiv.is(':visible') ? 'Less Info' : 'More Info');
                    });
                });
            } else {
                $('.matching-properties-list').parent().html('<p>Error loading properties. Please try again.</p>');
            }
        });
    });
    
    // Update station dropdown when metro line changes
    $('.metro-line-select').on('change', function() {
        var lineCode = $(this).val();
        var stationSelect = $('.metro-station-select');
        var postId = $('#post_ID').val();
        
        // Clear current options
        stationSelect.html('<option value="">Select a station</option>');
        
        if (!lineCode) {
            return;
        }
        
        // Show loading indicator
        stationSelect.prop('disabled', true);
        
        var data = {
            'action': 'get_stations_by_line',
            'line_code': lineCode,
            'post_id': postId,
            'nonce': houses_admin.nonce
        };
        
        $.post(ajaxurl, data, function(response) {
            stationSelect.prop('disabled', false);
            
            if (response.success) {
                // Add new options
                $.each(response.data, function(index, station) {
                    var displayName = station.code ? station.code + ' - ' + station.name : station.name;
                    stationSelect.append('<option value="' + station.id + '">' + displayName + '</option>');
                });
                
                // If there was a previously selected value, try to restore it
                var selectedStation = stationSelect.data('selected-value');
                if (selectedStation) {
                    stationSelect.val(selectedStation);
                }
            } else {
                alert('Error loading stations. Please try again.');
            }
        });
    });
    
    // Store the selected station value when the page loads
    $(window).on('load', function() {
        var stationSelect = $('.metro-station-select');
        var selectedValue = stationSelect.val();
        if (selectedValue) {
            stationSelect.data('selected-value', selectedValue);
        }
    });
});

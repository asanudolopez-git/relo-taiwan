jQuery(document).ready(function($) {
    // Cache DOM elements
    const $metroLineSelect = $('.metro-line-select');
    const $stationSelect = $('.metro-station-select');
    
    // Initialize with any pre-selected values
    if (HousesStationFilter.selected_line) {
        $metroLineSelect.val(HousesStationFilter.selected_line);
        
        // If we have a selected line, load the stations for that line
        loadStations(HousesStationFilter.selected_line, HousesStationFilter.selected_station);
    }
    
    // Handle metro line change
    $metroLineSelect.on('change', function() {
        const lineCode = $(this).val();
        
        // Clear current station options
        $stationSelect.html('<option value="">Select Station</option>');
        
        if (!lineCode) {
            return;
        }
        
        // Load stations for the selected line
        loadStations(lineCode);
    });
    
    /**
     * Load stations for a specific metro line
     * 
     * @param {string} lineCode The metro line code
     * @param {string} selectedStation Optional station ID to select after loading
     */
    function loadStations(lineCode, selectedStation = '') {
        // Show loading state
        $stationSelect.prop('disabled', true);
        
        $.ajax({
            url: HousesStationFilter.ajax_url,
            type: 'POST',
            data: {
                action: 'get_stations_by_line',
                line_code: lineCode,
                nonce: HousesStationFilter.nonce
            },
            success: function(response) {
                $stationSelect.prop('disabled', false);
                
                if (response.success && response.data) {
                    // Add new options
                    $.each(response.data, function(index, station) {
                        const selected = (station.id == selectedStation) ? ' selected' : '';
                        $stationSelect.append('<option value="' + station.id + '"' + selected + '>' + station.text + '</option>');
                    });
                } else {
                    console.error('Error loading stations:', response);
                }
            },
            error: function(xhr, status, error) {
                $stationSelect.prop('disabled', false);
                console.error('AJAX error:', status, error);
            }
        });
    }
});

/**
 * ZIP Code Autocomplete functionality
 */
jQuery(document).ready(function($) {
    // Sample ZIP codes for Taiwan districts
    const zipCodes = {
        'Songshan': '105',
        'Xinyi': '110',
        'Da\'an': '106',
        'Zhongshan': '104',
        'Zhongzheng': '100',
        'Datong': '103',
        'Wanhua': '108',
        'Wenshan': '116',
        'Nangang': '115',
        'Neihu': '114',
        'Shilin': '111',
        'Beitou': '112'
    };

    // Function to update ZIP code based on district selection
    function updateZipCode() {
        const districtId = $('#houses_property_district').val();
        if (districtId) {
            // Get the district name from the selected option text
            const districtName = $('#houses_property_district option:selected').text();
            
            if (districtName && zipCodes[districtName]) {
                $('#houses_property_zip_code').val(zipCodes[districtName]);
            }
        }
    }

    // Event listener for district field
    $('#houses_property_district').on('change', updateZipCode);
    
    // Allow manual entry by clicking on the ZIP code field
    $('#houses_property_zip_code').on('click', function() {
        $(this).select(); // Select all text to make it easier to replace
    });
});

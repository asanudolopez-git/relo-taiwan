/**
 * Currency field formatter
 * Automatically formats the rent and price fields to include commas for thousands and currency symbol
 */
jQuery(document).ready(function($) {
    // Find the rent and price fields
    const $rentField = $('#rent');
    const $priceField = $('#price');
    
    // Format the rent field
    if ($rentField.length) {
        // Format on initial load if there's a value
        if ($rentField.val()) {
            const formattedValue = 'NT$' + formatNumberWithCommas($rentField.val());
            $rentField.val(formattedValue);
        }
        
        // Format on input
        $rentField.on('input', function() {
            // Get the current value and remove any non-digit characters
            let value = $(this).val().replace(/[^\d]/g, '');
            
            // Format the value with NT$ prefix and commas
            if (value) {
                const formattedValue = 'NT$' + formatNumberWithCommas(value);
                $(this).val(formattedValue);
            } else {
                $(this).val('');
            }
        });
    }
    
    // Format the price field
    if ($priceField.length) {
        // Format on initial load if there's a value
        if ($priceField.val()) {
            const formattedValue = 'NT$' + formatNumberWithCommas($priceField.val());
            $priceField.val(formattedValue);
        }
        
        // Format on input
        $priceField.on('input', function() {
            // Get the current value and remove any non-digit characters
            let value = $(this).val().replace(/[^\d]/g, '');
            
            // Format the value with NT$ prefix and commas
            if (value) {
                const formattedValue = 'NT$' + formatNumberWithCommas(value);
                $(this).val(formattedValue);
            } else {
                $(this).val('');
            }
        });
    }
    
    // When the form is submitted, remove the formatting to store the raw numbers
    $('form#post').on('submit', function() {
        if ($rentField.length) {
            const rawRentValue = $rentField.val().replace(/NT\$|,/g, '');
            $rentField.val(rawRentValue);
        }
        
        if ($priceField.length) {
            const rawPriceValue = $priceField.val().replace(/NT\$|,/g, '');
            $priceField.val(rawPriceValue);
        }
        
        return true;
    });
    
    /**
     * Format a number with commas for thousands
     */
    function formatNumberWithCommas(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
});

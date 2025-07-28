/**
 * Budget field formatter for Assignee
 * 
 * Automatically formats the budget field with NT$ prefix and thousand separators
 */
jQuery(document).ready(function($) {
    // Store original value on focus
    $('.budget-field').on('focus', function() {
        let value = $(this).val();
        
        // If already formatted, remove formatting for editing
        if (value && value.indexOf('NT$') === 0) {
            // Remove NT$ prefix and commas
            value = value.replace('NT$', '').replace(/,/g, '');
            $(this).val(value);
        }
        
        // Store the raw value as data attribute
        $(this).data('raw-value', value);
    });
    
    // Format budget field on input
    $('.budget-field').on('input', function() {
        let value = $(this).val();
        
        // Remove any non-numeric characters
        value = value.replace(/[^\d]/g, '');
        
        // Just update with clean numeric value while typing
        $(this).val(value);
    });
    
    // Format with NT$ when field loses focus
    $('.budget-field').on('blur', function() {
        let value = $(this).val();
        
        // Skip if empty
        if (!value) {
            return;
        }
        
        // Parse as integer
        let numValue = parseInt(value, 10);
        
        // Skip if not a valid number
        if (isNaN(numValue)) {
            return;
        }
        
        // Format with thousand separators
        let formattedValue = numValue.toLocaleString('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
        
        // Add NT$ prefix
        formattedValue = 'NT$' + formattedValue;
        
        // Update field value
        $(this).val(formattedValue);
    });
    
    // Also format on page load for existing values
    $('.budget-field').each(function() {
        let value = $(this).val();
        
        // Skip if empty or already formatted
        if (!value || value.indexOf('NT$') === 0) {
            return;
        }
        
        // Remove any non-numeric characters
        value = value.replace(/[^\d]/g, '');
        
        // Parse as integer
        let numValue = parseInt(value, 10);
        
        // Skip if not a valid number
        if (isNaN(numValue)) {
            return;
        }
        
        // Format with thousand separators
        let formattedValue = numValue.toLocaleString('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
        
        // Add NT$ prefix
        formattedValue = 'NT$' + formattedValue;
        
        // Update field value
        $(this).val(formattedValue);
    });
});

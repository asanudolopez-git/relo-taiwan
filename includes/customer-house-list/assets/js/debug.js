/**
 * Debug script to verify JavaScript loading
 */

// Global debug message
console.log('DEBUG SCRIPT LOADED SUCCESSFULLY');

// Add a visible indicator on the page
jQuery(document).ready(function($) {
    $('body').append('<div id="js-debug-indicator" style="position: fixed; top: 10px; right: 10px; background: red; color: white; padding: 10px; z-index: 9999;">JavaScript Debug Active</div>');
    
    // Add click handlers to all buttons and links
    $('.property-action-buttons a').on('click', function(e) {
        e.preventDefault();
        console.log('Button clicked:', $(this).attr('id'));
        alert('Button clicked: ' + $(this).attr('id'));
        return false;
    });
    
    // Add click handler to More Info buttons
    $('.toggle-details').on('click', function(e) {
        e.preventDefault();
        console.log('More Info button clicked');
        alert('More Info button clicked');
        return false;
    });
});

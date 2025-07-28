jQuery(document).ready(function($) {
    // Media uploader
    var licenseUploader;
    
    // Upload license image
    $(document).on('click', '.upload-license-button', function(e) {
        e.preventDefault();
        
        var container = $(this).closest('.license-upload-container');
        var licenseInput = container.find('input[type="hidden"]');
        
        // If the media uploader instance exists, reopen it
        if (licenseUploader) {
            licenseUploader.open();
            return;
        }
        
        // Create a new media uploader instance
        licenseUploader = wp.media({
            title: 'Select Driver\'s License Image',
            button: {
                text: 'Use this image'
            },
            multiple: false,
            library: {
                type: ['image']
            }
        });
        
        // When an image is selected, handle the data
        licenseUploader.on('select', function() {
            var attachment = licenseUploader.state().get('selection').first().toJSON();
            
            // Set the image ID
            licenseInput.val(attachment.id);
            
            // Update the preview
            var previewHtml = '<div class="license-image">' +
                '<img src="' + (attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url) + '" alt="Driver\'s License">' +
                '</div>' +
                '<div class="license-info">' +
                '<span class="license-filename">' + attachment.filename + '</span>' +
                '<a href="' + attachment.url + '" target="_blank" class="license-view">View Image</a>' +
                '</div>' +
                '<a href="#" class="remove-license">Remove</a>';
            
            container.find('.license-preview').html(previewHtml);
        });
        
        // Open the uploader
        licenseUploader.open();
    });
    
    // Remove license image
    $(document).on('click', '.remove-license', function(e) {
        e.preventDefault();
        
        var container = $(this).closest('.license-upload-container');
        var licenseInput = container.find('input[type="hidden"]');
        
        // Clear the image ID
        licenseInput.val('');
        
        // Update the preview
        container.find('.license-preview').html('<div class="no-license">No license image uploaded</div>');
    });
});

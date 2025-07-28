jQuery(document).ready(function($) {
    // Media uploader
    var documentUploader;
    
    // Upload document
    $(document).on('click', '.upload-document-button', function(e) {
        e.preventDefault();
        
        var container = $(this).closest('.document-upload-container');
        var documentInput = container.find('input[type="hidden"]');
        
        // If the media uploader instance exists, reopen it
        if (documentUploader) {
            documentUploader.open();
            return;
        }
        
        // Create a new media uploader instance
        documentUploader = wp.media({
            title: 'Select Document',
            button: {
                text: 'Use this document'
            },
            multiple: false,
            library: {
                type: ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain']
            }
        });
        
        // When a document is selected, handle the data
        documentUploader.on('select', function() {
            var attachment = documentUploader.state().get('selection').first().toJSON();
            
            // Set the document ID
            documentInput.val(attachment.id);
            
            // Determine icon based on file type
            var fileIcon = 'dashicons-media-document';
            if (attachment.subtype === 'pdf') {
                fileIcon = 'dashicons-pdf';
            } else if (attachment.subtype === 'msword' || attachment.subtype === 'vnd.openxmlformats-officedocument.wordprocessingml.document') {
                fileIcon = 'dashicons-media-document';
            } else if (attachment.subtype === 'vnd.ms-excel' || attachment.subtype === 'vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                fileIcon = 'dashicons-media-spreadsheet';
            } else if (attachment.subtype === 'plain') {
                fileIcon = 'dashicons-media-text';
            }
            
            // Update the preview
            var previewHtml = '<div class="document-icon">' +
                '<span class="dashicons ' + fileIcon + '"></span>' +
                '</div>' +
                '<div class="document-info">' +
                '<span class="document-filename">' + attachment.filename + '</span>' +
                '<a href="' + attachment.url + '" target="_blank" class="document-view">View Document</a>' +
                '</div>' +
                '<a href="#" class="remove-document">Remove</a>';
            
            container.find('.document-preview').html(previewHtml);
        });
        
        // Open the uploader
        documentUploader.open();
    });
    
    // Remove document
    $(document).on('click', '.remove-document', function(e) {
        e.preventDefault();
        
        var container = $(this).closest('.document-upload-container');
        var documentInput = container.find('input[type="hidden"]');
        
        // Clear the document ID
        documentInput.val('');
        
        // Update the preview
        container.find('.document-preview').html('<div class="no-document">No document uploaded</div>');
    });
});

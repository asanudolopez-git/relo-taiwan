jQuery(document).ready(function($) {
    // Check if jQuery UI is loaded
    if (typeof $.ui === 'undefined') {
        console.error('jQuery UI is not loaded. Sortable will not work.');
        // Try to load it dynamically as a fallback
        var script = document.createElement('script');
        script.src = 'https://code.jquery.com/ui/1.13.2/jquery-ui.min.js';
        script.onload = function() {
            console.log('jQuery UI loaded dynamically');
            initSortable();
        };
        document.head.appendChild(script);
    } else {
        console.log('jQuery UI is loaded, version:', $.ui.version);
    }
    
    // Media uploader
    var mediaUploader;
    
    // Create modal for delete confirmation if it doesn't exist
    if ($('#gallery-delete-confirm').length === 0) {
        $('body').append(
            '<div id="gallery-delete-confirm" class="gallery-modal">' +
            '   <div class="gallery-modal-content">' +
            '       <h3>Confirm Deletion</h3>' +
            '       <p>Are you sure you want to delete this image?</p>' +
            '       <div class="gallery-modal-buttons">' +
            '           <button class="button cancel">Cancel</button>' +
            '           <button class="button button-primary confirm">Delete</button>' +
            '       </div>' +
            '   </div>' +
            '</div>'
        );
    }
    
    // Modal functionality
    var $modal = $('#gallery-delete-confirm');
    var currentImageToDelete = null;
    
    // Close modal on cancel
    $modal.find('.cancel').on('click', function() {
        $modal.hide();
        currentImageToDelete = null;
    });
    
    // Delete image on confirm
    $modal.find('.confirm').on('click', function() {
        if (currentImageToDelete) {
            currentImageToDelete.remove();
            $modal.hide();
            currentImageToDelete = null;
        }
    });
    
    // Function to add drag handles to images if they don't exist
    function addDragHandles() {
        $('.gallery-image').each(function() {
            var $image = $(this);
            if ($image.find('.image-drag-handle').length === 0) {
                $image.prepend('<div class="image-drag-handle dashicons dashicons-move"></div>');
            }
            
            // Replace text 'Remove' with trash icon if needed
            var $removeLink = $image.find('.remove-gallery-image');
            if ($removeLink.length && !$removeLink.hasClass('dashicons')) {
                var $newRemove = $('<a href="#" class="remove-gallery-image dashicons dashicons-trash" title="Remove Image"></a>');
                $removeLink.replaceWith($newRemove);
                
                // Attach click event to new trash icon
                $newRemove.on('click', function(e) {
                    e.preventDefault();
                    currentImageToDelete = $(this).closest('.gallery-image');
                    $modal.show();
                });
            }
        });
    }
    
    // Function to make gallery-images sortable
    function initSortable() {
        // Debug
        console.log('Trying to initialize sortable');
        console.log('jQuery UI version:', $.ui ? $.ui.version : 'Not loaded');
        console.log('Found gallery containers:', $('.gallery-images').length);
        
        // If jQuery UI is not loaded or no containers found, retry later
        if (!$.ui || !$.ui.sortable) {
            console.warn('jQuery UI Sortable not available yet. Will retry in 1 second.');
            setTimeout(initSortable, 1000);
            return;
        }
        
        if ($('.gallery-images').length === 0) {
            console.warn('No gallery containers found. Will retry in 1 second.');
            setTimeout(initSortable, 1000);
            return;
        }
        
        try {
            $('.gallery-images').each(function() {
                var $container = $(this);
                
                // Check if already initialized to prevent duplication
                if ($container.hasClass('ui-sortable')) {
                    $container.sortable('destroy');
                }
                
                // Find all images in this gallery
                var $images = $container.find('.gallery-image');
                if ($images.length <= 1) {
                    console.log('Not enough images to enable sorting');
                    return;
                }
                
                // Mark the container for debugging
                $container.addClass('sortable-initialized');
                
                // Force the addition of drag handles before initializing sortable
                $container.find('.gallery-image').each(function() {
                    if ($(this).find('.image-drag-handle').length === 0) {
                        $(this).prepend('<div class="image-drag-handle dashicons dashicons-move"></div>');
                    }
                });
                
                // Replace text 'Remove' with trash icon
                $container.find('.gallery-image').each(function() {
                    var $image = $(this);
                    var $removeLink = $image.find('.remove-gallery-image');
                    if ($removeLink.length && !$removeLink.hasClass('dashicons')) {
                        var $newRemove = $('<a href="#" class="remove-gallery-image dashicons dashicons-trash" title="Remove Image"></a>');
                        $removeLink.replaceWith($newRemove);
                    }
                });
                
                // Initialize sortable directly on the gallery-images container
                $container.sortable({
                    items: '.gallery-image',
                    cursor: 'move',
                    opacity: 0.7,
                    handle: '.image-drag-handle',
                    placeholder: 'sortable-placeholder',
                    forcePlaceholderSize: true,
                    tolerance: 'pointer',
                    helper: 'clone',
                    scroll: true,
                    scrollSpeed: 10,
                    containment: 'parent',
                    start: function(e, ui) {
                        ui.placeholder.height(ui.item.height());
                        ui.placeholder.width(ui.item.width());
                        ui.placeholder.addClass('gallery-image-placeholder');
                        console.log('Drag started');
                    },
                    stop: function(e, ui) {
                        console.log('Sorting stopped');
                        // Make sure the input order reflects the visual order
                        updateInputOrder($container);
                    },
                    update: function(e, ui) {
                        console.log('Order updated');
                        // Add visual feedback
                        $container.addClass('order-updated');
                        setTimeout(function() {
                            $container.removeClass('order-updated');
                        }, 1000);
                        // Update hidden inputs order
                        updateInputOrder($container);
                    }
                }).disableSelection();
                
                console.log('Sortable initialized on container', $container);
                
                // Trigger a click on handle to highlight it for the user
                setTimeout(function() {
                    $container.find('.image-drag-handle').first().css({
                        'transform': 'scale(1.2)',
                        'transition': 'transform 0.3s'
                    });
                    setTimeout(function() {
                        $container.find('.image-drag-handle').first().css({
                            'transform': 'scale(1)',
                        });
                    }, 600);
                }, 1000);
            });
            
            console.log('All sortable containers initialized successfully!');
        } catch (e) {
            console.error('Error initializing sortable:', e);
        }
    }
    
    // Initialize drag handles and sortable
    addDragHandles();
    setTimeout(initSortable, 500);
    
    // Update sortable on tab change in WordPress admin
    $(document).on('click', '.ui-tabs-anchor', function() {
        setTimeout(function() {
            addDragHandles();
            initSortable();
        }, 200);
    });
    
    // Function to update the order of hidden inputs to match visual order
    function updateInputOrder($container) {
        console.log('Updating input order based on visual order');
        
        // Get the field name from data attribute
        var fieldName = $container.data('field-name') || 'gallery_images[]';
        
        // Create new array of properly ordered inputs
        var imageIds = [];
        $container.find('.gallery-image').each(function() {
            var imageId = $(this).find('input[type="hidden"]').val();
            imageIds.push(imageId);
        });
        
        console.log('New image order:', imageIds);
        
        // Optional: Add a hidden field with the serialized order for debugging
        if ($('#image_order_debug').length === 0) {
            $('form#post').append('<input type="hidden" id="image_order_debug" name="image_order_debug" value="">');
        }
        $('#image_order_debug').val(JSON.stringify(imageIds));
    }
    
    // On form submit, ensure order is preserved
    $('form#post').on('submit', function() {
        $('.gallery-images').each(function() {
            console.log('Saving image order...');
            updateInputOrder($(this));
        });
    });
    
    // Add gallery image
    $(document).on('click', '.add-gallery-image', function(e) {
        e.preventDefault();
        
        var galleryContainer = $(this).closest('.gallery-images');
        var fieldName = galleryContainer.data('field-name') || 'gallery_images[]';
        
        // Store current order before adding new images
        var currentOrder = [];
        galleryContainer.find('.gallery-image input[type="hidden"]').each(function() {
            currentOrder.push($(this).val());
        });
        console.log('Current order before adding:', currentOrder);
        
        // If the media uploader instance exists, reopen it
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        // Create a new media uploader instance
        mediaUploader = wp.media({
            title: 'Select Images for Gallery',
            button: {
                text: 'Add to Gallery'
            },
            multiple: true
        });
        
        // When images are selected, handle the data
        mediaUploader.on('select', function() {
            var selection = mediaUploader.state().get('selection');
            var $container = galleryContainer; // Changed selector to use the gallery-images container directly
            
            selection.map(function(attachment) {
                attachment = attachment.toJSON();
                
                // Create image element with drag handle icon and trash icon
                var imageHtml = '<div class="gallery-image">\n' +
                               '<div class="image-drag-handle dashicons dashicons-move"></div>\n' +
                               '<img src="' + attachment.sizes.thumbnail.url + '" width="150" height="150" />\n' +
                               '<input type="hidden" name="' + fieldName + '" value="' + attachment.id + '" />\n' +
                               '<a href="#" class="remove-gallery-image dashicons dashicons-trash" title="Remove Image"></a>\n' +
                               '</div>';
                $container.append(imageHtml);
                
                // Add click handler for new remove button
                $container.find('.gallery-image:last-child .remove-gallery-image').on('click', function(e) {
                    e.preventDefault();
                    currentImageToDelete = $(this).closest('.gallery-image');
                    $modal.show();
                });
            });
            
            // Re-init sortable to include new images
            initSortable();
            console.log('New images added and sortable re-initialized');
        });
        
        mediaUploader.open();
    });
    
    // Show confirmation modal when clicking remove
    $(document).on('click', '.remove-gallery-image', function(e) {
        e.preventDefault();
        
        // Store the image to delete in the global variable
        currentImageToDelete = $(this).closest('.gallery-image');
        
        // Show the modal
        $modal.show();
        
        // Log for debugging
        console.log('Remove image clicked, showing confirmation modal');
    });
    
    // Re-init sortable when WordPress admin tabs change
    // This ensures it works when the gallery field is in a hidden tab initially
    $(document).on('click', '.postbox-header, .hndle', function() {
        setTimeout(function() {
            initSortable();
        }, 200);
    });
});

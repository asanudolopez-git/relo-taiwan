jQuery(document).ready(function($) {
    // Add CSS for animations
    $('<style>').text(`
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .pdf-notification {
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .pdf-notification .notice-dismiss {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            margin: 0;
            width: 20px;
            height: 20px;
        }
        .pdf-notification .notice-dismiss:before {
            content: '\f153';
            font-family: dashicons;
            font-size: 16px;
            color: #666;
        }
        .pdf-notification .notice-dismiss:hover:before {
            color: #000;
        }
    `).appendTo('head');
    
    var $pdfBtn = $('#generate-customer-house-list-pdf');
    if ($pdfBtn.length) {
        $pdfBtn.text('Generate PDF with Images');
    }
    // Agregar botón para PDF sin imágenes
    var $pdfBtnNoImg = $('<button id="generate-customer-house-list-pdf-noimg" type="button" class="button button-primary">Generate PDF (No Images)</button>');
    $pdfBtn.after($pdfBtnNoImg);
    
    // Function to handle PDF generation
    function generatePDF(action, buttonElement) {
        var postId = $pdfBtn.data('postid');
        var nonce = ClientHouseListPDF.nonce;
        
        // Store original button text
        var originalText = buttonElement.text();
        
        // Disable button and show loading with progress
        buttonElement.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> Generating PDF...');
        
        $.ajax({
            url: ClientHouseListPDF.ajax_url,
            type: 'GET',
            data: {
                action: action,
                post_id: postId,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    // Show success message with better styling
                    buttonElement.html('<span class="dashicons dashicons-yes"></span> PDF Generated!').css('background-color', '#00a32a');
                    
                    // Automatically download the PDF
                    var downloadLink = document.createElement('a');
                    downloadLink.href = response.data.download_url;
                    downloadLink.download = response.data.filename;
                    downloadLink.style.display = 'none';
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    document.body.removeChild(downloadLink);
                    
                    // Show success notification
                    showNotification('PDF generated and downloaded successfully!', 'success');
                    
                    // Refresh the page after a short delay to show the new PDF in the list
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showNotification('Error generating PDF: ' + (response.data || 'Unknown error'), 'error');
                }
            },
            error: function(xhr, status, error) {
                showNotification('Error generating PDF: ' + error, 'error');
            },
            complete: function() {
                // Re-enable button after delay if there was an error
                if (!$('.pdf-notification.success').length) {
                    setTimeout(function() {
                        buttonElement.prop('disabled', false).html(originalText).css('background-color', '');
                    }, 3000);
                }
            }
        });
    }
    
    // Function to show notifications
    function showNotification(message, type) {
        // Remove existing notifications
        $('.pdf-notification').remove();
        
        var notificationClass = type === 'success' ? 'notice-success' : 'notice-error';
        var iconClass = type === 'success' ? 'dashicons-yes-alt' : 'dashicons-warning';
        
        var notification = $('<div class="notice ' + notificationClass + ' pdf-notification ' + type + '" style="margin: 15px 0; padding: 12px 15px; display: flex; align-items: center;">' +
            '<span class="dashicons ' + iconClass + '" style="margin-right: 8px;"></span>' +
            '<span>' + message + '</span>' +
            '<button type="button" class="notice-dismiss" style="margin-left: auto;"><span class="screen-reader-text">Dismiss this notice.</span></button>' +
            '</div>');
        
        // Insert notification after the PDF buttons
        $pdfBtn.parent().after(notification);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            notification.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
        
        // Handle manual dismiss
        notification.find('.notice-dismiss').on('click', function() {
            notification.fadeOut(function() {
                $(this).remove();
            });
        });
    }
    
    $pdfBtn.on('click', function() {
        generatePDF('generate_customer_house_list_pdf', $(this));
    });
    
    $pdfBtnNoImg.on('click', function() {
        generatePDF('generate_customer_house_list_pdf_noimg', $(this));
    });
});

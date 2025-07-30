jQuery(document).ready(function($) {
    // Initialize datepicker
    $(".accommodation-date-field").datepicker({
        dateFormat: "yy-mm-dd",
        changeMonth: true,
        changeYear: true
    });
    
    // Load property info on page load if client is already selected
    var initialClientId = $("#customer_id").val();
    if (initialClientId) {
        loadClientLeaseInfo(initialClientId);
    }
    
    // Handle client selection changes
    $("#customer_id").on("change", function() {
        var clientId = $(this).val();
        var clientName = $(this).find("option:selected").text();
        
        if (clientId) {
            // Update accommodation title with client name
            updateAccommodationTitle(clientName);
            
            // Load client lease information
            loadClientLeaseInfo(clientId);
        } else {
            // Clear lease info if no client selected
            $("#client-lease-info").remove();
        }
    });
    
    function updateAccommodationTitle(clientName) {
        if (clientName && clientName !== "Select Client") {
            var postId = $("#post_ID").val();
            if (postId) {
                $.ajax({
                    url: accommodationAjax.ajaxurl,
                    type: "POST",
                    data: {
                        action: "update_accommodation_title",
                        post_id: postId,
                        client_name: clientName,
                        nonce: accommodationAjax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#title").val(response.data.title);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("Error updating title:", error);
                    }
                });
            }
        }
    }
    
    function loadClientLeaseInfo(clientId) {
        $.ajax({
            url: accommodationAjax.ajaxurl,
            type: "POST",
            data: {
                action: "get_client_lease_info",
                client_id: clientId,
                nonce: accommodationAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    displayLeaseInfo(response.data);
                } else {
                    console.log("AJAX Error:", response.data);
                }
            },
            error: function(xhr, status, error) {
                console.log("AJAX Error loading client lease information:", error);
                console.log("Status:", status);
                console.log("Response:", xhr.responseText);
            }
        });
    }
    
    function displayLeaseInfo(leaseData) {
        // Remove existing lease info
        $("#client-lease-info").remove();
        
        if (leaseData && leaseData.length > 0) {
            var leaseInfo = '<div id="client-lease-info" class="client-lease-info">';
            leaseInfo += '<h4>Client Lease & Property Information:</h4>';
            
            leaseData.forEach(function(lease, index) {
                leaseInfo += '<div style="margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">';
                leaseInfo += '<h5 style="margin-top: 0; color: #0073aa;">Lease: ' + lease.title + '</h5>';
                
                // Lease dates
                if (lease.start_date || lease.end_date) {
                    leaseInfo += '<div style="margin-bottom: 10px;">';
                    if (lease.start_date) {
                        leaseInfo += '<strong>Start Date:</strong> ' + lease.start_date + ' ';
                    }
                    if (lease.end_date) {
                        leaseInfo += '<strong>End Date:</strong> ' + lease.end_date;
                    }
                    leaseInfo += '</div>';
                }
                
                // Property information
                if (lease.property && lease.property.name) {
                    leaseInfo += '<h6 style="margin: 10px 0 5px 0; color: #333;">Property Details:</h6>';
                    leaseInfo += '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px; font-size: 0.9em;">';
                    
                    if (lease.property.name) {
                        leaseInfo += '<div><strong>Name:</strong> ' + lease.property.name + '</div>';
                    }
                    if (lease.property.rent) {
                        leaseInfo += '<div><strong>Rent:</strong> ' + lease.property.rent + '</div>';
                    }
                    if (lease.property.address) {
                        leaseInfo += '<div style="grid-column: 1 / -1;"><strong>Address:</strong> ' + lease.property.address + '</div>';
                    }
                    if (lease.property.chinese_address) {
                        leaseInfo += '<div style="grid-column: 1 / -1;"><strong>Chinese Address:</strong> ' + lease.property.chinese_address + '</div>';
                    }
                    if (lease.property.bedroom) {
                        leaseInfo += '<div><strong>Bedrooms:</strong> ' + lease.property.bedroom + '</div>';
                    }
                    if (lease.property.bathroom) {
                        leaseInfo += '<div><strong>Bathrooms:</strong> ' + lease.property.bathroom + '</div>';
                    }
                    if (lease.property.floor && lease.property.total_floor) {
                        leaseInfo += '<div><strong>Floor:</strong> ' + lease.property.floor + '/' + lease.property.total_floor + '</div>';
                    } else if (lease.property.floor) {
                        leaseInfo += '<div><strong>Floor:</strong> ' + lease.property.floor + '</div>';
                    }
                    if (lease.property.net_size) {
                        leaseInfo += '<div><strong>Size:</strong> ' + lease.property.net_size + ' ping</div>';
                    }
                    if (lease.property.square_meters) {
                        leaseInfo += '<div><strong>Square Meters:</strong> ' + lease.property.square_meters + ' m²</div>';
                    }
                    if (lease.property.property_type) {
                        leaseInfo += '<div><strong>Type:</strong> ' + lease.property.property_type.charAt(0).toUpperCase() + lease.property.property_type.slice(1) + '</div>';
                    }
                    if (lease.property.metro_line && lease.property.station) {
                        leaseInfo += '<div><strong>Metro:</strong> ' + lease.property.station + ' (' + lease.property.metro_line + ')</div>';
                    }
                    if (lease.property.parking && lease.property.parking !== "contact_agent") {
                        leaseInfo += '<div><strong>Parking:</strong> ' + lease.property.parking.charAt(0).toUpperCase() + lease.property.parking.slice(1) + '</div>';
                    }
                    if (lease.property.building_age) {
                        leaseInfo += '<div><strong>Building Age:</strong> ' + lease.property.building_age + ' years</div>';
                    }
                    
                    // Amenities
                    var amenities = [];
                    if (lease.property.gym === "yes") amenities.push("Gym");
                    if (lease.property.swimming_pool === "yes") amenities.push("Swimming Pool");
                    if (amenities.length > 0) {
                        leaseInfo += '<div style="grid-column: 1 / -1;"><strong>Amenities:</strong> ' + amenities.join(', ') + '</div>';
                    }
                    
                    leaseInfo += '</div>';
                }
                
                leaseInfo += '</div>';
            });
            
            leaseInfo += '</div>';
            
            // Insert after the customer selection field
            $("#customer_id").closest(".accommodation-meta-box-field").after(leaseInfo);
        }
    }
});

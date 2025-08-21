<?php
/**
 * Template: Single Assignee (Customer)
 * Shows public-facing profile for an assignee with sections:
 *  - Basic Information
 *  - Property Chosen (Lease Summary)
 *  - House List PDFs
 *  - Settling-in Services
 *  - Extension
 *  - Departure
 *
 * @package Houses Theme
 */

// Add custom styles for PDF section
function add_customer_pdf_styles() {
    echo '<style>
    /* Accordion styles */
    /* Scoped variables for a cleaner look */
    .assignee-page{
        --acc-bg:#ffffff;
        --acc-bg-collapsed:#fafafa;
        --acc-border:#d9d2c6;
        --acc-text:#2d2a26;
        --acc-hover-bg:#fffdf8;
        --acc-shadow:0 6px 14px rgba(0,0,0,.06);
        --acc-arrow:#6b7280;
    }

    /* Header */
    .assignee-page .accordion-header{
        width:100%;
        box-sizing:border-box;
        cursor:pointer;
        padding:16px 18px;
        background-color:var(--acc-bg);
        border:1px solid var(--acc-border);
        margin:0 0 8px 0;
        font-size:16px;
        font-weight:700;
        color:var(--acc-text);
        display:flex;
        justify-content:flex-start;
        align-items:center;
        gap:10px;
        border-radius:10px;
        box-shadow:var(--acc-shadow);
        transition:background-color .25s ease,border-color .25s ease,box-shadow .25s ease,color .25s ease;
        text-transform:uppercase;
        letter-spacing:.02em;
        position: relative;
    }

    .assignee-page .accordion-header:hover{
        background-color:var(--acc-hover-bg);
    }

    .assignee-page .accordion-header::after{
        content:"▾";
        margin-left:auto;
        width:26px;
        height:26px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-size:12px;
        color:var(--acc-arrow);
        background:#fff;
        border:1px solid var(--acc-border);
        border-radius:50%;
        box-shadow:inset 0 1px 1px rgba(0,0,0,.04);
        transition:transform .25s ease, background-color .25s ease, color .25s ease, border-color .25s ease;
        right: 20px;
        position: absolute;
        top: 15px;
    }

    .assignee-page .accordion-header.collapsed{
        background-color:var(--acc-bg-collapsed);
    }

    .assignee-page .accordion-header.collapsed::after{
        content:"▸";
        transform:none;
    }

    /* Content with smooth height animation */
    .assignee-page .accordion-content{
        padding:16px;
        border:1px solid var(--acc-border);
        border-radius:10px;
        background:#fff;
        box-shadow:var(--acc-shadow);
        margin-bottom:14px;
        overflow:hidden;
        max-height:0; /* JS sets actual height when opened */
        opacity:0;
        transform:translateY(-2px);
        transition:max-height .35s ease, opacity .25s ease, transform .25s ease;
    }

    .assignee-page .accordion-content:not(.collapsed){
        opacity:1;
        transform:translateY(0);
    }

    .assignee-page .accordion-content.collapsed{
        padding-top:0;
        padding-bottom:0;
        margin-bottom:0;
        border-width:0;
    }
    
    .assignee-page .house-list-pdfs .house-list-pdf-group,
    .assignee-page .documents .house-list-pdf-group {
        margin-bottom: 30px;
        padding: 20px;
        background: #ffffff;
        border-radius: 10px;
        border: 1px solid var(--acc-border);
        box-shadow: var(--acc-shadow);
    }
    
    .assignee-page .house-list-pdfs h3,
    .assignee-page .documents h3 {
        margin-top: 0;
        color: #23282d;
        border-bottom: 1px solid var(--acc-border);
        padding-bottom: 10px;
    }
    
    .pdf-item {
        display: flex;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }
    
    .pdf-item:last-child {
        border-bottom: none;
    }
    
    .pdf-item .dashicons {
        color: #0073aa;
        margin-right: 10px;
        font-size: 20px;
    }
    
    .pdf-item a {
        text-decoration: none;
        color: #0073aa;
        font-weight: 500;
        margin-right: 10px;
    }
    
    .pdf-item a:hover {
        text-decoration: underline;
    }
    
    .pdf-type.with_images {
        color: #0073aa;
        font-weight: bold;
    }
    
    .pdf-type.no_images {
        color: #00a32a;
        font-weight: bold;
    }
    
    .pdf-date {
        color: #666;
        font-size: 14px;
        margin-left: auto;
    }
    
    /* Property card styles */
    .property-card {
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 5px;
        overflow: hidden;
    }
    
    .property-card:last-child {
        margin-bottom: 0;
    }
    
    .property-card .property-title {
        cursor: pointer;
        padding: 15px;
        background-color: #f8f9fa;
        margin: 0;
        font-size: 16px;
        font-weight: bold;
        color: #23282d;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
        border-left: 4px solid #0073aa;
        position: relative;
    }
    
    .property-card .property-title:hover {
        background-color: #e8f4f8;
        border-left-color: #005a87;
    }
    
    .property-card .property-title .title-text {
        flex: 1;
        margin-right: 10px;
    }
    
    .property-card .property-title .title-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .property-card .property-title .accordion-arrow {
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: var(--acc-arrow);
        background: #fff;
        border: 1px solid var(--acc-border);
        border-radius: 50%;
        box-shadow: inset 0 1px 1px rgba(0,0,0,.04);
        transition: background-color .25s ease, color .25s ease, border-color .25s ease;
    }
    
    .property-card .property-details {
        padding: 15px;
        border-top: 1px solid #ddd;
        display: block;
    }
    
    .property-card .property-details.collapsed{
        display: none;
    }
    
    .property-card .edit-link {
        text-decoration: none;
        color: #666;
        cursor: pointer;
        transition: all 0.3s ease;
        padding: 5px;
        border-radius: 3px;
        display: flex;
        align-items: center;
        background-color: rgba(255, 255, 255, 0.7);
    }
    
    .property-card .edit-link:hover {
        color: #0073aa;
        background-color: rgba(255, 255, 255, 1);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .property-card .edit-link .dashicons {
        font-size: 16px;
        opacity: 0.8;
        transition: opacity 0.3s ease;
    }
    
    .property-card .edit-link:hover .dashicons {
        opacity: 1;
    }
    </style>';
}
add_action('wp_head', 'add_customer_pdf_styles');

/**
 * Add JavaScript for accordion functionality
 */
function add_customer_accordion_script() {
    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Get all accordion headers
        const accordionHeaders = document.querySelectorAll(".accordion-header");
        
        // Add click event to each header with smooth height animation
        accordionHeaders.forEach(function(header) {
            const content = header.nextElementSibling;
            
            // Initialize current height based on collapsed state
            if (content) {
                if (content.classList.contains("collapsed")) {
                    content.style.maxHeight = "0px";
                } else {
                    content.style.maxHeight = content.scrollHeight + "px";
                }
                // Cleanup inline height after expand to allow natural resizing
                content.addEventListener("transitionend", function(e) {
                    if (e.propertyName !== "max-height") return;
                    if (!content.classList.contains("collapsed")) {
                        content.style.maxHeight = "none";
                    }
                });
            }
            
            header.addEventListener("click", function() {
                if (!content) return;
                const isCollapsed = header.classList.contains("collapsed");
                if (isCollapsed) {
                    header.classList.remove("collapsed");
                    content.classList.remove("collapsed");
                    // Set exact height to animate open
                    content.style.maxHeight = content.scrollHeight + "px";
                } else {
                    // Set current height first to enable transition to 0
                    content.style.maxHeight = content.scrollHeight + "px";
                    requestAnimationFrame(function() {
                        header.classList.add("collapsed");
                        content.classList.add("collapsed");
                        content.style.maxHeight = "0px";
                    });
                }
            });
        });
        
        // Handle property card accordions (simple display toggle + arrow text)
        const propertyTitles = document.querySelectorAll(".property-card .property-title");
        propertyTitles.forEach(function(title) {
            const details = title.nextElementSibling;
            const arrow = title.querySelector(".accordion-arrow");
            // Initialize collapsed state
            title.classList.add("collapsed");
            if (details && details.classList.contains("property-details")) {
                details.classList.add("collapsed");
            }
            if (arrow) arrow.textContent = "▸";

            title.addEventListener("click", function(event) {
                if (event.target.closest(".edit-link")) return;
                if (!details || !details.classList.contains("property-details")) return;
                const isCollapsed = title.classList.contains("collapsed");
                if (isCollapsed) {
                    title.classList.remove("collapsed");
                    details.classList.remove("collapsed");
                    if (arrow) arrow.textContent = "▾";
                } else {
                    title.classList.add("collapsed");
                    details.classList.add("collapsed");
                    if (arrow) arrow.textContent = "▸";
                }
            });
        });
    });
    </script>';
}
add_action('wp_head', 'add_customer_accordion_script');

/**
 * Get complete property details including gallery
 */
function get_complete_property_details($property_id) {
    // Validate property ID
    if (!$property_id || !is_numeric($property_id)) {
        return null;
    }
    
    $property = get_post($property_id);
    if (!$property || $property->post_type !== 'property' || $property->post_status !== 'publish') {
        return null;
    }
    
    // Get all meta data in one query for better performance
    $meta_keys = array(
        'address_english', 'address_chinese', 'property_type', 'bedrooms', 'bathrooms',
        'size_sqm', 'floor', 'total_floors', 'rent_price', 'deposit', 'mrt', 'mrt_distance',
        'furnished', 'parking', 'pets_allowed', 'balcony', 'elevator', 'amenities', 'gallery_images', 
        'rent', 'tax_included', 'management_fee_included', 'property_post_id', 'metro_line', 'station', 
        'address', 'zip_code', 'chinese_address', 'bedroom', 'bathroom', 'floor', 'total_floor', 'building_age', 'net_size', 
        'square_meters', 'property_type', 'parking', 'gym', 'swimming_pool', 'notes', 'gallery_images', 'address_english', 
        'bedrooms', 'bathrooms', 'total_floors', 'size_sqm', 'rent_price', 'mrt', 'amenities'
    );
    
    $meta_data = array();
    foreach ($meta_keys as $key) {
        $meta_data[$key] = get_post_meta($property_id, $key, true);
    }
    
    // Validate and sanitize gallery images
    $gallery_images = $meta_data['gallery_images'];
    if (!empty($gallery_images)) {
        if (!is_array($gallery_images)) {
            $gallery_images = explode(',', $gallery_images);
        }
        $gallery_images = array_filter(array_map('absint', $gallery_images));
        // Verify that images actually exist
        $gallery_images = array_filter($gallery_images, function($img_id) {
            return wp_attachment_is_image($img_id);
        });
    } else {
        $gallery_images = array();
    }
    
    $details = array(
        'id' => $property_id,
        'title' => get_the_title($property_id),
        'content' => apply_filters('the_content', $property->post_content),
        // Basic Info fields from meta-boxes
        'rent' => sanitize_text_field($meta_data['rent'] ?? ''),
        'tax_included' => sanitize_text_field($meta_data['tax_included'] ?? ''),
        'management_fee_included' => sanitize_text_field($meta_data['management_fee_included'] ?? ''),
        'property_post_id' => sanitize_text_field($meta_data['property_post_id'] ?? ''),
        'metro_line' => sanitize_text_field($meta_data['metro_line'] ?? ''),
        'station' => sanitize_text_field($meta_data['station'] ?? ''),
        'address' => sanitize_text_field($meta_data['address'] ?? ''),
        'zip_code' => sanitize_text_field($meta_data['zip_code'] ?? ''),
        'chinese_address' => sanitize_text_field($meta_data['chinese_address'] ?? ''),
        'bedroom' => absint($meta_data['bedroom'] ?? 0),
        'bathroom' => absint($meta_data['bathroom'] ?? 0),
        'floor' => absint($meta_data['floor'] ?? 0),
        'total_floor' => absint($meta_data['total_floor'] ?? 0),
        'building_age' => absint($meta_data['building_age'] ?? 0),
        'net_size' => floatval($meta_data['net_size'] ?? 0),
        'square_meters' => sanitize_text_field($meta_data['square_meters'] ?? ''),
        'property_type' => sanitize_text_field($meta_data['property_type'] ?? ''),
        'parking' => sanitize_text_field($meta_data['parking'] ?? ''),
        // Details fields from meta-boxes
        'gym' => sanitize_text_field($meta_data['gym'] ?? ''),
        'swimming_pool' => sanitize_text_field($meta_data['swimming_pool'] ?? ''),
        'notes' => sanitize_textarea_field($meta_data['notes'] ?? ''),
        'gallery_images' => $gallery_images,
        // Legacy field mappings for backward compatibility
        'address_english' => sanitize_text_field($meta_data['address'] ?? ''),
        'bedrooms' => absint($meta_data['bedroom'] ?? 0),
        'bathrooms' => absint($meta_data['bathroom'] ?? 0),
        'total_floors' => absint($meta_data['total_floor'] ?? 0),
        'size_sqm' => floatval($meta_data['net_size'] ?? 0),
        'rent_price' => sanitize_text_field($meta_data['rent'] ?? ''),
        'mrt' => sanitize_text_field($meta_data['station'] ?? ''),
        'amenities' => sanitize_textarea_field($meta_data['notes'] ?? ''),
    );
    
    return $details;
}

get_header();

while (have_posts()) : the_post();
    $customer_id = get_the_ID();

    /* --------------------------------------------------
     * Basic Information
     * -------------------------------------------------- */
    $title              = get_post_meta($customer_id, 'title', true);
    $first_name         = get_post_meta($customer_id, 'first_name', true);
    $last_name          = get_post_meta($customer_id, 'last_name', true);
    $full_name          = trim($title . ' ' . $first_name . ' ' . $last_name);

    $nationality        = get_post_meta($customer_id, 'nationality', true);
    $email              = get_post_meta($customer_id, 'email', true);
    $phone              = get_post_meta($customer_id, 'phone', true);
    $assignment_date    = get_post_meta($customer_id, 'assignment_date', true);
    $assignment_period  = get_post_meta($customer_id, 'assignment_period', true);
    $budget             = get_post_meta($customer_id, 'budget', true);

    $budget_formatted = '';
    if ($budget !== '') {
        $numeric = floatval(preg_replace('/[^0-9.]/', '', $budget));
        if ($numeric) {
            $budget_formatted = 'NT$ ' . number_format($numeric);
        } else {
            $budget_formatted = $budget;
        }
    }

    $preferred_location = get_post_meta($customer_id, 'preferred_location', true);
    $family_size        = get_post_meta($customer_id, 'family_size', true);
    $office_address     = get_post_meta($customer_id, 'office_address', true);
    $company_id         = get_post_meta($customer_id, 'company_id', true);
    $company_name       = $company_id ? get_the_title($company_id) : '';

    /* --------------------------------------------------
     * Lease / Property Chosen
     * -------------------------------------------------- */
    $lease_args = array(
        'post_type'      => 'client_lease',
        'posts_per_page' => 1,
        'meta_query'     => array(
            array(
                'key'   => 'client_id',
                'value' => $customer_id,
            ),
        ),
        'orderby'        => 'meta_value', // by start_date
        'meta_key'       => 'start_date',
        'order'          => 'DESC',
        'post_status'    => 'publish',
    );
    $leases = get_posts($lease_args);
    $lease  = $leases ? $leases[0] : null;
    $lease_data = array();
    $lease_property_details = null;
    if ($lease) {
        $lease_id = $lease->ID;
        $lease_data = array(
            'start_date'               => get_post_meta($lease_id, 'start_date', true),
            'end_date'                 => get_post_meta($lease_id, 'end_date', true),
            'monthly_rent'             => get_post_meta($lease_id, 'monthly_rent', true),
            'deposit'                  => get_post_meta($lease_id, 'deposit', true),
            'property_id'              => get_post_meta($lease_id, 'property_id_hidden', true),
            'extension_authorized_date'=> get_post_meta($lease_id, 'extension_authorized_date', true),
            'extension_period'         => get_post_meta($lease_id, 'extension_period', true),
            'extension_signed_date'    => get_post_meta($lease_id, 'extension_signed_date', true),
            'contract_attachment'      => get_post_meta($lease_id, 'contract_attachment', true),
            'status'                   => get_post_meta($lease_id, 'status', true),
            'notes'                    => get_post_meta($lease_id, 'notes', true),
        );
        $lease_data['property_title'] = $lease_data['property_id'] ? get_the_title($lease_data['property_id']) : '';
        
        // Get complete property details for the leased property
        if ($lease_data['property_id']) {
            $lease_property_details = get_complete_property_details($lease_data['property_id']);
        }
    }

    /* --------------------------------------------------
     * Customer House List Properties
     * -------------------------------------------------- */
    $house_list_properties = array();
    $house_list_args = array(
        'post_type'      => 'customer-house-list',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => 'selected_customer',
                'value' => $customer_id,
            ),
        ),
        'post_status'    => 'publish',
    );
    $house_lists = get_posts($house_list_args);
    
    if ($house_lists) {
        foreach ($house_lists as $house_list) {
            $property_list = get_post_meta($house_list->ID, 'property_list', true);
            if (!empty($property_list) && is_array($property_list)) {
                foreach ($property_list as $property_id) {
                    if (get_post($property_id) && !in_array($property_id, array_column($house_list_properties, 'id'))) {
                        $house_list_properties[] = get_complete_property_details($property_id);
                    }
                }
            }
        }
    }



    /* --------------------------------------------------
     * Settling-in Services
     * -------------------------------------------------- */
    // Get settling services (accommodation records) for this specific lease ID only
    $settling_services = get_posts(array(
        'post_type'      => 'accommodation',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => 'client_lease_id',
                'value'   => $lease_id,
                'compare' => '=',
                'type'    => 'NUMERIC'
            ),
        ),
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC'
    ));

    /* --------------------------------------------------
     * Departure Services
     * -------------------------------------------------- */
    $departure_services = get_posts(array(
        'post_type'      => 'departure_service',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => 'client_lease_id',
                'value'   => $lease_id,
                'compare' => '=',
                'type'    => 'NUMERIC'
            ),
        ),
        'post_status'    => 'publish',
    ));

    // Debug information (remove in production)
    if (current_user_can('administrator')) {
        echo "<!-- DEBUG: Customer ID: $customer_id -->";
        echo "<!-- DEBUG: Settling Services Count: " . count($settling_services) . " -->";
        echo "<!-- DEBUG: Departure Services Count: " . count($departure_services) . " -->";
        
        // Debug settling services
        if (!empty($settling_services)) {
            echo "<!-- DEBUG: Settling Services: ";
            foreach ($settling_services as $service) {
                echo get_the_title($service) . " (ID: {$service->ID}), ";
            }
            echo " -->";
        }
        
        // Debug departure services
        if (!empty($departure_services)) {
            echo "<!-- DEBUG: Departure Services: ";
            foreach ($departure_services as $service) {
                echo get_the_title($service) . " (ID: {$service->ID}), ";
            }
            echo " -->";
        }
    }

    ?>

    <section class="assignee-page">
        <div class="container">
            <h1 class="assignee-name"><?php echo esc_html($full_name); ?></h1>

            <!-- Basic Information -->
            <div class="section basic-info">
                <h2 class="accordion-header collapsed"><?php _e('Basic Information', 'houses-theme'); ?></h2>
                <div class="accordion-content collapsed">
                    <div class="info-grid">
                        <?php if ($company_name) : ?><li><strong><?php _e('Company', 'houses-theme'); ?>:</strong> <?php echo esc_html($company_name); ?></li><?php endif; ?>
                        <?php if ($nationality)  : ?><li><strong><?php _e('Nationality', 'houses-theme'); ?>:</strong> <?php echo esc_html($nationality); ?></li><?php endif; ?>
                        <?php if ($email)        : ?><li><strong><?php _e('Email', 'houses-theme'); ?>:</strong> <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></li><?php endif; ?>
                        <?php if ($phone)        : ?><li><strong><?php _e('Phone', 'houses-theme'); ?>:</strong> <?php echo esc_html($phone); ?></li><?php endif; ?>
                        <?php if ($assignment_date): ?><li><strong><?php _e('Assignment Date', 'houses-theme'); ?>:</strong> <?php echo esc_html($assignment_date); ?></li><?php endif; ?>
                        <?php if ($assignment_period): ?><li><strong><?php _e('Assignment Period', 'houses-theme'); ?>:</strong> <?php echo esc_html($assignment_period); ?></li><?php endif; ?>
                        <?php if ($budget_formatted): ?><li><strong><?php _e('Budget', 'houses-theme'); ?>:</strong> <?php echo esc_html($budget_formatted); ?></li><?php endif; ?>
                        <?php if ($preferred_location): ?><li><strong><?php _e('Preferred Location', 'houses-theme'); ?>:</strong> <?php echo esc_html($preferred_location); ?></li><?php endif; ?>
                        <?php if ($family_size)  : ?><li><strong><?php _e('Family Size', 'houses-theme'); ?>:</strong> <?php echo esc_html($family_size); ?></li><?php endif; ?>
                        <?php if ($office_address): ?><li><strong><?php _e('Office Address', 'houses-theme'); ?>:</strong> <?php echo esc_html($office_address); ?></li><?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Property Chosen (Lease Details) -->
            <?php if ($lease && $lease_data['property_id'] && $lease_property_details) : ?>
                <div class="section property-chosen">
                    <h2 class="accordion-header collapsed"><?php _e('Property Chosen (Current Lease)', 'houses-theme'); ?></h2>
                    <div class="accordion-content collapsed">
                        <!-- Lease Information -->
                        <div class="lease-info">
                            <h3><a href="<?php echo esc_url(admin_url('post.php?post=' . $lease_id . '&action=edit')); ?>" target="_blank" title="Click para editar este lease" style="text-decoration: none; color: inherit; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.color='#0073aa'; this.querySelector('.dashicons').style.opacity='1';" onmouseout="this.style.color='inherit'; this.querySelector('.dashicons').style.opacity='0.7';"><?php _e('Lease Details', 'houses-theme'); ?> <span class="dashicons dashicons-edit" style="font-size: 16px; opacity: 0.7; margin-left: 8px; color: #0073aa;"></span></a></h3>
                            <ul>
                                <?php if ($lease_data['start_date'])    : ?><li><strong><?php _e('Start Date', 'houses-theme'); ?>:</strong> <?php echo esc_html($lease_data['start_date']); ?></li><?php endif; ?>
                                <?php if ($lease_data['end_date'])      : ?><li><strong><?php _e('End Date', 'houses-theme'); ?>:</strong> <?php echo esc_html($lease_data['end_date']); ?></li><?php endif; ?>
                                <?php if ($lease_data['monthly_rent'])  : ?><li><strong><?php _e('Monthly Rent', 'houses-theme'); ?>:</strong> <?php echo esc_html($lease_data['monthly_rent']); ?></li><?php endif; ?>
                                <?php if ($lease_data['deposit'])       : ?><li><strong><?php _e('Deposit', 'houses-theme'); ?>:</strong> <?php echo esc_html($lease_data['deposit']); ?></li><?php endif; ?>
                                <?php if ($lease_data['notes'])        : ?><li><strong><?php _e('Notes', 'houses-theme'); ?>:</strong> <?php echo esc_html($lease_data['notes']); ?></li><?php endif; ?>
                                <?php if ($lease_data['status'])       : ?><li><strong><?php _e('Status', 'houses-theme'); ?>:</strong> <?php echo esc_html($lease_data['status']); ?></li><?php endif; ?>
                                <?php if ($lease_data['contract_attachment']) : ?>
                                    <?php 
                                    $attachment_id = $lease_data['contract_attachment'];
                                    $attachment_url = wp_get_attachment_url($attachment_id);
                                    $attachment_title = get_the_title($attachment_id);
                                    $attachment_filename = basename(get_attached_file($attachment_id));
                                    ?>
                                    <li><strong><?php _e('Contract', 'houses-theme'); ?>:</strong> 
                                        <a href="<?php echo esc_url($attachment_url); ?>" target="_blank" style="color: #0073aa; text-decoration: none;" title="Click para descargar el contrato">
                                            <span class="dashicons dashicons-media-document" style="font-size: 16px; vertical-align: middle; margin-right: 5px;"></span>
                                            <?php echo esc_html($attachment_filename ? $attachment_filename : $attachment_title); ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <!-- Extension -->
                        <?php if ($lease && ($lease_data['extension_authorized_date'] || $lease_data['extension_signed_date'] || $lease_data['extension_period'])) : ?>
                            <div class="section extension">
                                <h2><?php _e('Extension', 'houses-theme'); ?></h2>
                                <ul>
                                    <?php if ($lease_data['extension_authorized_date']): ?><li><strong><?php _e('Authorized Date', 'houses-theme'); ?>:</strong> <?php echo esc_html($lease_data['extension_authorized_date']); ?></li><?php endif; ?>
                                    <?php if ($lease_data['extension_period']): ?><li><strong><?php _e('Period (months)', 'houses-theme'); ?>:</strong> <?php echo esc_html($lease_data['extension_period']); ?></li><?php endif; ?>
                                    <?php if ($lease_data['extension_signed_date']): ?><li><strong><?php _e('Signed Date', 'houses-theme'); ?>:</strong> <?php echo esc_html($lease_data['extension_signed_date']); ?></li><?php endif; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Property Details -->
                        <div class="property-details">
                            <h3><a href="<?php echo esc_url(admin_url('post.php?post=' . $lease_property_details['id'] . '&action=edit')); ?>" target="_blank" title="Click para editar esta propiedad" style="text-decoration: none; color: inherit; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.color='#0073aa'; this.querySelector('.dashicons').style.opacity='1';" onmouseout="this.style.color='inherit'; this.querySelector('.dashicons').style.opacity='0.7';"><?php echo esc_html($lease_property_details['title']); ?> <span class="dashicons dashicons-edit" style="font-size: 16px; opacity: 0.7; margin-left: 8px; color: #0073aa;"></span></a></h3>
                            
                            <!-- Property Gallery -->
                            <?php if (!empty($lease_property_details['gallery_images']) && is_array($lease_property_details['gallery_images'])) : ?>
                                <div class="property-gallery">
                                    <div class="gallery-grid">
                                        <?php foreach ($lease_property_details['gallery_images'] as $image_id) : ?>
                                            <?php $image_url = wp_get_attachment_image_url($image_id, 'medium'); ?>
                                            <?php $image_full = wp_get_attachment_image_url($image_id, 'full'); ?>
                                            <?php if ($image_url) : ?>
                                                <div class="gallery-item">
                                                    <a href="<?php echo esc_url($image_full); ?>" data-lightbox="lease-property-<?php echo $lease_property_details['id']; ?>">
                                                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($lease_property_details['title']); ?>">
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Property Information -->
                            <div class="property-info">
                                <!-- Basic Information Section -->
                                <div class="property-section">
                                    <h4 class="section-title"><?php _e('📍 Location & Address', 'houses-theme'); ?></h4>
                                    <div class="info-grid">
                                        <?php if ($lease_property_details['address']) : ?>
                                            <div class="info-item">
                                                <strong><?php _e('Address (English)', 'houses-theme'); ?>:</strong>
                                                <span><?php echo esc_html($lease_property_details['address']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($lease_property_details['chinese_address']) : ?>
                                            <div class="info-item">
                                                <strong><?php _e('Address (Chinese)', 'houses-theme'); ?>:</strong>
                                                <span><?php echo esc_html($lease_property_details['chinese_address']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($lease_property_details['zip_code']) : ?>
                                            <div class="info-item">
                                                <strong><?php _e('ZIP Code', 'houses-theme'); ?>:</strong>
                                                <span><?php echo esc_html($lease_property_details['zip_code']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($lease_property_details['metro_line'] || $lease_property_details['station']) : ?>
                                            <div class="info-item">
                                                <strong><?php _e('MRT Station', 'houses-theme'); ?>:</strong>
                                                <span>
                                                    <?php if ($lease_property_details['metro_line']) : ?>
                                                        <span class="metro-line"><?php echo esc_html($lease_property_details['metro_line']); ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($lease_property_details['station']) : ?>
                                                        <?php echo esc_html($lease_property_details['station']); ?>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Property Details Section -->
                                <div class="property-section">
                                    <h4 class="section-title"><?php _e('🏠 Property Details', 'houses-theme'); ?></h4>
                                    <div class="info-grid">
                                        <?php if ($lease_property_details['property_type']) : ?>
                                            <div class="info-item">
                                                <strong><?php _e('Property Type', 'houses-theme'); ?>:</strong>
                                                <span class="property-type"><?php echo esc_html(ucfirst($lease_property_details['property_type'])); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($lease_property_details['bedroom']) : ?>
                                            <div class="info-item">
                                                <strong><?php _e('Bedrooms', 'houses-theme'); ?>:</strong>
                                                <span class="bedroom-count"><?php echo esc_html($lease_property_details['bedroom']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($lease_property_details['bathroom']) : ?>
                                            <div class="info-item">
                                                <strong><?php _e('Bathrooms', 'houses-theme'); ?>:</strong>
                                                <span class="bathroom-count"><?php echo esc_html($lease_property_details['bathroom']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($lease_property_details['net_size']) : ?>
                                            <div class="info-item">
                                                <strong><?php _e('Net Size', 'houses-theme'); ?>:</strong>
                                                <span class="size"><?php echo esc_html($lease_property_details['net_size']); ?> m²</span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($lease_property_details['square_meters']) : ?>
                                            <div class="info-item">
                                                <strong><?php _e('Square Meters', 'houses-theme'); ?>:</strong>
                                                <span><?php echo esc_html($lease_property_details['square_meters']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($lease_property_details['floor'] || $lease_property_details['total_floor']) : ?>
                                            <div class="info-item">
                                                <strong><?php _e('Floor', 'houses-theme'); ?>:</strong>
                                                <span class="floor-info">
                                                    <?php if ($lease_property_details['floor']) echo esc_html($lease_property_details['floor']); ?>
                                                    <?php if ($lease_property_details['total_floor']) echo ' / ' . esc_html($lease_property_details['total_floor']); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($lease_property_details['building_age']) : ?>
                                            <div class="info-item">
                                                <strong><?php _e('Building Age', 'houses-theme'); ?>:</strong>
                                                <span><?php echo esc_html($lease_property_details['building_age']); ?> <?php _e('years', 'houses-theme'); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Financial Information Section -->
                                <div class="property-section">
                                    <h4 class="section-title"><?php _e('💰 Financial Information', 'houses-theme'); ?></h4>
                                    <div class="info-grid">
                                        <?php if ($lease_property_details['rent']) : ?>
                                            <div class="info-item">
                                                <strong><?php _e('Rent', 'houses-theme'); ?>:</strong>
                                                <span class="rent-price">NT$ <?php echo esc_html($lease_property_details['rent']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($lease_property_details['tax_included']) : ?>
                                            <div class="info-item">
                                                <strong><?php _e('Tax Included', 'houses-theme'); ?>:</strong>
                                                <span class="<?php echo $lease_property_details['tax_included'] === 'yes' ? 'status-yes' : 'status-no'; ?>">
                                                    <?php echo $lease_property_details['tax_included'] === 'yes' ? __('Yes', 'houses-theme') : __('No', 'houses-theme'); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($lease_property_details['management_fee_included']) : ?>
                                            <div class="info-item">
                                                <strong><?php _e('Management Fee Included', 'houses-theme'); ?>:</strong>
                                                <span class="<?php echo $lease_property_details['management_fee_included'] === 'yes' ? 'status-yes' : 'status-no'; ?>">
                                                    <?php echo $lease_property_details['management_fee_included'] === 'yes' ? __('Yes', 'houses-theme') : __('No', 'houses-theme'); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Amenities & Features Section -->
                                <div class="property-section">
                                    <h4 class="section-title"><?php _e('🏋️ Amenities & Features', 'houses-theme'); ?></h4>
                                    <div class="info-grid">
                                        <?php if ($lease_property_details['parking']) : ?>
                                            <div class="info-item">
                                                <strong><?php _e('Parking', 'houses-theme'); ?>:</strong>
                                                <span class="parking-status">
                                                    <?php 
                                                    switch($lease_property_details['parking']) {
                                                        case 'yes':
                                                            echo '<span class="status-yes">' . __('Available', 'houses-theme') . '</span>';
                                                            break;
                                                        case 'no':
                                                            echo '<span class="status-no">' . __('Not Available', 'houses-theme') . '</span>';
                                                            break;
                                                        case 'contact_agent':
                                                            echo '<span class="status-contact">' . __('Contact Agent', 'houses-theme') . '</span>';
                                                            break;
                                                        default:
                                                            echo esc_html($lease_property_details['parking']);
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($lease_property_details['gym']) : ?>
                                            <div class="info-item">
                                                <strong><?php _e('Gym', 'houses-theme'); ?>:</strong>
                                                <span class="<?php echo $lease_property_details['gym'] === 'yes' ? 'status-yes' : 'status-no'; ?>">
                                                    <?php echo $lease_property_details['gym'] === 'yes' ? __('Available', 'houses-theme') : __('Not Available', 'houses-theme'); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($lease_property_details['swimming_pool']) : ?>
                                            <div class="info-item">
                                                <strong><?php _e('Swimming Pool', 'houses-theme'); ?>:</strong>
                                                <span class="<?php echo $lease_property_details['swimming_pool'] === 'yes' ? 'status-yes' : 'status-no'; ?>">
                                                    <?php echo $lease_property_details['swimming_pool'] === 'yes' ? __('Available', 'houses-theme') : __('Not Available', 'houses-theme'); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Additional Notes Section -->
                                <?php if ($lease_property_details['notes']) : ?>
                                    <div class="property-section">
                                        <h4 class="section-title"><?php _e('📝 Additional Notes', 'houses-theme'); ?></h4>
                                        <div class="notes-content">
                                            <p><?php echo esc_html($lease_property_details['notes']); ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($house_list_properties)) : ?>
                <div class="section house-list-properties">
                    <h2 class="accordion-header collapsed"><?php _e('Property Options from House List', 'houses-theme'); ?></h2>
                    <div class="accordion-content collapsed">
                        <?php foreach ($house_list_properties as $property) : ?>
                            <div class="property-card">
                                <h3 class="property-title">
                                    <span class="title-text"><?php echo esc_html($property['title']); ?></span>
                                    <div class="title-actions">
                                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $property['id'] . '&action=edit')); ?>" target="_blank" title="Editar propiedad" class="edit-link" onclick="event.stopPropagation();">
                                            <span class="dashicons dashicons-edit"></span>
                                        </a>
                                        <span class="accordion-arrow">▼</span>
                                    </div>
                                </h3>
                                <div class="property-details">
                                
                                <!-- Property Gallery -->
                                <?php if (!empty($property['gallery_images']) && is_array($property['gallery_images'])) : ?>
                                    <div class="property-gallery">
                                        <div class="gallery-grid">
                                            <?php foreach ($property['gallery_images'] as $image_id) : ?>
                                                <?php $image_url = wp_get_attachment_image_url($image_id, 'medium'); ?>
                                                <?php $image_full = wp_get_attachment_image_url($image_id, 'full'); ?>
                                                <?php if ($image_url) : ?>
                                                    <div class="gallery-item">
                                                        <a href="<?php echo esc_url($image_full); ?>" data-lightbox="house-list-property-<?php echo $property['id']; ?>">
                                                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($property['title']); ?>">
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Property Information -->
                                <div class="property-info">
                                    <!-- Location & Address Section -->
                                    <div class="property-section">
                                        <h4 class="section-title"><?php _e('📍 Location & Address', 'houses-theme'); ?></h4>
                                        <div class="info-grid">
                                            <?php if ($property['address']) : ?>
                                                <div class="info-item">
                                                    <strong><?php _e('Address (English)', 'houses-theme'); ?>:</strong>
                                                    <span><?php echo esc_html($property['address']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($property['chinese_address']) : ?>
                                                <div class="info-item">
                                                    <strong><?php _e('Address (Chinese)', 'houses-theme'); ?>:</strong>
                                                    <span><?php echo esc_html($property['chinese_address']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($property['zip_code']) : ?>
                                                <div class="info-item">
                                                    <strong><?php _e('ZIP Code', 'houses-theme'); ?>:</strong>
                                                    <span><?php echo esc_html($property['zip_code']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($property['metro_line'] || $property['station']) : ?>
                                                <div class="info-item">
                                                    <strong><?php _e('MRT Station', 'houses-theme'); ?>:</strong>
                                                    <span>
                                                        <?php if ($property['metro_line']) : ?>
                                                            <span class="metro-line"><?php echo esc_html($property['metro_line']); ?></span>
                                                        <?php endif; ?>
                                                        <?php if ($property['station']) : ?>
                                                            <?php echo esc_html($property['station']); ?>
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Property Details Section -->
                                    <div class="property-section">
                                        <h4 class="section-title"><?php _e('🏠 Property Details', 'houses-theme'); ?></h4>
                                        <div class="info-grid">
                                            <?php if ($property['property_type']) : ?>
                                                <div class="info-item">
                                                    <strong><?php _e('Property Type', 'houses-theme'); ?>:</strong>
                                                    <span class="property-type"><?php echo esc_html(ucfirst($property['property_type'])); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($property['bedroom']) : ?>
                                                <div class="info-item">
                                                    <strong><?php _e('Bedrooms', 'houses-theme'); ?>:</strong>
                                                    <span class="bedroom-count"><?php echo esc_html($property['bedroom']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($property['bathroom']) : ?>
                                                <div class="info-item">
                                                    <strong><?php _e('Bathrooms', 'houses-theme'); ?>:</strong>
                                                    <span class="bathroom-count"><?php echo esc_html($property['bathroom']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($property['net_size']) : ?>
                                                <div class="info-item">
                                                    <strong><?php _e('Net Size', 'houses-theme'); ?>:</strong>
                                                    <span class="size"><?php echo esc_html($property['net_size']); ?> m²</span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($property['square_meters']) : ?>
                                                <div class="info-item">
                                                    <strong><?php _e('Square Meters', 'houses-theme'); ?>:</strong>
                                                    <span><?php echo esc_html($property['square_meters']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($property['floor'] || $property['total_floor']) : ?>
                                                <div class="info-item">
                                                    <strong><?php _e('Floor', 'houses-theme'); ?>:</strong>
                                                    <span class="floor-info">
                                                        <?php if ($property['floor']) echo esc_html($property['floor']); ?>
                                                        <?php if ($property['total_floor']) echo ' / ' . esc_html($property['total_floor']); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($property['building_age']) : ?>
                                                <div class="info-item">
                                                    <strong><?php _e('Building Age', 'houses-theme'); ?>:</strong>
                                                    <span><?php echo esc_html($property['building_age']); ?> <?php _e('years', 'houses-theme'); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Financial Information Section -->
                                    <div class="property-section">
                                        <h4 class="section-title"><?php _e('💰 Financial Information', 'houses-theme'); ?></h4>
                                        <div class="info-grid">
                                            <?php if ($property['rent']) : ?>
                                                <div class="info-item">
                                                    <strong><?php _e('Rent', 'houses-theme'); ?>:</strong>
                                                    <span class="rent-price">NT$<?php echo esc_html($property['rent']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($property['tax_included']) : ?>
                                                <div class="info-item">
                                                    <strong><?php _e('Tax Included', 'houses-theme'); ?>:</strong>
                                                    <span class="<?php echo $property['tax_included'] === 'yes' ? 'status-yes' : 'status-no'; ?>">
                                                        <?php echo $property['tax_included'] === 'yes' ? __('Yes', 'houses-theme') : __('No', 'houses-theme'); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($property['management_fee_included']) : ?>
                                                <div class="info-item">
                                                    <strong><?php _e('Management Fee Included', 'houses-theme'); ?>:</strong>
                                                    <span class="<?php echo $property['management_fee_included'] === 'yes' ? 'status-yes' : 'status-no'; ?>">
                                                        <?php echo $property['management_fee_included'] === 'yes' ? __('Yes', 'houses-theme') : __('No', 'houses-theme'); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Amenities & Features Section -->
                                    <div class="property-section">
                                        <h4 class="section-title"><?php _e('🏋️ Amenities & Features', 'houses-theme'); ?></h4>
                                        <div class="info-grid">
                                            <?php if ($property['parking']) : ?>
                                                <div class="info-item">
                                                    <strong><?php _e('Parking', 'houses-theme'); ?>:</strong>
                                                    <span class="parking-status">
                                                        <?php 
                                                        switch($property['parking']) {
                                                            case 'yes':
                                                                echo '<span class="status-yes">' . __('Available', 'houses-theme') . '</span>';
                                                                break;
                                                            case 'no':
                                                                echo '<span class="status-no">' . __('Not Available', 'houses-theme') . '</span>';
                                                                break;
                                                            case 'contact_agent':
                                                                echo '<span class="status-contact">' . __('Contact Agent', 'houses-theme') . '</span>';
                                                                break;
                                                            default:
                                                                echo esc_html($property['parking']);
                                                        }
                                                        ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($property['gym']) : ?>
                                                <div class="info-item">
                                                    <strong><?php _e('Gym', 'houses-theme'); ?>:</strong>
                                                    <span class="<?php echo $property['gym'] === 'yes' ? 'status-yes' : 'status-no'; ?>">
                                                        <?php echo $property['gym'] === 'yes' ? __('Available', 'houses-theme') : __('Not Available', 'houses-theme'); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($property['swimming_pool']) : ?>
                                                <div class="info-item">
                                                    <strong><?php _e('Swimming Pool', 'houses-theme'); ?>:</strong>
                                                    <span class="<?php echo $property['swimming_pool'] === 'yes' ? 'status-yes' : 'status-no'; ?>">
                                                        <?php echo $property['swimming_pool'] === 'yes' ? __('Available', 'houses-theme') : __('Not Available', 'houses-theme'); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Additional Notes Section -->
                                    <?php if ($property['notes']) : ?>
                                        <div class="property-section">
                                            <h4 class="section-title"><?php _e('📝 Additional Notes', 'houses-theme'); ?></h4>
                                            <div class="notes-content">
                                                <p><?php echo esc_html($property['notes']); ?></p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($property['content']) : ?>
                                    <div class="property-description">
                                        <h4><?php _e('Description', 'houses-theme'); ?></h4>
                                        <div class="description-content">
                                            <?php echo wp_kses_post($property['content']); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Customer House List PDFs -->
            <?php if (!empty($house_lists)) : ?>
                <div class="section house-list-pdfs">
                    <h2 class="accordion-header collapsed"><?php _e('House List PDFs', 'houses-theme'); ?></h2>
                    <div class="accordion-content collapsed">
                        <?php foreach ($house_lists as $house_list) : ?>
                            <?php
                            $generated_pdfs = get_post_meta($house_list->ID, '_generated_pdfs', true);
                            if (!empty($generated_pdfs) && is_array($generated_pdfs)) :
                                // Sort PDFs by date (newest first)
                                usort($generated_pdfs, function($a, $b) {
                                    return strtotime($b['date_created']) - strtotime($a['date_created']);
                                });
                                ?>
                                <div class="house-list-pdf-group">
                                    <h3><a href="<?php echo esc_url(admin_url('post.php?post=' . $house_list->ID . '&action=edit')); ?>" target="_blank" title="Click para editar esta lista de casas" style="text-decoration: none; color: inherit; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.color='#0073aa'; this.querySelector('.dashicons').style.opacity='1';" onmouseout="this.style.color='inherit'; this.querySelector('.dashicons').style.opacity='0.7';"><?php echo esc_html(get_the_title($house_list->ID)); ?> <span class="dashicons dashicons-edit" style="font-size: 16px; opacity: 0.7; margin-left: 8px; color: #0073aa;"></span></a></h3>
                                    <div class="pdf-list">
                                        <?php foreach ($generated_pdfs as $pdf) : ?>
                                            <div class="pdf-item">
                                                <span class="dashicons dashicons-media-document"></span>
                                                <a href="<?php echo esc_url($pdf['file_url']); ?>" target="_blank" download>
                                                    <?php echo esc_html($pdf['filename']); ?>
                                                </a>
                                                <span class="pdf-type <?php echo esc_attr($pdf['type']); ?>">
                                                    (<?php echo ($pdf['type'] === 'with_images') ? __('With Images', 'houses-theme') : __('No Images', 'houses-theme'); ?>)
                                                </span>
                                                <span class="pdf-date pdf-local-datetime" data-utc="<?php echo esc_attr($pdf['date_created']); ?>">
                                                    <?php echo esc_html(date('M d, Y H:i', strtotime($pdf['date_created']))); ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else : ?>
                                <div class="house-list-pdf-group">
                                    <h3><a href="<?php echo esc_url(admin_url('post.php?post=' . $house_list->ID . '&action=edit')); ?>" target="_blank" title="Click para editar esta lista de casas" style="text-decoration: none; color: inherit; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.color='#0073aa'; this.querySelector('.dashicons').style.opacity='1';" onmouseout="this.style.color='inherit'; this.querySelector('.dashicons').style.opacity='0.7';"><?php echo esc_html(get_the_title($house_list->ID)); ?> <span class="dashicons dashicons-edit" style="font-size: 16px; opacity: 0.7; margin-left: 8px; color: #0073aa;"></span></a></h3>
                                    <p><?php _e('No PDFs have been generated for this house list yet.', 'houses-theme'); ?></p>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <script type="text/javascript">
                        (function() {
                            function pad(n){ return (n < 10 ? '0' : '') + n; }
                            function convertToLocal(){
                                var nodes = document.querySelectorAll('.house-list-pdfs .pdf-local-datetime');
                                for (var i = 0; i < nodes.length; i++) {
                                    var el = nodes[i];
                                    var utcStr = el.getAttribute('data-utc');
                                    if (utcStr && utcStr.length > 0) {
                                        var d = new Date(utcStr.replace(' ', 'T') + 'Z');
                                        if (!isNaN(d.getTime())) {
                                            var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                                            el.textContent = months[d.getMonth()] + ' ' + pad(d.getDate()) + ', ' + d.getFullYear() + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
                                        }
                                    }
                                }
                            }
                            // Run now and once more shortly after
                            convertToLocal();
                            setTimeout(convertToLocal, 100);
                        })();
                        </script>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Documents (Lease Contract + House List PDFs) -->
            <div class="section documents">
                <h2 class="accordion-header collapsed"><?php _e('Documents', 'houses-theme'); ?></h2>
                <div class="accordion-content collapsed">
                    <?php if (!empty($lease_data['contract_attachment'])) : ?>
                        <?php 
                        $attachment_id = $lease_data['contract_attachment'];
                        $attachment_url = wp_get_attachment_url($attachment_id);
                        $attachment_title = get_the_title($attachment_id);
                        $attachment_filename = basename(get_attached_file($attachment_id));
                        ?>
                        <div class="house-list-pdf-group">
                            <h3><?php _e('Lease Contract', 'houses-theme'); ?></h3>
                            <div class="pdf-list">
                                <div class="pdf-item">
                                    <span class="dashicons dashicons-media-document"></span>
                                    <a href="<?php echo esc_url($attachment_url); ?>" target="_blank" download title="<?php esc_attr_e('Click to download contract', 'houses-theme'); ?>">
                                        <?php echo esc_html($attachment_filename ? $attachment_filename : $attachment_title); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($house_lists)) : ?>
                        <div class="house-list-pdf-group">
                            <h3><?php _e('House List PDFs', 'houses-theme'); ?></h3>
                            <?php foreach ($house_lists as $house_list) : ?>
                                <?php
                                $generated_pdfs = get_post_meta($house_list->ID, '_generated_pdfs', true);
                                if (!empty($generated_pdfs) && is_array($generated_pdfs)) :
                                    // Sort PDFs by date (newest first)
                                    usort($generated_pdfs, function($a, $b) {
                                        return strtotime($b['date_created']) - strtotime($a['date_created']);
                                    });
                                    ?>
                                    <div class="house-list-pdf-group">
                                        <h3><a href="<?php echo esc_url(admin_url('post.php?post=' . $house_list->ID . '&action=edit')); ?>" target="_blank" title="Click para editar esta lista de casas" style="text-decoration: none; color: inherit; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.color='#0073aa'; this.querySelector('.dashicons').style.opacity='1';" onmouseout="this.style.color='inherit'; this.querySelector('.dashicons').style.opacity='0.7';"><?php echo esc_html(get_the_title($house_list->ID)); ?> <span class="dashicons dashicons-edit" style="font-size: 16px; opacity: 0.7; margin-left: 8px; color: #0073aa;"></span></a></h3>
                                        <div class="pdf-list">
                                            <?php foreach ($generated_pdfs as $pdf) : ?>
                                                <div class="pdf-item">
                                                    <span class="dashicons dashicons-media-document"></span>
                                                    <a href="<?php echo esc_url($pdf['file_url']); ?>" target="_blank" download>
                                                        <?php echo esc_html($pdf['filename']); ?>
                                                    </a>
                                                    <span class="pdf-type <?php echo esc_attr($pdf['type']); ?>">
                                                        (<?php echo ($pdf['type'] === 'with_images') ? __('With Images', 'houses-theme') : __('No Images', 'houses-theme'); ?>)
                                                    </span>
                                                    <span class="pdf-date pdf-local-datetime" data-utc="<?php echo esc_attr($pdf['date_created']); ?>">
                                                        <?php echo esc_html(date('M d, Y H:i', strtotime($pdf['date_created']))); ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <div class="house-list-pdf-group">
                                        <h3><a href="<?php echo esc_url(admin_url('post.php?post=' . $house_list->ID . '&action=edit')); ?>" target="_blank" title="Click para editar esta lista de casas" style="text-decoration: none; color: inherit; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.color='#0073aa'; this.querySelector('.dashicons').style.opacity='1';" onmouseout="this.style.color='inherit'; this.querySelector('.dashicons').style.opacity='0.7';"><?php echo esc_html(get_the_title($house_list->ID)); ?> <span class="dashicons dashicons-edit" style="font-size: 16px; opacity: 0.7; margin-left: 8px; color: #0073aa;"></span></a></h3>
                                        <p><?php _e('No PDFs have been generated for this house list yet.', 'houses-theme'); ?></p>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <script type="text/javascript">
                        (function() {
                            function pad(n){ return (n < 10 ? '0' : '') + n; }
                            function convertToLocal(){
                                var nodes = document.querySelectorAll('.documents .pdf-local-datetime');
                                for (var i = 0; i < nodes.length; i++) {
                                    var el = nodes[i];
                                    var utcStr = el.getAttribute('data-utc');
                                    if (utcStr && utcStr.length > 0) {
                                        var d = new Date(utcStr.replace(' ', 'T') + 'Z');
                                        if (!isNaN(d.getTime())) {
                                            var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                                            el.textContent = months[d.getMonth()] + ' ' + pad(d.getDate()) + ', ' + d.getFullYear() + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
                                        }
                                    }
                                }
                            }
                            convertToLocal();
                            setTimeout(convertToLocal, 100);
                        })();
                        </script>
                    <?php else : ?>
                        <p><?php _e('No documents available.', 'houses-theme'); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Settling-in Services -->
            <div class="section settling-services">
                <h2 class="accordion-header collapsed"><?php _e('Settling-in Services', 'houses-theme'); ?></h2>
                <div class="accordion-content collapsed">
                    <?php if (!empty($settling_services)) : ?>
                        <div class="services-list">
                            <?php foreach ($settling_services as $service) : ?>
                                <div class="service-item">
                                    <h4><a href="<?php echo esc_url(admin_url('post.php?post=' . $service->ID . '&action=edit')); ?>" target="_blank" title="Click para editar este servicio de acomodación" style="text-decoration: none; color: inherit; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.color='#0073aa'; this.querySelector('.dashicons').style.opacity='1';" onmouseout="this.style.color='inherit'; this.querySelector('.dashicons').style.opacity='0.7';"><?php echo esc_html(get_the_title($service)); ?> <span class="dashicons dashicons-edit" style="font-size: 14px; opacity: 0.7; margin-left: 8px; color: #0073aa;"></span></a></h4>
                                    
                                    <!-- Basic Information -->
                                    <?php 
                                    $intro_call = get_post_meta($service->ID, 'intro_consultation_call', true);
                                    $intro_date = get_post_meta($service->ID, 'intro_consultation_call_date', true);
                                    $home_search = get_post_meta($service->ID, 'home_search', true);
                                    $home_search_date = get_post_meta($service->ID, 'home_search_date', true);
                                    $home_search_completed = get_post_meta($service->ID, 'home_search_completed_at', true);
                                    $lease_signed = get_post_meta($service->ID, 'lease_signed', true);
                                    $lease_signed_completed = get_post_meta($service->ID, 'lease_signed_completed_at', true);
                                    $check_in = get_post_meta($service->ID, 'check_in', true);
                                    $check_in_completed = get_post_meta($service->ID, 'check_in_completed_at', true);
                                    $notes = get_post_meta($service->ID, 'notes', true);
                                    ?>
                                    
                                    <?php if ($intro_call || $home_search || $lease_signed || $check_in || $notes) : ?>
                                        <div class="service-section">
                                            <h5><?php _e('Basic Services', 'houses-theme'); ?></h5>
                                            <div class="service-details">
                                                <?php if ($intro_call) : ?>
                                                    <p><strong><?php _e('Intro Consultation Call:', 'houses-theme'); ?></strong> 
                                                    <?php echo $intro_date ? esc_html($intro_date) : __('Completed', 'houses-theme'); ?></p>
                                                <?php endif; ?>
                                                <?php if ($home_search) : ?>
                                                    <p><strong><?php _e('Home Search:', 'houses-theme'); ?></strong> 
                                                    <?php echo $home_search_date ? esc_html($home_search_date) : __('Completed', 'houses-theme'); ?></p>
                                                    <?php if ($home_search_completed) : ?>
                                                        <p><strong><?php _e('Home Search Completed:', 'houses-theme'); ?></strong> <?php echo esc_html($home_search_completed); ?></p>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <?php if ($lease_signed) : ?>
                                                    <p><strong><?php _e('Lease Signed:', 'houses-theme'); ?></strong> 
                                                    <?php echo $lease_signed_completed ? esc_html($lease_signed_completed) : __('Completed', 'houses-theme'); ?></p>
                                                <?php endif; ?>
                                                <?php if ($check_in) : ?>
                                                    <p><strong><?php _e('Check In:', 'houses-theme'); ?></strong> 
                                                    <?php echo $check_in_completed ? esc_html($check_in_completed) : __('Completed', 'houses-theme'); ?></p>
                                                <?php endif; ?>
                                                <?php if ($notes) : ?>
                                                    <p><strong><?php _e('Notes:', 'houses-theme'); ?></strong> <?php echo esc_html($notes); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- WiFi Setup -->
                                    <?php 
                                    $wifi_setup = get_post_meta($service->ID, 'wifi_set_up', true);
                                    $wifi_completed = get_post_meta($service->ID, 'wifi_set_up_completed_at', true);
                                    $telecom_name = get_post_meta($service->ID, 'telecom_name', true);
                                    $monthly_fee = get_post_meta($service->ID, 'monthly_fee', true);
                                    $payment_term = get_post_meta($service->ID, 'payment_term', true);
                                    ?>
                                    
                                    <?php if ($wifi_setup || $telecom_name || $monthly_fee || $payment_term) : ?>
                                        <div class="service-section">
                                            <h5><?php _e('WiFi Setup', 'houses-theme'); ?></h5>
                                            <div class="service-details">
                                                <?php if ($wifi_setup) : ?>
                                                    <p><strong><?php _e('WiFi Set Up:', 'houses-theme'); ?></strong> 
                                                    <?php echo $wifi_completed ? esc_html($wifi_completed) : __('Completed', 'houses-theme'); ?></p>
                                                <?php endif; ?>
                                                <?php if ($telecom_name) : ?>
                                                    <p><strong><?php _e('Telecom Name:', 'houses-theme'); ?></strong> <?php echo esc_html($telecom_name); ?></p>
                                                <?php endif; ?>
                                                <?php if ($monthly_fee) : ?>
                                                    <p><strong><?php _e('Monthly Fee:', 'houses-theme'); ?></strong> $<?php echo esc_html($monthly_fee); ?></p>
                                                <?php endif; ?>
                                                <?php if ($payment_term) : ?>
                                                    <p><strong><?php _e('Payment Term:', 'houses-theme'); ?></strong> <?php echo esc_html($payment_term); ?> <?php _e('months', 'houses-theme'); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Mobile Services -->
                                    <?php 
                                    $mobile_setup = get_post_meta($service->ID, 'mobile_set_up', true);
                                    $mobile_completed = get_post_meta($service->ID, 'mobile_set_up_completed_at', true);
                                    $local_mobile = get_post_meta($service->ID, 'local_mobile_number', true);
                                    $telecom_company = get_post_meta($service->ID, 'telecom_company', true);
                                    ?>
                                    
                                    <?php if ($mobile_setup || $local_mobile || $telecom_company) : ?>
                                        <div class="service-section">
                                            <h5><?php _e('Mobile Services', 'houses-theme'); ?></h5>
                                            <div class="service-details">
                                                <?php if ($mobile_setup) : ?>
                                                    <p><strong><?php _e('Mobile Services:', 'houses-theme'); ?></strong> 
                                                    <?php echo $mobile_completed ? esc_html($mobile_completed) : __('Completed', 'houses-theme'); ?></p>
                                                <?php endif; ?>
                                                <?php if ($local_mobile) : ?>
                                                    <p><strong><?php _e('Local Mobile Number:', 'houses-theme'); ?></strong> <?php echo esc_html($local_mobile); ?></p>
                                                <?php endif; ?>
                                                <?php if ($telecom_company) : ?>
                                                    <p><strong><?php _e('Telecom Company:', 'houses-theme'); ?></strong> <?php echo esc_html($telecom_company); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Bank Account -->
                                    <?php 
                                    $bank_setup = get_post_meta($service->ID, 'bank_account_set_up', true);
                                    $bank_completed = get_post_meta($service->ID, 'bank_account_set_up_completed_at', true);
                                    ?>
                                    
                                    <?php if ($bank_setup) : ?>
                                        <div class="service-section">
                                            <h5><?php _e('Bank Account', 'houses-theme'); ?></h5>
                                            <div class="service-details">
                                                <p><strong><?php _e('Bank Account Set Up:', 'houses-theme'); ?></strong> 
                                                <?php echo $bank_completed ? esc_html($bank_completed) : __('Completed', 'houses-theme'); ?></p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Driver's License Conversion -->
                                    <?php 
                                    $license_conversion = get_post_meta($service->ID, 'license_conversion', true);
                                    $license_completed = get_post_meta($service->ID, 'license_conversion_completed_at', true);
                                    $domestic_license = get_post_meta($service->ID, 'domestic_license_location', true);
                                    $license_completion = get_post_meta($service->ID, 'license_completion', true);
                                    $license_notes = get_post_meta($service->ID, 'license_notes', true);
                                    ?>
                                    
                                    <?php if ($license_conversion || $domestic_license || $license_completion || $license_notes) : ?>
                                        <div class="service-section">
                                            <h5><?php _e('Driver\'s License Conversion', 'houses-theme'); ?></h5>
                                            <div class="service-details">
                                                <?php if ($license_conversion) : ?>
                                                    <p><strong><?php _e('License Conversion:', 'houses-theme'); ?></strong> 
                                                    <?php echo $license_completed ? esc_html($license_completed) : __('Completed', 'houses-theme'); ?></p>
                                                <?php endif; ?>
                                                <?php if ($domestic_license) : ?>
                                                    <p><strong><?php _e('Domestic License Location:', 'houses-theme'); ?></strong> <?php echo esc_html($domestic_license); ?></p>
                                                <?php endif; ?>
                                                <?php if ($license_completion) : ?>
                                                    <p><strong><?php _e('License Completion:', 'houses-theme'); ?></strong> <?php _e('Completed', 'houses-theme'); ?></p>
                                                <?php endif; ?>
                                                <?php if ($license_notes) : ?>
                                                    <p><strong><?php _e('License Notes:', 'houses-theme'); ?></strong> <?php echo esc_html($license_notes); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p class="no-services"><?php _e('No settling-in services found for this client.', 'houses-theme'); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Departure Services -->
            <div class="section departure-services">
                <h2 class="accordion-header collapsed"><?php _e('Departure Services', 'houses-theme'); ?></h2>
                <div class="accordion-content collapsed">
                    <?php if (!empty($departure_services)) : ?>
                        <div class="services-list">
                            <?php foreach ($departure_services as $service) : ?>
                                <div class="service-item">
                                    <h4><a href="<?php echo esc_url(admin_url('post.php?post=' . $service->ID . '&action=edit')); ?>" target="_blank" title="Click para editar este servicio de salida" style="text-decoration: none; color: inherit; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.color='#0073aa'; this.querySelector('.dashicons').style.opacity='1';" onmouseout="this.style.color='inherit'; this.querySelector('.dashicons').style.opacity='0.7';"><?php echo esc_html(get_the_title($service)); ?> <span class="dashicons dashicons-edit" style="font-size: 14px; opacity: 0.7; margin-left: 8px; color: #0073aa;"></span></a></h4>
                                    <?php 
                                    // Get departure service details
                                    $departure_date = get_post_meta($service->ID, 'departure_date', true);
                                    $pre_inspection = get_post_meta($service->ID, 'pre_inspection', true);
                                    $mover_packing = get_post_meta($service->ID, 'mover_packing', true);
                                    $final_inspection = get_post_meta($service->ID, 'final_inspection', true);
                                    $deposit_return = get_post_meta($service->ID, 'deposit_return', true);
                                    $key_handover = get_post_meta($service->ID, 'key_handover', true);
                                    
                                    // Closure services
                                    $wifi_closure = get_post_meta($service->ID, 'wifi_closure', true);
                                    $wifi_closure_notes = get_post_meta($service->ID, 'wifi_closure_notes', true);
                                    $bank_closure = get_post_meta($service->ID, 'bank_closure', true);
                                    $bank_closure_notes = get_post_meta($service->ID, 'bank_closure_notes', true);
                                    $utility_closure = get_post_meta($service->ID, 'utility_closure', true);
                                    $utility_closure_notes = get_post_meta($service->ID, 'utility_closure_notes', true);
                                    $notes = get_post_meta($service->ID, 'notes', true);
                                    ?>
                                    <div class="service-details">
                                        <?php if ($departure_date) : ?>
                                            <p><strong><?php _e('Departure Date:', 'houses-theme'); ?></strong> <?php echo esc_html($departure_date); ?></p>
                                        <?php endif; ?>
                                        <?php if ($pre_inspection) : ?>
                                            <p><strong><?php _e('Pre-inspection:', 'houses-theme'); ?></strong> <?php echo esc_html($pre_inspection); ?></p>
                                        <?php endif; ?>
                                        <?php if ($mover_packing) : ?>
                                            <p><strong><?php _e('Mover Packing:', 'houses-theme'); ?></strong> <?php echo esc_html($mover_packing); ?></p>
                                        <?php endif; ?>
                                        <?php if ($final_inspection) : ?>
                                            <p><strong><?php _e('Final Inspection:', 'houses-theme'); ?></strong> <?php echo esc_html($final_inspection); ?></p>
                                        <?php endif; ?>
                                        <?php if ($key_handover) : ?>
                                            <p><strong><?php _e('Key Handover:', 'houses-theme'); ?></strong> <?php echo esc_html($key_handover); ?></p>
                                        <?php endif; ?>
                                        <?php if ($deposit_return) : ?>
                                            <p><strong><?php _e('Deposit Return:', 'houses-theme'); ?></strong> <?php echo esc_html($deposit_return); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Closure Services -->
                                    <?php if ($wifi_closure || $bank_closure || $utility_closure) : ?>
                                        <div class="closure-services">
                                            <h5><?php _e('Closure Services:', 'houses-theme'); ?></h5>
                                            <div class="closure-details">
                                                <?php if ($wifi_closure) : ?>
                                                    <div class="closure-item">
                                                        <strong><?php _e('WiFi Closure:', 'houses-theme'); ?></strong> <?php echo esc_html($wifi_closure); ?>
                                                        <?php if ($wifi_closure_notes) : ?>
                                                            <div class="closure-notes"><?php echo esc_html($wifi_closure_notes); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($bank_closure) : ?>
                                                    <div class="closure-item">
                                                        <strong><?php _e('Bank Closure:', 'houses-theme'); ?></strong> <?php echo esc_html($bank_closure); ?>
                                                        <?php if ($bank_closure_notes) : ?>
                                                            <div class="closure-notes"><?php echo esc_html($bank_closure_notes); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($utility_closure) : ?>
                                                    <div class="closure-item">
                                                        <strong><?php _e('Utility Closure:', 'houses-theme'); ?></strong> <?php echo esc_html($utility_closure); ?>
                                                        <?php if ($utility_closure_notes) : ?>
                                                            <div class="closure-notes"><?php echo esc_html($utility_closure_notes); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- General Notes -->
                                    <?php if ($notes) : ?>
                                        <div class="service-notes">
                                            <h5><?php _e('Notes:', 'houses-theme'); ?></h5>
                                            <div class="notes-content"><?php echo wp_kses_post(nl2br($notes)); ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p class="no-services"><?php _e('No departure services found for this client.', 'houses-theme'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php
endwhile; // End of the loop.

get_footer();

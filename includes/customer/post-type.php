<?php
function register_customer_post_type() {
    $labels = array(
        'name' => __('Assignee', 'houses-theme'),
        'singular_name' => __('Assignee', 'houses-theme'),
        'add_new' => __('Add New Assignee', 'houses-theme'),
        'add_new_item' => __('Add New Assignee', 'houses-theme'),
        'edit_item' => __('Edit Assignee', 'houses-theme'),
        'new_item' => __('New Assignee', 'houses-theme'),
        'view_item' => __('View Assignee', 'houses-theme'),
        'search_items' => __('Search Assignee', 'houses-theme'),
        'not_found' => __('No customers found', 'houses-theme'),
        'not_found_in_trash' => __('No customers found in trash', 'houses-theme'),
        'menu_name' => __('Assignee', 'houses-theme'),
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'customers'),
        'supports' => array(), // Removed 'title' support
        'menu_icon' => 'dashicons-businessperson',
        'show_in_rest' => false,
        'capability_type' => 'post',
        'hierarchical' => false,
        'menu_position' => 5,
        'register_meta_box_cb' => null,
    );

    register_post_type('customer', $args);
}
add_action('init', 'register_customer_post_type');

function change_customer_title_placeholder($title_placeholder) {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'customer') {
        $title_placeholder = 'Name of Assignee';
    }
    return $title_placeholder;
}
add_filter('enter_title_here', 'change_customer_title_placeholder');

function register_customer_fields() {
    if (function_exists('acf_add_local_field_group')) {
        acf_add_local_field_group(array(
            'key' => 'group_customer_fields',
            'title' => 'Assignee Details (Taiwan)',
            'fields' => array(
                array(
                    'key' => 'field_customer_id',
                    'label' => 'Assignee ID',
                    'name' => 'customer_id',
                    'type' => 'text',
                    'required' => true,
                ),
                array(
                    'key' => 'field_company',
                    'label' => 'Company',
                    'name' => 'company',
                    'type' => 'post_object',
                    'post_type' => array('company'),
                    'return_format' => 'id',
                    'ui' => 1,
                ),
                array(
                    'key' => 'field_property_requirements',
                    'label' => 'Property Requirements',
                    'name' => 'property_requirements',
                    'type' => 'textarea',
                    'instructions' => 'Specify the assignee\'s property requirements.',
                ),
                array(
                    'key' => 'field_customer_notes',
                    'label' => 'Notes',
                    'name' => 'customer_notes',
                    'type' => 'textarea',
                    'instructions' => 'Additional notes about the assignee.',
                    'placeholder' => 'Enter important notes about the assignee here...',
                    'rows' => 5,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'customer',
                    ),
                ),
            ),
        ));
    }
}
add_action('acf/init', 'register_customer_fields');

function disable_gutenberg_for_customers($use_block_editor, $post_type) {
    if ($post_type === 'customer') {
        return false;
    }
    return $use_block_editor;
}
add_filter('use_block_editor_for_post_type', 'disable_gutenberg_for_customers', 10, 2);

/**
 * Auto-generate title for customer posts based on first and last name
 */
function auto_generate_customer_title($post_id) {
    // Only run for customer post type
    if (get_post_type($post_id) !== 'customer') {
        return;
    }
    
    // Don't run when doing autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Don't run when importing
    if (defined('WP_IMPORTING') && WP_IMPORTING) {
        return;
    }
    
    // Get first and last name from post meta
    $first_name = get_post_meta($post_id, 'first_name', true);
    $last_name = get_post_meta($post_id, 'last_name', true);
    
    // If POST data is available, use that instead (for immediate updates)
    if (isset($_POST['first_name'])) {
        $first_name = sanitize_text_field($_POST['first_name']);
    }
    
    if (isset($_POST['last_name'])) {
        $last_name = sanitize_text_field($_POST['last_name']);
    }
    
    // Only proceed if we have at least one of the names
    if (!empty($first_name) || !empty($last_name)) {
        // Create the title
        $title = trim($first_name . ' ' . $last_name);
        
        // Only update if title is not empty
        if (!empty($title)) {
            // Unhook this function to prevent infinite loop
            remove_action('save_post', 'auto_generate_customer_title');
            remove_action('updated_post_meta', 'update_customer_title_on_meta_change', 10);
            
            // Update the post title
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => $title,
            ));
            
            // Re-hook this function
            add_action('save_post', 'auto_generate_customer_title');
            add_action('updated_post_meta', 'update_customer_title_on_meta_change', 10, 4);
        }
    }
}
add_action('save_post', 'auto_generate_customer_title');

/**
 * Update customer title when meta fields change
 */
function update_customer_title_on_meta_change($meta_id, $post_id, $meta_key, $meta_value) {
    // Only run for relevant meta keys
    if ($meta_key !== 'first_name' && $meta_key !== 'last_name') {
        return;
    }
    
    // Only run for customer post type
    if (get_post_type($post_id) !== 'customer') {
        return;
    }
    
    // Call the title generation function
    auto_generate_customer_title($post_id);
}
add_action('updated_post_meta', 'update_customer_title_on_meta_change', 10, 4);
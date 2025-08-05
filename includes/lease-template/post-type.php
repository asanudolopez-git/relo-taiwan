<?php
/**
 * Lease Template Custom Post Type
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Lease Template post type
 */
function houses_register_lease_template_post_type() {
    $labels = array(
        'name'                  => _x('Lease Template', 'Post type general name', 'houses-theme'),
        'singular_name'         => _x('Lease Template', 'Post type singular name', 'houses-theme'),
        'menu_name'             => _x('Lease Template', 'Admin Menu text', 'houses-theme'),
        'name_admin_bar'        => _x('Lease Template', 'Add New on Toolbar', 'houses-theme'),
        'add_new'               => __('Add New', 'houses-theme'),
        'add_new_item'          => __('Add New Lease Template', 'houses-theme'),
        'new_item'              => __('New Lease Template', 'houses-theme'),
        'edit_item'             => __('Edit Lease Template', 'houses-theme'),
        'view_item'             => __('View Lease Template', 'houses-theme'),
        'all_items'             => __('All Lease Templates', 'houses-theme'),
        'search_items'          => __('Search Lease Templates', 'houses-theme'),
        'parent_item_colon'     => __('Parent Lease Templates:', 'houses-theme'),
        'not_found'             => __('No lease templates found.', 'houses-theme'),
        'not_found_in_trash'    => __('No lease templates found in Trash.', 'houses-theme'),
        'featured_image'        => _x('Lease Template Image', 'Overrides the "Featured Image" phrase', 'houses-theme'),
        'set_featured_image'    => _x('Set lease template image', 'Overrides the "Set featured image" phrase', 'houses-theme'),
        'remove_featured_image' => _x('Remove lease template image', 'Overrides the "Remove featured image" phrase', 'houses-theme'),
        'use_featured_image'    => _x('Use as lease template image', 'Overrides the "Use as featured image" phrase', 'houses-theme'),
        'archives'              => _x('Lease Template archives', 'The post type archive label used in nav menus', 'houses-theme'),
        'insert_into_item'      => _x('Insert into lease template', 'Overrides the "Insert into post"/"Insert into page" phrase', 'houses-theme'),
        'uploaded_to_this_item' => _x('Uploaded to this lease template', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase', 'houses-theme'),
        'filter_items_list'     => _x('Filter lease templates list', 'Screen reader text for the filter links heading on the post type listing screen', 'houses-theme'),
        'items_list_navigation' => _x('Lease Templates list navigation', 'Screen reader text for the pagination heading on the post type listing screen', 'houses-theme'),
        'items_list'            => _x('Lease Templates list', 'Screen reader text for the items list heading on the post type listing screen', 'houses-theme'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'lease-template'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'menu_icon'          => 'dashicons-media-document',
        'supports'           => array('title'),
    );

    register_post_type('lease_template', $args);
}
add_action('init', 'houses_register_lease_template_post_type');

/**
 * Import sample lease templates data
 */
function houses_import_sample_lease_templates_data($force = false) {
    // Only run this function in development environment or if forced
    if ((!defined('WP_DEBUG') || !WP_DEBUG) && !$force) {
        return;
    }

    // Check if we've already imported lease templates (skip if forced)
    if (get_option('houses_lease_templates_imported', false) && !$force) {
        return;
    }

    // Sample lease templates data
    $lease_templates = array(
        'Standard Residential Lease Agreement',
        'Commercial Property Lease',
        'Month-to-Month Rental Agreement',
        'Sublease Agreement',
        'Room Rental Agreement'
    );

    // Delete existing lease templates if forced
    if ($force) {
        $existing_templates = get_posts(array(
            'post_type' => 'lease_template',
            'numberposts' => -1,
            'post_status' => 'any',
            'fields' => 'ids'
        ));

        foreach ($existing_templates as $template_id) {
            wp_delete_post($template_id, true);
        }
    }

    // Create lease templates
    foreach ($lease_templates as $template_name) {
        $template_id = wp_insert_post(array(
            'post_title'    => $template_name,
            'post_status'   => 'publish',
            'post_type'     => 'lease_template',
        ));
    }

    // Mark as imported
    update_option('houses_lease_templates_imported', true);
}
add_action('admin_init', 'houses_import_sample_lease_templates_data');

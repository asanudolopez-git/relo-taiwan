<?php
/**
 * Landlord Custom Post Type
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Landlord post type
 */
function houses_register_landlord_post_type() {
    $labels = array(
        'name'                  => _x('Landlord', 'Post type general name', 'houses-theme'),
        'singular_name'         => _x('Landlord', 'Post type singular name', 'houses-theme'),
        'menu_name'             => _x('Landlord', 'Admin Menu text', 'houses-theme'),
        'name_admin_bar'        => _x('Landlord', 'Add New on Toolbar', 'houses-theme'),
        'add_new'               => __('Add New', 'houses-theme'),
        'add_new_item'          => __('Add New Landlord', 'houses-theme'),
        'new_item'              => __('New Landlord', 'houses-theme'),
        'edit_item'             => __('Edit Landlord', 'houses-theme'),
        'view_item'             => __('View Landlord', 'houses-theme'),
        'all_items'             => __('All Landlords', 'houses-theme'),
        'search_items'          => __('Search Landlords', 'houses-theme'),
        'parent_item_colon'     => __('Parent Landlords:', 'houses-theme'),
        'not_found'             => __('No landlords found.', 'houses-theme'),
        'not_found_in_trash'    => __('No landlords found in Trash.', 'houses-theme'),
        'featured_image'        => _x('Landlord Image', 'Overrides the "Featured Image" phrase', 'houses-theme'),
        'set_featured_image'    => _x('Set landlord image', 'Overrides the "Set featured image" phrase', 'houses-theme'),
        'remove_featured_image' => _x('Remove landlord image', 'Overrides the "Remove featured image" phrase', 'houses-theme'),
        'use_featured_image'    => _x('Use as landlord image', 'Overrides the "Use as featured image" phrase', 'houses-theme'),
        'archives'              => _x('Landlord archives', 'The post type archive label used in nav menus', 'houses-theme'),
        'insert_into_item'      => _x('Insert into landlord', 'Overrides the "Insert into post"/"Insert into page" phrase', 'houses-theme'),
        'uploaded_to_this_item' => _x('Uploaded to this landlord', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase', 'houses-theme'),
        'filter_items_list'     => _x('Filter landlords list', 'Screen reader text for the filter links heading on the post type listing screen', 'houses-theme'),
        'items_list_navigation' => _x('Landlords list navigation', 'Screen reader text for the pagination heading on the post type listing screen', 'houses-theme'),
        'items_list'            => _x('Landlords list', 'Screen reader text for the items list heading on the post type listing screen', 'houses-theme'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'landlord'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'menu_icon'          => 'dashicons-businessman',
        'supports'           => array('title'),
    );

    register_post_type('landlord', $args);
}
//add_action('init', 'houses_register_landlord_post_type');

/**
 * Import sample landlords data
 */
function houses_import_sample_landlords_data($force = false) {
    // Only run this function in development environment or if forced
    if ((!defined('WP_DEBUG') || !WP_DEBUG) && !$force) {
        return;
    }

    // Check if we've already imported landlords (skip if forced)
    if (get_option('houses_landlords_imported', false) && !$force) {
        return;
    }

    // Sample landlords data
    $landlords = array(
        'John Smith',
        'Jane Doe',
        'Robert Johnson',
        'Emily Williams',
        'Michael Brown'
    );

    // Delete existing landlords if forced
    if ($force) {
        $existing_landlords = get_posts(array(
            'post_type' => 'landlord',
            'numberposts' => -1,
            'post_status' => 'any',
            'fields' => 'ids'
        ));

        foreach ($existing_landlords as $landlord_id) {
            wp_delete_post($landlord_id, true);
        }
    }

    // Create landlords
    foreach ($landlords as $landlord_name) {
        $landlord_id = wp_insert_post(array(
            'post_title'    => $landlord_name,
            'post_status'   => 'publish',
            'post_type'     => 'landlord',
        ));
    }

    // Mark as imported
    update_option('houses_landlords_imported', true);
}
//add_action('admin_init', 'houses_import_sample_landlords_data');

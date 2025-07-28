<?php
/**
 * District Custom Post Type
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register the District custom post type
 */
function houses_register_district_post_type() {
    $labels = array(
        'name'               => _x('Districts', 'post type general name', 'houses-theme'),
        'singular_name'      => _x('District', 'post type singular name', 'houses-theme'),
        'menu_name'          => _x('Districts', 'admin menu', 'houses-theme'),
        'name_admin_bar'     => _x('District', 'add new on admin bar', 'houses-theme'),
        'add_new'            => _x('Add New', 'district', 'houses-theme'),
        'add_new_item'       => __('Add New District', 'houses-theme'),
        'new_item'           => __('New District', 'houses-theme'),
        'edit_item'          => __('Edit District', 'houses-theme'),
        'view_item'          => __('View District', 'houses-theme'),
        'all_items'          => __('All Districts', 'houses-theme'),
        'search_items'       => __('Search Districts', 'houses-theme'),
        'parent_item_colon'  => __('Parent Districts:', 'houses-theme'),
        'not_found'          => __('No districts found.', 'houses-theme'),
        'not_found_in_trash' => __('No districts found in Trash.', 'houses-theme')
    );

    $args = array(
        'labels'             => $labels,
        'description'        => __('Districts for properties', 'houses-theme'),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'district'),
        'show_in_rest' => false,
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array('title'),
        'menu_icon'          => 'dashicons-location',
    );

    register_post_type('district', $args);
}
add_action('init', 'houses_register_district_post_type');

/**
 * Import sample districts data
 */
function houses_import_sample_districts_data($force = false) {
    // Only run this function if forced or in development environment
    if ((!defined('WP_DEBUG') || !WP_DEBUG) && !$force) {
        return;
    }
    
    // Check if we've already imported districts (skip if forced)
    if (get_option('houses_districts_imported', false) && !$force) {
        return;
    }
    
    // Sample district data
    $sample_districts = array(
        'Songshan',
        'Xinyi',
        'Da\'an',
        'Zhongshan',
        'Zhongzheng',
        'Datong',
        'Wanhua',
        'Wenshan',
        'Nangang',
        'Neihu',
        'Shilin',
        'Beitou'
    );
    
    // Create each district
    foreach ($sample_districts as $district_name) {
        // Check if district already exists
        $existing = get_page_by_title($district_name, OBJECT, 'district');
        
        if (!$existing) {
            // Create new district
            $district_id = wp_insert_post(array(
                'post_title'    => $district_name,
                'post_type'     => 'district',
                'post_status'   => 'publish',
            ));
        }
    }
    
    // Mark as imported
    update_option('houses_districts_imported', true);
}
add_action('after_setup_theme', 'houses_import_sample_districts_data');

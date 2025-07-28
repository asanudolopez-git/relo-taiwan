<?php
/**
 * Register custom post type for Departure Services
 *
 * @package Houses Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Departure Services post type
 */
function houses_register_departure_services_post_type() {
    $labels = array(
        'name'                  => _x('Departure Services', 'Post type general name', 'houses-theme'),
        'singular_name'         => _x('Departure Service', 'Post type singular name', 'houses-theme'),
        'menu_name'             => _x('Departure Services', 'Admin Menu text', 'houses-theme'),
        'add_new'               => __('Add New', 'houses-theme'),
        'add_new_item'          => __('Add New Departure Service', 'houses-theme'),
        'edit_item'             => __('Edit Departure Service', 'houses-theme'),
        'new_item'              => __('New Departure Service', 'houses-theme'),
        'view_item'             => __('View Departure Service', 'houses-theme'),
        'search_items'          => __('Search Departure Services', 'houses-theme'),
        'not_found'             => __('No departure services found', 'houses-theme'),
        'not_found_in_trash'    => __('No departure services found in Trash', 'houses-theme'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'departure-service'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'supports'           => array('title', 'thumbnail'),
        'menu_icon'          => 'dashicons-migrate',
    );

    register_post_type('departure_service', $args);
}
add_action('init', 'houses_register_departure_services_post_type');

<?php
/**
 * Register custom post type for Settling-In Services
 *
 * @package Houses Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Settling-In Services post type
 */
function houses_register_accommodation_post_type()
{
    $labels = array(
        'name' => _x('Settling-In Services', 'Post type general name', 'houses-theme'),
        'singular_name' => _x('Settling-In Service', 'Post type singular name', 'houses-theme'),
        'menu_name' => _x('Settling-In Services', 'Admin Menu text', 'houses-theme'),
        'add_new' => __('Add New', 'houses-theme'),
        'add_new_item' => __('Add New Settling-In Service', 'houses-theme'),
        'edit_item' => __('Edit Settling-In Service', 'houses-theme'),
        'new_item' => __('New Settling-In Service', 'houses-theme'),
        'view_item' => __('View Settling-In Service', 'houses-theme'),
        'search_items' => __('Search Settling-In Services', 'houses-theme'),
        'not_found' => __('No settling-in services found', 'houses-theme'),
        'not_found_in_trash' => __('No settling-in services found in Trash', 'houses-theme'),
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'accommodation'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => 5,
        'supports' => array('title', 'thumbnail'),
        'menu_icon' => 'dashicons-building',
    );

    register_post_type('accommodation', $args);
}
add_action('init', 'houses_register_accommodation_post_type');

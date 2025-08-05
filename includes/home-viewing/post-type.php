<?php
/**
 * Register custom post type for Home Viewing
 *
 * @package Houses Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Home Viewing post type
 */
function houses_register_home_viewing_post_type() {
    $labels = array(
        'name'                  => _x('Home Viewing', 'Post type general name', 'houses-theme'),
        'singular_name'         => _x('Home Viewing', 'Post type singular name', 'houses-theme'),
        'menu_name'            => _x('Home Viewing', 'Admin Menu text', 'houses-theme'),
        'add_new'              => __('Add New', 'houses-theme'),
        'add_new_item'         => __('Add New Home Viewing', 'houses-theme'),
        'edit_item'            => __('Edit Home Viewing', 'houses-theme'),
        'new_item'             => __('New Home Viewing', 'houses-theme'),
        'view_item'            => __('View Home Viewing', 'houses-theme'),
        'search_items'         => __('Search Home Viewings', 'houses-theme'),
        'not_found'            => __('No home viewings found', 'houses-theme'),
        'not_found_in_trash'   => __('No home viewings found in Trash', 'houses-theme'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'home-viewing'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'supports'           => array('title', 'thumbnail'),
        'menu_icon'          => 'dashicons-visibility',
    );

    register_post_type('home-viewing', $args);
}
add_action('init', 'houses_register_home_viewing_post_type');

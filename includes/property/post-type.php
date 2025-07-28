<?php
/**
 * Register custom post type for Property
 */
function houses_register_post_type() {
    $labels = array(
        'name'                  => _x('Property', 'Post type general name', 'houses-theme'),
        'singular_name'         => _x('Property', 'Post type singular name', 'houses-theme'),
        'menu_name'            => _x('Property', 'Admin Menu text', 'houses-theme'),
        'add_new'              => __('Add New', 'houses-theme'),
        'add_new_item'         => __('Add New Property', 'houses-theme'),
        'edit_item'            => __('Edit Property', 'houses-theme'),
        'new_item'             => __('New Property', 'houses-theme'),
        'view_item'            => __('View Property', 'houses-theme'),
        'search_items'         => __('Search Property', 'houses-theme'),
        'not_found'            => __('No Property found', 'houses-theme'),
        'not_found_in_trash'   => __('No Property found in Trash', 'houses-theme'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'property'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
        'menu_icon'          => 'dashicons-building',
    );

    register_post_type('property', $args);
}
add_action('init', 'houses_register_post_type');

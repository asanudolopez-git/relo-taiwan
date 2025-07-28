<?php
/**
 * Register Follow-up Email Post Type
 */
function register_follow_up_email_post_type() {
    $labels = array(
        'name'               => __('Follow-up Emails', 'houses-theme'),
        'singular_name'      => __('Follow-up Email', 'houses-theme'),
        'add_new'            => __('Add New Email', 'houses-theme'),
        'add_new_item'       => __('Add New Follow-up Email', 'houses-theme'),
        'edit_item'          => __('Edit Follow-up Email', 'houses-theme'),
        'new_item'           => __('New Follow-up Email', 'houses-theme'),
        'view_item'          => __('View Email', 'houses-theme'),
        'search_items'       => __('Search Emails', 'houses-theme'),
        'not_found'          => __('No emails found', 'houses-theme'),
        'not_found_in_trash' => __('No emails found in trash', 'houses-theme'),
        'menu_name'          => __('Follow-up Emails', 'houses-theme'),
    );

    $args = array(
        'labels'              => $labels,
        'public'              => false,
        'show_ui'             => true,
        'has_archive'         => false,
        'rewrite'             => false,
        'supports'            => array('title'),
        'menu_icon'           => 'dashicons-email',
        'show_in_rest'        => false,
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'menu_position'       => 6,
    );

    register_post_type('follow_up_email', $args);
}
add_action('init', 'register_follow_up_email_post_type');

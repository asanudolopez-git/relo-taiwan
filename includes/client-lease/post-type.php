<?php
/**
 * Register Lease Summary Post Type
 */
function register_client_lease_post_type() {
    $labels = array(
        'name'               => __('Client Lease Summaries', 'houses-theme'),
        'singular_name'      => __('Lease Summary', 'houses-theme'),
        'add_new'            => __('Add New Lease', 'houses-theme'),
        'add_new_item'       => __('Add New Lease', 'houses-theme'),
        'edit_item'          => __('Edit Lease', 'houses-theme'),
        'new_item'           => __('New Lease', 'houses-theme'),
        'view_item'          => __('View Lease', 'houses-theme'),
        'search_items'       => __('Search Leases', 'houses-theme'),
        'not_found'          => __('No leases found', 'houses-theme'),
        'not_found_in_trash' => __('No leases found in trash', 'houses-theme'),
        'menu_name'          => __('Client Lease Summaries', 'houses-theme'),
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => true,
        'rewrite'             => array('slug' => 'lease-summaries'),
        'supports'            => array('title'),
        'menu_icon'           => 'dashicons-media-document',
        'show_in_rest'        => false,
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'menu_position'       => 5,
        'register_meta_box_cb' => null,
    );

    register_post_type('client_lease', $args);
}
add_action('init', 'register_client_lease_post_type');

/**
 * Change the placeholder text for the title field
 */
function change_client_lease_title_placeholder($title_placeholder) {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'client_lease') {
        $title_placeholder = 'Lease Name/Reference';
    }
    return $title_placeholder;
}
add_filter('enter_title_here', 'change_client_lease_title_placeholder');

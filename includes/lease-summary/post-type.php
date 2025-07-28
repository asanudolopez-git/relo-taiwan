<?php
/**
 * Register Lease Summary Post Type
 */
function register_lease_summary_post_type() {
    $labels = array(
        'name'               => __('Lease Summary', 'houses-theme'),
        'singular_name'      => __('Lease Summary', 'houses-theme'),
        'add_new'            => __('Add New Summary', 'houses-theme'),
        'add_new_item'       => __('Add New Summary', 'houses-theme'),
        'edit_item'          => __('Edit Summary', 'houses-theme'),
        'new_item'           => __('New Summary', 'houses-theme'),
        'view_item'          => __('View Summary', 'houses-theme'),
        'search_items'       => __('Search Summaries', 'houses-theme'),
        'not_found'          => __('No summaries found', 'houses-theme'),
        'not_found_in_trash' => __('No summaries found in trash', 'houses-theme'),
        'menu_name'          => __('Lease Summary', 'houses-theme'),
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => true,
        'rewrite'             => array('slug' => 'lease-summaries'),
        'supports'            => array('title'),
        'menu_icon'           => 'dashicons-clipboard',
        'show_in_rest'        => false,
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'menu_position'       => 5,
        'register_meta_box_cb' => null,
    );

    register_post_type('lease_summary', $args);
}
add_action('init', 'register_lease_summary_post_type');

/**
 * Change the placeholder text for the title field
 */
function change_lease_summary_title_placeholder($title_placeholder) {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'lease_summary') {
        $title_placeholder = 'Summary Name/Reference';
    }
    return $title_placeholder;
}
add_filter('enter_title_here', 'change_lease_summary_title_placeholder');

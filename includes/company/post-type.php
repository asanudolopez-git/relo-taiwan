<?php
/**
 * Register Company Post Type
 */
function register_company_post_type() {
    $labels = array(
        'name'               => __('Companies', 'houses-theme'),
        'singular_name'      => __('Company', 'houses-theme'),
        'add_new'            => __('Add New Company', 'houses-theme'),
        'add_new_item'       => __('Add New Company', 'houses-theme'),
        'edit_item'          => __('Edit Company', 'houses-theme'),
        'new_item'           => __('New Company', 'houses-theme'),
        'view_item'          => __('View Company', 'houses-theme'),
        'search_items'       => __('Search Companies', 'houses-theme'),
        'not_found'          => __('No companies found', 'houses-theme'),
        'not_found_in_trash' => __('No companies found in trash', 'houses-theme'),
        'menu_name'          => __('Companies', 'houses-theme'),
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => true,
        'rewrite'             => array('slug' => 'companies'),
        'supports'            => array('title'),
        'menu_icon'           => 'dashicons-building',
        'show_in_rest'        => false,
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'menu_position'       => 5,
        'register_meta_box_cb' => null,
    );

    register_post_type('company', $args);
}
add_action('init', 'register_company_post_type');

/**
 * Change the placeholder text for the title field
 */
function change_company_title_placeholder($title_placeholder) {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'company') {
        $title_placeholder = 'Company Name';
    }
    return $title_placeholder;
}
add_filter('enter_title_here', 'change_company_title_placeholder');

/**
 * Disable Gutenberg editor for company post type
 */
function disable_gutenberg_for_company($use_block_editor, $post_type) {
    if ($post_type === 'company') {
        return false;
    }
    return $use_block_editor;
}
add_filter('use_block_editor_for_post_type', 'disable_gutenberg_for_company', 10, 2);



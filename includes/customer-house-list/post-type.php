<?php
function register_customer_house_list_post_type() {
    $labels = array(
        'name' => __('Assignee House Lists', 'houses-theme'),
        'singular_name' => __('Assignee House List', 'houses-theme'),
        'add_new' => __('Add New List', 'houses-theme'),
        'add_new_item' => __('Add New House List', 'houses-theme'),
        'edit_item' => __('Edit House List', 'houses-theme'),
        'new_item' => __('New House List', 'houses-theme'),
        'view_item' => __('View House List', 'houses-theme'),
        'search_items' => __('Search House Lists', 'houses-theme'),
        'not_found' => __('No house lists found', 'houses-theme'),
        'not_found_in_trash' => __('No house lists found in trash', 'houses-theme'),
        'menu_name' => __('House Lists', 'houses-theme'),
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'house-lists'),
        'supports' => array('title'),
        'menu_icon' => 'dashicons-list-view',
        'show_in_rest' => false,
        'capability_type' => 'post',
        'hierarchical' => false,
        'menu_position' => 6,
        'register_meta_box_cb' => null,
    );

    register_post_type('customer-house-list', $args);
}
add_action('init', 'register_customer_house_list_post_type');

// Change the title placeholder
function change_customer_house_list_title_placeholder($title_placeholder) {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'customer-house-list') {
        $title_placeholder = 'List Name';
    }
    return $title_placeholder;
}
add_filter('enter_title_here', 'change_customer_house_list_title_placeholder');

/**
 * Register scripts and styles for the customer house list
 */
function register_customer_house_list_scripts($hook) {
    if (is_admin()) {
        // Verificar si estamos en la página de edición del customer-house-list
        global $post_type, $pagenow, $post;
        $current_post_type = '';
        
        if ($pagenow == 'post.php' && isset($_GET['post'])) {
            $current_post_type = get_post_type($_GET['post']);
        } elseif ($pagenow == 'post-new.php' && isset($_GET['post_type'])) {
            $current_post_type = $_GET['post_type'];
        } elseif (isset($post) && is_object($post)) {
            $current_post_type = $post->post_type;
        }
        
        if ($current_post_type == 'customer-house-list') {
            // Registrar y cargar jQuery primero para asegurar que esté disponible
            wp_enqueue_script('jquery');
            
            // Registrar y cargar el script principal con versión aleatoria para evitar caché
            wp_enqueue_script(
                'property-search-js',
                get_template_directory_uri() . '/includes/customer-house-list/assets/js/property-search.js',
                array('jquery'),
                rand(1000, 9999),
                false // Cargar en el head para asegurar que esté disponible antes de cargar la página
            );
            
            // Registrar y cargar los estilos
            wp_enqueue_style(
                'property-search-css',
                get_template_directory_uri() . '/includes/customer-house-list/assets/css/property-search.css',
                array(),
                rand(1000, 9999)
            );
            
            // Registrar y cargar el script para exportación PDF
            wp_enqueue_script(
                'pdf-export-js',
                get_template_directory_uri() . '/includes/customer-house-list/pdf-export.js',
                array('jquery'),
                rand(1000, 9999),
                false
            );
            
            // Localizar el script con datos necesarios para AJAX
            wp_localize_script(
                'pdf-export-js',
                'ClientHouseListPDF',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce'    => wp_create_nonce('generate_customer_house_list_pdf_nonce')
                )
            );
        }
    }
}
add_action('admin_enqueue_scripts', 'register_customer_house_list_scripts');

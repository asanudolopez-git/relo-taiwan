<?php
/**
 * Property Template Loader
 */

function houses_property_template_loader($template) {
    if (is_singular('property')) {
        // Enqueue property styles
        wp_enqueue_style(
            'property-single-style',
            get_template_directory_uri() . '/includes/property/assets/css/single.css',
            array(),
            _S_VERSION
        );
    }
    
    return $template;
}
add_action('template_include', 'houses_property_template_loader');

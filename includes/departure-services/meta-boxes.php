<?php
/**
 * Departure Services Meta Boxes Handler
 *
 * @package Houses Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Houses_Departure_Services_Meta_Boxes
 */
class Houses_Departure_Services_Meta_Boxes {
    /**
     * Meta box fields configuration
     */
    private $fields = array(
        'basic_info' => array(
            'title' => 'Departure Service Details',
            'fields' => array(
                'customer_id' => array(
                    'label' => 'Client',
                    'type' => 'select',
                    'options' => array(), // Will be populated in constructor
                    'class' => 'full-width',
                ),
                'property_display' => array(
                    'label' => 'Property Details',
                    'type' => 'textarea',
                    'readonly' => true,
                    'class' => 'full-width',
                    'description' => 'Detailed property information associated with the selected client',
                ),
                'property_id_hidden' => array(
                    'label' => '',
                    'type' => 'hidden',
                    'class' => '',
                ),
                'departure_date' => array(
                    'label' => 'Departure Date',
                    'type' => 'date',
                    'class' => '',
                ),
                'pre_inspection' => array(
                    'label' => 'Pre-inspection',
                    'type' => 'date',
                    'class' => '',
                ),
                'mover_packing' => array(
                    'label' => 'Mover Packing',
                    'type' => 'date',
                    'class' => '',
                ),
                'wifi_closure_label' => array(
                    'label' => 'Wifi Closure:',
                    'type' => 'label_only',
                    'class' => 'departure-section',
                ),
                'wifi_closure' => array(
                    'label' => 'Date',
                    'type' => 'date',
                    'class' => 'subfield',
                ),
                'wifi_closure_notes' => array(
                    'label' => 'Notes',
                    'type' => 'textarea',
                    'class' => 'subfield',
                ),
                'bank_closure_label' => array(
                    'label' => 'Bank Closure:',
                    'type' => 'label_only',
                    'class' => 'departure-section',
                ),
                'bank_closure' => array(
                    'label' => 'Date',
                    'type' => 'date',
                    'class' => 'subfield',
                ),
                'bank_closure_notes' => array(
                    'label' => 'Notes',
                    'type' => 'textarea',
                    'class' => 'subfield',
                ),
                'utility_closure_label' => array(
                    'label' => 'Utility Closure:',
                    'type' => 'label_only',
                    'class' => 'departure-section',
                ),
                'utility_closure' => array(
                    'label' => 'Date',
                    'type' => 'date',
                    'class' => 'subfield',
                ),
                'utility_closure_notes' => array(
                    'label' => 'Notes',
                    'type' => 'textarea',
                    'class' => 'subfield',
                ),
                'notes' => array(
                    'label' => 'Notes',
                    'type' => 'textarea',
                    'class' => 'full-width',
                ),
            ),
        ),
    );

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize the select options
        $this->fields['basic_info']['fields']['customer_id']['options'] = $this->get_customers_options();
        
        // Register meta boxes
        add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
        add_action('wp_ajax_get_property_details', array($this, 'ajax_get_property_details'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_scripts($hook) {
        global $post;
        
        if ($hook == 'post-new.php' || $hook == 'post.php') {
            if (isset($post) && 'departure_service' === $post->post_type) {
                wp_enqueue_script('jquery-ui-datepicker');
                // Enqueue custom admin script for dynamic property details
                wp_enqueue_script(
                    'departure-service-admin',
                    get_template_directory_uri() . '/includes/departure-services/assets/js/admin.js',
                    array('jquery'),
                    '1.0.0',
                    true
                );
                wp_localize_script('departure-service-admin', 'departure_service_data', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce'    => wp_create_nonce('departure_service_nonce'),
                ));
                
                // Cargar los estilos de jQuery UI para el datepicker
                wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', array(), '1.13.2');
                
                // Add custom styles
                wp_add_inline_style('jquery-ui-style', '
                    /* Contenedor principal */
                    .departure-meta-box-container {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        grid-gap: 20px;
                        padding: 15px;
                        background-color: #f9f9f9;
                        border-radius: 5px;
                    }
                    
                    /* Título principal */
                    .departure-meta-box-container h3 {
                        grid-column: span 2;
                        margin-top: 0;
                        padding-bottom: 10px;
                        border-bottom: 1px solid #e2e4e7;
                        color: #23282d;
                    }
                    
                    /* Campos generales */
                    .departure-meta-box-field {
                        margin-bottom: 20px;
                        background: #fff;
                        padding: 15px;
                        border-radius: 4px;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
                    }
                    
                    /* Etiquetas de campos */
                    .departure-meta-box-field label {
                        display: block;
                        margin-bottom: 8px;
                        font-weight: 600;
                        color: #23282d;
                        font-size: 14px;
                    }
                    
                    /* Inputs, selects y textareas */
                    .departure-meta-box-field input[type="text"],
                    .departure-meta-box-field input[type="date"],
                    .departure-meta-box-field select,
                    .departure-meta-box-field textarea {
                        width: 100%;
                        padding: 8px 10px;
                        border: 1px solid #ddd;
                        border-radius: 4px;
                        box-shadow: inset 0 1px 2px rgba(0,0,0,0.07);
                    }
                    
                    /* Campos que ocupan todo el ancho */
                    .departure-meta-box-field.full-width {
                        grid-column: span 2;
                    }
                    
                    /* Campos de checkbox */
                    .checkbox-field {
                        display: flex;
                        align-items: center;
                    }
                    .checkbox-field input[type="checkbox"] {
                        margin-right: 8px;
                    }
                    
                    /* Secciones principales (Wifi Closure, Bank Closure, etc.) */
                    .departure-section {
                        grid-column: span 2;
                        margin-top: 10px;
                        margin-bottom: 5px;
                        padding: 0;
                        background: transparent;
                        box-shadow: none;
                    }
                    
                    /* Títulos de secciones */
                    h4.section-title {
                        margin: 0 0 10px 0 !important;
                        font-weight: 600;
                        color: #23282d;
                        font-size: 16px;
                        padding-bottom: 5px;
                        border-bottom: 1px solid #eee;
                    }
                    
                    /* Subcampos */
                    .departure-meta-box-field.subfield {
                        margin-left: 0;
                        position: relative;
                        margin-bottom: 15px;
                        padding: 12px 15px 12px 30px;
                    }
                    
                    /* Círculo para subcampos */
                    .departure-meta-box-field.subfield:before {
                        content: "○";
                        position: absolute;
                        left: 10px;
                        top: 14px;
                        font-size: 12px;
                        color: #0073aa;
                    }
                    
                    /* Ajustes para datepicker */
                    .ui-datepicker {
                        background-color: #fff;
                        border: 1px solid #ddd;
                        border-radius: 4px;
                        box-shadow: 0 3px 6px rgba(0,0,0,0.1);
                    }
                    
                    /* Responsive para móviles */
                    @media (max-width: 782px) {
                        .departure-meta-box-container {
                            display: block;
                        }
                        .departure-meta-box-field {
                            margin-bottom: 15px;
                        }
                    }
                ');
                
                // Add custom styles
                wp_add_inline_style('jquery-ui-style', '
                    /* Contenedor principal */
                    .departure-meta-box-container {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        grid-gap: 20px;
                        padding: 15px;
                        background-color: #f9f9f9;
                        border-radius: 5px;
                    }
                    
                    /* Título principal */
                    .departure-meta-box-container h3 {
                        grid-column: span 2;
                        margin-top: 0;
                        padding-bottom: 10px;
                        border-bottom: 1px solid #e2e4e7;
                        color: #23282d;
                    }
                    
                    /* Campos generales */
                    .departure-meta-box-field {
                        margin-bottom: 20px;
                        background: #fff;
                        padding: 15px;
                        border-radius: 4px;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
                    }
                    
                    /* Etiquetas de campos */
                    .departure-meta-box-field label {
                        display: block;
                        margin-bottom: 8px;
                        font-weight: 600;
                        color: #23282d;
                        font-size: 14px;
                    }
                    
                    /* Inputs, selects y textareas */
                    .departure-meta-box-field input[type="text"],
                    .departure-meta-box-field input[type="date"],
                    .departure-meta-box-field select,
                    .departure-meta-box-field textarea {
                        width: 100%;
                        padding: 8px 10px;
                        border: 1px solid #ddd;
                        border-radius: 4px;
                        box-shadow: inset 0 1px 2px rgba(0,0,0,0.07);
                    }
                    
                    /* Campos que ocupan todo el ancho */
                    .departure-meta-box-field.full-width {
                        grid-column: span 2;
                    }
                    
                    /* Campos de checkbox */
                    .checkbox-field {
                        display: flex;
                        align-items: center;
                    }
                    .checkbox-field input[type="checkbox"] {
                        margin-right: 8px;
                    }
                    
                    /* Secciones principales (Wifi Closure, Bank Closure, etc.) */
                    .departure-section {
                        grid-column: span 2;
                        margin-top: 10px;
                        margin-bottom: 5px;
                        padding: 0;
                        background: transparent;
                        box-shadow: none;
                    }
                    
                    /* Títulos de secciones */
                    h4.section-title {
                        margin: 0 0 10px 0 !important;
                        font-weight: 600;
                        color: #23282d;
                        font-size: 16px;
                        padding-bottom: 5px;
                        border-bottom: 1px solid #eee;
                    }
                    
                    /* Subcampos */
                    .departure-meta-box-field.subfield {
                        margin-left: 0;
                        position: relative;
                        margin-bottom: 15px;
                        padding: 12px 15px 12px 30px;
                    }
                    
                    /* Círculo para subcampos */
                    .departure-meta-box-field.subfield:before {
                        content: "○";
                        position: absolute;
                        left: 10px;
                        top: 14px;
                        font-size: 12px;
                        color: #0073aa;
                    }
                    
                    /* Ajustes para datepicker */
                    .ui-datepicker {
                        background-color: #fff;
                        border: 1px solid #ddd;
                        border-radius: 4px;
                        box-shadow: 0 3px 6px rgba(0,0,0,0.1);
                    }
                    
                    /* Responsive para móviles */
                    @media (max-width: 782px) {
                        .departure-meta-box-container {
                            display: block;
                        }
                        .departure-meta-box-field {
                            margin-bottom: 15px;
                        }
                    }
                ');
                
                // Add datepicker initialization
                wp_add_inline_script('jquery-ui-datepicker', '
                    jQuery(document).ready(function($) {
                        $(".departure-date-field").datepicker({
                            dateFormat: "yy-mm-dd",
                            changeMonth: true,
                            changeYear: true
                        });
                    });
                ');
            }
        }
    }

    /**
     * Register meta boxes
     */
    public function register_meta_boxes() {
        add_meta_box(
            'departure_service_details',
            __('Departure Service Details', 'houses-theme'),
            array($this, 'render_meta_box'),
            'departure_service',
            'normal',
            'high'
        );
    }

    /**
     * Render meta box
     */
    public function render_meta_box($post) {
        wp_nonce_field('houses_departure_service_details', 'houses_departure_service_details_nonce');
        
        // Get all field values
        $values = array();
        foreach ($this->fields as $section) {
            foreach ($section['fields'] as $field_id => $field) {
                $values[$field_id] = get_post_meta($post->ID, $field_id, true);
            }
        }
        
        // Output fields
        echo '<div class="departure-meta-box-container">';
        
        foreach ($this->fields as $section_id => $section) {
            echo '<h3>' . esc_html($section['title']) . '</h3>';
            
            foreach ($section['fields'] as $field_id => $field) {
                $field['id'] = $field_id;
                $class = isset($field['class']) ? $field['class'] : '';
                
                echo '<div class="departure-meta-box-field ' . esc_attr($class) . '">';
                if ($field['type'] !== 'label_only') {
                    echo '<label for="' . esc_attr($field_id) . '">' . esc_html($field['label']) . '</label>';
                }
                $this->render_field($field, $values[$field_id]);
                if (!empty($field['description'])) {
                    echo '<p class="description">' . esc_html($field['description']) . '</p>';
                }
                echo '</div>';
            }
        }
        
        echo '</div>';
    }

    /**
     * Save meta box content
     */
    public function save_meta_boxes($post_id) {
        // Check if our nonce is set
        if (!isset($_POST['houses_departure_service_details_nonce'])) {
            return;
        }

        // Verify that the nonce is valid
        if (!wp_verify_nonce($_POST['houses_departure_service_details_nonce'], 'houses_departure_service_details')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save all fields
        foreach ($this->fields as $section) {
            foreach ($section['fields'] as $field_id => $field) {
                if (isset($_POST[$field_id])) {
                    if ($field['type'] === 'textarea') {
                        update_post_meta($post_id, $field_id, sanitize_textarea_field($_POST[$field_id]));
                    } else {
                        update_post_meta($post_id, $field_id, sanitize_text_field($_POST[$field_id]));
                    }
                }
            }
        }
    }

    /**
     * AJAX handler: return detailed property info for selected client
     */
    public function ajax_get_property_details() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'departure_service_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
        }
        $client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
        if (!$client_id) {
            wp_send_json_error(array('message' => 'Invalid client') );
        }
        // Get latest client_lease for this client
        $leases = get_posts(array(
            'post_type'      => 'client_lease',
            'meta_key'       => 'client_id',
            'meta_value'     => $client_id,
            'posts_per_page' => 1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'fields'         => 'ids',
        ));
        if (empty($leases)) {
            wp_send_json_error(array('message' => 'No lease found for client') );
        }
        $lease_id    = $leases[0];
        $property_id = get_post_meta($lease_id, 'property_id_hidden', true);
        if (!$property_id) {
            wp_send_json_error(array('message' => 'No property associated to lease') );
        }
        $property_post = get_post($property_id);
        if (!$property_post) {
            wp_send_json_error(array('message' => 'Property not found') );
        }
        $details = $property_post->post_title;
        $meta_values = get_post_meta($property_id);
        $exclude_keys = array('gallery_images');
        foreach ($meta_values as $key => $vals) {
            // Skip private/meta keys that start with underscore or explicitly excluded keys
            if (substr($key, 0, 1) === '_' || in_array($key, $exclude_keys, true)) {
                continue;
            }
            // Get first value only if it is an array
            $val = is_array($vals) ? reset($vals) : $vals;
            // Skip empty or arrays/objects
            if ($val === '' || is_array($val) || is_object($val)) {
                continue;
            }
            $pretty_key = ucwords(str_replace('_', ' ', $key));
            $details   .= "\n{$pretty_key}: {$val}";
        }
        wp_send_json_success(array(
            'property_id' => $property_id,
            'details'     => $details,
        ));
    }

    /**
     * Get customers for select options
     */
    public function get_customers_options() {
        $options = array('' => __('Select Client', 'houses-theme'));
        
        // Get all customer IDs that have a client_lease
        $customers_with_lease = array();
        
        // Find all client_lease posts
        $leases = get_posts(array(
            'post_type' => 'client_lease',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ));
        
        // Collect all customer IDs from these leases
        foreach ($leases as $lease_id) {
            $client_id = get_post_meta($lease_id, 'client_id', true);
            if (!empty($client_id)) {
                $customers_with_lease[] = $client_id;
            }
        }
        
        // If no customers with leases found, return empty options
        if (empty($customers_with_lease)) {
            return $options;
        }
        
        // Get customers that have a lease
        $customers = get_posts(array(
            'post_type' => 'customer',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'post__in' => $customers_with_lease,
        ));
        
        foreach ($customers as $customer) {
            $first_name = get_post_meta($customer->ID, 'first_name', true);
            $last_name = get_post_meta($customer->ID, 'last_name', true);
            $display_name = $first_name && $last_name ? $first_name . ' ' . $last_name : $customer->post_title;
            
            $options[$customer->ID] = $display_name;
        }
        
        return $options;
    }

    /**
     * Get properties for select options
     */
    public function get_properties_options() {
        $options = array('' => __('Select Property', 'houses-theme'));
        
        $properties = get_posts(array(
            'post_type' => 'property',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));
        
        foreach ($properties as $property) {
            $options[$property->ID] = $property->post_title;
        }
        
        return $options;
    }

    /**
     * Render field
     */
    public function render_field($field, $value) {
        switch ($field['type']) {
            case 'label_only':
                // No input field, just a label
                echo '<h4 class="section-title">' . esc_html($field['label']) . '</h4>';
                break;
                
            case 'select':
                echo '<select id="' . esc_attr($field['id']) . '" name="' . esc_attr($field['id']) . '">';
                foreach ($field['options'] as $option_value => $option_label) {
                    echo '<option value="' . esc_attr($option_value) . '" ' . selected($value, $option_value, false) . '>' . esc_html($option_label) . '</option>';
                }
                echo '</select>';
                break;
                
            case 'date':
                echo '<input type="date" id="' . esc_attr($field['id']) . '" name="' . esc_attr($field['id']) . '" value="' . esc_attr($value) . '" class="departure-date-field">';
                break;
                
            case 'text':
                $readonly_attr = (isset($field['readonly']) && $field['readonly']) ? 'readonly="readonly"' : '';
                echo '<input type="text" id="' . esc_attr($field['id']) . '" name="' . esc_attr($field['id']) . '" value="' . esc_attr($value) . '" ' . $readonly_attr . '>';
                break;
                
            case 'textarea':
                $readonly_attr = (isset($field['readonly']) && $field['readonly']) ? 'readonly="readonly"' : '';
                echo '<textarea id="' . esc_attr($field['id']) . '" name="' . esc_attr($field['id']) . '" rows="4" ' . $readonly_attr . '>' . esc_textarea($value) . '</textarea>';
                break;
                
            case 'checkbox':
                echo '<div class="checkbox-field">';
                echo '<input type="checkbox" id="' . esc_attr($field['id']) . '" name="' . esc_attr($field['id']) . '" value="1" ' . checked($value, '1', false) . '>';
                echo '</div>';
                break;
                
            default:
                echo '<input type="text" id="' . esc_attr($field['id']) . '" name="' . esc_attr($field['id']) . '" value="' . esc_attr($value) . '">';
                break;
        }
    }
}

// Initialize the meta boxes
new Houses_Departure_Services_Meta_Boxes();

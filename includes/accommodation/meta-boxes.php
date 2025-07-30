<?php
/**
 * Settling-In Services Meta Boxes Handler
 *
 * @package Houses Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Houses_Accommodation_Meta_Boxes
 */
class Houses_Accommodation_Meta_Boxes
{
    /**
     * Meta box fields configuration
     */
    private $fields = array(
        'basic_info' => array(
            'title' => 'Settling-In Service Details',
            'fields' => array(
                'customer_id' => array(
                    'label' => 'Client',
                    'type' => 'select',
                    'options' => array(), // Will be populated in constructor
                ),
                'intro_consultation_call' => array(
                    'label' => 'Briefing Call',
                    'type' => 'checkbox',
                ),
                'intro_consultation_call_date' => array(
                    'label' => 'Completed At (Briefing Call)',
                    'type' => 'date',
                ),
                'home_search_date' => array(
                    'label' => 'Home search date',
                    'type' => 'date',
                ),
                'home_search_completed_at' => array(
                    'label' => 'Completed At (Home search)',
                    'type' => 'date',
                ),
                'lease_signed' => array(
                    'label' => 'Lease signed',
                    'type' => 'checkbox',
                ),
                'lease_signed_completed_at' => array(
                    'label' => 'Completed At (Lease signed)',
                    'type' => 'date',
                ),
                'check_in' => array(
                    'label' => 'Check in',
                    'type' => 'checkbox',
                ),
                'check_in_completed_at' => array(
                    'label' => 'Completed At (Check in)',
                    'type' => 'date',
                ),
                'notes' => array(
                    'label' => 'Notes',
                    'type' => 'textarea',
                ),
            ),
        ),
        'wifi_setup' => array(
            'title' => 'Wifi Set Up',
            'fields' => array(
                'wifi_set_up' => array(
                    'label' => 'Wifi Set Up',
                    'type' => 'checkbox',
                ),
                'wifi_set_up_completed_at' => array(
                    'label' => 'Completed At (Wifi Set Up)',
                    'type' => 'date',
                ),
                'telecom_name' => array(
                    'label' => 'Telecom Name',
                    'type' => 'text',
                ),
                'monthly_fee' => array(
                    'label' => 'Monthly Fee',
                    'type' => 'number',
                    'step' => '0.01',
                ),
                'payment_term' => array(
                    'label' => 'Payment Term',
                    'type' => 'number',
                ),
            ),
        ),
        'mobile_services' => array(
            'title' => 'Mobile Services',
            'fields' => array(
                'mobile_set_up' => array(
                    'label' => 'Mobile Services',
                    'type' => 'checkbox',
                ),
                'mobile_set_up_completed_at' => array(
                    'label' => 'Completed At (Mobile Services)',
                    'type' => 'date',
                ),
                'local_mobile_number' => array(
                    'label' => 'Local mobile #',
                    'type' => 'text',
                ),
                'telecom_company' => array(
                    'label' => 'Telecom Company',
                    'type' => 'text',
                ),
            ),
        ),
        'bank_account' => array(
            'title' => 'Bank Account',
            'fields' => array(
                'bank_account_set_up' => array(
                    'label' => 'Bank Account Set up',
                    'type' => 'checkbox',
                ),
                'bank_account_set_up_completed_at' => array(
                    'label' => 'Completed At (Bank Account Set up)',
                    'type' => 'date',
                ),
            ),
        ),
        'drivers_license' => array(
            'title' => 'Driver\'s License Conversion',
            'fields' => array(
                'license_conversion' => array(
                    'label' => 'License Conversion',
                    'type' => 'checkbox',
                ),
                'license_conversion_completed_at' => array(
                    'label' => 'Completed At (License Conversion)',
                    'type' => 'date',
                ),
                'domestic_license_location' => array(
                    'label' => 'Domestic License (Location)',
                    'type' => 'text',
                ),
                'license_completion' => array(
                    'label' => 'License Completion',
                    'type' => 'checkbox',
                ),
                'license_notes' => array(
                    'label' => 'Notes',
                    'type' => 'textarea',
                ),
            ),
        ),
        'departure_services' => array(
            'title' => 'Departure Services',
            'fields' => array(
                'departure_date' => array(
                    'label' => 'Departure Date',
                    'type' => 'date',
                ),
                'pre_inspection_date' => array(
                    'label' => 'Pre-inspection',
                    'type' => 'date',
                ),
                'mover_packing_date' => array(
                    'label' => 'Mover Packing',
                    'type' => 'date',
                ),
                'wifi_closure_date' => array(
                    'label' => 'Wifi Closure',
                    'type' => 'date',
                ),
                'wifi_closure_notes' => array(
                    'label' => 'Notes',
                    'type' => 'textarea',
                ),
                'bank_closure_date' => array(
                    'label' => 'Bank Closure',
                    'type' => 'date',
                ),
                'utility_closure_date' => array(
                    'label' => 'Utility Closure',
                    'type' => 'date',
                ),
                'departure_notes' => array(
                    'label' => 'Notes',
                    'type' => 'textarea',
                ),
            ),
        ),
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        // Initialize the select options
        if (isset($this->fields['basic_info']['fields']['customer_id'])) {
            $this->fields['basic_info']['fields']['customer_id']['options'] = $this->get_customers_options();
        }

        // Register meta boxes
        add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'), 10, 1);

        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // AJAX endpoints for dynamic client selection
        add_action('wp_ajax_get_client_lease_info', array($this, 'ajax_get_client_lease_info'));
        add_action('wp_ajax_update_accommodation_title', array($this, 'ajax_update_accommodation_title'));
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_scripts($hook)
    {
        global $post;

        if ($hook == 'post-new.php' || $hook == 'post.php') {
            if (isset($post) && 'accommodation' === $post->post_type) {
                wp_enqueue_script('jquery-ui-datepicker');

                // Enqueue and localize script for AJAX
                wp_enqueue_script('accommodation-admin', get_template_directory_uri() . '/includes/accommodation/assets/admin.js', array('jquery'), '1.0.0', true);
                wp_localize_script('accommodation-admin', 'accommodationAjax', array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('houses_accommodation_details')
                ));

                // Cargar los estilos de jQuery UI para el datepicker
                wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', array(), '1.13.2');

                // Add custom styles
                wp_add_inline_style('jquery-ui-style', '
                    .accommodation-meta-box-container {
                        grid-gap: 15px;
                        max-width: 600px;
                    }
                    .accommodation-meta-box-field {
                        grid-column: span 2;
                        margin-bottom: 15px;
                    }
                    .accommodation-meta-box-field label {
                        display: block;
                        margin-bottom: 5px;
                        font-weight: 600;
                    }
                    .accommodation-meta-box-field input[type="text"],
                    .accommodation-meta-box-field input[type="date"],
                    .accommodation-meta-box-field select,
                    .accommodation-meta-box-field textarea {
                        width: 100%;
                    }
                    .checkbox-field {
                        display: flex;
                        align-items: center;
                    }
                    .checkbox-field input[type="checkbox"] {
                        margin-right: 8px;
                    }
                    .client-lease-info {
                        background: #f9f9f9;
                        padding: 10px;
                        margin-top: 10px;
                        border-left: 3px solid #0073aa;
                    }
                ');

                // JavaScript functionality is now handled by external admin.js file
            }
        }
    }

    /**
     * Register meta boxes
     */
    public function register_meta_boxes()
    {
        // Register a separate meta box for each section so they appear as individual boxes
        foreach ($this->fields as $section_id => $section) {
            add_meta_box(
                'accommodation_' . $section_id,
                __($section['title'], 'houses-theme'),
                array($this, 'render_section_meta_box'),
                'accommodation',
                'normal',
                'high',
                array('section_id' => $section_id)
            );
        }
    }

    /**
     * Render a single section meta box.
     *
     * @param WP_Post $post The post object.
     * @param array   $metabox Metabox callback arguments.
     */
    public function render_section_meta_box($post, $metabox)
    {
        $section_id = isset($metabox['args']['section_id']) ? $metabox['args']['section_id'] : '';
        if (!$section_id || !isset($this->fields[$section_id])) {
            return;
        }

        // Add nonce field only for the first section to avoid duplicates
        if ($section_id === 'basic_info') {
            wp_nonce_field('houses_accommodation_details', 'houses_accommodation_details_nonce');
        }

        // Gather current values for this section.
        $values = array();
        foreach ($this->fields[$section_id]['fields'] as $field_id => $field) {
            $values[$field_id] = get_post_meta($post->ID, $field_id, true);
        }

        // Output the fields.
        echo '<div class="accommodation-meta-box-container">';

        // Define checkbox-date pairs for this section
        $checkbox_date_pairs = array();
        if ($section_id === 'basic_info') {
            $checkbox_date_pairs = array(
                'intro_consultation_call' => 'intro_consultation_call_date',
                'lease_signed' => 'lease_signed_completed_at',
                'check_in' => 'check_in_completed_at',
            );
        } elseif ($section_id === 'wifi_setup') {
            $checkbox_date_pairs = array(
                'wifi_set_up' => 'wifi_set_up_completed_at',
            );
        } elseif ($section_id === 'mobile_services') {
            $checkbox_date_pairs = array(
                'mobile_set_up' => 'mobile_set_up_completed_at',
            );
        } elseif ($section_id === 'bank_account') {
            $checkbox_date_pairs = array(
                'bank_account_set_up' => 'bank_account_set_up_completed_at',
            );
        } elseif ($section_id === 'drivers_license') {
            $checkbox_date_pairs = array(
                'license_conversion' => 'license_conversion_completed_at',
            );
        }

        $skip_fields = array();

        foreach ($this->fields[$section_id]['fields'] as $field_id => $field) {
            // Validate field configuration
            if (!is_array($field) || !isset($field['type']) || !isset($field['label'])) {
                continue;
            }

            if (in_array($field_id, $skip_fields, true)) {
                continue; // Date field already rendered with its checkbox
            }

            // Group checkbox + date input when mapping exists
            if (isset($checkbox_date_pairs[$field_id])) {
                $date_field_id = $checkbox_date_pairs[$field_id];
                if (isset($this->fields[$section_id]['fields'][$date_field_id])) {
                    $date_field_conf = $this->fields[$section_id]['fields'][$date_field_id];

                    // Validate date field configuration
                    if (!is_array($date_field_conf) || !isset($date_field_conf['label'])) {
                        continue;
                    }

                    echo '<div class="accommodation-meta-box-field checkbox-group">';
                    echo '<label for="' . esc_attr($field_id) . '">' . esc_html($field['label']) . '</label>';
                    echo '<div class="checkbox-field">';
                    // Checkbox
                    $checkbox_value = isset($values[$field_id]) ? $values[$field_id] : '';
                    echo '<input type="checkbox" id="' . esc_attr($field_id) . '" name="' . esc_attr($field_id) . '" value="1" ' . checked($checkbox_value, '1', false) . ' />';
                    echo '<span class="checkbox-label">' . esc_html($field['label']) . '</span>';
                    echo '</div>';
                    // Date input
                    echo '<label for="' . esc_attr($date_field_id) . '">' . esc_html($date_field_conf['label']) . '</label>';
                    $date_value = isset($values[$date_field_id]) ? $values[$date_field_id] : '';
                    echo '<input type="text" id="' . esc_attr($date_field_id) . '" name="' . esc_attr($date_field_id) . '" value="' . esc_attr($date_value) . '" class="widefat accommodation-date-field" />';
                    echo '</div>'; // .accommodation-meta-box-field

                    // Mark date field as rendered
                    $skip_fields[] = $date_field_id;
                    continue;
                }
            }

            // Render regular fields
            $field['id'] = $field_id;
            $class = isset($field['class']) ? $field['class'] : '';
            $field_value = isset($values[$field_id]) ? $values[$field_id] : '';

            echo '<div class="accommodation-meta-box-field ' . esc_attr($class) . '">';
            echo '<label for="' . esc_attr($field_id) . '">' . esc_html($field['label']) . '</label>';
            $this->render_field($field, $field_value);
            echo '</div>';
        }

        echo '</div>';
    }

    /**
     * Render meta box
     */
    public function render_meta_box($post)
    {
        wp_nonce_field('houses_accommodation_details', 'houses_accommodation_details_nonce');

        // Get all field values
        $values = array();
        foreach ($this->fields as $section) {
            foreach ($section['fields'] as $field_id => $field) {
                $values[$field_id] = get_post_meta($post->ID, $field_id, true);
            }
        }

        // Output fields
        echo '<div class="accommodation-meta-box-container">';

        foreach ($this->fields as $section_id => $section) {
            echo '<h3>' . esc_html($section['title']) . '</h3>';

            foreach ($section['fields'] as $field_id => $field) {
                $field['id'] = $field_id;
                $class = isset($field['class']) ? $field['class'] : '';

                echo '<div class="accommodation-meta-box-field ' . esc_attr($class) . '">';
                echo '<label for="' . esc_attr($field_id) . '">' . esc_html($field['label']) . '</label>';
                $this->render_field($field, $values[$field_id]);
                echo '</div>';
            }
        }

        echo '</div>';
    }

    /**
     * Save meta box content
     */
    public function save_meta_boxes($post_id)
    {
        // Debug logging - remove after fixing
        error_log('ACCOMMODATION SAVE: Starting save for post ID: ' . $post_id);
        error_log('ACCOMMODATION SAVE: Post type: ' . get_post_type($post_id));

        // Check if this is the correct post type
        if (get_post_type($post_id) !== 'accommodation') {
            return;
        }

        // Check if our nonce is set
        if (!isset($_POST['houses_accommodation_details_nonce'])) {
            return;
        }

        // Verify that the nonce is valid
        if (!wp_verify_nonce($_POST['houses_accommodation_details_nonce'], 'houses_accommodation_details')) {
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
        $saved_count = 0;
        foreach ($this->fields as $section) {
            if (!isset($section['fields']) || !is_array($section['fields'])) {
                continue;
            }

            foreach ($section['fields'] as $field_id => $field) {
                // Skip if field configuration is invalid
                if (!is_array($field) || !isset($field['type'])) {
                    error_log('ACCOMMODATION SAVE: Skipping invalid field: ' . $field_id);
                    continue;
                }

                if ($field['type'] === 'checkbox') {
                    // Handle checkbox fields
                    $value = isset($_POST[$field_id]) ? '1' : '0';
                    $result = update_post_meta($post_id, $field_id, $value);
                    $saved_count++;
                } elseif (isset($_POST[$field_id])) {
                    if ($field['type'] === 'textarea') {
                        $value = sanitize_textarea_field($_POST[$field_id]);
                        $result = update_post_meta($post_id, $field_id, $value);
                    } elseif ($field['type'] === 'number') {
                        $value = floatval($_POST[$field_id]);
                        $result = update_post_meta($post_id, $field_id, $value);
                    } else {
                        $value = sanitize_text_field($_POST[$field_id]);
                        $result = update_post_meta($post_id, $field_id, $value);
                    }
                    $saved_count++;
                } else {
                    // Clear the field if it's not set (except for checkboxes which are handled above)
                    if ($field['type'] !== 'checkbox') {
                        $result = update_post_meta($post_id, $field_id, '');
                    }
                }
            }
        }

    }

    /**
     * Get customers for select options
     * Only returns customers who have existing leases
     */
    public function get_customers_options()
    {
        $options = array('' => __('Select Client', 'houses-theme'));

        // Get all client leases to find customers with leases
        $leases = get_posts(array(
            'post_type' => 'client_lease',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'client_id',
                    'compare' => 'EXISTS'
                )
            )
        ));

        // Collect unique customer IDs that have leases
        $customer_ids_with_leases = array();
        foreach ($leases as $lease) {
            $client_id = get_post_meta($lease->ID, 'client_id', true);
            if ($client_id && !in_array($client_id, $customer_ids_with_leases)) {
                $customer_ids_with_leases[] = $client_id;
            }
        }

        // If no customers have leases, return empty options
        if (empty($customer_ids_with_leases)) {
            return $options;
        }

        // Get only customers who have leases
        $customers = get_posts(array(
            'post_type' => 'customer',
            'posts_per_page' => -1,
            'post__in' => $customer_ids_with_leases,
            'orderby' => 'title',
            'order' => 'ASC'
        ));

        foreach ($customers as $customer) {
            $options[$customer->ID] = $customer->post_title;
        }

        return $options;
    }

    /**
     * AJAX handler to get client lease information
     */
    public function ajax_get_client_lease_info()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'houses_accommodation_details')) {
            wp_die('Security check failed');
        }

        $client_id = intval($_POST['client_id']);
        
        if (!$client_id) {
            wp_send_json_error('Invalid client ID');
        }

        // Get all leases for this client
        $leases = get_posts(array(
            'post_type' => 'client_lease',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'client_id',
                    'value' => $client_id,
                    'compare' => '='
                )
            )
        ));

        $lease_data = array();
        foreach ($leases as $lease) {
            $property_id = get_post_meta($lease->ID, 'property_id_hidden', true);
            $property_info = array(
                'name' => '',
                'address' => '',
                'chinese_address' => '',
                'rent' => '',
                'bedroom' => '',
                'bathroom' => '',
                'floor' => '',
                'total_floor' => '',
                'net_size' => '',
                'square_meters' => '',
                'property_type' => '',
                'metro_line' => '',
                'station' => '',
                'parking' => '',
                'building_age' => '',
                'gym' => '',
                'swimming_pool' => ''
            );
            
            if ($property_id) {
                $property_post = get_post($property_id);
                if ($property_post) {
                    $property_info['name'] = $property_post->post_title;
                    
                    // Get all property meta fields
                    $property_info['address'] = get_post_meta($property_id, 'address', true);
                    $property_info['chinese_address'] = get_post_meta($property_id, 'chinese_address', true);
                    $property_info['rent'] = get_post_meta($property_id, 'rent', true);
                    $property_info['bedroom'] = get_post_meta($property_id, 'bedroom', true);
                    $property_info['bathroom'] = get_post_meta($property_id, 'bathroom', true);
                    $property_info['floor'] = get_post_meta($property_id, 'floor', true);
                    $property_info['total_floor'] = get_post_meta($property_id, 'total_floor', true);
                    $property_info['net_size'] = get_post_meta($property_id, 'net_size', true);
                    $property_info['square_meters'] = get_post_meta($property_id, 'square_meters', true);
                    $property_info['property_type'] = get_post_meta($property_id, 'property_type', true);
                    $property_info['metro_line'] = get_post_meta($property_id, 'metro_line', true);
                    $property_info['station'] = get_post_meta($property_id, 'station', true);
                    $property_info['parking'] = get_post_meta($property_id, 'parking', true);
                    $property_info['building_age'] = get_post_meta($property_id, 'building_age', true);
                    $property_info['gym'] = get_post_meta($property_id, 'gym', true);
                    $property_info['swimming_pool'] = get_post_meta($property_id, 'swimming_pool', true);
                }
            }

            $lease_data[] = array(
                'id' => $lease->ID,
                'title' => $lease->post_title,
                'property_id' => $property_id,
                'property' => $property_info,
                'start_date' => get_post_meta($lease->ID, 'start_date', true),
                'end_date' => get_post_meta($lease->ID, 'end_date', true)
            );
        }

        wp_send_json_success($lease_data);
    }

    /**
     * AJAX handler to update accommodation title with client name
     */
    public function ajax_update_accommodation_title()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'houses_accommodation_details')) {
            wp_die('Security check failed');
        }

        $post_id = intval($_POST['post_id']);
        $client_name = sanitize_text_field($_POST['client_name']);
        
        if (!$post_id || !$client_name) {
            wp_send_json_error('Invalid parameters');
        }

        // Create new title with client name
        $new_title = 'Accommodation - ' . $client_name;
        
        // Update post title
        $updated = wp_update_post(array(
            'ID' => $post_id,
            'post_title' => $new_title
        ));

        if ($updated && !is_wp_error($updated)) {
            wp_send_json_success(array('title' => $new_title));
        } else {
            wp_send_json_error('Failed to update title');
        }
    }

    /**
     * Get properties for select options
     */
    public function get_properties_options()
    {
        $options = array('' => __('Select Property', 'houses-theme'));

        $properties = get_posts(array(
            'post_type' => 'property',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));

        foreach ($properties as $property) {
            $options[$property->ID] = $property->post_title;
        }

        return $options;
    }

    /**
     * Render field
     */
    private function render_field($field, $value)
    {
        // Validate field configuration
        if (!is_array($field) || !isset($field['type'])) {
            return;
        }

        switch ($field['type']) {
            case 'text':
                ?>
                <input type="text" id="<?php echo esc_attr($field['id']); ?>" name="<?php echo esc_attr($field['id']); ?>"
                    value="<?php echo esc_attr($value); ?>" class="widefat">
                <?php
                break;

            case 'number':
                $step = isset($field['step']) ? $field['step'] : '1';
                $min = isset($field['min']) ? $field['min'] : '';
                ?>
                <input type="number" id="<?php echo esc_attr($field['id']); ?>" name="<?php echo esc_attr($field['id']); ?>"
                    value="<?php echo esc_attr($value); ?>" step="<?php echo esc_attr($step); ?>" min="<?php echo esc_attr($min); ?>"
                    class="widefat">
                <?php
                break;

            case 'date':
                ?>
                <input type="text" id="<?php echo esc_attr($field['id']); ?>" name="<?php echo esc_attr($field['id']); ?>"
                    value="<?php echo esc_attr($value); ?>" class="widefat accommodation-date-field">
                <?php
                break;

            case 'select':
                ?>
                <select id="<?php echo esc_attr($field['id']); ?>" name="<?php echo esc_attr($field['id']); ?>" class="widefat">
                    <?php foreach ($field['options'] as $option_value => $option_label): ?>
                        <option value="<?php echo esc_attr($option_value); ?>" <?php selected($value, $option_value); ?>>
                            <?php echo esc_html($option_label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php
                break;

            case 'textarea':
                ?>
                <textarea id="<?php echo esc_attr($field['id']); ?>" name="<?php echo esc_attr($field['id']); ?>" class="widefat"
                    rows="4"><?php echo esc_textarea($value); ?></textarea>
                <?php
                break;

            case 'checkbox':
                ?>
                <div class="checkbox-field 0">
                    <input type="checkbox" id="<?php echo esc_attr($field['id']); ?>" name="<?php echo esc_attr($field['id']); ?>"
                        value="1" <?php checked($value, '1'); ?>>
                    <span class="checkbox-label"><?php _e('Yes', 'houses-theme'); ?></span>
                </div>
                <?php
                break;
        }
    }
}

// Initialize the meta boxes
new Houses_Accommodation_Meta_Boxes();

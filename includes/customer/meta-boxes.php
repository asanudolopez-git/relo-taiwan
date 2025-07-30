<?php

class Houses_Client_Meta_Boxes
{
    /**
     * Meta box fields configuration
     */
    private $fields = array(
        'basic_info' => array(
            'title' => 'Basic Information',
            'fields' => array(
                'company_id' => array(
                    'label' => 'Company',
                    'type' => 'select',
                    'options' => array(), // Will be populated dynamically
                    'class' => '',
                ),
                'title' => array(
                    'label' => 'Title',
                    'type' => 'select',
                    'options' => array(
                        '' => 'Select Title',
                        'mr' => 'Mr.',
                        'mrs' => 'Mrs.',
                        'ms' => 'Ms.',
                        'dr' => 'Dr.',
                        'prof' => 'Prof.',
                    ),
                    'class' => '',
                ),
                'first_name' => array(
                    'label' => 'First Name',
                    'type' => 'text',
                    'class' => '',
                    'required' => true,
                ),
                'last_name' => array(
                    'label' => 'Last Name',
                    'type' => 'text',
                    'class' => '',
                    'required' => true,
                ),
                'customer_post_id' => array(
                    'label' => 'Customer ID',
                    'type' => 'text',
                    'class' => '',
                ),
                'nationality' => array(
                    'label' => 'Nationality',
                    'type' => 'text',
                    'class' => '',
                ),
                'email' => array(
                    'label' => 'Email',
                    'type' => 'text',
                    'class' => '',
                ),
                'phone' => array(
                    'label' => 'Mobile',
                    'type' => 'text',
                    'class' => '',
                ),
                'assignment_date' => array(
                    'label' => 'Assignment Date',
                    'type' => 'text',
                    'class' => 'datepicker',
                ),
                'assignment_period' => array(
                    'label' => 'Assignment Period',
                    'type' => 'date_range',
                    'class' => '',
                ),
                'budget' => array(
                    'label' => 'Budget',
                    'type' => 'text',
                    'class' => 'budget-field',
                    'description' => 'Enter amount and it will be formatted as NT$',
                ),
                'preferred_location' => array(
                    'label' => 'Preferred Location',
                    'type' => 'text',
                    'class' => '',
                ),
                'family_size' => array(
                    'label' => 'Family Size',
                    'type' => 'number',
                    'min' => '1',
                    'class' => '',
                ),
                'office_address' => array(
                    'label' => 'Office Address',
                    'type' => 'text',
                    'class' => 'full-width',
                ),
            ),
        ),
        'property_requirements' => array(
            'title' => 'Property Requirements',
            'fields' => array(
                'property_type' => array(
                    'label' => 'Property Type',
                    'type' => 'radio',
                    'options' => array(
                        'apartment' => 'Apartment',
                        'house' => 'House',
                    ),
                ),
                'preferred_station' => array(
                    'label' => 'Preferred Station',
                    'type' => 'select',
                    'options' => array(), // Will be populated dynamically
                    'class' => 'houses-select2',
                ),
                'parking' => array(
                    'label' => 'Parking',
                    'type' => 'select',
                    'options' => array(
                        '' => 'Select',
                        'yes' => 'Yes',
                        'no' => 'No',
                        'optional' => 'Optional'
                    ),
                    'class' => '',
                ),
                'furnished' => array(
                    'label' => 'Furnished/Unfurnished',
                    'type' => 'select',
                    'options' => array(
                        '' => 'Select',
                        'furnished' => 'Furnished',
                        'unfurnished' => 'Unfurnished',
                        'partially' => 'Partially Furnished',
                        'any' => 'Any'
                    ),
                    'class' => '',
                ),
                'bedrooms' => array(
                    'label' => '# Bedrooms',
                    'type' => 'number',
                    'step' => '0.5',
                    'min' => '0',
                    'class' => '',
                ),
                'bathrooms' => array(
                    'label' => '# Bathrooms',
                    'type' => 'number',
                    'step' => '0.5',
                    'min' => '0',
                    'class' => '',
                ),
                'additional_notes' => array(
                    'label' => 'Additional Requirements',
                    'type' => 'textarea',
                    'class' => 'full-width',
                ),
            ),
        ),
        'additional_info' => array(
            'title' => 'Additional Information',
            'fields' => array(
                'notes' => array(
                    'label' => 'Notes',
                    'type' => 'textarea',
                    'class' => 'full-width',
                    'description' => 'Additional notes about the assignee',
                ),
            ),
        ),
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_init', array($this, 'populate_select_options'));

        // Hide title field on edit screen
        add_action('admin_head', array($this, 'hide_title_field'));
    }

    /**
     * Hide the title field and editor on customer edit screen
     */
    public function hide_title_field()
    {
        global $current_screen;
        if (!empty($current_screen) && $current_screen->post_type === 'customer') {
            echo '<style type="text/css">
                #titlediv { display: none !important; }
                #postdivrich { display: none !important; }
                #wp-content-editor-tools { display: none !important; }
                .wp-editor-area { display: none !important; }
                #wp-content-editor-container { display: none !important; }
                #ed_toolbar { display: none !important; }
            </style>';
        }
    }

    /**
     * Populate company dropdown options
     */
    public function populate_select_options()
    {
        // Get all companies
        $companies = get_posts(array(
            'post_type' => 'company',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        // Add empty option
        $this->fields['basic_info']['fields']['company_id']['options'] = array('' => 'Select Company');

        // Add companies to dropdown
        foreach ($companies as $company) {
            $this->fields['basic_info']['fields']['company_id']['options'][$company->ID] = $company->post_title;
        }

        // Populate stations
        $this->fields['property_requirements']['fields']['preferred_station']['options'] = $this->get_stations_options();
    }

    /**
     * Get stations for select options
     */
    public function get_stations_options()
    {
        $options = array('' => __('Select Station', 'houses-theme'));

        $stations = get_posts(array(
            'post_type' => 'station',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        foreach ($stations as $station) {
            $options[$station->ID] = $station->post_title;
        }

        return $options;
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts($hook)
    {
        global $post;

        if (($hook == 'post-new.php' || $hook == 'post.php') && isset($post) && $post->post_type === 'customer') {
            // Enqueue custom admin styles
            wp_enqueue_style(
                'houses-admin-customer',
                get_template_directory_uri() . '/includes/customer/assets/css/admin-customer.css',
                array(),
                _S_VERSION
            );

            // Enqueue media uploader
            wp_enqueue_media();

            // Enqueue jQuery UI datepicker
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_style(
                'jquery-ui-style',
                '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css',
                array(),
                '1.12.1'
            );

            // Enqueue license upload script
            wp_enqueue_script(
                'houses-admin-license',
                get_template_directory_uri() . '/includes/customer/assets/js/admin-license.js',
                array('jquery'),
                _S_VERSION,
                true
            );

            // Enqueue datepicker initialization script
            wp_enqueue_script(
                'houses-admin-datepicker',
                get_template_directory_uri() . '/includes/customer/assets/js/admin-datepicker.js',
                array('jquery', 'jquery-ui-datepicker'),
                _S_VERSION,
                true
            );

            // Enqueue budget formatter script
            wp_enqueue_script(
                'houses-admin-budget',
                get_template_directory_uri() . '/includes/customer/assets/js/admin-budget.js',
                array('jquery'),
                _S_VERSION,
                true
            );

            // Enqueue Select2 for searchable dropdowns
            wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0-rc.0');
            wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0-rc.0', true);
            wp_enqueue_script(
                'houses-admin-select2',
                get_template_directory_uri() . '/assets/js/admin-select2.js',
                array('jquery', 'select2-js'),
                _S_VERSION,
                true
            );
        }
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes()
    {
        add_meta_box(
            'houses_customer_meta_boxes',
            'Assignee Details',
            array($this, 'render_meta_boxes'),
            'customer',
            'normal',
            'high'
        );

        add_meta_box(
            'houses_customer_house_list',
            'House List Properties',
            array($this, 'render_house_list_properties'),
            'customer',
            'normal',
            'default'
        );
    }

    /**
     * Render meta boxes
     */
    public function render_meta_boxes($post)
    {
        wp_nonce_field('houses_customer_meta_boxes', 'houses_customer_meta_boxes_nonce');
        foreach ($this->fields as $section => $data) {
            echo '<div class="meta-box-section">';
            echo '<h3>' . esc_html($data['title']) . '</h3>';
            if (isset($data['fields'])) {
                $this->render_fields($data['fields'], $post);
            }
            echo '</div>';
        }
    }

    /**
     * Render House List Properties meta box
     */
    public function render_house_list_properties($post)
    {
        $customer_id = $post->ID;

        $house_lists = get_posts(array(
            'post_type' => 'customer-house-list',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'selected_customer',
                    'value' => $customer_id,
                    'compare' => '=',
                ),
            ),
        ));

        if (empty($house_lists)) {
            echo '<p>No house lists found for this customer.</p>';
            return;
        }

        echo '<table class="house-list-properties-table">';
        echo '<thead><tr><th>House List</th><th>Title</th><th>Property ID</th><th>Address</th><th>Price</th><th>Gross Size</th><th>Layout</th></tr></thead>';
        echo '<tbody>';

        $has_properties = false;

        foreach ($house_lists as $house_list) {
            $property_ids = get_post_meta($house_list->ID, 'property_list', true);
            $house_list_title = get_the_title($house_list->ID);
            $house_list_edit_link = get_edit_post_link($house_list->ID);

            if (!empty($property_ids) && is_array($property_ids)) {
                $has_properties = true;
                foreach ($property_ids as $property_id) {
                    $property = get_post($property_id);
                    if ($property) {
                        $edit_link = get_edit_post_link($property_id);
                        $property_meta = get_post_meta($property_id);

                        echo '<tr>';
                        echo '<td>';
                        if ($house_list_edit_link) {
                            echo '<a href="' . esc_url($house_list_edit_link) . '" target="_blank">' . esc_html($house_list_title) . '</a>';
                        } else {
                            echo esc_html($house_list_title);
                        }
                        echo '</td>';
                        echo '<td>';
                        if ($edit_link) {
                            echo '<a href="' . esc_url($edit_link) . '" target="_blank">' . esc_html($property->post_title) . '</a>';
                        } else {
                            echo esc_html($property->post_title);
                        }
                        echo '</td>';
                        echo '<td>' . esc_html($property_id) . '</td>';
                        echo '<td>' . esc_html($property_meta['address'][0] ?? '') . '</td>';
                        echo '<td>NT$ ' . esc_html(number_format_i18n(floatval($property_meta['price'][0] ?? 0))) . '</td>';
                        echo '<td>' . esc_html($property_meta['gross_size'][0] ?? '') . '</td>';
                        echo '<td>' . esc_html($property_meta['layout'][0] ?? '') . '</td>';
                        echo '</tr>';
                    }
                }
            }
        }

        echo '</tbody>';
        echo '</table>';

        if (!$has_properties) {
            echo '<p>No properties found in the house list(s) for this customer.</p>';
        }
    }

    /**
     * Render fields recursively
     */
    private function render_fields($fields, $post)
    {
        foreach ($fields as $field => $config) {
            if (isset($config['fields'])) {
                echo '<div class="meta-box-subsection">';
                echo '<h4>' . esc_html($config['title']) . '</h4>';
                $this->render_fields($config['fields'], $post);
                echo '</div>';
            } else {
                $value = get_post_meta($post->ID, $field, true);
                echo '<div class="meta-box-field">';
                $required = isset($config['required']) && $config['required'] ? ' <span class="required" style="color: red;">*</span>' : '';
                echo '<label for="' . esc_attr($field) . '">' . esc_html($config['label']) . $required . '</label>';
                switch ($config['type']) {
                    case 'text':
                        $required_attr = isset($config['required']) && $config['required'] ? ' required="required"' : '';
                        $field_value = ($field === 'customer_post_id') ? esc_attr($post->ID) : esc_attr($value);
                        $readonly_attr = ($field === 'customer_post_id') ? ' readonly="readonly"' : '';
                        echo '<input type="text" id="' . esc_attr($field) . '" name="' . esc_attr($field) . '" value="' . $field_value . '" class="' . esc_attr($config['class']) . '"' . $required_attr . $readonly_attr . '>';
                        break;
                    case 'number':
                        $step = isset($config['step']) ? $config['step'] : '1';
                        $min = isset($config['min']) ? $config['min'] : '';
                        echo '<input type="number" id="' . esc_attr($field) . '" name="' . esc_attr($field) . '" value="' . esc_attr($value) . '" step="' . esc_attr($step) . '" min="' . esc_attr($min) . '" class="' . esc_attr($config['class']) . '">';
                        break;
                    case 'textarea':
                        echo '<textarea id="' . esc_attr($field) . '" name="' . esc_attr($field) . '" class="' . esc_attr($config['class']) . '">' . esc_textarea($value) . '</textarea>';
                        break;
                    case 'select':
                        echo '<select id="' . esc_attr($field) . '" name="' . esc_attr($field) . '" class="' . esc_attr($config['class']) . '">';
                        foreach ($config['options'] as $option_value => $option_label) {
                            echo '<option value="' . esc_attr($option_value) . '" ' . selected($value, $option_value, false) . '>' . esc_html($option_label) . '</option>';
                        }
                        echo '</select>';
                        break;
                    case 'multiselect':
                        $selected_values = !empty($value) ? (is_array($value) ? $value : array($value)) : array();
                        echo '<select id="' . esc_attr($field) . '" name="' . esc_attr($field) . '[]" class="' . esc_attr($config['class']) . '" multiple="multiple">';
                        foreach ($config['options'] as $option_value => $option_label) {
                            echo '<option value="' . esc_attr($option_value) . '" ' . (in_array($option_value, $selected_values) ? 'selected' : '') . '>' . esc_html($option_label) . '</option>';
                        }
                        echo '</select>';
                        break;
                    case 'radio':
                        echo '<div class="radio-group">';
                        // Handle legacy array data from when this was a multiselect
                        $current_value = is_array($value) ? reset($value) : $value;
                        foreach ($config['options'] as $option_value => $option_label) {
                            echo '<label style="margin-right: 15px;"><input type="radio" name="' . esc_attr($field) . '" value="' . esc_attr($option_value) . '" ' . checked($current_value, $option_value, false) . '> ' . esc_html($option_label) . '</label>';
                        }
                        echo '</div>';
                        break;
                    case 'date_range':
                        $start_date = get_post_meta($post->ID, $field . '_start', true);
                        $end_date = get_post_meta($post->ID, $field . '_end', true);
                        echo '<div class="date-range-wrapper">';
                        echo '<input type="text" id="' . esc_attr($field) . '_start" name="' . esc_attr($field) . '_start" value="' . esc_attr($start_date) . '" class="datepicker" placeholder="Start Date">';
                        echo '<span>-</span>';
                        echo '<input type="text" id="' . esc_attr($field) . '_end" name="' . esc_attr($field) . '_end" value="' . esc_attr($end_date) . '" class="datepicker" placeholder="End Date">';
                        echo '</div>';
                        break;
                    case 'license_image':
                        echo '<div class="license-upload-container">';
                        echo '<input type="hidden" id="' . esc_attr($field) . '" name="' . esc_attr($field) . '" value="' . esc_attr($value) . '">';
                        echo '<div class="license-preview">';
                        if (!empty($value)) {
                            $image_url = wp_get_attachment_image_url($value, 'thumbnail');
                            $full_url = wp_get_attachment_url($value);
                            $filename = basename(get_attached_file($value));

                            echo '<div class="license-image">';
                            echo '<img src="' . esc_url($image_url) . '" alt="Driver\'s License">';
                            echo '</div>';
                            echo '<div class="license-info">';
                            echo '<span class="license-filename">' . esc_html($filename) . '</span>';
                            echo '<a href="' . esc_url($full_url) . '" target="_blank" class="license-view">View Image</a>';
                            echo '</div>';
                            echo '<a href="#" class="remove-license">Remove</a>';
                        } else {
                            echo '<div class="no-license">No license image uploaded</div>';
                        }
                        echo '</div>';
                        echo '<button type="button" class="button upload-license-button">Upload Driver\'s License</button>';
                        echo '</div>';
                        break;
                }
                echo '</div>';
            }
        }
    }

    /**
     * Save meta boxes
     */
    public function save_meta_boxes($post_id)
    {
        if (!isset($_POST['houses_customer_meta_boxes_nonce']) || !wp_verify_nonce($_POST['houses_customer_meta_boxes_nonce'], 'houses_customer_meta_boxes')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save all fields
        $this->save_fields($this->fields, $post_id);

        // Note: Title update is now handled by auto_generate_customer_title function in post-type.php
    }

    /**
     * Save fields recursively
     */
    private function save_fields($fields, $post_id)
    {
        foreach ($fields as $field => $config) {
            if (isset($config['fields'])) {
                $this->save_fields($config['fields'], $post_id);
            } else {
                if ($config['type'] === 'date_range') {
                    if (isset($_POST[$field . '_start'])) {
                        update_post_meta($post_id, $field . '_start', sanitize_text_field($_POST[$field . '_start']));
                    }
                    if (isset($_POST[$field . '_end'])) {
                        update_post_meta($post_id, $field . '_end', sanitize_text_field($_POST[$field . '_end']));
                    }
                } elseif (isset($_POST[$field])) {
                    $value = $_POST[$field];
                    if ($config['type'] === 'multiselect') {
                        update_post_meta($post_id, $field, array_map('sanitize_text_field', $value));
                    } elseif ($config['type'] === 'license_image') {
                        if (!empty($_POST[$field])) {
                            // The value is an attachment ID
                            update_post_meta($post_id, $field, absint($_POST[$field]));
                        } else {
                            // Delete the meta if it's empty
                            delete_post_meta($post_id, $field);
                        }
                    } else {
                        update_post_meta($post_id, $field, sanitize_text_field($value));
                    }
                }
            }
        }
    }
}

// Initialize the meta boxes
new Houses_Client_Meta_Boxes();
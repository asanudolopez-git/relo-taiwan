<?php
/**
 * Property Meta Boxes Handler
 */

class Houses_Property_Meta_Boxes
{
    /**
     * Meta box fields configuration
     */
    private $fields = array(
        'basic_info' => array(
            'title' => 'Basic Info',
            'fields' => array(
                'rent' => array(
                    'label' => 'Rent',
                    'type' => 'text',
                    'class' => '',
                    'description' => 'Format: NT$230,000',
                ),
                'tax_included' => array(
                    'label' => 'Tax Included',
                    'type' => 'select',
                    'options' => array(
                        'no' => 'No',
                        'yes' => 'Yes',
                    ),
                    'class' => '',
                ),
                'management_fee_included' => array(
                    'label' => 'Management Fee Included',
                    'type' => 'select',
                    'options' => array(
                        'no' => 'No',
                        'yes' => 'Yes',
                    ),
                    'class' => '',
                ),
                'property_post_id' => array(
                    'label' => 'Property ID',
                    'type' => 'text',
                    'class' => '',
                    'readonly' => true,
                ),
                'metro_line' => array(
                    'label' => 'Metro Line',
                    'type' => 'select',
                    'options' => array(
                        '' => 'Select a line',
                        'BR' => 'Brown Line (Wenhu Line)',
                        'R' => 'Red Line (Tamsui-Xinyi Line)',
                        'G' => 'Green Line (Songshan-Xindian Line)',
                        'O' => 'Orange Line (Zhonghe-Xinlu Line)',
                        'BL' => 'Blue Line (Bannan Line)',
                        'Y' => 'Yellow Line (Circular Line)'
                    ),
                    'class' => 'metro-line-select',
                ),
                'station' => array(
                    'label' => 'Station',
                    'type' => 'select',
                    'options' => array(), // Will be populated dynamically via AJAX
                    'class' => 'metro-station-select houses-select2',
                ),
                'address' => array(
                    'label' => 'Address',
                    'type' => 'text',
                    'class' => 'full-width',
                ),
                'zip_code' => array(
                    'label' => 'ZIP Code',
                    'type' => 'text',
                    'class' => '',
                    'description' => 'Autocomplete with option to manually enter',
                ),
                'chinese_address' => array(
                    'label' => 'Chinese Address (地址)',
                    'type' => 'text',
                    'class' => 'full-width',
                ),
                'bedroom' => array(
                    'label' => 'Bedroom',
                    'type' => 'number',
                    'class' => '',
                ),
                'bathroom' => array(
                    'label' => 'Bathroom',
                    'type' => 'number',
                    'class' => '',
                ),
                'floor' => array(
                    'label' => 'Floor',
                    'type' => 'number',
                    'class' => '',
                    'description' => 'The floor number of the property',
                ),
                'total_floor' => array(
                    'label' => 'Total Floor',
                    'type' => 'number',
                    'class' => '',
                    'description' => 'Total number of floors in the building',
                ),
                'building_age' => array(
                    'label' => 'Building Age (years)',
                    'type' => 'number',
                    'class' => '',
                ),
                'net_size' => array(
                    'label' => 'Net Size',
                    'type' => 'number',
                    'step' => '0.01',
                    'class' => '',
                ),
                'square_meters' => array(
                    'label' => 'Square Meters',
                    'type' => 'string',
                    'class' => '',
                ),
                'property_type' => array(
                    'label' => 'Property Type',
                    'type' => 'select',
                    'options' => array(
                        'apartment' => 'Apartment',
                        'house' => 'House',
                    ),
                    'class' => '',
                ),
                'parking' => array(
                    'label' => 'Parking',
                    'type' => 'select',
                    'options' => array(
                        'yes' => 'Yes',
                        'no' => 'No',
                    ),
                ),
            ),
        ),
        'details' => array(
            'title' => 'Details',
            'fields' => array(
                'gym' => array(
                    'label' => 'Gym',
                    'type' => 'select',
                    'options' => array(
                        'no' => 'No',
                        'yes' => 'Yes',
                    ),
                    'class' => '',
                ),
                'swimming_pool' => array(
                    'label' => 'Swimming Pool',
                    'type' => 'select',
                    'options' => array(
                        'no' => 'No',
                        'yes' => 'Yes',
                    ),
                    'class' => '',
                ),
                'notes' => array(
                    'label' => 'Notes',
                    'type' => 'textarea',
                    'class' => 'full-width',
                ),
            ),
        ),
        'gallery' => array(
            'title' => 'Gallery Images',
            'fields' => array(
                'gallery_images' => array(
                    'label' => 'Property Gallery Images',
                    'type' => 'gallery',
                    'class' => 'full-width',
                ),
            ),
        ),


    );

    /**
     * Constructor
     */
    public function __construct()
    {
        // Initialize the agent options
        $this->fields['basic_info']['fields']['agent_id']['options'] = $this->get_agents_options();
        // District field is commented out, so we don't need to populate its options
        // $this->fields['basic_info']['fields']['district']['options'] = $this->get_districts_options();
        $this->fields['basic_info']['fields']['station']['options'] = $this->get_stations_options();
        // Register meta boxes
        add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        // AJAX handler for station filtering
        add_action('wp_ajax_get_stations_by_line', array($this, 'ajax_get_stations_by_line'));
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_scripts($hook)
    {
        global $post;

        if ($hook == 'post-new.php' || $hook == 'post.php') {
            if (isset($post) && 'property' === $post->post_type) {
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

                // Enqueue media uploader
                wp_enqueue_media();

                // Enqueue jQuery UI for sortable - adding both WordPress core and CDN version for backup
                wp_enqueue_script('jquery-ui-sortable');

                // Add jQuery UI from CDN to ensure it's available
                wp_enqueue_style(
                    'jquery-ui-css',
                    'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css',
                    array(),
                    '1.13.2'
                );

                wp_enqueue_script(
                    'jquery-ui-external',
                    'https://code.jquery.com/ui/1.13.2/jquery-ui.min.js',
                    array('jquery'),
                    '1.13.2',
                    true
                );

                // Enqueue gallery styles and script
                wp_enqueue_style(
                    'houses-admin-gallery-css',
                    get_template_directory_uri() . '/includes/property/assets/css/admin-gallery.css',
                    array(),
                    _S_VERSION
                );

                wp_enqueue_script(
                    'houses-admin-gallery',
                    get_template_directory_uri() . '/includes/property/assets/js/admin-gallery.js',
                    array('jquery', 'jquery-ui-sortable'),
                    _S_VERSION,
                    true
                );

                // Enqueue rent formatter script
                wp_enqueue_script(
                    'houses-rent-formatter',
                    get_template_directory_uri() . '/includes/property/assets/js/rent-formatter.js',
                    array('jquery'),
                    _S_VERSION,
                    true
                );

                // Enqueue ZIP code autocomplete script
                wp_enqueue_script(
                    'houses-zip-autocomplete',
                    get_template_directory_uri() . '/includes/property/assets/js/zip-autocomplete.js',
                    array('jquery'),
                    _S_VERSION,
                    true
                );

                // Create a new JS file for station filtering
                wp_enqueue_script(
                    'houses-station-filter',
                    get_template_directory_uri() . '/includes/property/assets/js/station-filter.js',
                    array('jquery'),
                    _S_VERSION,
                    true
                );

                // Pass data to the script
                wp_localize_script(
                    'houses-station-filter',
                    'HousesStationFilter',
                    array(
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'nonce' => wp_create_nonce('houses_property_details'),
                        'selected_station' => get_post_meta($post->ID, 'station', true),
                        'selected_line' => get_post_meta($post->ID, 'metro_line', true)
                    )
                );

                // Add dashicons for our gallery UI
                wp_enqueue_style('dashicons');
            }
        }
    }

    /**
     * Register meta boxes
     */
    public function register_meta_boxes()
    {
        add_meta_box(
            'property_details',
            __('Property Details', 'houses-theme'),
            array($this, 'render_meta_box'),
            'property',
            'normal',
            'high'
        );
    }

    /**
     * Render meta box
     */
    public function render_meta_box($post)
    {
        wp_nonce_field('houses_property_details', 'houses_property_details_nonce');

        // Get all field values
        $values = array();
        foreach ($this->fields as $section) {
            foreach ($section['fields'] as $field_id => $field) {
                $values[$field_id] = get_post_meta($post->ID, $field_id, true);
            }
        }

        // Include the template
        include get_template_directory() . '/includes/property/meta-boxes/property-details.php';
    }

    /**
     * Save meta box content
     */
    public function save_meta_boxes($post_id)
    {
        if (!isset($_POST['houses_property_details_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['houses_property_details_nonce'], 'houses_property_details')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save all fields
        foreach ($this->fields as $section) {
            if (!isset($section['fields']) || !is_array($section['fields'])) {
                continue;
            }
            foreach ($section['fields'] as $field_id => $field) {
                // Skip if field is not properly defined
                if (!isset($field['type']) || empty($field['type'])) {
                    continue;
                }
                
                if ($field['type'] === 'gallery') {
                    // Handle gallery field specially
                    if (isset($_POST[$field_id]) && is_array($_POST[$field_id])) {
                        $gallery_images = array_map('absint', $_POST[$field_id]);
                        $gallery_images = array_filter($gallery_images);
                        update_post_meta($post_id, $field_id, $gallery_images);
                    } else {
                        delete_post_meta($post_id, $field_id);
                    }
                } elseif (isset($_POST[$field_id])) {
                    if ($field['type'] === 'textarea') {
                        update_post_meta($post_id, $field_id, sanitize_textarea_field($_POST[$field_id]));
                    } else {
                        update_post_meta($post_id, $field_id, sanitize_text_field($_POST[$field_id]));
                    }
                } else {
                    // Clear the field if not set in POST data
                    delete_post_meta($post_id, $field_id);
                }
            }
        }
    }

    /**
     * Get field configuration
     */
    public function get_fields()
    {
        return $this->fields;
    }

    /**
     * Get agents for select options
     */
    public function get_agents_options()
    {
        $options = array('' => __('Select Agent', 'houses-theme'));

        $agents = get_posts(array(
            'post_type' => 'agent',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));

        foreach ($agents as $agent) {
            $options[$agent->ID] = $agent->post_title;
        }

        return $options;
    }

    /**
     * Get districts for select options
     */
    public function get_districts_options()
    {
        $options = array('' => __('Select District', 'houses-theme'));

        $districts = get_posts(array(
            'post_type' => 'district',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));

        foreach ($districts as $district) {
            $options[$district->ID] = $district->post_title;
        }

        return $options;
    }

    /**
     * Get stations for select options
     * 
     * @param string $line_code Optional line code to filter stations
     * @return array Array of station options
     */
    public function get_stations_options($line_code = '')
    {
        $options = array('' => __('Select Station', 'houses-theme'));

        $args = array(
            'post_type' => 'station',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        );

        // Add meta query to filter by line if specified
        if (!empty($line_code)) {
            $args['meta_query'] = array(
                array(
                    'key' => 'station_line',
                    'value' => $line_code,
                    'compare' => '='
                )
            );
        }

        $stations = get_posts($args);

        foreach ($stations as $station) {
            $station_code = get_post_meta($station->ID, 'station_code', true);
            $display_name = $station_code ? $station_code . ' - ' . $station->post_title : $station->post_title;
            $options[$station->ID] = $display_name;
        }

        return $options;
    }

    /**
     * AJAX handler to get stations by line
     */
    public function ajax_get_stations_by_line()
    {
        // Check if we have a nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'houses_property_details')) {
            wp_send_json_error('Invalid nonce');
        }

        // Check if we have a line code
        if (!isset($_POST['line_code'])) {
            wp_send_json_error('No line code provided');
        }

        $line_code = sanitize_text_field($_POST['line_code']);
        $options = $this->get_stations_options($line_code);

        // Format response for select2
        $response = array();
        foreach ($options as $value => $label) {
            if (empty($value))
                continue; // Skip empty option
            $response[] = array(
                'id' => $value,
                'text' => $label
            );
        }

        wp_send_json_success($response);
    }

    /**
     * Render field
     */
    private function render_field($field, $value)
    {
        switch ($field['type']) {
            case 'text':
            case 'number':
                ?>
                <input type="<?php echo esc_attr($field['type']); ?>" id="<?php echo esc_attr($field['id']); ?>"
                    name="<?php echo esc_attr($field['id']); ?>" value="<?php echo esc_attr($value); ?>" class="widefat">
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

            case 'gallery':
                ?>
                <div class="gallery-images" data-field-name="<?php echo esc_attr($field['id']); ?>[]">
                    <p class="description">
                        <?php _e('Drag and drop images to reorder. The first image will be used as the main property image.', 'houses-theme'); ?>
                    </p>

                    <?php if (!empty($value)): ?>
                        <?php foreach ($value as $image_id): ?>
                            <div class="gallery-image" data-id="<?php echo esc_attr($image_id); ?>">
                                <div class="image-drag-handle dashicons dashicons-move"></div>
                                <?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?>
                                <input type="hidden" name="<?php echo esc_attr($field['id']); ?>[]" value="<?php echo esc_attr($image_id); ?>">
                                <a href="#" class="remove-gallery-image dashicons dashicons-trash"
                                    title="<?php _e('Remove image', 'houses-theme'); ?>"></a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <a href="#" class="add-gallery-image button button-primary"><?php _e('Add Images', 'houses-theme'); ?></a>
                    <div style="clear:both;"></div>
                </div>
                <?php
                break;
        }

        if (isset($field['after_field'])) {
            echo $field['after_field'];
        }
    }
}

// Initialize the meta boxes
new Houses_Property_Meta_Boxes();

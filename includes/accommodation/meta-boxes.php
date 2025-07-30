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
                'client_lease_id' => array(
                    'label' => 'Lease',
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
        $this->fields['basic_info']['fields']['client_lease_id']['options'] = $this->get_client_lease_options();
        $this->fields['basic_info']['fields']['property_id']['options'] = $this->get_properties_options();

        // Register meta boxes
        add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));

        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
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
                ');
                wp_enqueue_script(
                    'accomodation-admin',
                    get_template_directory_uri() . '/includes/accommodation/assets/js/admin.js',
                    array('jquery'),
                    '1.0.0',
                    true
                );
                // Add datepicker initialization
                wp_add_inline_script('jquery-ui-datepicker', '
                    jQuery(document).ready(function($) {
                        $(".accommodation-date-field").datepicker({
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

        // Gather current values for this section.
        $values = array();
        foreach ($this->fields[$section_id]['fields'] as $field_id => $field) {
            $values[$field_id] = get_post_meta($post->ID, $field_id, true);
        }

        // Output the fields.
        echo '<div class="accommodation-meta-box-container">';

        $checkbox_date_pairs = array(
            'intro_consultation_call' => 'intro_consultation_call_date',
            'lease_signed' => 'lease_signed_completed_at',
            'check_in' => 'check_in_completed_at',
        );
        $skip_fields = array();

        foreach ($this->fields[$section_id]['fields'] as $field_id => $field) {
            if (in_array($field_id, $skip_fields, true)) {
                continue; // Date field already rendered with its checkbox
            }

            // Group checkbox + date input when mapping exists
            if (isset($checkbox_date_pairs[$field_id])) {
                $date_field_id = $checkbox_date_pairs[$field_id];
                $date_field_conf = $this->fields[$section_id]['fields'][$date_field_id];

                echo '<div class="accommodation-meta-box-field checkbox-group">';
                echo '<div class="checkbox-field">';
                // Checkbox
                echo '<input type="checkbox" id="' . esc_attr($field_id) . '" name="' . esc_attr($field_id) . '" value="1" ' . checked($values[$field_id], '1', false) . ' />';
                // Label from date field (e.g., Completed At ...)
                echo '<span class="completed-label">' . esc_html($date_field_conf['label']) . '</span>';
                // Date input
                echo '<br/><input type="text" id="' . esc_attr($date_field_id) . '" name="' . esc_attr($date_field_id) . '" value="' . esc_attr($values[$date_field_id]) . '" class="accommodation-date-field" />';
                echo '</div>'; // .checkbox-field
                echo '</div>'; // .accommodation-meta-box-field

                // Mark date field as rendered
                $skip_fields[] = $date_field_id;
                continue;
            }

            // Skip date fields that were paired earlier just in case
            if (isset($checkbox_date_pairs[$field_id])) {
                continue;
            }

            $field['id'] = $field_id;
            $class = isset($field['class']) ? $field['class'] : '';

            echo '<div class="accommodation-meta-box-field ' . esc_attr($class) . '">';
            echo '<label for="' . esc_attr($field_id) . '">' . esc_html($field['label']) . '</label>';
            $this->render_field($field, $values[$field_id]);
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
        foreach ($this->fields as $section) {
            foreach ($section['fields'] as $field_id => $field) {
                if ($field['type'] === 'checkbox') {
                    // Handle checkbox fields
                    $value = isset($_POST[$field_id]) ? '1' : '';
                    update_post_meta($post_id, $field_id, $value);
                } elseif (isset($_POST[$field_id])) {
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
     * Get Client Leases for select options
     */
    public function get_client_lease_options()
    {
        $options = array('' => __('Select Lease Summary', 'houses-theme'));

        $client_leases = get_posts(array(
            'post_type' => 'client_lease',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));

        foreach ($client_leases as $client_lease) {
            $options[$client_lease->ID] = $client_lease->post_title;
        }

        return $options;
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

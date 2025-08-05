<?php
/**
 * Lease Summary Meta Boxes
 */
class Houses_Client_Lease_Meta_Boxes
{
    /**
     * Meta box fields
     */
    private $meta_boxes = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        // Define meta boxes
        $this->meta_boxes = array(
            'client_selection' => array(
                'title' => 'Assignee Selection',
                'fields' => array(
                    'client_id' => array(
                        'label' => 'Assignee',
                        'type' => 'select',
                        'options' => array(), // Will be populated in init
                        'class' => 'full-width',
                        'description' => 'Select an assignee first to load their properties',
                    ),
                ),
            ),
            'lease_details' => array(
                'title' => 'Lease Details',
                'fields' => array(
                    'property_id' => array(
                        'label' => 'Property',
                        'type' => 'select',
                        'options' => array(), // Will be populated dynamically via AJAX
                        'class' => 'full-width',
                        'description' => 'Properties from assignee\'s house list',
                    ),
                    'property_id_hidden' => array(
                        'label' => '',
                        'type' => 'hidden',
                        'class' => '',
                    ),
                    'lease_template_id' => array(
                        'label' => 'Lease Template',
                        'type' => 'select',
                        'options' => array(), // Will be populated in init
                        'class' => 'full-width',
                    ),
                    'start_date' => array(
                        'label' => 'Start Date',
                        'type' => 'date',
                        'class' => '',
                    ),
                    'end_date' => array(
                        'label' => 'End Date',
                        'type' => 'date',
                        'class' => '',
                    ),
                    'monthly_rent' => array(
                        'label' => 'Monthly Rent',
                        'type' => 'number',
                        'class' => '',
                    ),
                    'deposit' => array(
                        'label' => 'Deposit',
                        'type' => 'number',
                        'class' => '',
                    ),
                    'status' => array(
                        'label' => 'Status',
                        'type' => 'select',
                        'options' => array(
                            'draft' => 'Draft',
                            'pending' => 'Pending Signature',
                            'active' => 'Active',
                            'expired' => 'Expired',
                            'terminated' => 'Terminated',
                        ),
                        'class' => '',
                    ),
                    'notes' => array(
                        'label' => 'Notes',
                        'type' => 'textarea',
                        'class' => 'full-width',
                    ),
                    'extension_authorized_date' => array(
                        'label' => 'Extension Authorized Date',
                        'type' => 'date',
                        'class' => '',
                    ),
                    'extension_period' => array(
                        'label' => 'Extension Period (months)',
                        'type' => 'number',
                        'class' => '',
                        'min' => '0',
                    ),
                    'extension_signed_date' => array(
                        'label' => 'Extension Signed Date',
                        'type' => 'date',
                        'class' => '',
                    ),
                    'contract_attachment' => array(
                        'label' => 'Contract Attachment',
                        'type' => 'file',
                        'class' => '',
                        'description' => 'Upload the signed contract document (PDF, DOC, DOCX)',
                        'allowed_types' => array('pdf', 'doc', 'docx'),
                    ),
                ),
            ),
        );

        // Add meta boxes
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));

        // Save meta boxes
        add_action('save_post', array($this, 'save_meta_boxes'), 10, 2);

        // Populate select options
        add_action('admin_init', array($this, 'populate_select_options'));

        // Add AJAX handler for dynamic property loading
        add_action('wp_ajax_load_client_properties', array($this, 'load_client_properties'));

        // Enqueue scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Enqueue scripts
     */
    public function enqueue_scripts($hook)
    {
        global $post;

        if (($hook == 'post.php' || $hook == 'post-new.php') && isset($post) && $post->post_type === 'client_lease') {
            wp_enqueue_script(
                'client-lease-admin',
                get_template_directory_uri() . '/includes/client-lease/assets/js/admin.js',
                array('jquery'),
                '1.0.0',
                true
            );

            wp_localize_script('client-lease-admin', 'client_lease_data', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('client_lease_nonce'),
                'property_id' => get_post_meta($post->ID, 'property_id', true),
            ));
        }
    }

    /**
     * AJAX handler to load properties for a client
     */
    public function load_client_properties()
    {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'client_lease_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
        }

        // Check client ID
        if (empty($_POST['client_id'])) {
            wp_send_json_error(array('message' => 'No assignee selected'));
        }

        $client_id = intval($_POST['client_id']);

        // Find house lists for this client
        $house_lists = get_posts(array(
            'post_type' => 'customer-house-list',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'selected_customer',
                    'value' => $client_id,
                ),
            ),
        ));

        $properties = array();

        // Get properties from each house list
        foreach ($house_lists as $list) {
            $property_list = get_post_meta($list->ID, 'property_list', true);

            if (!empty($property_list) && is_array($property_list)) {
                foreach ($property_list as $property_id) {
                    if (!isset($properties[$property_id])) {
                        $property_code = get_post_meta($property_id, 'property_id', true);
                        $title = get_the_title($property_id);

                        if ($property_code) {
                            $title = "#{$property_code} - {$title}";
                        }

                        $properties[$property_id] = $title;
                    }
                }
            }
        }

        // If no properties found, return empty options
        if (empty($properties)) {
            wp_send_json_success(array(
                'options' => '<option value="">No properties found for this assignee</option>',
            ));
            return;
        }

        // Build options HTML
        $options = '<option value="">Select Property</option>';
        foreach ($properties as $id => $title) {
            $options .= '<option value="' . esc_attr($id) . '">' . esc_html($title) . '</option>';
        }

        wp_send_json_success(array(
            'options' => $options,
        ));
    }

    /**
     * Populate select options
     */
    public function populate_select_options()
    {
        // Populate clients
        $clients = get_posts(array(
            'post_type' => 'customer',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        $this->meta_boxes['client_selection']['fields']['client_id']['options'] = array('' => 'Select Assignee');
        foreach ($clients as $client) {
            $client_id = get_post_meta($client->ID, 'customer_id', true);
            $title = $client->post_title;
            if ($client_id) {
                $title = "#{$client_id} - {$title}";
            }
            $this->meta_boxes['client_selection']['fields']['client_id']['options'][$client->ID] = $title;
        }

        // Populate lease templates
        $lease_templates = get_posts(array(
            'post_type' => 'lease_template',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        $this->meta_boxes['lease_details']['fields']['lease_template_id']['options'] = array('' => 'Select Lease Template');
        foreach ($lease_templates as $template) {
            $this->meta_boxes['lease_details']['fields']['lease_template_id']['options'][$template->ID] = $template->post_title;
        }

        // Populate agents
        $agents = get_users(array(
            'role__in' => array('administrator', 'editor', 'author'), // Incluir más roles
            'orderby' => 'display_name',
        ));

        $this->meta_boxes['lease_details']['fields']['agent_id']['options'] = array('' => 'Select Agent');

        // Debug - guardar en log cuántos agentes se encontraron
        error_log('Found ' . count($agents) . ' agents for Lease Summary dropdown');

        if (empty($agents)) {
            // Si no hay agentes, añadir al menos el usuario actual
            $current_user = wp_get_current_user();
            if ($current_user && $current_user->ID > 0) {
                $this->meta_boxes['lease_details']['fields']['agent_id']['options'][$current_user->ID] = $current_user->display_name . ' (You)';
                error_log('No agents found, added current user: ' . $current_user->display_name);
            } else {
                // Añadir un mensaje de error como opción
                $this->meta_boxes['lease_details']['fields']['agent_id']['options']['error'] = 'No agents available - contact admin';
                error_log('No agents found and could not get current user');
            }
        } else {
            // Añadir los agentes encontrados
            foreach ($agents as $agent) {
                $this->meta_boxes['lease_details']['fields']['agent_id']['options'][$agent->ID] = $agent->display_name;
                error_log('Added agent: ' . $agent->display_name . ' (ID: ' . $agent->ID . ')');
            }
        }

        // Property options will be loaded dynamically via AJAX
        $this->meta_boxes['lease_details']['fields']['property_id']['options'] = array('' => 'Select Assignee First');
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes()
    {
        foreach ($this->meta_boxes as $id => $meta_box) {
            add_meta_box(
                'houses_client_lease_' . $id,
                $meta_box['title'],
                array($this, 'render_meta_box'),
                'client_lease',
                'normal',
                'high',
                $id
            );
        }
    }

    /**
     * Render meta box
     */
    public function render_meta_box($post, $args)
    {
        $meta_box_id = $args['args'];
        $meta_box = $this->meta_boxes[$meta_box_id];

        // Add nonce for security
        wp_nonce_field('houses_client_lease_meta_box', 'houses_client_lease_meta_box_nonce');

        // Check if this meta box has file fields
        $has_file_fields = false;
        foreach ($meta_box['fields'] as $field) {
            if (isset($field['type']) && $field['type'] === 'file') {
                $has_file_fields = true;
                break;
            }
        }

        // Add JavaScript to handle file upload form encoding
        if ($has_file_fields) {
            echo '<script>
            jQuery(document).ready(function($) {
                // Set form encoding for file uploads
                $("#post").attr("enctype", "multipart/form-data");
            });
            </script>';
        }

        // Output fields
        echo '<table class="form-table">';
        foreach ($meta_box['fields'] as $id => $field) {
            $field['id'] = $id;
            $value = get_post_meta($post->ID, $id, true);

            // Skip rendering table row for hidden fields
            if (isset($field['type']) && $field['type'] === 'hidden') {
                $this->render_field($field, $value);
                continue;
            }

            echo '<tr>';
            $label_text = isset($field['label']) ? $field['label'] : '';
            echo '<th><label for="' . esc_attr($id) . '">' . esc_html($label_text) . '</label></th>';
            echo '<td' . (isset($field['class']) && $field['class'] === 'full-width' ? ' colspan="2"' : '') . '>';
            $this->render_field($field, $value);
            if (!empty($field['description'])) {
                echo '<p class="description">' . esc_html($field['description']) . '</p>';
            }
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
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

            case 'date':
                ?>
                <input type="date" id="<?php echo esc_attr($field['id']); ?>" name="<?php echo esc_attr($field['id']); ?>"
                    value="<?php echo esc_attr($value); ?>" class="widefat">
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
                    rows="5"><?php echo esc_textarea($value); ?></textarea>
                <?php
                break;

            case 'hidden':
                ?>
                <input type="hidden" id="<?php echo esc_attr($field['id']); ?>" name="<?php echo esc_attr($field['id']); ?>"
                    value="<?php echo esc_attr($value); ?>">
                <?php
                break;

            case 'file':
                $attachment_id = $value;
                $file_url = '';
                $file_name = '';
                
                if ($attachment_id) {
                    $file_url = wp_get_attachment_url($attachment_id);
                    $file_name = get_the_title($attachment_id);
                }
                
                $allowed_types = isset($field['allowed_types']) ? implode(',', array_map(function($type) { return '.' . $type; }, $field['allowed_types'])) : '';
                ?>
                <div class="contract-attachment-field">
                    <input type="file" id="<?php echo esc_attr($field['id']); ?>" name="<?php echo esc_attr($field['id']); ?>" 
                           accept="<?php echo esc_attr($allowed_types); ?>" class="widefat">
                    <input type="hidden" id="<?php echo esc_attr($field['id']); ?>_id" name="<?php echo esc_attr($field['id']); ?>_id" 
                           value="<?php echo esc_attr($attachment_id); ?>">
                    
                    <?php if ($attachment_id && $file_url): ?>
                        <div class="current-attachment" style="margin-top: 10px; padding: 10px; background: #f9f9f9; border-left: 4px solid #0073aa;">
                            <strong>Current File:</strong> 
                            <a href="<?php echo esc_url($file_url); ?>" target="_blank" style="text-decoration: none;">
                                📄 <?php echo esc_html($file_name ?: basename($file_url)); ?>
                            </a>
                            <br>
                            <small style="color: #666;">Upload a new file to replace the current one</small>
                            <br>
                            <button type="button" class="button button-secondary" onclick="removeAttachment('<?php echo esc_attr($field['id']); ?>')" style="margin-top: 5px;">
                                Remove File
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                
                <script>
                function removeAttachment(fieldId) {
                    if (confirm('Are you sure you want to remove this attachment?')) {
                        document.getElementById(fieldId + '_id').value = '';
                        document.querySelector('.current-attachment').style.display = 'none';
                    }
                }
                </script>
                <?php
                break;
        }
    }

    /**
     * Handle file uploads
     */
    private function handle_file_uploads($post_id)
    {
        // Check if WordPress upload functions are available
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        foreach ($this->meta_boxes as $meta_box) {
            foreach ($meta_box['fields'] as $id => $field) {
                if (isset($field['type']) && $field['type'] === 'file') {
                    // Check if file was uploaded
                    if (isset($_FILES[$id]) && $_FILES[$id]['error'] === UPLOAD_ERR_OK) {
                        // Validate file type
                        $allowed_types = isset($field['allowed_types']) ? $field['allowed_types'] : array();
                        $file_extension = strtolower(pathinfo($_FILES[$id]['name'], PATHINFO_EXTENSION));
                        
                        if (!empty($allowed_types) && !in_array($file_extension, $allowed_types)) {
                            add_action('admin_notices', function() use ($field, $allowed_types) {
                                echo '<div class="notice notice-error is-dismissible"><p>';
                                echo 'Invalid file type for ' . esc_html($field['label']) . '. Allowed types: ' . implode(', ', $allowed_types);
                                echo '</p></div>';
                            });
                            continue;
                        }

                        // Set up upload overrides
                        $upload_overrides = array(
                            'test_form' => false,
                            'mimes' => array(
                                'pdf' => 'application/pdf',
                                'doc' => 'application/msword',
                                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                            )
                        );

                        // Handle the upload
                        $uploaded_file = wp_handle_upload($_FILES[$id], $upload_overrides);

                        if (!isset($uploaded_file['error'])) {
                            // Create attachment
                            $attachment = array(
                                'post_mime_type' => $uploaded_file['type'],
                                'post_title' => sanitize_file_name(pathinfo($_FILES[$id]['name'], PATHINFO_FILENAME)),
                                'post_content' => '',
                                'post_status' => 'inherit',
                                'post_parent' => $post_id
                            );

                            // Insert the attachment
                            $attachment_id = wp_insert_attachment($attachment, $uploaded_file['file'], $post_id);

                            if (!is_wp_error($attachment_id)) {
                                // Generate attachment metadata
                                if (!function_exists('wp_generate_attachment_metadata')) {
                                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                                }
                                $attachment_data = wp_generate_attachment_metadata($attachment_id, $uploaded_file['file']);
                                wp_update_attachment_metadata($attachment_id, $attachment_data);

                                // Delete old attachment if exists
                                $old_attachment_id = get_post_meta($post_id, $id, true);
                                if ($old_attachment_id && $old_attachment_id != $attachment_id) {
                                    wp_delete_attachment($old_attachment_id, true);
                                }

                                // Save the attachment ID
                                update_post_meta($post_id, $id, $attachment_id);

                                // Add success notice
                                add_action('admin_notices', function() use ($field) {
                                    echo '<div class="notice notice-success is-dismissible"><p>';
                                    echo esc_html($field['label']) . ' uploaded successfully!';
                                    echo '</p></div>';
                                });
                            }
                        } else {
                            // Add error notice
                            add_action('admin_notices', function() use ($field, $uploaded_file) {
                                echo '<div class="notice notice-error is-dismissible"><p>';
                                echo 'Error uploading ' . esc_html($field['label']) . ': ' . esc_html($uploaded_file['error']);
                                echo '</p></div>';
                            });
                        }
                    }
                    // Handle file removal
                    elseif (isset($_POST[$id . '_id'])) {
                        $current_attachment_id = get_post_meta($post_id, $id, true);
                        $posted_attachment_id = sanitize_text_field($_POST[$id . '_id']);
                        
                        // If the posted ID is empty but we have a current attachment, remove it
                        if (empty($posted_attachment_id) && !empty($current_attachment_id)) {
                            wp_delete_attachment($current_attachment_id, true);
                            delete_post_meta($post_id, $id);
                            
                            add_action('admin_notices', function() use ($field) {
                                echo '<div class="notice notice-success is-dismissible"><p>';
                                echo esc_html($field['label']) . ' removed successfully!';
                                echo '</p></div>';
                            });
                        }
                    }
                }
            }
        }
    }

    /**
     * Save meta boxes
     */
    public function save_meta_boxes($post_id, $post)
    {
        // Check if we're supposed to be saving
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check post type
        if ($post->post_type !== 'client_lease') {
            return;
        }

        // Check nonce
        if (!isset($_POST['houses_client_lease_meta_box_nonce']) || !wp_verify_nonce($_POST['houses_client_lease_meta_box_nonce'], 'houses_client_lease_meta_box')) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Handle file uploads first
        $this->handle_file_uploads($post_id);

        // Save fields
        foreach ($this->meta_boxes as $meta_box) {
            foreach ($meta_box['fields'] as $id => $field) {
                // Skip file fields as they are handled separately
                if (isset($field['type']) && $field['type'] === 'file') {
                    continue;
                }

                if (isset($_POST[$id])) {
                    $value = $_POST[$id];

                    // Sanitize based on field type
                    switch ($field['type']) {
                        case 'text':
                            $value = sanitize_text_field($value);
                            break;
                        case 'number':
                            $value = floatval($value);
                            break;
                        case 'date':
                            $value = sanitize_text_field($value);
                            break;
                        case 'select':
                            // Make sure it's a valid option
                            if (!isset($field['options'][$value]) && $value !== '') {
                                $value = '';
                            }
                            break;
                        case 'textarea':
                            $value = sanitize_textarea_field($value);
                            break;
                    }

                    update_post_meta($post_id, $id, $value);
                }
            }
        }
    }
}

// Initialize the meta boxes
new Houses_Client_Lease_Meta_Boxes();

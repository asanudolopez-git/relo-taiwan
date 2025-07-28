<?php
/**
 * Lease Summary Meta Boxes
 */
class Houses_Lease_Summary_Meta_Boxes {
    /**
     * Meta box fields
     */
    private $meta_boxes = array();

    /**
     * Constructor
     */
    public function __construct() {
        // Define meta boxes
        $this->meta_boxes = array(
            'lease_reference' => array(
                'title' => 'Lease Reference',
                'fields' => array(
                    'client_lease_id' => array(
                        'label' => 'Lease Summary',
                        'type' => 'select',
                        'options' => array(), // Will be populated in init
                        'class' => 'full-width',
                        'description' => 'Select the associated lease',
                    ),
                ),
            ),
            'extension_details' => array(
                'title' => 'Extension Details',
                'fields' => array(
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
                    'notes' => array(
                        'label' => 'Notes',
                        'type' => 'textarea',
                        'class' => 'full-width',
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
        
        // Enqueue scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Enqueue scripts
     */
    public function enqueue_scripts($hook) {
        global $post;
        
        if (($hook == 'post.php' || $hook == 'post-new.php') && isset($post) && $post->post_type === 'lease_summary') {
            wp_enqueue_script(
                'lease-summary-admin',
                get_template_directory_uri() . '/includes/lease-summary/assets/js/admin.js',
                array('jquery'),
                '1.0.0',
                true
            );
            
            wp_localize_script('lease-summary-admin', 'lease_summary_data', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('lease_summary_nonce'),
            ));
        }
    }

    /**
     * Populate select options
     */
    public function populate_select_options() {
        // Populate client leases
        $client_leases = get_posts(array(
            'post_type' => 'client_lease',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        $this->meta_boxes['lease_reference']['fields']['client_lease_id']['options'] = array('' => 'Select Lease Summary');
        foreach ($client_leases as $lease) {
            $this->meta_boxes['lease_reference']['fields']['client_lease_id']['options'][$lease->ID] = $lease->post_title;
        }
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        foreach ($this->meta_boxes as $id => $meta_box) {
            add_meta_box(
                'houses_lease_summary_' . $id,
                $meta_box['title'],
                array($this, 'render_meta_box'),
                'lease_summary',
                'normal',
                'high',
                $id
            );
        }
    }

    /**
     * Render meta box
     */
    public function render_meta_box($post, $args) {
        $meta_box_id = $args['args'];
        $meta_box = $this->meta_boxes[$meta_box_id];
        
        // Add nonce for security
        wp_nonce_field('houses_lease_summary_meta_box', 'houses_lease_summary_meta_box_nonce');
        
        // Output fields
        echo '<table class="form-table">';
        foreach ($meta_box['fields'] as $id => $field) {
            $field['id'] = $id;
            $value = get_post_meta($post->ID, $id, true);
            
            echo '<tr>';
            echo '<th><label for="' . esc_attr($id) . '">' . esc_html($field['label']) . '</label></th>';
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
    private function render_field($field, $value) {
        switch ($field['type']) {
            case 'text':
            case 'number':
                ?>
                <input type="<?php echo esc_attr($field['type']); ?>" 
                       id="<?php echo esc_attr($field['id']); ?>" 
                       name="<?php echo esc_attr($field['id']); ?>" 
                       value="<?php echo esc_attr($value); ?>" 
                       class="widefat"
                       <?php if (isset($field['min'])) echo ' min="' . esc_attr($field['min']) . '"'; ?>>
                <?php
                break;

            case 'date':
                ?>
                <input type="date" 
                       id="<?php echo esc_attr($field['id']); ?>" 
                       name="<?php echo esc_attr($field['id']); ?>" 
                       value="<?php echo esc_attr($value); ?>" 
                       class="widefat">
                <?php
                break;

            case 'select':
                ?>
                <select id="<?php echo esc_attr($field['id']); ?>" 
                        name="<?php echo esc_attr($field['id']); ?>" 
                        class="widefat">
                    <?php foreach ($field['options'] as $option_value => $option_label) : ?>
                        <option value="<?php echo esc_attr($option_value); ?>" 
                                <?php selected($value, $option_value); ?>>
                            <?php echo esc_html($option_label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php
                break;

            case 'textarea':
                ?>
                <textarea id="<?php echo esc_attr($field['id']); ?>" 
                          name="<?php echo esc_attr($field['id']); ?>" 
                          class="widefat" 
                          rows="5"><?php echo esc_textarea($value); ?></textarea>
                <?php
                break;
        }
    }

    /**
     * Save meta boxes
     */
    public function save_meta_boxes($post_id, $post) {
        // Check if we're supposed to be saving
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check post type
        if ($post->post_type !== 'lease_summary') {
            return;
        }
        
        // Check nonce
        if (!isset($_POST['houses_lease_summary_meta_box_nonce']) || !wp_verify_nonce($_POST['houses_lease_summary_meta_box_nonce'], 'houses_lease_summary_meta_box')) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save fields
        foreach ($this->meta_boxes as $meta_box) {
            foreach ($meta_box['fields'] as $id => $field) {
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
new Houses_Lease_Summary_Meta_Boxes();

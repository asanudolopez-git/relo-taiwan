<?php
/**
 * Lease Template Meta Boxes Handler
 */

class Houses_Lease_Template_Meta_Boxes {
    /**
     * Meta box fields configuration
     */
    private $fields = array(
        'document' => array(
            'title' => 'Lease Document',
            'fields' => array(
                'landlord_id' => array(
                    'label' => 'Landlord',
                    'type' => 'select',
                    'options' => array(), // Will be populated in constructor
                    'class' => '',
                ),
                'document_file' => array(
                    'label' => 'Document File',
                    'type' => 'file',
                    'class' => 'full-width',
                ),
                'document_description' => array(
                    'label' => 'Document Description',
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
        // Initialize the landlord options
        $this->fields['document']['fields']['landlord_id']['options'] = $this->get_landlords_options();
        // Register meta boxes
        add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
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
            if (isset($post) && 'lease_template' === $post->post_type) {
                // Enqueue media uploader
                wp_enqueue_media();
                
                // Enqueue document upload script
                wp_enqueue_script(
                    'houses-admin-document',
                    get_template_directory_uri() . '/includes/lease-template/assets/js/admin-document.js',
                    array('jquery'),
                    _S_VERSION,
                    true
                );
                
                // Enqueue admin styles
                wp_enqueue_style(
                    'houses-admin-lease-template', 
                    get_template_directory_uri() . '/includes/lease-template/assets/css/admin.css',
                    array(),
                    _S_VERSION
                );
            }
        }
    }

    /**
     * Register meta boxes
     */
    public function register_meta_boxes() {
        add_meta_box(
            'houses_lease_template_document',
            __('Lease Document', 'houses-theme'),
            array($this, 'render_meta_box'),
            'lease_template',
            'normal',
            'high'
        );
    }

    /**
     * Render meta box
     */
    public function render_meta_box($post) {
        wp_nonce_field('houses_lease_template_details', 'houses_lease_template_details_nonce');
        
        // Get all field values
        $values = array();
        foreach ($this->fields as $section) {
            foreach ($section['fields'] as $field_id => $field) {
                $values[$field_id] = get_post_meta($post->ID, $field_id, true);
            }
        }
        
        // Include the template
        include get_template_directory() . '/includes/lease-template/meta-boxes/document-details.php';
    }

    /**
     * Save meta box content
     */
    public function save_meta_boxes($post_id) {
        if (!isset($_POST['houses_lease_template_details_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['houses_lease_template_details_nonce'], 'houses_lease_template_details')) {
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
            foreach ($section['fields'] as $field_id => $field) {
                if ($field['type'] === 'file') {
                    // Handle file field specially
                    if (isset($_POST[$field_id]) && !empty($_POST[$field_id])) {
                        update_post_meta($post_id, $field_id, sanitize_text_field($_POST[$field_id]));
                    }
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
     * Get landlords options
     */
    private function get_landlords_options() {
        $options = array('' => __('Select Landlord', 'houses-theme'));
        
        $landlords = get_posts(array(
            'post_type' => 'landlord',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));

        foreach ($landlords as $landlord) {
            $options[$landlord->ID] = $landlord->post_title;
        }

        return $options;
    }
}

// Initialize the meta boxes
new Houses_Lease_Template_Meta_Boxes();

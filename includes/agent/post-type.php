<?php
/**
 * Agent Post Type
 */

class Houses_Agent_Post_Type {
    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
    }

    /**
     * Register the post type
     */
    public function register_post_type() {
        $labels = array(
            'name'               => _x('Agents', 'Post type general name', 'houses-theme'),
            'singular_name'      => _x('Agent', 'Post type singular name', 'houses-theme'),
            'menu_name'          => _x('Agents', 'Admin Menu text', 'houses-theme'),
            'name_admin_bar'     => _x('Agent', 'Add New on Toolbar', 'houses-theme'),
            'add_new'            => __('Add New', 'houses-theme'),
            'add_new_item'       => __('Add New Agent', 'houses-theme'),
            'new_item'           => __('New Agent', 'houses-theme'),
            'edit_item'          => __('Edit Agent', 'houses-theme'),
            'view_item'          => __('View Agent', 'houses-theme'),
            'all_items'          => __('All Agents', 'houses-theme'),
            'search_items'       => __('Search Agents', 'houses-theme'),
            'not_found'          => __('No agents found.', 'houses-theme'),
            'not_found_in_trash' => __('No agents found in Trash.', 'houses-theme')
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'editor', 'thumbnail'),
            'menu_icon'          => 'dashicons-businessperson'
        );

        register_post_type('agent', $args);
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'agent_details',
            __('Agent Details', 'houses-theme'),
            array($this, 'render_meta_boxes'),
            'agent',
            'normal',
            'high'
        );
    }

    /**
     * Render meta boxes
     */
    public function render_meta_boxes($post) {
        wp_enqueue_media();
        wp_nonce_field('agent_meta_box', 'agent_meta_box_nonce');

        // Get saved values
        $phone = get_post_meta($post->ID, 'phone', true);
        $office_phone = get_post_meta($post->ID, 'office_phone', true);
        $line = get_post_meta($post->ID, 'line', true);
        $position = get_post_meta($post->ID, 'position', true);
        $email = get_post_meta($post->ID, 'email', true);
        $languages = get_post_meta($post->ID, 'languages', true);
        $agent_photo = get_post_meta($post->ID, 'agent_photo', true);
        ?>
        <div class="agent-meta-box">
            <p>
                <label for="phone"><?php _e('Phone Number', 'houses-theme'); ?></label>
                <input type="text" id="phone" name="phone" value="<?php echo esc_attr($phone); ?>" class="widefat">
            </p>
            <p>
                <label for="office_phone"><?php _e('Office Phone', 'houses-theme'); ?></label>
                <input type="text" id="office_phone" name="office_phone" value="<?php echo esc_attr($office_phone); ?>" class="widefat">
            </p>
            <p>
                <label for="line"><?php _e('LINE ID', 'houses-theme'); ?></label>
                <input type="text" id="line" name="line" value="<?php echo esc_attr($line); ?>" class="widefat">
            </p>
            <p>
                <label for="position"><?php _e('Position', 'houses-theme'); ?></label>
                <input type="text" id="position" name="position" value="<?php echo esc_attr($position); ?>" class="widefat">
            </p>
            <p>
                <label for="email"><?php _e('Email Address', 'houses-theme'); ?></label>
                <input type="email" id="email" name="email" value="<?php echo esc_attr($email); ?>" class="widefat">
            </p>
            <p>
                <label for="languages"><?php _e('Languages', 'houses-theme'); ?></label>
                <input type="text" id="languages" name="languages" value="<?php echo esc_attr($languages); ?>" class="widefat">
            </p>
            <p>
                <label for="agent_photo"><?php _e('Agent Photo', 'houses-theme'); ?></label>
                <input type="text" id="agent_photo" name="agent_photo" value="<?php echo esc_attr($agent_photo); ?>" class="widefat">
                <button type="button" class="upload_agent_photo button"><?php _e('Upload Photo', 'houses-theme'); ?></button>
            </p>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('.upload_agent_photo').on('click', function(e) {
                e.preventDefault();
                var button = $(this);
                var custom_uploader = wp.media({
                    title: '<?php _e('Select Agent Photo', 'houses-theme'); ?>',
                    button: {
                        text: '<?php _e('Use this photo', 'houses-theme'); ?>'
                    },
                    multiple: false
                }).on('select', function() {
                    var attachment = custom_uploader.state().get('selection').first().toJSON();
                    $('#agent_photo').val(attachment.url);
                }).open();
            });
        });
        </script>
        <style>
            .agent-meta-box label {
                display: block;
                font-weight: 600;
                margin: 1em 0 0.5em;
            }
            .agent-meta-box input {
                margin-bottom: 1em;
            }
        </style>
        <?php
    }

    /**
     * Save meta box data
     */
    public function save_meta_boxes($post_id) {
        // Check if our nonce is set
        if (!isset($_POST['agent_meta_box_nonce'])) {
            return;
        }

        // Verify that the nonce is valid
        if (!wp_verify_nonce($_POST['agent_meta_box_nonce'], 'agent_meta_box')) {
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

        // Save the data
        $fields = array('phone', 'office_phone', 'line', 'position', 'email', 'languages', 'agent_photo');
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
    }
}

// Initialize the class
new Houses_Agent_Post_Type();

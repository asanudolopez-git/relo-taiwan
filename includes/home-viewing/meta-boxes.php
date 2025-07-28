<?php
/**
 * Home Viewing Meta Boxes
 *
 * @package Houses Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Home Viewing Meta Boxes
 */
function houses_register_home_viewing_meta_boxes() {
    add_meta_box(
        'home_viewing_details',
        __('Home Viewing Details', 'houses-theme'),
        'houses_home_viewing_details_meta_box_callback',
        'home-viewing',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes_home-viewing', 'houses_register_home_viewing_meta_boxes');

/**
 * Home Viewing Details Meta Box Callback
 */
function houses_home_viewing_details_meta_box_callback($post) {
    // Add nonce for security
    wp_nonce_field('houses_home_viewing_meta_box', 'houses_home_viewing_meta_box_nonce');

    // Get current values
    $lease_summary = get_post_meta($post->ID, 'lease_summary', true);
    $lease_template_id = get_post_meta($post->ID, 'lease_template_id', true);

    // Get all lease templates for dropdown
    $lease_templates = get_posts(array(
        'post_type' => 'lease-template',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ));
    ?>
    <div class="houses-meta-box-container">
        <div class="houses-meta-box-field">
            <label for="lease_summary"><?php _e('Lease Summary', 'houses-theme'); ?></label>
            <textarea id="lease_summary" name="lease_summary" rows="4"><?php echo esc_textarea($lease_summary); ?></textarea>
        </div>

        <div class="houses-meta-box-field">
            <label for="lease_template_id"><?php _e('Lease Template', 'houses-theme'); ?></label>
            <select id="lease_template_id" name="lease_template_id">
                <option value=""><?php _e('Select Lease Template', 'houses-theme'); ?></option>
                <?php foreach ($lease_templates as $template) : ?>
                    <option value="<?php echo esc_attr($template->ID); ?>" <?php selected($lease_template_id, $template->ID); ?>>
                        <?php echo esc_html($template->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <style>
        .houses-meta-box-container {
            display: grid;
            grid-template-columns: 1fr;
            grid-gap: 15px;
        }
        .houses-meta-box-field {
            margin-bottom: 15px;
        }
        .houses-meta-box-field label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .houses-meta-box-field input[type="text"],
        .houses-meta-box-field select,
        .houses-meta-box-field textarea {
            width: 100%;
        }
    </style>
    <?php
}

/**
 * Save Home Viewing Meta Box Data
 */
function houses_save_home_viewing_meta_box_data($post_id) {
    // Check if nonce is set
    if (!isset($_POST['houses_home_viewing_meta_box_nonce'])) {
        return;
    }

    // Verify nonce
    if (!wp_verify_nonce($_POST['houses_home_viewing_meta_box_nonce'], 'houses_home_viewing_meta_box')) {
        return;
    }

    // If this is an autosave, don't do anything
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save data
    if (isset($_POST['lease_summary'])) {
        update_post_meta($post_id, 'lease_summary', sanitize_textarea_field($_POST['lease_summary']));
    }

    if (isset($_POST['lease_template_id'])) {
        update_post_meta($post_id, 'lease_template_id', sanitize_text_field($_POST['lease_template_id']));
    }
}
add_action('save_post_home-viewing', 'houses_save_home_viewing_meta_box_data');

<?php
/**
 * Landlord Meta Boxes
 *
 * @package Houses Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Landlord Meta Boxes
 */
function houses_register_landlord_meta_boxes() {
    add_meta_box(
        'landlord_details',
        __('Landlord Details', 'houses-theme'),
        'houses_landlord_details_meta_box_callback',
        'landlord',
        'normal',
        'high'
    );
}
//add_action('add_meta_boxes_landlord', 'houses_register_landlord_meta_boxes');

/**
 * Landlord Details Meta Box Callback
 */
function houses_landlord_details_meta_box_callback($post) {
    // Add nonce for security
    wp_nonce_field('houses_landlord_meta_box', 'houses_landlord_meta_box_nonce');

    // Get current values
    $phone = get_post_meta($post->ID, 'phone', true);
    $email = get_post_meta($post->ID, 'email', true);
    $address = get_post_meta($post->ID, 'address', true);
    $company = get_post_meta($post->ID, 'company', true);
    ?>
    <div class="houses-meta-box-container">
        <div class="houses-meta-box-field">
            <label for="landlord_phone"><?php _e('Phone Number', 'houses-theme'); ?></label>
            <input type="text" id="landlord_phone" name="landlord_phone" value="<?php echo esc_attr($phone); ?>">
        </div>

        <div class="houses-meta-box-field">
            <label for="landlord_email"><?php _e('Email Address', 'houses-theme'); ?></label>
            <input type="email" id="landlord_email" name="landlord_email" value="<?php echo esc_attr($email); ?>">
        </div>

        <div class="houses-meta-box-field">
            <label for="landlord_address"><?php _e('Address', 'houses-theme'); ?></label>
            <textarea id="landlord_address" name="landlord_address" rows="3"><?php echo esc_textarea($address); ?></textarea>
        </div>

        <div class="houses-meta-box-field">
            <label for="landlord_company"><?php _e('Company', 'houses-theme'); ?></label>
            <input type="text" id="landlord_company" name="landlord_company" value="<?php echo esc_attr($company); ?>">
        </div>
    </div>
    <style>
        .houses-meta-box-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
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
        .houses-meta-box-field input[type="email"],
        .houses-meta-box-field textarea {
            width: 100%;
        }
    </style>
    <?php
}

/**
 * Save Landlord Meta Box Data
 */
function houses_save_landlord_meta_box_data($post_id) {
    // Check if nonce is set
    if (!isset($_POST['houses_landlord_meta_box_nonce'])) {
        return;
    }

    // Verify nonce
    if (!wp_verify_nonce($_POST['houses_landlord_meta_box_nonce'], 'houses_landlord_meta_box')) {
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
    if (isset($_POST['landlord_phone'])) {
        update_post_meta($post_id, 'phone', sanitize_text_field($_POST['landlord_phone']));
    }

    if (isset($_POST['landlord_email'])) {
        update_post_meta($post_id, 'email', sanitize_email($_POST['landlord_email']));
    }

    if (isset($_POST['landlord_address'])) {
        update_post_meta($post_id, 'address', sanitize_textarea_field($_POST['landlord_address']));
    }

    if (isset($_POST['landlord_company'])) {
        update_post_meta($post_id, 'company', sanitize_text_field($_POST['landlord_company']));
    }
}
//add_action('save_post_landlord', 'houses_save_landlord_meta_box_data');

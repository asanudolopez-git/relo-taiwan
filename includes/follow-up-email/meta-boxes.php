<?php
/**
 * Meta boxes and email sending logic for Follow-up Email CPT
 */
class Houses_Follow_Up_Email_Meta_Boxes {

    /**
     * @var array Definition of the meta fields for the CPT
     */
    private $fields = array();

    public function __construct() {
        // Define the fields
        $this->fields = array(
            'client_lease_id'  => array(
                'label' => __('Client Lease', 'houses-theme'),
                'type'  => 'select',
                'options' => array(), // populated later
                'class' => 'full-width',
                'description' => __('Select the related Client Lease', 'houses-theme'),
            ),
            'email_type' => array(
                'label' => __('Email Type', 'houses-theme'),
                'type'  => 'select',
                'options' => array(
                    'check_in_emergency' => __('Check-in / Emergency List', 'houses-theme'),
                    'utility_samples'    => __('Utility Samples', 'houses-theme'),
                    'medical_info'       => __('Medical Info', 'houses-theme'),
                    'map_link'           => __('Map Link (Daily Living)', 'houses-theme'),
                    'mandarin_lessons'   => __('Mandarin Lessons', 'houses-theme'),
                ),
            ),
            'recipient_email' => array(
                'label' => __('Recipient Email', 'houses-theme'),
                'type'  => 'text',
                'class' => 'widefat',
            ),
            'email_subject' => array(
                'label' => __('Email Subject', 'houses-theme'),
                'type'  => 'text',
                'class' => 'widefat',
            ),
            'email_body' => array(
                'label' => __('Email Body', 'houses-theme'),
                'type'  => 'textarea',
                'class' => 'widefat',
            ),
            'attachment_id' => array(
                'label' => __('Attachment', 'houses-theme'),
                'type'  => 'file', // custom render
                'class' => 'widefat',
            ),
            'sent_status' => array(
                'label' => __('Sent?', 'houses-theme'),
                'type'  => 'checkbox',
            ),
            'date_sent' => array(
                'label' => __('Date Sent', 'houses-theme'),
                'type'  => 'date',
            ),
        );

        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('save_post', array($this, 'save_meta_box'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

        // AJAX handler for sending email
        add_action('wp_ajax_houses_send_follow_up_email', array($this, 'ajax_send_email'));
    }

    /**
     * Add meta box to Follow-up Email CPT
     */
    public function add_meta_box() {
        add_meta_box(
            'houses_follow_up_email_meta',
            __('Follow-up Email Details', 'houses-theme'),
            array($this, 'render_meta_box'),
            'follow_up_email',
            'normal',
            'high'
        );
    }

    /**
     * Enqueue admin script and localize data
     */
    public function enqueue_scripts($hook) {
        global $post;
        if (!isset($post) || $post->post_type !== 'follow_up_email') {
            return;
        }

        // Enqueue WordPress media uploader
        wp_enqueue_media();

        wp_enqueue_script(
            'houses-follow-up-email-admin',
            get_template_directory_uri() . '/includes/follow-up-email/assets/js/admin.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_localize_script('houses-follow-up-email-admin', 'houses_follow_email', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('houses_follow_up_email_nonce'),
            'post_id'  => $post->ID,
        ));
    }

    /**
     * Render the meta box
     */
    public function render_meta_box($post) {
        // Populate Client Lease options
        $client_leases = get_posts(array(
            'post_type'      => 'client_lease',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ));
        $this->fields['client_lease_id']['options'] = array('' => __('Select Client Lease', 'houses-theme'));
        foreach ($client_leases as $lease) {
            $this->fields['client_lease_id']['options'][$lease->ID] = $lease->post_title;
        }

        // Nonce
        wp_nonce_field('houses_follow_up_email_meta_box', 'houses_follow_up_email_meta_box_nonce');

        echo '<table class="form-table">';
        foreach ($this->fields as $id => $field) {
            $value = get_post_meta($post->ID, $id, true);
            echo '<tr>';
            echo '<th><label for="' . esc_attr($id) . '">' . esc_html($field['label']) . '</label></th>';
            echo '<td>';
            $this->render_field($id, $field, $value);
            if (!empty($field['description'])) {
                echo '<p class="description">' . esc_html($field['description']) . '</p>';
            }
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';

        // Send button (placed at bottom)
        echo '<p><button type="button" class="button button-primary" id="houses-send-follow-email">' . __('Send Email', 'houses-theme') . '</button></p>';
    }

    /**
     * Render individual field
     */
    private function render_field($id, $field, $value) {
        switch ($field['type']) {
            case 'text':
                printf('<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text" />', esc_attr($id), esc_attr($value));
                break;
            case 'textarea':
                printf('<textarea id="%1$s" name="%1$s" rows="5" class="large-text">%2$s</textarea>', esc_attr($id), esc_textarea($value));
                break;
            case 'date':
                printf('<input type="date" id="%1$s" name="%1$s" value="%2$s" />', esc_attr($id), esc_attr($value));
                break;
            case 'checkbox':
                printf('<input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s />', esc_attr($id), checked($value, '1', false));
                break;
            case 'select':
                printf('<select id="%1$s" name="%1$s">', esc_attr($id));
                foreach ($field['options'] as $opt_val => $opt_label) {
                    printf('<option value="%1$s" %2$s>%3$s</option>', esc_attr($opt_val), selected($value, $opt_val, false), esc_html($opt_label));
                }
                echo '</select>';
                break;
            case 'file':
                $attachment_url = $value ? wp_get_attachment_url($value) : '';
                printf('<input type="hidden" id="%1$s" name="%1$s" value="%2$s" />', esc_attr($id), esc_attr($value));
                printf('<button type="button" class="button" id="%1$s_button">%s</button>', esc_attr($id), __('Select File', 'houses-theme'));
                echo '<span class="file-preview" style="margin-left:10px;">' . esc_html($attachment_url) . '</span>';
                break;
        }
    }

    /**
     * Save meta box data
     */
    public function save_meta_box($post_id, $post) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if ($post->post_type !== 'follow_up_email') {
            return;
        }
        if (!isset($_POST['houses_follow_up_email_meta_box_nonce']) || !wp_verify_nonce($_POST['houses_follow_up_email_meta_box_nonce'], 'houses_follow_up_email_meta_box')) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        foreach ($this->fields as $id => $field) {
            if (isset($_POST[$id])) {
                $val = $_POST[$id];
                switch ($field['type']) {
                    case 'text':
                    case 'date':
                    case 'textarea':
                    case 'select':
                        $val = sanitize_text_field($val);
                        break;
                    case 'checkbox':
                        $val = '1';
                        break;
                    case 'file':
                        $val = intval($val);
                        break;
                }
                update_post_meta($post_id, $id, $val);
            } else {
                if ($field['type'] === 'checkbox') {
                    update_post_meta($post_id, $id, '0');
                }
            }
        }
    }

    /**
     * AJAX handler to send the email
     */
    public function ajax_send_email() {
        check_ajax_referer('houses_follow_up_email_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Permission denied.', 'houses-theme'));
        }

        $post_id = intval($_POST['post_id']);
        if (!$post_id) {
            wp_send_json_error(__('Invalid post ID.', 'houses-theme'));
        }

        // Retrieve meta values
        $recipient = get_post_meta($post_id, 'recipient_email', true);
        $subject   = get_post_meta($post_id, 'email_subject', true);
        $body      = get_post_meta($post_id, 'email_body', true);
        $attachment_id = intval(get_post_meta($post_id, 'attachment_id', true));
        $attachments = array();
        if ($attachment_id) {
            $file_path = get_attached_file($attachment_id);
            if ($file_path) {
                $attachments[] = $file_path;
            }
        }

        // Send email
        $sent = wp_mail($recipient, $subject, $body, array('Content-Type: text/html; charset=UTF-8'), $attachments);

        if ($sent) {
            update_post_meta($post_id, 'sent_status', '1');
            update_post_meta($post_id, 'date_sent', current_time('Y-m-d'));
            wp_send_json_success(__('Email sent successfully.', 'houses-theme'));
        } else {
            wp_send_json_error(__('Failed to send email.', 'houses-theme'));
        }
    }
}

// Initialize
new Houses_Follow_Up_Email_Meta_Boxes();

<?php
/**
 * SMTP Settings Page for Houses Theme
 */
if (!class_exists('Houses_SMTP_Settings_Page')) {
    class Houses_SMTP_Settings_Page {
        /**
         * Option name used to store settings
         */
        const OPTION_NAME = 'houses_smtp_settings';

        /**
         * Register hooks
         */
        public function __construct() {
            add_action('admin_menu', array($this, 'add_menu'));    // create menu entry
            add_action('admin_init', array($this, 'register_settings')); // register settings
        }

        /**
         * Add settings page under "Settings"
         */
        public function add_menu() {
            add_submenu_page(
                'options-general.php',
                __('SMTP Settings', 'houses-theme'),
                __('SMTP Settings', 'houses-theme'),
                'manage_options',
                'houses-smtp-settings',
                array($this, 'render_page')
            );
        }

        /**
         * Register settings, section, and fields
         */
        public function register_settings() {
            register_setting(
                'houses_smtp_settings_group',
                self::OPTION_NAME,
                array($this, 'sanitize')
            );

            add_settings_section(
                'houses_smtp_main_section',
                __('SMTP Configuration', 'houses-theme'),
                '__return_false',
                'houses-smtp-settings'
            );

            $fields = array(
                'host'      => __('SMTP Host', 'houses-theme'),
                'port'      => __('Port', 'houses-theme'),
                'secure'    => __('Encryption (tls/ssl/none)', 'houses-theme'),
                'user'      => __('Username', 'houses-theme'),
                'pass'      => __('Password', 'houses-theme'),
                'from'      => __('From Email', 'houses-theme'),
                'from_name' => __('From Name', 'houses-theme'),
            );

            foreach ($fields as $id => $label) {
                add_settings_field(
                    $id,
                    $label,
                    array($this, 'render_field'),
                    'houses-smtp-settings',
                    'houses_smtp_main_section',
                    array('id' => $id, 'label' => $label)
                );
            }
        }

        /**
         * Sanitize input
         */
        public function sanitize($input) {
            $output = array();
            $output['host']      = sanitize_text_field($input['host'] ?? '');
            $output['port']      = intval($input['port'] ?? 587);
            $output['secure']    = sanitize_text_field($input['secure'] ?? 'tls');
            $output['user']      = sanitize_text_field($input['user'] ?? '');
            $output['pass']      = sanitize_text_field($input['pass'] ?? '');
            $output['from']      = sanitize_email($input['from'] ?? '');
            $output['from_name'] = sanitize_text_field($input['from_name'] ?? '');
            return $output;
        }

        /**
         * Render individual input field
         */
        public function render_field($args) {
            $options = get_option(self::OPTION_NAME, array());
            $id = esc_attr($args['id']);
            $value = $options[$id] ?? '';
            $type = 'text';
            if ($id === 'pass') {
                $type = 'password';
            } elseif ($id === 'port') {
                $type = 'number';
            }
            printf('<input type="%s" id="%s" name="%s[%s]" value="%s" class="regular-text" />', $type, $id, self::OPTION_NAME, $id, esc_attr($value));
            if ($id === 'secure') {
                echo '<p class="description">' . esc_html__('Use "tls", "ssl" or leave blank for none.', 'houses-theme') . '</p>';
            }
        }

        /**
         * Render settings page content
         */
        public function render_page() {
            if (!current_user_can('manage_options')) {
                return;
            }
            ?>
            <div class="wrap">
                <h1><?php _e('SMTP Settings', 'houses-theme'); ?></h1>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('houses_smtp_settings_group');
                    do_settings_sections('houses-smtp-settings');
                    submit_button();
                    ?>
                </form>
            </div>
            <?php
        }
    }

    new Houses_SMTP_Settings_Page();
}

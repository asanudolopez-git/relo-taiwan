<?php
/**
 * Houses Theme functions and definitions
 */

if (!defined('_S_VERSION')) {
    define('_S_VERSION', '1.0.0');
}

// Fix SSL verification issues
add_filter('https_ssl_verify', '__return_false');
add_filter('https_local_ssl_verify', '__return_false');

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function houses_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    
    // Register navigation menus
    register_nav_menus(array(
        'menu-1' => esc_html__('Primary Menu', 'houses-theme'),
        'footer-menu' => esc_html__('Footer Menu', 'houses-theme'),
    ));

    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));

    // Add theme support for selective refresh for widgets.
    add_theme_support('customize-selective-refresh-widgets');
}
add_action('after_setup_theme', 'houses_theme_setup');

/**
 * Enqueue scripts and styles.
 */
function houses_theme_scripts() {
    wp_enqueue_style('houses-theme-style', get_stylesheet_uri(), array(), _S_VERSION);
    wp_style_add_data('houses-theme-style', 'rtl', 'replace');

    // Enqueue property listing styles on property archive page
    if (is_post_type_archive('property')) {
        wp_enqueue_style(
            'houses-property-listing-css',
            get_template_directory_uri() . '/includes/property/admin/assets/css/admin-list.css',
            array(),
            _S_VERSION
        );
    }
    
    // Enqueue single customer property display styles and scripts
    if (is_singular('customer')) {
        wp_enqueue_style(
            'houses-single-customer-properties-css',
            get_template_directory_uri() . '/assets/css/single-customer-properties.css',
            array(),
            _S_VERSION
        );
        
        wp_enqueue_script(
            'houses-single-customer-gallery-js',
            get_template_directory_uri() . '/assets/js/single-customer-gallery.js',
            array('jquery'),
            _S_VERSION,
            true
        );
    }
    
    wp_enqueue_style('dashicons');
    wp_enqueue_script('houses-theme-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true);

    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'houses_theme_scripts');

/**
 * Enqueue admin styles
 */
function houses_admin_styles($hook) {
    global $post;
    
    if ($hook == 'post-new.php' || $hook == 'post.php') {
        // Enqueue common admin styles (checkboxes, etc.)
        wp_enqueue_style(
            'houses-admin-common',
            get_template_directory_uri() . '/includes/common/assets/css/admin.css',
            array(),
            _S_VERSION
        );
        if ('property' === $post->post_type) {
            wp_enqueue_style(
                'houses-admin-property', 
                get_template_directory_uri() . '/includes/property/assets/css/admin.css',
                array(),
                _S_VERSION
            );
        }
        if ('customer' === $post->post_type) {
            wp_enqueue_style(
                'houses-admin-customer', 
                get_template_directory_uri() . '/includes/customer/assets/css/admin.css',
                array(),
                _S_VERSION
            );
        }
    }
}
add_action('admin_enqueue_scripts', 'houses_admin_styles');

/**
 * Load property related files
 */
require_once get_template_directory() . '/includes/property/post-type.php';
require_once get_template_directory() . '/includes/property/meta-boxes.php';
require_once get_template_directory() . '/includes/property/admin/admin-list.php';


// Load agent files
require_once get_template_directory() . '/includes/agent/post-type.php';

// Load customer files
require_once get_template_directory() . '/includes/customer/post-type.php';
require_once get_template_directory() . '/includes/customer/meta-boxes.php';
require_once get_template_directory() . '/includes/customer/admin-columns.php';

// Include Customer House List files
require get_template_directory() . '/includes/customer-house-list/post-type.php';
require get_template_directory() . '/includes/customer-house-list/meta-boxes.php';
require get_template_directory() . '/includes/customer-house-list/install.php';

// Load Departure Services files
require get_template_directory() . '/includes/departure-services/post-type.php';
require get_template_directory() . '/includes/departure-services/meta-boxes.php';

// Asegurar que la tabla de snapshots exista
add_action('init', 'houses_ensure_customer_list_tables');
function houses_ensure_customer_list_tables() {
    houses_customer_list_install();
}

// Función para traducir direcciones de inglés a chino
function houses_translate_address() {
    // Verificar nonce por seguridad
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'property_address_translate')) {
        wp_send_json_error(['message' => 'Security check failed']);
    }
    
    // Verificar que se proporcionó una dirección
    if (empty($_POST['address'])) {
        wp_send_json_error(['message' => 'No address provided']);
    }
    
    $address = sanitize_text_field($_POST['address']);
    
    // Usar LibreTranslate API (gratuita)
    $translated = houses_translate_with_libre_translate($address);
    
    if ($translated) {
        wp_send_json_success(['translated_address' => $translated]);
    } else {
        // Si falla, intentar con una simulación simple
        $simulated = houses_simulate_translation($address);
        wp_send_json_success(['translated_address' => $simulated]);
    }
}
add_action('wp_ajax_translate_address', 'houses_translate_address');

// Función para traducir usando LibreTranslate API (gratuita)
function houses_translate_with_libre_translate($text) {
    $url = 'https://libretranslate.com/translate';
    
    $args = [
        'body' => json_encode([
            'q' => $text,
            'source' => 'en',
            'target' => 'zh',
            'format' => 'text',
            'api_key' => '' // API key opcional, puede ser necesaria en algunos casos
        ]),
        'headers' => [
            'Content-Type' => 'application/json'
        ]
    ];
    
    $response = wp_remote_post($url, $args);
    
    if (is_wp_error($response)) {
        return false;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (isset($data['translatedText'])) {
        return $data['translatedText'];
    }
    
    return false;
}

// Función para simular traducción (fallback)
function houses_simulate_translation($text) {
    // Simulación básica para direcciones comunes en Taiwán
    $translations = [
        'Lane' => '巷',
        'Sec.' => '段',
        'Section' => '段',
        'Road' => '路',
        'Street' => '街',
        'Avenue' => '大道',
        'No.' => '號',
        'Number' => '號',
        'Floor' => '樓',
        'F' => '樓',
        'Alley' => '弄',
    ];
    
    // Reemplazar palabras comunes
    $translated = $text;
    foreach ($translations as $en => $zh) {
        $translated = str_replace($en, $zh, $translated);
    }
    
    // Agregar caracteres chinos para simular traducción
    return $translated . ' (中文地址)';
}

// Cargar scripts para el traductor de direcciones y Google Maps API
function houses_enqueue_property_scripts($hook) {
    global $post; 

    $current_post_type = '';
    if (isset($GLOBALS['typenow'])) {
        $current_post_type = $GLOBALS['typenow'];
    } elseif (isset($_GET['post_type'])) {
        $current_post_type = sanitize_text_field($_GET['post_type']);
    } elseif (isset($post->post_type)) {
        $current_post_type = $post->post_type;
    }

    if ( ($hook == 'post.php' || $hook == 'post-new.php') && $current_post_type === 'property' ) {
        
        wp_enqueue_script(
            'address-translator',
            get_template_directory_uri() . '/includes/property/assets/js/address-translator.js',
            array('jquery'), 
            '1.0.3', // Incremented version
            true     
        );

        // Your API Key
        $google_api_key = 'AIzaSyBZ9H803vG0QMi646uj7E45NBJmAnsk6VU'; 

        if ( !empty($google_api_key) ) { 
            wp_enqueue_script(
                'google-maps-places-api',
                'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($google_api_key) . '&libraries=places&callback=initMap', // Keep callback
                array(), 
                null,    
                true     
            );
        } else {
            if (current_user_can('manage_options')) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-warning is-dismissible"><p>';
                    echo '<strong>Houses Theme - Traductor de Direcciones:</strong> La API Key de Google Maps no está configurada en <code>functions.php</code>. La traducción de direcciones con Google Places no funcionará.';
                    echo '</p></div>';
                });
            }
            error_log('Houses Theme: Google Maps API Key no configurada para el traductor de direcciones.');
        }
    }
}

/**
 * Print Google Maps initMap callback function in admin head.
 */
function houses_print_google_maps_init_callback() {
    $screen = get_current_screen();
    
    if (!$screen) {
        return;
    }

    $base_hook = $screen->base; // 'post', 'edit', etc.

    $current_post_type = '';
    if (isset($GLOBALS['typenow'])) {
        $current_post_type = $GLOBALS['typenow'];
    } elseif (isset($_GET['post_type'])) {
        $current_post_type = sanitize_text_field($_GET['post_type']);
    } elseif (isset($screen->post_type) && $screen->post_type) {
        $current_post_type = $screen->post_type;
    }
    
    // Condition to match where Google Maps API is enqueued
    if ( ($base_hook == 'post' || $base_hook == 'post-new') && $current_post_type === 'property' ) {
        
        // Your API Key - ensure this matches the key used in enqueue script or retrieve from options
        $google_api_key = 'AIzaSyBZ9H803vG0QMi646uj7E45NBJmAnsk6VU'; 

        if (!empty($google_api_key)) {
            echo "<script type=\"text/javascript\">\n";
            echo "// Google Maps initMap defined in admin_head via houses_print_google_maps_init_callback\n";
            echo "function initMap() {\n";
            echo "  console.log('Google Maps API initMap() successfully called. (Defined in admin_head)');\n";
            echo "}\n";
            echo "</script>\n";
        }
    }
}
add_action('admin_head', 'houses_print_google_maps_init_callback');
add_action('admin_enqueue_scripts', 'houses_enqueue_property_scripts');

// Load district files
require_once get_template_directory() . '/includes/district/post-type.php';

// Load station files
require_once get_template_directory() . '/includes/station/post-type.php';

// Load landlord files
require_once get_template_directory() . '/includes/landlord/post-type.php';
require_once get_template_directory() . '/includes/landlord/meta-boxes.php';

// Load lease template files
require_once get_template_directory() . '/includes/lease-template/post-type.php';
require_once get_template_directory() . '/includes/lease-template/meta-boxes.php';

// Load client lease files
require_once get_template_directory() . '/includes/client-lease/post-type.php';
require_once get_template_directory() . '/includes/client-lease/meta-boxes.php';

// Load Lease Summary files
// require_once get_template_directory() . '/includes/lease-summary/post-type.php';
// require_once get_template_directory() . '/includes/lease-summary/meta-boxes.php';

// Load accommodation files
require_once get_template_directory() . '/includes/accommodation/post-type.php';
require_once get_template_directory() . '/includes/accommodation/meta-boxes.php';

// Load home viewing files
require_once get_template_directory() . '/includes/home-viewing/post-type.php';
require_once get_template_directory() . '/includes/home-viewing/meta-boxes.php';

// Load Follow-up Email files
require_once get_template_directory() . '/includes/follow-up-email/post-type.php';
require_once get_template_directory() . '/includes/follow-up-email/meta-boxes.php';

// Load admin files
require_once get_template_directory() . '/includes/admin/admin-page.php';
require_once get_template_directory() . '/includes/admin/hide-permalink.php';
require_once get_template_directory() . '/includes/admin/smtp-settings.php';

// Load company files
require_once get_template_directory() . '/includes/company/post-type.php';
require_once get_template_directory() . '/includes/company/meta-boxes.php';

function houses_enqueue_scripts() {
    // Property styles
    wp_enqueue_style(
        'houses-property-single',
        get_template_directory_uri() . '/includes/property/assets/css/single.css',
        array(),
        '1.0.0'
    );

    // Agent styles
    wp_enqueue_style(
        'houses-agent',
        get_template_directory_uri() . '/includes/agent/assets/css/agent.css',
        array(),
        '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'houses_enqueue_scripts');

function remove_admin_menu_items() {
    if (current_user_can('editor')) {
        // Define which menu items to keep (exact slugs from post-type registrations)
        $keep_menus = array(
            'edit.php?post_type=property',     // Property
            'edit.php?post_type=departure_service',        // Assignee (Agents)
            'edit.php?post_type=accommodation',        // Assignee (Agents)
            'edit.php?post_type=customer',        // Assignee (Agents)
            'edit.php?post_type=client_lease' // Lease Template
        );
        
        // Get the global menu array
        global $menu;
        
        // Loop through the menu and remove items not in our keep list
        foreach ($menu as $key => $item) {
            // The [2] element contains the menu slug
            if (isset($item[2]) && !in_array($item[2], $keep_menus)) {
                remove_menu_page($item[2]);
            }
        }
    }
}
add_action('admin_menu', 'remove_admin_menu_items');

/**
 * Remove default editor for properties
 */
function houses_remove_property_editor() {
    remove_post_type_support('property', 'editor');
}
add_action('init', 'houses_remove_property_editor');

/**
 * Add custom CSS to hide the editor toolbar
 */
function houses_hide_editor_toolbar() {
    echo '<style>
        .property-form .wp-editor-tools,
        .property-form .wp-editor-tabs,
        #wp-content-editor-tools,
        .wp-editor-container .wp-editor-tabs {
            display: none !important;
        }
    </style>';
}
add_action('admin_head', 'houses_hide_editor_toolbar');

/**
 * Configure SMTP using constants defined in wp-config.php
 * Define the following constants in wp-config.php to enable SMTP:
 *   define('HOUSES_SMTP_HOST', 'smtp.example.com');
 *   define('HOUSES_SMTP_PORT', 587);
 *   define('HOUSES_SMTP_USER', 'user@example.com');
 *   define('HOUSES_SMTP_PASS', 'password');
 *   define('HOUSES_SMTP_SECURE', 'tls'); // tls or ssl
 *   define('HOUSES_SMTP_FROM', 'noreply@example.com');
 *   define('HOUSES_SMTP_FROM_NAME', 'Houses Theme');
 */
function houses_setup_phpmailer($phpmailer) {
    // Try settings stored in options first
    $smtp_settings = get_option('houses_smtp_settings', array());

    if (!empty($smtp_settings['host'])) {
        $phpmailer->isSMTP();
        $phpmailer->Host       = $smtp_settings['host'];
        $phpmailer->Port       = !empty($smtp_settings['port']) ? intval($smtp_settings['port']) : 587;
        $secure                = !empty($smtp_settings['secure']) ? $smtp_settings['secure'] : 'tls';
        $phpmailer->SMTPSecure = $secure;

        // Auth only if user/pass provided
        if (!empty($smtp_settings['user'])) {
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = $smtp_settings['user'];
            $phpmailer->Password = !empty($smtp_settings['pass']) ? $smtp_settings['pass'] : '';
        }

        $phpmailer->From     = !empty($smtp_settings['from']) ? $smtp_settings['from'] : get_bloginfo('admin_email');
        $phpmailer->FromName = !empty($smtp_settings['from_name']) ? $smtp_settings['from_name'] : get_bloginfo('name');
        return; // done
    }

    // Fallback to constants for backward compatibility
    if (defined('HOUSES_SMTP_HOST')) {
        $phpmailer->isSMTP();
        $phpmailer->Host       = HOUSES_SMTP_HOST;
        $phpmailer->Port       = defined('HOUSES_SMTP_PORT') ? HOUSES_SMTP_PORT : 587;
        $phpmailer->SMTPAuth   = true;
        $phpmailer->Username   = defined('HOUSES_SMTP_USER') ? HOUSES_SMTP_USER : '';
        $phpmailer->Password   = defined('HOUSES_SMTP_PASS') ? HOUSES_SMTP_PASS : '';
        $phpmailer->SMTPSecure = defined('HOUSES_SMTP_SECURE') ? HOUSES_SMTP_SECURE : 'tls';
        $phpmailer->From       = defined('HOUSES_SMTP_FROM') ? HOUSES_SMTP_FROM : get_bloginfo('admin_email');
        $phpmailer->FromName   = defined('HOUSES_SMTP_FROM_NAME') ? HOUSES_SMTP_FROM_NAME : get_bloginfo('name');
    }
}
add_action('phpmailer_init', 'houses_setup_phpmailer');

require_once get_template_directory() . '/pdf-generator.php';   
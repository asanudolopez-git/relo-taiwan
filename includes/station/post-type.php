<?php
/**
 * Station Custom Post Type
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register the Station custom post type
 */
function houses_register_station_post_type() {
    $labels = array(
        'name'               => _x('Stations', 'post type general name', 'houses-theme'),
        'singular_name'      => _x('Station', 'post type singular name', 'houses-theme'),
        'menu_name'          => _x('Stations', 'admin menu', 'houses-theme'),
        'name_admin_bar'     => _x('Station', 'add new on admin bar', 'houses-theme'),
        'add_new'            => _x('Add New', 'station', 'houses-theme'),
        'add_new_item'       => __('Add New Station', 'houses-theme'),
        'new_item'           => __('New Station', 'houses-theme'),
        'edit_item'          => __('Edit Station', 'houses-theme'),
        'view_item'          => __('View Station', 'houses-theme'),
        'all_items'          => __('All Stations', 'houses-theme'),
        'search_items'       => __('Search Stations', 'houses-theme'),
        'parent_item_colon'  => __('Parent Stations:', 'houses-theme'),
        'not_found'          => __('No stations found.', 'houses-theme'),
        'not_found_in_trash' => __('No stations found in Trash.', 'houses-theme')
    );

    $args = array(
        'labels'             => $labels,
        'description'        => __('Stations for properties', 'houses-theme'),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'station'),
        'show_in_rest'       => false,
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array('title'),
        'menu_icon'          => 'dashicons-location-alt',
    );

    register_post_type('station', $args);
}
add_action('init', 'houses_register_station_post_type');

/**
 * Add meta box for station line
 */
function houses_add_station_meta_boxes() {
    add_meta_box(
        'houses_station_meta_box',
        __('Station Details', 'houses-theme'),
        'houses_render_station_meta_box',
        'station',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'houses_add_station_meta_boxes');

/**
 * Render station meta box
 */
function houses_render_station_meta_box($post) {
    // Add nonce for security
    wp_nonce_field('houses_station_meta_box', 'houses_station_meta_box_nonce');
    
    // Get current values
    $station_line = get_post_meta($post->ID, 'station_line', true);
    $station_code = get_post_meta($post->ID, 'station_code', true);
    
    // Lines options
    $lines = array(
        'BR' => 'Brown Line (Wenhu Line)',
        'R' => 'Red Line (Tamsui-Xinyi Line)',
        'G' => 'Green Line (Songshan-Xindian Line)',
        'O' => 'Orange Line (Zhonghe-Xinlu Line)',
        'BL' => 'Blue Line (Bannan Line)',
        'Y' => 'Yellow Line (Circular Line)'
    );
    
    // Output fields
    echo '<p>';
    echo '<label for="station_line">' . __('Metro Line', 'houses-theme') . '</label><br>';
    echo '<select id="station_line" name="station_line" class="widefat">';
    echo '<option value="">' . __('Select a line', 'houses-theme') . '</option>';
    
    foreach ($lines as $code => $name) {
        echo '<option value="' . esc_attr($code) . '"' . selected($station_line, $code, false) . '>' . esc_html($name) . '</option>';
    }
    
    echo '</select>';
    echo '</p>';
    
    echo '<p>';
    echo '<label for="station_code">' . __('Station Code', 'houses-theme') . '</label><br>';
    echo '<input type="text" id="station_code" name="station_code" value="' . esc_attr($station_code) . '" class="widefat">';
    echo '</p>';
}

/**
 * Save station meta box data
 */
function houses_save_station_meta_box($post_id) {
    // Check if our nonce is set
    if (!isset($_POST['houses_station_meta_box_nonce'])) {
        return;
    }
    
    // Verify the nonce
    if (!wp_verify_nonce($_POST['houses_station_meta_box_nonce'], 'houses_station_meta_box')) {
        return;
    }
    
    // If this is an autosave, we don't want to do anything
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save the data
    if (isset($_POST['station_line'])) {
        update_post_meta($post_id, 'station_line', sanitize_text_field($_POST['station_line']));
    }
    
    if (isset($_POST['station_code'])) {
        update_post_meta($post_id, 'station_code', sanitize_text_field($_POST['station_code']));
    }
}
add_action('save_post', 'houses_save_station_meta_box');

/**
 * Import sample stations data (only for development)
 */
function houses_import_sample_stations_data($force = false) {
    // Only run this function in development environment or if forced
    if ((!defined('WP_DEBUG') || !WP_DEBUG) && !$force) {
        return;
    }
    
    // Check if we've already imported stations (skip if forced)
    if (get_option('houses_stations_imported', false) && !$force) {
        return;
    }
    
    // Sample station data with line codes
    $sample_stations = array(
        // Brown Line (Wenhu Line)
        'BR' => array(
            'BR01' => 'Taipei Zoo',
            'BR02' => 'Muzha',
            'BR03' => 'Wanfang Community',
            'BR04' => 'Wanfang Hospital',
            'BR05' => 'Xinhai',
            'BR06' => 'Linguang',
            'BR07' => 'Liuzhangli',
            'BR08' => 'Technology Building',
            'BR09' => 'Daan',
            'BR10' => 'Zhongxiao Fuxing',
            'BR11' => 'Nanjing Fuxing',
            'BR12' => 'Zhongshan Junior High School',
            'BR13' => 'Songshan Airport',
            'BR14' => 'Dazhi',
            'BR15' => 'Jiannan Rd.',
            'BR16' => 'Xihu',
            'BR17' => 'Gangqian',
            'BR18' => 'Wende',
            'BR19' => 'Neihu',
            'BR20' => 'Dahu Park',
            'BR21' => 'Huzhou',
            'BR22' => 'Donghu',
            'BR23' => 'Nangang Software Park',
            'BR24' => 'Taipei Nangang Exhibition Center'
        ),
        // Red Line (Tamsui-Xinyi Line)
        'R' => array(
            'R02' => 'Xiangshan',
            'R03' => 'Taipei 101/World Trade Center',
            'R04' => 'Xinyi Anhe',
            'R05' => 'Daan',
            'R06' => 'Daan Park',
            'R07' => 'Dongmen',
            'R08' => 'C.K.S. Memorial Hall',
            'R09' => 'NTU Hospital',
            'R10' => 'Taipei Main Station',
            'R11' => 'Zhongshan',
            'R12' => 'Shuanglian',
            'R13' => 'Minquan W. Rd.',
            'R14' => 'Yuanshan',
            'R15' => 'Jiantan',
            'R16' => 'Shilin',
            'R17' => 'Zhishan',
            'R18' => 'Mingde',
            'R19' => 'Shipai',
            'R20' => 'Qilian',
            'R21' => 'Qiyan',
            'R22' => 'Beitou',
            'R22A' => 'Xinbeitou',
            'R23' => 'Fuxinggang',
            'R24' => 'Zhongyi',
            'R25' => 'Guandu',
            'R26' => 'Zhuwei',
            'R27' => 'Hongshulin',
            'R28' => 'Tamsui'
        ),
        // Green Line (Songshan-Xindian Line)
        'G' => array(
            'G01' => 'Xindian',
            'G02' => 'Xindian District Office',
            'G03' => 'Qizhang',
            'G03A' => 'Xiaobitan',
            'G04' => 'Dapinglin',
            'G05' => 'Jingmei',
            'G06' => 'Wanlong',
            'G07' => 'Gongguan',
            'G08' => 'Taipower Building',
            'G09' => 'Guting',
            'G10' => 'C.K.S. Memorial Hall',
            'G11' => 'Xiaonanmen',
            'G12' => 'Ximen',
            'G13' => 'Beimen',
            'G14' => 'Zhongshan',
            'G15' => 'Songjiang Nanjing',
            'G16' => 'Nanjing Fuxing',
            'G17' => 'Taipei Arena',
            'G18' => 'Nanjing Sanmin',
            'G19' => 'Songshan'
        ),
        // Orange Line (Zhonghe-Xinlu Line)
        'O' => array(
            'O01' => 'Nanshijiao',
            'O02' => 'Jingan',
            'O03' => 'Yongan Market',
            'O04' => 'Dingxi',
            'O05' => 'Guting',
            'O06' => 'Dongmen',
            'O07' => 'Zhongxiao Xinsheng',
            'O08' => 'Songjiang Nanjing',
            'O09' => 'Xingtian Temple',
            'O10' => 'Zhongshan Elementary School',
            'O11' => 'Minquan W. Rd.',
            'O12' => 'Daqiaotou',
            'O13' => 'Taipei Bridge',
            'O14' => 'Cailiao',
            'O15' => 'Sanchong',
            'O16' => 'Xianse Temple',
            'O17' => 'Touqianzhuang',
            'O18' => 'Xinzhuang',
            'O19' => 'Fu Jen University',
            'O20' => 'Danfeng',
            'O21' => 'Huilong',
            'O50' => 'Sanchong Elementary School',
            'O51' => 'Sanhe Junior High School',
            'O52' => 'St. Ignatius High School',
            'O53' => 'Sanmin Senior High School',
            'O54' => 'Luzhou'
        ),
        // Blue Line (Bannan Line)
        'BL' => array(
            'BL01' => 'Dingpu',
            'BL02' => 'Yongning',
            'BL03' => 'Tucheng',
            'BL04' => 'Haishan',
            'BL05' => 'Far Eastern Hospital',
            'BL06' => 'Fuzhong',
            'BL07' => 'Banqiao',
            'BL08' => 'Xinpu',
            'BL09' => 'Jiangzicui',
            'BL10' => 'Longshan Temple',
            'BL11' => 'Ximen',
            'BL12' => 'Taipei Main Station',
            'BL13' => 'Shandao Temple',
            'BL14' => 'Zhongxiao Xinsheng',
            'BL15' => 'Zhongxiao Fuxing',
            'BL16' => 'Zhongxiao Dunhua',
            'BL17' => 'S.Y.S. Memorial Hall',
            'BL18' => 'Taipei City Hall',
            'BL19' => 'Yongchun',
            'BL20' => 'Houshanpi',
            'BL21' => 'Kunyang',
            'BL22' => 'Nangang',
            'BL23' => 'Taipei Nangang Exhibition Center'
        ),
        // Yellow Line (Circular Line)
        'Y' => array(
            'Y07' => 'Dapinglin',
            'Y08' => 'Shisizhang',
            'Y09' => 'Xiulang Bridge',
            'Y10' => 'Jingping',
            'Y11' => 'Jingan',
            'Y12' => 'Zhonghe',
            'Y13' => 'Qiaohe',
            'Y14' => 'Zhongyuan',
            'Y15' => 'Banxin',
            'Y16' => 'Banqiao',
            'Y17' => 'Xinpu Minsheng',
            'Y18' => 'Touqianzhuang',
            'Y19' => 'Xingfu',
            'Y20' => 'New Taipei Industrial Park'
        )
    );
    
    // Create each station
    foreach ($sample_stations as $line_code => $stations) {
        foreach ($stations as $station_code => $station_name) {
            // Check if station already exists
            $existing = get_page_by_title($station_name, OBJECT, 'station');
            
            if (!$existing) {
                // Create new station
                $station_id = wp_insert_post(array(
                    'post_title'    => $station_name,
                    'post_type'     => 'station',
                    'post_status'   => 'publish',
                ));
                
                if (!is_wp_error($station_id)) {
                    // Save station line and code
                    update_post_meta($station_id, 'station_line', $line_code);
                    update_post_meta($station_id, 'station_code', $station_code);
                }
            }
        }
    }
    
    // Mark as imported
    update_option('houses_stations_imported', true);
}
add_action('after_setup_theme', 'houses_import_sample_stations_data');

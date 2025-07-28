<?php
/**
 * Houses Theme Admin Page
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Add admin menu page
 */
function houses_add_admin_menu() {
    add_menu_page(
        __('Houses Settings', 'houses-theme'),
        __('Houses Settings', 'houses-theme'),
        'manage_options',
        'houses-settings',
        'houses_admin_page_content',
        'dashicons-admin-home',
        30
    );
}
add_action('admin_menu', 'houses_add_admin_menu');

/**
 * Admin page content
 */
function houses_admin_page_content() {
    // Handle form submissions
    if (isset($_POST['houses_reset_districts']) && check_admin_referer('houses_reset_data', 'houses_nonce')) {
        houses_reset_districts();
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Districts have been reset to defaults.', 'houses-theme') . '</p></div>';
    }
    
    if (isset($_POST['houses_reset_stations']) && check_admin_referer('houses_reset_data', 'houses_nonce')) {
        houses_reset_stations();
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Stations have been reset to defaults.', 'houses-theme') . '</p></div>';
    }
    
    // Display admin page
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Houses Theme Settings', 'houses-theme'); ?></h1>
        
        <div class="card">
            <h2><?php echo esc_html__('Reset Default Data', 'houses-theme'); ?></h2>
            <p><?php echo esc_html__('Use these buttons to restore default data for districts and stations.', 'houses-theme'); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('houses_reset_data', 'houses_nonce'); ?>
                
                <div style="margin-bottom: 20px;">
                    <h3><?php echo esc_html__('Districts', 'houses-theme'); ?></h3>
                    <p><?php echo esc_html__('This will delete all existing districts and restore the default list.', 'houses-theme'); ?></p>
                    <button type="submit" name="houses_reset_districts" class="button button-primary">
                        <?php echo esc_html__('Restore Default Districts', 'houses-theme'); ?>
                    </button>
                </div>
                
                <div>
                    <h3><?php echo esc_html__('Stations', 'houses-theme'); ?></h3>
                    <p><?php echo esc_html__('This will delete all existing stations and restore the default list.', 'houses-theme'); ?></p>
                    <button type="submit" name="houses_reset_stations" class="button button-primary">
                        <?php echo esc_html__('Restore Default Stations', 'houses-theme'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php
}

/**
 * Reset districts to default
 */
function houses_reset_districts() {
    // Delete all existing districts
    $existing_districts = get_posts(array(
        'post_type' => 'district',
        'numberposts' => -1,
        'post_status' => 'any',
        'fields' => 'ids',
    ));
    
    foreach ($existing_districts as $district_id) {
        wp_delete_post($district_id, true);
    }
    
    // Default districts
    $default_districts = array(
        'Songshan',
        'Xinyi',
        'Da\'an',
        'Zhongshan',
        'Zhongzheng',
        'Datong',
        'Wanhua',
        'Wenshan',
        'Nangang',
        'Neihu',
        'Shilin',
        'Beitou'
    );
    
    // Create default districts
    foreach ($default_districts as $district_name) {
        wp_insert_post(array(
            'post_title' => $district_name,
            'post_type' => 'district',
            'post_status' => 'publish',
        ));
    }
    
    // Mark as imported
    update_option('houses_districts_reset', true);
}

/**
 * Reset stations to default
 */
function houses_reset_stations() {
    // Delete all existing stations
    $existing_stations = get_posts(array(
        'post_type' => 'station',
        'numberposts' => -1,
        'post_status' => 'any',
        'fields' => 'ids',
    ));
    
    foreach ($existing_stations as $station_id) {
        wp_delete_post($station_id, true);
    }
    
    // Import default stations
    houses_import_sample_stations_data(true);
    
    // Mark as reset
    update_option('houses_stations_reset', true);
}

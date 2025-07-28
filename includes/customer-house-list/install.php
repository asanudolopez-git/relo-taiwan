<?php
function houses_customer_list_install() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'house_list_property_snapshots';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        list_id bigint(20) NOT NULL,
        property_id bigint(20) NOT NULL,
        snapshot_data longtext NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY list_id (list_id),
        KEY property_id (property_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(get_template_directory() . '/functions.php', 'houses_customer_list_install');

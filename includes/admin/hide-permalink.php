<?php
/**
 * Hide permalink functionality for various post types
 */

/**
 * Hide permalink in edit screen for specified post types
 */
function houses_hide_permalink() {
    global $post_type;
    
    // List of post types where permalink should be hidden
    $hide_permalink_post_types = array(
        'customer',          // Assignee
        'company',           // Company
        'customer-house-list', // House List
        'client_lease',      // Client Lease
        'accommodation',     // Accommodation
        'home_viewing',      // Home Viewing
        'district',          // District
        'station',           // Station
        'landlord',          // Landlord
        'lease_template'     // Lease Template
    );
    
    // Check if current post type should have permalink hidden
    if (in_array($post_type, $hide_permalink_post_types)) {
        echo '<style>#edit-slug-box {display: none;}</style>';
    }
}

// Add the function to both post.php and post-new.php screens
add_action('admin_head-post.php', 'houses_hide_permalink');
add_action('admin_head-post-new.php', 'houses_hide_permalink');

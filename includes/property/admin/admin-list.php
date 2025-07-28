<?php
/**
 * Custom Property Admin List
 * 
 * Customizes the property list in the WordPress admin panel
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Property Admin List Customization
 */
class Houses_Property_Admin_List {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Add custom columns to property list
        add_filter('manage_property_posts_columns', array($this, 'add_custom_columns'));
        
        // Populate custom columns with data
        add_action('manage_property_posts_custom_column', array($this, 'populate_custom_columns'), 10, 2);
        
        // Make columns sortable
        add_filter('manage_edit-property_sortable_columns', array($this, 'make_columns_sortable'));
        
        // Add custom CSS for admin list
        add_action('admin_head', array($this, 'admin_list_styles'));
        
        // Add custom filtering options
        add_action('restrict_manage_posts', array($this, 'add_admin_filters'));
        
        // Handle custom sorting
        add_action('pre_get_posts', array($this, 'handle_custom_sorting'));
        
        // Add custom row actions
        add_filter('post_row_actions', array($this, 'modify_row_actions'), 10, 2);
    }
    
    /**
     * Add custom columns to property list
     */
    public function add_custom_columns($columns) {
        $new_columns = array();
        
        // Add checkbox and title first
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['ID'] = 'ID';
        
        // Add our custom columns
        $new_columns['property_image'] = '<span class="dashicons dashicons-format-image"></span>';
        $new_columns['property_details'] = __('Property Details', 'houses-theme');
        $new_columns['property_price'] = __('Price', 'houses-theme');
        $new_columns['property_specs'] = __('Specs', 'houses-theme');
        $new_columns['property_location'] = __('Location', 'houses-theme');
        $new_columns['property_availability'] = __('Availability', 'houses-theme');
        $new_columns['property_actions'] = __('Actions', 'houses-theme');
        
        return $new_columns;
    }
    
    /**
     * Populate custom columns with data
     */
    public function populate_custom_columns($column, $post_id) {
        switch ($column) {
            case 'ID':
                echo $post_id;
                break;
            case 'property_image':
                $this->display_property_image($post_id);
                break;
                
            case 'property_details':
                $this->display_property_details($post_id);
                break;
                
            case 'property_price':
                $this->display_property_price($post_id);
                break;
                
            case 'property_specs':
                $this->display_property_specs($post_id);
                break;
                
            case 'property_location':
                $this->display_property_location($post_id);
                break;
                
            case 'property_availability':
                $this->display_property_availability($post_id);
                break;
                
            case 'property_actions':
                $this->display_property_actions($post_id);
                break;
        }
    }
    
    /**
     * Display property image
     */
    private function display_property_image($post_id) {
        $gallery_images = get_post_meta($post_id, 'gallery_images', true);
        
        echo '<div class="property-thumbnail">';
        if (!empty($gallery_images) && is_array($gallery_images)) {
            $image_url = wp_get_attachment_image_url($gallery_images[0], 'thumbnail');
            if ($image_url) {
                echo '<img src="' . esc_url($image_url) . '" alt="Property Image">';
            } else {
                echo '<span class="no-image dashicons dashicons-format-image"></span>';
            }
        } else {
            echo '<span class="no-image dashicons dashicons-format-image"></span>';
        }
        echo '</div>';
    }
    
    /**
     * Display property details
     */
    private function display_property_details($post_id) {
        $title = get_the_title($post_id);
        $address = get_post_meta($post_id, 'address', true);
        $chinese_address = get_post_meta($post_id, 'chinese_address', true);
        
        echo '<div class="property-details-column">';
        echo '<strong><a href="' . get_edit_post_link($post_id) . '">' . esc_html($title) . '</a></strong>';
        
        if (!empty($address)) {
            echo '<div class="property-address">' . esc_html($address) . '</div>';
        }
        
        if (!empty($chinese_address)) {
            echo '<div class="property-chinese-address">' . esc_html($chinese_address) . '</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * Display property price
     */
    private function display_property_price($post_id) {
        $rent = get_post_meta($post_id, 'rent', true);
        
        echo '<div class="property-price-column">';
        if (!empty($rent)) {
            echo '<div class="property-price">NT$ ' . number_format($rent) . '</div>';
        } else {
            echo '<div class="property-price-empty">NT$ 0.00</div>';
        }
        echo '</div>';
    }
    
    /**
     * Display property specs
     */
    private function display_property_specs($post_id) {
        $bedrooms = get_post_meta($post_id, 'bedroom', true);
        $bathrooms = get_post_meta($post_id, 'bathroom', true);
        $size_sqm = get_post_meta($post_id, 'square_meters', true);
        
        echo '<div class="property-specs-column">';
        
        echo '<div class="property-specs-grid">';
        
        // Bedrooms
        echo '<div class="spec-item">';
        echo '<span class="dashicons dashicons-building"></span> ';
        echo !empty($bedrooms) ? esc_html($bedrooms) : '—';
        echo '</div>';
        
        // Bathrooms
        echo '<div class="spec-item">';
        echo '<span class="dashicons dashicons-admin-generic"></span> ';
        echo !empty($bathrooms) ? esc_html($bathrooms) : '—';
        echo '</div>';
        
        echo '</div>'; // End property-specs-grid
        
        echo '</div>'; // End property-specs-column
    }
    
    /**
     * Display property location
     */
    private function display_property_location($post_id) {
        $district_id = get_post_meta($post_id, 'district', true);
        $station_id = get_post_meta($post_id, 'station', true);
        
        $district_name = '';
        if (!empty($district_id)) {
            $district = get_post($district_id);
            if ($district) {
                $district_name = $district->post_title;
            }
        }
        
        $station_name = '';
        if (!empty($station_id)) {
            $station = get_post($station_id);
            if ($station) {
                $station_name = $station->post_title;
            }
        }
        
        echo '<div class="property-location-column">';
        
        if (!empty($district_name)) {
            echo '<div class="property-district">' . esc_html($district_name) . '</div>';
        }
        
        if (!empty($station_name)) {
            echo '<div class="property-station">' . esc_html($station_name) . '</div>';
        }
        
        if (empty($district_name) && empty($station_name)) {
            echo '<div class="property-location-empty">—</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * Display property availability
     */
    private function display_property_availability($post_id) {
        // For demo purposes, using current date
        $date = date('j M');
        $time = date('H:i');
        
        echo '<div class="property-availability-column">';
        echo '<div class="availability-status">Available</div>';
        echo '<div class="availability-date">' . esc_html($date) . '</div>';
        echo '<div class="availability-time">' . esc_html($time) . '</div>';
        echo '</div>';
    }
    
    /**
     * Display property actions
     */
    private function display_property_actions($post_id) {
        echo '<div class="property-actions-column">';
        
        // Message action
        echo '<a href="#" class="property-action message-action" title="Message">';
        echo '<span class="dashicons dashicons-email-alt"></span>';
        echo '</a>';
        
        // Calendar action
        echo '<a href="#" class="property-action calendar-action" title="Schedule">';
        echo '<span class="dashicons dashicons-calendar-alt"></span>';
        echo '</a>';
        
        // Cancel action
        echo '<a href="#" class="property-action cancel-action" title="Cancel">';
        echo '<span class="dashicons dashicons-no-alt"></span>';
        echo '</a>';
        
        // Favorite action
        echo '<a href="#" class="property-action favorite-action" title="Favorite">';
        echo '<span class="dashicons dashicons-star-empty"></span>';
        echo '</a>';
        
        echo '</div>';
    }
    
    /**
     * Make columns sortable
     */
    public function make_columns_sortable($columns) {
        $columns['property_price'] = 'property_price';
        $columns['property_specs'] = 'property_bedrooms';
        $columns['property_location'] = 'property_district';
        $columns['property_availability'] = 'property_availability';
        
        return $columns;
    }
    
    /**
     * Add admin list styles
     */
    public function admin_list_styles() {
        global $post_type;
        
        // Only apply to property list
        if ($post_type !== 'property') {
            return;
        }
        
        ?>
        <style type="text/css">
            /* Property list table styles */
            .wp-list-table .column-property_image {
                width: 80px;
            }
            
            .wp-list-table .column-property_details {
                width: 20%;
            }
            
            .wp-list-table .column-property_price {
                width: 10%;
            }
            
            .wp-list-table .column-property_specs {
                width: 10%;
            }
            
            .wp-list-table .column-property_location {
                width: 15%;
            }
            
            .wp-list-table .column-property_availability {
                width: 12%;
            }
            
            .wp-list-table .column-property_actions {
                width: 120px;
                text-align: center;
            }
            
            /* Property thumbnail */
            .property-thumbnail {
                width: 60px;
                height: 60px;
                overflow: hidden;
                border-radius: 4px;
                background-color: #f0f0f0;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .property-thumbnail img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            
            .property-thumbnail .no-image {
                color: #999;
                font-size: 24px;
            }
            
            /* Property details */
            .property-details-column {
                display: flex;
                flex-direction: column;
            }
            
            .property-address,
            .property-chinese-address {
                font-size: 12px;
                color: #666;
                margin-top: 3px;
                line-height: 1.3;
            }
            
            /* Property price */
            .property-price {
                font-weight: 600;
                color: #333;
            }
            
            .property-price-empty {
                color: #999;
            }
            
            /* Property specs */
            .property-specs-grid {
                display: flex;
                gap: 10px;
            }
            
            .spec-item {
                display: flex;
                align-items: center;
                color: #666;
            }
            
            .spec-item .dashicons {
                font-size: 16px;
                width: 16px;
                height: 16px;
                margin-right: 3px;
            }
            
            /* Property location */
            .property-district {
                font-weight: 600;
                margin-bottom: 3px;
            }
            
            .property-station {
                font-size: 12px;
                color: #666;
            }
            
            .property-location-empty {
                color: #999;
            }
            
            /* Property availability */
            .availability-status {
                color: #4CAF50;
                font-weight: 600;
                margin-bottom: 3px;
            }
            
            .availability-date,
            .availability-time {
                font-size: 12px;
                color: #666;
            }
            
            /* Property actions */
            .property-actions-column {
                display: flex;
                justify-content: center;
                gap: 5px;
            }
            
            .property-action {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 28px;
                height: 28px;
                border-radius: 50%;
                background-color: #f5f5f5;
                color: #666;
                text-decoration: none;
                transition: all 0.2s ease;
            }
            
            .property-action:hover {
                background-color: #e0e0e0;
            }
            
            .property-action .dashicons {
                font-size: 14px;
                width: 14px;
                height: 14px;
            }
            
            .message-action:hover {
                background-color: #0073aa;
                color: #fff;
            }
            
            .calendar-action:hover {
                background-color: #4CAF50;
                color: #fff;
            }
            
            .cancel-action:hover {
                background-color: #f44336;
                color: #fff;
            }
            
            .favorite-action:hover {
                background-color: #FFC107;
                color: #fff;
            }
            
            /* Responsive adjustments */
            @media screen and (max-width: 1200px) {
                .wp-list-table .column-property_actions {
                    width: 90px;
                }
                
                .property-actions-column {
                    flex-wrap: wrap;
                }
            }
        </style>
        <?php
    }
    
    /**
     * Add admin filters
     */
    public function add_admin_filters($post_type) {
        if ($post_type !== 'property') {
            return;
        }
        
        // Check if any filter is active
        $has_active_filters = isset($_GET['property_year']) || isset($_GET['property_month']) || 
                              isset($_GET['property_district']) || isset($_GET['property_bedrooms']) || 
                              isset($_GET['property_budget']);
        
        // Year and Month filters
        $this->render_date_filters();
        
        // District filter
        $districts = get_posts(array(
            'post_type' => 'district',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        
        if (!empty($districts)) {
            $selected_district = isset($_GET['property_district']) ? $_GET['property_district'] : '';
            
            echo '<select name="property_district">';
            echo '<option value="">' . __('All Districts', 'houses-theme') . '</option>';
            
            foreach ($districts as $district) {
                echo '<option value="' . esc_attr($district->ID) . '" ' . selected($selected_district, $district->ID, false) . '>' . esc_html($district->post_title) . '</option>';
            }
            
            echo '</select>';
        }
        
        // Bedroom filter
        $selected_bedrooms = isset($_GET['property_bedrooms']) ? $_GET['property_bedrooms'] : '';
        
        echo '<select name="property_bedrooms">';
        echo '<option value="">' . __('All Bedrooms', 'houses-theme') . '</option>';
        
        for ($i = 1; $i <= 5; $i++) {
            echo '<option value="' . esc_attr($i) . '" ' . selected($selected_bedrooms, $i, false) . '>' . $i . ' ' . _n('Bedroom', 'Bedrooms', $i, 'houses-theme') . '</option>';
        }
        
        echo '<option value="6" ' . selected($selected_bedrooms, '6', false) . '>6+ ' . __('Bedrooms', 'houses-theme') . '</option>';
        
        echo '</select>';
        
        // Rental Budget filter
        $this->render_budget_filter();
        
        // Add Clear Filters button at the far right if filters are active
        if ($has_active_filters) {
            $clear_url = remove_query_arg(array('property_year', 'property_month', 
                                          'property_district', 'property_bedrooms', 'property_budget'));
            echo '<div style="float: right; margin-left: 45px;">';
            echo '<a href="' . esc_url($clear_url) . '" class="button action">' . __('Clear Filters', 'houses-theme') . '</a>';
            echo '</div>';
        }
    }
    
    /**
     * Render year and month filters
     */
    private function render_date_filters() {
        global $wpdb;

        // Year filter
        $sql_years = "SELECT DISTINCT YEAR(post_date) AS year FROM $wpdb->posts WHERE post_type = 'property' ORDER BY year DESC";
        $years = $wpdb->get_col($sql_years);
        $current_year = isset($_GET['property_year']) ? $_GET['property_year'] : '';

        echo '<select name="property_year">';
        echo '<option value="">' . __('All Years', 'houses-theme') . '</option>';
        foreach ($years as $year) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr($year),
                selected($year, $current_year, false),
                esc_html($year)
            );
        }
        echo '</select>';

        // Month filter
        $current_month = isset($_GET['property_month']) ? $_GET['property_month'] : '';
        $months = array(
            '01' => __('January', 'houses-theme'),
            '02' => __('February', 'houses-theme'),
            '03' => __('March', 'houses-theme'),
            '04' => __('April', 'houses-theme'),
            '05' => __('May', 'houses-theme'),
            '06' => __('June', 'houses-theme'),
            '07' => __('July', 'houses-theme'),
            '08' => __('August', 'houses-theme'),
            '09' => __('September', 'houses-theme'),
            '10' => __('October', 'houses-theme'),
            '11' => __('November', 'houses-theme'),
            '12' => __('December', 'houses-theme')
        );

        echo '<select name="property_month">';
        echo '<option value="">' . __('All Months', 'houses-theme') . '</option>';
        foreach ($months as $value => $label) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr($value),
                selected($value, $current_month, false),
                esc_html($label)
            );
        }
        echo '</select>';
    }
    
    // Company filter removed as properties don't have company associations
    
    /**
     * Render budget filter
     */
    private function render_budget_filter() {
        // Define budget ranges
        $budget_ranges = array(
            '0-50000' => 'NT$0-50,000',
            '50001-100000' => 'NT$50,001-100,000',
            '100001-150000' => 'NT$100,001-150,000',
            '150001-200000' => 'NT$150,001-200,000',
            '200001-250000' => 'NT$200,001-250,000',
            '250001-300000' => 'NT$250,001-300,000',
            '300001-350000' => 'NT$300,001-350,000',
            '350001-400000' => 'NT$350,001-400,000',
            '400001-450000' => 'NT$400,001-450,000',
            '450001-500000' => 'NT$450,001-500,000',
            '500001-' => 'NT$500,001+'
        );
        
        $selected_budget = isset($_GET['property_budget']) ? $_GET['property_budget'] : '';
        
        echo '<select name="property_budget">';
        echo '<option value="">' . __('All Budgets', 'houses-theme') . '</option>';
        
        foreach ($budget_ranges as $value => $label) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr($value),
                selected($value, $selected_budget, false),
                esc_html($label)
            );
        }
        
        echo '</select>';
    }
    
    /**
     * Handle custom sorting
     */
    public function handle_custom_sorting($query) {
        if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'property') {
            return;
        }
        
        // Handle sorting
        $orderby = $query->get('orderby');
        
        switch ($orderby) {
            case 'property_price':
                $query->set('meta_key', 'rent');
                $query->set('orderby', 'meta_value_num');
                break;
                
            case 'property_bedrooms':
                $query->set('meta_key', 'bedroom');
                $query->set('orderby', 'meta_value_num');
                break;
                
            case 'property_district':
                $query->set('meta_key', 'district');
                $query->set('orderby', 'meta_value');
                break;
        }
        
        // Handle date filters (year and month)
        $year = isset($_GET['property_year']) ? $_GET['property_year'] : '';
        $month = isset($_GET['property_month']) ? $_GET['property_month'] : '';
        
        if (!empty($year)) {
            $query->set('year', $year);
        }
        
        if (!empty($month)) {
            $query->set('monthnum', $month);
        }
        
        // Handle meta filters
        $district = isset($_GET['property_district']) ? $_GET['property_district'] : '';
        $bedrooms = isset($_GET['property_bedrooms']) ? $_GET['property_bedrooms'] : '';
        $budget = isset($_GET['property_budget']) ? $_GET['property_budget'] : '';
        
        $meta_query = array();
        
        if (!empty($district)) {
            $meta_query[] = array(
                'key' => 'district',
                'value' => $district,
                'compare' => '='
            );
        }
        
        if (!empty($bedrooms)) {
            if ($bedrooms == '6') {
                $meta_query[] = array(
                    'key' => 'bedroom',
                    'value' => 5,
                    'compare' => '>',
                    'type' => 'NUMERIC'
                );
            } else {
                $meta_query[] = array(
                    'key' => 'bedroom',
                    'value' => $bedrooms,
                    'compare' => '=',
                    'type' => 'NUMERIC'
                );
            }
        }
        
        // Company filter removed as properties don't have company associations
        
        // Budget filter using optimized LIKE pattern matching for text fields
        if (!empty($budget)) {
            $budget_range = explode('-', $budget);
            
            if (count($budget_range) === 2) {
                $min = $budget_range[0];
                $max = $budget_range[1];
                
                // For budget ranges with both min and max
                if (!empty($min) && !empty($max)) {
                    $min_formatted = number_format((int)$min, 0, '.', ',');
                    $max_formatted = number_format((int)$max, 0, '.', ',');
                    
                    $meta_query[] = array(
                        'relation' => 'AND',
                        array(
                            'key' => 'rent',
                            'value' => (int)$min,
                            'compare' => '>=',
                            'type' => 'NUMERIC'
                        ),
                        array(
                            'key' => 'rent',
                            'value' => (int)$max,
                            'compare' => '<=',
                            'type' => 'NUMERIC'
                        )
                    );
                }
                // For budget >= X (e.g., 500001-)
                elseif (!empty($min) && empty($max)) {
                    $min_formatted = number_format((int)$min, 0, '.', ',');
                    
                    $meta_query[] = array(
                        'key' => 'rent',
                        'value' => (int)$min,
                        'compare' => '>=',
                        'type' => 'NUMERIC'
                    );
                }
            }
        }
        
        if (!empty($meta_query)) {
            $query->set('meta_query', $meta_query);
        }
    }
    
    /**
     * Modify row actions
     */
    public function modify_row_actions($actions, $post) {
        if ($post->post_type === 'property') {
            // Add custom actions
            $actions['view_property'] = '<a href="' . get_permalink($post->ID) . '" target="_blank">' . __('View Property', 'houses-theme') . '</a>';
        }
        
        return $actions;
    }
}

// Initialize the admin list customization
new Houses_Property_Admin_List();

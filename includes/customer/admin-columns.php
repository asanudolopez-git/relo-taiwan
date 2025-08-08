<?php
/**
 * Custom Admin Columns for Assignees
 *
 * @package Houses Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class for adding filters to the customer admin list
 */
class Houses_Customer_Admin_Filters
{

    public function __construct()
    {
        add_action('restrict_manage_posts', array($this, 'add_filters'));
        add_filter('parse_query', array($this, 'apply_filters'));
    }

    public function add_filters($post_type)
    {
        if ('customer' !== $post_type) {
            return;
        }

        // Check if any filter is active
        $has_active_filters = isset($_GET['customer_year']) || isset($_GET['customer_month']) ||
            isset($_GET['customer_company']) || isset($_GET['customer_budget']);

        // Year and Month filters
        $this->render_date_filters();

        // Company Name filter
        $this->render_company_filter();

        // Rental Budget filter
        $this->render_budget_filter();

        // Add Clear Filters button at the far right if filters are active
        if ($has_active_filters) {
            $clear_url = remove_query_arg(array('customer_year', 'customer_month', 'customer_company', 'customer_budget'));
            echo '<div style="float: right; margin-left: 45px;">';
            echo '<a href="' . esc_url($clear_url) . '" class="button action">' . __('Clear Filters', 'houses-theme') . '</a>';
            echo '</div>';
        }
    }

    private function render_date_filters()
    {
        global $wpdb;

        // Year filter
        $sql_years = "SELECT DISTINCT YEAR(post_date) AS year FROM $wpdb->posts WHERE post_type = 'customer' ORDER BY year DESC";
        $years = $wpdb->get_col($sql_years);
        $current_year = isset($_GET['customer_year']) ? $_GET['customer_year'] : '';

        echo '<select name="customer_year">';
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
        $current_month = isset($_GET['customer_month']) ? $_GET['customer_month'] : '';
        echo '<select name="customer_month">';
        echo '<option value="">' . __('All Months', 'houses-theme') . '</option>';
        for ($i = 1; $i <= 12; $i++) {
            $month_name = date('F', mktime(0, 0, 0, $i, 10));
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr($i),
                selected($i, $current_month, false),
                esc_html($month_name)
            );
        }
        echo '</select>';
    }

    private function render_company_filter()
    {
        $current_company = isset($_GET['customer_company']) ? (int) $_GET['customer_company'] : 0;

        $args = array(
            'post_type' => 'company',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'post_status' => 'publish',
        );

        $companies = get_posts($args);

        if (!empty($companies)) {
            echo '<select name="customer_company">';
            echo '<option value="">' . __('All Companies', 'houses-theme') . '</option>';
            foreach ($companies as $company) {
                printf(
                    '<option value="%d"%s>%s</option>',
                    $company->ID,
                    selected($company->ID, $current_company, false),
                    esc_html($company->post_title)
                );
            }
            echo '</select>';
        }
    }

    private function render_budget_filter()
    {
        $budget_ranges = array(
            '0-50000' => 'NT$0 - NT$50,000',
            '50001-100000' => 'NT$50,001 - NT$100,000',
            '100001-150000' => 'NT$100,001 - NT$150,000',
            '150001-200000' => 'NT$150,001 - NT$200,000',
            '200001-99999999' => 'NT$200,001+',
        );

        $current_budget = isset($_GET['customer_budget']) ? $_GET['customer_budget'] : '';

        echo '<select name="customer_budget">';
        echo '<option value="">' . __('Any Budget', 'houses-theme') . '</option>';
        foreach ($budget_ranges as $range => $label) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr($range),
                selected($range, $current_budget, false),
                esc_html($label)
            );
        }
        echo '</select>';
    }

    public function apply_filters($query)
    {
        global $pagenow;

        if (is_admin() && $pagenow == 'edit.php' && isset($query->query['post_type']) && $query->query['post_type'] == 'customer') {

            // Date filter
            $date_query = array();
            if (!empty($_GET['customer_year'])) {
                $date_query['year'] = $_GET['customer_year'];
            }
            if (!empty($_GET['customer_month'])) {
                $date_query['month'] = $_GET['customer_month'];
            }
            if (!empty($date_query)) {
                $query->set('date_query', $date_query);
            }

            $meta_query = (array) $query->get('meta_query');

            // Company filter
            if (!empty($_GET['customer_company'])) {
                $meta_query[] = array(
                    'key' => 'company_id',
                    'value' => sanitize_text_field($_GET['customer_company']),
                    'compare' => '=',
                );
            }

            // Budget filter - handling text-based budget field with better performance
            if (!empty($_GET['customer_budget'])) {
                $range = explode('-', $_GET['customer_budget']);
                $min = (int) $range[0];
                $max = (int) $range[1];

                // Use a custom meta_query to filter by budget range
                // We can't directly filter by numeric value since it's stored as text
                // Instead, we'll add multiple meta_query clauses to narrow down results

                // Start with a clause that requires the budget field to exist
                $meta_query[] = array(
                    'key' => 'budget',
                    'compare' => 'EXISTS',
                );

                // Flag to track if we've added any LIKE clauses
                $added_like_clauses = false;

                // Create an array of possible numeric patterns within the range
                // This approach is more efficient than loading all posts into memory
                $like_clauses = array('relation' => 'OR');

                // For each major digit position, add LIKE clauses
                // This helps filter by first few digits which are most significant
                if ($min <= 9999 && $max >= 1000) {
                    // Handle 1,000-9,999 range
                    for ($i = 1; $i <= 9; $i++) {
                        if ($i * 1000 >= $min && $i * 1000 <= $max) {
                            $like_clauses[] = array(
                                'key' => 'budget',
                                'value' => $i . ',',
                                'compare' => 'LIKE'
                            );
                            $added_like_clauses = true;
                        }
                    }
                }

                if ($min <= 99999 && $max >= 10000) {
                    // Handle 10,000-99,999 range
                    for ($i = 10; $i <= 99; $i++) {
                        if ($i * 1000 >= $min && $i * 1000 <= $max) {
                            $like_clauses[] = array(
                                'key' => 'budget',
                                'value' => $i . ',',
                                'compare' => 'LIKE'
                            );
                            $added_like_clauses = true;
                        }
                    }
                }

                if ($min <= 999999 && $max >= 100000) {
                    // Handle 100,000-999,999 range
                    for ($i = 100; $i <= 999; $i++) {
                        if ($i * 1000 >= $min && $i * 1000 <= $max) {
                            $like_clauses[] = array(
                                'key' => 'budget',
                                'value' => $i . ',',
                                'compare' => 'LIKE'
                            );
                            $added_like_clauses = true;
                        }
                    }
                }

                if ($min <= 9999999 && $max >= 1000000) {
                    // Handle 1,000,000+ range
                    $like_clauses[] = array(
                        'key' => 'budget',
                        'value' => '1,000,',
                        'compare' => 'LIKE'
                    );
                    $added_like_clauses = true;
                }

                // Add the LIKE clauses if we have any
                if ($added_like_clauses) {
                    $meta_query[] = $like_clauses;
                } else {
                    // Fallback if no LIKE clauses were added
                    // This might happen for very small ranges
                    $meta_query[] = array(
                        'key' => 'budget',
                        'value' => $min,
                        'compare' => 'LIKE'
                    );
                }
            }

            if (!empty($meta_query)) {
                $query->set('meta_query', $meta_query);
            }
        }
    }
}

// Initialize the filters
new Houses_Customer_Admin_Filters();


/**
 * Extend admin search to include External ID, first name, and last name meta fields
 */
function houses_extend_customer_search($query) {
    // Only apply to customer post type in admin
    if (is_admin() && $query->is_main_query() && $query->get('post_type') === 'customer') {
        // Only apply when a search is being performed
        if (!empty($query->get('s'))) {
            // WordPress already handles post_title search by default
            // We just need to extend it to include our custom meta fields
            // Use a custom WHERE clause to search meta fields
            add_filter('posts_where', 'houses_search_external_id_where', 10, 2);
        }
    }
}

/**
 * Modify the WHERE clause to include External ID, first name, and last name in search
 */
function houses_search_external_id_where($where, $wp_query) {
    global $wpdb;
    
    // Only apply to customer searches in admin
    if (is_admin() && $wp_query->is_main_query() && $wp_query->get('post_type') === 'customer' && !empty($wp_query->get('s'))) {
        $search_term = $wp_query->get('s');
        
        // Remove the filter to prevent infinite loops
        remove_filter('posts_where', 'houses_search_external_id_where', 10);
        
        // Add OR conditions to search in multiple meta fields
        $where .= $wpdb->prepare(
            " OR {$wpdb->posts}.ID IN (
                SELECT post_id FROM {$wpdb->postmeta} 
                WHERE (
                    (meta_key = 'external_id' AND meta_value LIKE %s) OR
                    (meta_key = 'first_name' AND meta_value LIKE %s) OR
                    (meta_key = 'last_name' AND meta_value LIKE %s)
                )
            )",
            '%' . $wpdb->esc_like($search_term) . '%',
            '%' . $wpdb->esc_like($search_term) . '%',
            '%' . $wpdb->esc_like($search_term) . '%'
        );
    }
    
    return $where;
}
add_action('pre_get_posts', 'houses_extend_customer_search');


/**
 * Add custom columns to the Assignee list
 */
function add_customer_columns($columns)
{
    // Create entirely new column structure
    $new_columns = array(
        'cb' => $columns['cb'], // Keep the checkbox
        'id_name' => __('ID: Name', 'houses-theme'),
        'external_id' => __('External ID', 'houses-theme'),
        'budget' => __('Budget', 'houses-theme'),
        'company' => __('Company', 'houses-theme'),
        'assignment_period' => __('Assignment Period', 'houses-theme'),
        'property_chosen' => __('Property Chosen', 'houses-theme'),
        'final_rental' => __('Final Rental', 'houses-theme'),
        'lease_end_date' => __('Lease End Date', 'houses-theme'),
        'date' => $columns['date'] // Keep the date column
    );

    return $new_columns;
}
add_filter('manage_customer_posts_columns', 'add_customer_columns');

/**
 * Display content for custom columns in the Assignee list
 */
function display_customer_columns($column, $post_id)
{
    switch ($column) {
        case 'id_name':
            // Display ID: Name format
            echo '<strong>' . $post_id . ':</strong> ' . get_the_title($post_id);
            break;

        case 'external_id':
            // Get external ID from meta field
            $external_id = get_post_meta($post_id, 'external_id', true);
            echo esc_html($external_id);
            break;

        case 'company':
            // Get company from meta field
            $company_id = get_post_meta($post_id, 'company_id', true);
            if (!empty($company_id)) {
                $company = get_post($company_id);
                if ($company) {
                    echo esc_html($company->post_title);
                }
            }
            break;

        case 'budget':
            // Get budget from meta field
            $budget = get_post_meta($post_id, 'budget', true);
            echo esc_html($budget);
            break;

        case 'assignment_period':
            // Get assignment period from meta fields
            $start_date = get_post_meta($post_id, 'assignment_period_start', true);
            $end_date = get_post_meta($post_id, 'assignment_period_end', true);
            if ($start_date || $end_date) {
                echo esc_html($start_date) . ' - ' . esc_html($end_date);
            }
            break;

        case 'property_chosen':
            // Get property information
            // We need to find if there's a lease associated with this customer
            $leases = get_posts(array(
                'post_type' => 'client_lease',
                'meta_query' => array(
                    array(
                        'key' => 'client_id',
                        'value' => $post_id,
                    ),
                ),
                'posts_per_page' => 1,
                'orderby' => 'date',
                'order' => 'DESC',
            ));


            if (!empty($leases)) {
                $lease = $leases[0];
                $property_id = get_post_meta($lease->ID, 'property_id_hidden', true);

                if (!empty($property_id)) {
                    $property = get_post($property_id);
                    if ($property) {
                        $address = get_post_meta($property_id, 'title', true);
                        echo esc_html($property->post_title);
                        if (!empty($address)) {
                            echo '<br><small>' . esc_html($address) . '</small>';
                        }
                    }
                }
            }
            break;

        case 'final_rental':
            // Get final rental from lease if available
            $leases = get_posts(array(
                'post_type' => 'client_lease',
                'meta_query' => array(
                    array(
                        'key' => 'client_id',
                        'value' => $post_id,
                    ),
                ),
                'posts_per_page' => 1,
                'orderby' => 'date',
                'order' => 'DESC',
            ));


            if (!empty($leases)) {
                $lease = $leases[0];
                $property_id = get_post_meta($lease->ID, 'property_id_hidden', true);

                if (!empty($property_id)) {
                    $property = get_post($property_id);
                    if ($property) {
                        $rent = get_post_meta($property_id, 'rent', true);
                        echo "NT$ " . esc_html($rent);
                    }
                }
            }
            break;

        case 'lease_end_date':
            // Get lease end date from associated lease
            $leases = get_posts(array(
                'post_type' => 'client_lease',
                'meta_query' => array(
                    array(
                        'key' => 'client_id',
                        'value' => $post_id,
                    ),
                ),
                'posts_per_page' => 1,
                'orderby' => 'date',
                'order' => 'DESC',
            ));

            if (!empty($leases)) {
                $lease = $leases[0];
                $end_date = get_post_meta($lease->ID, 'end_date', true);
                if (!empty($end_date)) {
                    echo esc_html($end_date);
                } else {
                    echo __('Not set', 'houses-theme');
                }
            } else {
                echo __('No lease', 'houses-theme');
            }
            break;
    }
}
add_action('manage_customer_posts_custom_column', 'display_customer_columns', 10, 2);

/**
 * Make custom columns sortable
 */
function make_customer_columns_sortable($columns)
{
    $columns['id_name'] = 'ID';
    $columns['company'] = 'company';
    $columns['budget'] = 'budget';
    $columns['assignment_period'] = 'assignment_period_start';
    return $columns;
}
add_filter('manage_edit-customer_sortable_columns', 'make_customer_columns_sortable');

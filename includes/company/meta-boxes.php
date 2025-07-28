<?php
/**
 * Company Meta Boxes
 */
class Houses_Company_Meta_Boxes {
    /**
     * Constructor
     */
    public function __construct() {
        // Add meta boxes
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'houses_company_assignees',
            __('Assignees under this Company', 'houses-theme'),
            array($this, 'render_assignees_meta_box'),
            'company',
            'normal',
            'high'
        );
    }

    /**
     * Render assignees meta box
     */
    public function render_assignees_meta_box($post) {
        $company_id = $post->ID;
        
        // Get all assignees (customers) that belong to this company
        // Primero intentamos con el campo ACF 'company'
        $assignees_acf = get_posts(array(
            'post_type' => 'customer',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'company',
                    'value' => $company_id,
                    'compare' => '='
                )
            ),
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        
        // Luego intentamos con el campo meta 'company_id'
        $assignees_meta = get_posts(array(
            'post_type' => 'customer',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'company_id',
                    'value' => $company_id,
                    'compare' => '='
                )
            ),
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        
        // Combinamos los resultados
        $assignees = array_merge($assignees_acf, $assignees_meta);
        
        // Eliminamos duplicados si los hay
        $unique_assignees = array();
        $seen_ids = array();
        
        foreach ($assignees as $assignee) {
            if (!in_array($assignee->ID, $seen_ids)) {
                $unique_assignees[] = $assignee;
                $seen_ids[] = $assignee->ID;
            }
        }
        
        $assignees = $unique_assignees;
        
        if (empty($assignees)) {
            echo '<p>' . __('No assignees found for this company.', 'houses-theme') . '</p>';
            return;
        }
        
        echo '<table class="widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . __('ID', 'houses-theme') . '</th>';
        echo '<th>' . __('Assignee Name', 'houses-theme') . '</th>';
        echo '<th>' . __('Actions', 'houses-theme') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($assignees as $assignee) {
            $assignee_id = get_post_meta($assignee->ID, 'customer_id', true);
            
            echo '<tr>';
            echo '<td>' . esc_html($assignee_id) . '</td>';
            echo '<td>' . esc_html($assignee->post_title) . '</td>';
            echo '<td>';
            echo '<a href="' . esc_url(get_edit_post_link($assignee->ID)) . '" class="button button-small">' . __('Edit', 'houses-theme') . '</a> ';
            echo '<a href="' . esc_url(get_permalink($assignee->ID)) . '" class="button button-small" target="_blank">' . __('View', 'houses-theme') . '</a>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        
        // Add a button to add a new assignee pre-filled with this company
        echo '<p class="submit">';
        echo '<a href="' . esc_url(admin_url('post-new.php?post_type=customer&company=' . $company_id)) . '" class="button button-primary">';
        echo __('Add New Assignee to this Company', 'houses-theme');
        echo '</a>';
        echo '</p>';
    }
}

// Initialize the meta boxes
new Houses_Company_Meta_Boxes();

// Handle pre-filling company when creating a new assignee
function houses_prefill_company_for_new_assignee() {
    global $pagenow;
    
    if ($pagenow === 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'customer' && isset($_GET['company'])) {
        $company_id = intval($_GET['company']);
        
        // Add script to set the company field value after the page loads
        add_action('admin_footer', function() use ($company_id) {
            ?>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Para el campo ACF
                if (typeof acf !== 'undefined') {
                    acf.add_action('ready', function() {
                        // Set the company field value
                        var companyField = acf.getField('field_company');
                        if (companyField) {
                            companyField.val(<?php echo $company_id; ?>);
                        }
                    });
                }
                
                // Para el campo normal de metabox
                $('#company_id').val(<?php echo $company_id; ?>);
            });
            </script>
            <?php
        });
    }
}
add_action('admin_init', 'houses_prefill_company_for_new_assignee');

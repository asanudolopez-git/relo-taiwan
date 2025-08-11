<?php
class Houses_Client_House_List_Meta_Boxes {
    private $fields = array(
        'customer_selection' => array(
            'title' => 'Assignee Selection',
            'fields' => array(
                'selected_customer' => array(
                    'label' => 'Select Assignee',
                    'type' => 'customer_select',
                    'class' => '',
                ),
            ),
        ),
        'map_link' => array(
            'title' => 'Map Link',
            'fields' => array(
                'map_link' => array(
                    'label' => 'Map Link',
                    'type' => 'text',
                    'class' => 'widefat',
                ),
            ),
        ),
        'matching_properties' => array(
            'title' => 'Matching Properties',
            'fields' => array(
                'property_list' => array(
                    'type' => 'property_list',
                ),
            ),
        ),
    );

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // AJAX handlers
        add_action('wp_ajax_get_matching_properties', array($this, 'ajax_get_matching_properties'));
        add_action('wp_ajax_get_stations_by_line', array($this, 'ajax_get_stations_by_line'));
    }

    public function add_meta_boxes() {
        add_meta_box(
            'houses_customer_house_list_meta_boxes',
            'Assignee House List Details',
            array($this, 'render_meta_boxes'),
            'customer-house-list',
            'normal',
            'high'
        );
    }

    public function render_meta_boxes($post) {
        wp_nonce_field('houses_customer_house_list_meta_boxes', 'houses_customer_house_list_meta_boxes_nonce');
        
        foreach ($this->fields as $section => $data) {
            echo '<div class="meta-box-section">';
            echo '<h3>' . esc_html($data['title']) . '</h3>';
            
            foreach ($data['fields'] as $field => $config) {
                $value = get_post_meta($post->ID, $field, true);
                
                switch ($config['type']) {
                    case 'customer_select':
                        $this->render_customer_select($field, $value);
                        break;
                    case 'property_list':
                        $this->render_property_list($post->ID, $value);
                        break;
                    case 'text':
                        $this->render_text_field($field, $config, $value);
                        break;
                    case 'metro_line_select':
                        $this->render_metro_line_select($field, $value);
                        break;
                    case 'metro_station_select':
                        $this->render_metro_station_select($field, $value, $post->ID);
                        break;
                }
            }
            
            echo '</div>';
        }
        // Agregar botón para generar PDF
        echo '<button type="button" class="button button-primary" id="generate-customer-house-list-pdf" data-postid="' . esc_attr($post->ID) . '">Generate PDF</button>';
        
        // Display generated PDFs section
        $this->render_generated_pdfs_section($post->ID);
    }

    private function render_customer_select($field, $value) {
        $customers = get_posts(array(
            'post_type' => 'customer',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));

        echo '<div class="meta-box-field">';
        echo '<label for="' . esc_attr($field) . '">Select Assignee</label>';
        echo '<select id="' . esc_attr($field) . '" name="' . esc_attr($field) . '" class="widefat">';
        echo '<option value="">Select a customer...</option>';
        
        foreach ($customers as $customer) {
            echo '<option value="' . esc_attr($customer->ID) . '" ' . selected($value, $customer->ID, false) . '>';
            echo esc_html($customer->post_title);
            echo '</option>';
        }
        
        echo '</select>';
        echo '</div>';
    }

    private function save_property_snapshot($list_id, $property_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'house_list_property_snapshots';

        // Verificar si ya existe un snapshot
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE list_id = %d AND property_id = %d",
            $list_id,
            $property_id
        ));

        if ($existing) {
            return; // Ya existe un snapshot
        }

        // Obtener todos los meta datos de la propiedad
        $meta_keys = array('price', 'property_type', 'mrt', 'property_id', 'layout', 
                          'address', 'gross_size', 'net_size', 'floor', 'elevator', 'parking');
        
        $property_data = array();
        $post = get_post($property_id);
        
        if ($post) {
            $property_data['post'] = array(
                'ID' => $post->ID,
                'post_title' => $post->post_title,
                'post_content' => $post->post_content,
                'post_status' => $post->post_status,
                'post_type' => $post->post_type,
            );

            foreach ($meta_keys as $key) {
                $property_data['meta'][$key] = get_post_meta($property_id, $key, true);
            }

            $wpdb->insert(
                $table_name,
                array(
                    'list_id' => $list_id,
                    'property_id' => $property_id,
                    'snapshot_data' => json_encode($property_data)
                ),
                array('%d', '%d', '%s')
            );
        }
    }

    private function get_property_data($list_id, $property_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'house_list_property_snapshots';

        // Intentar obtener el snapshot
        $snapshot = $wpdb->get_var($wpdb->prepare(
            "SELECT snapshot_data FROM $table_name WHERE list_id = %d AND property_id = %d",
            $list_id,
            $property_id
        ));

        if ($snapshot) {
            return json_decode($snapshot, true);
        }

        // Si no hay snapshot y la propiedad existe, retornar datos actuales
        $post = get_post($property_id);
        if ($post) {
            $property_data = array(
                'post' => array(
                    'ID' => $post->ID,
                    'post_title' => $post->post_title,
                    'post_content' => $post->post_content,
                    'post_status' => $post->post_status,
                    'post_type' => $post->post_type,
                ),
                'meta' => array()
            );

            // Obtener TODOS los metadatos del post sin filtrar
            $all_meta = get_post_meta($property_id);
            if (!empty($all_meta)) {
                foreach ($all_meta as $meta_key => $meta_values) {
                    // Los metadatos se almacenan en arrays, pero normalmente queremos el primer valor
                    $property_data['meta'][$meta_key] = isset($meta_values[0]) ? $meta_values[0] : '';
                }
            }

            return $property_data;
        }

        return null;
    }

    public function save_meta_boxes($post_id) {
        if (!isset($_POST['houses_customer_house_list_meta_boxes_nonce']) || 
            !wp_verify_nonce($_POST['houses_customer_house_list_meta_boxes_nonce'], 'houses_customer_house_list_meta_boxes')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save selected customer
        if (isset($_POST['selected_customer'])) {
            update_post_meta($post_id, 'selected_customer', sanitize_text_field($_POST['selected_customer']));
        }
        
        // Save map link
        if (isset($_POST['map_link'])) {
            update_post_meta($post_id, 'map_link', esc_url_raw($_POST['map_link']));
        }
        
        // Save metro line and station
        if (isset($_POST['metro_line'])) {
            update_post_meta($post_id, 'metro_line', sanitize_text_field($_POST['metro_line']));
        }
        
        if (isset($_POST['metro_station'])) {
            update_post_meta($post_id, 'metro_station', sanitize_text_field($_POST['metro_station']));
        }

        // Save selected properties and create snapshots
        $old_properties = get_post_meta($post_id, 'property_list', true);
        $old_properties = !empty($old_properties) ? (array)$old_properties : array();
        
        $new_properties = isset($_POST['property_list']) ? array_map('absint', $_POST['property_list']) : array();
        
        // Crear snapshots para las nuevas propiedades seleccionadas
        foreach ($new_properties as $property_id) {
            $this->save_property_snapshot($post_id, $property_id);
        }

        update_post_meta($post_id, 'property_list', $new_properties);
    }

    private function render_property_list($post_id, $value) {
        $customer_id = get_post_meta($post_id, 'selected_customer', true);
        
        if (!$customer_id) {
            echo '<p>Please select a customer first to see properties.</p>';
            return;
        }

        // Get customer requirements for highlighting matches
        $property_types = (array) get_post_meta($customer_id, 'property_type', true);
        $mrt_lines = (array) get_post_meta($customer_id, 'preferred_mrt_line', true);
        
        // Query ALL properties
        $args = array(
            'post_type' => 'property',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        );

        $all_properties = get_posts($args);
        $selected_properties = !empty($value) ? (array)$value : array();

        // Obtener propiedades seleccionadas que ya no existen
        $deleted_properties = array();
        foreach ($selected_properties as $property_id) {
            if (!get_post($property_id)) {
                $snapshot_data = $this->get_property_data($post_id, $property_id);
                if ($snapshot_data) {
                    $deleted_properties[] = $snapshot_data;
                }
            }
        }

        if (empty($all_properties) && empty($deleted_properties)) {
            echo '<p>No properties available.</p>';
            return;
        }
        
        // Get all property types and MRT lines for filters
        $all_property_types = array();
        $all_mrt_lines = array();
        foreach ($all_properties as $property) {
            $type = get_post_meta($property->ID, 'property_type', true);
            $mrt = get_post_meta($property->ID, 'mrt', true);
            
            if (!empty($type) && !in_array($type, $all_property_types)) {
                $all_property_types[] = $type;
            }
            
            if (!empty($mrt) && !in_array($mrt, $all_mrt_lines)) {
                $all_mrt_lines[] = $mrt;
            }
        }
        
        // Sort filters alphabetically
        sort($all_property_types);
        sort($all_mrt_lines);
        
        // Add search and filter controls
        echo '<div class="property-search-filters">';
        echo '<h3>Search and Filter Properties</h3>';
        echo '<div class="search-filter-row">';
        
        // Search input
        echo '<div class="search-filter-field">';
        echo '<label for="property-search-input">Search:</label>';
        echo '<input type="text" id="property-search-input" placeholder="Search by name, address, or ID...">';
        echo '</div>';
        
        // Property type filter
        echo '<div class="search-filter-field">';
        echo '<label for="property-type-filter">Property Type:</label>';
        echo '<select id="property-type-filter">';
        echo '<option value="">All Types</option>';
        foreach ($all_property_types as $type) {
            echo '<option value="' . esc_attr($type) . '">' . esc_html(ucfirst($type)) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        // MRT line filter
        echo '<div class="search-filter-field">';
        echo '<label for="mrt-line-filter">MRT Line:</label>';
        echo '<select id="mrt-line-filter">';
        echo '<option value="">All Lines</option>';
        foreach ($all_mrt_lines as $line) {
            echo '<option value="' . esc_attr($line) . '">' . esc_html(str_replace('_', ' ', ucfirst($line))) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        echo '</div>'; // End search-filter-row
        
        // Action buttons
        echo '<div class="property-action-buttons">';
        echo '<a href="#" id="clear-property-filters" class="button">Clear Filters</a>';
        echo '<a href="#" id="select-all-properties" class="button">Select All Visible</a>';
        echo '<a href="#" id="deselect-all-properties" class="button">Deselect All</a>';
        echo '<a href="#" id="show-selected-properties" class="button">Show Selected Only</a>';
        echo '<a href="#" id="show-all-properties" class="button">Show All Properties</a>';
        echo '</div>';
        
        // Property count
        echo '<div id="property-count" class="property-count"></div>';
        
        echo '</div>'; // End property-search-filters

        echo '<div class="all-properties-list">';
        
        // Mostrar propiedades activas
        foreach ($all_properties as $property) {
            $this->render_property_item($property, $selected_properties, $property_types, $mrt_lines);
        }

        // Mostrar propiedades eliminadas
        if (!empty($deleted_properties)) {
            echo '<div class="deleted-properties-section">';
            echo '<h3>Previously Selected Properties (No Longer Available)</h3>';
            foreach ($deleted_properties as $property_data) {
                $this->render_deleted_property_item($property_data, $selected_properties);
            }
            echo '</div>';
        }

        echo '</div>';
    }

    private function render_property_item($property, $selected_properties, $property_types, $mrt_lines) {
        $checked = in_array($property->ID, $selected_properties) ? 'checked' : '';
        
        // Get property meta
        $meta = array(
            'price' => get_post_meta($property->ID, 'price', true),
            'property_type' => get_post_meta($property->ID, 'property_type', true),
            'mrt' => get_post_meta($property->ID, 'mrt', true),
            'property_id' => get_post_meta($property->ID, 'property_id', true),
            'layout' => get_post_meta($property->ID, 'layout', true),
            'address' => get_post_meta($property->ID, 'address', true),
            'gross_size' => get_post_meta($property->ID, 'gross_size', true),
            'net_size' => get_post_meta($property->ID, 'net_size', true),
            'floor' => get_post_meta($property->ID, 'floor', true),
            'elevator' => get_post_meta($property->ID, 'elevator', true),
            'parking' => get_post_meta($property->ID, 'parking', true)
        );

        $this->render_property_html($property->ID, $property->post_title, $meta, $checked, false, $property_types, $mrt_lines);
    }

    private function render_deleted_property_item($property_data, $selected_properties) {
        $checked = in_array($property_data['post']['ID'], $selected_properties) ? 'checked' : '';
        
        $this->render_property_html(
            $property_data['post']['ID'],
            $property_data['post']['post_title'],
            $property_data['meta'],
            $checked,
            true,
            array(),
            array()
        );
    }
    
    private function render_text_field($field, $config, $value) {
        echo '<div class="meta-box-field">';
        if (!empty($config['label'])) {
            echo '<label for="' . esc_attr($field) . '">' . esc_html($config['label']) . '</label>';
        }
        echo '<input type="text" id="' . esc_attr($field) . '" name="' . esc_attr($field) . '" value="' . esc_attr($value) . '" class="' . esc_attr($config['class']) . '" placeholder="Enter map URL here">';
        echo '</div>';
    }
    
    private function render_metro_line_select($field, $value) {
        // Lines options
        $lines = array(
            '' => 'Select a line',
            'BR' => 'Brown Line (Wenhu Line)',
            'R' => 'Red Line (Tamsui-Xinyi Line)',
            'G' => 'Green Line (Songshan-Xindian Line)',
            'O' => 'Orange Line (Zhonghe-Xinlu Line)',
            'BL' => 'Blue Line (Bannan Line)',
            'Y' => 'Yellow Line (Circular Line)'
        );
        
        echo '<div class="meta-box-field">';
        echo '<label for="' . esc_attr($field) . '">Metro Line</label>';
        echo '<select id="' . esc_attr($field) . '" name="' . esc_attr($field) . '" class="widefat metro-line-select">';
        
        foreach ($lines as $code => $name) {
            echo '<option value="' . esc_attr($code) . '"' . selected($value, $code, false) . '>' . esc_html($name) . '</option>';
        }
        
        echo '</select>';
        echo '</div>';
    }
    
    private function render_metro_station_select($field, $value, $post_id) {
        $selected_line = get_post_meta($post_id, 'metro_line', true);
        
        echo '<div class="meta-box-field">';
        echo '<label for="' . esc_attr($field) . '">Metro Station</label>';
        echo '<select id="' . esc_attr($field) . '" name="' . esc_attr($field) . '" class="widefat metro-station-select">';
        echo '<option value="">Select a station</option>';
        
        if ($selected_line) {
            // Get stations for the selected line
            $args = array(
                'post_type' => 'station',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC',
                'meta_query' => array(
                    array(
                        'key' => 'station_line',
                        'value' => $selected_line,
                        'compare' => '='
                    )
                )
            );
            
            $stations = get_posts($args);
            
            foreach ($stations as $station) {
                $station_code = get_post_meta($station->ID, 'station_code', true);
                $display_name = $station_code ? $station_code . ' - ' . $station->post_title : $station->post_title;
                
                echo '<option value="' . esc_attr($station->ID) . '"' . selected($value, $station->ID, false) . '>';
                echo esc_html($display_name);
                echo '</option>';
            }
        }
        
        echo '</select>';
        echo '</div>';
    }

    private function render_property_html($id, $title, $meta, $checked, $is_deleted = false, $property_types = array(), $mrt_lines = array()) {
        $deleted_class = $is_deleted ? ' deleted-property' : '';
        
        // Asegurar que los valores de meta existan y sean válidos
        $property_id = isset($meta['property_id']) ? $meta['property_id'] : '';
        $property_type = isset($meta['property_type']) ? $meta['property_type'] : '';
        $mrt = isset($meta['mrt']) ? $meta['mrt'] : '';
        $address = isset($meta['address']) ? $meta['address'] : '';
        $price = isset($meta['price']) ? $meta['price'] : '';
        
        // Add data attributes for JavaScript filtering
        $data_attrs = '';
        $data_attrs .= ' data-property-id="' . esc_attr($property_id) . '"';
        $data_attrs .= ' data-property-type="' . esc_attr($property_type) . '"';
        $data_attrs .= ' data-mrt-line="' . esc_attr($mrt) . '"';
        $data_attrs .= ' data-address="' . esc_attr($address) . '"';
        $data_attrs .= ' data-price="' . esc_attr($price) . '"';
        
        echo '<div class="property-item' . $deleted_class . '"' . $data_attrs . '>';
        echo '<div class="property-header">';
        echo '<label class="property-title">';
        echo '<input type="checkbox" name="property_list[]" value="' . esc_attr($id) . '" ' . $checked . '>';
        echo '<span class="title-text">' . esc_html($title);
        if ($is_deleted) {
            echo ' <span class="deleted-label">(Deleted)</span>';
        }
        echo '</span>';

        // Mostrar tags de coincidencia solo para propiedades activas
        if (!$is_deleted) {
            $matches = array();
            if (in_array($meta['property_type'], $property_types)) {
                $matches[] = 'Property Type';
            }
            if (in_array($meta['mrt'], $mrt_lines)) {
                $matches[] = 'MRT Line';
            }
            
            if (!empty($matches)) {
                echo '<span class="match-tags">';
                foreach ($matches as $match) {
                    echo '<span class="match-tag">' . esc_html($match) . ' Match</span>';
                }
                echo '</span>';
            }
        }

        echo '</label>';
        echo '<a href="#" class="toggle-details button" data-property-id="' . esc_attr($id) . '">More Info</a>';
        echo '</div>';
        
        echo '<div class="property-details" id="property-details-' . esc_attr($id) . '" style="display: none;">';
        
        // Mostrar todos los metadatos ordenados alfabéticamente para que sea más fácil localizarlos
        $sorted_meta = $meta;
        ksort($sorted_meta);
        
        echo '<div class="all-property-fields">';
        echo '<table class="property-info-table full-width">';
        
        // Encabezados de la tabla
        echo '<thead>';
        echo '<tr>';
        echo '<th width="30%">Campo</th>';
        echo '<th width="70%">Valor</th>';
        echo '</tr>';
        echo '</thead>';
        
        echo '<tbody>';
        foreach ($sorted_meta as $key => $value) {
            // Formatear el nombre del campo para mejor legibilidad
            $field_name = str_replace('_', ' ', $key);
            $field_name = ucwords($field_name);
            
            // Formatear el valor según su tipo
            $display_value = $value;
            
            // Si es un array o un objeto, convertirlo a JSON para visualización
            if (is_array($value) || is_object($value)) {
                $display_value = '<pre>' . json_encode($value, JSON_PRETTY_PRINT) . '</pre>';
            }
            // Si es un ID de imagen o galería, mostrar las imágenes
            elseif ($key === 'gallery_images' && !empty($value)) {
                $gallery_images = explode(',', $value);
                $display_value = $value . '<div class="property-gallery">';
                
                foreach ($gallery_images as $image_id) {
                    if (!empty($image_id)) {
                        $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                        if ($image_url) {
                            $display_value .= '<div class="gallery-thumbnail"><img src="' . esc_url($image_url) . '" alt="Property Image"></div>';
                        }
                    }
                }
                
                $display_value .= '</div>';
            }
            // Si es una referencia a un post (como agente, estación, etc.), mostrar el título del post
            elseif (($key === 'agent_id' || $key === 'station') && !empty($value)) {
                $referenced_post = get_post($value);
                if ($referenced_post) {
                    $display_value = $value . ' (' . $referenced_post->post_title . ')';
                }
            }
            // Formatear valores booleanos para mejor legibilidad
            elseif ($value === '1' || $value === 'yes' || $value === 'true') {
                $display_value = '<span class="value-true">Sí</span>';
            }
            elseif ($value === '0' || $value === 'no' || $value === 'false') {
                $display_value = '<span class="value-false">No</span>';
            }
            
            echo '<tr>';
            echo '<th>' . esc_html($field_name) . '</th>';
            echo '<td>' . (is_string($display_value) ? wp_kses_post($display_value) : esc_html(print_r($display_value, true))) . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        
        echo '</div>'; // End property-details
        echo '</div>'; // End property-item
    }

    public function enqueue_scripts($hook) {
        global $post;
        
        if ($hook == 'post-new.php' || $hook == 'post.php') {
            if (isset($post) && $post->post_type === 'customer-house-list') {
                wp_enqueue_style('houses-customer-house-list-admin', get_template_directory_uri() . '/includes/customer-house-list/assets/css/admin.css');
                wp_enqueue_script('houses-customer-house-list-admin', get_template_directory_uri() . '/includes/customer-house-list/assets/js/admin.js', array('jquery'), '1.0.0', true);
                
                wp_localize_script('houses-customer-house-list-admin', 'houses_admin', array(
                    'nonce' => wp_create_nonce('houses_customer_house_list_ajax')
                ));
            }
        }
    }
    
    /**
     * AJAX handler to get stations by line
     */
    public function ajax_get_stations_by_line() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'houses_customer_house_list_ajax')) {
            wp_send_json_error('Invalid nonce');
        }
        
        // Check line code
        if (!isset($_POST['line_code']) || empty($_POST['line_code'])) {
            wp_send_json_error('No line code provided');
        }
        
        $line_code = sanitize_text_field($_POST['line_code']);
        
        // Get stations for the selected line
        $args = array(
            'post_type' => 'station',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => 'station_line',
                    'value' => $line_code,
                    'compare' => '='
                )
            )
        );
        
        $stations = get_posts($args);
        $result = array();
        
        foreach ($stations as $station) {
            $station_code = get_post_meta($station->ID, 'station_code', true);
            
            $result[] = array(
                'id' => $station->ID,
                'name' => $station->post_title,
                'code' => $station_code
            );
        }
        
        wp_send_json_success($result);
    }
    
    public function enqueue_scripts_pdf($hook) {
        global $post;
        
        if (!$post || $post->post_type !== 'customer-house-list') {
            return;
        }
        
        wp_enqueue_style(
            'houses-customer-house-list',
            get_template_directory_uri() . '/includes/customer-house-list/assets/css/admin.css',
            array(),
            '1.0.0'
            );

        wp_enqueue_script(
            'houses-customer-house-list',
            get_template_directory_uri() . '/includes/customer-house-list/assets/js/admin.js',
            array('jquery'),
            time(), // Force cache refresh
            true
        );

        wp_enqueue_script(
            'houses-customer-house-list-pdf',
            get_template_directory_uri() . '/includes/customer-house-list/pdf-export.js',
            array('jquery'),
            null,
            true
        );
        wp_localize_script('houses-customer-house-list-pdf', 'ClientHouseListPDF', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('customer_house_list_pdf_nonce'),
        ));
    }
    
    private function render_generated_pdfs_section($post_id) {
        $generated_pdfs = get_post_meta($post_id, '_generated_pdfs', true);
        
        echo '<div style="margin-top: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;">';
        echo '<h3 style="margin-top: 0; color: #23282d; font-size: 18px; border-bottom: 2px solid #0073aa; padding-bottom: 10px; margin-bottom: 20px;"><span class="dashicons dashicons-media-document" style="margin-right: 8px;"></span>Generated PDFs</h3>';
        
        if (!is_array($generated_pdfs) || empty($generated_pdfs)) {
            echo '<div style="text-align: center; padding: 40px; color: #666;">';
            echo '<span class="dashicons dashicons-pdf" style="font-size: 48px; color: #ccc; display: block; margin-bottom: 15px;"></span>';
            echo '<p style="font-size: 16px; margin: 0;">No PDFs have been generated yet.</p>';
            echo '<p style="color: #999; margin: 5px 0 0 0;">Click the "Generate PDF" buttons above to create your first PDF.</p>';
            echo '</div>';
            echo '</div>';
            return;
        }
        
        // Sort PDFs by date (newest first)
        usort($generated_pdfs, function($a, $b) {
            return strtotime($b['date_created']) - strtotime($a['date_created']);
        });
        
        echo '<div style="background: white; border-radius: 3px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
        echo '<table class="wp-list-table widefat fixed striped" style="margin: 0; border: none;">';
        echo '<thead style="background: #f1f1f1;">';
        echo '<tr>';
        echo '<th style="width: 15%; padding: 12px 15px; font-weight: 600; color: #23282d;"><span class="dashicons dashicons-format-image" style="margin-right: 5px;"></span>Type</th>';
        echo '<th style="width: 25%; padding: 12px 15px; font-weight: 600; color: #23282d;"><span class="dashicons dashicons-calendar-alt" style="margin-right: 5px;"></span>Date Created</th>';
        echo '<th style="width: 40%; padding: 12px 15px; font-weight: 600; color: #23282d;"><span class="dashicons dashicons-media-document" style="margin-right: 5px;"></span>Filename</th>';
        echo '<th style="width: 20%; padding: 12px 15px; font-weight: 600; color: #23282d; text-align: center;"><span class="dashicons dashicons-download" style="margin-right: 5px;"></span>Action</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($generated_pdfs as $index => $pdf) {
            $type_display = ($pdf['type'] === 'with_images') ? 'With Images' : 'No Images';
            $type_icon = ($pdf['type'] === 'with_images') ? 'dashicons-format-gallery' : 'dashicons-format-aside';
            $type_color = ($pdf['type'] === 'with_images') ? '#0073aa' : '#00a32a';
            $date_display = date('M d, Y', strtotime($pdf['date_created']));
            $time_display = date('H:i', strtotime($pdf['date_created']));
            
            $row_bg = ($index % 2 === 0) ? '#ffffff' : '#f9f9f9';
            
            echo '<tr style="background: ' . $row_bg . ';">';
            echo '<td style="padding: 15px; vertical-align: middle;">';
            echo '<span class="dashicons ' . $type_icon . '" style="color: ' . $type_color . '; margin-right: 8px;"></span>';
            echo '<strong style="color: ' . $type_color . ';">' . esc_html($type_display) . '</strong>';
            echo '</td>';
            echo '<td style="padding: 15px; vertical-align: middle;">';
            echo '<div style="line-height: 1.4;">';
            echo '<strong class="pdf-local-date" data-utc="' . esc_attr($pdf['date_created']) . '">' . esc_html($date_display) . '</strong><br>';
            echo '<small class="pdf-local-time" data-utc="' . esc_attr($pdf['date_created']) . '" style="color: #666;">' . esc_html($time_display) . '</small>';
            echo '</div>';
            echo '</td>';
            echo '<td style="padding: 15px; vertical-align: middle;">';
            echo '<code style="background: #f1f1f1; padding: 4px 8px; border-radius: 3px; font-size: 12px; color: #333;">' . esc_html($pdf['filename']) . '</code>';
            echo '</td>';
            echo '<td style="padding: 15px; vertical-align: middle; text-align: center;">';
            echo '<a href="' . esc_url($pdf['file_url']) . '" class="button button-primary button-small" target="_blank" download style="text-decoration: none;">';
            echo '<span class="dashicons dashicons-download" style="font-size: 14px; margin-right: 5px; vertical-align: middle;"></span>Download';
            echo '</a>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        
        // Add inline JavaScript to convert UTC times to local
        echo '<script type="text/javascript">
        jQuery(document).ready(function($) {
            // Helper function to pad numbers
            function pad(n) {
                return (n < 10 ? "0" : "") + n;
            }
            
            // Convert UTC dates to local time
            function convertToLocal() {
                $(".pdf-local-date").each(function() {
                    var utcStr = $(this).attr("data-utc");
                    if (utcStr && utcStr.length > 0) {
                        // Parse the UTC date string (format: YYYY-MM-DD HH:MM:SS)
                        // Convert to ISO format and add Z for UTC
                        var isoStr = utcStr.replace(" ", "T") + "Z";
                        var utcDate = new Date(isoStr);
                        
                        if (!isNaN(utcDate.getTime())) {
                            // Format date as "MMM DD, YYYY"
                            var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", 
                                         "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                            var localDateStr = months[utcDate.getMonth()] + " " + 
                                             pad(utcDate.getDate()) + ", " + 
                                             utcDate.getFullYear();
                            $(this).text(localDateStr);
                        }
                    }
                });
                
                $(".pdf-local-time").each(function() {
                    var utcStr = $(this).attr("data-utc");
                    if (utcStr && utcStr.length > 0) {
                        // Parse the UTC date string (format: YYYY-MM-DD HH:MM:SS)
                        // Convert to ISO format and add Z for UTC
                        var isoStr = utcStr.replace(" ", "T") + "Z";
                        var utcDate = new Date(isoStr);
                        
                        if (!isNaN(utcDate.getTime())) {
                            // Format time as "HH:MM" in local timezone
                            var localTimeStr = pad(utcDate.getHours()) + ":" + 
                                             pad(utcDate.getMinutes());
                            $(this).text(localTimeStr);
                        }
                    }
                });
            }
            
            // Run conversion
            convertToLocal();
        });
        </script>';
        
        // Add summary info
        $total_pdfs = count($generated_pdfs);
        $with_images = count(array_filter($generated_pdfs, function($pdf) { return $pdf['type'] === 'with_images'; }));
        $no_images = $total_pdfs - $with_images;
        
        echo '<div style="margin-top: 15px; padding: 10px; background: #e7f3ff; border-left: 4px solid #0073aa; font-size: 13px; color: #0073aa;">';
        echo '<strong>Summary:</strong> ' . $total_pdfs . ' PDF' . ($total_pdfs !== 1 ? 's' : '') . ' generated';
        if ($with_images > 0) echo ' • ' . $with_images . ' with images';
        if ($no_images > 0) echo ' • ' . $no_images . ' without images';
        echo '</div>';
        
        echo '</div>';
    }
}

new Houses_Client_House_List_Meta_Boxes();

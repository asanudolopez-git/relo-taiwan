<?php

// AJAX handler para generar PDF de Customer House List
add_action('wp_ajax_generate_customer_house_list_pdf', 'generate_customer_house_list_pdf');
function generate_customer_house_list_pdf() {
    if (!current_user_can('edit_posts')) {
        wp_die('No tienes permisos.');
    }
    // if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'customer_house_list_pdf_nonce')) {
    //    wp_die('Nonce inválido.');
    // }
    $post_id = intval($_GET['post_id']);
    if (!$post_id) {
        wp_die('ID de post inválido');
    }
    // Cargar autoload de Composer correctamente
    $autoload_path = get_template_directory() . '/vendor/autoload.php';
    if (!file_exists($autoload_path)) {
        wp_die('No se encontró autoload de Composer en: ' . $autoload_path);
    }
    require_once $autoload_path;
    $customer = get_post_meta($post_id, 'selected_customer', true);
    $property_list = get_post_meta($post_id, 'property_list', true);
    $property_list = !empty($property_list) ? (array)$property_list : array();
    
    // Get customer/assignee information
    $client_id = get_post_meta($post_id, 'selected_customer', true);
    $assignee_title = get_the_title($client_id);
    
    // Get company information
    $company_id = get_post_meta($client_id, 'company', true);
    if (empty($company_id)) {
        $company_id = get_post_meta($client_id, 'company_id', true);
    }
    
    if (!empty($company_id)) {
        $company_name = get_the_title($company_id);
    } else {
        $company_name = ''; // No company assigned
    }
    $today = date('F d, Y');
    $logo_path = get_template_directory() . '/assets/relo-logo.png';
    if (file_exists($logo_path)) {
        $logo_data = base64_encode(file_get_contents($logo_path));
        $logo_src = 'data:image/png;base64,' . $logo_data;
    } else {
        $logo_src = get_template_directory_uri() . '/assets/relo-logo.png';
    }
    // HTML para portada y propiedades
    ob_start();
    ?>
    <html><body><div style="text-align:left;padding:0 20px;"><img src="<?= $logo_src; ?>" style="height:60px;" /></div>
    <div style="width:100%;padding:30px 0 0 0;text-align:center;">
        <h1 style="background:#c81018;color:#fff;padding:20px 40px;border-radius:25px;display:inline-block;margin-top:60px;font-size:40px;"><span style="vertical-align:middle;">🏠</span> Home Search: Taipei</h1>
    </div>
    <div style="margin-top:80px;font-size:30px;">
        <b>Company:</b> <?= esc_html($company_name) ?><br>
        <b>Assignee:</b> <?= esc_html($assignee_title) ?><br>
        <b>Date:</b> <?= esc_html($today) ?><br>
    </div>
    <hr style="margin:40px 0 0 0;border:1px solid #c81018;">
    </body></html>
    <?php
    $cover = ob_get_clean();
    try {
        // Set up writable temp directory for mPDF
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/mpdf-temp';
        
        // Create temp directory if it doesn't exist
        if (!file_exists($temp_dir)) {
            wp_mkdir_p($temp_dir);
        }
        
        // Configuración mejorada para mPDF
        $mpdf = new \Mpdf\Mpdf([
            'tempDir' => $temp_dir,
            'orientation' => 'L',
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 5,
            'margin_bottom' => 5,
            'default_font' => 'sun-exta',
        ]);
        $mpdf->SetFont('sun-exta');
        
        // Escribir la portada
        $mpdf->WriteHTML($cover);
        
        // Enfoque completamente nuevo: reconstruir el HTML de las propiedades
        // Esto evita problemas con la estructura HTML existente
        $property_count = 0;
        
        foreach ($property_list as $i => $property_id) {
            $fields = array();
            $meta_fields = [
                'property_id', 'price', 'rent', 'agent_id', 'district', 'station', 'mrt', 'layout', 'address', 
                'chinese_address', 'bedroom', 'bathroom', 'floor', 'total_floor', 'building_age', 'gross_size', 'net_size', 
                'square_meters', 'ping', 'property_type', 'elevator', 'gym', 'swimming_pool', 
                'concierge', 'maids_room', 'garbage', 'open_kitchen', 'short_term_rental', 'legal_use', 
                'bathtub', 'notes', 'features', 'latitude', 'longitude', 'parking', 'rent_negotiable',
            ];
            
            foreach ($meta_fields as $field) {
                $fields[$field] = get_post_meta($property_id, $field, true);
            }
            
            $gallery_images = get_post_meta($property_id, 'gallery_images', true);
            if (!is_array($gallery_images)) {
                $gallery_images = !empty($gallery_images) ? explode(',', $gallery_images) : [];
            }
            
            // Agregar páginas para todas las propiedades
            $property_count++;
            $mpdf->AddPage('L');
            
            // Construir el HTML directamente
            $property_html = '<div style="font-size:12px; font-family:sans-serif;">';

            // Logo and "Rent can be negotiated" tag
            $property_html .= '<table style="width:100%; border-collapse:collapse; border:none; margin-bottom:5px;"><tr>';
            $property_html .= '<td style="width:50%; text-align:left;"><img src="'. $logo_src .'" style="height:45px;" /></td>';
            if (!empty($fields['rent_negotiable']) && strtolower($fields['rent_negotiable']) === 'yes') {
                $property_html .= '<td style="width:50%; text-align:right; vertical-align:bottom;"><span style="background:yellow;color:#000;font-size:14px;padding:4px 8px;border:1px solid #000;">Rent can be negotiated</span></td>';
            } else {
                $property_html .= '<td style="width:50%;"></td>';
            }
            $property_html .= '</tr></table>';

            // Main property table
            $property_html .= '<table style="width:100%; border-collapse:collapse; border:2px solid #000;">';

            // First row with main info
            $property_html .= '<tr>';
            // No. cell (rowspan=2)
            $property_html .= '<td rowspan="2" style="width:10%; border:1px solid #000; text-align:center; vertical-align:middle; font-size:22px; font-weight:bold;">No.' . ($i + 1) . '</td>';
            // Title and address cell
            $property_html .= '<td style="border:1px solid #000; text-align:center; vertical-align:middle; padding:8px; width:65%;">';
            $property_html .= '<div style="font-size:20px; font-weight:bold;">' . esc_html(get_the_title($property_id)) . '</div>';
            if (!empty($fields['address'])) {
                $property_html .= '<div style="font-size:14px;">(' . esc_html($fields['address']) . ')</div>';
            }
            $property_html .= '</td>';
            // Rent cell (rowspan=2)
            $property_html .= '<td rowspan="2" style="width:25%; border:1px solid #000; text-align:center; vertical-align:middle; padding:8px;">';
            $property_html .= '<div style="font-size:16px;">Rent 月租金</div>';
            $property_html .= '<div style="font-size:24px; color:#c81018; font-weight:bold; margin:8px 0;">NT$' . number_format((float)$fields['price']) . '</div>';
            $property_html .= '<div style="font-size:11px; color:#c81018;">(taxes & mgmt fee included)</div>';
            $property_html .= '</td>';
            $property_html .= '</tr>';

            // Second row with the nested table
            $property_html .= '<tr>';
            $property_html .= '<td style="padding:0; margin:0; border-top:1px solid #000;">';
            $property_html .= '<table style="width:100%; border-collapse:collapse;">';
            // Headers
            $property_html .= '<tr style="background:#f0f0f0;">';
            $property_html .= '<th style="border:1px solid #000;padding:4px; font-size:10px; font-weight:normal; text-align:center;">MRT</th>';
            $property_html .= '<th style="border:1px solid #000;padding:4px; font-size:10px; font-weight:normal; text-align:center;">Station</th>';
            $property_html .= '<th style="border:1px solid #000;padding:4px; font-size:10px; font-weight:normal; text-align:center;">Floor<br>層</th>';
            $property_html .= '<th style="border:1px solid #000;padding:4px; font-size:10px; font-weight:normal; text-align:center;">Ping (net)<br>坪</th>';
            $property_html .= '<th style="border:1px solid #000;padding:4px; font-size:10px; font-weight:normal; text-align:center;">Square meter (net)<br>平方公尺</th>';
            $property_html .= '<th style="border:1px solid #000;padding:4px; font-size:10px; font-weight:normal; text-align:center;">BDG age<br>屋齡</th>';
            $property_html .= '<th style="border:1px solid #000;padding:4px; font-size:10px; font-weight:normal; text-align:center;">Bedroom<br>房間</th>';
            $property_html .= '<th style="border:1px solid #000;padding:4px; font-size:10px; font-weight:normal; text-align:center;">Bathroom<br>浴室</th>';
            $property_html .= '<th style="border:1px solid #000;padding:4px; font-size:10px; font-weight:normal; text-align:center;">Parking<br>停車場</th>';
            $property_html .= '<th style="border:1px solid #000;padding:4px; font-size:10px; font-weight:normal; text-align:center;">Gym<br>健身房</th>';
            $property_html .= '<th style="border:1px solid #000;padding:4px; font-size:10px; font-weight:normal; text-align:center;">Swimming pool<br>游泳池</th>';
            $property_html .= '</tr>';
            // Data
            $property_html .= '<tr>';
            $property_html .= '<td style="border:1px solid #000;padding:4px;text-align:center;">' . esc_html($fields['mrt']) . '</td>';
            $property_html .= '<td style="border:1px solid #000;padding:4px;text-align:center;">' . esc_html($fields['station']) . '</td>';
            $floor = !empty($fields['floor']) ? $fields['floor'] : '';
            $total_floor = !empty($fields['total_floor']) ? $fields['total_floor'] : '';
            $floor_display = (!empty($floor) && !empty($total_floor)) ? $floor . '/' . $total_floor : $floor;
            $property_html .= '<td style="border:1px solid #000;padding:4px;text-align:center;">' . esc_html($floor_display) . '</td>';
            $property_html .= '<td style="border:1px solid #000;padding:4px;text-align:center;">' . esc_html($fields['ping']) . '</td>';
            $property_html .= '<td style="border:1px solid #000;padding:4px;text-align:center;">' . esc_html($fields['square_meters']) . '</td>';
            $property_html .= '<td style="border:1px solid #000;padding:4px;text-align:center;">' . esc_html($fields['building_age']) . '</td>';
            $property_html .= '<td style="border:1px solid #000;padding:4px;text-align:center;">' . esc_html($fields['bedroom']) . '</td>';
            $property_html .= '<td style="border:1px solid #000;padding:4px;text-align:center;">' . esc_html($fields['bathroom']) . '</td>';
            $property_html .= '<td style="border:1px solid #000;padding:4px;text-align:center;">' . esc_html(ucfirst(strtolower($fields['parking']))) . '</td>';
            $property_html .= '<td style="border:1px solid #000;padding:4px;text-align:center;">' . esc_html(ucfirst(strtolower($fields['gym']))) . '</td>';
            $property_html .= '<td style="border:1px solid #000;padding:4px;text-align:center;">' . esc_html(ucfirst(strtolower($fields['swimming_pool']))) . '</td>';
            $property_html .= '</tr>';
            $property_html .= '</table>';
            $property_html .= '</td>';
            $property_html .= '</tr>';

            $property_html .= '</table>';
            
            // Galería de imágenes optimizada para formato A4 - cuadrícula 3x2
            if (!empty($gallery_images)) {
                // Contenedor de tabla con ancho completo de página A4
                $property_html .= '<table cellpadding="1" cellspacing="1" style="width:100%; border-collapse:collapse;">';
                
                $images_count = count($gallery_images);
                $images_per_row = 3;
                $rows = ceil($images_count / $images_per_row);
                
                $img_counter = 0;
                for ($row = 0; $row < $rows && $row < 2; $row++) { // Limitamos a 2 filas para A4
                    $property_html .= '<tr>';
                    
                    for ($col = 0; $col < $images_per_row; $col++) {
                        if ($img_counter < $images_count) {
                            $img_id = $gallery_images[$img_counter];
                            $img_url = wp_get_attachment_image_url($img_id, 'large');
                            
                            // Celdas con tamaño fijo para formato A4
                            $property_html .= '<td style="width:33.33%; text-align:center;">';
                            
                            if ($img_url && file_exists(get_attached_file($img_id))) {
                                $img_path = get_attached_file($img_id);
                                $img_type = pathinfo($img_path, PATHINFO_EXTENSION);
                                $img_data = base64_encode(file_get_contents($img_path));
                                $img_src = 'data:image/' . $img_type . ';base64,' . $img_data;
                                
                                // Tamaño fijo optimizado para A4
                                $property_html .= '<img src="' . $img_src . '" style="width:440px; height:320px;" />';
                            } elseif ($img_url) {
                                $property_html .= '<img src="' . esc_url($img_url) . '" style="width:220px; height:140px;" />';
                            }
                            
                            $property_html .= '</td>';
                            $img_counter++;
                        } else {
                            $property_html .= '<td style="width:33.33%;"></td>';
                        }
                    }
                    
                    $property_html .= '</tr>';
                }
                
                $property_html .= '</table></div>';
            }
            
            $property_html .= '</div>';
            
            // Escribir el HTML de la propiedad
            $mpdf->WriteHTML($property_html);
        }
        
        // Si no hay propiedades, mostrar un mensaje
        if ($property_count === 0) {
            $mpdf->AddPage('L');
            $mpdf->WriteHTML('<div style="text-align:center;margin-top:100px;font-size:24px;">No hay propiedades para mostrar</div>');
        }
        
        $filename = sanitize_title($company_name) . '-' . sanitize_title($assignee_title) . '-' . date('Ymd') . '.pdf';
        
        // Save PDF to WordPress uploads directory
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/customer-house-list-pdfs';
        
        // Create directory if it doesn't exist
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }
        
        $file_path = $pdf_dir . '/' . $filename;
        $file_url = $upload_dir['baseurl'] . '/customer-house-list-pdfs/' . $filename;
        
        // Save PDF file
        $mpdf->Output($file_path, \Mpdf\Output\Destination::FILE);
        
        // Store PDF metadata
        $pdf_data = array(
            'filename' => $filename,
            'file_path' => $file_path,
            'file_url' => $file_url,
            'type' => 'with_images',
            'date_created' => current_time('mysql'),
            'post_id' => $post_id
        );
        
        // Get existing PDFs for this post
        $existing_pdfs = get_post_meta($post_id, '_generated_pdfs', true);
        if (!is_array($existing_pdfs)) {
            $existing_pdfs = array();
        }
        
        // Add new PDF to the list
        $existing_pdfs[] = $pdf_data;
        update_post_meta($post_id, '_generated_pdfs', $existing_pdfs);
        
        // Return success response with download link
        wp_send_json_success(array(
            'message' => 'PDF generated successfully',
            'download_url' => $file_url,
            'filename' => $filename
        ));
        exit;
    } catch (\Throwable $e) {
        echo '<b>ERROR mPDF:</b> ' . $e->getMessage();
        exit;
    }
}

// AJAX handler para generar PDF de Customer House List sin imágenes
add_action('wp_ajax_generate_customer_house_list_pdf_noimg', 'generate_customer_house_list_pdf_noimg');
function generate_customer_house_list_pdf_noimg() {
    if (!current_user_can('edit_posts')) {
        wp_die('No tienes permisos.');
    }
    // if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'customer_house_list_pdf_nonce')) {
    //     wp_die('Nonce inválido.');
    // }
    $post_id = intval($_GET['post_id']);
    if (!$post_id) {
        wp_die('ID de post inválido');
    }
    $autoload_path = get_template_directory() . '/vendor/autoload.php';
    if (!file_exists($autoload_path)) {
        wp_die('No se encontró autoload de Composer en: ' . $autoload_path);
    }
    require_once $autoload_path;
    $customer = get_post_meta($post_id, 'selected_customer', true);
    $property_list = get_post_meta($post_id, 'property_list', true);
    $property_list = !empty($property_list) ? (array)$property_list : array();
    
    // Get customer/assignee information
    $client_id = get_post_meta($post_id, 'selected_customer', true);
    $assignee_title = get_the_title($client_id);
    
    // Get company information
    $company_id = get_post_meta($client_id, 'company', true);
    if (empty($company_id)) {
        $company_id = get_post_meta($client_id, 'company_id', true);
    }
    
    if (!empty($company_id)) {
        $company_name = get_the_title($company_id);
    } else {
        $company_name = ''; // No company assigned
    }
    $today = date('F d, Y');
    $logo_path = get_template_directory() . '/assets/relo-logo.png';
    if (file_exists($logo_path)) {
        $logo_data = base64_encode(file_get_contents($logo_path));
        $logo_src = 'data:image/png;base64,' . $logo_data;
    } else {
        $logo_src = get_template_directory_uri() . '/assets/relo-logo.png';
    }
    // Portada tipo tabla
    ob_start();
    ?>
    <html><body><div style="text-align:left;padding:0 20px;"><img src="<?= $logo_src; ?>" style="height:60px;" /></div>
    <div style="width:100%;padding:30px 0 0 0;text-align:center;">
        <h1 style="background:#c81018;color:#fff;padding:20px 40px;border-radius:25px;display:inline-block;margin-top:60px;font-size:40px;"><span style="vertical-align:middle;">🏠</span> Home Search: Taipei</h1>
    </div>
    <div style="margin-top:80px;font-size:30px;text-align:left;padding:0 20px;">
        <b>Company:</b> <?= esc_html($company_name) ?><br>
        <b>Assignee:</b> <?= esc_html($assignee_title) ?><br>
        <b>Date:</b> <?= esc_html($today) ?><br>
    </div>
    <hr style="margin:40px 0 0 0;border:1px solid #c81018;">
    <div style="font-family:sans-serif;font-size:13px;margin:20px 0;">
        <div style="margin-bottom:10px;text-align:right;">
            <span>1 Ping = 3.3 square meters</span>
        </div>
        <table style="width:100%;border-collapse:collapse;">
            <tr style="background:#c81018;color:#fff;">
                <th style="border:1px solid #000;padding:4px;">No.</th>
                <th style="border:1px solid #000;padding:4px;">Unit</th>
                <th style="border:1px solid #000;padding:4px;">Building</th>
                <th style="border:1px solid #000;padding:4px;">District</th>
                <th style="border:1px solid #000;padding:4px;">Address</th>
                <th style="border:1px solid #000;padding:4px;">Rent (taxes & mgmt fee included)</th>
                <th style="border:1px solid #000;padding:4px;">Net Ping/Sq.mt</th>
                <th style="border:1px solid #000;padding:4px;">Floor</th>
                <th style="border:1px solid #000;padding:4px;">Remarks</th>
            </tr>
            <?php foreach ($property_list as $idx => $property_id):
                $fields = array();
                $meta_fields = [
                    'property_id', 'price', 'rent', 'district', 'address', 'chinese_address', 'building_age', 'gross_size', 'net_size', 'square_meters', 'ping', 'floor', 'total_floor', 'notes', 'unit', 'remarks'
                ];
                foreach ($meta_fields as $field) {
                    $fields[$field] = get_post_meta($property_id, $field, true);
                }
                ?>
                <tr>
                    <td style="border:1px solid #000;padding:4px;"> <?= ($idx+1) ?> </td>
                    <td style="border:1px solid #000;padding:4px;"> <?= esc_html($fields['unit']) ?> </td>
                    <td style="border:1px solid #000;padding:4px;"> <?= esc_html(get_the_title($property_id)) ?> </td>
                    <td style="border:1px solid #000;padding:4px;"> <?= esc_html(get_the_title($fields['district'])) ?> </td>
                    <td style="border:1px solid #000;padding:4px;"> <?= esc_html($fields['address']) ?> </td>
                    <td style="border:1px solid #000;padding:4px;color:#c81018;font-weight:bold;">NT$<?= number_format((float)$fields['price']) ?></td>
                    <td style="border:1px solid #000;padding:4px;"> <?= esc_html($fields['ping']) ?>/<?= esc_html($fields['square_meters']) ?> </td>
                    <td style="border:1px solid #000;padding:4px;"> <?php 
                        $floor = !empty($fields['floor']) ? $fields['floor'] : '';
                        $total_floor = !empty($fields['total_floor']) ? $fields['total_floor'] : '';
                        $floor_display = (!empty($floor) && !empty($total_floor)) ? "$floor/$total_floor" : $floor;
                        echo esc_html($floor_display);
                    ?> </td>
                    <td style="border:1px solid #000;padding:4px;"> <?= esc_html($fields['remarks']) ?> </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    </body></html>
    <?php
    $html = ob_get_clean();
    try {
        // Set up writable temp directory for mPDF
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/mpdf-temp';
        
        // Create temp directory if it doesn't exist
        if (!file_exists($temp_dir)) {
            wp_mkdir_p($temp_dir);
        }
        
        $mpdf = new \Mpdf\Mpdf([
            'tempDir' => $temp_dir,
            'orientation' => 'L',
            'default_font' => 'sun-exta',
        ]);
        $mpdf->SetFont('sun-exta');
        $mpdf->WriteHTML($html);
        $filename = sanitize_title($company_name) . '-' . sanitize_title($assignee_title) . '-' . date('Ymd') . '-noimg.pdf';
        
        // Save PDF to WordPress uploads directory
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/customer-house-list-pdfs';
        
        // Create directory if it doesn't exist
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }
        
        $file_path = $pdf_dir . '/' . $filename;
        $file_url = $upload_dir['baseurl'] . '/customer-house-list-pdfs/' . $filename;
        
        // Save PDF file
        $mpdf->Output($file_path, \Mpdf\Output\Destination::FILE);
        
        // Store PDF metadata
        $pdf_data = array(
            'filename' => $filename,
            'file_path' => $file_path,
            'file_url' => $file_url,
            'type' => 'no_images',
            'date_created' => current_time('mysql'),
            'post_id' => $post_id
        );
        
        // Get existing PDFs for this post
        $existing_pdfs = get_post_meta($post_id, '_generated_pdfs', true);
        if (!is_array($existing_pdfs)) {
            $existing_pdfs = array();
        }
        
        // Add new PDF to the list
        $existing_pdfs[] = $pdf_data;
        update_post_meta($post_id, '_generated_pdfs', $existing_pdfs);
        
        // Return success response with download link
        wp_send_json_success(array(
            'message' => 'PDF generated successfully',
            'download_url' => $file_url,
            'filename' => $filename
        ));
        exit;
    } catch (\Throwable $e) {
        echo '<b>ERROR mPDF:</b> ' . $e->getMessage();
        exit;
    }
}
<?php
/**
 * Lease Template Document Details Meta Box Template
 */
?>
<div class="houses-meta-box">
    <?php foreach ($this->fields as $section_id => $section) : ?>
        <div class="houses-meta-section">
            <h3><?php echo esc_html($section['title']); ?></h3>
            <?php foreach ($section['fields'] as $field_id => $field) : ?>
                <p>
                    <label for="<?php echo esc_attr($field_id); ?>"><?php echo esc_html($field['label']); ?>:</label>
                    <?php if ($field['type'] === 'file') : ?>
                        <div class="document-upload-container">
                            <input type="hidden" id="<?php echo esc_attr($field_id); ?>" name="<?php echo esc_attr($field_id); ?>" value="<?php echo esc_attr($values[$field_id]); ?>">
                            <div class="document-preview">
                                <?php if (!empty($values[$field_id])) : 
                                    $file_url = wp_get_attachment_url($values[$field_id]);
                                    $file_type = wp_check_filetype(basename($file_url));
                                    $file_icon = 'dashicons-media-document';
                                    
                                    // Determine icon based on file type
                                    if (strpos($file_type['type'], 'pdf') !== false) {
                                        $file_icon = 'dashicons-pdf';
                                    } elseif (strpos($file_type['type'], 'word') !== false) {
                                        $file_icon = 'dashicons-media-document';
                                    } elseif (strpos($file_type['type'], 'excel') !== false || strpos($file_type['type'], 'spreadsheet') !== false) {
                                        $file_icon = 'dashicons-media-spreadsheet';
                                    } elseif (strpos($file_type['type'], 'text') !== false) {
                                        $file_icon = 'dashicons-media-text';
                                    }
                                ?>
                                    <div class="document-icon">
                                        <span class="dashicons <?php echo esc_attr($file_icon); ?>"></span>
                                    </div>
                                    <div class="document-info">
                                        <span class="document-filename"><?php echo esc_html(basename($file_url)); ?></span>
                                        <a href="<?php echo esc_url($file_url); ?>" target="_blank" class="document-view">View Document</a>
                                    </div>
                                    <a href="#" class="remove-document">Remove</a>
                                <?php else : ?>
                                    <div class="no-document">No document uploaded</div>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button upload-document-button">Upload Document</button>
                        </div>
                    <?php elseif ($field['type'] === 'textarea') : ?>
                        <textarea id="<?php echo esc_attr($field_id); ?>" 
                                name="<?php echo esc_attr($field_id); ?>" 
                                class="<?php echo esc_attr($field['class']); ?>" 
                                rows="5"><?php echo esc_textarea($values[$field_id]); ?></textarea>
                    <?php elseif ($field['type'] === 'select') : ?>
                        <select id="<?php echo esc_attr($field_id); ?>" 
                                name="<?php echo esc_attr($field_id); ?>" 
                                class="<?php echo esc_attr($field['class']); ?>">
                            <?php foreach ($field['options'] as $option_value => $option_label) : ?>
                                <option value="<?php echo esc_attr($option_value); ?>" 
                                        <?php selected($values[$field_id], $option_value); ?>>
                                    <?php echo esc_html($option_label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else : ?>
                        <input type="<?php echo esc_attr($field['type']); ?>" 
                               id="<?php echo esc_attr($field_id); ?>" 
                               name="<?php echo esc_attr($field_id); ?>" 
                               value="<?php echo esc_attr($values[$field_id]); ?>" 
                               class="<?php echo esc_attr($field['class']); ?>">
                    <?php endif; ?>
                </p>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>

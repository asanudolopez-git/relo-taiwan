<?php
/**
 * Property Details Meta Box Template
 */
?>
<div class="houses-meta-box">
    <?php foreach ($this->fields as $section_id => $section): ?>
        <div class="houses-meta-section">
            <h3><?php echo esc_html($section['title']); ?></h3>
            <?php foreach ($section['fields'] as $field_id => $field): ?>
                <?php if (isset($field) && !is_null($field) && isset($field['label']) && isset($field['type'])): ?>
                    <p>
                        <label for="<?php echo esc_attr($field_id); ?>"><?php echo esc_html($field['label']); ?>:</label>
                        <?php if ($field['type'] === 'select'): ?>
                            <select id="<?php echo esc_attr($field_id); ?>" name="<?php echo esc_attr($field_id); ?>"
                                class="<?php echo esc_attr($field['class'] ?? ''); ?>">
                                <?php if (isset($field['options']) && is_array($field['options'])): ?>
                                    <?php foreach ($field['options'] as $value => $label): ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($values[$field_id] ?? '', $value); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        <?php elseif ($field['type'] === 'textarea'): ?>
                            <textarea id="<?php echo esc_attr($field_id); ?>" name="<?php echo esc_attr($field_id); ?>"
                                class="<?php echo esc_attr($field['class'] ?? ''); ?>"
                                rows="5"><?php echo esc_textarea($values[$field_id] ?? ''); ?></textarea>
                        <?php elseif ($field['type'] === 'gallery'): ?>
                        <div class="gallery-images">
                            <?php
                            $gallery_images = !empty($values[$field_id]) ? $values[$field_id] : array();
                            if (!empty($gallery_images) && is_array($gallery_images)):
                                ?>
                                <?php foreach ($gallery_images as $image_id): ?>
                                    <div class="gallery-image">
                                        <?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?>
                                        <input type="hidden" name="<?php echo esc_attr($field_id); ?>[]"
                                            value="<?php echo esc_attr($image_id); ?>">
                                        <a href="#" class="remove-gallery-image">Remove</a>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <a href="#" class="add-gallery-image button button-primary">Add Images</a>
                            <div style="clear:both;"></div>
                        </div>
                    <?php else: ?>
                        <input type="<?php echo esc_attr($field['type']); ?>" id="<?php echo esc_attr($field_id); ?>"
                            name="<?php echo esc_attr($field_id); ?>"
                            value="<?php echo ($field_id === 'property_post_id') ? esc_attr($post->ID) : esc_attr($values[$field_id] ?? ''); ?>"
                            class="<?php echo esc_attr($field['class'] ?? ''); ?>" 
                            <?php if (!empty($field['readonly'])): ?>readonly<?php endif; ?>
                            <?php if (isset($field['step'])): ?>step="<?php echo esc_attr($field['step']); ?>"<?php endif; ?>>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>
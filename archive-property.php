<?php
/**
 * The template for displaying property archive pages
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container">
        <header class="page-header">
            <h1 class="page-title">Properties</h1>
        </header><!-- .page-header -->

        <?php if (have_posts()) : ?>
            <div class="property-listings">
                <?php
                while (have_posts()) :
                    the_post();
                    
                    // Get property meta data
                    $property_id = get_the_ID();
                    $rent = get_post_meta($property_id, 'rent', true);
                    $bedrooms = get_post_meta($property_id, 'bedroom', true);
                    $bathrooms = get_post_meta($property_id, 'bathroom', true);
                    $district = get_post_meta($property_id, 'district', true);
                    $address = get_post_meta($property_id, 'address', true);
                    $chinese_address = get_post_meta($property_id, 'chinese_address', true);
                    
                    // Get district name if ID is provided
                    $district_name = '';
                    if (!empty($district)) {
                        $district_obj = get_post($district);
                        if ($district_obj) {
                            $district_name = $district_obj->post_title;
                        }
                    }
                    
                    // Get the first image from gallery
                    $gallery_images = get_post_meta($property_id, 'gallery_images', true);
                    $featured_image = '';
                    if (!empty($gallery_images) && is_array($gallery_images)) {
                        $featured_image = wp_get_attachment_image_url($gallery_images[0], 'medium');
                    }
                    
                    // Get availability date (using current date for demo)
                    $availability_date = date('j M');
                    $availability_time = date('H:i');
                    $full_date = date('j M, Y');
                    ?>
                    
                    <div class="property-item">
                        <div class="property-image">
                            <?php if (!empty($featured_image)) : ?>
                                <img src="<?php echo esc_url($featured_image); ?>" alt="<?php the_title_attribute(); ?>">
                            <?php else : ?>
                                <div class="no-image"></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="property-icon">
                            <span class="dashicons dashicons-building"></span>
                        </div>
                        
                        <div class="property-details">
                            <h3 class="property-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            <p class="property-address">
                                <?php echo esc_html($address); ?>
                                <?php if (!empty($district_name)) : ?>
                                    , <?php echo esc_html($district_name); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <div class="property-price">
                            <?php if (!empty($rent)) : ?>
                                <div class="price">NT$ <?php echo number_format($rent); ?></div>
                            <?php endif; ?>
                            <div class="property-specs">
                                <?php if (!empty($bedrooms)) : ?>
                                    <span class="spec-item">
                                        <span class="dashicons dashicons-building"></span>
                                        <?php echo esc_html($bedrooms); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($bathrooms)) : ?>
                                    <span class="spec-item">
                                        <span class="dashicons dashicons-admin-generic"></span>
                                        <?php echo esc_html($bathrooms); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="property-availability">
                            <div class="availability-status">Available</div>
                            <div class="availability-date"><?php echo esc_html($full_date); ?></div>
                        </div>
                        
                        <div class="property-date">
                            <div class="date"><?php echo esc_html($availability_date); ?></div>
                            <div class="time"><?php echo esc_html($availability_time); ?></div>
                        </div>
                        
                        <div class="property-actions">
                            <a href="#" class="action-button message-button">
                                <span class="dashicons dashicons-email-alt"></span>
                            </a>
                        </div>
                        
                        <div class="property-actions">
                            <a href="#" class="action-button calendar-button">
                                <span class="dashicons dashicons-calendar-alt"></span>
                            </a>
                        </div>
                        
                        <div class="property-actions">
                            <a href="#" class="action-button cancel-button">
                                <span class="dashicons dashicons-no-alt"></span>
                            </a>
                        </div>
                        
                        <div class="property-actions">
                            <a href="#" class="action-button favorite-button">
                                <span class="dashicons dashicons-star-empty"></span>
                            </a>
                        </div>
                    </div>
                    
                <?php endwhile; ?>
            </div>
            
            <?php the_posts_navigation(); ?>
            
        <?php else : ?>
            <p>No properties found.</p>
        <?php endif; ?>
    </div>
</main><!-- #main -->

<?php
get_footer();

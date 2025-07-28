<?php get_header(); ?>

<main id="primary" class="site-main">
    <div class="welcome-banner">
        <div class="welcome-content">
            <h1><?php echo esc_html__('Exceptional Properties', 'houses-theme'); ?></h1>
            <p><?php echo esc_html__('Discover our curated collection of distinguished properties', 'houses-theme'); ?></p>
        </div>
    </div>

    <?php if (have_posts()) : ?>
        <div class="properties-grid">
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('property-card'); ?>>
                    <div class="property-card-image">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('large'); ?>
                        <?php else : ?>
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/placeholder.jpg'); ?>" alt="<?php echo esc_attr__('Property Image', 'houses-theme'); ?>">
                        <?php endif; ?>
                    </div>

                    <div class="property-card-content">
                        <div class="property-card-price">
                            <?php 
                            $price = get_post_meta(get_the_ID(), 'property_price', true);
                            if ($price) {
                                echo esc_html('$' . number_format($price));
                            } else {
                                echo esc_html__('Price Upon Request', 'houses-theme');
                            }
                            ?>
                        </div>

                        <h2 class="entry-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>

                        <div class="property-card-details">
                            <?php
                            $details = array(
                                'property_location' => __('Location', 'houses-theme'),
                                'property_size' => __('Size', 'houses-theme'),
                                'property_bedrooms' => __('Bedrooms', 'houses-theme'),
                                'property_bathrooms' => __('Bathrooms', 'houses-theme')
                            );

                            foreach ($details as $key => $label) :
                                $value = get_post_meta(get_the_ID(), $key, true);
                                if ($value) :
                            ?>
                                <div class="detail">
                                    <div class="label"><?php echo esc_html($label); ?></div>
                                    <div class="value"><?php echo esc_html($value); ?></div>
                                </div>
                            <?php 
                                endif;
                            endforeach;
                            ?>
                        </div>

                        <a href="<?php the_permalink(); ?>" class="view-details">
                            <?php echo esc_html__('View Property', 'houses-theme'); ?>
                        </a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <?php
        the_posts_navigation(array(
            'prev_text' => __('Previous', 'houses-theme'),
            'next_text' => __('Next', 'houses-theme'),
        ));
        ?>

    <?php else : ?>
        <div class="no-properties">
            <div class="welcome-content">
                <h2><?php echo esc_html__('No Properties Available', 'houses-theme'); ?></h2>
                <p><?php echo esc_html__('Please check back soon for new listings.', 'houses-theme'); ?></p>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php get_footer(); ?>

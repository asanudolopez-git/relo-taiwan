<?php
/**
 * Template for displaying single property
 */

get_header();
?>

<div class="property-single">
    <div class="property-header">
        <?php if ($price = get_post_meta(get_the_ID(), 'price', true)): ?>
            <div class="property-price">
                <h1>NT$ <?php echo number_format($price); ?> /Month</h1>
                <p>Building Management Fee: Included in Rent</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if (has_post_thumbnail()): ?>
        <div class="property-gallery">
            <?php the_post_thumbnail('full'); ?>
        </div>
    <?php endif; ?>

    <div class="property-container">
        <div class="property-main">
            <div class="property-content">
                <!-- Basic Info Section -->
                <section class="info-section">
                    <h2>Basic Info</h2>
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-item">
                                <div class="label">Property ID</div>
                                <div class="value">
                                    <?php echo esc_html(get_post_meta(get_the_ID(), 'property_id', true)); ?>
                                </div>
                            </div>
                        </div>

                        <div class="info-row two-columns">
                            <div class="info-item">
                                <div class="label">Layout</div>
                                <div class="value"><?php echo esc_html(get_post_meta(get_the_ID(), 'layout', true)); ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="label">Gross Size</div>
                                <div class="value">
                                    <?php echo esc_html(get_post_meta(get_the_ID(), 'gross_size', true)); ?>
                                </div>
                            </div>
                        </div>

                        <div class="info-row two-columns">
                            <div class="info-item">
                                <div class="label">Net Size</div>
                                <div class="value">
                                    <?php echo esc_html(get_post_meta(get_the_ID(), 'net_size', true)); ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="label">MRT</div>
                                <div class="value"><?php echo esc_html(get_post_meta(get_the_ID(), 'mrt', true)); ?>
                                </div>
                            </div>
                        </div>

                        <div class="info-row two-columns">
                            <div class="info-item">
                                <div class="label">Property Type</div>
                                <div class="value">
                                    <?php echo esc_html(get_post_meta(get_the_ID(), 'property_type', true)); ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="label">Floor</div>
                                <div class="value"><?php echo esc_html(get_post_meta(get_the_ID(), 'floor', true)); ?>
                                </div>
                            </div>
                        </div>

                        <div class="info-row two-columns">
                            <div class="info-item">
                                <div class="label">Elevator</div>
                                <div class="value">
                                    <?php echo esc_html(get_post_meta(get_the_ID(), 'elevator', true)); ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="label">Parking</div>
                                <div class="value"><?php echo esc_html(get_post_meta(get_the_ID(), 'parking', true)); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($address = get_post_meta(get_the_ID(), 'address', true)): ?>
                        <div class="address-section">
                            <h3>Address</h3>
                            <p><?php echo esc_html($address); ?></p>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Details Section -->
                <section class="info-section">
                    <h2>Details</h2>
                    <div class="info-grid">
                        <div class="info-row two-columns">
                            <div class="info-item">
                                <div class="label">Concierge</div>
                                <div class="value">
                                    <?php echo esc_html(get_post_meta(get_the_ID(), 'concierge', true)); ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="label">Maid's Room</div>
                                <div class="value">
                                    <?php echo esc_html(get_post_meta(get_the_ID(), 'maids_room', true)); ?>
                                </div>
                            </div>
                        </div>

                        <div class="info-row two-columns">
                            <div class="info-item">
                                <div class="label">Garbage</div>
                                <div class="value"><?php echo esc_html(get_post_meta(get_the_ID(), 'garbage', true)); ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="label">Open Kitchen</div>
                                <div class="value">
                                    <?php echo esc_html(get_post_meta(get_the_ID(), 'open_kitchen', true)); ?>
                                </div>
                            </div>
                        </div>

                        <div class="info-row two-columns">
                            <div class="info-item">
                                <div class="label">Short Term Rental</div>
                                <div class="value">
                                    <?php echo esc_html(get_post_meta(get_the_ID(), 'short_term_rental', true)); ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="label">Legal Use</div>
                                <div class="value">
                                    <?php echo esc_html(get_post_meta(get_the_ID(), 'legal_use', true)); ?>
                                </div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-item">
                                <div class="label">Bathtub</div>
                                <div class="value"><?php echo esc_html(get_post_meta(get_the_ID(), 'bathtub', true)); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Features Section -->
                <?php if ($features = get_post_meta(get_the_ID(), 'features', true)): ?>
                    <section class="info-section">
                        <h2>Features</h2>
                        <div class="features-list">
                            <?php
                            $features_array = explode("\n", $features);
                            foreach ($features_array as $feature) {
                                $feature = trim($feature);
                                if (!empty($feature)) {
                                    echo '<div class="feature-item">';
                                    echo '<span class="bullet">•</span> ';
                                    echo esc_html($feature);
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Location Section -->
                <?php
                $latitude = get_post_meta(get_the_ID(), 'latitude', true);
                $longitude = get_post_meta(get_the_ID(), 'longitude', true);

                if (!empty($latitude) && !empty($longitude)):
                    ?>
                    <section class="info-section">
                        <h2>Location</h2>
                        <div class="map-container">
                            <div class="map-tabs">
                                <button class="map-tab active">Map</button>
                                <button class="map-tab">Satellite</button>
                            </div>
                            <div id="property-map" class="property-map" data-lat="<?php echo esc_attr($latitude); ?>"
                                data-lng="<?php echo esc_attr($longitude); ?>">
                            </div>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
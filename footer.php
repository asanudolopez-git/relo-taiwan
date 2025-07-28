    </div><!-- #content -->

    <footer class="site-footer">
        <div class="footer-content">
            <div class="footer-branding">
                <h3 class="footer-title"><?php bloginfo('name'); ?></h3>
                <p class="footer-description"><?php bloginfo('description'); ?></p>
            </div>
            
            <div class="footer-contact">
                <h4><?php echo esc_html__('Contact Us', 'houses-theme'); ?></h4>
                <p><?php echo esc_html__('Email: info@example.com', 'houses-theme'); ?></p>
                <p><?php echo esc_html__('Phone: +1 (555) 123-4567', 'houses-theme'); ?></p>
                <p><?php echo esc_html__('Address: 123 Luxury Avenue, Suite 100', 'houses-theme'); ?></p>
            </div>

            <div class="footer-menu">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer-menu',
                    'menu_class'     => 'footer-links',
                    'fallback_cb'    => false,
                ));
                ?>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php echo esc_html__('All rights reserved.', 'houses-theme'); ?></p>
            </div>
        </div>
    </footer>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>

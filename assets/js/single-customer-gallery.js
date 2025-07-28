/**
 * Single Customer Property Gallery JavaScript
 * Handles lightbox functionality and gallery interactions
 */

(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        initPropertyGalleries();
        initGalleryNavigation();
        initImageLazyLoading();
    });

    /**
     * Initialize property galleries with lightbox functionality
     */
    function initPropertyGalleries() {
        // Simple lightbox implementation
        $('.gallery-item a').on('click', function(e) {
            e.preventDefault();
            
            const imageUrl = $(this).attr('href');
            const imageAlt = $(this).find('img').attr('alt');
            const galleryGroup = $(this).data('lightbox');
            
            openLightbox(imageUrl, imageAlt, galleryGroup);
        });
    }

    /**
     * Open lightbox with image
     */
    function openLightbox(imageUrl, imageAlt, galleryGroup) {
        // Create lightbox if it doesn't exist
        if (!$('#property-lightbox').length) {
            createLightbox();
        }

        const $lightbox = $('#property-lightbox');
        const $lightboxImg = $lightbox.find('.lightbox-image');
        const $lightboxCaption = $lightbox.find('.lightbox-caption');

        // Set image and caption
        $lightboxImg.attr('src', imageUrl).attr('alt', imageAlt);
        $lightboxCaption.text(imageAlt);

        // Show lightbox
        $lightbox.fadeIn(300);
        $('body').addClass('lightbox-open');

        // Store current gallery group for navigation
        $lightbox.data('current-group', galleryGroup);
        updateNavigationButtons(imageUrl, galleryGroup);
    }

    /**
     * Create lightbox HTML structure
     */
    function createLightbox() {
        const lightboxHtml = `
            <div id="property-lightbox" class="property-lightbox">
                <div class="lightbox-overlay"></div>
                <div class="lightbox-content">
                    <button class="lightbox-close" aria-label="Close">&times;</button>
                    <button class="lightbox-prev" aria-label="Previous">&#8249;</button>
                    <button class="lightbox-next" aria-label="Next">&#8250;</button>
                    <img class="lightbox-image" src="" alt="">
                    <div class="lightbox-caption"></div>
                </div>
            </div>
        `;

        $('body').append(lightboxHtml);

        // Bind close events
        $('#property-lightbox .lightbox-close, #property-lightbox .lightbox-overlay').on('click', closeLightbox);
        
        // Close on escape key
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27 && $('#property-lightbox').is(':visible')) {
                closeLightbox();
            }
        });
    }

    /**
     * Close lightbox
     */
    function closeLightbox() {
        $('#property-lightbox').fadeOut(300);
        $('body').removeClass('lightbox-open');
    }

    /**
     * Initialize gallery navigation (prev/next)
     */
    function initGalleryNavigation() {
        $(document).on('click', '.lightbox-prev', function() {
            navigateGallery('prev');
        });

        $(document).on('click', '.lightbox-next', function() {
            navigateGallery('next');
        });

        // Keyboard navigation
        $(document).on('keydown', function(e) {
            if ($('#property-lightbox').is(':visible')) {
                if (e.keyCode === 37) { // Left arrow
                    navigateGallery('prev');
                } else if (e.keyCode === 39) { // Right arrow
                    navigateGallery('next');
                }
            }
        });
    }

    /**
     * Navigate through gallery images
     */
    function navigateGallery(direction) {
        const $lightbox = $('#property-lightbox');
        const currentGroup = $lightbox.data('current-group');
        const currentSrc = $lightbox.find('.lightbox-image').attr('src');
        
        const $galleryItems = $(`[data-lightbox="${currentGroup}"]`);
        let currentIndex = -1;

        // Find current image index
        $galleryItems.each(function(index) {
            if ($(this).attr('href') === currentSrc) {
                currentIndex = index;
                return false;
            }
        });

        if (currentIndex === -1) return;

        let newIndex;
        if (direction === 'next') {
            newIndex = (currentIndex + 1) % $galleryItems.length;
        } else {
            newIndex = (currentIndex - 1 + $galleryItems.length) % $galleryItems.length;
        }

        const $newItem = $galleryItems.eq(newIndex);
        const newImageUrl = $newItem.attr('href');
        const newImageAlt = $newItem.find('img').attr('alt');

        // Update lightbox
        $lightbox.find('.lightbox-image').attr('src', newImageUrl).attr('alt', newImageAlt);
        $lightbox.find('.lightbox-caption').text(newImageAlt);

        updateNavigationButtons(newImageUrl, currentGroup);
    }

    /**
     * Update navigation button states
     */
    function updateNavigationButtons(currentSrc, galleryGroup) {
        const $galleryItems = $(`[data-lightbox="${galleryGroup}"]`);
        const $lightbox = $('#property-lightbox');

        if ($galleryItems.length <= 1) {
            $lightbox.find('.lightbox-prev, .lightbox-next').hide();
        } else {
            $lightbox.find('.lightbox-prev, .lightbox-next').show();
        }
    }

    /**
     * Initialize lazy loading for gallery images
     */
    function initImageLazyLoading() {
        // Simple lazy loading implementation
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        observer.unobserve(img);
                    }
                }
            });
        });

        // Observe all gallery images with data-src
        document.querySelectorAll('.gallery-item img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }

    /**
     * Add smooth scrolling to property sections
     */
    function initSmoothScrolling() {
        // Add navigation links if needed
        const $sections = $('.section');
        if ($sections.length > 1) {
            createQuickNavigation($sections);
        }
    }

    /**
     * Create quick navigation for property sections
     */
    function createQuickNavigation($sections) {
        const $nav = $('<nav class="property-quick-nav"><ul></ul></nav>');
        
        $sections.each(function() {
            const $section = $(this);
            const sectionId = $section.attr('id') || 'section-' + $sections.index($section);
            $section.attr('id', sectionId);
            
            const sectionTitle = $section.find('h2').first().text();
            const $navItem = $(`<li><a href="#${sectionId}">${sectionTitle}</a></li>`);
            
            $nav.find('ul').append($navItem);
        });

        $('.assignee-page .container').prepend($nav);

        // Smooth scroll behavior
        $nav.find('a').on('click', function(e) {
            e.preventDefault();
            const target = $($(this).attr('href'));
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 500);
            }
        });
    }

    // Initialize additional features
    $(window).on('load', function() {
        initSmoothScrolling();
    });

})(jQuery);

<?php
/**
 * Frontend Category Carousel Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('ekwa_bag_settings', array());
$show_labels = isset($settings['show_before_after_labels']) ? $settings['show_before_after_labels'] : 1;

// Carousel settings
$carousel_settings = isset($settings['carousel']) ? $settings['carousel'] : array();
$show_arrows = isset($carousel_settings['show_arrows']) ? $carousel_settings['show_arrows'] : 1;
$show_dots = isset($carousel_settings['show_dots']) ? $carousel_settings['show_dots'] : 1;
$show_title = isset($carousel_settings['show_title']) ? $carousel_settings['show_title'] : 1;
$carousel_title_text = isset($carousel_settings['title_text']) ? $carousel_settings['title_text'] : __('Before & After Results', 'ekwa-before-after-gallery');
?>

<?php
$custom_tpl_enabled = isset($carousel_settings['custom_card_template_enabled']) ? $carousel_settings['custom_card_template_enabled'] : 0;
$wrapper_class = 'ekwa-bag-carousel-wrapper' . ($custom_tpl_enabled ? ' ekwa-bag-custom-tpl' : '');
?>
<div class="<?php echo esc_attr($wrapper_class); ?>" data-instance-id="<?php echo esc_attr($carousel_instance_id); ?>" data-show-labels="<?php echo esc_attr($show_labels); ?>">
    
    <?php if ($show_title && !empty($carousel_title_text)) : ?>
    <h2 class="ekwa-bag-carousel-title"><?php echo esc_html($carousel_title_text); ?></h2>
    <?php endif; ?>

    <!-- Category Filter Tabs (rendered by JS when category=all) -->
    <div class="ekwa-bag-carousel-filter-tabs" style="display:none;"></div>

    <?php if (empty($carousel_cases)) : ?>
        <div class="ekwa-bag-carousel-empty">
            <i class="fas fa-images"></i>
            <p><?php esc_html_e('No before & after cases found for this category.', 'ekwa-before-after-gallery'); ?></p>
        </div>
    <?php else : ?>

    <div class="ekwa-bag-carousel-container<?php echo !$show_arrows ? ' no-arrows' : ''; ?>">
        <?php if ($show_arrows) : ?>
        <button class="ekwa-bag-carousel-arrow prev" aria-label="<?php esc_attr_e('Previous slide', 'ekwa-before-after-gallery'); ?>"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
        <?php endif; ?>

        <div class="ekwa-bag-carousel-viewport">
            <div class="ekwa-bag-carousel-track">
                <!-- Slides rendered by JS -->
            </div>
        </div>

        <?php if ($show_arrows) : ?>
        <button class="ekwa-bag-carousel-arrow next" aria-label="<?php esc_attr_e('Next slide', 'ekwa-before-after-gallery'); ?>"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
        <?php endif; ?>
    </div>

    <?php if ($show_dots) : ?>
    <div class="ekwa-bag-carousel-dots">
        <!-- Dots rendered by JS -->
    </div>
    <?php endif; ?>

    <!-- Carousel Modal -->
    <div class="ekwa-bag-carousel-modal">
        <div class="ekwa-bag-carousel-modal-backdrop"></div>
        <div class="ekwa-bag-carousel-modal-content">
            <div class="ekwa-bag-carousel-modal-header">
                <div class="ekwa-bag-carousel-modal-header-info">
                    <div class="ekwa-bag-carousel-modal-breadcrumb"><?php esc_html_e('Category', 'ekwa-before-after-gallery'); ?></div>
                    <h2 class="ekwa-bag-carousel-modal-title"><?php esc_html_e('Treatment Title', 'ekwa-before-after-gallery'); ?></h2>
                </div>
                <button class="ekwa-bag-carousel-modal-close" aria-label="<?php esc_attr_e('Close', 'ekwa-before-after-gallery'); ?>"><i class="fas fa-times" aria-hidden="true"></i></button>
            </div>
            <div class="ekwa-bag-carousel-modal-body">
                <div class="ekwa-bag-carousel-modal-images">
                    <div class="ekwa-bag-carousel-modal-img-box">
                        <img class="ekwa-bag-carousel-modal-before" src="" alt="<?php esc_attr_e('Before', 'ekwa-before-after-gallery'); ?>">
                        <span class="ekwa-bag-carousel-modal-img-tag"><?php esc_html_e('Before', 'ekwa-before-after-gallery'); ?></span>
                    </div>
                    <div class="ekwa-bag-carousel-modal-img-box after">
                        <img class="ekwa-bag-carousel-modal-after" src="" alt="<?php esc_attr_e('After', 'ekwa-before-after-gallery'); ?>">
                        <span class="ekwa-bag-carousel-modal-img-tag"><?php esc_html_e('After', 'ekwa-before-after-gallery'); ?></span>
                    </div>
                </div>
                <div class="ekwa-bag-carousel-modal-desc">
                    <p><?php esc_html_e('Description here...', 'ekwa-before-after-gallery'); ?></p>
                </div>
                <div class="ekwa-bag-carousel-modal-thumbs-section">
                    <div class="ekwa-bag-carousel-modal-thumbs-label"><?php esc_html_e('All Views', 'ekwa-before-after-gallery'); ?> (<span class="ekwa-bag-carousel-modal-view-count">0</span>)</div>
                    <div class="ekwa-bag-carousel-modal-thumbs">
                        <!-- Rendered by JS -->
                    </div>
                </div>
            </div>
            <div class="ekwa-bag-carousel-modal-footer">
                <button class="ekwa-bag-carousel-modal-nav-btn prev"><i class="fas fa-chevron-left"></i> <?php esc_html_e('Previous', 'ekwa-before-after-gallery'); ?></button>
                <div class="ekwa-bag-carousel-modal-counter">
                    <strong class="ekwa-bag-carousel-modal-current-num">1</strong> / <span class="ekwa-bag-carousel-modal-total-num">0</span>
                </div>
                <button class="ekwa-bag-carousel-modal-nav-btn next"><?php esc_html_e('Next', 'ekwa-before-after-gallery'); ?> <i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>

    <?php endif; ?>
</div>

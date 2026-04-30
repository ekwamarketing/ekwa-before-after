<?php
/**
 * Plugin Name: EKWA Before After Gallery
 * Plugin URI: https://ekwa.com
 * Description: A beautiful before and after gallery with stacked card design for dental and medical practices.
 * Version: 1.2.2
 * Author: EKWA
 * Author URI: https://ekwa.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ekwa-before-after-gallery
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


require 'includes/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/ekwamarketing/ekwa-before-after/',
	__FILE__,
	'ekwa-before-after-gallery'
);

$myUpdateChecker->setBranch('main');



//  plugin constants
define('EKWA_BAG_VERSION', '1.4.1');
define('EKWA_BAG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EKWA_BAG_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once EKWA_BAG_PLUGIN_DIR . 'includes/class-watermark.php';

/**
 * Main Plugin Class
 */
class EKWA_Before_After_Gallery {
    
    private static $instance = null;
    
    /**
     * Watermark handler
     */
    private $watermark = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->watermark = new EKWA_BAG_Watermark();
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Init hooks
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomies'));
        add_action('init', array($this, 'register_shortcode'));
        
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        
        // Media library watermark status
        add_filter('manage_media_columns', array($this, 'add_watermark_column'));
        add_action('manage_media_custom_column', array($this, 'show_watermark_column'), 10, 2);
        
        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
        add_action('wp_head', array($this, 'output_dynamic_css'));
        
        // AJAX hooks
        add_action('wp_ajax_ekwa_bag_get_cases', array($this, 'ajax_get_cases'));
        add_action('wp_ajax_nopriv_ekwa_bag_get_cases', array($this, 'ajax_get_cases'));
        
        // Watermark AJAX hooks
        add_action('wp_ajax_ekwa_bag_bulk_watermark', array($this, 'ajax_bulk_watermark'));
        add_action('wp_ajax_ekwa_bag_remove_watermarks', array($this, 'ajax_remove_watermarks'));
        add_action('wp_ajax_ekwa_bag_export_settings', array($this, 'ajax_export_settings'));
        add_action('wp_ajax_ekwa_bag_import_settings', array($this, 'ajax_import_settings'));
        add_action('wp_ajax_ekwa_bag_test_watermark', array($this, 'ajax_test_watermark'));
        add_action('wp_ajax_ekwa_bag_clear_and_reapply', array($this, 'ajax_clear_and_reapply'));
        
        // Auto-watermark scheduled event
        add_action('ekwa_bag_auto_watermark', array($this, 'auto_watermark_case'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        $this->register_post_type();
        $this->register_taxonomies();
        $this->create_default_categories();
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Register custom post type
     */
    public function register_post_type() {
        $labels = array(
            'name'               => __('Gallery Cases', 'ekwa-before-after-gallery'),
            'singular_name'      => __('Gallery Case', 'ekwa-before-after-gallery'),
            'menu_name'          => __('BA Gallery', 'ekwa-before-after-gallery'),
            'add_new'            => __('Add New Case', 'ekwa-before-after-gallery'),
            'add_new_item'       => __('Add New Case', 'ekwa-before-after-gallery'),
            'edit_item'          => __('Edit Case', 'ekwa-before-after-gallery'),
            'new_item'           => __('New Case', 'ekwa-before-after-gallery'),
            'view_item'          => __('View Case', 'ekwa-before-after-gallery'),
            'search_items'       => __('Search Cases', 'ekwa-before-after-gallery'),
            'not_found'          => __('No cases found', 'ekwa-before-after-gallery'),
            'not_found_in_trash' => __('No cases found in trash', 'ekwa-before-after-gallery'),
        );
        
        $args = array(
            'labels'              => $labels,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => false,
            'capability_type'     => 'post',
            'hierarchical'        => false,
                'supports'            => array('title', 'editor'),
            'has_archive'         => false,
            'rewrite'             => false,
        );
        
        register_post_type('ekwa_bag_case', $args);
    }
    
    /**
     * Register taxonomies
     */
    public function register_taxonomies() {
        // Main Category
        register_taxonomy('ekwa_bag_category', 'ekwa_bag_case', array(
            'labels' => array(
                'name'          => __('Categories', 'ekwa-before-after-gallery'),
                'singular_name' => __('Category', 'ekwa-before-after-gallery'),
                'add_new_item'  => __('Add New Category', 'ekwa-before-after-gallery'),
            ),
            'hierarchical' => true,
            'public'       => false,
            'publicly_queryable' => false,
            'show_ui'      => true,
            'show_admin_column' => true,
            'rewrite'      => false,
        ));
    }
    
    /**
     * Create default categories
     */
    private function create_default_categories() {
        $categories = array(
            'cosmetic' => array(
                'name' => 'Cosmetic',
                'children' => array('Whitening', 'Veneers')
            ),
            'restorative' => array(
                'name' => 'Restorative',
                'children' => array('Crowns', 'Implants', 'Dentures')
            ),
            'orthodontic' => array(
                'name' => 'Orthodontic',
                'children' => array('Clear Aligners', 'Braces')
            )
        );
        
        foreach ($categories as $slug => $cat) {
            $parent = term_exists($cat['name'], 'ekwa_bag_category');
            if (!$parent) {
                $parent = wp_insert_term($cat['name'], 'ekwa_bag_category', array('slug' => $slug));
            }
            
            if (!is_wp_error($parent)) {
                $parent_id = is_array($parent) ? $parent['term_id'] : $parent;
                foreach ($cat['children'] as $child) {
                    if (!term_exists($child, 'ekwa_bag_category')) {
                        wp_insert_term($child, 'ekwa_bag_category', array(
                            'parent' => $parent_id
                        ));
                    }
                }
            }
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Before After Gallery', 'ekwa-before-after-gallery'),
            __('BA Gallery', 'ekwa-before-after-gallery'),
            'manage_options',
            'ekwa-bag',
            array($this, 'render_admin_page'),
            'dashicons-images-alt2',
            30
        );
        
        add_submenu_page(
            'ekwa-bag',
            __('All Cases', 'ekwa-before-after-gallery'),
            __('All Cases', 'ekwa-before-after-gallery'),
            'manage_options',
            'edit.php?post_type=ekwa_bag_case'
        );
        
        add_submenu_page(
            'ekwa-bag',
            __('Add New Case', 'ekwa-before-after-gallery'),
            __('Add New', 'ekwa-before-after-gallery'),
            'manage_options',
            'post-new.php?post_type=ekwa_bag_case'
        );
        
        add_submenu_page(
            'ekwa-bag',
            __('Categories', 'ekwa-before-after-gallery'),
            __('Categories', 'ekwa-before-after-gallery'),
            'manage_options',
            'edit-tags.php?taxonomy=ekwa_bag_category&post_type=ekwa_bag_case'
        );
        
        add_submenu_page(
            'ekwa-bag',
            __('Settings', 'ekwa-before-after-gallery'),
            __('Settings', 'ekwa-before-after-gallery'),
            'manage_options',
            'ekwa-bag-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Render admin dashboard page
     */
    public function render_admin_page() {
        include EKWA_BAG_PLUGIN_DIR . 'includes/admin/dashboard.php';
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        include EKWA_BAG_PLUGIN_DIR . 'includes/admin/settings.php';
    }
    
    /**
     * Admin enqueue scripts
     */
    public function admin_enqueue_scripts($hook) {
        global $post_type;
        
        if ($post_type === 'ekwa_bag_case' || strpos($hook, 'ekwa-bag') !== false) {
            wp_enqueue_media();
            
            // Enqueue color picker for settings page
            if (strpos($hook, 'ekwa-bag-settings') !== false) {
                wp_enqueue_style('wp-color-picker');
                wp_enqueue_script('wp-color-picker');
                
                // Enqueue CodeMirror for custom template editor
                $cm_settings = wp_enqueue_code_editor(array('type' => 'text/html'));
                if ($cm_settings !== false) {
                    wp_add_inline_script('ekwa-bag-admin', sprintf(
                        'window.ekwaCmSettings = %s;',
                        wp_json_encode($cm_settings)
                    ), 'before');
                }
            }
            
            wp_enqueue_style('ekwa-bag-admin', EKWA_BAG_PLUGIN_URL . 'assets/css/admin.css', array(), EKWA_BAG_VERSION);
            wp_enqueue_script('ekwa-bag-admin', EKWA_BAG_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'jquery-ui-sortable', 'wp-color-picker'), EKWA_BAG_VERSION, true);
            
            wp_localize_script('ekwa-bag-admin', 'ekwaBagAdmin', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('ekwa_bag_admin_nonce'),
                'strings' => array(
                    'selectImage' => __('Select Image', 'ekwa-before-after-gallery'),
                    'useImage'    => __('Use This Image', 'ekwa-before-after-gallery'),
                    'removeSet'   => __('Remove this image set?', 'ekwa-before-after-gallery'),
                )
            ));
        }
    }
    
    /**
     * Frontend enqueue scripts
     */
    public function frontend_enqueue_scripts() {
        global $post;
        
        $has_gallery = is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'ekwa_gallery');
        $has_carousel = is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'ekwa_category_carousel');
        
        if ($has_gallery) {
            wp_enqueue_style('ekwa-bag-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap', array(), null);
            wp_enqueue_style('ekwa-bag-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0');
            wp_enqueue_style('ekwa-bag-gallery', EKWA_BAG_PLUGIN_URL . 'assets/css/gallery.css', array(), EKWA_BAG_VERSION);
            
            wp_enqueue_script('ekwa-bag-gallery', EKWA_BAG_PLUGIN_URL . 'assets/js/gallery.js', array(), EKWA_BAG_VERSION, true);
            
            wp_localize_script('ekwa-bag-gallery', 'ekwaBagFrontend', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('ekwa_bag_frontend_nonce'),
            ));
        }
        
        if ($has_carousel) {
            wp_enqueue_style('ekwa-bag-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap', array(), null);
            wp_enqueue_style('ekwa-bag-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0');
            wp_enqueue_style('ekwa-bag-carousel', EKWA_BAG_PLUGIN_URL . 'assets/css/carousel.css', array(), EKWA_BAG_VERSION);
            
            wp_enqueue_script('ekwa-bag-carousel', EKWA_BAG_PLUGIN_URL . 'assets/js/carousel.js', array(), EKWA_BAG_VERSION, true);
        }
    }
    
    /**
     * Register shortcodes
     */
    public function register_shortcode() {
        add_shortcode('ekwa_gallery', array($this, 'render_shortcode'));
        add_shortcode('ekwa_category_carousel', array($this, 'render_carousel_shortcode'));
    }
    
    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'category'    => '',
            'limit'       => -1,
            'columns'     => 3,
            'show_filter' => 'yes',
        ), $atts, 'ekwa_gallery');
        
        // Get cases data
        $cases = $this->get_cases_data($atts);
        $categories = $this->get_categories_tree();
        
        // Ensure scripts are loaded
        wp_enqueue_style('ekwa-bag-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap', array(), null);
        wp_enqueue_style('ekwa-bag-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0');
        wp_enqueue_style('ekwa-bag-gallery', EKWA_BAG_PLUGIN_URL . 'assets/css/gallery.css', array(), EKWA_BAG_VERSION);
        
        wp_enqueue_script('ekwa-bag-gallery', EKWA_BAG_PLUGIN_URL . 'assets/js/gallery.js', array(), EKWA_BAG_VERSION, true);
        
        // Get settings for card design
        $settings = get_option('ekwa_bag_settings', array());
        $card_design = isset($settings['card_design']) ? $settings['card_design'] : 'stacked';
        $show_labels = isset($settings['show_before_after_labels']) ? $settings['show_before_after_labels'] : 1;
        
        // Use inline script to pass data reliably
        $json_data = json_encode(array(
            'ajaxUrl'    => admin_url('admin-ajax.php'),
            'nonce'      => wp_create_nonce('ekwa_bag_frontend_nonce'),
            'cases'      => $cases,
            'categories' => $categories,
            'cardDesign' => $card_design,
            'showLabels' => $show_labels,
            'debug'      => $this->get_debug_info(),
        ));
        
        wp_add_inline_script('ekwa-bag-gallery', 'var ekwaBagFrontend = ' . $json_data . ';', 'before');
        
        ob_start();
        include EKWA_BAG_PLUGIN_DIR . 'includes/frontend/gallery-template.php';
        return ob_get_clean();
    }
    
    /**
     * Render category carousel shortcode
     */
    public function render_carousel_shortcode($atts) {
        static $carousel_instance = 0;
        $carousel_instance++;
        
        $atts = shortcode_atts(array(
            'category'         => '',    // Category slug - if empty, auto-detect from page slug
            'limit'            => -1,
            'per_page'         => '',    // Override slides to show (desktop)
            'per_page_tablet'  => '',    // Override tablet slides
            'per_page_mobile'  => '',    // Override mobile slides
            'show_arrows'      => '',    // Override arrows setting
            'show_dots'        => '',    // Override dots setting
            'autoplay'         => '',    // Override autoplay setting
            'title'            => '',    // Override carousel title
        ), $atts, 'ekwa_category_carousel');
        
        // Auto-detect category from page slug if not set
        $category_slug = $atts['category'];
        $show_category_filter = false;
        if ($category_slug === 'all') {
            $category_slug = ''; // Fetch all cases
            $show_category_filter = true;
        } elseif (empty($category_slug)) {
            $category_slug = $this->detect_category_from_page_slug();
        }
        
        // Get cases filtered by category
        $carousel_atts = array(
            'category' => $category_slug,
            'limit'    => $atts['limit'],
        );
        $carousel_cases = $this->get_cases_data($carousel_atts);
        $categories = $this->get_categories_tree();
        
        // Settings
        $settings = get_option('ekwa_bag_settings', array());
        $carousel_settings = isset($settings['carousel']) ? $settings['carousel'] : array();
        $show_labels = isset($settings['show_before_after_labels']) ? $settings['show_before_after_labels'] : 1;
        
        // Resolve responsive per_page (shortcode attr > admin setting > default)
        $per_page_desktop = !empty($atts['per_page']) ? absint($atts['per_page']) : (isset($carousel_settings['per_page_desktop']) ? absint($carousel_settings['per_page_desktop']) : 3);
        $per_page_tablet = !empty($atts['per_page_tablet']) ? absint($atts['per_page_tablet']) : (isset($carousel_settings['per_page_tablet']) ? absint($carousel_settings['per_page_tablet']) : 2);
        $per_page_mobile = !empty($atts['per_page_mobile']) ? absint($atts['per_page_mobile']) : (isset($carousel_settings['per_page_mobile']) ? absint($carousel_settings['per_page_mobile']) : 1);
        $show_arrows = $atts['show_arrows'] !== '' ? ($atts['show_arrows'] === 'yes' ? 1 : 0) : (isset($carousel_settings['show_arrows']) ? $carousel_settings['show_arrows'] : 1);
        $show_dots = $atts['show_dots'] !== '' ? ($atts['show_dots'] === 'yes' ? 1 : 0) : (isset($carousel_settings['show_dots']) ? $carousel_settings['show_dots'] : 1);
        $autoplay = $atts['autoplay'] !== '' ? ($atts['autoplay'] === 'yes' ? 1 : 0) : (isset($carousel_settings['autoplay']) ? $carousel_settings['autoplay'] : 0);
        $autoplay_speed = isset($carousel_settings['autoplay_speed']) ? absint($carousel_settings['autoplay_speed']) : 5000;
        
        // Override title if set in shortcode
        if (!empty($atts['title'])) {
            $settings['carousel']['title_text'] = $atts['title'];
        }
        
        // Enqueue assets
        wp_enqueue_style('ekwa-bag-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap', array(), null);
        wp_enqueue_style('ekwa-bag-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0');
        wp_enqueue_style('ekwa-bag-carousel', EKWA_BAG_PLUGIN_URL . 'assets/css/carousel.css', array(), EKWA_BAG_VERSION);
        wp_enqueue_script('ekwa-bag-carousel', EKWA_BAG_PLUGIN_URL . 'assets/js/carousel.js', array(), EKWA_BAG_VERSION, true);
        
        // Pass data via inline script for this instance
        $carousel_instance_id = $carousel_instance;
        
        // Custom card template
        $custom_template_enabled = isset($carousel_settings['custom_card_template_enabled']) ? $carousel_settings['custom_card_template_enabled'] : 0;
        $custom_card_template = isset($carousel_settings['custom_card_template']) ? $carousel_settings['custom_card_template'] : '';
        
        $json_data = json_encode(array(
            'cases'           => $carousel_cases,
            'categories'      => $categories,
            'slidesToShow'    => $per_page_desktop,
            'slidesTablet'    => $per_page_tablet,
            'slidesMobile'    => $per_page_mobile,
            'showArrows'      => $show_arrows ? '1' : '0',
            'showDots'        => $show_dots ? '1' : '0',
            'autoplay'        => $autoplay ? '1' : '0',
            'autoplaySpeed'   => $autoplay_speed,
            'showLabels'      => $show_labels,
            'showCategoryFilter' => $show_category_filter ? '1' : '0',
            'customTemplateEnabled' => $custom_template_enabled ? '1' : '0',
            'customCardTemplate'    => $custom_card_template,
        ));
        
        wp_add_inline_script('ekwa-bag-carousel', 'var ekwaBagCarousel_' . $carousel_instance_id . ' = ' . $json_data . ';', 'before');
        
        ob_start();
        include EKWA_BAG_PLUGIN_DIR . 'includes/frontend/carousel-template.php';
        return ob_get_clean();
    }
    
    /**
     * Detect category slug from the current page slug
     */
    private function detect_category_from_page_slug() {
        global $post;
        
        if (!is_a($post, 'WP_Post')) {
            return '';
        }
        
        $page_slug = $post->post_name;
        
        if (empty($page_slug)) {
            return '';
        }
        
        // Check if there's a taxonomy term matching this page slug
        $term = get_term_by('slug', $page_slug, 'ekwa_bag_category');
        if ($term && !is_wp_error($term)) {
            return $term->slug;
        }
        
        // Also try matching partial slugs (e.g., page "teeth-whitening-results" -> term "teeth-whitening")
        $all_terms = get_terms(array(
            'taxonomy'   => 'ekwa_bag_category',
            'hide_empty' => false,
        ));
        
        if (!is_wp_error($all_terms)) {
            foreach ($all_terms as $term) {
                // Check if page slug contains the term slug
                if (strpos($page_slug, $term->slug) !== false) {
                    return $term->slug;
                }
            }
        }
        
        return '';
    }
    
    /**
     * Get cases data for frontend
     */
    private function get_cases_data($atts = array()) {
        $args = array(
            'post_type'      => 'ekwa_bag_case',
            'post_status'    => 'publish',
            'posts_per_page' => isset($atts['limit']) ? intval($atts['limit']) : -1,
            'orderby'        => 'menu_order date',
            'order'          => 'ASC',
        );
        
        if (!empty($atts['category'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'ekwa_bag_category',
                    'field'    => 'slug',
                    'terms'    => explode(',', $atts['category']),
                )
            );
        }
        
        $query = new WP_Query($args);
        $cases = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                // Get categories
                $terms = get_the_terms($post_id, 'ekwa_bag_category');
                $mainCat = '';
                $subCat = '';
                
                if ($terms && !is_wp_error($terms)) {
                    foreach ($terms as $term) {
                        if ($term->parent == 0) {
                            $mainCat = $term->slug;
                        } else {
                            $subCat = $term->slug;
                            // If only subcategory is assigned, get the parent as mainCat
                            if (empty($mainCat)) {
                                $parent_term = get_term($term->parent, 'ekwa_bag_category');
                                if ($parent_term && !is_wp_error($parent_term)) {
                                    $mainCat = $parent_term->slug;
                                }
                            }
                        }
                    }
                }
                
                // Get image sets
                $image_sets = get_post_meta($post_id, '_ekwa_bag_image_sets', true);
                $sets = array();
                
                if (!empty($image_sets) && is_array($image_sets)) {
                    foreach ($image_sets as $set) {
                        // Check if this is a single combined image or separate before/after
                        if (isset($set['single_image'])) {
                            // Single combined image mode - use same image for both before and after
                            $single_id = absint($set['single_image']);
                            
                            if ($single_id) {
                                $is_watermarked = $this->watermark ? $this->watermark->is_watermarked($single_id) : false;
                                
                                error_log(sprintf(
                                    'EKWA Gallery Single Image Check: ID=%d (watermarked=%s)',
                                    $single_id,
                                    $is_watermarked ? 'YES' : 'NO'
                                ));
                                
                                // Use watermarked URL if available
                                $image_url = $this->watermark ? $this->watermark->get_display_url($single_id, 'large') : null;
                                if (!$image_url) {
                                    $image_url = wp_get_attachment_image_url($single_id, 'large');
                                    if (!$image_url) $image_url = wp_get_attachment_image_url($single_id, 'full');
                                }
                                
                                if ($image_url) {
                                    $image_alt = get_post_meta($single_id, '_wp_attachment_image_alt', true);
                                    $image_meta = wp_get_attachment_metadata($single_id);
                                    
                                    $image_width = $image_height = '';
                                    if (!empty($image_meta['sizes']['large'])) {
                                        $image_width = $image_meta['sizes']['large']['width'];
                                        $image_height = $image_meta['sizes']['large']['height'];
                                    } elseif (!empty($image_meta['width']) && !empty($image_meta['height'])) {
                                        $image_width = $image_meta['width'];
                                        $image_height = $image_meta['height'];
                                    }
                                    
                                    // Use the single combined image
                                    $sets[] = array(
                                        'before' => $image_url,
                                        'after'  => $image_url,
                                        'beforeAlt' => $image_alt ?: get_the_title($single_id),
                                        'afterAlt' => $image_alt ?: get_the_title($single_id),
                                        'beforeWidth' => $image_width,
                                        'beforeHeight' => $image_height,
                                        'afterWidth' => $image_width,
                                        'afterHeight' => $image_height,
                                        'isCombined' => true,
                                    );
                                    
                                    error_log(sprintf(
                                        'EKWA Gallery Single Image Data: URL=%s (alt=%s, %dx%d)',
                                        $image_url,
                                        $image_alt ?: 'none',
                                        $image_width,
                                        $image_height
                                    ));
                                }
                            }
                        } else {
                            // Separate before/after images mode
                            $before_id = isset($set['before']) ? absint($set['before']) : 0;
                            $after_id = isset($set['after']) ? absint($set['after']) : 0;
                        
                        if ($before_id && $after_id) {
                            // Debug watermark status
                            $before_is_watermarked = $this->watermark ? $this->watermark->is_watermarked($before_id) : false;
                            $after_is_watermarked = $this->watermark ? $this->watermark->is_watermarked($after_id) : false;
                            
                            error_log(sprintf(
                                'EKWA Gallery Image Check: Before ID=%d (watermarked=%s), After ID=%d (watermarked=%s)',
                                $before_id,
                                $before_is_watermarked ? 'YES' : 'NO',
                                $after_id,
                                $after_is_watermarked ? 'YES' : 'NO'
                            ));
                            
                            // Use watermarked URLs if available (with cache busting)
                            $before_url = $this->watermark ? $this->watermark->get_display_url($before_id, 'large') : null;
                            if (!$before_url) {
                                $before_url = wp_get_attachment_image_url($before_id, 'large');
                                if (!$before_url) $before_url = wp_get_attachment_image_url($before_id, 'full');
                            }

                            $after_url = $this->watermark ? $this->watermark->get_display_url($after_id, 'large') : null;
                            if (!$after_url) {
                                $after_url = wp_get_attachment_image_url($after_id, 'large');
                                if (!$after_url) $after_url = wp_get_attachment_image_url($after_id, 'full');
                            }
                            
                            error_log(sprintf(
                                'EKWA Gallery URLs: Before=%s, After=%s',
                                $before_url,
                                $after_url
                            ));
                            
                            if ($before_url && $after_url) {
                                // Get image metadata for alt text and dimensions
                                $before_alt = get_post_meta($before_id, '_wp_attachment_image_alt', true);
                                $after_alt = get_post_meta($after_id, '_wp_attachment_image_alt', true);
                                $before_meta = wp_get_attachment_metadata($before_id);
                                $after_meta = wp_get_attachment_metadata($after_id);
                                
                                // Get dimensions for the 'large' size (or fall back to full size)
                                $before_width = $before_height = $after_width = $after_height = '';
                                
                                if (!empty($before_meta['sizes']['large'])) {
                                    $before_width = $before_meta['sizes']['large']['width'];
                                    $before_height = $before_meta['sizes']['large']['height'];
                                } elseif (!empty($before_meta['width']) && !empty($before_meta['height'])) {
                                    $before_width = $before_meta['width'];
                                    $before_height = $before_meta['height'];
                                }
                                
                                if (!empty($after_meta['sizes']['large'])) {
                                    $after_width = $after_meta['sizes']['large']['width'];
                                    $after_height = $after_meta['sizes']['large']['height'];
                                } elseif (!empty($after_meta['width']) && !empty($after_meta['height'])) {
                                    $after_width = $after_meta['width'];
                                    $after_height = $after_meta['height'];
                                }
                                
                                $sets[] = array(
                                    'before' => $before_url,
                                    'after'  => $after_url,
                                    'beforeAlt' => $before_alt ?: get_the_title($before_id),
                                    'afterAlt' => $after_alt ?: get_the_title($after_id),
                                    'beforeWidth' => $before_width,
                                    'beforeHeight' => $before_height,
                                    'afterWidth' => $after_width,
                                    'afterHeight' => $after_height,
                                );
                                
                                // Debug log
                                error_log(sprintf(
                                    'EKWA Gallery Set Data: Before=%s (alt=%s, %dx%d), After=%s (alt=%s, %dx%d)',
                                    $before_url,
                                    $before_alt ?: 'none',
                                    $before_width,
                                    $before_height,
                                    $after_url,
                                    $after_alt ?: 'none',
                                    $after_width,
                                    $after_height
                                ));
                            }
                        }
                        } // End of else block for separate before/after images
                    }
                }
                
                // Only include cases that have at least one valid image set
                if (!empty($sets)) {
                    $cases[] = array(
                        'id'      => $post_id,
                        'title'   => get_the_title(),
                        'mainCat' => $mainCat ?: 'uncategorized',
                        'subCat'  => $subCat,
                        'desc'    => wp_strip_all_tags(get_the_content()),
                        'sets'    => $sets,
                    );
                }
            }
            wp_reset_postdata();
        }
        
        return $cases;
    }
    
    /**
     * Debug helper - get info about all cases
     */
    private function get_debug_info() {
        $debug = array(
            'function_called' => true,
            'timestamp' => current_time('mysql'),
        );
        
        $args = array(
            'post_type'      => 'ekwa_bag_case',
            'post_status'    => 'any',
            'posts_per_page' => -1,
        );
        
        $query = new WP_Query($args);
        
        $debug['query_args'] = $args;
        $debug['total_posts'] = $query->found_posts;
        $debug['posts'] = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $image_sets_raw = get_post_meta($post_id, '_ekwa_bag_image_sets', true);
                
                // Try to get image URLs
                $processed_sets = array();
                if (!empty($image_sets_raw) && is_array($image_sets_raw)) {
                    foreach ($image_sets_raw as $set) {
                        $before_id = isset($set['before']) ? absint($set['before']) : 0;
                        $after_id = isset($set['after']) ? absint($set['after']) : 0;
                        $before_url = $before_id ? wp_get_attachment_image_url($before_id, 'large') : false;
                        $after_url = $after_id ? wp_get_attachment_image_url($after_id, 'large') : false;
                        
                        $processed_sets[] = array(
                            'before_id' => $before_id,
                            'after_id' => $after_id,
                            'before_url' => $before_url,
                            'after_url' => $after_url,
                        );
                    }
                }
                
                $debug['posts'][] = array(
                    'id'              => $post_id,
                    'title'           => get_the_title(),
                    'status'          => get_post_status(),
                    'image_sets_raw'  => $image_sets_raw,
                    'processed_sets'  => $processed_sets,
                );
            }
            wp_reset_postdata();
        }
        
        return $debug;
    }
    
    /**
     * Get categories tree for frontend
     */
    private function get_categories_tree() {
        $tree = array(
            'all' => array(
                'label'   => __('All', 'ekwa-before-after-gallery'),
                'subCats' => array()
            )
        );
        
        $parent_terms = get_terms(array(
            'taxonomy'   => 'ekwa_bag_category',
            'hide_empty' => false,
            'parent'     => 0,
        ));
        
        if (!is_wp_error($parent_terms)) {
            foreach ($parent_terms as $parent) {
                $children = get_terms(array(
                    'taxonomy'   => 'ekwa_bag_category',
                    'hide_empty' => false,
                    'parent'     => $parent->term_id,
                ));
                
                $subCats = array();
                if (!is_wp_error($children)) {
                    foreach ($children as $child) {
                        $subCats[] = array(
                            'key'   => $child->slug,
                            'label' => $child->name,
                        );
                    }
                }
                
                $tree[$parent->slug] = array(
                    'label'   => $parent->name,
                    'subCats' => $subCats,
                );
            }
        }
        
        return $tree;
    }
    
    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'ekwa_bag_image_sets',
            __('Before & After Image Sets', 'ekwa-before-after-gallery'),
            array($this, 'render_image_sets_meta_box'),
            'ekwa_bag_case',
            'normal',
            'high'
        );
    }
    
    /**
     * Render image sets meta box
     */
    public function render_image_sets_meta_box($post) {
        wp_nonce_field('ekwa_bag_save_meta', 'ekwa_bag_meta_nonce');
        $image_sets = get_post_meta($post->ID, '_ekwa_bag_image_sets', true);
        
        // Get the pair_as_one_image setting
        $settings = get_option('ekwa_bag_settings', array());
        $pair_as_one_image = isset($settings['pair_as_one_image']) ? $settings['pair_as_one_image'] : 0;
        
        if (empty($image_sets)) {
            if ($pair_as_one_image) {
                $image_sets = array(array('single_image' => ''));
            } else {
                $image_sets = array(array('before' => '', 'after' => ''));
            }
        }
        include EKWA_BAG_PLUGIN_DIR . 'includes/admin/meta-box-image-sets.php';
    }
    
    /**
     * Save meta boxes
     */
    public function save_meta_boxes($post_id) {
        // Verify nonce
        if (!isset($_POST['ekwa_bag_meta_nonce']) || !wp_verify_nonce($_POST['ekwa_bag_meta_nonce'], 'ekwa_bag_save_meta')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save image sets
        if (isset($_POST['ekwa_bag_image_sets'])) {
            $settings = get_option('ekwa_bag_settings', array());
            $pair_as_one_image = isset($settings['pair_as_one_image']) ? $settings['pair_as_one_image'] : 0;
            
            $image_sets = array();
            foreach ($_POST['ekwa_bag_image_sets'] as $set) {
                if ($pair_as_one_image) {
                    // Single combined image mode
                    if (!empty($set['single_image'])) {
                        $image_sets[] = array(
                            'single_image' => absint($set['single_image']),
                        );
                    }
                } else {
                    // Separate before/after images mode
                    if (!empty($set['before']) || !empty($set['after'])) {
                        $image_sets[] = array(
                            'before' => absint($set['before']),
                            'after'  => absint($set['after']),
                        );
                    }
                }
            }
            update_post_meta($post_id, '_ekwa_bag_image_sets', $image_sets);
            
            // Auto-apply watermarks if enabled - run directly
            if ($this->watermark && $this->watermark->is_enabled()) {
                $this->watermark->apply_watermarks_to_case($post_id);
            }
        }
    }
    
    /**
     * Auto-apply watermarks to case images
     */
    public function auto_watermark_case($post_id) {
        if ($this->watermark && $this->watermark->is_enabled()) {
            $this->watermark->apply_watermarks_to_case($post_id);
        }
    }
    
    /**
     * AJAX handler to get cases
     */
    public function ajax_get_cases() {
        check_ajax_referer('ekwa_bag_frontend_nonce', 'nonce');
        
        $cases = $this->get_cases_data();
        $categories = $this->get_categories_tree();
        
        wp_send_json_success(array(
            'cases'      => $cases,
            'categories' => $categories,
        ));
    }
    
    /**
     * Output dynamic CSS based on settings
     */
    public function output_dynamic_css() {
        global $post;
        $has_gallery = is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'ekwa_gallery');
        $has_carousel = is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'ekwa_category_carousel');
        
        if (!$has_gallery && !$has_carousel) {
            return;
        }
        
        $settings = get_option('ekwa_bag_settings', array());
        $defaults = array(
            'color_bg'          => '#f5f3f0',
            'color_card_bg'     => '#ffffff',
            'color_text'        => '#1a1a1a',
            'color_text_soft'   => '#777777',
            'color_accent'      => '#c9a87c',
            'color_accent_dark' => '#b08d5b',
            'color_border'      => '#e8e4df',
            'cards_per_row'     => 3,
        );
        $settings = wp_parse_args($settings, $defaults);
        
        ?>
        <style id="ekwa-bag-dynamic-css">
            .ekwa-bag-wrapper {
                --ekwa-bg: <?php echo esc_attr($settings['color_bg']); ?>;
                --ekwa-card-bg: <?php echo esc_attr($settings['color_card_bg']); ?>;
                --ekwa-text: <?php echo esc_attr($settings['color_text']); ?>;
                --ekwa-text-soft: <?php echo esc_attr($settings['color_text_soft']); ?>;
                --ekwa-accent: <?php echo esc_attr($settings['color_accent']); ?>;
                --ekwa-accent-dark: <?php echo esc_attr($settings['color_accent_dark']); ?>;
                --ekwa-border: <?php echo esc_attr($settings['color_border']); ?>;
            }
            .ekwa-bag-card-grid {
                grid-template-columns: repeat(<?php echo absint($settings['cards_per_row']); ?>, 1fr);
            }
            .ekwa-bag-carousel-wrapper {
                --ekwa-bg: <?php echo esc_attr($settings['color_bg']); ?>;
                --ekwa-card-bg: <?php echo esc_attr($settings['color_card_bg']); ?>;
                --ekwa-text: <?php echo esc_attr($settings['color_text']); ?>;
                --ekwa-text-soft: <?php echo esc_attr($settings['color_text_soft']); ?>;
                --ekwa-accent: <?php echo esc_attr($settings['color_accent']); ?>;
                --ekwa-accent-dark: <?php echo esc_attr($settings['color_accent_dark']); ?>;
                --ekwa-border: <?php echo esc_attr($settings['color_border']); ?>;
            }
        </style>
        <?php
    }
    
    /**
     * AJAX handler for bulk watermark
     */
    public function ajax_bulk_watermark() {
        check_ajax_referer('ekwa_bag_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'ekwa-before-after-gallery')));
        }
        
        // Reload settings before bulk operation
        $this->watermark->reload_settings();
        
        // Check if watermark is properly configured
        if (!$this->watermark->is_enabled()) {
            wp_send_json_error(array('message' => __('Watermark is not enabled. Please enable it in settings first.', 'ekwa-before-after-gallery')));
        }
        
        $results = $this->watermark->bulk_apply_watermarks();
        
        // Include detailed error info
        if (!empty($results['errors'])) {
            $results['error_details'] = $results['errors'][0]['error'] ?? 'Unknown error';
        }
        
        wp_send_json_success($results);
    }
    
    /**
     * AJAX handler for removing watermarks
     */
    public function ajax_remove_watermarks() {
        check_ajax_referer('ekwa_bag_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'ekwa-before-after-gallery')));
        }
        
        $results = $this->watermark->bulk_remove_watermarks();
        
        wp_send_json_success($results);
    }
    
    /**
     * AJAX handler for exporting settings
     */
    public function ajax_export_settings() {
        check_ajax_referer('ekwa_bag_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'ekwa-before-after-gallery')));
        }
        
        $settings = get_option('ekwa_bag_settings', array());
        
        wp_send_json_success(array(
            'settings' => $settings,
            'version'  => EKWA_BAG_VERSION,
            'exported' => current_time('mysql'),
        ));
    }
    
    /**
     * AJAX handler for importing settings
     */
    public function ajax_import_settings() {
        check_ajax_referer('ekwa_bag_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'ekwa-before-after-gallery')));
        }
        
        $import_data = isset($_POST['import_data']) ? json_decode(stripslashes($_POST['import_data']), true) : null;
        
        if (!$import_data || !isset($import_data['settings'])) {
            wp_send_json_error(array('message' => __('Invalid import file.', 'ekwa-before-after-gallery')));
        }
        
        // Sanitize imported settings
        $settings = array();
        $allowed_keys = array(
            'color_bg', 'color_card_bg', 'color_text', 'color_text_soft',
            'color_accent', 'color_accent_dark', 'color_border',
            'gallery_title', 'gallery_subtitle', 'cards_per_page',
            'enable_lightbox', 'enable_lazy_load', 'image_quality',
            'watermark_enabled', 'watermark_type', 'watermark_text',
            'watermark_image', 'watermark_position', 'watermark_opacity',
            'watermark_size', 'watermark_color', 'watermark_padding',
        );
        
        foreach ($allowed_keys as $key) {
            if (isset($import_data['settings'][$key])) {
                if (strpos($key, 'color') !== false) {
                    $settings[$key] = sanitize_hex_color($import_data['settings'][$key]);
                } elseif (in_array($key, array('watermark_enabled', 'enable_lightbox', 'enable_lazy_load', 'watermark_image', 'cards_per_page', 'image_quality', 'watermark_opacity', 'watermark_size', 'watermark_padding'))) {
                    $settings[$key] = absint($import_data['settings'][$key]);
                } else {
                    $settings[$key] = sanitize_text_field($import_data['settings'][$key]);
                }
            }
        }
        
        update_option('ekwa_bag_settings', $settings);
        
        wp_send_json_success(array('message' => __('Settings imported successfully.', 'ekwa-before-after-gallery')));
    }
    
    /**
     * AJAX handler for testing watermark configuration
     */
    public function ajax_test_watermark() {
        check_ajax_referer('ekwa_bag_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'ekwa-before-after-gallery')));
        }
        
        $this->watermark->reload_settings();
        $settings = $this->watermark->get_settings();
        
        $status = array(
            'library'     => $this->watermark->get_library() ?: 'none',
            'is_available' => $this->watermark->is_available(),
            'is_enabled'  => $this->watermark->is_enabled(),
            'is_configured' => $this->watermark->is_configured(),
            'type'        => $settings['watermark_type'],
            'has_text'    => !empty($settings['watermark_text']),
            'has_image'   => !empty($settings['watermark_image']),
            'text'        => $settings['watermark_text'],
            'image_id'    => $settings['watermark_image'],
        );
        
        wp_send_json_success($status);
    }
    
    /**
     * AJAX handler for clearing and reapplying watermarks
     */
    public function ajax_clear_and_reapply() {
        check_ajax_referer('ekwa_bag_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'ekwa-before-after-gallery')));
        }
        
        // Get all gallery images
        $image_ids = $this->watermark->get_gallery_image_ids();
        
        // Clear all watermark metadata first
        foreach ($image_ids as $id) {
            $this->watermark->remove_watermark($id);
        }
        
        // Now apply watermarks fresh
        $this->watermark->reload_settings();
        $results = $this->watermark->bulk_apply_watermarks($image_ids);
        
        // Include detailed error info
        if (!empty($results['errors'])) {
            $results['error_details'] = $results['errors'][0]['error'] ?? 'Unknown error';
        }
        
        wp_send_json_success($results);
    }
    
    /**
     * Add watermark column to media library
     */
    public function add_watermark_column($columns) {
        $columns['watermark'] = __('Watermark', 'ekwa-before-after-gallery');
        return $columns;
    }
    
    /**
     * Show watermark status in media library
     */
    public function show_watermark_column($column_name, $attachment_id) {
        if ($column_name === 'watermark') {
            if ($this->watermark->is_watermarked($attachment_id)) {
                $info = $this->watermark->get_watermark_info($attachment_id);
                if ($info['watermarked_exists']) {
                    echo '<span style="color: #46b450;">✓ Watermarked</span><br>';
                    echo '<small><a href="' . esc_url(wp_get_attachment_url($attachment_id)) . '" target="_blank">Original</a> | ';
                    $wm_url = str_replace(wp_upload_dir()['basedir'], wp_upload_dir()['baseurl'], $info['watermarked_path']);
                    echo '<a href="' . esc_url($wm_url) . '" target="_blank">Watermarked</a></small>';
                } else {
                    echo '<span style="color: #dc3232;">✗ Missing file</span>';
                }
            } else {
                echo '<span style="color: #999;">—</span>';
            }
        }
    }
}

// Initialize plugin
function ekwa_before_after_gallery_init() {
    return EKWA_Before_After_Gallery::get_instance();
}
add_action('plugins_loaded', 'ekwa_before_after_gallery_init');

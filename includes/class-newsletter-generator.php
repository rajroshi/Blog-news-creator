<?php
namespace WeeklyPostNewsletter;

if (!defined('ABSPATH')) {
    exit;
}

class NewsletterGenerator {
    private $settings_manager;

    public function __construct() {
        $this->settings_manager = new SettingsManager();
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_generate_newsletter', array($this, 'generate_newsletter'));
        add_action('wp_ajax_save_newsletter_settings', array($this, 'save_settings'));
    }

    public function generate_newsletter() {
        // Prevent any output before our JSON response
        ob_clean();
        
        try {
            if (!check_ajax_referer('wpn_generate_newsletter', 'nonce', false)) {
                throw new \Exception('Security check failed');
            }

            $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : 'Weekly Newsletter';
            $logo = isset($_POST['logo']) ? esc_url_raw($_POST['logo']) : '';
            $footer_text = isset($_POST['footer_text']) ? wp_kses_post($_POST['footer_text']) : '';
            $social_links = isset($_POST['social_links']) ? array_map('esc_url_raw', (array)$_POST['social_links']) : array();
            
            $collector = new PostCollector();
            $posts = $collector->get_weekly_posts();

            if (empty($posts)) {
                throw new \Exception('No posts found for the last week');
            }

            // Capture the template output
            ob_start();
            include WPN_PLUGIN_DIR . 'templates/email-template.php';
            $html = ob_get_clean();

            if (empty($html)) {
                throw new \Exception('Failed to generate newsletter content');
            }

            // Send JSON response
            header('Content-Type: application/json');
            echo json_encode(array(
                'success' => true,
                'data' => array(
                    'html' => $html,
                    'message' => 'Newsletter generated successfully!',
                    'posts_count' => count($posts)
                )
            ));
            exit;

        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(array(
                'success' => false,
                'data' => array(
                    'message' => $e->getMessage()
                )
            ));
            exit;
        }
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Weekly Newsletter', 'weekly-post-newsletter'),
            __('Weekly Newsletter', 'weekly-post-newsletter'),
            'manage_options',
            'weekly-post-newsletter',
            array($this, 'render_admin_page'),
            'dashicons-email-alt',
            30
        );
    }

    public function enqueue_admin_assets($hook) {
        if ($hook !== 'toplevel_page_weekly-post-newsletter') {
            return;
        }

        // Enqueue WordPress media uploader
        wp_enqueue_media();

        wp_enqueue_style(
            'wpn-admin-style',
            WPN_PLUGIN_URL . 'assets/css/newsletter-style.css',
            array(),
            WPN_VERSION
        );

        wp_enqueue_script(
            'wpn-admin-script',
            WPN_PLUGIN_URL . 'assets/js/admin-script.js',
            array('jquery'),
            WPN_VERSION,
            true
        );

        wp_localize_script('wpn-admin-script', 'wpnAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpn_generate_newsletter')
        ));
    }

    public function render_admin_page() {
        $settings = $this->settings_manager->get_settings();
        include WPN_PLUGIN_DIR . 'templates/admin-page.php';
    }

    public function save_settings() {
        try {
            if (!check_ajax_referer('wpn_generate_newsletter', 'nonce', false)) {
                throw new \Exception('Security check failed');
            }

            $settings = array(
                'logo' => isset($_POST['logo']) ? esc_url_raw($_POST['logo']) : '',
                'footer_text' => isset($_POST['footer_text']) ? wp_kses_post($_POST['footer_text']) : '',
                'social_links' => isset($_POST['social_links']) ? array_map('esc_url_raw', $_POST['social_links']) : array()
            );

            $this->settings_manager->save_settings($settings);

            wp_send_json_success(array(
                'message' => 'Settings saved successfully!'
            ));

        } catch (\Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage()
            ));
        }
    }
} 
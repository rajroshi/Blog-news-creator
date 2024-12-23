<?php
/**
 * Plugin Name: Blog News Creator
 * Plugin URI: https://github.com/rajroshi/Blog-news-creator
 * Description: A powerful WordPress plugin that automatically generates beautiful HTML newsletters from your recent blog posts. Features include logo customization, footer editing, social media integration, and live preview.
 * Version: 0.9.0
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author: Rajesh Benjwal
 * Author URI: https://tantragurukul.org
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: weekly-post-newsletter
 * Domain Path: /languages
 * Update URI: https://github.com/rajroshi/Blog-news-creator
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WPN_VERSION', '0.9.0');
define('WPN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPN_PLUGIN_URL', plugin_dir_url(__FILE__));

// Auto-update functionality
require_once WPN_PLUGIN_DIR . 'includes/class-plugin-updater.php';

// Include required files
require_once WPN_PLUGIN_DIR . 'includes/class-settings-manager.php';
require_once WPN_PLUGIN_DIR . 'includes/class-post-collector.php';
require_once WPN_PLUGIN_DIR . 'includes/class-newsletter-generator.php';

// Initialize plugin
function wpn_init() {
    try {
        // Initialize updater
        new WeeklyPostNewsletter\PluginUpdater([
            'plugin' => plugin_basename(__FILE__),
            'github_url' => 'https://github.com/rajroshi/Blog-news-creator',
            'zip_url' => 'https://github.com/rajroshi/Blog-news-creator/archive/refs/tags/'
            // access_token is now optional
        ]);

        new WeeklyPostNewsletter\NewsletterGenerator();
    } catch (Exception $e) {
        error_log('Newsletter Plugin Error: ' . $e->getMessage());
    }
}

add_action('plugins_loaded', 'wpn_init'); 
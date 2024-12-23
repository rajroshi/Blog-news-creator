<?php
/**
 * Plugin Name: Weekly Post Newsletter
 * Plugin URI: https://yourwebsite.com/weekly-post-newsletter
 * Description: Automatically generates beautiful HTML newsletters from weekly blog posts
 * Version: 1.0.0-beta
 * Author: Rajesh Benjwal
 * Author URI: https://yourwebsite.com
 * Text Domain: weekly-post-newsletter
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WPN_VERSION', '1.0.0-beta');
define('WPN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPN_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once WPN_PLUGIN_DIR . 'includes/class-settings-manager.php';
require_once WPN_PLUGIN_DIR . 'includes/class-post-collector.php';
require_once WPN_PLUGIN_DIR . 'includes/class-newsletter-generator.php';

// Initialize plugin
function wpn_init() {
    try {
        new WeeklyPostNewsletter\NewsletterGenerator();
    } catch (Exception $e) {
        error_log('Newsletter Plugin Error: ' . $e->getMessage());
    }
}

add_action('plugins_loaded', 'wpn_init'); 
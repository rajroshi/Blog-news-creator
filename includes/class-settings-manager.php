<?php
namespace WeeklyPostNewsletter;

if (!defined('ABSPATH')) {
    exit;
}

class SettingsManager {
    private $option_name = 'wpn_newsletter_settings';

    public function save_settings($settings) {
        return update_option($this->option_name, $settings);
    }

    public function get_settings() {
        $defaults = array(
            'logo' => '',
            'footer_text' => 'You received this email because you\'re subscribed to our weekly newsletter.',
            'social_links' => array(
                'facebook' => '',
                'twitter' => '',
                'instagram' => ''
            )
        );

        $saved_settings = get_option($this->option_name, array());
        return wp_parse_args($saved_settings, $defaults);
    }
} 
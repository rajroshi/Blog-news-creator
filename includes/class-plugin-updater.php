<?php
namespace WeeklyPostNewsletter;

if (!defined('ABSPATH')) {
    exit;
}

class PluginUpdater {
    private $plugin;
    private $github_url;
    private $zip_url;
    private $access_token;
    private $plugin_data;
    private $plugin_basename;

    public function __construct($config = array()) {
        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        $this->plugin = $config['plugin'];
        $this->plugin_basename = plugin_basename($this->plugin);
        $this->github_url = $config['github_url'];
        $this->zip_url = $config['zip_url'];
        $this->access_token = $config['access_token'];
        $this->plugin_data = \get_plugin_data(WP_PLUGIN_DIR . '/' . $this->plugin);

        // Hook into the plugin update system
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));
        add_filter('plugins_api', array($this, 'plugin_popup'), 10, 3);
        add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);
        
        // Add a custom update message
        add_action('in_plugin_update_message-' . $this->plugin_basename, array($this, 'show_upgrade_notification'), 10, 2);
        
        // Force WordPress to check for updates
        $this->force_update_check();
    }

    private function force_update_check() {
        // Delete the transient to force a fresh update check
        delete_site_transient('update_plugins');
    }

    public function check_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $remote_version = $this->get_remote_version();
        
        if ($remote_version && version_compare($remote_version, $this->plugin_data['Version'], '>')) {
            $response = new \stdClass();
            $response->slug = dirname($this->plugin_basename);
            $response->plugin = $this->plugin_basename;
            $response->new_version = $remote_version;
            $response->url = $this->github_url;
            $response->package = $this->zip_url . 'v' . $remote_version . '.zip';
            $response->tested = '6.4'; // Update this as needed
            
            $transient->response[$this->plugin_basename] = $response;
        }

        return $transient;
    }

    private function get_remote_version() {
        // Cache the result for 12 hours
        $cache_key = 'wpn_github_version';
        $version = get_transient($cache_key);

        if (false === $version) {
            $response = wp_remote_get($this->github_url . '/releases/latest', array(
                'headers' => array(
                    'Accept' => 'application/json',
                    'Authorization' => $this->access_token ? 'token ' . $this->access_token : '',
                ),
                'sslverify' => true,
            ));

            if (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
                $data = json_decode(wp_remote_retrieve_body($response));
                if (isset($data->tag_name)) {
                    $version = ltrim($data->tag_name, 'v');
                    set_transient($cache_key, $version, 12 * HOUR_IN_SECONDS);
                }
            }
        }

        return $version;
    }

    public function plugin_popup($result, $action, $args) {
        if ($action !== 'plugin_information' || !isset($args->slug) || $args->slug !== dirname($this->plugin_basename)) {
            return $result;
        }

        $response = wp_remote_get($this->github_url . '/releases/latest', array(
            'headers' => array(
                'Accept' => 'application/json',
                'Authorization' => $this->access_token ? 'token ' . $this->access_token : '',
            ),
        ));

        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response));
            if ($data) {
                $info = new \stdClass();
                $info->name = $this->plugin_data['Name'];
                $info->slug = dirname($this->plugin_basename);
                $info->version = ltrim($data->tag_name, 'v');
                $info->author = $this->plugin_data['Author'];
                $info->author_profile = $this->plugin_data['AuthorURI'];
                $info->last_updated = $data->published_at;
                $info->homepage = $this->github_url;
                $info->requires = '5.0';
                $info->tested = '6.4';
                $info->downloaded = 0;
                $info->banners = array(
                    'high' => '',
                    'low' => ''
                );
                $info->sections = array(
                    'description' => $this->plugin_data['Description'],
                    'changelog' => $data->body,
                );

                return $info;
            }
        }

        return $result;
    }

    public function after_install($response, $hook_extra, $result) {
        global $wp_filesystem;

        $plugin_folder = WP_PLUGIN_DIR . '/' . dirname($this->plugin_basename);
        $wp_filesystem->move($result['destination'], $plugin_folder);
        $result['destination'] = $plugin_folder;

        return $result;
    }

    public function show_upgrade_notification($current_plugin_metadata, $new_plugin_metadata) {
        // Check if there's a release note
        $response = wp_remote_get($this->github_url . '/releases/latest');
        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response));
            if (isset($data->body)) {
                echo '<br><br>' . wp_kses_post($data->body);
            }
        }
    }
} 
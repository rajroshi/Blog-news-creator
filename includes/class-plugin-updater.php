<?php
namespace WeeklyPostNewsletter;

if (!defined('ABSPATH')) {
    exit;
}

class PluginUpdater {
    private $plugin;
    private $github_url;
    private $zip_url;
    private $plugin_data;
    private $plugin_basename;
    private $cache_key;

    public function __construct($config = array()) {
        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        $this->plugin = $config['plugin'];
        $this->plugin_basename = plugin_basename($this->plugin);
        $this->github_url = 'https://api.github.com/repos/rajroshi/Blog-news-creator';
        $this->zip_url = 'https://github.com/rajroshi/Blog-news-creator/archive/';
        $this->plugin_data = \get_plugin_data(WP_PLUGIN_DIR . '/' . $this->plugin);
        $this->cache_key = 'wpn_github_version_' . md5($this->plugin_basename);

        // Hook into the plugin update system
        add_filter('site_transient_update_plugins', array($this, 'check_update'));
        add_filter('transient_update_plugins', array($this, 'check_update'));
        add_filter('plugins_api', array($this, 'plugin_popup'), 10, 3);
        add_filter('upgrader_source_selection', array($this, 'upgrader_source_selection'), 10, 4);
        add_filter('plugin_row_meta', array($this, 'add_check_update_link'), 10, 2);
        
        // Add AJAX handler
        add_action('wp_ajax_check_plugin_updates', array($this, 'handle_manual_check'));
    }

    public function check_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        // Get the remote version
        $remote_version = $this->get_remote_version();
        $current_version = $this->plugin_data['Version'];

        if ($remote_version && version_compare($remote_version, $current_version, '>')) {
            $response = new \stdClass();
            $response->slug = dirname($this->plugin_basename);
            $response->plugin = $this->plugin_basename;
            $response->new_version = $remote_version;
            $response->url = str_replace('api.', '', $this->github_url);
            $response->package = $this->zip_url . 'v' . $remote_version . '.zip';
            $response->tested = '6.4';
            
            $transient->response[$this->plugin_basename] = $response;
            
            // Force clear cache
            wp_clean_plugins_cache(true);
        }

        return $transient;
    }

    private function get_remote_version() {
        $version = get_transient($this->cache_key);

        if (false === $version) {
            $raw_response = wp_remote_get($this->github_url . '/releases/latest', array(
                'headers' => array(
                    'Accept' => 'application/vnd.github.v3+json'
                )
            ));

            if (!is_wp_error($raw_response) && 200 === wp_remote_retrieve_response_code($raw_response)) {
                $response = json_decode(wp_remote_retrieve_body($raw_response));
                if (isset($response->tag_name)) {
                    $version = ltrim($response->tag_name, 'v');
                    set_transient($this->cache_key, $version, HOUR_IN_SECONDS);
                }
            }
        }

        return $version;
    }

    public function plugin_popup($result, $action, $args) {
        if ($action !== 'plugin_information' || !isset($args->slug) || $args->slug !== dirname($this->plugin_basename)) {
            return $result;
        }

        $remote_info = wp_remote_get($this->github_url . '/releases/latest');

        if (!is_wp_error($remote_info)) {
            $remote_info = json_decode(wp_remote_retrieve_body($remote_info));
            if ($remote_info) {
                $info = new \stdClass();
                $info->name = 'Blog News Creator';
                $info->slug = dirname($this->plugin_basename);
                $info->version = ltrim($remote_info->tag_name, 'v');
                $info->author = 'Rajesh Benjwal';
                $info->homepage = str_replace('api.', '', $this->github_url);
                $info->requires = '5.0';
                $info->tested = '6.4';
                $info->downloaded = 0;
                $info->last_updated = $remote_info->published_at;
                $info->sections = array(
                    'description' => $this->plugin_data['Description'],
                    'changelog' => $remote_info->body
                );
                $info->download_link = $this->zip_url . $remote_info->tag_name . '.zip';

                return $info;
            }
        }

        return $result;
    }

    public function upgrader_source_selection($source, $remote_source, $upgrader, $hook_extra) {
        global $wp_filesystem;

        if (isset($hook_extra['plugin']) && $hook_extra['plugin'] === $this->plugin_basename) {
            $source_files = $wp_filesystem->dirlist($source);
            if (count($source_files) === 1) {
                $first_directory = key($source_files);
                $source = trailingslashit($source) . $first_directory;
                $new_source = trailingslashit($remote_source) . dirname($this->plugin_basename);

                // Remove existing directory if it exists
                if ($wp_filesystem->exists($new_source)) {
                    $wp_filesystem->delete($new_source, true);
                }

                // Move to the correct location
                if ($wp_filesystem->move($source, $new_source)) {
                    return $new_source;
                }
            }
        }

        return $source;
    }

    public function add_check_update_link($links, $file) {
        if ($file === $this->plugin_basename) {
            $links[] = '<a href="#" class="check-update-link" data-plugin="' . esc_attr($this->plugin_basename) . '">' . __('Check for updates', 'weekly-post-newsletter') . '</a>';
            
            add_action('admin_footer', function() {
                ?>
                <script>
                jQuery(document).ready(function($) {
                    $('.check-update-link').on('click', function(e) {
                        e.preventDefault();
                        var $link = $(this);
                        $link.text('Checking...');
                        
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'check_plugin_updates',
                                plugin: $link.data('plugin'),
                                nonce: '<?php echo wp_create_nonce('check_plugin_updates'); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    location.reload();
                                } else {
                                    $link.text('Check for updates');
                                    alert('Failed to check for updates. Please try again.');
                                }
                            },
                            error: function() {
                                $link.text('Check for updates');
                                alert('Failed to check for updates. Please try again.');
                            }
                        });
                    });
                });
                </script>
                <?php
            });
        }
        return $links;
    }

    public function handle_manual_check() {
        check_ajax_referer('check_plugin_updates');
        
        // Clear all caches
        delete_site_transient('update_plugins');
        delete_transient($this->cache_key);
        wp_clean_plugins_cache(true);
        
        wp_send_json_success();
    }
} 
<div class="wrap wpn-admin">
    <h1 class="wpn-title">
        <span class="dashicons dashicons-email-alt"></span>
        <?php _e('Weekly Newsletter Generator', 'weekly-post-newsletter'); ?>
    </h1>

    <div class="wpn-notices"></div>

    <div class="wpn-admin-container">
        <!-- Settings Panel -->
        <div class="wpn-settings-panel">
            <form id="wpn-generate-form">
                <!-- Newsletter Basic Settings -->
                <div class="wpn-settings-section">
                    <h2><?php _e('Newsletter Settings', 'weekly-post-newsletter'); ?></h2>
                    
                    <div class="wpn-field-group">
                        <label for="wpn-title"><?php _e('Newsletter Title', 'weekly-post-newsletter'); ?></label>
                        <input type="text" id="wpn-title" name="title" 
                               value="<?php echo esc_attr(get_bloginfo('name')); ?> - Weekly Newsletter" 
                               class="regular-text">
                    </div>

                    <!-- Logo Upload -->
                    <div class="wpn-field-group">
                        <label for="wpn-logo"><?php _e('Newsletter Logo', 'weekly-post-newsletter'); ?></label>
                        <div class="wpn-logo-upload">
                            <div class="wpn-logo-preview" <?php echo $settings['logo'] ? 'style="background-image: url(' . esc_url($settings['logo']) . ')"' : ''; ?>></div>
                            <input type="hidden" id="wpn-logo" name="logo" value="<?php echo esc_attr($settings['logo']); ?>">
                            <button type="button" class="button button-secondary wpn-upload-logo">
                                <?php _e('Upload Logo', 'weekly-post-newsletter'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer Settings -->
                <div class="wpn-settings-section">
                    <h2><?php _e('Footer Settings', 'weekly-post-newsletter'); ?></h2>
                    
                    <div class="wpn-field-group">
                        <label for="wpn-footer-text"><?php _e('Footer Text', 'weekly-post-newsletter'); ?></label>
                        <textarea id="wpn-footer-text" name="footer_text" rows="3" class="large-text"><?php 
                            echo esc_textarea($settings['footer_text']); 
                        ?></textarea>
                    </div>

                    <div class="wpn-field-group">
                        <label for="wpn-social-links"><?php _e('Social Links', 'weekly-post-newsletter'); ?></label>
                        <div class="wpn-social-links">
                            <input type="url" name="social_facebook" placeholder="Facebook URL" class="regular-text" 
                                   value="<?php echo esc_url($settings['social_links']['facebook']); ?>">
                            <input type="url" name="social_twitter" placeholder="Twitter URL" class="regular-text"
                                   value="<?php echo esc_url($settings['social_links']['twitter']); ?>">
                            <input type="url" name="social_instagram" placeholder="Instagram URL" class="regular-text"
                                   value="<?php echo esc_url($settings['social_links']['instagram']); ?>">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="wpn-actions">
                    <button type="submit" class="button button-primary button-large">
                        <span class="dashicons dashicons-email"></span>
                        <?php _e('Generate Newsletter', 'weekly-post-newsletter'); ?>
                    </button>
                    <button type="button" class="button button-secondary button-large wpn-preview-toggle">
                        <span class="dashicons dashicons-visibility"></span>
                        <?php _e('Toggle Preview', 'weekly-post-newsletter'); ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Preview Panel -->
        <div class="wpn-preview-panel">
            <h2>
                <span class="dashicons dashicons-visibility"></span>
                <?php _e('Newsletter Preview', 'weekly-post-newsletter'); ?>
            </h2>
            <div id="wpn-preview-content"></div>
        </div>
    </div>
</div> 
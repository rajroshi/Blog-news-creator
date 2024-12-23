jQuery(document).ready(function($) {
    // Media Uploader
    let mediaUploader;
    let isPreviewMode = true;
    
    $('.wpn-upload-logo').on('click', function(e) {
        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: 'Select Newsletter Logo',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        mediaUploader.on('select', function() {
            const attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#wpn-logo').val(attachment.url);
            $('.wpn-logo-preview').css('background-image', `url(${attachment.url})`);
        });

        mediaUploader.open();
    });

    // Auto-save settings when changed
    let saveTimeout;
    $('.wpn-settings-panel').on('change', 'input, textarea', function() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(saveSettings, 1000);
    });

    function saveSettings() {
        const settings = {
            action: 'save_newsletter_settings',
            nonce: wpnAjax.nonce,
            logo: $('#wpn-logo').val(),
            footer_text: $('#wpn-footer-text').val(),
            social_links: {
                facebook: $('input[name="social_facebook"]').val(),
                twitter: $('input[name="social_twitter"]').val(),
                instagram: $('input[name="social_instagram"]').val()
            }
        };

        $.ajax({
            url: wpnAjax.ajaxurl,
            type: 'POST',
            data: settings,
            success: function(response) {
                if (response.success) {
                    // Show a subtle notification
                    const $notice = $('<div class="notice notice-success is-dismissible"><p>Settings saved</p></div>')
                        .hide()
                        .insertAfter('.wpn-title')
                        .slideDown();
                    
                    setTimeout(() => {
                        $notice.slideUp(function() {
                            $(this).remove();
                        });
                    }, 2000);
                }
            }
        });
    }

    // Toggle Preview/HTML
    $('.wpn-preview-toggle').on('click', function() {
        const $preview = $('#wpn-preview-content');
        const $button = $(this);
        const html = $preview.html();
        
        if (isPreviewMode) {
            // Switch to HTML view
            const $textarea = $('<textarea>', {
                class: 'wpn-html-view',
                style: 'width: 100%; height: 500px; font-family: monospace; padding: 10px;'
            }).val(html);
            
            $preview.html($textarea);
            $button.html('<span class="dashicons dashicons-visibility"></span> Show Preview');
        } else {
            // Switch back to preview
            const htmlContent = $preview.find('textarea').val();
            $preview.html(htmlContent);
            $button.html('<span class="dashicons dashicons-html"></span> Show HTML');
        }
        
        isPreviewMode = !isPreviewMode;
    });

    // Form Submission
    $('#wpn-generate-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitButton = $form.find('button[type="submit"]');
        const $preview = $('#wpn-preview-content');
        const $notices = $('.wpn-notices');
        
        $notices.empty();
        $submitButton.prop('disabled', true)
            .find('.dashicons').addClass('dashicons-update-alt spin');
        
        const formData = {
            action: 'generate_newsletter',
            nonce: wpnAjax.nonce,
            title: $('#wpn-title').val(),
            logo: $('#wpn-logo').val(),
            footer_text: $('#wpn-footer-text').val(),
            social_links: {
                facebook: $('input[name="social_facebook"]').val(),
                twitter: $('input[name="social_twitter"]').val(),
                instagram: $('input[name="social_instagram"]').val()
            }
        };

        $.ajax({
            url: wpnAjax.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: formData,
            success: function(response) {
                if (response.success) {
                    if (isPreviewMode) {
                        $preview.html(response.data.html);
                    } else {
                        const $textarea = $('<textarea>', {
                            class: 'wpn-html-view',
                            readonly: 'readonly',
                            style: 'width: 100%; height: 500px; font-family: monospace; padding: 10px;'
                        }).val(response.data.html);
                        $preview.html($textarea);
                    }
                    
                    $notices.html(
                        `<div class="notice notice-success is-dismissible">
                            <p>${response.data.message}</p>
                            <p>Found ${response.data.posts_count} posts</p>
                            <button class="button button-primary copy-html">
                                <span class="dashicons dashicons-clipboard"></span>
                                Copy HTML
                            </button>
                        </div>`
                    );
                } else {
                    $notices.html(
                        `<div class="notice notice-error is-dismissible">
                            <p>Error: ${response.data.message}</p>
                        </div>`
                    );
                }
            },
            error: function(xhr, status, error) {
                $notices.html(
                    `<div class="notice notice-error is-dismissible">
                        <p>Server Error: ${error}</p>
                    </div>`
                );
            },
            complete: function() {
                $submitButton.prop('disabled', false)
                    .find('.dashicons').removeClass('dashicons-update-alt spin');
            }
        });
    });

    // Copy HTML functionality
    $(document).on('click', '.copy-html', function() {
        const $button = $(this);
        const html = isPreviewMode ? 
            $('#wpn-preview-content').html() : 
            $('#wpn-preview-content textarea').val();
        
        navigator.clipboard.writeText(html).then(function() {
            $button.html('<span class="dashicons dashicons-yes"></span> Copied!');
            setTimeout(function() {
                $button.html('<span class="dashicons dashicons-clipboard"></span> Copy HTML');
            }, 2000);
        });
    });
}); 
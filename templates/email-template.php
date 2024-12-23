<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($title); ?></title>
    <style>
        /* Reset styles */
        body {
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        
        /* Container */
        .newsletter-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
        }
        
        /* Header */
        .newsletter-header {
            text-align: center;
            padding: 20px;
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            margin-bottom: 30px;
        }
        
        .newsletter-header h1 {
            color: #2c3338;
            font-size: 24px;
            margin: 0;
            padding: 0;
        }
        
        /* Post styles */
        .post {
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        
        .post:last-child {
            border-bottom: none;
        }
        
        .post-image {
            width: 100%;
            height: auto;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .post-title {
            color: #2c3338;
            font-size: 20px;
            margin: 0 0 10px 0;
            text-decoration: none;
        }
        
        .post-meta {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .post-excerpt {
            margin-bottom: 15px;
            color: #555;
            font-size: 16px;
        }
        
        .read-more {
            background: #0073aa;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 3px;
            display: inline-block;
            font-size: 14px;
        }
        
        /* Footer */
        .newsletter-footer {
            text-align: center;
            padding: 20px;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            margin-top: 30px;
            font-size: 14px;
            color: #666;
        }
        
        .newsletter-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .newsletter-logo img {
            max-width: 200px;
            height: auto;
        }
        
        .social-links {
            text-align: center;
            margin: 20px 0;
        }
        
        .social-links a {
            margin: 0 10px;
            color: #666;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="newsletter-container">
        <?php if (!empty($logo)): ?>
        <div class="newsletter-logo">
            <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
        </div>
        <?php endif; ?>

        <div class="newsletter-header">
            <h1><?php echo esc_html($title); ?></h1>
            <p><?php echo date('F j, Y'); ?></p>
        </div>
        
        <?php foreach ($posts as $post): ?>
            <?php $meta = (new \WeeklyPostNewsletter\PostCollector())->get_post_meta_data($post); ?>
            <div class="post">
                <?php if ($meta['thumbnail']): ?>
                    <img class="post-image" src="<?php echo esc_url($meta['thumbnail']); ?>" alt="">
                <?php endif; ?>
                
                <h2 class="post-title"><?php echo esc_html($meta['title']); ?></h2>
                
                <div class="post-meta">
                    By <?php echo esc_html($meta['author']); ?> | 
                    <?php echo esc_html($meta['date']); ?> | 
                    <?php echo wp_kses_post($meta['categories']); ?>
                </div>
                
                <div class="post-excerpt">
                    <?php echo wp_kses_post($meta['excerpt']); ?>
                </div>
                
                <a href="<?php echo esc_url($meta['permalink']); ?>" class="read-more">
                    Read More
                </a>
            </div>
        <?php endforeach; ?>
        
        <div class="newsletter-footer">
            <?php if (!empty($footer_text)): ?>
                <p><?php echo wp_kses_post($footer_text); ?></p>
            <?php endif; ?>

            <?php if (!empty($social_links)): ?>
            <div class="social-links">
                <?php if (!empty($social_links['facebook'])): ?>
                    <a href="<?php echo esc_url($social_links['facebook']); ?>">Facebook</a>
                <?php endif; ?>
                
                <?php if (!empty($social_links['twitter'])): ?>
                    <a href="<?php echo esc_url($social_links['twitter']); ?>">Twitter</a>
                <?php endif; ?>
                
                <?php if (!empty($social_links['instagram'])): ?>
                    <a href="<?php echo esc_url($social_links['instagram']); ?>">Instagram</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 
</html> 
<?php
namespace WeeklyPostNewsletter;

class PostCollector {
    public function get_weekly_posts($args = array()) {
        $default_args = array(
            'post_type' => 'post',
            'posts_per_page' => -1,
            'date_query' => array(
                array(
                    'after' => '1 week ago'
                )
            ),
            'orderby' => 'date',
            'order' => 'DESC'
        );

        $query_args = wp_parse_args($args, $default_args);
        return get_posts($query_args);
    }

    public function get_post_meta_data($post) {
        return array(
            'title' => get_the_title($post),
            'excerpt' => get_the_excerpt($post),
            'author' => get_the_author_meta('display_name', $post->post_author),
            'date' => get_the_date('F j, Y', $post),
            'thumbnail' => get_the_post_thumbnail_url($post, 'medium'),
            'categories' => get_the_category_list(', ', '', $post->ID),
            'permalink' => get_permalink($post)
        );
    }
} 
<?php
/* Front-page placement rules wrapper. Original template is preserved in front-page-original.php. */
if (!defined('ABSPATH')) exit;

if (function_exists('orh_artist_public_ids')) {
    add_action('pre_get_posts', function ($query) {
        if (is_admin() || !$query->is_main_query() && !$query->is_front_page()) return;

        $post_type = $query->get('post_type');
        if ($post_type === 'orh_song' && $query->get('meta_key') === 'orh_plays' && (int)$query->get('posts_per_page') === 5) {
            $song_ids = get_posts([
                'post_type' => 'orh_song',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'orderby' => 'date',
                'order' => 'DESC',
                'no_found_rows' => true,
            ]);
            $eligible = [];
            foreach ($song_ids as $song_id) {
                $artist_id = (int)get_post_meta($song_id, 'orh_artist_id', true);
                if ($artist_id && orh_artist_level_public($artist_id)) $eligible[] = (int)$song_id;
            }
            $query->set('post__in', $eligible ?: [0]);
        }

        if ($post_type === 'orh_artist') {
            $query->set('post__in', orh_artist_public_ids() ?: [0]);
        }
    }, 30);
}

$original = get_theme_file_path('front-page-original.php');
if (file_exists($original)) include $original;

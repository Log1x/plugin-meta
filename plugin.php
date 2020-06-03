<?php

/**
 * Plugin Name: Plugin Meta
 * Plugin URI:  https://github.com/log1x/plugin-meta
 * Description: A simple meta package for my commonly used WordPress plugins
 * Version:     1.0.0
 * Author:      Brandon Nifong
 * Author URI:  https://github.com/log1x
 * Licence:     MIT
 */

add_action('plugins_loaded', new class
{
    /**
     * Invoke the plugin.
     *
     * @return void
     */
    public function __invoke()
    {
        /**
         * Disable unnecessary ACF Extended modules.
         *
         * @return boolean
         */
        foreach([
            'acf/settings/acfe/modules/dynamic_post_types',
            'acf/settings/acfe/modules/dynamic_taxonomies',
            'acf/settings/acfe/modules/dynamic_forms',
            'acf/settings/acfe/modules/dynamic_options_pages',
            'acf/settings/acfe/modules/dynamic_block_types',
            'acf/settings/acfe/modules/author',
            'acf/settings/acfe/modules/taxonomies',
            'acf/settings/acfe/modules/options',
            'acf/settings/acfe/dev'
        ] as $filter) {
            add_filter($filter, '__return_false', 100);
        };

        /**
         * Remove the EditorsKit and CoBlocks getting started screens.
         *
         * @return bool
         */
        foreach(['blockopts_welcome_cap', 'coblocks_getting_started_screen_capability'] as $filter) {
            add_filter($filter, '__return_false', 100);
        }

        /**
         * Move User Activity to Settings -> Activity
         *
         * @param  array $default
         * @return array
         */
        add_filter('wp_user_activity_get_post_type_args', function ($default) {
            return array_merge($default, [
                'labels' => ['all_items' => 'Activity'] + $default['labels'],
                'show_in_menu' => 'options-general.php'
            ]);
        });

        /**
         * Fix the role capability for Better Search Replace.
         *
         * @return string
         */
        add_filter('bsr_capability', function () {
            return 'manage_options';
        });
        
        /**
         * Make WP User Profiles stop being naughty.
         *
         * @return void
         */
        remove_filter('wp_user_profiles_show_other_section', 'wp_user_profiles_has_profile_actions');

        /**
         * Remove metaboxes created by Related Posts and Yoast.
         *
         * @return void
         */
        add_action('do_meta_boxes', function () {
            remove_meta_box('rp4wp_metabox_related_posts', get_post_types(), 'normal');
            remove_meta_box('rp4wp_metabox_exclude_post', get_post_types(), 'side');
            remove_meta_box('yoast_internal_linking', get_post_types(), 'side');
        });

        /**
         * Lower The SEO Framework's Metabox Priority
         *
         * @return string
         */
        add_filter('the_seo_framework_metabox_priority', function () {
            return 'low';
        });

        /**
         * Lower WordPress SEO's Metabox Priority
         *
         * @return string
         */
        add_filter('wpseo_metabox_prio', function () {
            return 'low';
        });

        /**
         * Remove Gutenberg's admin menu item.
         *
         * @return void
         */
        remove_filter('admin_menu', 'gutenberg_menu');
    }
});

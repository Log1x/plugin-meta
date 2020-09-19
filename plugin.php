<?php

/**
 * Plugin Name: Plugin Meta
 * Plugin URI:  https://github.com/log1x/plugin-meta
 * Description: A simple meta package for my commonly used WordPress plugins
 * Version:     1.1.3
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
            'acf/settings/acfe/modules/single_meta',
        ] as $hook) {
            add_filter($hook, '__return_false');
        };

        add_filter('init', function () {
            unregister_taxonomy('acf-field-group-category');
        }, 100);

        /**
         * Enable good ACF Extended modules.
         *
         * @return bool
         */
        foreach([
            'acf/settings/acfe/modules/ui',
            'acf/settings/acfe/modules/multilang',
        ] as $hook) {
            add_filter($hook, '__return_true');
        };

        /**
         * Remove the EditorsKit and CoBlocks getting started screens.
         *
         * @return bool
         */
        foreach(['blockopts_welcome_cap', 'coblocks_getting_started_screen_capability'] as $filter) {
            add_filter($hook, '__return_false');
        }

        /**
         * Remove the UpdraftPlus admin bar item.
         *
         * @return void
         */
        add_filter('init', function () {
            if (defined('UPDRAFTPLUS_ADMINBAR_DISABLE')) {
                return;
            }

            define('UPDRAFTPLUS_ADMINBAR_DISABLE', true);
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
         * Remove metaboxes created by Related Posts for WordPress.
         *
         * @return void
         */
        add_filter('do_meta_boxes', function () {
            remove_meta_box('rp4wp_metabox_related_posts', get_post_types(), 'normal');
            remove_meta_box('rp4wp_metabox_exclude_post', get_post_types(), 'side');
        });

        /**
         * Disable unwanted default functionality of Related Posts for WordPress.
         *
         * @return void
         */
        add_filter('rp4wp_append_content', '__return_false');
        add_filter('rp4wp_disable_css', '__return_true');

        /**
         * Remove metaboxes created by Pretty Links.
         *
         * @return void
         */
        add_filter('wp_dashboard_setup', function () {
            remove_meta_box('prli_dashboard_widget', 'dashboard', 'normal');
            remove_meta_box('prli_dashboard_widget', 'dashboard', 'side');
        });

        /**
         * Change the WordPress login header to the blog name
         *
         * @return string
         */
        add_filter('login_headertext', function () {
            return get_bloginfo('name');
        });

        /**
         * Change the WordPress login header URL to the home URL
         *
         * @return string
         */
        add_filter('login_headerurl', function () {
            return get_home_url();
        });

        /**
         * Remove Gutenberg's admin menu item.
         *
         * @return void
         */
        remove_filter('admin_menu', 'gutenberg_menu');

        /**
         * Remove the WP Rocket option metabox.
         *
         * @return void
         */
        add_filter('admin_init', function () {
            remove_filter('add_meta_boxes', 'rocket_cache_options_meta_boxes');
        });

        /**
         * Deregister useless plugin widgets.
         *
         * @return void
         */
        add_filter('widgets_init', function () {
            foreach([
                'Su_Widget',
                'RP4WP_Related_Posts_Widget'
            ] as $widget) {
                unregister_widget($widget);
            }
        });

        /**
         * Remove failed login logging from Simple History.
         *
         * @param  bool   $logged
         * @param  string $slug
         * @param  string $key
         * @param  int    $level
         * @param  string $context
         * @return bool
         */
        add_filter('simple_history/simple_logger/log_message_key', function ($logged, $slug, $key, $level, $context) {
            if ($this->contains($slug, 'SimpleUserLogger') && $this->contains($key, ['user_login_failed', 'user_unknown_login_failed'])) {
                return false;
            }

            return $logged;
        }, 10, 5);
    }

    /**
     * Determine if a given string contains a given substring.
     *
     * @param  string  $haystack
     * @param  string|string[]  $needles
     * @return bool
     */
    public function contains($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
});

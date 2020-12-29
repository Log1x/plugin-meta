<?php

/**
 * Plugin Name: Plugin Meta
 * Plugin URI:  https://github.com/log1x/plugin-meta
 * Description: A simple meta package for my commonly used WordPress plugins
 * Version:     1.1.9
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
         * Process shortcodes inside of titles.
         *
         * @return string
         */
        add_filter('the_title', 'do_shortcode');
        add_filter('wpseo_title', 'do_shortcode');

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

        /**
         * Remove the post actions created by Page Generator Pro.
         *
         * @param  array $actions
         * @return array
         */
        foreach([
            'post_row_actions',
            'page_row_actions',
        ] as $hook) {
            add_filter($hook, function ($actions) {
                if (! empty($actions['page_generator_pro_import'])) {
                    unset($actions['page_generator_pro_import']);
                }

                return $actions;
            }, 100);
        }

        /**
         * Remove the blog snippet schema added to the homepage by RankMath.
         *
         * @param  array $data
         * @return array
         */
        add_filter('rank_math/json_ld', function ($data) {
            if (is_home() && ! empty($data['Blog'])) {
                unset($data['Blog']);
            }

            return $data;
        });

        /**
         * Disable primary category terms.
         *
         * @return bool
         */
        add_filter('rank_math/admin/disable_primary_term', '__return_true');

        /**
         * Disable RankMath's whitelabeling.
         *
         * @return bool
         */
        foreach([
            'rank_math/whitelabel',
            'rank_math/link/remove_class',
            'rank_math/sitemap/remove_credit',
            'rank_math/frontend/remove_credit_notice',
        ] as $hook) {
            add_filter($hook, '__return_true');
        }

        /**
         * Register the defined Google Maps API key with ACF.
         *
         * @return void
         */
        add_filter('acf/init', function () {
            if (! defined('GOOGLE_MAPS_API_KEY') || ! function_exists('acf_update_setting')) {
                return;
            }

            acf_update_setting('google_api_key', GOOGLE_MAPS_API_KEY);
        });
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

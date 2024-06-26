<?php

/**
 * Plugin Name: Plugin Meta
 * Plugin URI:  https://github.com/log1x/plugin-meta
 * Description: A simple meta package for my commonly used WordPress plugins
 * Version:     1.3.5
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
            'acf/settings/acfe/modules/author',
            'acf/settings/acfe/modules/block_types',
            'acf/settings/acfe/modules/categories',
            'acf/settings/acfe/modules/forms',
            'acf/settings/acfe/modules/options_pages',
            'acf/settings/acfe/modules/options',
            'acf/settings/acfe/modules/performance',
            'acf/settings/acfe/modules/post_types',
            'acf/settings/acfe/modules/taxonomies',
        ] as $hook) {
            add_filter($hook, '__return_false');
        };

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
         * Remove metaboxes created by Pretty Links.
         *
         * @return void
         */
        add_filter('do_meta_boxes', function () {
            remove_meta_box('pretty-links-sidebar', get_post_types(), 'side');
        });

        /**
         * Remove editor styles added by Pretty Links.
         *
         * @return void
         */
        add_filter('admin_enqueue_scripts', function () {
            wp_dequeue_script('pretty-link-richtext-format');
        }, 100);

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
         * Process shortcodes inside of titles, descriptions, and open graph data.
         *
         * @return string
         */
        foreach([
            'the_title',
            'get_the_title',
            'wpseo_title',
            'wpseo_metadesc',
            'wpseo_opengraph_title',
            'wpseo_opengraph_desc',
            'wpseo_opengraph_site_name',
            'wpseo_twitter_title',
            'wpseo_twitter_description'
        ] as $hook) {
            add_filter($hook, 'do_shortcode');
        }

        /**
         * Process shortcodes inside of WordPress SEO's schema.
         *
         * @param  string[] $data
         * @return string[]
         */
        foreach(['wpseo_schema_webpage', 'wpseo_schema_article'] as $hook) {
            add_filter($hook, function ($data) {
                return array_map(function ($item) {
                    return is_string($item) ? do_shortcode($item) : $item;
                }, $data);
            });
        }

        /**
         * Hide version on WordPress SEO's HTML output.
         *
         * @return bool
         */
        add_filter('wpseo_hide_version', '__return_true');

        /**
         * Remove MonsterInsights scroll tracking.
         *
         * @return void
         */
        add_filter('after_setup_theme', function () {
            remove_action('wp_footer', 'monsterinsights_scroll_tracking_output_after_script', 11);
        });

        /**
         * Remove the WP Rocket option metabox.
         *
         * @return void
         */
        add_filter('admin_init', function () {
            remove_filter('add_meta_boxes', 'rocket_cache_options_meta_boxes');
        });

        /**
         * Remove Find My Blocks' reusable blocks admin menu hook.
         *
         * @return void
         */
        remove_filter('admin_menu', 'find_my_blocks_add_reusable_to_admin_menu');

        /**
         * Disable the widget block editor.
         *
         * @return bool
         */
        add_filter('gutenberg_use_widgets_block_editor', '__return_false');

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
         * Remove the block editor formatting created by Page Generator Pro.
         *
         * @return void
         */
        add_filter('admin_enqueue_scripts', function () {
            wp_dequeue_script('page-generator-pro-gutenberg-block-formatters');
        }, 100);

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

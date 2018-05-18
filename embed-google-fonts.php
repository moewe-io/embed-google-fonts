<?php

/*
 * Plugin Name: Embed Google Fonts
 * Plugin URI: https://github.com/moewe-io/embed-google-fonts
 * Description: Helper plugin for embedding Google fonts.
 * Version: 1.2.6
 * Author: MOEWE
 * Author URI: https://www.moewe.io/
 * Text Domain: embed-google-fonts
 */

define('EMBED_GOOGLE_FONTS_VERSION', '1.2.6');

class Embed_Google_Fonts {

    function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue'], 0);
        add_action('wp_enqueue_scripts', [$this, 'replace_queued_sources'], PHP_INT_MAX);

        add_filter('embed_google_fonts_get_slug', [$this, 'get_slug'], 10, 1);
        add_filter('embed_google_fonts_get_handle', [$this, 'get_handle'], 10, 1);
    }


    function enqueue() {
        /** @var WP_Filesystem_Base $wp_filesystem */
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once(ABSPATH . '/wp-admin/includes/file.php');
            WP_Filesystem();
        }

        $base_url = plugins_url('/fonts/', __FILE__);
        $fonts = $wp_filesystem->dirlist(plugin_dir_path(__FILE__) . '/fonts', false, false);

        foreach ($fonts as $font) {
            $handle = apply_filters('embed_google_fonts_get_handle', $font['name']);
            wp_register_style($handle, $base_url . $font['name'] . '/_font.css', false, EMBED_GOOGLE_FONTS_VERSION);
        }
    }

    function replace_queued_sources() {
        $wp_styles = wp_styles();
        /** @var _WP_Dependency $dependency */
        foreach ($wp_styles->registered as $key => $dependency) {
            // Example https://fonts.googleapis.com/css?family=Lato:300
            if (strpos($dependency->src, 'fonts.googleapis.com') === false) {
                continue;
            }
            $query = wp_parse_url($dependency->src, PHP_URL_QUERY);
            $query = wp_parse_args($query, array());
            $families = explode('|', $query['family']);
            foreach ($families as $family) {
                $family = explode(':', $family)[0];
                $slug = apply_filters('embed_google_fonts_get_slug', $family);
                if (is_file(plugin_dir_path(__FILE__) . '/fonts/' . $slug . '/_font.css')) {
                    $handle = apply_filters('embed_google_fonts_get_handle', $family);
                    wp_enqueue_style($handle);
                } else {
                    error_log('Missing font family: ' . $family);
                }
            }
            // Remove original Google font from styles
            $wp_styles->remove($key);
            $wp_styles->dequeue($key);
        }
    }

    function get_slug($name = '') {
        return strtolower(str_replace(' ', '-', $name));
    }

    function get_handle($name = '') {
        return 'embed-google-fonts-' . apply_filters('embed_google_fonts_get_slug', $name);
    }
}

new Embed_Google_Fonts();

// specific theme and plugin support
include 'includes/avada.php';
include 'includes/memorable.php';

// Updates
require 'libs/plugin-update-checker-4.4/plugin-update-checker.php';
Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/moewe-io/embed-google-fonts/',
    __FILE__,
    'embed-google-fonts'
)->setBranch('master');
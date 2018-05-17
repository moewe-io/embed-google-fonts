<?php

/*
 * Plugin Name: Embed Google Fonts
 * Plugin URI: https://github.com/moewe-io/embed-google-fonts
 * Description: Helper plugin for embedding Google fonts.
 * Version: 1.1.1
 * Author: MOEWE
 * Author URI: https://www.moewe.io/
 * Text Domain: embed-google-fonts
 */


class Embed_Google_Fonts {
    /** @var array Name => Version */
    private $embedded_fonts = array(
        'Lato'                => '14',
        'Open Sans'           => '15',
        'Open Sans Condensed' => '12'
    );

    function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue'], 0);
        add_action('wp_enqueue_scripts', [$this, 'replace_queued_sources'], PHP_INT_MAX);

        add_filter('embed_google_fonts_get_slug', [$this, 'get_slug'], 10, 1);
        add_filter('embed_google_fonts_get_handle', [$this, 'get_handle'], 10, 1);
    }


    function enqueue() {
        $base_url = plugins_url() . '/embed-google-fonts/fonts/';

        foreach ($this->embedded_fonts as $name => $version) {
            $slug = apply_filters('embed_google_fonts_get_slug', $name);
            $handle = apply_filters('embed_google_fonts_get_handle', $name);
            wp_register_style($handle, $base_url . $slug . '/_font.css', array(), $version);
        }
    }

    function replace_queued_sources() {
        $wp_styles = wp_styles();
        /** @var _WP_Dependency $dependency */
        foreach ($wp_styles->registered as $key => $dependency) {
            // Example https://fonts.googleapis.com/css?family=Lato:300
            if (strpos($dependency->src, 'fonts.googleapis.com') !== false) {
                $query = wp_parse_url($dependency->src, PHP_URL_QUERY);
                $query = wp_parse_args($query, array());
                $family = explode(':', $query['family'])[0];
                if (array_key_exists($family, $this->embedded_fonts)) {
                    $wp_styles->remove($key);
                    $wp_styles->dequeue($key);
                    $handle = apply_filters('embed_google_fonts_get_handle', $family);
                    wp_enqueue_style($handle);
                } else {
                    error_log('Missing font family: ' . $family);
                }
            };
        }
    }

    function get_slug($name = '') {
        return strtolower(str_replace(' ', '-', $name));
    }

    function get_handle($name = '') {
        return 'embed-google-font-' . apply_filters('embed_google_fonts_get_slug', $name);
    }
}

new Embed_Google_Fonts();

// Updates
require 'libs/plugin-update-checker-4.4/plugin-update-checker.php';
Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/moewe-io/embed-google-fonts/',
    __FILE__,
    'embed-google-fonts'
)->setBranch('master');
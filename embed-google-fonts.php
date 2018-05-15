<?php

/*
 * Plugin Name: Embed Google Fonts
 * Plugin URI: https://github.com/moewe-io/embed-google-fonts
 * Description: Helper plugin for embedding Google fonts.
 * Version: 1.0.1
 * Author: MOEWE
 * Author URI: https://www.moewe.io/
 * Text Domain: embed-google-fonts
 */

add_action('wp_enqueue_scripts', 'embed_google_fonts_enqueue', 0);

function embed_google_fonts_enqueue() {
    $base_url = plugins_url() . '/replace-google-fonts/fonts/';
    wp_register_style('rgf-open-sans', $base_url . 'open-sans/open-sans.css', array(), '15');

    // Lucid Theme
    wp_enqueue_style('google_font_open_sans', $base_url . 'open-sans.css', array(), '15');
    wp_enqueue_style('google_font_open_sans_condensed', $base_url . 'open-sans.css', array(), '15');
}

/**
 * Replacement for Elegant Themes
 */
if (!function_exists('et_gf_enqueue_fonts')) {
    function et_gf_enqueue_fonts($et_gf_font_names) {
        foreach ($et_gf_font_names as $et_gf_font_name) {
            $et_gf_font_name_slug = strtolower(str_replace(' ', '-', $et_gf_font_name));
            wp_enqueue_style('rgf-' . $et_gf_font_name_slug);
        }
    }
}

// Updates
require 'libs/plugin-update-checker-4.4/plugin-update-checker.php';
Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/moewe-io/embed-google-fonts/',
    __FILE__,
    'embed-google-fonts'
)->setBranch('master');
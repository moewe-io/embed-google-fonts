<?php

/*
 * Plugin Name: Embed Google Fonts
 * Plugin URI: https://github.com/moewe-io/embed-google-fonts
 * Description: Helper plugin for embedding Google fonts.
 * Version: 2.0.4
 * Author: MOEWE
 * Author URI: https://www.moewe.io/
 * Text Domain: embed-google-fonts
 */

define('EMBED_GOOGLE_FONTS_VERSION', '2.0.4');

class Embed_Google_Fonts {

    function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'replace_queued_sources'], PHP_INT_MAX);
        add_action('wp_print_styles', [$this, 'replace_queued_sources'], PHP_INT_MAX);

        add_filter('embed_google_fonts_get_slug', [$this, 'get_slug'], 10, 1);
        add_filter('embed_google_fonts_get_handle', [$this, 'get_handle'], 10, 1);
    }

    function replace_queued_sources() {
        $wp_styles = wp_styles();
        $upload_dir = wp_upload_dir();
        $base_url = content_url('/cache/embed-google-fonts/');
        $base_path = dirname($upload_dir['basedir']) . '/cache/embed-google-fonts/';

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
                if (!is_file($base_path . $slug . '/_font.css')) {
                    $this->download_font($base_path, $slug);
                }
                $handle = apply_filters('embed_google_fonts_get_handle', $family);
                wp_enqueue_style($handle, $base_url . $slug . '/_font.css', false, filemtime($base_path . $slug . '/_font.css'));
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

    private function download_font($base_path, $slug) {
        $directory = $base_path . $slug . '/';
        if (!wp_mkdir_p($directory)) {
            error_log('Error creating needed directory: ' . $directory);
            return false;
        }
        $api_url = 'https://google-webfonts-helper.herokuapp.com/api/fonts/' . $slug;

        $response = wp_remote_get(add_query_arg(array(
            'subsets' => apply_filters('embed_google_fonts_get_subsets', 'devanagari,vietnamese,cyrillic-ext,latin,greek-ext,greek,cyrillic,latin-ext,hebrew'),
        ), $api_url));

        if (!is_array($response)) {
            /** @var WP_Error response */
            error_log($response->get_error_message());
            return false;
        }
        $font_definition = json_decode($response['body']); // use the content

        $download_url = add_query_arg(array(
            'download' => 'zip',
            'subsets'  => join(",", $font_definition->subsets),
        ), $api_url);

        $download_target = $directory . 'font.zip';

        // Download the fonts
        wp_remote_get($download_url, array(
            'timeout'  => 300,
            'stream'   => true,
            'filename' => $download_target
        ));

        $zip = new ZipArchive;
        $res = $zip->open($download_target);
        if ($res === true) {
            $zip->extractTo($directory);
            $zip->close();
            unlink($download_target);
        } else {
            error_log("error extracting font file");
            return false;
        }

        $css_file = $directory . '_font.css';
        $file = fopen($css_file, "w");
        try {
            if ($file == false) {
                error_log("Error in opening new file: " . $css_file);
                return false;
            }
            foreach ($font_definition->variants as $variant) {
                fwrite($file, '@font-face {');
                fwrite($file, 'font-family: ' . $variant->fontFamily . ';');
                fwrite($file, 'font-style: ' . $variant->fontStyle . ';');
                fwrite($file, 'font-weight: ' . $variant->fontWeight . ';');

                $font_prefix = $slug . '-' . $font_definition->version . '-' . $font_definition->storeID . '-';

                if ($variant->fontWeight == 400) {
                    if ($variant->fontStyle === 'italic') {
                        $font_prefix .= 'italic';
                    } else {
                        $font_prefix .= 'regular';
                    }
                } else {
                    $font_prefix .= $variant->fontWeight . ($variant->fontStyle === 'italic' ? 'italic' : '');
                }

                if (isset($variant->eot)) {
                    fwrite($file, 'src: url("' . $font_prefix . '.eot"); /* IE9 Compat Modes */');
                }
                fwrite($file, 'src:');

                foreach ($variant->local as $local) {
                    fwrite($file, 'local("' . $local . '"),');
                }

                $formats = array();
                foreach (['eot'  => '.eot?#iefix', 'woff2' => '.woff2',
                          'woff' => '.woff', 'truetype' => '.truetype',
                          'ttf'  => '.ttf',
                          'svg'  => '.svg#' . $font_definition->family] as $format => $extension) {
                    if (isset($variant->$format)) {
                        $formats[] = 'url("' . $font_prefix . $extension . '") format("' . $format . '")';
                    }
                }
                fwrite($file, join(',', $formats));
                fwrite($file, ';');
                fwrite($file, '}');
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        } finally {
            fclose($file);
        }
        return true;
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
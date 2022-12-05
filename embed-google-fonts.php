<?php

/*
 * Plugin Name: Embed Google Fonts
 * Plugin URI: https://github.com/moewe-io/embed-google-fonts
 * Description: Helper plugin for embedding Google fonts.
 * Version: 3.1.0
 * Requires at least: 6.0
 * Requires PHP: 7.3
 * Author: Adrian Mörchen + Contributors
 * Author URI: https://moerchen.io/
 * Text Domain: embed-google-fonts
 */

include "includes/class.embed-google-fonts-proxy.php";
include "includes/class.embed-google-fonts-administration.php";

new Embed_Google_Fonts_Proxy();
new Embed_Google_Fonts_Administration();

<?php
// Memorable theme (from Woothemes)
add_action( 'woo_head', function () {
    remove_action( 'woo_head', 'apply_custom_fonts', 10 );
    wp_enqueue_style( apply_filters( 'embed_google_fonts_get_handle', 'Merriweather Sans' ) );
}, 0 );

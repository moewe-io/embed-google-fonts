<?php

/**
 * We need Avada to use external and not inline styles for fonts
 */

add_filter('transient_avada_googlefonts_contents', function ($value, $transient) {
    return 'failed';
}, PHP_INT_MAX, 2);

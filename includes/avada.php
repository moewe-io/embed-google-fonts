<?php

// Since Avada 5.5.2 the theme itself can embed Google Fonts. You should use this feature instead.

/**
 * We need Avada to use external and not inline styles for fonts
 */

add_filter('transient_avada_googlefonts_contents', function ($value, $transient) {
    return 'failed';
}, PHP_INT_MAX, 2);

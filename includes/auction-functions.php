<?php


// Add common functions used throughout the plugin here
add_filter('template_include', 'auction_custom_template');
function auction_custom_template($template) {
    if (is_singular('auction')) {
        $new_template = plugin_dir_path(__FILE__) . 'templates/single-auction.php';
        if ('' !== $new_template) {
            return $new_template;
        }
    }
    return $template;
}


add_filter('template_include', 'auction_theme_override');
function auction_theme_override($template) {
    if (is_singular('auction')) {
        $new_template = locate_template(array('single-auction.php'));
        if ('' !== $new_template) {
            return $new_template;
        }
    }
    return $template;
}

<?php
/*
Plugin Name: Auction - Dingfaire
Description: Custom plugin for managing auctions.
Version: 1.0
Author: Your Name
*/

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/auction-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/auction-post-type.php';
require_once plugin_dir_path(__FILE__) . 'includes/auction-taxonomy.php';
require_once plugin_dir_path(__FILE__) . 'admin/auction-settings.php';

// Initialize the plugin
add_action('init', 'auction_dingfaire_init');
function auction_dingfaire_init() {
    // Initialize custom post type and taxonomy
    register_auction_post_type();
    register_auction_taxonomy();

    // Add other initialization actions here if needed
}

<?php
/*
Plugin Name: Auction - Dingfaire
Description: Custom plugin for managing auctions.
Version: 1.0
Author: Darkknight
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

// Enqueue CSS and JS files
add_action('wp_enqueue_scripts', 'auction_enqueue_scripts');
function auction_enqueue_scripts() {
    // Enqueue CSS file
    wp_enqueue_style('auction-styles', plugin_dir_url(__FILE__) . 'css/auction-styles.css');

    // Enqueue JS file with jQuery dependency
    wp_enqueue_script('auction-scripts', plugin_dir_url(__FILE__) . 'js/auction-scripts.js', array('jquery'), null, true);
}

// Rest of your plugin code...


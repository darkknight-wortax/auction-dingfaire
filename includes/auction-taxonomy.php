<?php
// Register custom taxonomy "Auction Type"
function register_auction_taxonomy() {
    $labels = array(
        'name' => 'Auction Types',
        'singular_name' => 'Auction Type',
        'search_items' => 'Search Auction Types',
        'all_items' => 'All Auction Types',
        'edit_item' => 'Edit Auction Type',
        'update_item' => 'Update Auction Type',
        'add_new_item' => 'Add New Auction Type',
        'new_item_name' => 'New Auction Type Name',
        'menu_name' => 'Auction Types',
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'auction-type'),
    );

    register_taxonomy('auction_type', 'auction', $args);
}

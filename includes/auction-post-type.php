<?php
// Register custom post type "Auction"
function register_auction_post_type() {
    $labels = array(
        'name' => 'Auctions',
        'singular_name' => 'Auction',
        'add_new' => 'Add New Auction',
        'add_new_item' => 'Add New Auction',
        'edit_item' => 'Edit Auction',
        'new_item' => 'New Auction',
        'view_item' => 'View Auction',
        'search_items' => 'Search Auctions',
        'not_found' => 'No auctions found',
        'not_found_in_trash' => 'No auctions found in trash',
        'menu_name' => 'Auctions'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'auction'),
        'supports' => array('title', 'editor', 'thumbnail'),
    );

    register_post_type('auction', $args);

    // Register custom fields for the Auction post type
    add_action('add_meta_boxes', 'auction_add_custom_fields');
}

// Add custom fields to the Auction post type
function auction_add_custom_fields() {
    add_meta_box('auction_fields', 'Auction Details', 'auction_render_custom_fields', 'auction', 'normal', 'default');
}

// Render custom fields in the Auction post type editor
function auction_render_custom_fields() {
    global $post;

    // Add your custom fields HTML here
    echo '<label for="initial_cost">Initial Cost:</label><br>';
    echo '<input type="text" id="initial_cost" name="initial_cost" value="' . get_post_meta($post->ID, 'initial_cost', true) . '"><br>';

    echo '<label for="start_datetime">Start Date/Time:</label><br>';
    echo '<input type="datetime-local" id="start_datetime" name="start_datetime" value="' . get_post_meta($post->ID, 'start_datetime', true) . '"><br>';

    echo '<label for="end_datetime">End Date/Time:</label><br>';
    echo '<input type="datetime-local" id="end_datetime" name="end_datetime" value="' . get_post_meta($post->ID, 'end_datetime', true) . '"><br>';

    echo '<label for="location">Location:</label><br>';
    echo '<input type="text" id="location" name="location" value="' . get_post_meta($post->ID, 'location', true) . '"><br>';
}

// Save custom field data when the Auction post type is saved
add_action('save_post', 'auction_save_custom_fields');
function auction_save_custom_fields($post_id) {
    if (array_key_exists('initial_cost', $_POST)) {
        update_post_meta(
            $post_id,
            'initial_cost',
            sanitize_text_field($_POST['initial_cost'])
        );
    }

    if (array_key_exists('start_datetime', $_POST)) {
        update_post_meta(
            $post_id,
            'start_datetime',
            sanitize_text_field($_POST['start_datetime'])
        );
    }

    if (array_key_exists('end_datetime', $_POST)) {
        update_post_meta(
            $post_id,
            'end_datetime',
            sanitize_text_field($_POST['end_datetime'])
        );
    }

    if (array_key_exists('location', $_POST)) {
        update_post_meta(
            $post_id,
            'location',
            sanitize_text_field($_POST['location'])
        );
    }
}

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


// Function to create the table
function create_auction_bidding_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'auction_bidding';

    // Check if the table already exists
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            post_id int(11) NOT NULL,
            user_id int(11) NOT NULL,
            bidding_datetime datetime NOT NULL,
            time int(11) NOT NULL,
            bidding_amount int(11) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}



//Function for Auction Ajax
add_action('wp_ajax_submit_bidding_form', 'handle_bidding_form_submission');
add_action('wp_ajax_nopriv_submit_bidding_form', 'handle_bidding_form_submission');
function handle_bidding_form_submission() {
    // Check nonce for security
    check_ajax_referer('bidding_form_nonce', 'nonce');

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to Bid having an active user account.');
        die();
    }

    // Get the form field value
    if (isset($_POST['bidding_cost'])) {
        global $wpdb;
        $current_currency = get_option('auction_currency_general_dk');
        $url     = wp_get_referer();
        $post_id = url_to_postid( $url );

        $start_datetime_auction = date("Y-m-d H:i:s", strtotime(get_post_meta($post_id, 'start_datetime', true)));
        $end_datetime_auction = date("Y-m-d H:i:s", strtotime(get_post_meta($post_id, 'end_datetime', true)));


        $bidding_cost_value = sanitize_text_field($_POST['bidding_cost']);
        $table_name = $wpdb->prefix . 'auction_bidding';
        $last_bid = $wpdb->get_results("SELECT * FROM $table_name WHERE post_id=$post_id ORDER BY ID DESC LIMIT 1")[0]->bidding_amount;

        $initial_cost = get_post_meta($post_id, 'initial_cost', true);
        $current_cost = $initial_cost;

        if ($last_bid) {
            $current_cost = $last_bid;
        }

        if($bidding_cost_value > $current_cost){
            $user_id = get_current_user_id();
            $bidding_datetime = current_time('mysql');
            $time = time();
    
            // Insert the data into the database
            $table_name = $wpdb->prefix . 'auction_bidding';
            $data = array(
                'post_id' => $post_id,
                'user_id' => $user_id,
                'bidding_datetime' => $bidding_datetime,
                'time' => $time,
                'bidding_amount' => $bidding_cost_value
            );
    
            $format = array(
                '%d', // post_id
                '%d', // user_id
                '%s', // bidding_datetime
                '%d', // time
                '%d'  // bidding_amount
            );
    
            $inserted = $wpdb->insert($table_name, $data, $format);
    
            if ($inserted) {
                wp_send_json_success('Bid has been submitted successfully. Value: ' . $current_currency . $bidding_cost_value);
            } else {
                wp_send_json_error('Something went wrong, please try again or try refreshing the page.');
            }
        }
        else{
            wp_send_json_error('Please enter the higher amount to Bid.');
        }


        // Process the form field value as needed
        // For demonstration purposes, we just return it back
        //wp_send_json_success('Bid has been submitted successfully. Value: ' . $post_id);
    } else {
        wp_send_json_error('Please enter the valid Bid amount.');
    }
}


<?php


// Add common functions used throughout the plugin here
add_filter('template_include', 'auction_custom_template');
function auction_custom_template($template)
{
    if (is_singular('auction')) {
        $new_template = plugin_dir_path(__FILE__) . 'templates/single-auction.php';
        if ('' !== $new_template) {
            return $new_template;
        }
    }
    return $template;
}


add_filter('template_include', 'auction_theme_override');
function auction_theme_override($template)
{
    if (is_singular('auction')) {
        $new_template = locate_template(array('single-auction.php'));
        if ('' !== $new_template) {
            return $new_template;
        }
    }
    return $template;
}


// Function to create the table bidding if it doesnot exist in database
function create_auction_bidding_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'auction_bidding';

    // Check if the table already exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
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


// Create "My Auctions" page on plugin activation
function auction_create_my_auctions_page() {
    $page_title = 'My Auctions';
    $page_slug = 'my-auctions';
    $page_content = '[My_Account_Auctions]';

    // Check if the page exists
    $existing_page = get_page_by_path($page_slug);

    if (!$existing_page) {
        // Page doesn't exist, create it
        $page_data = array(
            'post_title' => $page_title,
            'post_name' => $page_slug,
            'post_content' => $page_content,
            'post_status' => 'publish',
            'post_type' => 'page'
        );

        // Insert the page into the database
        wp_insert_post($page_data);
    }
}

// Flush rewrite rules on plugin activation
function auction_flush_rewrite_rules() {
    auction_create_my_auctions_page();
    auction_add_rewrite_rules();
    flush_rewrite_rules();
}


// Add rewrite rules for sub-URLs
function auction_add_rewrite_rules() {
    add_rewrite_rule('^my-auctions/my-bids/?', 'index.php?auction_page=my-bids', 'top');
    add_rewrite_rule('^my-auctions/submit-auction/?', 'index.php?auction_page=submit-auction', 'top');
}
add_action('init', 'auction_add_rewrite_rules');

// Add query vars
function auction_add_query_vars($query_vars) {
    $query_vars[] = 'auction_page';
    return $query_vars;
}
add_filter('query_vars', 'auction_add_query_vars');

// Load custom templates
function auction_template_include($template) {
    $auction_page = get_query_var('auction_page');
    $url = $_SERVER['REQUEST_URI'];
    if ($auction_page == 'my-bids') {
        // print_r($url);
        return plugin_dir_path(__FILE__) . 'templates/my-auctions/my-bids-template.php';
    } elseif ($auction_page == 'submit-auction') {
        return plugin_dir_path(__FILE__) . 'templates/my-auctions/submit-auction-template.php';
    }
    if (strpos($url,'my-auctions') !== false) {
        return plugin_dir_path(__FILE__) . 'templates/page-my-auctions.php'; 
    }

    return $template;
}
add_filter('template_include', 'auction_template_include');

// Register shortcodes (if needed)
// function auction_my_account_auctions_shortcode() {
//     // Placeholder content for My Auctions account page
//     ob_start();
//     echo '<h2>My Auctions</h2>';
//     echo '<p>This is the My Auctions account page.</p>';
//     return ob_get_clean();
// }
// add_shortcode('My_Account_Auctions', 'auction_my_account_auctions_shortcode');




//Function for Auction Bidding Ajax
add_action('wp_ajax_submit_bidding_form', 'handle_bidding_form_submission');
add_action('wp_ajax_nopriv_submit_bidding_form', 'handle_bidding_form_submission');
function handle_bidding_form_submission()
{
    // Check nonce for security
    check_ajax_referer('bidding_form_nonce', 'nonce');

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to Bid having an active user account.');
        die();
    }
    $url     = wp_get_referer();
    $post_id = url_to_postid($url);
    $start_datetime = date("Y-m-d H:i:s", strtotime(get_post_meta($post_id, 'start_datetime', true)));
    $end_datetime = date("Y-m-d H:i:s", strtotime(get_post_meta($post_id, 'end_datetime', true)));
    $current_datetime = current_datetime()->format('Y-m-d H:i:s');

    if ($current_datetime >= $start_datetime && $current_datetime <= $end_datetime) {
        // Get the form field value
        if (isset($_POST['bidding_cost'])) {
            global $wpdb;
            $current_currency = get_option('auction_currency_general_dk');

            // $start_datetime_auction = date("Y-m-d H:i:s", strtotime(get_post_meta($post_id, 'start_datetime', true)));
            // $end_datetime_auction = date("Y-m-d H:i:s", strtotime(get_post_meta($post_id, 'end_datetime', true)));


            $bidding_cost_value = sanitize_text_field($_POST['bidding_cost']);
            $table_name = $wpdb->prefix . 'auction_bidding';
            $last_bid = $wpdb->get_results("SELECT * FROM $table_name WHERE post_id=$post_id ORDER BY ID DESC LIMIT 1")[0]->bidding_amount;

            $initial_cost = get_post_meta($post_id, 'initial_cost', true);
            $current_cost = $initial_cost;

            if ($last_bid) {
                $current_cost = $last_bid;
            }

            if ($bidding_cost_value > $current_cost) {
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
            } else {
                wp_send_json_error('Please enter the higher amount to Bid.');
            }


            // Process the form field value as needed
            // For demonstration purposes, we just return it back
            //wp_send_json_success('Bid has been submitted successfully. Value: ' . $post_id);
        } else {
            wp_send_json_error('Please enter the valid Bid amount.');
        }
    } else {
        wp_send_json_error('Auction is already ended, you cannot bid for ended auction.');
    }
}

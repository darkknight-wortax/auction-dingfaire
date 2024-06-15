<?php
/*
Plugin Name: Auction - Dingfaire
Description: Custom plugin for managing auctions.
Version: 1.0
Author: Wortax - Darkknight
*/

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/auction-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/auction-post-type.php';
require_once plugin_dir_path(__FILE__) . 'includes/auction-taxonomy.php';
require_once plugin_dir_path(__FILE__) . 'admin/auction-settings.php';

// Initialize the plugin
add_action('init', 'auction_dingfaire_init');
function auction_dingfaire_init()
{
    // Initialize custom post type and taxonomy
    register_auction_post_type();
    register_auction_taxonomy();

    // Add other initialization actions here if needed
}
// Activation hook
// register_activation_hook(__FILE__, 'auction_create_submit_auction_page');
// Hook the table creation function to the plugin activation
register_activation_hook(__FILE__, 'create_auction_bidding_table');
// Create new my auction page and its sub URLs along with registering templates
register_activation_hook(__FILE__, 'auction_flush_rewrite_rules');

// Enqueue CSS and JS files
add_action('wp_enqueue_scripts', 'auction_enqueue_scripts');
function auction_enqueue_scripts()
{
    // Enqueue CSS file
    wp_enqueue_style('auction-styles', plugin_dir_url(__FILE__) . 'assets/css/auction-styles.css');

    // Enqueue JS file with jQuery dependency
    wp_enqueue_script('auction-scripts', plugin_dir_url(__FILE__) . 'assets/js/auction-scripts.js', array('jquery'), null, true);
    // Localize script to pass AJAX URL and nonce
    wp_localize_script('auction-scripts', 'dkAuctionSubmissionAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('bidding_form_nonce'),
    ));
    wp_enqueue_media();
    // wp_localize_script('auction-scripts', 'auctionAjax', array(
    //     'ajaxurl' => admin_url('admin-ajax.php'),
    //     'nonce' => wp_create_nonce('auction_nonce')
    // ));
    // Pass the auction ID to the script
    wp_localize_script('auction-scripts', 'auctionSseData', array(
        'auction_id' => get_the_ID(),
        'sse_url' => home_url('/?sse_auction_update=1&auction_id=' . get_the_ID())
    ));

    if (is_singular('auction')) {
//         wp_enqueue_script('auction-view-count', plugin_dir_url(__FILE__) . 'assets/js/auction-scripts.js', array('jquery'), null, true);

        // Localize the script with the AJAX URL and nonce
        wp_localize_script('auction-scripts', 'auctionViewCount', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'post_id' => get_the_ID(),
            'nonce' => wp_create_nonce('auction_view_count_nonce')
        ));
    }
}

// Add admin menu page for plugin options
add_action('admin_menu', 'auction_options_page');
function auction_options_page()
{
    add_menu_page(
        'Auction Settings',      // Page title
        'Auction Settings',      // Menu title
        'manage_options',       // Capability required to access
        'auction-settings',     // Menu slug
        'auction_settings_page' // Callback function to display the page
    );
}

function auction_settings_page()
{
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save settings if form is submitted
    if (isset($_POST['auction_save_settings'])) {
        // Sanitize and save settings
        update_option('auction_title_dk', sanitize_text_field($_POST['auction_title_dk']));
        update_option('auction_currency_general_dk', sanitize_text_field($_POST['auction_currency_general_dk']));
        update_option('bid_increment', sanitize_text_field($_POST['bid_increment']));
        update_option('admin_notification_email', sanitize_email($_POST['admin_notification_email']));
        update_option('no_auction_message', sanitize_textarea_field($_POST['no_auction_message']));
        // Add more settings as needed
    }

    // Handle manual scheduler trigger
    if (isset($_POST['run_auction_scheduler']) && check_admin_referer('run_auction_scheduler_nonce')) {
        do_action('check_completed_auctions');
        echo '<div class="notice notice-success is-dismissible"><p>Auction Scheduler has been run successfully.</p></div>';
    }

    // Display options page HTML
?>
    <div class="wrap">
        <h1>Auction Settings</h1>
        <form method="post" action="">
            <?php wp_nonce_field('auction_save_settings'); ?>
            <label for="auction_title_dk">Auction Title:</label>
            <input type="text" id="auction_title_dk" name="auction_title_dk" value="<?php echo esc_attr(get_option('auction_title_dk')); ?>"><br>
            <br />
            <label for="auction_currency_general_dk">Auction Currency:</label>
            <select id="auction_currency_general_dk" name="auction_currency_general_dk">
                <option value="$" <?php selected(get_option('auction_currency_general_dk'), '$'); ?>>USD ($)</option>
                <option value="€" <?php selected(get_option('auction_currency_general_dk'), '€'); ?>>Euro (€)</option>
                <option value="£" <?php selected(get_option('auction_currency_general_dk'), '£'); ?>>Pound (£)</option>
            </select>
            <br />
            <label for="bid_increment">Set Bid Increment:</label>
            <?php
            $default_bid_increment = 5;
            if (get_option('bid_increment')) {
                $default_bid_increment = get_option('bid_increment');
            }
            ?>
            <input type="number" id="bid_increment" name="bid_increment" value="<?php echo esc_attr($default_bid_increment); ?>"><br>
            <br />
            <label for="admin_notification_email">Admin Notification Email:</label>
            <?php
            $admin_email_notif = get_bloginfo('admin_email');
            if (get_option('admin_notification_email')) {
                $admin_email_notif = get_option('admin_notification_email');
            }
            ?>
            <input type="email" id="admin_notification_email" name="admin_notification_email" value="<?php echo esc_attr($admin_email_notif); ?>"><br>
            <br />
            <label for="no_auction_message">No Auction Message:</label><br>
            <textarea id="no_auction_message" name="no_auction_message" rows="4"><?php echo esc_textarea(get_option('no_auction_message')); ?></textarea><br>
            <!-- Add more settings fields as needed -->

            <input type="submit" name="auction_save_settings" class="button button-primary" value="Save Settings">
        </form>

        <h2>Manual Auction Scheduler</h2>
        <form method="post" action="">
            <?php wp_nonce_field('run_auction_scheduler_nonce'); ?>
            <input type="hidden" name="run_auction_scheduler" value="1">
            <input type="submit" class="button button-secondary" value="Run Scheduler Now">
        </form>
    </div>
<?php
}

add_action('admin_menu', 'auction_options_page');




if (!class_exists('ActionScheduler')) {
    include_once(WP_PLUGIN_DIR . '/woocommerce/packages/action-scheduler/action-scheduler.php');
}


// Add a custom interval of 2 minutes
function add_custom_cron_intervals($schedules) {
    $schedules['every_two_minutes'] = array(
        'interval' => 120, // 2 minutes in seconds
        'display'  => __('Every 2 Minutes')
    );
    return $schedules;
}
add_filter('cron_schedules', 'add_custom_cron_intervals');

// Step 2: Schedule the event to run every 2 minutes
function schedule_auction_check() {
    if (!wp_next_scheduled('check_completed_auctions')) {
        wp_schedule_event(time(), 'every_two_minutes', 'check_completed_auctions');
    }
}
add_action('init', 'schedule_auction_check');

function debug_notices_auc()
    {
        if (current_user_can('manage_options')) {
            $scheduled = wp_next_scheduled('check_completed_auctions');
            if ($scheduled) {
                echo '<div class="notice notice-success"><p>Auction Completion Checker: Next sync scheduled for ' . date('Y-m-d H:i:s', $scheduled) . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Auction Completion Checker: No sync scheduled.</p></div>';
            }
        }
    }
add_action('admin_notices', 'debug_notices_auc');


function check_completed_auctions()
{
    global $wpdb;

    $current_time = current_time('mysql');
    $table_name = $wpdb->prefix . 'auction_bidding';

    // Get all completed auctions
    $args = array(
        'post_type' => 'auction',
        'meta_query' => array(
            array(
                'key' => 'end_datetime',
                'value' => $current_time,
                'compare' => '<=',
                'type' => 'DATETIME'
            ),
            array(
                'key' => 'status',
                'value' => '0',
                'compare' => '='
            )
        ),
        'posts_per_page' => -1
    );
    $completed_auctions = get_posts($args);

    print_r($completed_auctions);

    foreach ($completed_auctions as $auction) {
        $post_id = $auction->ID;

        // Get the highest bid
        $highest_bid = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE post_id = %d ORDER BY bidding_amount DESC LIMIT 1",
                $post_id
            )
        );
        $shipping_fee = get_post_meta($post_id, 'shipping_fee', true);
        if (!$shipping_fee) {
            $shipping_fee = 0;
        }
        if ($highest_bid) {
            // Create a WooCommerce product
            $product = new WC_Product_Simple();
            $product->set_name($auction->post_title);
            $product->set_description($auction->post_content);
            $product->set_regular_price($highest_bid->bidding_amount + (int)$shipping_fee);
            $product->set_status('publish');
			$product->set_manage_stock(true);
            $product->set_stock_quantity(1);
            $product->save();

            // Update auction status to 1
            update_post_meta($post_id, 'status', '1');

            // Restrict product visibility to the highest bidder
            $highest_bidder_id = $highest_bid->user_id;
            update_post_meta($product->get_id(), '_highest_bidder_id', $highest_bidder_id);
        }
    }
}
add_action('check_completed_auctions', 'check_completed_auctions');


function restrict_product_visibility_to_highest_bidder($query)
{
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if (is_product()) {
        $product_id = get_the_ID();
        $highest_bidder_id = get_post_meta($product_id, '_highest_bidder_id', true);

        if ($highest_bidder_id && get_current_user_id() != $highest_bidder_id) {
            // Redirect to 404 if the user is not the highest bidder
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
            include(get_query_template('404'));
            exit;
        }
    }
}
add_action('pre_get_posts', 'restrict_product_visibility_to_highest_bidder');

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
// Activation hook
register_activation_hook(__FILE__, 'auction_create_submit_auction_page');

// Enqueue CSS and JS files
add_action('wp_enqueue_scripts', 'auction_enqueue_scripts');
function auction_enqueue_scripts() {
    // Enqueue CSS file
    wp_enqueue_style('auction-styles', plugin_dir_url(__FILE__) . 'css/auction-styles.css');

    // Enqueue JS file with jQuery dependency
    wp_enqueue_script('auction-scripts', plugin_dir_url(__FILE__) . 'js/auction-scripts.js', array('jquery'), null, true);
}

// Add admin menu page for plugin options
add_action('admin_menu', 'auction_options_page');
function auction_options_page() {
    add_menu_page(
        'Auction Settings',      // Page title
        'Auction Settings',      // Menu title
        'manage_options',       // Capability required to access
        'auction-settings',     // Menu slug
        'auction_settings_page' // Callback function to display the page
    );
}

// Callback function to display options page content
function auction_settings_page() {
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

    // Display options page HTML
    ?>
    <div class="wrap">
        <h1>Auction Settings</h1>
        <form method="post" action="">
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
                if(get_option('bid_increment')){
                    $default_bid_increment = get_option('bid_increment');
                }
            ?>
            <input type="number" id="bid_increment" name="bid_increment" value="<?php echo esc_attr($default_bid_increment); ?>"><br>
            <br />
            <label for="admin_notification_email">Admin Notification Email:</label>

            <?php 
                $admin_email_notif = get_bloginfo('admin_email');
                if(get_option('admin_notification_email')){
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
    </div>
    <?php
}

// Function to create "Submit Auction" page if not already present
function auction_create_submit_auction_page() {
    $page_title = 'Submit Auction';
    $page_content = '[Submit_Auction]'; // Add content if needed
    //$template_path = plugin_dir_path(__FILE__) . 'templates/submit-auction-template.php';

    // Check if the page exists
    $existing_page = get_page_by_title($page_title);

    if (!$existing_page) {
        // Page doesn't exist, create it
        $page_data = array(
            'post_title' => $page_title,
            'post_content' => $page_content,
            'post_status' => 'publish',
            'post_type' => 'page'
        );

        // Insert the page into the database
        $new_page_id = wp_insert_post($page_data);

        // Set the page template
        update_post_meta($new_page_id, '_wp_page_template', str_replace(WP_CONTENT_DIR, 'wp-content', $template_path));
    }
}

function submit_auction_form_with_functionality(){
    ob_start();

    echo 'Hello';

    return ob_get_clean();
}
add_shortcode('Submit_Auction','submit_auction_form_with_functionality');

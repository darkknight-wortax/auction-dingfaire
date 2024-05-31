<?php

// Ensure the code runs only in the admin area
if (is_admin()) {
    // Hook to admin_notices to display an admin notice
    add_action('admin_notices', 'auction_dingfaire_check_woocommerce');

    // Function to check if WooCommerce is active
    function auction_dingfaire_check_woocommerce()
    {
        // Check if WooCommerce is not active
        if (!is_plugin_active('woocommerce/woocommerce.php')) {
            // Display admin notice
            echo '<div class="error"><p><strong>Auction - Dingfaire requires WooCommerce to be installed and active to work properly.</strong></p></div>';

            // Deactivate the plugin
            deactivate_plugins(plugin_basename(__FILE__));
        }
    }
}


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

        require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}


// Create "My Auctions" page on plugin activation
function auction_create_my_auctions_page()
{
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
function auction_flush_rewrite_rules()
{
    auction_create_my_auctions_page();
    auction_add_rewrite_rules();
    flush_rewrite_rules();
}


// Add rewrite rules for sub-URLs
function auction_add_rewrite_rules()
{
    add_rewrite_rule('^my-auctions/my-bids/?', 'index.php?auction_page=my-bids', 'top');
    add_rewrite_rule('^my-auctions/submit-auction/?', 'index.php?auction_page=submit-auction', 'top');
}
add_action('init', 'auction_add_rewrite_rules');

// Add query vars
function auction_add_query_vars($query_vars)
{
    $query_vars[] = 'auction_page';
    return $query_vars;
}
add_filter('query_vars', 'auction_add_query_vars');

// Load custom templates
function auction_template_include($template)
{
    $auction_page = get_query_var('auction_page');
    $url = $_SERVER['REQUEST_URI'];
    if ($auction_page == 'my-bids') {
        // print_r($url);
        return plugin_dir_path(__FILE__) . 'templates/my-auctions/my-bids-template.php';
    } elseif ($auction_page == 'submit-auction') {
        return plugin_dir_path(__FILE__) . 'templates/my-auctions/submit-auction-template.php';
    }
    if (strpos($url, 'my-auctions') !== false) {
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
    $url = wp_get_referer();
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

//SSE for Updating the Latest Bid from server side
// Function to handle SSE requests
function my_sse_handler2()
{
    // Set headers for SSE
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');

    // Prevent the script from timing out
    set_time_limit(0);

    // Get the auction ID from the query parameter
    $auction_id = isset($_GET['auction_id']) ? intval($_GET['auction_id']) : 0;

    // Check if auction ID is valid
    if ($auction_id <= 0) {
        echo "retry: 5000\n";
        echo "data: Invalid auction ID\n\n";
        flush();
        exit();
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'auction_bidding';

    // Infinite loop to keep the connection open
    // while (true) {
    // Fetch the latest auction cost from the database
    $result = $wpdb->get_results("SELECT bidding_amount FROM $table_name WHERE post_id = $auction_id ORDER BY ID DESC LIMIT 1");
    $current_bid = !empty($result) ? $result[0]->bidding_amount : 0;

    // Send the current bid as SSE
    echo "data: " . json_encode(array('current_bid' => $current_bid)) . "\n\n";
    flush();

    // Sleep for a while before checking for updates again
    // sleep(2);
    // }
}
// add_action('wp_ajax_sse_auction_update', 'my_sse_handler2');
// add_action('wp_ajax_nopriv_sse_auction_update', 'my_sse_handler2');







//Image Upload Capability for Subscribers
function allow_subscriber_uploads()
{
    $role = get_role('subscriber');
    if ($role && !$role->has_cap('upload_files')) {
        $role->add_cap('upload_files');
    }
}
add_action('init', 'allow_subscriber_uploads');

// Restrict Media Library to Only Show User's Own Uploads
function restrict_media_library_access($wp_query)
{
    if (!current_user_can('manage_options')) {
        $user_id = get_current_user_id();
        $wp_query->set('author', $user_id);
    }
}
add_action('pre_get_posts', 'restrict_media_library_access');

// Allow subscribers to upload only images
function restrict_mime_types_for_subscribers($mime_types)
{
    if (!current_user_can('manage_options')) {
        $mime_types = array(
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        );
    }
    return $mime_types;
}
add_filter('upload_mimes', 'restrict_mime_types_for_subscribers');

// Filter to show only images in the media library
function show_only_images_in_media_library($query)
{
    if (!current_user_can('manage_options')) {
        $post_mime_types = array(
            'image/jpeg',
            'image/png',
            'image/gif'
        );
        $query['post_mime_type'] = $post_mime_types;
    }
    return $query;
}
add_filter('ajax_query_attachments_args', 'show_only_images_in_media_library');




//Auction Frontend Submission Shortcode
// Shortcode to display the auction submission form
function auction_submission_form_shortcode()
{
    if (!is_user_logged_in()) {
        return '<p>Please login to Submit Auction.</p>';
    }
    ob_start();
    ?>
    <form id="auction-submission-form" class="auction-submission-form" method="post">
        <div class="auction-form-fields-wrapper">
            <div class="auction-column auction-col-50">
                <label for="auction-title">Title:</label>
                <input type="text" id="auction-title" class="auction-input" name="auction_title" required>
            </div>
            <div class="auction-column auction-col-50">
                <label for="auction-category">Category:</label>
                <?php
                $categories = get_terms(
                    array(
                        'taxonomy' => 'auction_type',
                        'hide_empty' => false,
                    )
                );
                ?>
                <select id="auction-category" name="auction_type" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo esc_attr($category->term_id); ?>"><?php echo esc_html($category->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="auction-column auction-col-50">
                <label for="auction-initial-cost">Initial Cost:</label>
                <input type="number" id="auction-initial-cost" class="auction-input" name="auction_initial_cost" required>
            </div>
            <div class="auction-column auction-col-50">
                <label for="auction-location">Location:</label>
                <input type="text" id="auction-location" class="auction-input" name="auction_location" required>
            </div>
            <div class="auction-column auction-col-50">
                <label for="auction-featured-image">Featured Image:</label>
                <input type="button" id="auction-featured-image-btn" class="button auction-btn" value="Upload Featured Image">
                <input type="hidden" id="auction-featured-image" name="auction_featured_image">
                <div id="auction-featured-image-preview" class="auction-image-preview"></div>
            </div>
            <div class="auction-column auction-col-50">
                <label for="auction-gallery-images">Image Gallery:</label>
                <input type="button" id="auction-gallery-images-btn" class="button auction-btn" value="Upload Gallery Images">
                <input type="hidden" id="auction-gallery-images" name="auction_gallery_images">
                <div id="auction-gallery-images-preview" class="auction-image-preview auction-gallery-images-preview"></div>
            </div>
            <div class="auction-column auction-col-50">
                <label for="auction-start-datetime">Start Date/Time:</label>
                <input type="datetime-local" id="auction-start-datetime" class="auction-input" name="auction_start_datetime" required>
            </div>
            <div class="auction-column auction-col-50">
                <label for="auction-end-datetime">End Date/Time:</label>
                <input type="datetime-local" id="auction-end-datetime" class="auction-input" name="auction_end_datetime" required>
            </div>
            <div class="auction-column auction-col-50">
                <label for="auction-description">Description:</label>
                <textarea id="auction-description" name="auction_description" class="auction-input" required></textarea>
            </div>
            <div class="auction-column auction-col-100">
                <input type="submit" class="auction-btn" value="Submit Auction">
            </div>


        </div>
    </form>



    <script>
        jQuery(document).ready(function ($) {
            // Handle featured image upload
            $('#auction-featured-image-btn').click(function (e) {
                e.preventDefault();
                var imageUploader = wp.media({
                    title: 'Upload Featured Image',
                    button: {
                        text: 'Set as Featured Image'
                    },
                    multiple: false
                }).on('select', function () {
                    var attachment = imageUploader.state().get('selection').first().toJSON();
                    $('#auction-featured-image').val(attachment.id);
                    $('#auction-featured-image-preview').html('<div class="image-preview"><img src="' + attachment.url + '"><a href="#" class="remove-image" data-image-id="' + attachment.id + '">Remove</a></div>');
                }).open();
            });

            // Handle gallery images upload
            $('#auction-gallery-images-btn').click(function (e) {
                e.preventDefault();
                var imageUploader = wp.media({
                    title: 'Upload Gallery Images',
                    button: {
                        text: 'Add to Gallery'
                    },
                    multiple: true
                }).on('select', function () {
                    var attachments = imageUploader.state().get('selection').toJSON();
                    var imageIDs = $('#auction-gallery-images').val().split(',').filter(Boolean);
                    attachments.forEach(function (attachment) {
                        imageIDs.push(attachment.id);
                        $('#auction-gallery-images-preview').append('<div class="image-preview"><img src="' + attachment.url + '"><a href="#" class="remove-image" data-image-id="' + attachment.id + '">Remove</a></div>');
                    });
                    $('#auction-gallery-images').val(imageIDs.join(','));
                }).open();
            });

            // Handle image removal
            $('body').on('click', '.remove-image', function (e) {
                e.preventDefault();
                var imageID = $(this).data('image-id');
                $(this).parent().remove();
                var imageIDs = $('#auction-gallery-images').val().split(',').filter(Boolean);
                imageIDs = imageIDs.filter(function (id) {
                    return id != imageID;
                });
                $('#auction-gallery-images').val(imageIDs.join(','));
            });

            // Handle form submission
            $('#auction-submission-form').submit(function (e) {
                e.preventDefault();
                if (!<?php echo is_user_logged_in() ? 'true' : 'false'; ?>) {
                    alert('You must be logged in to submit an auction.');
                    return;
                }

                var formData = $(this).serialize();
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: formData + '&action=auction_fe_submission',
                    success: function (response) {
                        alert(response.data.message);
                        if (response.success) {
                            $('#auction-submission-form')[0].reset();
                            $('#auction-featured-image-preview').empty();
                            $('#auction-gallery-images-preview').empty();
                        }
                    },
                    error: function (response) {
                        alert('An error occurred while submitting the auction.');
                    }
                });
            });
        });
    </script>


    <?php
    return ob_get_clean();
}
add_shortcode('auction_submission_form', 'auction_submission_form_shortcode');




function auction_data_submission_frontend()
{
    // Check if the user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'You must be logged in to submit an auction.'));
    }

    // Get form data
    $title = sanitize_text_field($_POST['auction_title']);
    $description = sanitize_textarea_field($_POST['auction_description']);
    $featured_image = intval($_POST['auction_featured_image']);
    $gallery_images = sanitize_text_field($_POST['auction_gallery_images']);
    $start_datetime = sanitize_text_field($_POST['auction_start_datetime']);
    $end_datetime = sanitize_text_field($_POST['auction_end_datetime']);
    $initial_cost = sanitize_text_field($_POST['auction_initial_cost']);
    $location = sanitize_text_field($_POST['auction_location']);
    $category = intval($_POST['auction_type']);

    // Validate datetime
    if (strtotime($end_datetime) <= strtotime($start_datetime)) {
        wp_send_json_error(array('message' => 'End date must be after the start date.'));
    }

    // Create a new auction post
    $auction_id = wp_insert_post(
        array(
            'post_title' => $title,
            'post_content' => $description,
            'post_status' => 'pending',
            'post_type' => 'auction',
            'meta_input' => array(
                'initial_cost' => $initial_cost,
                'start_datetime' => $start_datetime,
                'end_datetime' => $end_datetime,
                'location' => $location,
                'auction_gallery_images' => $gallery_images
            )
        )
    );

    if (is_wp_error($auction_id)) {
        wp_send_json_error(array('message' => 'An error occurred while creating the auction.'));
    }

    // Set featured image
    if ($featured_image) {
        set_post_thumbnail($auction_id, $featured_image);
    }

    // Set category
    wp_set_post_terms($auction_id, array($category), 'auction_type');

    wp_send_json_success(array('message' => 'Auction submitted successfully!'));
}
add_action('wp_ajax_auction_fe_submission', 'auction_data_submission_frontend');
add_action('wp_ajax_nopriv_auction_fe_submission', 'auction_data_submission_frontend');

<?php
// Get search parameters
$search_query = get_query_var('search_query', '');
$category = get_query_var('category', '');
$location = get_query_var('location', '');
$radius = get_query_var('radius', '');

$args = array(
    'post_type' => 'auction',
    'posts_per_page' => -1,
    's' => $search_query,
);

// If a category is selected, filter by category
if ($category) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'auction_type',
            'field' => 'slug',
            'terms' => $category,
        ),
    );
}

// If a location is selected, filter by location meta field
if ($location) {
    $args['meta_query'] = array(
        array(
            'key' => 'location',
            'value' => $location,
            'compare' => 'LIKE'
        ),
    );
}

// Custom query
$query = new WP_Query($args);
get_header();
?>
<div class="auction-search-results">

    <h2>Auction Results</h2>

    <?php if ($query->have_posts()) :
        global $wpdb;
        $table_name = $wpdb->prefix . 'auction_bidding';
    ?>
        <ul class="auctions_listing_wrapper">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <li>
                    <div class="auction_listing_feat_wrap">
                        <?php
                        $post_id = get_the_ID();
                        $current_currency = get_option('auction_currency_general_dk');

                        // $last_bid = $wpdb->get_results("SELECT * FROM $table_name WHERE post_id=$post_id ORDER BY ID DESC LIMIT 1")[0]->bidding_amount;
                        // Retrieve the last bid
                        $last_bid_query = $wpdb->get_results("SELECT * FROM $table_name WHERE post_id=$post_id ORDER BY ID DESC LIMIT 1");
                        $last_bid = null;
                        if (!empty($last_bid_query)) {
                            $last_bid = $last_bid_query[0]->bidding_amount;
                        }

                        $start_datetime = date("Y-m-d H:i:s", strtotime(get_post_meta($post_id, 'start_datetime', true)));
                        $end_datetime = date("Y-m-d H:i:s", strtotime(get_post_meta($post_id, 'end_datetime', true)));
                        $current_datetime = current_datetime()->format('Y-m-d H:i:s');

                        //Setting Up Shipping Fee
                        $shipping_fee = get_post_meta($post_id, 'shipping_fee', true);
                        if (!$shipping_fee) {
                            $shipping_fee = 0;
                        }

                        $location = get_post_meta($post_id, 'location', true);
                        $initial_cost = (int)get_post_meta($post_id, 'initial_cost', true) + (int)$shipping_fee;
                        $current_cost = (int)$initial_cost + (int)$shipping_fee;
                        $location = get_post_meta($post_id, 'location', true);
                        if ($last_bid) {
                            $current_cost = (int)$last_bid + (int)$shipping_fee;
                        }
                        // Output the featured image
                        if (get_the_post_thumbnail_url()) {
                            $featured_image = '<img src="' . get_the_post_thumbnail_url(null, 'full') . '" alt="Featured Image" class="" />';
                        } else {
                            $featured_image = '<img src="' . esc_url($plugin_path . '../../assets/images/thumbnail-auction.png') . '" class="" />';
                        }
                        ?>
                        <a href="<?php the_permalink(); ?>"><?php echo $featured_image; ?></a>
                    </div>
                    <div class="auction_listing_content_wrap">
                        <div class="auction_listing_title_area">
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

                            <?php if (!empty($initial_cost)) { ?>
                                <p>Initial Cost: <?php echo $current_currency . $initial_cost; ?></p>
                            <?php } ?>
                            <?php if ($last_bid) { ?>
                                <p>Latest Bid: <span id="latest_bid"><?php echo $current_currency . ((int)$last_bid + (int)$shipping_fee); ?></span></p>
                            <?php } ?>
                            <?php if (!empty($location)) { ?>
                                <p><?php echo 'Location: ' . $location; ?></p>
                            <?php } ?>
                        </div>
                        <div class="auction_listing_timer">
                            <?php if (!empty($initial_cost)) { ?>
                                <div id="dk_timer_auction_<?php echo $post_id; ?>"></div>
                            <?php } ?>
                        </div>

                    </div>
                    <script>
                        jQuery(document).ready(function($) {
                            function updateCountdown() {
                                // Set the target date and time (in Local Timezone)
                                const targetDate = new Date("<?php echo $end_datetime ?>");

                                // Get the current date and time (in the user's timezone)
                                const startDatetime = new Date("<?php echo $start_datetime ?>");

                                const now = new Date();

                                // Calculate the remaining time
                                const timeDifference = targetDate - now;
                                // console.log(timeDifference);

                                const check_start = now - startDatetime;
                                if (check_start <= 0) {
                                    let elem_c = document.getElementById("dk_timer_auction_<?php echo $post_id; ?>");
                                    elem_c.innerHTML = "<div class='aunction-start aunction-dates'>Auction will start on: <?php echo $start_datetime ?></div>";
                                    bid_form.style.display = 'none';
                                    // elem_c.parentNode.closest('section').remove();
                                    // Perform any action here when the countdown reaches zero
                                    // For example: redirect to another page, display a message, etc.
                                    return;
                                }

                                // If the time difference is less than or equal to 0, the countdown has reached zero
                                if (timeDifference <= 0) {
                                    let elem_c = document.getElementById("dk_timer_auction_<?php echo $post_id; ?>");
                                    elem_c.innerHTML = "<div class='aunction-end aunction-dates'>Auction has been ended";
                                    // bid_form.style.display = 'none';
                                    // elem_c.parentNode.closest('section').remove();
                                    // Perform any action here when the countdown reaches zero
                                    // For example: redirect to another page, display a message, etc.
                                    return;
                                }

                                // Convert the time difference to days, hours, minutes, and seconds
                                const days = Math.floor(timeDifference / (1000 * 60 * 60 * 24));
                                const hours = Math.floor((timeDifference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                const minutes = Math.floor((timeDifference % (1000 * 60 * 60)) / (1000 * 60));
                                const seconds = Math.floor((timeDifference % (1000 * 60)) / 1000);

                                const daysString = days < 10 ? "0" + days : days;
                                const hoursString = hours < 10 ? "0" + hours : hours;
                                const minutesString = minutes < 10 ? "0" + minutes : minutes;
                                const secondsString = seconds < 10 ? "0" + seconds : seconds;

                                // Display the countdown in the specified element
                                let day_display = "<div class='timer_area'>";
                                if (days > 0) {
                                    day_display = "<div class='timer_area'><div class='itm_dk day_dk'><span class='itm_prefix_dk'>Day</span><span class='itm_value_dk'>" + daysString + "</span></div> ";
                                }
                                document.getElementById("dk_timer_auction_<?php echo $post_id; ?>").innerHTML = day_display + "<div class='itm_dk'><span class='itm_prefix_dk'>Hr</span><span class='itm_value_dk'>" + hoursString + "</span></div>:<div class='itm_dk'><span class='itm_prefix_dk'>Min</span><span class='itm_value_dk'>" + minutesString + "</span></div>:<div class='itm_dk'><span class='itm_prefix_dk'>Sec</span><span class='itm_value_dk'>" + secondsString + "</span></div></div>";
                                // bid_form.style.display = 'block';
                            }

                            // Call the updateCountdown function every second to keep the countdown updated
                            setInterval(updateCountdown, 1000);

                            // Call the function initially to display the countdown immediately
                            updateCountdown();
                        });
                    </script>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else : ?>
        <p>No auctions found.</p>
    <?php endif; ?>
</div>
<?php
wp_reset_postdata();
get_footer();

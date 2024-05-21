<?php
// Template Name: Auction Single
get_header();

if (have_posts()):
    while (have_posts()):
        the_post();
        $plugin_path = plugin_dir_url(__FILE__);
        
        $post_id = get_the_ID();
        global $wpdb;
        $table_name = $wpdb->prefix . 'auction_bidding';

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
        // $current_datetime = date("Y-m-d H:i:s");
        $current_datetime = current_datetime()->format('Y-m-d H:i:s');
        // print_r($current_date_time);

        $location = get_post_meta($post->ID, 'location', true);
        $initial_cost = get_post_meta($post_id, 'initial_cost', true);
        $current_cost = $initial_cost;

        if ($last_bid) {
            $current_cost = $last_bid;
        }

        // Default Bid increment from admin
        $default_bid_increment = 5;
        if (get_option('bid_increment')) {
            $default_bid_increment = get_option('bid_increment');
        }

        ?>

        <link rel="stylesheet" href="<?php echo esc_url($plugin_path . '../../assets/css/slick.min.css'); ?>">
        <link rel="stylesheet" href="<?php echo esc_url($plugin_path . '../../assets/css/slick-theme.min.css'); ?>">
        <script src="<?php echo esc_url($plugin_path . '../../assets/js/slick.min.js'); ?>"></script>

        <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

            <div class="entry-content">

                <!-- Section 1 -->
                <section id="section_1" class="section_1_auction">
                    <!-- Row 1 -->
                    <div class="sec_1_row_1 row_dk">
                        <!-- Column 1 -->
                        <div class="col_1_dk">
                            <?php
                            // Output the featured image
                            if(get_the_post_thumbnail_url()){
                                $featured_image = '<div><img src="' . get_the_post_thumbnail_url(null, 'full') . '" alt="Featured Image" class=""></div>';
                            }
                            else{
                                $featured_image = '<div><img src="'.esc_url($plugin_path . '../../assets/images/thumbnail-auction.png').'" class=""></div>';
                            }
                            

                            // Output the gallery images
                            $gallery_images = '';
                            $gallery_images_meta = get_post_meta($post_id, 'auction_gallery_images', true);
                            $gallery_image_ids = explode(',', $gallery_images_meta);
                            foreach ($gallery_image_ids as $image_id) {
                                $image_url = wp_get_attachment_image_src($image_id, 'full');
                                if ($image_url) {
                                    $gallery_images .= '<div><img src="' . esc_url($image_url[0]) . '" alt="Gallery Image" class="slick-image"></div>';
                                }
                            }
                            ?>
                            <div class="slider-for-single-auction">
                                <?php echo $featured_image; ?>
                                <?php echo $gallery_images; ?>
                            </div>

                            <?php if($gallery_image_ids[0]){
                                //print_r($gallery_image_ids[0]);
                                ?>
                                <div class="slider-nav-single-auction">
                                    <?php echo $featured_image; ?>
                                    <?php echo $gallery_images; ?>
                                </div>
                            <?php }?>

                        </div>
                        <!-- Column 1 End-->

                        <!-- Column 2 -->
                        <div class="col_2_dk">
                            <h2><?php the_title(); ?></h2>
                            <?php if (!empty($initial_cost)) { ?>
                                <p>Initial Cost: <?php echo $current_currency . $initial_cost; ?></p>
                            <?php } ?>
                            <?php if ($last_bid) { ?>
                                <p>Latest Bid: <span id="latest_bid"><?php echo $current_currency . $last_bid; ?></span></p>
                            <?php } ?>
                            <?php if (!empty($initial_cost)) { ?>
                            <div id="dk_timer_auction"></div>
                            <?php } ?>
                            <!-- <label><input type="number" min="<?php echo $current_cost ?>" step="<?php echo $default_bid_increment ?>" value="<?php echo $current_cost ?>" /></label> -->
                            
                            <form id="bid_form" style="<?php if ($current_datetime >= $start_datetime && $current_datetime <= $end_datetime) {echo 'display:none';}?>">
                                <?php if (!empty($initial_cost)) { ?>
                                <div class="min-add-button">
                                    <div class="input-group_audf">
                                        <a href="#" class="input-group-addon minus_audf increment_audf">-</a>
                                        <input type="text" class="form-control" id="auction_cost"
                                            value="<?php echo $current_cost ?>" readonly>
                                        <a href="#" class="input-group-addon plus_audf increment_audf">+</a>
                                    </div>
                                </div>
                                <?php } ?>
                                <?php if (!empty($initial_cost) && !empty($end_datetime)) { ?>
                                    <input type="submit" value="Bid" id="submit_bid" />
                                <?php } ?>
                            </form>
                            <div id="bidding_response"></div>
                            <?php if (!empty($location)) { ?>
                                <p><?php echo 'Location: ' . $location; ?></p>
                            <?php } ?>
                            <?php the_content(); ?>
                        </div>
                        <!-- Column 2 End -->

                    </div>
                    <!-- Row 2 -->
                </section>
                <!-- Section 1 End -->


            </div>
        </div>
        <?php
    endwhile;
endif;

?>
<script>
    jQuery('.slider-for-single-auction').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false,
        fade: true,
        asNavFor: '.slider-nav-single-auction'
    });
    jQuery('.slider-nav-single-auction').slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        asNavFor: '.slider-for-single-auction',
        dots: true,
        centerMode: true,
        focusOnSelect: true
    });

    let bid_form = document.getElementById("bid_form");
    // Function to update the countdown
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
            let elem_c = document.getElementById("dk_timer_auction");
            elem_c.innerHTML = "<div class='aunction-start aunction-dates'>Auction will start on: <?php echo $start_datetime ?></div>";
            bid_form.style.display = 'none';
            // elem_c.parentNode.closest('section').remove();
            // Perform any action here when the countdown reaches zero
            // For example: redirect to another page, display a message, etc.
            return;
        }

        // If the time difference is less than or equal to 0, the countdown has reached zero
        if (timeDifference <= 0) {
            let elem_c = document.getElementById("dk_timer_auction");
            elem_c.innerHTML = "<div class='aunction-end aunction-dates'>Auction has been ended";
            bid_form.style.display = 'none';
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
        document.getElementById("dk_timer_auction").innerHTML = day_display + "<div class='itm_dk'><span class='itm_prefix_dk'>Hr</span><span class='itm_value_dk'>" + hoursString + "</span></div>:<div class='itm_dk'><span class='itm_prefix_dk'>Min</span><span class='itm_value_dk'>" + minutesString + "</span></div>:<div class='itm_dk'><span class='itm_prefix_dk'>Sec</span><span class='itm_value_dk'>" + secondsString + "</span></div></div>";
        bid_form.style.display = 'block';
    }

    // Call the updateCountdown function every second to keep the countdown updated
    setInterval(updateCountdown, 1000);

    // Call the function initially to display the countdown immediately
    updateCountdown();


    //Step Buttons
    jQuery(function ($) {
        $('.increment_audf').click(function () {
            var valueElement = $('#' + $(this).siblings('input').attr('id'));

            if ($(this).hasClass('plus_audf')) {
                valueElement.val(Math.max(parseInt(valueElement.val()) + <?php echo $default_bid_increment ?>));
            } else if (valueElement.val() > <?php echo $current_cost; ?>) { // Stops the value going into negatives
                valueElement.val(Math.max(parseInt(valueElement.val()) - <?php echo $default_bid_increment ?>));
            }

            return false;
        });
    });
</script>

<?php
get_footer();

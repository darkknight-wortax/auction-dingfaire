<?php
// Template Name: Auction Single
get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();
        $plugin_path = plugin_dir_url(__FILE__);

        // print_r($plugin_path);

?>

        <link rel="stylesheet" href="<?php echo esc_url($plugin_path . '../../css/slick.min.css'); ?>">
        <link rel="stylesheet" href="<?php echo esc_url($plugin_path . '../../css/slick-theme.min.css'); ?>">
        <script src="<?php echo esc_url($plugin_path . '../../js/slick.min.js'); ?>"></script>

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
                            $featured_image = '<div><img src="' . get_the_post_thumbnail_url(null, 'full') . '" alt="Featured Image" class="slick-image"></div>';

                            // Output the gallery images
                            $gallery_images = '';
                            $gallery_images_meta = get_post_meta(get_the_ID(), 'auction_gallery_images', true);
                            $gallery_image_ids = explode(',', $gallery_images_meta);
                            foreach ($gallery_image_ids as $image_id) {
                                $image_url = wp_get_attachment_image_src($image_id, 'full');
                                if ($image_url) {
                                    $gallery_images .= '<div><img src="' . esc_url($image_url[0]) . '" alt="Gallery Image" class="slick-image"></div>';
                                }
                            }
                            ?>
                            <div class="slider-for">
                                <?php echo $featured_image; ?>
                                <?php echo $gallery_images; ?>
                            </div>

                            <div class="slider-nav">
                                <?php echo $featured_image; ?>
                                <?php echo $gallery_images; ?>
                            </div>

                        </div>
                        <!-- Column 1 End-->

                        <!-- Column 2 -->
                        <div class="col_2_dk">
                            <h2><?php the_title(); ?></h2>
                            <div id="dk_timer_auction" class="timer_area"></div>
                            <?php
                            $start_datetime = date("Y-m-d H:i:s", strtotime(get_post_meta($post->ID, 'start_datetime', true)));
                            $end_datetime = date("Y-m-d H:i:s", strtotime(get_post_meta($post->ID, 'end_datetime', true)));
                            
                            $initial_cost = get_post_meta($post->ID, 'initial_cost', true) ;

                            echo 'Location: '.get_post_meta($post->ID, 'location', true) . '<br>';
                            ?>
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
    jQuery('.slider-for').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false,
        fade: true,
        asNavFor: '.slider-nav'
    });
    jQuery('.slider-nav').slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        asNavFor: '.slider-for',
        dots: true,
        centerMode: true,
        focusOnSelect: true
    });


    // Function to update the countdown
    function updateCountdown() {
        // Set the target date and time (in UTC)
        const targetDate = new Date("<?php echo $end_datetime ?>");

        // Get the current date and time (in the user's timezone)
        // const now = new Date("<?php echo $start_datetime ?>");

        const now = new Date();

        // Calculate the remaining time
        const timeDifference = targetDate - now;
        // console.log(timeDifference);

        // If the time difference is less than or equal to 0, the countdown has reached zero
        if (timeDifference <= 0) {
            let elem_c = document.getElementById("dk_timer_auction");
            elem_c.innerHTML = "Auction has been ended";
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
        let day_display = '';
        if (days > 0) {
            day_display = "<div class='itm_dk day_dk'><span class='itm_prefix_dk'>Day</span><span class='itm_value_dk'>" + daysString + "</span></div> ";
        }
        document.getElementById("dk_timer_auction").innerHTML = day_display + "<div class='itm_dk'><span class='itm_prefix_dk'>Hr</span><span class='itm_value_dk'>" + hoursString + "</span></div>:<div class='itm_dk'><span class='itm_prefix_dk'>Min</span><span class='itm_value_dk'>" + minutesString + "</span></div>:<div class='itm_dk'><span class='itm_prefix_dk'>Sec</span><span class='itm_value_dk'>" + secondsString + "</span></div>";
    }

    // Call the updateCountdown function every second to keep the countdown updated
    setInterval(updateCountdown, 1000);

    // Call the function initially to display the countdown immediately
    updateCountdown();
</script>
<style>
    #section_1 {}

    .section_1_auction {
        padding: 50px 10px;
    }

    .section_1_auction img {
        max-width: 100%;
        height: auto;
    }

    .sec_1_row_1 {
        max-width: 1200px;
        margin: auto;
        display: flex;
        flex-wrap: wrap;
        flex-direction: row;
    }

    .col_1_dk {
        width: calc(50% - 20px);
        padding: 10px;
        display: block;
    }

    .col_2_dk {
        width: calc(50% - 20px);
        padding: 10px;
    }

    /* Timer Style Start */
    div.timer_area {
        background-image: linear-gradient(rgb(0 0 0) 0%, rgb(129 129 129) 100%);
        border-radius: 5px;
        display: flex;
        flex-wrap: wrap;
        color: #fff;
        font-family: Inter;
        font-size: 40px;
        font-weight: 700;
        line-height: 48px;
        letter-spacing: 0em;
        text-align: center;
        align-content: center;
        justify-content: center;
        align-items: flex-end;
        padding: 10px;
        max-width: 340px;
        /* margin: auto; */
        margin-bottom: 10px;
    }

    .itm_dk {
        display: flex;
        flex-wrap: wrap;
        flex-direction: column;
        align-content: center;
        margin: 0 5px;
        width: 50px;
        transform: translatey(3px)
    }

    .itm_prefix_dk {
        font-size: 16px;
        line-height: 19px;
        text-transform: uppercase;
        text-align: center;
    }

    .day_dk.itm_dk {
        margin-right: 10px;
    }

    .discount_info_wrapper_dk h3 {
        color: #fff;
        font-family: Inter;
        font-size: 24px;
        font-weight: 800;
        line-height: 41px;
        letter-spacing: 0em;
        text-align: center;
        margin: 15px 0
    }

    .discount_info_wrapper_dk p {
        max-width: 215px;
        margin: auto;
        padding: 10px;
        background: #EF5726;
        font-family: Inter;
        font-size: 20px;
        font-weight: 700;
        line-height: 34px;
        letter-spacing: 0em;
        text-align: center;
        border-radius: 5px;
        color: #fff;
    }

    .discount_info_wrapper_dk p span {
        font-size: 32px;
        margin: 0 5px
    }

    /* Timer Style End */
</style>
<?php
get_footer();

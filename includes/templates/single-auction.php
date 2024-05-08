<?php
// Template Name: Auction Single
get_header();

if (have_posts()):
    while (have_posts()):
        the_post();
        $plugin_path = plugins_url() . '/auction-dingfaire-master';

        ?>

        <link rel="stylesheet" href="<?php echo esc_url($plugin_path . '/css/slick.min.css'); ?>">
        <link rel="stylesheet" href="<?php echo esc_url($plugin_path . '/css/slick-theme.min.css'); ?>">
        <script src="<?php echo esc_url($plugin_path . '/js/slick.min.js'); ?>"></script>

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
                            <?php
                            echo get_post_meta($post->ID, 'initial_cost', true) . '<br>';
                            echo get_post_meta($post->ID, 'start_datetime', true) . '<br>';
                            echo get_post_meta($post->ID, 'end_datetime', true) . '<br>';
                            echo get_post_meta($post->ID, 'location', true) . '<br>';
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
</style>
<?php
get_footer();

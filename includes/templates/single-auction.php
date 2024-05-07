<?php
// Template Name: Auction Single
get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();
?>
        <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

            <div class="entry-content">

                <!-- Section 1 -->
                <section id="section_1" class="section_1_auction">
                    <!-- Row 1 -->
                    <div class="sec_1_row_1 row_dk">

                        <!-- Column 1 -->
                        <div class="col_1_dk">
                            <?php the_post_thumbnail(); ?>
                        </div>
                        <!-- Column 1 End-->

                        <!-- Column 2 -->
                        <div class="col_2_dk">
                            <h1><?php the_title(); ?></h1>
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

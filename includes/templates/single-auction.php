<?php
// Template Name: Auction Single
get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();
        ?>
        <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <h1><?php the_title(); ?></h1>
            <div class="entry-content">
                Darkknight
                <?php the_post_thumbnail(); ?>
                <?php the_content(); ?>
            </div>
        </div>
        <?php
    endwhile;
endif;

get_footer();

<?php
/* Template Name: My Auctions */

get_header();
?>
<div class="my-auction-main">
    <div class="container d-flex">
        <div class="auction-sidebar">
            <?php include_once(plugin_dir_path(__FILE__) . '/dashboard-sidebar.php'); ?>
        </div>
        <div class="auction-content-area">
            <h1><?php the_title(); ?></h1>
            <?php
            if (!is_user_logged_in()) {
                echo '<p>Please login to view auctions.</p>';
            } else {
                global $wpdb;
                $plugin_path = plugin_dir_url(__FILE__);
                $user_id = get_current_user_id();
                $currency = get_option('auction_currency_general_dk');

                if(current_user_can('administrator')){

                }
                else{

                }

            ?>
                <p>This is the My Auctions page.</p>

            <?php } ?>
        </div>
    </div>
</div>
<?php
get_footer();
?>
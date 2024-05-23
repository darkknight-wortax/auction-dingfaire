<?php
/* Template Name: Submit Auction */

get_header();
?>
<div class="my-auction-main">
    <div class="container d-flex">
        <div class="auction-sidebar">
            <?php include_once (plugin_dir_path(__FILE__) . '../dashboard-sidebar.php'); ?>
        </div>
        <div class="auction-content-area">
            <h2>Submit Auction</h2>
            <?php echo do_shortcode('[auction_submission_form]');?>
        </div>
    </div>
</div>

<?php
get_footer();
?>
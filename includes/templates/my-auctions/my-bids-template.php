<?php
/* Template Name: My Bids */

get_header();
?>
<div class="my-auction-main">
    <div class="container d-flex">
        <div class="auction-sidebar">
            <?php include_once (plugin_dir_path(__FILE__) . '../dashboard-sidebar.php'); ?>
        </div>
        <div class="auction-content-area">
            <h1>My Bids</h1>
            <p>This is the My Auctions page.</p>
        </div>
    </div>
</div>

<?php
get_footer();
?>

<?php
/* Template Name: My Bids */

get_header();
?>
<div class="my-auction-main">
    <div class="container d-flex">
        <div class="auction-sidebar">
            <?php include_once(plugin_dir_path(__FILE__) . '../dashboard-sidebar.php'); ?>
        </div>
        <div class="auction-content-area">
            <h1>My Bids</h1>
            <?php
            if (!is_user_logged_in()) {
                echo '<p>Please login to view your bids.</p>';
            } else {
                $auction_id = $_GET['auction'];
                if ($auction_id) {
                    include_once(plugin_dir_path(__FILE__) . './user-bids.php');
                } else {

                    global $wpdb;
                    $plugin_path = plugin_dir_url(__FILE__);
                    $user_id = get_current_user_id();
                    $currency = get_option('auction_currency_general_dk');

                    $table_name = $wpdb->prefix . 'auction_bidding';

                    $get_auction_bids = $wpdb->get_results('SELECT DISTINCT `post_id` FROM `' . $table_name . '` WHERE `user_id`=' . $user_id . ' ORDER BY `id` DESC');

                    if ($get_auction_bids) {
            ?><table>
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Initial Cost</th>
                                    <th>Your Last Bid</th>
                                    <th>Current Bid</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php
                                foreach ($get_auction_bids as $item) {
                                    $post_id = $item->post_id;
                                    $current_bid = $wpdb->get_results('SELECT `bidding_amount` FROM `' . $table_name . '` WHERE `post_id`=' . $post_id . ' ORDER BY `id` DESC LIMIT 1')[0]->bidding_amount;
                                    $my_latest_bid = $wpdb->get_results('SELECT `bidding_amount` FROM `' . $table_name . '` WHERE `user_id`=' . $user_id . ' AND `post_id`=' . $post_id . ' ORDER BY `id` DESC LIMIT 1')[0]->bidding_amount;
                                    $feat_img = get_the_post_thumbnail_url($post_id);
                                    if (!$feat_img) {
                                        $feat_img = esc_url($plugin_path . '../../../assets/images/thumbnail-auction.png');
                                    }
                                ?>
                                    <tr>
                                        <td><img src="<?php echo $feat_img; ?>" width="50" height="50" /></td>
                                        <td><a href="?auction=<?php echo $post_id ?>"><?php echo get_the_title($post_id); ?></a></td>
                                        <td><?php echo $currency . get_post_meta($post_id, 'initial_cost', true); ?></td>
                                        <td><?php if ($my_latest_bid) {
                                                echo $currency . $my_latest_bid;
                                            } else {
                                                echo '-';
                                            } ?></td>
                                        <td><?php if ($current_bid) {
                                                echo $currency . $current_bid;
                                            } else {
                                                echo '-';
                                            }  ?></td>
                                    </tr>
                                <?php

                                }
                                ?>
                            </tbody>
                        </table>
                    <?php

                    } else {
                    ?><p>You haven't bid yet for any auction.</p><?php
                                                                }
                                                            }
                                                        } ?>
        </div>
    </div>
</div>

<?php
get_footer();
?>
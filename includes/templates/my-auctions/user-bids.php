<?php
$auction_id = $_GET['auction'];
$user_id = get_current_user_id();

if ($auction_id) {
    $table_name = $wpdb->prefix . 'auction_bidding';
    $get_auction_bids = $wpdb->get_results('SELECT `user_id`,`bidding_amount` FROM `' . $table_name . '` WHERE `post_id`=' . $auction_id . ' ORDER BY `id` DESC');

    if ($get_auction_bids) {
        $currency = get_option('auction_currency_general_dk');
?>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Bids</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($get_auction_bids as $bids) {
                    $cur_user = "";
                    if($bids->user_id == $user_id){
                        $cur_user = "my_bids_tr";
                    }
                    $user = get_user_by('id', $bids->user_id);
                ?>
                    <tr class="<?php echo $cur_user;?>">
                        <td><?php print_r($user->display_name);?></td>
                        <td><?php echo $currency . $bids->bidding_amount; ?></td>
                    </tr>
                <?php
                }
                ?>

            </tbody>
        </table>
<?php
    } else {
        echo '<p>No bids to this auction yet.</p>';
    }
    // echo '<pre>';
    //     print_r($get_auction_bids);
    // echo '</pre>';
} else {
    echo '<p>Page not found.</p>';
}

?>
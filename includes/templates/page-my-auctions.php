<?php
/* Template Name: My Auctions */

get_header();
?>
<div class="my-auction-main">
    <div class="container d-flex">
        <div class="auction-sidebar">
            <?php include_once (plugin_dir_path(__FILE__) . '/dashboard-sidebar.php'); ?>
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

                $args = array(
                    'post_type' => 'auction',
                    'posts_per_page' => -1,
                );

                if (!current_user_can('administrator')) {
                    $args['author'] = $user_id;
                }

                $auction_query = new WP_Query($args);

                if ($auction_query->have_posts()) {
                    echo '<table class="auction-table">';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th>Title</th>';
                    echo '<th>Initial Cost</th>';
                    echo '<th>Status</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';

                    while ($auction_query->have_posts()) {
                        $auction_query->the_post();
                        $start_time = get_post_meta(get_the_ID(), 'start_datetime', true);
                        $end_time = get_post_meta(get_the_ID(), 'end_datetime', true);
                        $current_time = current_time('timestamp');
                        
                        if ($start_time && $end_time) {
                            if ($current_time < strtotime($start_time)) {
                                $status = 'Auction not started yet';
                            } elseif ($current_time >= strtotime($start_time) && $current_time <= strtotime($end_time)) {
                                $status = 'Auction is active';
                            } else {
                                $status = 'Auction ended';
                            }
                        } else {
                            $status = 'No auction times set';
                        }
                        ?>
                        <tr>
                            <td><?php the_title(); ?></td>
                            <td><?php echo esc_html($currency) . get_post_meta(get_the_ID(), 'initial_cost', true); ?></td>
                            <td><?php echo esc_html($status); ?></td>

                        </tr>
                        <?php
                    }

                    echo '</tbody>';
                    echo '</table>';
                } else {
                    echo '<p>No auctions found against your profile.</p>';
                }

                wp_reset_postdata(); 
            }
            ?>
        </div>
    </div>
</div>
<?php
get_footer();
?>
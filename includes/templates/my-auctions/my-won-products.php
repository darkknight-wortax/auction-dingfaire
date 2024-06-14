<?php
/* Template Name: My Won Products */
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}
$current_user_id = get_current_user_id();
$args = array(
    'post_type' => 'product',
    'meta_query' => array(
        'relation' => 'AND',
        array(
            'key' => '_highest_bidder_id',
            'value' => $current_user_id,
            'compare' => '='
        ),
        array(
            'key' => '_stock_status',
            'value' => 'instock',
            'compare' => '='
        )
    )
);

$won_products = new WP_Query($args);
get_header();
?>
<div class="my-auction-main">
    <div class="container d-flex">
        <div class="auction-sidebar">
            <?php include_once(plugin_dir_path(__FILE__) . '../dashboard-sidebar.php'); ?>
        </div>
        <div class="auction-content-area">
            <h1>My Won Auctions</h1>
            <?php if ($won_products->have_posts()) : ?>
                <table>
                    <thead>
                        <tr>
                            <th>Product Title</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($won_products->have_posts()) : $won_products->the_post();
                            global $product; ?>
                            <tr>
                                <td><?php the_title(); ?></td>
                                <td><?php echo $product->get_price_html(); ?></td>
                                <td>
                                    <form action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
                                        <input type="hidden" name="add-to-cart" value="<?php echo $product->get_id(); ?>">
                                        <button type="submit">Buy Now</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>You have not won any Auction.</p>
            <?php endif; ?>

            <?php wp_reset_postdata(); ?>
        </div>
    </div>
</div>

<?php
get_footer();
?>
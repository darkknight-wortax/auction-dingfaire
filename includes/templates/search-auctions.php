<?php
// Get search parameters
$search_query = get_query_var('search_query', '');
$category = get_query_var('category', '');
$location = get_query_var('location', '');
$radius = get_query_var('radius', '');

$args = array(
    'post_type' => 'auction',
    'posts_per_page' => -1,
    's' => $search_query,
);

// If a category is selected, filter by category
if ($category) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'auction_type',
            'field' => 'slug',
            'terms' => $category,
        ),
    );
}

// Custom query
$query = new WP_Query($args);
get_header();
?>
<div class="auction-search-results">
    <?php if ($query->have_posts()) : ?>
        <h2>Search Results</h2>
        <ul>
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <li>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else : ?>
        <p>No auctions found.</p>
    <?php endif; ?>
</div>
<?php
wp_reset_postdata();
get_footer();


<?php

// Determine if HTTPS is enabled
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
// Get the current URL
$current_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$get_url = get_site_url();
?>

<ul class="menu-inline">
    <li class="<?php echo ($current_url == $get_url.'/my-auctions/') ? 'active-menu' : ''; ?>">
        <a href="<?php echo $get_url?>/my-auctions/">Dashboard - Auctions</a>
    </li>
    <li class="<?php echo ($current_url == $get_url.'/my-auctions/my-bids/') ? 'active-menu' : ''; ?>">
        <a href="<?php echo $get_url?>/my-auctions/my-bids/">My Bids</a>
    </li>
    <li class="<?php echo ($current_url == $get_url.'/my-auctions/submit-auction/') ? 'active-menu' : ''; ?>">
        <a href="<?php echo $get_url?>/my-auctions/submit-auction/">Submit Auction</a>
    </li>
</ul>

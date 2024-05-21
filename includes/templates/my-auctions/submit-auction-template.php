<?php
/* Template Name: Submit Auction */

get_header();
?>

<div class="content">
    <h2>Submit Auction</h2>
    <form id="submit-auction-form" method="post">
        <label for="auction-title">Auction Title:</label>
        <input type="text" id="auction-title" name="auction-title" required>

        <label for="auction-description">Auction Description:</label>
        <textarea id="auction-description" name="auction-description" required></textarea>

        <button type="submit">Submit</button>
    </form>
    <div id="form-message"></div>
</div>

<?php
get_footer();
?>

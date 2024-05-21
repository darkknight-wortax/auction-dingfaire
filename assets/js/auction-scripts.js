jQuery(document).ready(function($) {
    // JavaScript functionality for Auction plugin
    $('.auction-item').click(function() {
        // Handle click event for auction items
        $(this).toggleClass('selected');
    });

    //Ajax Form Submission
    $('#bid_form').on('submit', function(e) {
        e.preventDefault();

        var bidding_cost = $('#auction_cost').val();

        $.ajax({
            type: 'POST',
            url: dkAuctionSubmissionAjax.ajax_url,
            data: {
                action: 'submit_bidding_form',
                nonce: dkAuctionSubmissionAjax.nonce,
                bidding_cost: bidding_cost
            },
            success: function(response) {
                if (response.success) {
                    $('#bidding_response').html('<p style="color:green">' + response.data + '</p>');
                } else {
                    $('#bidding_response').html('<p style="color:red">' + response.data + '</p>');
                }
            }
        });
    });
});

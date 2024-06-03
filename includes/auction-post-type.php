<?php
// Register custom post type "Auction"
function register_auction_post_type() {
    $labels = array(
        'name' => 'Auctions',
        'singular_name' => 'Auction',
        'add_new' => 'Add New Auction',
        'add_new_item' => 'Add New Auction',
        'edit_item' => 'Edit Auction',
        'new_item' => 'New Auction',
        'view_item' => 'View Auction',
        'search_items' => 'Search Auctions',
        'not_found' => 'No auctions found',
        'not_found_in_trash' => 'No auctions found in trash',
        'menu_name' => 'Auctions'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'auction'),
        'supports' => array('title', 'editor', 'thumbnail'),
    );

    register_post_type('auction', $args);

    // Register custom fields for the Auction post type
    add_action('add_meta_boxes', 'auction_add_custom_fields');
}

// Add custom fields to the Auction post type
function auction_add_custom_fields() {
    add_meta_box('auction_fields', 'Auction Details', 'auction_render_custom_fields', 'auction', 'normal', 'default');
}

// Render custom fields in the Auction post type editor
function auction_render_custom_fields() {
    global $post;

    // Add your custom fields HTML here
    echo '<label for="initial_cost">Initial Cost:</label><br>';
    echo '<input type="text" id="initial_cost" name="initial_cost" value="' . get_post_meta($post->ID, 'initial_cost', true) . '"><br>';

    echo '<label for="start_datetime">Start Date/Time:</label><br>';
    echo '<input type="datetime-local" id="start_datetime" name="start_datetime" value="' . get_post_meta($post->ID, 'start_datetime', true) . '"><br>';

    echo '<label for="end_datetime">End Date/Time:</label><br>';
    echo '<input type="datetime-local" id="end_datetime" name="end_datetime" value="' . get_post_meta($post->ID, 'end_datetime', true) . '"><br>';

    echo '<label for="location">Location:</label><br>';
    echo '<input type="text" id="location" name="location" value="' . get_post_meta($post->ID, 'location', true) . '"><br>';

    echo '<label for="shipping_fee">Shipping Fee:</label><br>';
    echo '<input type="text" id="shipping_fee" name="shipping_fee" value="' . get_post_meta($post->ID, 'shipping_fee', true) . '"><br>';
}

// Save custom field data when the Auction post type is saved
add_action('save_post', 'auction_save_custom_fields');
function auction_save_custom_fields($post_id) {
    if (array_key_exists('initial_cost', $_POST)) {
        update_post_meta(
            $post_id,
            'initial_cost',
            sanitize_text_field($_POST['initial_cost'])
        );
    }

    if (array_key_exists('start_datetime', $_POST)) {
        update_post_meta(
            $post_id,
            'start_datetime',
            sanitize_text_field($_POST['start_datetime'])
        );
    }

    if (array_key_exists('end_datetime', $_POST)) {
        update_post_meta(
            $post_id,
            'end_datetime',
            sanitize_text_field($_POST['end_datetime'])
        );
    }

    if (array_key_exists('location', $_POST)) {
        update_post_meta(
            $post_id,
            'location',
            sanitize_text_field($_POST['location'])
        );
    }
    if (array_key_exists('shipping_fee', $_POST)) {
        update_post_meta(
            $post_id,
            'shipping_fee',
            sanitize_text_field($_POST['shipping_fee'])
        );
    }
}

// Add image gallery custom field to Auction post type
add_action('add_meta_boxes', 'auction_add_image_gallery_field');
function auction_add_image_gallery_field() {
    add_meta_box(
        'auction_image_gallery',
        'Auction Image Gallery',
        'auction_render_image_gallery_field',
        'auction',
        'normal',
        'default'
    );
}

function auction_render_image_gallery_field($post) {
    // Retrieve existing gallery images
    $gallery_images = get_post_meta($post->ID, 'auction_gallery_images', true);
    $image_ids = explode(',', $gallery_images);
    ?>
    <div>
        <label for="auction_gallery_images">Gallery Images:</label><br>
        <input type="button" id="auction_gallery_upload_btn" class="button" value="Upload Images">
        <input type="hidden" id="auction_gallery_images" name="auction_gallery_images" value="<?php echo esc_attr($gallery_images); ?>">
        <div id="auction_gallery_preview">
            <?php 
            // print_r($image_ids);
            foreach ($image_ids as $image_id) : ?>
                <?php 
                if($image_id){
                $image_url = wp_get_attachment_image_src($image_id, 'thumbnail'); ?>
                <div class="gallery-image">
                    <img style="max-width:150px" src="<?php echo esc_url($image_url[0]); ?>" alt="Gallery Image">
                    <div class="gallery-actions">
                        <!-- <a href="#" class="modify-image" data-image-id="<?php echo esc_attr($image_id); ?>">Modify</a> -->
                        <a href="#" class="delete-image" data-image-id="<?php echo esc_attr($image_id); ?>">Delete</a>
                    </div>
                </div>
            <?php
                } 
            endforeach; ?>
        </div>
    </div>

    <script>

        jQuery(document).ready(function($) {
            $('#auction_gallery_upload_btn').click(function(e) {
            e.preventDefault();

            // Define and open the WordPress media uploader
            var imageUploader = wp.media({
                title: 'Upload Images for Auction Gallery',
                button: {
                    text: 'Add to Gallery'
                },
                multiple: true
            });

            // Handle selected images
            imageUploader.on('select', function() {
                var attachment = imageUploader.state().get('selection').toJSON();
                var imageIDs = [];
                console.log(attachment);
                // Extract image IDs and build image preview HTML
                for (var i = 0; i < attachment.length; i++) {
                    imageIDs.push(attachment[i].id);
                    $('#auction_gallery_preview').append('<div class="gallery-image"><img style="max-width:150px"  src="' + attachment[i].url + '" alt="' + attachment[i].alt + '"><div class="gallery-actions"><a href="#" class="delete-image" data-image-id="'+attachment[i].id+'">Delete</a></div></div>');
                }

                // Update the hidden input field with image IDs
                $('#auction_gallery_images').val(imageIDs.join(','));
            });

            // Open the media uploader
            imageUploader.open();
        });

            // Modify image action
            $('#auction_gallery_preview').on('click', '.modify-image', function(e) {
                e.preventDefault();
                var imageId = $(this).data('image-id');
                // Add your code to handle modify action (e.g., open modal for editing)
            });

            // Delete image action
            $('#auction_gallery_preview').on('click', '.delete-image', function(e) {
                e.preventDefault();
                var imageId = $(this).data('image-id');
                var galleryImages = $('#auction_gallery_images').val().split(',');
                var updatedGalleryImages = galleryImages.filter(function(id) {
                    return id !== imageId.toString();
                });
                $('#auction_gallery_images').val(updatedGalleryImages.join(','));
                $(this).closest('.gallery-image').remove(); // Remove the image preview from DOM
            });
        });


    </script>

    <?php
}


// Save image gallery custom field data
add_action('save_post', 'auction_save_image_gallery_field');
function auction_save_image_gallery_field($post_id) {
    if (isset($_POST['auction_gallery_images'])) {
        update_post_meta($post_id, 'auction_gallery_images', sanitize_text_field($_POST['auction_gallery_images']));
    }
}

<?php
global $wpdb;
$gallery_id = absint($_GET['id']);
$gallery_table = $wpdb->prefix . 'cuny_slider_galleries';
$image_table = $wpdb->prefix . 'cuny_slider_images';

$gallery = $wpdb->get_row($wpdb->prepare("SELECT * FROM $gallery_table WHERE id = %d", $gallery_id));
if (!$gallery) {
    echo '<div class="notice notice-error"><p>Gallery not found.</p></div>';
    return;
}
$images = $wpdb->get_results($wpdb->prepare("SELECT * FROM $image_table WHERE gallery_id = %d AND deleted = 0", $gallery_id));
?>
<div class="wrap">
    <h2>Editing Gallery: <?php echo esc_html($gallery->gallery_name); ?></h2>
    <form method="post">
        <input type="hidden" name="gallery_id" value="<?php echo $gallery_id; ?>">
        <label><h3>Gallery Style:</h3></label>
        <select name="new_style">
            <option value="slider" <?php selected($gallery->style, 'slider'); ?>>Slider</option>
            <option value="gallery" <?php selected($gallery->style, 'gallery'); ?>>Lazy Load Gallery</option>
            <option value="slider-gallery" <?php selected($gallery->style, 'slider-gallery'); ?>>Slider + Gallery</option>
        </select>
        <input type="submit" name="update_gallery_style" value="Update Style" class="button-primary">
    </form>
    <hr>
    <h3>Add Image:</h3>
    <form method="post">
        <input type="hidden" name="new_image_id" id="new_image_id">
        <div id="new_image_preview" style="margin-top:10px;"></div>
        <input type="hidden" name="gallery_id" value="<?php echo $gallery_id; ?>">
        <button type="button" id="add-image" class="button">Select Image</button>
        <input type="submit" class="button-primary" name="add_image_to_gallery" value="Add Image to Gallery">
    </form>
    <hr>
    <h3>Images in Gallery</h3>
    <div class="gallery-container">
        <?php foreach ($images as $img): ?>
            <div class="gallery-item isVisible-<?php echo $img->visible; ?>">
                <div class="image-wrapper">
                    <?php echo wp_get_attachment_image($img->image_id, 'thumbnail'); ?>
                </div>
                <div class="image-options">
                    <a class="button toggle-visibility-button" href="?page=cuny-gallery&view=edit&id=<?php echo $gallery_id; ?>&toggle_image=<?php echo $img->id; ?>">
                        <?php echo $img->visible ? 'Hide' : 'Show'; ?>
                    </a>
                    <a class="button delete-button" href="?page=cuny-gallery&view=edit&id=<?php echo $gallery_id; ?>&delete_image=<?php echo $img->id; ?>" onclick="return confirm('Delete image?')">Delete</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
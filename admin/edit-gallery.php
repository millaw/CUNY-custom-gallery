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
$images = $wpdb->get_results($wpdb->prepare("SELECT * FROM $image_table WHERE gallery_id = %d ORDER BY sort_order ASC", $gallery_id));

?>
<div class="cuny-gallery-wrapper wrap">
    <h2><?php echo esc_html($gallery->gallery_name); ?></h2>
    <h3>Edit Gallery: </h3>
    <form method="post">
        <input type="hidden" name="gallery_id" value="<?php echo $gallery_id; ?>">
        <table class="widefat">
            <tr style="background-color: #f1f1f1; font-weight: bold;">
                <th style="width: 50%;">Gallery Name:</th>
                <th>Gallery Style:</th>
                <th>Shortcode:</th>
            </tr>
            <tr>
                <td><input type="text" name="new_name" value="<?php echo esc_html($gallery->gallery_name); ?>" required style="width: 100%;"></td>
                <td>
                    <select name="new_style" id="gallery_style">
                        <option value="slider" <?php selected($gallery->gallery_style, 'slider'); ?>>Slider</option>
                        <option value="gallery" <?php selected($gallery->gallery_style, 'gallery'); ?>>Lazy Load Gallery</option>
                        <option value="slider-gallery" <?php selected($gallery->gallery_style, 'slider-gallery'); ?>>Slider + Gallery</option>
                    </select>
                </td>
                <td><?php echo esc_html($gallery->gallery_shortcode); ?></td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: center; padding: 10px;">
                    <input type="submit" name="update_gallery" value="Update Gallery" class="button button-primary">
                    <input type="submit" name="duplicate_gallery" value="Duplicate Gallery" class="button button-warning" onclick="return confirm('Duplicate this gallery?')">
                </td>
            </tr>
        </table>
    </form>
    <hr>
    <h3>Add Image:</h3>
    <form method="post">
        <input type="hidden" name="new_image_id" id="new_image_id">
        <div id="new_image_preview" style="margin-top:10px;"></div>
        <input type="hidden" name="gallery_id" value="<?php echo $gallery_id; ?>">
        <button type="button" id="add-image" class="button button-primary">Select Image</button>
        <input type="submit" class="button-primary" name="add_image_to_gallery" value="Add Image to Gallery">
    </form>
    <hr>
    <h3>Images in Gallery</h3>
    <div class="gallery-container sortable-images">
        <?php foreach ($images as $img): ?>
            <div class="gallery-item isVisible-<?php echo $img->visible; ?>" 
            data-id="<?php echo $img->id; ?>"
            data-image-id="<?php echo $img->image_id; ?>"
            data-alt="<?php echo esc_attr($img->image_alt_text); ?>" 
            data-visible="<?php echo $img->visible; ?>">
                <div class="image-wrapper">
                    <?php echo wp_get_attachment_image($img->image_id, 'thumbnail'); ?>
                    <button class="menu-button" title="Options">⋮</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<!-- Modal -->
<div id="imageOptionsModal" class="cuny-modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Edit Image Options</h3>
      <button type="button" class="close-modal" id="closeModalBtn">&times;</button>
    </div>
    <form method="post">
      <input type="hidden" name="image_id" id="modalImageId" data-gallery="<?php echo $gallery_id; ?>">
      <input type="hidden" name="gallery_id" value="<?php echo $gallery_id; ?>">

      <div class="modal-body">
        <label for="modalAltText">Alt Text:</label>
        <input type="text" name="new_alt_text" id="modalAltText" class="widefat" placeholder="Enter alt text">
        <input type="submit" name="update_image_alt_text" value="Save Alt Text" class="button button-primary">
      </div>
    </form>
    <hr>
    <div class="modal-actions">
      <a href="#" class="button button-success" id="toggleVisibilityBtn">Show/Hide</a>
      <a href="#" class="button button-danger" id="deleteImageBtn" onclick="return confirm('Delete image?')">Delete</a>
    </div>
  </div>
</div>
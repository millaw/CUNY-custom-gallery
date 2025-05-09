<?php
global $wpdb;
$gallery_table = $wpdb->prefix . 'cuny_slider_galleries';
$galleries = $wpdb->get_results("SELECT * FROM $gallery_table ORDER BY id DESC");
// Sort items alphabetically by title
usort($galleries, function($a, $b) {
    return strcasecmp($a->gallery_name, $b->gallery_name);
});
function display_style_name($g) {
    $style_friendly_name = '';
		switch ($g) {
        case 'gallery':
            $style_friendly_name = 'Lazy Load Gallery';
            break;
        case 'slider-gallery':
            $style_friendly_name = 'Slider + Gallery';
            break;
        default:
            $style_friendly_name = 'Slider';
    }
	return $style_friendly_name;
}
?>
<div class="cuny-gallery-wrapper wrap">
    <h2>Create New Gallery</h2>
    <form method="post">
        <p style="float:left; width: 50%;">
            <label for="post_id">New Gallery Name:</label><br>
            <input type="text" name="gallery_name" id="gallery_name" required style="width: 100%;" placeholder="Enter gallery name">
        </p>
        <p style="float:left; width: 40%; padding-left: 10%;">
            <label for="style">New Gallery Style:</label><br>
            <select name="gallery_style" id="style" style="width: 100%;">
                <option value="slider">Slider</option>
                <option value="gallery">Lazy Loader Gallery</option>
                <option value="slider-gallery">Slider + Gallery</option>
            </select>
        </p>
        <p><input type="submit" class="button-primary" value="Create Gallery"></p>
    </form>

    <h2>CUNY Galleries</h2>
    <table class="widefat">
        <thead><tr><th>Name</th><th>Style</th><th>Shortcode</th><th>Visible</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($galleries as $g): ?>
            <tr>
                <td><?php echo esc_html($g->gallery_name); ?></td>
                <td><?php echo display_style_name(esc_html($g->gallery_style)); ?></td>
                <td><?php echo esc_html($g->gallery_shortcode); ?></td>
                <td><?php echo $g->visible ? '<span style="color: green;">Yes</span>' : '<span style="color: red;">No</span>'; ?></td>
                <td>
                    <a class="button button-primary" href="?page=cuny-gallery&view=edit&id=<?php echo $g->id; ?>">Edit</a>
                    <a class="button button-success" href="?page=cuny-gallery&toggle_gallery=<?php echo $g->id; ?>"><?php echo $g->visible ? 'Hide' : 'Show'; ?></a>
                    <a class="button button-danger" href="?page=cuny-gallery&delete_gallery=<?php echo $g->id; ?>" onclick="return confirm('Delete this gallery?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

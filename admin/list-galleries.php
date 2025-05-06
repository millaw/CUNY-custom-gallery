<?php
global $wpdb;
$gallery_table = $wpdb->prefix . 'cuny_slider_galleries';
$galleries = $wpdb->get_results("SELECT * FROM $gallery_table WHERE deleted = 0 ORDER BY id DESC");
// Sort items alphabetically by title
usort($galleries, function($a, $b) {
    return strcasecmp($a->gallery_name, $b->gallery_name);
});
// Get already-used post IDs
$used_ids = $wpdb->get_col("SELECT post_id FROM {$gallery_table} WHERE deleted = 0");

// Get all public post types
$post_types = get_post_types(['public' => true], 'objects');
unset($post_types['attachment'], $post_types['nav_menu_item']); // remove unwanted types

// Fetch published items from all CPTs
$args = [
    'post_type'   => array_keys($post_types),
    'post_status' => 'publish',
    'numberposts' => -1,
];
$items = get_posts($args);

// Sort items alphabetically by title
usort($items, function($a, $b) {
    return strcasecmp($a->post_title, $b->post_title);
});
?>
<div class="wrap">
    <h2>Create New Gallery</h2>
    <form method="post">
        <p>
            <label for="post_id">Select Page / Post / Item:</label><br>
            <select name="post_id" id="post_id" required>
                <option value="">-- Select --</option>
                <?php foreach ($items as $item): if (in_array($item->ID, $used_ids)) continue; ?>
                    <?php $label = ucfirst($post_types[$item->post_type]->labels->singular_name); ?>
                    <option value="<?php echo esc_attr($item->ID); ?>">[<?php echo $label; ?>] <?php echo esc_html($item->post_title); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="style">Gallery Style:</label><br>
            <select name="style" id="style">
                <option value="slider">Slider</option>
                <option value="gallery">Lazy Loader Gallery</option>
                <option value="slider-gallery">Slider + Gallery</option>
            </select>
        </p>
        <p><input type="submit" class="button-primary" value="Create Gallery"></p>
    </form>

    <h2>CUNY Galleries</h2>
    <table class="widefat">
        <thead><tr><th>Name</th><th>Style</th><th>Visible</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($galleries as $g): ?>
            <tr>
                <td><?php echo esc_html($g->gallery_name); ?></td>
                <td><?php echo esc_html($g->style); ?></td>
                <td><?php echo $g->visible ? 'Yes' : 'No'; ?></td>
                <td>
                    <a class="button" href="?page=cuny-gallery&view=edit&id=<?php echo $g->id; ?>">Edit</a>
                    <a class="button" href="?page=cuny-gallery&toggle_gallery=<?php echo $g->id; ?>"><?php echo $g->visible ? 'Hide' : 'Show'; ?></a>
                    <a class="button" href="?page=cuny-gallery&delete_gallery=<?php echo $g->id; ?>" onclick="return confirm('Delete this gallery?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

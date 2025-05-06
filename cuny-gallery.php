<?php
/**
 * Plugin Name: CUNY Gallery
 * Description: Gallery creation, image management, and frontend shortcode display. Insert a shortcode [cuny_gallery] into a page to display the gallery.
 * Version: 1.0
 * Author: Milla Wynn
 */

if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, function() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $gallery_table = $wpdb->prefix . 'cuny_slider_galleries';
    $image_table = $wpdb->prefix . 'cuny_slider_images';

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    dbDelta("CREATE TABLE $gallery_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        gallery_name VARCHAR(255) NOT NULL,
        post_id BIGINT(20) UNSIGNED NOT NULL,
        style VARCHAR(50) DEFAULT 'slider',
        visible TINYINT(1) DEFAULT 1,
        deleted TINYINT(1) DEFAULT 0,
        PRIMARY KEY (id)
    ) $charset_collate;");

    dbDelta("CREATE TABLE $image_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        gallery_id BIGINT(20) UNSIGNED NOT NULL,
        image_id BIGINT(20) UNSIGNED NOT NULL,
        visible TINYINT(1) DEFAULT 1,
        deleted TINYINT(1) DEFAULT 0,
        PRIMARY KEY (id)
    ) $charset_collate;");
});

add_action('admin_menu', function() {
    add_menu_page('CUNY Gallery', 'CUNY Gallery', 'manage_options', 'cuny-gallery', function() {
        $view = $_GET['view'] ?? 'list';
        if ($view === 'edit') {
            require_once plugin_dir_path(__FILE__) . 'admin/edit-gallery.php';
        } else {
            require_once plugin_dir_path(__FILE__) . 'admin/list-galleries.php';
        }
    }, 'dashicons-format-gallery');
});

add_action('admin_enqueue_scripts', function($hook) {
    if (strpos($hook, 'cuny-gallery') !== false) {
        wp_enqueue_media();
        wp_enqueue_script('cuny-gallery-admin', plugin_dir_url(__FILE__) . 'admin/assets/admin.js', ['jquery'], null, true);
        wp_enqueue_style('cuny-gallery-admin-style', plugin_dir_url(__FILE__) . 'admin/assets/admin.css');
    }
});

add_action('wp_enqueue_scripts', function () {
    if (is_singular() && has_shortcode(get_post()->post_content, 'cuny_gallery')) {
        wp_enqueue_style('cuny-gallery-style', plugin_dir_url(__FILE__) . 'frontend/assets/gallery-slider.css');
        wp_enqueue_script('cuny-gallery-script', plugin_dir_url(__FILE__) . 'frontend/assets/gallery-slider.js', ['jquery'], time(), true);
    }
});

// Admin-safe redirect logic
add_action('admin_init', function() {
    global $wpdb;
    $gallery_table = $wpdb->prefix . 'cuny_slider_galleries';
    $image_table = $wpdb->prefix . 'cuny_slider_images';

    if (isset($_GET['toggle_image'], $_GET['id'])) {
        $id = absint($_GET['toggle_image']);
        $current = $wpdb->get_var($wpdb->prepare("SELECT visible FROM $image_table WHERE id = %d", $id));
        $wpdb->update($image_table, ['visible' => $current ? 0 : 1], ['id' => $id]);
        wp_redirect(admin_url('admin.php?page=cuny-gallery&view=edit&id=' . absint($_GET['id'])));
        exit;
    }

    if (isset($_GET['delete_image'], $_GET['id'])) {
        $id = absint($_GET['delete_image']);
        $wpdb->update($image_table, ['deleted' => 1], ['id' => $id]);
        wp_redirect(admin_url('admin.php?page=cuny-gallery&view=edit&id=' . absint($_GET['id'])));
        exit;
    }

    if (isset($_GET['toggle_gallery'])) {
        $id = absint($_GET['toggle_gallery']);
        $current = $wpdb->get_var($wpdb->prepare("SELECT visible FROM $gallery_table WHERE id = %d", $id));
        $wpdb->update($gallery_table, ['visible' => $current ? 0 : 1], ['id' => $id]);
        wp_redirect(admin_url('admin.php?page=cuny-gallery'));
        exit;
    }

    if (isset($_GET['delete_gallery'])) {
        $id = absint($_GET['delete_gallery']);
        $wpdb->update($gallery_table, ['deleted' => 1], ['id' => $id]);
        wp_redirect(admin_url('admin.php?page=cuny-gallery'));
        exit;
    }

    if (isset($_POST['add_image_to_gallery'], $_POST['new_image_id'], $_POST['gallery_id'])) {
        $img_id = absint($_POST['new_image_id']);
        $gal_id = absint($_POST['gallery_id']);
        $wpdb->insert($image_table, [
            'gallery_id' => $gal_id,
            'image_id' => $img_id,
            'visible' => 1,
            'deleted' => 0
        ]);
        wp_redirect(admin_url('admin.php?page=cuny-gallery&view=edit&id=' . $gal_id));
        exit;
    }

    if (isset($_POST['update_gallery_style'], $_POST['gallery_id'], $_POST['new_style'])) {
        $gal_id = absint($_POST['gallery_id']);
        $new_style = sanitize_text_field($_POST['new_style']);
        $wpdb->update($gallery_table, ['style' => $new_style], ['id' => $gal_id]);
        wp_redirect(admin_url('admin.php?page=cuny-gallery&view=edit&id=' . $gal_id));
        exit;
    }

    // Create a new gallery
    if (isset($_POST['post_id'], $_POST['style'])) {
        $post_id = absint($_POST['post_id']);
        $style = sanitize_text_field($_POST['style']);
        $post = get_post($post_id);
        if ($post) {
            $wpdb->insert($gallery_table, [
                'gallery_name' => $post->post_title,
                'post_id' => $post_id,
                'style' => $style,
                'visible' => 1,
                'deleted' => 0
            ]);
            wp_redirect(admin_url('admin.php?page=cuny-gallery'));
            exit;
        }
    }
});

if (!is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'frontend/frontend.php';
}

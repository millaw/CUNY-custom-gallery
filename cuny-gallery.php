<?php
/**
 * Plugin Name: CUNY Gallery
 * Plugin URI: https://www.github.com/millawy/cuny-custom-gallery
 * Description: Gallery creation, image management, and frontend shortcode display. Insert a shortcode [cuny_gallery_X] into a page to display the gallery.
 * Version: 1.3.0
 * Author: Milla Wynn
 * Author URI: https://www.github.com/millaw
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cuny-gallery
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
        gallery_shortcode VARCHAR(255) DEFAULT '',
        gallery_style VARCHAR(50) DEFAULT 'slider',
        visible TINYINT(1) DEFAULT 1,
        PRIMARY KEY (id)
    ) $charset_collate;");

    dbDelta("CREATE TABLE $image_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        gallery_id BIGINT(20) UNSIGNED NOT NULL,
        image_id BIGINT(20) UNSIGNED NOT NULL,
        image_alt_text VARCHAR(255) DEFAULT '',
        sort_order INT UNSIGNED DEFAULT 0,
        visible TINYINT(1) DEFAULT 1,
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
        wp_enqueue_script('cuny-gallery-admin', plugin_dir_url(__FILE__) . 'admin/assets/admin.js', ['jquery'], time(), true);
        wp_enqueue_style('cuny-gallery-admin-style', plugin_dir_url(__FILE__) . 'admin/assets/admin.css', [], time());

        // Pass current gallery ID to JS if present
        if (isset($_GET['view']) && $_GET['view'] === 'edit' && isset($_GET['id'])) {
            wp_localize_script('cuny-gallery-admin', 'CUNYGalleryData', [
                'gallery_id' => absint($_GET['id'])
            ]);
        }
		
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('cuny-gallery-reorder', plugin_dir_url(__FILE__) . 'admin/assets/reorder.js', ['jquery', 'jquery-ui-sortable'], null, true);
        wp_localize_script('cuny-gallery-reorder', 'cunyGalleryAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cuny_gallery_reorder')
        ]);
    }
});

add_action('wp_enqueue_scripts', function () {
    $post = get_post();
    if (is_singular() && $post && preg_match('/\[cuny_gallery(_\d+)?\]/', $post->post_content)) {
        wp_enqueue_style('cuny-gallery-style', plugin_dir_url(__FILE__) . 'frontend/assets/gallery-slider.css');
        wp_enqueue_script('cuny-gallery-script', plugin_dir_url(__FILE__) . 'frontend/assets/gallery-slider.js', ['jquery'], time(), true);
    }
});

// Admin-safe redirect logic
add_action('admin_init', function() {
    global $wpdb;
    $gallery_table = $wpdb->prefix . 'cuny_slider_galleries';
    $image_table = $wpdb->prefix . 'cuny_slider_images';

    // Create a new gallery
    if (isset($_POST['gallery_name'], $_POST['gallery_style'])) {
        $gallery_name = sanitize_text_field($_POST['gallery_name']);
        $style = sanitize_text_field($_POST['gallery_style']);
    
        $inserted = $wpdb->insert($gallery_table, [
            'gallery_name' => $gallery_name,
            'gallery_style' => $style,
            'visible' => 1
        ]);
    
        if ($inserted) {
            $gallery_id = $wpdb->insert_id;
            $shortcode = '[cuny_gallery_' . $gallery_id . ']';
    
            $wpdb->update(
                $gallery_table,
                ['gallery_shortcode' => $shortcode],
                ['id' => $gallery_id]
            );
    
            wp_redirect(admin_url('admin.php?page=cuny-gallery'));
            exit;
        }
    }
    // Duplicate gallery
    if (isset($_POST['duplicate_gallery'], $_POST['gallery_id'])) {
        $original_id = absint($_POST['gallery_id']);
        $original = $wpdb->get_row($wpdb->prepare("SELECT * FROM $gallery_table WHERE id = %d", $original_id));
        if ($original) {
            $wpdb->insert($gallery_table, [
                'gallery_name' => $original->gallery_name . ' (Copy)',
                'gallery_style' => $original->gallery_style,
                'visible' => 1
            ]);
            $new_id = $wpdb->insert_id;
            $shortcode = '[cuny_gallery_' . $new_id . ']';
            $wpdb->update($gallery_table, ['gallery_shortcode' => $shortcode], ['id' => $new_id]);

            // Copy images
            $images = $wpdb->get_results($wpdb->prepare("SELECT * FROM $image_table WHERE gallery_id = %d", $original_id));
            foreach ($images as $img) {
                $wpdb->insert($image_table, [
                    'gallery_id' => $new_id,
                    'image_id' => $img->image_id,
                    'image_alt_text' => $img->image_alt_text,
                    'visible' => $img->visible
                ]);
            }

            wp_redirect(admin_url('admin.php?page=cuny-gallery'));
            exit;
        }
    }
    // Add image to gallery
    if (isset($_POST['add_image_to_gallery'], $_POST['new_image_id'], $_POST['gallery_id'])) {
        $img_id = absint($_POST['new_image_id']);
        $gal_id = absint($_POST['gallery_id']);
        $image_alt_text = get_post_meta($img_id, '_wp_attachment_image_alt', true);
        $wpdb->insert($image_table, [
            'gallery_id' => $gal_id,
            'image_id' => $img_id,
            'image_alt_text' => $image_alt_text,
            'visible' => 1
        ]);
        wp_redirect(admin_url('admin.php?page=cuny-gallery&view=edit&id=' . $gal_id));
        exit;
    }

    // Update gallery style or name
    if (isset($_POST['update_gallery'], $_POST['gallery_id'])) {
        $gal_id = absint($_POST['gallery_id']);
        $new_style = sanitize_text_field($_POST['new_style']);
        $new_name = sanitize_text_field($_POST['new_name']);
        $wpdb->update($gallery_table, [
            'gallery_style' => $new_style,
            'gallery_name' => $new_name,
        ], ['id' => $gal_id]);
        wp_redirect(admin_url('admin.php?page=cuny-gallery&view=edit&id=' . $gal_id));
        exit;
    }

    // Show/Hide gallery
    if (isset($_GET['toggle_gallery'])) {
        $id = absint($_GET['toggle_gallery']);
        $current = $wpdb->get_var($wpdb->prepare("SELECT visible FROM $gallery_table WHERE id = %d", $id));
        $wpdb->update($gallery_table, ['visible' => !$current], ['id' => $id]);
        wp_redirect(admin_url('admin.php?page=cuny-gallery'));
        exit;
    }

    // Update image alt text
    if (isset($_POST['update_image_alt_text'], $_POST['image_id'])) {
        $image_id = absint($_POST['image_id']);
        $new_alt_text = sanitize_text_field($_POST['new_alt_text']);
        $wpdb->update($image_table, ['image_alt_text' => $new_alt_text], ['id' => $image_id]);
        wp_redirect(admin_url('admin.php?page=cuny-gallery&view=edit&id=' . absint($_POST['gallery_id'])));
        exit;
    }

    // Show/Hide image
    if (isset($_GET['toggle_image'], $_GET['id'])) {
        $id = absint($_GET['toggle_image']);
        $current = $wpdb->get_var($wpdb->prepare("SELECT visible FROM $image_table WHERE id = %d", $id));
        $wpdb->update($image_table, ['visible' => !$current], ['id' => $id]);
        wp_redirect(admin_url('admin.php?page=cuny-gallery&view=edit&id=' . absint($_GET['id'])));
        exit;
    }

    // Delete gallery and its images
    if (isset($_GET['delete_gallery'])) {
        $id = absint($_GET['delete_gallery']);
        $wpdb->delete($image_table, ['gallery_id' => $id]);
        $wpdb->delete($gallery_table, ['id' => $id]);
        wp_redirect(admin_url('admin.php?page=cuny-gallery'));
        exit;
    }

    // Permanently delete image
    if (isset($_GET['delete_image'], $_GET['id'])) {
        $wpdb->delete($image_table, ['id' => absint($_GET['delete_image'])]);
        wp_redirect(admin_url('admin.php?page=cuny-gallery&view=edit&id=' . absint($_GET['id'])));
        exit;
    }

    // Reorder images
    add_action('wp_ajax_cuny_gallery_reorder', function () {
        check_ajax_referer('cuny_gallery_reorder', 'nonce');
        if (!current_user_can('manage_options') || !isset($_POST['order']) || !is_array($_POST['order'])) {
            wp_send_json_error();
        }
    
        global $wpdb;
        $image_table = $wpdb->prefix . 'cuny_slider_images';
    
        foreach ($_POST['order'] as $index => $id) {
            $wpdb->update($image_table, ['sort_order' => $index], ['id' => intval($id)]);
        }
    
        wp_send_json_success();
    });
});
// Load the handler function for the shortcode
if (!is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'frontend/frontend.php';
}

// Attach the filter
add_filter('the_content', 'process_cuny_gallery', 5);
if (!function_exists('process_cuny_gallery')) {
    function process_cuny_gallery($content) {
        // Convert [cuny_gallery_1] → [cuny_gallery id="1"]
        $converted = preg_replace_callback(
            '/\[cuny_gallery_(\d+)\]/i',
            function($matches) {
                return '[cuny_gallery id="' . intval($matches[1]) . '"]';
            },
            $content
        );
    
        // Run do_shortcode to actually process the new shortcodes
        return do_shortcode($converted);
    }        
}

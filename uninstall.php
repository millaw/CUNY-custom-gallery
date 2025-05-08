<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;

global $wpdb;

$gallery_table = $wpdb->prefix . 'cuny_slider_galleries';
$image_table = $wpdb->prefix . 'cuny_slider_images';

$wpdb->query("DROP TABLE IF EXISTS $gallery_table");
$wpdb->query("DROP TABLE IF EXISTS $image_table");

<?php
if (!function_exists('cuny_gallery_handler')) {
    function cuny_gallery_handler($atts) {
        global $wpdb;
        $gallery_table = $wpdb->prefix . 'cuny_slider_galleries';
        $image_table = $wpdb->prefix . 'cuny_slider_images';

        // Shortcode attributes
        $atts = shortcode_atts(['id' => '0'], $atts);
        $gallery_id = absint($atts['id']);
        if (!$gallery_id) return '';

        if (!is_numeric($gallery_id)) return '';
        

    $gallery = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $gallery_table WHERE id = %d AND visible = 1", $gallery_id
    ));
    if (!$gallery) return '';

    $images = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $image_table WHERE gallery_id = %d AND visible = 1 ORDER BY sort_order ASC, id ASC", $gallery_id
    ));
    if (empty($images)) return '';

    $images_array = array_map(function($img) {
        return [
            'href' => wp_get_attachment_url($img->image_id),
            'src' => wp_get_attachment_image_url($img->image_id, 'medium'),
            'alt' => $img->image_alt_text
        ];
    }, $images);
    $initial_chunk = array_slice($images_array, 0, 0);
    $total_images = count($images_array);
    ob_start();
?>
<div class="cuny-gallery-wrapper">
<?php if ($gallery->gallery_style === 'gallery'): ?>
    <div class="gallery" id="gallery">
        <?php foreach ($initial_chunk as $index => $img): ?>
            <article class="gallery-item" data-index="<?php echo $index; ?>">
                <a href="<?php echo esc_url($img['href']); ?>" aria-label="View full-size image: <?php echo esc_attr($img['alt']); ?>">
                    <img src="<?php echo esc_url($img['src']); ?>" alt="<?php echo esc_attr($img['alt']); ?>" loading="lazy">
                </a>
            </article>
        <?php endforeach; ?>
    </div>
    <?php if ($total_images > 8): ?>
        <button id="loadMoreBtn" class="load-more-btn cuny-cta-button">Load More</button>
    <?php endif; ?>
<?php elseif ($gallery->gallery_style === 'slider'): ?>
    <div class="slider-container" id="sliderContainer">
        <button class="slider-btn left" id="prevBtn"><span aria-label="Previous image">‹</span></button>
        <div class="slider-track" id="sliderTrack">
            <?php foreach ($images_array as $index => $img): ?>
                <div class="slide" data-index="<?php echo $index; ?>">
                    <img src="<?php echo esc_url($img['href']); ?>" alt="<?php echo esc_attr($img['alt']); ?>">
                </div>
            <?php endforeach; ?>
        </div>
        <button class="slider-btn right" id="nextBtn"><span aria-label="Next image">›</span></button>
    </div>
<?php elseif ($gallery->gallery_style === 'slider-gallery'): ?>
    <div class="slider-gallery-container" id="sliderGalleryContainer">
        <button class="slider-gallery-btn left" id="prevGalleryBtn" aria-label="Previous slide"><span>‹</span></button>
        <div class="slider-gallery-track" id="sliderGalleryTrack">
            <?php
            $imagesPerSlide = 6;
            $chunks = array_chunk($images_array, $imagesPerSlide);
            foreach ($chunks as $slideIndex => $group): ?>
                <div class="slider-gallery-slide">
                    <?php foreach ($group as $i => $img): ?>
                        <div class="slider-gallery-item" data-index="<?php echo ($slideIndex * $imagesPerSlide) + $i; ?>">
                            <img src="<?php echo esc_url($img['src']); ?>" alt="<?php echo esc_attr($img['alt']); ?>">
                            <div class="slider-gallery-overlay"><span><?php echo esc_html($img['alt']); ?></span></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="slider-gallery-btn right" id="nextGalleryBtn" aria-label="Next slide"><span>›</span></button>
    </div>
<?php endif; ?>
    <!-- MODAL -->
    <div id="modal" class="modal" aria-hidden="true">
        <div class="modal-content" role="dialog" aria-modal="true" aria-label="Image viewer">
            <button class="modal-close" id="modalClose" aria-label="Close modal"><span aria-label="Close modal">&times;</span></button>
            <img src="" alt="">
            <button class="modal-nav prev" id="modalPrev" aria-label="Previous image"><span>‹</span></button>
            <button class="modal-nav next" id="modalNext" aria-label="Next image"><span>›</span></button>
        </div>
    </div>
</div>
<script>
    const images = <?php echo json_encode($images_array, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP); ?>;
</script>
<?php
        return ob_get_clean();
    }
}
add_shortcode('cuny_gallery', 'cuny_gallery_handler');
?>

jQuery(function ($) {
    $('.sortable-images').sortable({
        update: function () {
            const order = $(this).sortable('toArray', { attribute: 'data-id' });
            $.post(cunyGalleryAjax.ajax_url, {
                action: 'cuny_gallery_reorder',
                order: order,
                nonce: cunyGalleryAjax.nonce
            });
        }
    });
});

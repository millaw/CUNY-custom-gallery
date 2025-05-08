jQuery(document).ready(function($) {
    console.log('Admin JS loaded');

    const addBtn = $('input[name="add_image_to_gallery"]');
    const hiddenInput = $('#new_image_id');

    addBtn.prop('disabled', true); // Disable submit button initially

    $('#add-image').on('click', function(e) {
        e.preventDefault();

        const frame = wp.media({
            title: 'Select or Upload Image',
            button: { text: 'Use this image' },
            multiple: false
        });

        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            $('#new_image_id').val(attachment.id);
            $('#new_image_preview').html('<img src="' + (attachment.sizes.thumbnail?.url || attachment.url) + '" style="max-width:100px;">');

            addBtn.prop('disabled', false); // Enable submit
        });

        frame.open();
    });

    // === Image Options Modal ===
    $('.menu-button').on('click', function () {
        const item = $(this).closest('.gallery-item');
        const imageId = item.data('id');
        const visible = item.data('visible');
        const alt = item.data('alt');
        const galleryId = CUNYGalleryData.gallery_id;

        $('#modalImageId').val(imageId);
        $('#modalAltText').val(alt);
        $('#toggleVisibilityBtn')
            .attr('href', `?page=cuny-gallery&view=edit&id=${galleryId}&toggle_image=${imageId}`)
            .text(visible ? 'Hide' : 'Show');
        $('#deleteImageBtn')
            .attr('href', `?page=cuny-gallery&view=edit&id=${galleryId}&delete_image=${imageId}`);
        $('#imageOptionsModal').fadeIn();
    });

    $('#closeModalBtn').on('click', function () {
        $('#imageOptionsModal').fadeOut();
    });

    // Enable submit if image selected
    hiddenInput.on('input', function () {
        addBtn.prop('disabled', $(this).val().trim() === '');
    });
});

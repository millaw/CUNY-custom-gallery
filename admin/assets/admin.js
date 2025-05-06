jQuery(document).ready(function($) {
    $('#add-image').on('click', function(e) {
        e.preventDefault();
        var frame = wp.media({
            title: 'Select or Upload Image',
            button: { text: 'Use this image' },
            multiple: false
        });
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#new_image_id').val(attachment.id);
            $('#new_image_preview').html('<img src="' + attachment.sizes.thumbnail.url + '" style="max-width:100px;">');
        });
        frame.open();
    });
});

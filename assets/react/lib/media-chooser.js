window.jQuery(document).ready(function ($) {

    const { __ } = window.wp.i18n;

    /**
     * upload thumbnail
     * @since v.1.5.6
     */

    let enterClicked = false;
    document.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            enterClicked = true;
        }
    });


    if (enterClicked !== false) {
        enterClicked = false;
        return false;
    }


    $(document).on('click', '.tutor-thumbnail-uploader .tutor-thumbnail-upload-button', function (event) {
        event.preventDefault();

        var wrapper = $(this).closest('.tutor-thumbnail-uploader');
        var frame;
        if (frame) {
            frame.open();
            return;
        }
        frame = wp.media({
            title: wrapper.data('media-heading'),
            button: {
                text: wrapper.data('button-text')
            },
            library: { type: "image" },
            multiple: false, // Set to true to allow multiple files to be selected
        });
        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();

            wrapper.find('img').attr('src', attachment.url);
            wrapper.find('input[type="hidden"].tutor-tumbnail-id-input').val(attachment.id);
            wrapper.find('.delete-btn').show();

            $('#save_tutor_option').prop('disabled', false);
        });
        frame.open();
    });

    /**
     * Thumbnail Delete
     * @since 1.5.6
     */
    $(document).on('click', '.tutor-thumbnail-uploader .delete-btn', function (e) {
        e.preventDefault();

        let $that = $(this),
            wrapper = $that.closest('.tutor-thumbnail-uploader'),
            img = wrapper.find('img'),
            placeholder = img.data('placeholder') || '';

        wrapper.find('input[type="hidden"].tutor-tumbnail-id-input').val('');
        img.attr('src', placeholder);
        $that.hide();

        // Enable save button after thumbnail remove.
        $('#save_tutor_option').prop('disabled', false);
    });
});
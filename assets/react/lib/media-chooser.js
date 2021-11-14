window.jQuery(document).ready(function ($) {

    const { __ } = window.wp.i18n;

    /**
     * upload thumbnail
     * /
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
        });
        frame.open();
    });
*/

    const uploaderButton = document.querySelector('.tutor-thumbnail-uploader .tutor-thumbnail-upload-button');
    uploaderButton.onclick = (event) => {
        event.preventDefault();
        var wrapper = uploaderButton.closest('.tutor-thumbnail-uploader');
        var frame;
        if (frame) {
            frame.open();
            return;
        }


        frame = wp.media({
            title: wrapper.dataset.mediaHeading,
            button: {
                text: wrapper.dataset.buttonText
            },
            library: { type: "image" },
            multiple: false, // Set to true to allow multiple files to be selected
        });
        console.log(wrapper);

        frame.on('select', () => {
            var attachment = frame.state().get('selection').first().toJSON();
            wrapper.querySelector('img').setAttribute('src', attachment.url);
            wrapper.querySelector('input[type="hidden"].tutor-tumbnail-id-input').value = attachment.id;
            wrapper.querySelector('.delete-btn').style.display = "block";
            document.getElementById('save_tutor_option').disabled = false;
        })

        frame.open();

    }
    /**
     * Thumbnail Delete
     * @since v.1.5.6
     */
    $(document).on('click', '.tutor-thumbnail-uploader .delete-btn', function (e) {
        e.preventDefault();

        var $that = $(this);
        var wrapper = $that.closest('.tutor-thumbnail-uploader');

        wrapper.find('input[type="hidden"].tutor-tumbnail-id-input').val('');
        wrapper.find('img').attr('src', '');

        $that.hide();
    });
});
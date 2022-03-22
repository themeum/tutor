window.jQuery(document).ready($=>{

    const {__} = wp.i18n;

    // On Remove video
    $(document).on('click', '.video_source_wrap_html5 .tutor-attachment-cards .tutor-delete-attachment', function() {
        $(this)
            .closest('.video_source_wrap_html5')
            .removeClass('tutor-has-video')
            .find('input.input_source_video_id').val('');
    });

    // Upload video
    $(document).on("click", ".video_source_wrap_html5 .video_upload_btn", function(event) {
        event.preventDefault();

        var container = $(this).closest('.video_source_wrap_html5');
        var attachment_card = container.find('.tutor-attachment-cards');
        var frame;

        // If the media frame already exists, reopen it.
        if (frame) {
            frame.open();
            return;
        }

        // Create a new media frame
        frame = wp.media({
            title: __("Select or Upload Media Of Your Choice", "tutor"),
            button: {
                text: __("Upload media", "tutor"),
            },
            library: { type: "video" },
            multiple: false, // Set to true to allow multiple files to be selected
        });

        // When an image is selected in the media frame...
        frame.on("select", function() {
            // Get media attachment details from the frame state
            var attachment = frame.state().get("selection").first().toJSON();

            // Show video info
            attachment_card.find(".filename").text(attachment.name).attr('href', attachment.url);
            attachment_card.find('.filesize').text(attachment.filesizeHumanReadable);
            
            // Add video id to hidden input
            container
                .find("input.input_source_video_id")
                .val(attachment.id)
                .data('video_url', attachment.url)
                .trigger('paste');

            // Add identifer that video added
            container.addClass('tutor-has-video');
        });

        // Finally, open the modal on click
        frame.open();
    });
});
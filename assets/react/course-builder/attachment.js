
window.jQuery(document).ready(function($){
    
    const { __, _x, _n, _nx } = wp.i18n;
    
    $(document).on('click', '.tutor-attachment-cards .tutor-delete-attachment', function(e){
        e.preventDefault();
        $(this).closest('[data-attachment_id]').remove();
    });

    $(document).on('click', '.tutorUploadAttachmentBtn', function(e){
        e.preventDefault();

        var $that = $(this);
        var frame;
        // If the media frame already exists, reopen it.
        if ( frame ) {
            frame.open();
            return;
        }
        // Create a new media frame
        frame = wp.media({
            title: __( 'Select or Upload Media Of Your Choice', 'tutor' ),
            button: {
                text: __( 'Upload media', 'tutor' )
            },
            multiple: true  // Set to true to allow multiple files to be selected
        });
        // When an image is selected in the media frame...
        frame.on( 'select', function() {
            // Get media attachment details from the frame state
            var attachments = frame.state().get('selection').toJSON();
            if (attachments.length){
                for (var i=0; i < attachments.length; i++){
                    var attachment = attachments[i];

                    var inputHtml = `<div data-attachment_id="${attachment.id}">
                        <div>
                            <a href="${attachment.url}" target="_blank">
                                ${attachment.filename}
                            </a>
                            <input type="hidden" name="tutor_attachments[]" value="${attachment.id}">
                        </div>
                        <div>
                            <span class="filesize">${__('Size', 'tutor')}: ${attachment.filesizeHumanReadable}</span>
                            <span class="tutor-delete-attachment tutor-icon-line-cross"></span>
                        </div>
                    </div>`;
                    
                    $that.closest('.tutor-attachments-metabox').find('.tutor-attachment-cards').append(inputHtml);
                }
            }
        });
        // Finally, open the modal on click
        frame.open();
    });
});
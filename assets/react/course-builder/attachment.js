
window.jQuery(document).ready(function($){
    
    const { __, _x, _n, _nx } = wp.i18n;
    
    $(document).on('click', '.tutor-attachment-cards:not(.tutor-no-control) .tutor-delete-attachment', function(e){
        e.preventDefault();
        $(this).closest('[data-attachment_id]').remove();
    });

    $(document).on('click', '.tutorUploadAttachmentBtn', function(e){
        e.preventDefault();

        var $that = $(this);
        var name = $that.data('name');
        var card_wrapper = $that.parent().find('.tutor-attachment-cards');
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
                        
                    var inputHtml = `<div class="tutor-card tutor-d-flex tutor-align-items-center tutor-px-20 tutor-py-16 tutor-mb-16" data-attachment_id="${attachment.id}">
                        <div class="tutor-w-100 tutor-pr-24">
                            <div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mb-4">${attachment.filename}</div>
                            <div class="tutor-fs-7 tutor-color-muted">${__('Size', 'tutor')}: ${attachment.filesizeHumanReadable}</div>
                            <input type="hidden" name="${name}" value="${attachment.id}">
                        </div>
                        <div class="tutor-ml-auto">
                            <span class="tutor-delete-attachment tutor-iconic-btn tutor-iconic-btn-secondary tutor-iconic-btn-lg" role="button">
                                <span class="tutor-icon-times" area-hidden="true"></span>
                            </span>
                        </div>
                    </div>`;
                    
                    $that.parent().find('.tutor-attachment-cards').append(inputHtml);
                }
            }
        });
        // Finally, open the modal on click
        frame.open();
    });
});
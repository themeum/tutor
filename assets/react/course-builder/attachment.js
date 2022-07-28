
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
                        
                    var inputHtml = `<div class="tutor-col-lg-6 tutor-col-xl-4 tutor-mb-16" data-attachment_id="${attachment.id}">
                        <div class="tutor-card">
                            <div class="tutor-card-body">
                                <div class="tutor-row tutor-align-center">
                                    <div class="tutor-col tutor-overflow-hidden">
                                        <div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-text-ellipsis tutor-mb-4">${attachment.filename}</div>
                                        <div class="tutor-fs-7 tutor-color-muted">${__('Size', 'tutor')}: ${attachment.filesizeHumanReadable}</div>
                                        <input type="hidden" name="${name}" value="${attachment.id}">
                                    </div>

                                    <div class="tutor-col-auto">
                                        <span class="tutor-delete-attachment tutor-iconic-btn tutor-iconic-btn-secondary" role="button">
                                            <span class="tutor-icon-times" area-hidden="true"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
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
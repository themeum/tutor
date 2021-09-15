import './topic';
import './lesson';
import './assignment';

window.jQuery(document).ready(function($){
    
    const { __, _x, _n, _nx } = wp.i18n;
    
    $(document).on('click', 'a.tutor-delete-attachment', function(e){
        e.preventDefault();
        $(this).closest('.tutor-added-attachment').remove();
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

                    var inputHtml = '<div class="tutor-added-attachment"><i class="tutor-icon-archive"></i> <a href="javascript:;" class="tutor-delete-attachment tutor-icon-line-cross"></a> <span> <a href="'+attachment.url+'">'+attachment.filename+'</a> </span><input type="hidden" name="tutor_attachments[]" value="'+attachment.id+'"></div>';
                    $that.closest('.tutor-lesson-attachments-metabox').find('.tutor-added-attachments-wrap').append(inputHtml);
                }
            }
        });
        // Finally, open the modal on click
        frame.open();
    });
});
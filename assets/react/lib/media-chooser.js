window.jQuery(document).ready(function($){

    const {__} = window.wp.i18n;

    /**
     * Lesson upload thumbnail
     */
     $(document).on( 'click', '.tutor-thumbnail-uploader .tutor-thumbnail-upload-button',  function( event ){
        event.preventDefault();

        var wrapper = $(this).closest('.tutor-thumbnail-uploader');
        var frame;
        if ( frame ) {
            frame.open();
            return;
        }
        frame = wp.media({
            title: wrapper.data('media-heading'),
            button: {
                text: wrapper.data('button-text')
            },
            multiple: false
        });
        frame.on( 'select', function() {
            var attachment = frame.state().get('selection').first().toJSON();

            wrapper.find('img').attr('src', attachment.url);
            wrapper.find('input[type="hidden"].tutor-tumbnail-id-input').val(attachment.id);
            wrapper.find('.delete-btn').show();
        });
        frame.open();
    });

    /**
     * Lesson Feature Image Delete
     * @since v.1.5.6
     */
     $(document).on('click', '.tutor-thumbnail-uploader .delete-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var wrapper = $that.closest('.tutor-thumbnail-uploader');

        wrapper.find('input[type="hidden"].tutor-tumbnail-id-input').val('');
        wrapper.find('img').attr('src', '');
        
        $that.hide();
    });

})
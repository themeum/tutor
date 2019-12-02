jQuery(document).ready(function($){
    'use strict';

    /**
     * Video source tabs
     */

    if (jQuery().select2){
        $('.videosource_select2').select2({
            width: "100%",
            templateSelection: iformat,
            templateResult: iformat,
            allowHtml: true
        });
    }
    //videosource_select2

    function iformat(icon) {
        var originalOption = icon.element;
        return $('<span><i class="tutor-icon-' + $(originalOption).data('icon') + '"></i> ' + icon.text + '</span>');
    }

    $(document).on('change', '.tutor_lesson_video_source', function(e){
        var $that = $(this);
        var selector = $(this).val();

        if (selector){
            $('.video-metabox-source-input-wrap').show();
        }else{
            $('.video-metabox-source-input-wrap').hide();
        }
        $that.closest('.tutor-option-field').find('.video-metabox-source-item').hide();
        $that.closest('.tutor-option-field').find('.video_source_wrap_'+selector).show();
    });

    /**
     * Course Builder
     *
     * @since v.1.3.4
     */

    $(document).on( 'click', '.tutor-course-thumbnail-upload-btn',  function( event ){
        event.preventDefault();
        var $that = $(this);
        var frame;
        if ( frame ) {
            frame.open();
            return;
        }
        frame = wp.media({
            title: 'Select or Upload Media Of Your Chosen Persuasion',
            button: {
                text: 'Use this media'
            },
            multiple: false
        });
        frame.on( 'select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $that.closest('.tutor-thumbnail-wrap').find('.thumbnail-img').attr('src', attachment.url);
            $that.closest('.tutor-thumbnail-wrap').find('input').val(attachment.id);
            $('.tutor-course-thumbnail-delete-btn').show();
        });
        frame.open();
    });

    //Delete Thumbnail
    $(document).on( 'click', '.tutor-course-thumbnail-delete-btn',  function( event ){
        event.preventDefault();
        var $that = $(this);

        var placeholder_src = $that.closest('.tutor-thumbnail-wrap').find('.thumbnail-img').attr('data-placeholder-src');
        $that.closest('.tutor-thumbnail-wrap').find('.thumbnail-img').attr('src', placeholder_src);
        $that.closest('.tutor-thumbnail-wrap').find('input').val('');
        $('.tutor-course-thumbnail-delete-btn').hide();
    });



});
(function($){
    window.enable_sorting_topic_lesson=function(){
        if (jQuery().sortable) {
            $(".course-contents").sortable({
                handle: ".course-move-handle",
                start: function (e, ui) {
                    ui.placeholder.css('visibility', 'visible');
                },
                stop: function (e, ui) {
                    tutor_sorting_topics_and_lesson();
                },
            });
            $(".tutor-lessons:not(.drop-lessons)").sortable({
                connectWith: ".tutor-lessons",
                items: "div.course-content-item",
                start: function (e, ui) {
                    ui.placeholder.css('visibility', 'visible');
                },
                stop: function (e, ui) {
                    tutor_sorting_topics_and_lesson();
                },
            });
        }
    }


    window.tutor_sorting_topics_and_lesson=function(){
        var topics = {};
        $('.tutor-topics-wrap').each(function(index, item){
            var $topic = $(this);
            var topics_id = parseInt($topic.attr('id').match(/\d+/)[0], 10);
            var lessons = {};

            $topic.find('.course-content-item').each(function(lessonIndex, lessonItem){
                var $lesson = $(this);
                var lesson_id = parseInt($lesson.attr('id').match(/\d+/)[0], 10);

                lessons[lessonIndex] = lesson_id;
            });
            topics[index] = { 'topic_id' : topics_id, 'lesson_ids' : lessons };
        });
        $('#tutor_topics_lessons_sorting').val(JSON.stringify(topics));
    }

})(window.jQuery);


window.jQuery(document).ready(function($){
    
    const { __, _x, _n, _nx } = wp.i18n;
    
    enable_sorting_topic_lesson();
    
    /**
     * Open Lesson Modal
     */
     $(document).on('click', '.open-tutor-lesson-modal', function(e){
        e.preventDefault();

        var $that = $(this);
        var lesson_id = $that.attr('data-lesson-id');
        var topic_id = $that.attr('data-topic-id');
        var course_id = $('#post_ID').val();

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {lesson_id : lesson_id, topic_id : topic_id, course_id : course_id, action: 'tutor_load_edit_lesson_modal'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('.tutor-lesson-modal-wrap .modal-container').html(data.data.output);
                $('.tutor-lesson-modal-wrap').attr({'data-lesson-id' : lesson_id, 'data-topic-id':topic_id}).addClass('show');
                $('.tutor-lesson-modal-wrap').addClass('tutor-is-active');

                var tinymceConfig = tinyMCEPreInit.mceInit.tutor_editor_config;
                if ( ! tinymceConfig){
                    tinymceConfig = tinyMCEPreInit.mceInit.course_description;
                }
                tinymce.init(tinymceConfig);
                tinymce.execCommand( 'mceRemoveEditor', false, 'tutor_lesson_modal_editor' );
                tinyMCE.execCommand('mceAddEditor', false, "tutor_lesson_modal_editor");

                $(document).trigger('lesson_modal_loaded', {lesson_id : lesson_id, topic_id : topic_id, course_id : course_id});
            },
            complete: function () {
                quicktags({id : "tutor_lesson_modal_editor"});
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Lesson upload thumbnail
     */
     $(document).on( 'click', '.lesson_thumbnail_upload_btn',  function( event ){
        event.preventDefault();
        var $that = $(this);
        var frame;
        if ( frame ) {
            frame.open();
            return;
        }
        frame = wp.media({
            title: __( 'Select or Upload Media Of Your Chosen Persuasion', 'tutor' ),
            button: {
                text: __( 'Use this media', 'tutor' )
            },
            multiple: false
        });
        frame.on( 'select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $that.closest('.tutor-thumbnail-wrap').find('img.upload_preview').attr('src', attachment.url);
            $that.closest('.tutor-thumbnail-wrap').find('input[name="_lesson_thumbnail_id"]').val(attachment.id);
            $that.closest('.tutor-thumbnail-wrap').find('.delete-btn').show();
        });
        frame.open();
    });

    // Update lesson
    $(document).on( 'click', '.update_lesson_modal_btn',  function( event ){
        event.preventDefault();

        var $that = $(this);
        var content;
        var inputid = 'tutor_lesson_modal_editor';
        var editor = tinyMCE.get(inputid);
        if (editor) {
            content = editor.getContent();
        } else {
            content = $('#'+inputid).val();
        }

        var form_data = $(this).closest('.tutor-modal').find('form').serializeObject();
        form_data.lesson_content = content;

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : form_data,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    $('#tutor-course-content-wrap').html(data.data.course_contents);
                    enable_sorting_topic_lesson();

                    //Close the modal
                    $that.closest('.tutor-modal').removeClass('tutor-is-active');
                    
                    tutor_toast(__('Success', 'tutor'), __('Lesson Updated', 'tutor'), 'success');
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });
});
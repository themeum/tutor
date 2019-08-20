jQuery(document).ready(function($){
    'use strict';

    /**
     * Slider bar
     */
    function tutor_slider_init(){
        $('.tutor-field-slider').each(function(){
            var $slider = $(this);
            var $input = $slider.closest('.tutor-field-type-slider').find('input[type="hidden"]');
            var $showVal = $slider.closest('.tutor-field-type-slider').find('.tutor-field-type-slider-value');
            var min = parseFloat($slider.closest('.tutor-field-type-slider').attr('data-min'));
            var max = parseFloat($slider.closest('.tutor-field-type-slider').attr('data-max'));

            $slider.slider({
                range: "max",
                min: min,
                max: max,
                value: $input.val(),
                slide: function( event, ui ) {
                    $showVal.text(ui.value);
                    $input.val(ui.value);
                }
            });
        });
    }
    tutor_slider_init();


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

    /**
     * Quiz Builder
     */

    /**
     * Ope modal for edit quiz
     */
    $(document).on('click', '.open-tutor-quiz-modal', function(e){
        e.preventDefault();

        var $that = $(this);
        var quiz_id = $that.attr('data-quiz-id');
        var topic_id = $that.attr('data-topic-id');
        var course_id = $('#post_ID').val();

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {quiz_id : quiz_id, topic_id : topic_id, course_id : course_id, action: 'tutor_load_edit_quiz_modal'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('.tutor-quiz-builder-modal-wrap .modal-container').html(data.data.output);
                $('.tutor-quiz-builder-modal-wrap').attr('data-quiz-id', quiz_id).addClass('show');

                //Back to question Tab if exists
                if ($that.attr('data-back-to-tab')){
                    var tabSelector = $that.attr('data-back-to-tab');
                    $('#tutor-quiz-modal-tab-items-wrap a[href="'+tabSelector+'"]').trigger('click');
                }
                tutor_slider_init();
                enable_quiz_questions_sorting();
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    $(document).on('click', '.quiz-modal-settings-save-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var quiz_id = $('.tutor-quiz-builder-modal-wrap').attr('data-quiz-id');

        var $formInput = $('#quiz-builder-tab-settings :input, #quiz-builder-tab-advanced-options :input').serialize()+'&quiz_id='+quiz_id+'&action=tutor_quiz_modal_update_settings';

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : $formInput,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                //
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
                if ($that.attr('data-action') === 'modal_close'){
                    $('.tutor-modal-wrap').removeClass('show');
                }
            }
        });
    });


    /**
     * Quiz Question edit save and continue
     */
    $(document).on('click', '.quiz-modal-question-save-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var $formInput = $('.quiz_question_form :input').serialize()+'&action=tutor_quiz_modal_update_question';
        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : $formInput,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                //ReOpen questions
                $that.closest('.tutor-quiz-builder-modal-contents').find('.open-tutor-quiz-modal').trigger('click');
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Sort quiz questions
     */
    function enable_quiz_questions_sorting(){
        if (jQuery().sortable) {
            $(".quiz-builder-questions-wrap").sortable({
                handle: ".question-sorting",
                start: function (e, ui) {
                    ui.placeholder.css('visibility', 'visible');
                },
                stop: function (e, ui) {
                    tutor_save_sorting_quiz_questions_order();
                },
            });
        }
    }

    function tutor_save_sorting_quiz_questions_order(){
        var questions = {};
        $('.quiz-builder-question-wrap').each(function(index, item){
            var $question = $(this);
            var question_id = parseInt($question.attr('data-question-id'), 10);
            questions[index] = question_id;
        });

        $.ajax({url : ajaxurl, type : 'POST',
            data : {sorted_question_ids : questions, action: 'tutor_quiz_question_sorting'},
        });
    }

});
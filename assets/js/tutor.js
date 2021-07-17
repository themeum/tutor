function tutor_get_nonce_data(send_key_value) {

    var nonce_data = window._tutorobject || {};
    var nonce_key = nonce_data.nonce_key || '';
    var nonce_value = nonce_data[nonce_key] || '';

    if(send_key_value) {
        return {key:nonce_key, value:nonce_value};
    }

    return {[nonce_key]:nonce_value};
}

window.tutor_component = function($, icon, padding) {

    var $this = this;
    var element; 

    this.popup_wrapper = function(contents) {
        return '<div class="tutor-component-popup-container">\
            <div class="tutor-component-popup-'+padding+'">\
                <img class="tutor-pop-icon" src="'+window._tutorobject.tutor_url+'assets/images/'+icon+'.svg"/>\
                ' + contents + '\
                <div class="tutor-component-button-container"></div>\
            </div>\
        </div>';
    }

    this.popup = function(data) {
        
        var title = data.title ? '<h3>'+data.title+'</h3>' : '';
        var description = data.description ? '<p>'+data.description+'</p>' : '';

        var buttons = Object.keys(data.buttons || {}).map(function(key) {
            var button = data.buttons[key];

            return $('<button class="tutor-button tutor-button-'+button.class+'">'+button.title+'</button>').click(button.callback);
        });

        element = $($this.popup_wrapper( title + description ), padding);

        // Assign close event
        element.click(function() {
            $(this).remove();
        }).children().click(function(e) {
            e.stopPropagation();
        });

        // Append action button
        for(var i=0; i<buttons.length; i++) {
            element.find('.tutor-component-button-container').append(buttons[i]);
        }
        
        $('body').append(element);

        return element;
    }

    return {popup: this.popup};
}

jQuery(document).ready(function($){
    'use strict';

    const { __, _x, _n, _nx } = wp.i18n;

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

    $(document).on('click', '.create_new_topic_btn', function (e) {
        e.preventDefault();
        $('.tutor-metabox-add-topics').slideToggle();
    });

    $(document).on('click', '#tutor-add-topic-btn', function (e) {
        e.preventDefault();
        var $that = $(this);
        var form_data = $that.closest('.tutor-metabox-add-topics').find('input, textarea').serializeObject();
        form_data.action = 'tutor_add_course_topic';

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
                    $that.closest('.tutor-metabox-add-topics').find('input[type!="hidden"], textarea').each(function () {
                        $(this).val('');
                    });
                    $that.closest('.tutor-metabox-add-topics').slideUp();
                    enable_sorting_topic_lesson();
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Zoom Meeting js
     * here for support enable_sorting_topic_lesson function
     * @since 1.7.1
     */
    $('.tutor-zoom-meeting-modal-wrap').on('submit', '.tutor-meeting-modal-form', function (e) {
        e.preventDefault();
        var $form = $(this);
        var data = $form.serializeObject();
        var $btn = $form.find('button[type="submit"]');
        
        $.ajax({
            url: window._tutorobject.ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $btn.addClass('tutor-updating-message');
            },
            success: function (data) {

                data.success ?
                    tutor_toast(__('Success', 'tutor'), $btn.data('toast_success_message'), 'success'):
                    tutor_toast(__('Update Error', 'tutor'), __('Meeting Update Failed', 'tutor'), 'error');

                if(data.course_contents) {
                    $(data.selector).html(data.course_contents);
                    if ( data.selector == '#tutor-course-content-wrap') {
                        enable_sorting_topic_lesson();
                    }
                    //Close the modal
                    $('.tutor-zoom-meeting-modal-wrap').removeClass('show');
                } else {
                    location.reload();
                }
            },
            complete: function () {
                $btn.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Resorting...
     */
    function enable_sorting_topic_lesson(){
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

    function tutor_sorting_topics_and_lesson(){
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

    $(document).on('change keyup', '.course-edit-topic-title-input', function (e) {
        e.preventDefault();
        $(this).closest('.tutor-topics-top').find('.topic-inner-title').html($(this).val());
    });

    $(document).on('click', '.topic-edit-icon', function (e) {
        e.preventDefault();
        $(this).closest('.tutor-topics-top').find('.tutor-topics-edit-form').slideToggle();
    });

    $(document).on('click', '.tutor-topics-edit-button', function(e){
        e.preventDefault();
        var $button = $(this);
        var $topic = $button.closest('.tutor-topics-wrap');
        var topics_id = parseInt($topic.attr('id').match(/\d+/)[0], 10);
        var topic_title = $button.closest('.tutor-topics-wrap').find('[name="topic_title"]').val();
        var topic_summery = $button.closest('.tutor-topics-wrap').find('[name="topic_summery"]').val();

        var data = {topic_title: topic_title, topic_summery : topic_summery, topic_id : topics_id, action: 'tutor_update_topic'};
        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : data,
            beforeSend: function () {
                $button.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    $button.closest('.tutor-topics-wrap').find('span.topic-inner-title').text(topic_title);
                    $button.closest('.tutor-topics-wrap').find('.tutor-topics-edit-form').slideUp();
                }
            },
            complete: function () {
                $button.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Update Lesson Modal
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
            title: 'Select or Upload Media Of Your Chosen Persuasion',
            button: {
                text: 'Use this media'
            },
            multiple: false
        });
        frame.on( 'select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $that.closest('.tutor-thumbnail-wrap').find('.thumbnail-img').html('<img src="'+attachment.url+'" alt="" /><a href="javascript:;" class="tutor-lesson-thumbnail-delete-btn"><i class="tutor-icon-line-cross"></i></a>');
            $that.closest('.tutor-thumbnail-wrap').find('input').val(attachment.id);
            $('.tutor-lesson-thumbnail-delete-btn').show();
        });
        frame.open();
    });

    /**
     * Lesson Feature Image Delete
     * @since v.1.5.6
     */
    $(document).on('click', '.tutor-lesson-thumbnail-delete-btn', function(e){
        e.preventDefault();

        var $that = $(this);

        $that.closest('.tutor-thumbnail-wrap').find('._lesson_thumbnail_id').val('');
        $that.closest('.tutor-thumbnail-wrap').find('.thumbnail-img').html('');
        $that.hide();

    });

    /**
     * Delete Lesson from course builder
     */
    $(document).on('click', '.tutor-delete-lesson-btn', function(e){
        e.preventDefault();

        if( ! confirm('Are you sure?')){
            return;
        }

        var $that = $(this);
        var lesson_id = $that.attr('data-lesson-id');

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {lesson_id : lesson_id, action: 'tutor_delete_lesson_by_id'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    $that.closest('.course-content-item').remove();
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Confirmation for deleting Topic
     */
    $(document).on('click', '.topic-delete-btn a', function(e){
        var topic_id = $(this).attr('data-topic-id');

        if ( ! confirm('Are you sure to delete?')){
            e.preventDefault();
        }
    });

    $(document).on('click', '.tutor-expand-all-topic', function (e) {
        e.preventDefault();
        $('.tutor-topics-body').slideDown();
        $('.expand-collapse-wrap i').removeClass('tutor-icon-light-down').addClass('tutor-icon-light-up');
    });
    $(document).on('click', '.tutor-collapse-all-topic', function (e) {
        e.preventDefault();
        $('.tutor-topics-body').slideUp();
        $('.expand-collapse-wrap i').removeClass('tutor-icon-light-up').addClass('tutor-icon-light-down');
    });
    $(document).on('click', '.topic-inner-title, .expand-collapse-wrap', function (e) {
        e.preventDefault();
        var $that = $(this);
        $that.closest('.tutor-topics-wrap').find('.tutor-topics-body').slideToggle();
        $that.closest('.tutor-topics-wrap').find('.expand-collapse-wrap i').toggleClass('tutor-icon-light-down tutor-icon-light-up');
    });

    /**
     * Create new quiz
     */

    $(document).on('click', '.quiz-modal-btn-first-step', function(e){
        e.preventDefault();

        var $that = $(this);
        var $quizTitle = $('[name="quiz_title"]');
        var quiz_title = $quizTitle.val();
        var quiz_description = $('[name="quiz_description"]').val();

        if ( ! quiz_title){
            $quizTitle.closest('.tutor-quiz-builder-group').find('.quiz_form_msg').html('Please enter quiz title');
            return;
        }else{
            $quizTitle.closest('.tutor-quiz-builder-group').find('.quiz_form_msg').html('');
        }

        var course_id = $('#post_ID').val();
        var topic_id = $that.closest('.tutor-modal-wrap').attr('quiz-for-post-id');

        if ($('#tutor_quiz_builder_quiz_id').length) {
            /**
             *
             * @type {jQuery}
             *
             * if quiz id exists, we are sending it to update quiz
             */

            var quiz_id = $('#tutor_quiz_builder_quiz_id').val();
            $.ajax({
                url : window._tutorobject.ajaxurl,
                type : 'POST',
                data : {quiz_title:quiz_title, quiz_description: quiz_description, quiz_id : quiz_id, topic_id : topic_id, action: 'tutor_quiz_builder_quiz_update'},
                beforeSend: function () {
                    $that.addClass('tutor-updating-message');
                },
                success: function (data) {
                    $('#tutor-quiz-'+quiz_id).html(data.data.output_quiz_row);
                    $('#tutor-quiz-modal-tab-items-wrap a[href="#quiz-builder-tab-questions"]').trigger('click');

                    tutor_slider_init();
                },
                complete: function () {
                    $that.removeClass('tutor-updating-message');
                }
            });

            return;
        }

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {quiz_title:quiz_title, quiz_description: quiz_description, course_id : course_id, topic_id : topic_id, action: 'tutor_create_quiz_and_load_modal'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('.tutor-quiz-builder-modal-wrap .modal-container').html(data.data.output);
                $('#tutor-topics-'+topic_id+' .tutor-lessons').append(data.data.output_quiz_row);
                $('#tutor-quiz-modal-tab-items-wrap a[href="#quiz-builder-tab-questions"]').trigger('click');

                tutor_slider_init();

                $(document).trigger('quiz_modal_loaded', {topic_id : topic_id, course_id : course_id});
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });

    });


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
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {quiz_id : quiz_id, topic_id : topic_id, course_id : course_id, action: 'tutor_load_edit_quiz_modal'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('.tutor-quiz-builder-modal-wrap .modal-container').html(data.data.output);
                $('.tutor-quiz-builder-modal-wrap').attr('data-quiz-id', quiz_id).attr('quiz-for-post-id', topic_id).addClass('show');

                //Back to question Tab if exists
                if ($that.attr('data-back-to-tab')){
                    var tabSelector = $that.attr('data-back-to-tab');
                    $('#tutor-quiz-modal-tab-items-wrap a[href="'+tabSelector+'"]').trigger('click');
                }

                $(document).trigger('quiz_modal_loaded', {quiz_id : quiz_id, topic_id : topic_id, course_id : course_id});

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

        var $formInput = $('#quiz-builder-tab-settings :input, #quiz-builder-tab-advanced-options :input').serializeObject();
        $formInput.quiz_id = quiz_id;
        $formInput.action = 'tutor_quiz_modal_update_settings';

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : $formInput,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                data.success ? 
                    tutor_toast(__('Success', 'tutor'), $that.data('toast_success_message'), 'success') : 
                    tutor_toast(__('Update Error', 'tutor'), __('Quiz Update Failed', 'tutor'), 'error');
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
        var $formInput = $('.quiz_question_form :input').serializeObject();
        $formInput.action = 'tutor_quiz_modal_update_question';

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : $formInput,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    //ReOpen questions
                    $that.closest('.tutor-quiz-builder-modal-contents').find('.open-tutor-quiz-modal').trigger('click');
                }else{
                    if (typeof data.data !== 'undefined') {
                        $('#quiz_validation_msg_wrap').html(data.data.validation_msg);
                    }
                }
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

        $.ajax({url : window._tutorobject.ajaxurl, type : 'POST',
            data : {sorted_question_ids : questions, action: 'tutor_quiz_question_sorting'},
        });
    }

    /**
     * Quiz Modal
     */

    $(document).on('click', '.modal-close-btn', function(e){
        e.preventDefault();
        $('.tutor-modal-wrap').removeClass('show');
    });
    $(document).on('keyup', function(e){
        if (e.keyCode === 27){
            $('.tutor-modal-wrap').removeClass('show');
        }
    });

    $(document).on('click', '.tutor-add-quiz-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var quiz_for_post_id = $(this).closest('.tutor_add_quiz_wrap').attr('data-add-quiz-under');
        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {quiz_for_post_id : quiz_for_post_id, action: 'tutor_load_quiz_builder_modal'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('.tutor-quiz-builder-modal-wrap .modal-container').html(data.data.output);
                $('.tutor-quiz-builder-modal-wrap').attr('quiz-for-post-id', quiz_for_post_id).addClass('show');
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });


    /**
     * Quiz Builder Modal Tabs
     */
    $(document).on('click', '.tutor-quiz-modal-tab-item', function(e){
        e.preventDefault();

        var $that = $(this);

        var $quizTitle = $('[name="quiz_title"]');
        var quiz_title = $quizTitle.val();
        if ( ! quiz_title){
            $quizTitle.closest('.tutor-quiz-builder-form-row').find('.quiz_form_msg').html('<p class="quiz-form-warning">Please save the quiz' +
                ' first</p>');
            return;
        }else{
            $quizTitle.closest('.tutor-quiz-builder-form-row').find('.quiz_form_msg').html('');
        }

        var tabSelector = $that.attr('href');
        $('.quiz-builder-tab-container').hide();
        $(tabSelector).show();

        $('a.tutor-quiz-modal-tab-item').removeClass('active');
        $that.addClass('active');
    });

    //Next Prev Tab
    $(document).on('click', '.quiz-modal-btn-next, .quiz-modal-btn-back', function(e){
        e.preventDefault();

        var tabSelector = $(this).attr('href');
        $('#tutor-quiz-modal-tab-items-wrap a[href="'+tabSelector+'"]').trigger('click');
    });

    $(document).on('click', '.quiz-modal-tab-navigation-btn.quiz-modal-btn-cancel', function(e){
        e.preventDefault();
        $('.tutor-modal-wrap').removeClass('show');
    });

    /**
     * Add Question to quiz modal
     */
    $(document).on('click', '.tutor-quiz-open-question-form', function(e){
        e.preventDefault();

        var $that = $(this);

        var quiz_id = $('#tutor_quiz_builder_quiz_id').val();
        var course_id = $('#post_ID').val();
        var question_id = $that.attr('data-question-id');


        var params = {quiz_id : quiz_id, course_id : course_id, action: 'tutor_quiz_builder_get_question_form'};

        if (question_id) {
            params.question_id = question_id;
        }

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : params,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('.tutor-quiz-builder-modal-contents').html(data.data.output);

                //Initializing Tutor Select
                tutor_select().reInit();
                enable_quiz_answer_sorting();
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });

    });

    $(document).on('click', '.tutor-quiz-question-trash', function(e){
        e.preventDefault();

        var $that = $(this);
        var question_id = $that.attr('data-question-id');

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {question_id : question_id, action: 'tutor_quiz_builder_question_delete'},
            beforeSend: function () {
                $that.closest('.quiz-builder-question-wrap').remove();
            },
        });
    });

    /**
     * Get question answers option form to save multiple/single/true-false options
     *
     * @since v.1.0.0
     */

    $(document).on('click', '.add_question_answers_option', function(e){
        e.preventDefault();

        var $that = $(this);
        var question_id = $that.attr('data-question-id');
        
        var $formInput = $('.quiz_question_form :input').serializeObject();
        $formInput.question_id = question_id;
        $formInput.action = 'tutor_quiz_add_question_answers';

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : $formInput,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('#tutor_quiz_question_answer_form').html(data.data.output);
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Get question answers option edit form
     *
     * @since v.1.0.0
     */
    $(document).on('click', '.tutor-quiz-answer-edit a', function(e){
        e.preventDefault();

        var $that = $(this);
        var answer_id = $that.closest('.tutor-quiz-answer-wrap').attr('data-answer-id');

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {answer_id : answer_id, action : 'tutor_quiz_edit_question_answer'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('#tutor_quiz_question_answer_form').html(data.data.output);
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Saving question answers options
     * Student should select the right answer at quiz attempts
     *
     * @since v.1.0.0
     */

    $(document).on('click', '#quiz-answer-save-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var $formInput = $('.quiz_question_form :input').serializeObject();
        $formInput.action = 'tutor_save_quiz_answer_options';

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : $formInput,
            beforeSend: function () {
                $('#quiz_validation_msg_wrap').html("");
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('#tutor_quiz_question_answers').trigger('refresh');
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Updating Answer
     *
     * @since v.1.0.0
     */
    $(document).on('click', '#quiz-answer-edit-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var $formInput = $('.quiz_question_form :input').serializeObject();
        $formInput.action = 'tutor_update_quiz_answer_options';

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : $formInput,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('#tutor_quiz_question_answers').trigger('refresh');
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    $(document).on('change', '.tutor-quiz-answers-mark-correct-wrap input', function(e){
        e.preventDefault();

        var $that = $(this);

        var answer_id = $that.val();
        var inputValue = 1;
        if ( ! $that.prop('checked')) {
            inputValue = 0;
        }

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {answer_id:answer_id, inputValue : inputValue, action : 'tutor_mark_answer_as_correct'},
        });
    });


    $(document).on('refresh', '#tutor_quiz_question_answers', function(e){
        e.preventDefault();

        var $that = $(this);
        var question_id = $that.attr('data-question-id');
        var question_type = $('.tutor_select_value_holder').val();

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {question_id : question_id, question_type : question_type, action: 'tutor_quiz_builder_get_answers_by_question'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
                $('#tutor_quiz_question_answer_form').html('');
            },
            success: function (data) {
                if (data.success){
                    $that.html(data.data.output);
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });



    /**
     * Delete answer for a question in quiz builder
     *
     * @since v.1.0.0
     */

    $(document).on('click', '.tutor-quiz-answer-trash-wrap a.answer-trash-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var answer_id = $that.attr('data-answer-id');

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {answer_id : answer_id, action: 'tutor_quiz_builder_delete_answer'},
            beforeSend: function () {
                $that.closest('.tutor-quiz-answer-wrap').remove();
            },
        });
    });


    /**
     * Delete Quiz
     * @since v.1.0.0
     */

    $(document).on('click', '.tutor-delete-quiz-btn', function(e){
        e.preventDefault();

        if( ! confirm('Are you sure?')){
            return;
        }

        var $that = $(this);
        var quiz_id = $that.attr('data-quiz-id');

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {quiz_id : quiz_id, action: 'tutor_delete_quiz_by_id'},
            beforeSend: function () {
                $that.closest('.course-content-item').remove();
            }
        });
    });

    /**
     * Save answer sorting placement
     *
     * @since v.1.0.0
     */
    function enable_quiz_answer_sorting(){
        if (jQuery().sortable) {
            $("#tutor_quiz_question_answers").sortable({
                handle: ".tutor-quiz-answer-sort-icon",
                start: function (e, ui) {
                    ui.placeholder.css('visibility', 'visible');
                },
                stop: function (e, ui) {
                    tutor_save_sorting_quiz_answer_order();
                },
            });
        }
    }
    function tutor_save_sorting_quiz_answer_order(){
        var answers = {};
        $('.tutor-quiz-answer-wrap').each(function(index, item){
            var $answer = $(this);
            var answer_id = parseInt($answer.attr('data-answer-id'), 10);
            answers[index] = answer_id;
        });

        $.ajax({url : window._tutorobject.ajaxurl, type : 'POST',
            data : {sorted_answer_ids : answers, action: 'tutor_quiz_answer_sorting'},
        });
    }


    /**
     * Tutor Custom Select
     */

    function tutor_select(){
        var obj = {
            init : function(){
                $(document).on('click', '.tutor-select .tutor-select-option', function(e){
                    e.preventDefault();

                    var $that = $(this);
                    if ($that.attr('data-is-pro') !== 'true') {
                        var $html = $that.html().trim();
                        $that.closest('.tutor-select').find('.select-header .lead-option').html($html);
                        $that.closest('.tutor-select').find('.select-header input.tutor_select_value_holder').val($that.attr('data-value')).trigger('change');
                        $that.closest('.tutor-select-options').hide();
                    }else{
                        alert('Tutor Pro version required');
                    }
                });
                $(document).on('click', '.tutor-select .select-header', function(e){
                    e.preventDefault();

                    var $that = $(this);
                    $that.closest('.tutor-select').find('.tutor-select-options').slideToggle();
                });

                this.setValue();
                this.hideOnOutSideClick();
            },
            setValue : function(){
                $('.tutor-select').each(function(){
                    var $that = $(this);
                    var $option = $that.find('.tutor-select-option');

                    if ($option.length){
                        $option.each(function(){
                            var $thisOption = $(this);

                            if ($thisOption.attr('data-selected') === 'selected'){
                                var $html = $thisOption.html().trim();
                                $thisOption.closest('.tutor-select').find('.select-header .lead-option').html($html);
                                $thisOption.closest('.tutor-select').find('.select-header input.tutor_select_value_holder').val($thisOption.attr('data-value'));
                            }
                        });
                    }
                });
            },
            hideOnOutSideClick : function(){
                $(document).mouseup(function(e) {
                    var $option_wrap = $(".tutor-select-options");
                    if ( ! $(e.target).closest('.select-header').length && !$option_wrap.is(e.target) && $option_wrap.has(e.target).length === 0) {
                        $option_wrap.hide();
                    }
                });
            },
            reInit : function(){
                this.setValue();
            }
        };

        return obj;
    }
    tutor_select().init();


    /**
     * If change question type from quiz builder question
     *
     * @since v.1.0.0
     */
    $(document).on('change', 'input.tutor_select_value_holder', function(e) {
        var $that = $(this);
        //$('#tutor_quiz_question_answer_form').html('');
        $('.add_question_answers_option').trigger('click');
        $('#tutor_quiz_question_answers').trigger('refresh');
    });

    $(document).on('click', '.tutor-media-upload-btn', function(e){
        e.preventDefault();

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
            $that.html('<img src="'+attachment.url+'" alt="" />');
            $that.closest('.tutor-media-upload-wrap').find('input').val(attachment.id);
        });
        frame.open();
    });
    $(document).on('click', '.tutor-media-upload-trash', function(e){
        e.preventDefault();

        var $that = $(this);
        $that.closest('.tutor-media-upload-wrap').find('.tutor-media-upload-btn').html('<i class="tutor-icon-image1"></i>');
        $that.closest('.tutor-media-upload-wrap').find('input').val('');
    });

    /**
     * Delay Function
     */

    var tutor_delay = (function(){
        var timer = 0;
        return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();

    /**
     * Add instructor modal
     */
    $(document).on('click', '.tutor-add-instructor-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var course_id = $('#post_ID').val();

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {course_id : course_id, action: 'tutor_load_instructors_modal'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    $('.tutor-instructors-modal-wrap .modal-container').html(data.data.output);
                    $('.tutor-instructors-modal-wrap').addClass('show');
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    $(document).on('change keyup', '.tutor-instructors-modal-wrap .tutor-modal-search-input', function(e){
        e.preventDefault();

        var $that = $(this);
        var $modal = $('.tutor-modal-wrap');

        tutor_delay(function(){
            var search_terms = $that.val();
            var course_id = $('#post_ID').val();

            $.ajax({
                url : window._tutorobject.ajaxurl,
                type : 'POST',
                data : {course_id : course_id, search_terms : search_terms, action: 'tutor_load_instructors_modal'},
                beforeSend: function () {
                    $modal.addClass('loading');
                },
                success: function (data) {
                    if (data.success){
                        $('.tutor-instructors-modal-wrap .modal-container').html(data.data.output);
                        $('.tutor-instructors-modal-wrap').addClass('show');
                    }
                },
                complete: function () {
                    $modal.removeClass('loading');
                }
            });

        }, 1000)
    });
    $(document).on('click', '.add_instructor_to_course_btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var $modal = $('.tutor-modal-wrap');
        var course_id = $('#post_ID').val();
        
        var data = $modal.find('input').serializeObject();
        data.course_id = course_id;
        data.action = 'tutor_add_instructors_to_course';

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : data,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    $('.tutor-course-available-instructors').html(data.data.output);
                    $('.tutor-modal-wrap').removeClass('show');
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    $(document).on('click', '.tutor-instructor-delete-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var course_id = $('#post_ID').val();
        var instructor_id = $that.closest('.added-instructor-item').attr('data-instructor-id');

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {course_id:course_id, instructor_id:instructor_id, action : 'detach_instructor_from_course'},
            success: function (data) {
                if (data.success){
                    $that.closest('.added-instructor-item').remove();
                }
            }
        });
    });

    $(document).on('click', '.settings-tabs-navs li', function(e){
        e.preventDefault();

        var $that = $(this);
        var data_target = $that.find('a').attr('data-target');
        var url = $that.find('a').attr('href');

        $that.addClass('active').siblings('li.active').removeClass('active');
        $('.settings-tab-wrap').removeClass('active').hide();
        $(data_target).addClass('active').show();

        window.history.pushState({}, '', url);
    });

    /**
     * Re init required
     * Modal Loaded...
     */

    $(document).on('lesson_modal_loaded quiz_modal_loaded assignment_modal_loaded', function(e, obj){
        if (jQuery().select2){
            $('.select2_multiselect').select2({
                dropdownCssClass:'increasezindex'
            });
        }
        if (jQuery.datepicker){
            $( ".tutor_date_picker" ).datepicker({"dateFormat" : 'yy-mm-dd'});
        }
    });
    $(document).on('lesson_modal_loaded', function(e, obj){
        $('.tutor-lesson-modal-wrap .modal-title h1').html('Lesson');
    });
    $(document).on('assignment_modal_loaded', function(e, obj){
        $('.tutor-lesson-modal-wrap .modal-title h1').html('Assignment');
    });

    /**
     * Tutor number validation
     *
     * @since v.1.6.3
     */
    $(document).on('keyup change', '.tutor-number-validation', function(e) {
        var input = $(this);
        var val = parseInt(input.val());
        var min = parseInt(input.attr('data-min'));
        var max = parseInt(input.attr('data-max'));
        if ( val < min )  { 
            input.val(min);
        } else if ( val > max ) {
            input.val(max);
        }
    });

    /*
    * @since v.1.6.4
    * Quiz Attempts Instructor Feedback 
    */
   $(document).on('click', '.tutor-instructor-feedback', function(e) {

    e.preventDefault();
    var $that = $(this);
    
    $.ajax({
        url : (window.ajaxurl || _tutorobject.ajaxurl),
        type : 'POST',
        data : {attempts_id: $that.data('attemptid'), feedback: $('.tutor-instructor-feedback-content').val() , action: 'tutor_instructor_feedback'},
        beforeSend: function () {
            $that.addClass('tutor-updating-message'); 
        },
        success: function (data) {
            if (data.success){
                $that.closest('.course-content-item').remove();
                tutor_toast(__('Success', 'tutor'), $that.data('toast_success_message'), 'success');
            }
        },
        complete: function () {
            $that.removeClass('tutor-updating-message');
        }
        });
    });

    /**
     * Since 1.7.9
     * Announcements scripts
     */
    var add_new_button = $(".tutor-announcement-add-new");
    var update_button = $(".tutor-announcement-edit");
    var delete_button = $(".tutor-announcement-delete");
    var details_button = $(".tutor-announcement-details");
    var close_button = $(".tutor-announcement-close-btn");
    var create_modal = $(".tutor-accouncement-create-modal");
    var update_modal = $(".tutor-accouncement-update-modal");
    var details_modal = $(".tutor-accouncement-details-modal");
    //open create modal
    $(add_new_button).click(function(){
      create_modal.addClass("show");
      $("#tutor-annoucement-backend-create-modal").addClass('show');
    })

    $(details_button).click(function(){
        var announcement_date = $(this).attr('announcement-date');
        var announcement_id = $(this).attr('announcement-id');
        var course_id = $(this).attr('course-id');
        var course_name = $(this).attr('course-name');
        var announcement_title = $(this).attr('announcement-title');
        var announcement_summary = $(this).attr('announcement-summary'); 
        
        $(".tutor-announcement-detail-content").html(`<h3>${announcement_title}</h3><p>${announcement_summary}</p>`);  
        $(".tutor-announcement-detail-course-info p").html(`${course_name}`);     
        $(".tutor-announcement-detail-date-info p").html(`${announcement_date}`); 
        //set attr on edit button
        $("#tutor-announcement-edit-from-detail").attr('announcement-id',announcement_id);    
        $("#tutor-announcement-edit-from-detail").attr('course-id',course_id);    
        $("#tutor-announcement-edit-from-detail").attr('announcement-title',announcement_title);    
        $("#tutor-announcement-edit-from-detail").attr('announcement-summary',announcement_summary);  
        $("#tutor-announcement-delete-from-detail").attr('announcement-id',announcement_id);  
        details_modal.addClass("show");
    })

    //open update modal
    $(update_button).click(function(){
        if(details_modal){
            details_modal.removeClass('show');
        }
        var announcement_id = $(this).attr('announcement-id');
        var course_id = $(this).attr('course-id');
        var announcement_title = $(this).attr('announcement-title');
        var announcement_summary = $(this).attr('announcement-summary');

        $("#tutor-announcement-course-id").val(course_id);
        $("#announcement_id").val(announcement_id);
        $("#tutor-announcement-title").val(announcement_title);
        $("#tutor-announcement-summary").val(announcement_summary);
        
        update_modal.addClass("show");
    })

    //close create and update modal
    $(close_button).click(function(){
        create_modal.removeClass("show");
        update_modal.removeClass("show");
        details_modal.removeClass("show");
        $("#tutor-annoucement-backend-create-modal").removeClass('show');
    })

    //create announcement
    $(".tutor-announcements-form").on('submit',function(e){
        e.preventDefault();
        var $btn = $(this).find('button[type="submit"]');
        var formData = $(".tutor-announcements-form").serialize() + '&action=tutor_announcement_create' + '&action_type=create';
        
        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : formData,
            beforeSend: function() {
                $btn.addClass('tutor-updating-message');
            },
            success: function(data) {
                
                $(".tutor-alert").remove();
                
                if(data.status=="success") {
                    location.reload();
                }

                if(data.status=="validation_error"){
                    $(".tutor-announcements-create-alert").append(`<div class="tutor-alert alert-warning"></div>`);
                    for(let [key,value] of Object.entries(data.message)){
                        
                        $(".tutor-announcements-create-alert .tutor-alert").append(`<li>${value}</li>`);
                    }
                }                
                if(data.status=="fail"){
                    
                    $(".tutor-announcements-create-alert").html(`<li>${data.message}</li>`);
                
                }            
            },
            error: function(data){
                console.log(data);
            }
        })
    })
    //update announcement
    $(".tutor-announcements-update-form").on('submit',function(e){
        e.preventDefault();
        var $btn = $(this).find('button[type="submit"]');
        var formData  = $(".tutor-announcements-update-form").serialize() + '&action=tutor_announcement_create' + '&action_type=update';
       
        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : formData,
            beforeSend: function() {
                $btn.addClass('tutor-updating-message');
            },
            success: function(data) {
                
                $(".tutor-alert").remove();
                if(data.status=="success"){
                    location.reload();
                }
                if(data.status=="validation_error"){
                    $(".tutor-announcements-update-alert").append(`<div class="tutor-alert alert-warning"></div>`);
                    for(let [key,value] of Object.entries(data.message)){
                        $(".tutor-announcements-update-alert > .tutor-alert").append(`<li>${value}</li>`);
                    }
                }                
                if(data.status=="fail"){
                    
                        $(".tutor-announcements-create-alert").html(`<li>${data.message}</li>`);
                
                }  
            },
            error: function(){

            }
        })
    });

    $(delete_button).click(function(){
        var announcement_id = $(this).attr('announcement-id');
        var whichtr = $("#tutor-announcement-tr-"+announcement_id);
        if(confirm("Do you want to delete?")){
            $.ajax({
                url : window._tutorobject.ajaxurl,
                type : 'POST',
                data : {action:'tutor_announcement_delete',announcement_id:announcement_id},
                beforeSend: function() {
                
                },
                success: function(data) {
                    
                    whichtr.remove();
                    if(details_modal.length){
                        details_modal.removeClass('show');
                    }
                    if(data.status == "fail"){
                        console.log(data.message);
                    }
                },
                error: function(){

                }
            })            
        }
    })
    //sorting 
    if (jQuery.datepicker){
        $( "#tutor-announcement-datepicker" ).datepicker({"dateFormat" : 'yy-mm-dd'});
    }
    function urlPrams(type, val){
        var url = new URL(window.location.href);
        var search_params = url.searchParams;
        search_params.set(type, val);
        
        url.search = search_params.toString();
        
        search_params.set('paged', 1);
        url.search = search_params.toString();

        return url.toString();
    }
    $('.tutor-announcement-course-sorting').on('change', function(e){
        window.location = urlPrams( 'course-id', $(this).val() );
    });
    $('.tutor-announcement-order-sorting').on('change', function(e){
        window.location = urlPrams( 'order', $(this).val() );
    });
    $('.tutor-announcement-date-sorting').on('change', function(e){
        window.location = urlPrams( 'date', $(this).val() );
    });
    $('.tutor-announcement-search-sorting').on('click', function(e){
        window.location = urlPrams( 'search', $(".tutor-announcement-search-field").val() );
    });
    //dropdown toggle
    $(document).click(function(){
        $(".tutor-dropdown").removeClass('show');
      });

      $(".tutor-dropdown").click(function(e){
        e.stopPropagation();
        if ( $('.tutor-dropdown').hasClass('show') ) {
            $('.tutor-dropdown').removeClass('show')
        }
        $(this).addClass('show');
      });

    //announcement end


    
   /**
    * @since v.1.9.0
    * Parse and show video duration on link paste in lesson video 
    */
    var video_url_input = '.video_source_wrap_external_url input, .video_source_wrap_vimeo input, .video_source_wrap_youtube input, .video_source_wrap_html5, .video_source_upload_wrap_html5';
    var autofill_url_timeout;
    $('body').on('paste', video_url_input, function(e) {
        e.stopImmediatePropagation();

        var root = $(this).closest('.lesson-modal-form-wrap').find('.tutor-option-field-video-duration');
        var duration_label = root.find('label');
        var is_wp_media = $(this).hasClass('video_source_wrap_html5') || $(this).hasClass('video_source_upload_wrap_html5');
        var autofill_url = $(this).data('autofill_url');
        $(this).data('autofill_url', null);

        var video_url = is_wp_media ? $(this).find('span').data('video_url') : (autofill_url || e.originalEvent.clipboardData.getData('text')); 
        
        var toggle_loading = function(show) {

            if(!show) {
                duration_label.find('img').remove();
                return;
            }

            // Show loading icon
            if(duration_label.find('img').length==0) {
                duration_label.append(' <img src="'+window._tutorobject.loading_icon_url+'" style="display:inline-block"/>');
            }
        }

        var set_duration = function(sec_num) {
            var hours   = Math.floor(sec_num / 3600);
            var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
            var seconds = Math.round( sec_num - (hours * 3600) - (minutes * 60) );

            if (hours   < 10) {hours   = "0"+hours;}
            if (minutes < 10) {minutes = "0"+minutes;}
            if (seconds < 10) {seconds = "0"+seconds;}
    
            var fragments = [hours, minutes, seconds];
            var time_fields = root.find('input');
            for(var i=0; i<3; i++) {
                time_fields.eq(i).val(fragments[i]);
            }
        }
        
        var yt_to_seconds = function (duration) {
            var match = duration.match(/PT(\d+H)?(\d+M)?(\d+S)?/);
          
            match = match.slice(1).map(function(x) {
                if (x != null) {
                    return x.replace(/\D/, '');
                }
            });
          
            var hours = (parseInt(match[0]) || 0);
            var minutes = (parseInt(match[1]) || 0);
            var seconds = (parseInt(match[2]) || 0);
          
            return hours * 3600 + minutes * 60 + seconds;
        }

        if(is_wp_media || $(this).parent().hasClass('video_source_wrap_external_url')) {
            var player = document.createElement('video');
            player.addEventListener('loadedmetadata', function() {
                set_duration( player.duration );
                toggle_loading(false);
            });

            toggle_loading(true);
            player.src = video_url;

        } else if($(this).parent().hasClass('video_source_wrap_vimeo')) {

            var regExp = /^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/;
            var match = video_url.match(regExp);
            var video_id = match ? match[5] : null;

            if(video_id) {
                toggle_loading(true);

                $.getJSON('http://vimeo.com/api/v2/video/'+video_id+'/json', function(data) {
                    if(Array.isArray(data) && data[0] && data[0].duration!==undefined) {
                        set_duration(data[0].duration);
                    }
                    
                    toggle_loading(false);
                });
            }            
        } else if($(this).parent().hasClass('video_source_wrap_youtube')) {
            var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/;
            var match = video_url.match(regExp);
            var video_id = (match && match[7].length==11) ? match[7] : false;
            var api_key = $(this).data('youtube_api_key');

            if(video_id && api_key) {

                var result_url = 'https://www.googleapis.com/youtube/v3/videos?id='+video_id+'&key='+api_key+'&part=contentDetails';
                toggle_loading(true);

                $.getJSON(result_url, function(data) {
                    if(typeof data=='object' && data.items && data.items[0] && data.items[0].contentDetails && data.items[0].contentDetails.duration) {
                        set_duration( yt_to_seconds(data.items[0].contentDetails.duration) );
                    }

                    toggle_loading(false);
                });
            }
        }
    }).on('input', video_url_input, function() {

        if(autofill_url_timeout) {
            clearTimeout(autofill_url_timeout);
        }

        var $this = $(this);
        autofill_url_timeout = setTimeout(function() {
            var val = $this.val();
            val = val ? val.trim() : '';
            console.log('Trigger', val);
            val ? $this.data('autofill_url', val).trigger('paste') : 0;
        }, 700);
    });

   /**
    * @since v.1.8.6
    * SUbmit form through ajax
    */
    $('.tutor-form-submit-through-ajax').submit(function(e) {
        e.preventDefault();

        var $that = $(this);
        var url = $(this).attr('action') || window.location.href;
        var type = $(this).attr('method') || 'GET';
        var data = $(this).serializeObject();

        $that.find('button').addClass('tutor-updating-message');

        $.ajax({
            url: url,
            type: type,
            data: data,
            success: function() {
                tutor_toast(__('Success', 'tutor'), $that.data('toast_success_message'), 'success');
            },
            complete: function () {
                $that.find('button').removeClass('tutor-updating-message');
            }
        });
    });

    /*
    * @since v.1.7.9
    * Send wp nonce to every ajax request
    */
    $.ajaxSetup({data : tutor_get_nonce_data()});
});

jQuery.fn.serializeObject = function()
{
   var values = {};
   var array = this.serializeArray();

   jQuery.each(array, function() {
       if (values[this.name]) {
           if (!values[this.name].push) {
               values[this.name] = [values[this.name]];
           }
           values[this.name].push(this.value || '');
       } else {
           values[this.name] = this.value || '';
       }
   });

   return values;
};

function tutor_toast(title, description, type) {
    var tutor_ob = window._tutorobject || {};
    var asset = (tutor_ob.tutor_url || '') + 'assets/images/';

    if(!jQuery('.tutor-toast-parent').length) {
        jQuery('body').append('<div class="tutor-toast-parent"></div>');
    }

    var icons = {
        success : asset+'icon-check.svg',
        error: asset+'icon-cross.svg'
    }
    
    var content = jQuery('\
        <div>\
            <div>\
                <img src="'+icons[type]+'"/>\
            </div>\
            <div>\
                <div>\
                    <b>'+title+'</b>\
                    <span>'+description+'</span>\
                </div>\
            </div>\
            <div>\
                <i class="tutor-toast-close tutor-icon-line-cross"></i>\
            </div>\
        </div>');

    content.find('.tutor-toast-close').click(function() {
        content.remove();
    });

    jQuery('.tutor-toast-parent').append(content);

    setTimeout(function() {
        if(content) {
            content.fadeOut('fast', function() {
                jQuery(this).remove();
            });
        }
    }, 5000);
}
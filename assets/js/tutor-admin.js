jQuery(document).ready(function($){
    'use strict';

    if (jQuery().select2){
        $('.tutor_select2').select2();
    }

    /**
     * Option Settings Nav Tab
     */
    $('.tutor-option-nav-tabs li a').click(function(e){
        e.preventDefault();
        var tab_page_id = $(this).attr('href');
        $('.option-nav-item').removeClass('current');
        $(this).closest('li').addClass('current');
        $('.tutor-option-nav-page').hide();
        $(tab_page_id).addClass('current-page').show();
    });

    $('#save_tutor_option').click(function (e) {
        e.preventDefault();
        $(this).closest('form').submit();
    });
    $('#tutor-option-form').submit(function(e){
        e.preventDefault();

        var $form = $(this);
        var data = $form.serialize();

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : data,
            beforeSend: function () {
                $form.find('.button').addClass('tutor-updating-message');
            },
            success: function (data) {
                //
            },
            complete: function () {
                $form.find('.button').removeClass('tutor-updating-message');
                window.location.reload();
            }
        });
    });

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
     * Course and lesson sorting
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
    enable_sorting_topic_lesson();
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

    $(document).on('click', '.create_new_topic_btn', function (e) {
        e.preventDefault();
        $('.tutor-metabox-add-topics').slideToggle();
    });

    $(document).on('click', '#tutor-add-topic-btn', function (e) {
        e.preventDefault();
        var $that = $(this);
        var form_data = $that.closest('.tutor-metabox-add-topics').find('input, textarea').serialize()+'&action=tutor_add_course_topic';

        $.ajax({
            url : ajaxurl,
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
            url : ajaxurl,
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
     * Confirmation for deleting Topic
     */
    $(document).on('click', '.topic-delete-btn a', function(e){
        var topic_id = $(this).attr('data-topic-id');
        console.log(topic_id);

        if ( ! confirm('Are you sure to delete?')){
            e.preventDefault();
        }
    });

    /**
     * Create Lesson Under Topic
     */
    /*
    $(document).on('click', '.create-lesson-in-topic-btn', function(e){
        e.preventDefault();
        $(this).closest('.tutor-lessons').find('.tutor-create-new-lesson-form').toggle();
    });
    $(document).on('click', '.tutor-create-lesson-btn', function(e){
        e.preventDefault();
        var $that = $(this);

        var course_id = $('#post_ID').val();
        var topic_id = $that.closest('.tutor-create-new-lesson-form').attr('data-topic-id');

        var form_data = $that.closest('.tutor-create-new-lesson-form').find('input, textarea').serialize()+'&course_id='+course_id+'&topic_id='+topic_id+'&action=tutor_create_lesson';

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : form_data,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    $('#tutor-course-content-wrap').html(data.data.course_contents);
                    $that.closest('.tutor-create-new-lesson-form').find('input, textarea').each(function () {
                        $(this).val('');
                    });
                    enable_sorting_topic_lesson();
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    $(document).on('click', '.open-inline-lesson-edit-btn', function(e){
        e.preventDefault();
        $(this).closest('.tutor-lesson').find('.tutor-edit-inline-lesson-form').toggle();
    });
    $(document).on('change keyup', '.inline-lesson-title-input', function (e) {
        e.preventDefault();
        $(this).closest('.tutor-lesson').find('.open-inline-lesson-edit-btn').html($(this).val());
    });

    $(document).on('click', '.edit-inline-lesson-btn', function(e){
        e.preventDefault();
        var $that = $(this);

        var course_id = $('#post_ID').val();
        var lesson_id = $that.closest('.tutor-edit-inline-lesson-form').attr('data-lesson-id');
        var topic_id = $that.closest('.tutor-edit-inline-lesson-form').attr('data-topic-id');

        var form_data = $that.closest('.tutor-edit-inline-lesson-form').find('input, textarea').serialize()+'&course_id='+course_id+'&lesson_id='+lesson_id+'&topic_id='+topic_id+'&action=tutor_update_inline_lesson';

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : form_data,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    $that.closest('.tutor-edit-inline-lesson-form').hide();
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });
*/
    $(document).on('click', '.tutor-expand-all-topic', function (e) {
        e.preventDefault();
        $('.tutor-topics-body').slideDown();
    });
    $(document).on('click', '.tutor-collapse-all-topic', function (e) {
        e.preventDefault();
        $('.tutor-topics-body').slideUp();
    });
    $(document).on('click', '.expand-collapse-wrap', function (e) {
        e.preventDefault();
        var $that = $(this);
        $that.closest('.tutor-topics-wrap').find('.tutor-topics-body').slideToggle();
        $that.closest('.tutor-topics-wrap').find('.expand-collapse-wrap .dashicons').toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2 ');
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
            url : ajaxurl,
            type : 'POST',
            data : {lesson_id : lesson_id, topic_id : topic_id, course_id : course_id, action: 'tutor_load_edit_lesson_modal'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('.tutor-lesson-modal-wrap .modal-container').html(data.data.output);
                $('.tutor-lesson-modal-wrap').attr({'data-lesson-id' : lesson_id, 'data-topic-id':topic_id}).addClass('show');

                tinymce.init(tinyMCEPreInit.mceInit.content);
                tinymce.execCommand( 'mceRemoveEditor', false, 'tutor_lesson_modal_editor' );
                tinyMCE.execCommand('mceAddEditor', false, "tutor_lesson_modal_editor");
            },
            complete: function () {
                quicktags({id : "tutor_lesson_modal_editor"});
                $that.removeClass('tutor-updating-message');
            }
        });
    });

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
            $that.closest('.tutor-thumbnail-wrap').find('.thumbnail-img').html('<img src="'+attachment.url+'" alt="" />');
            $that.closest('.tutor-thumbnail-wrap').find('input').val(attachment.id);
        });
        frame.open();
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
            url : ajaxurl,
            type : 'POST',
            data : {lesson_id : lesson_id, action: 'tutor_delete_lesson_by_id'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    $that.closest('.tutor-lesson').remove();
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Lesson Update or Create Modal
     */
    $(document).on( 'click', '.update_lesson_modal_btn',  function( event ){
        event.preventDefault();

        var $that = $(this);
        var content;
        var editor = tinyMCE.get('tutor_lesson_modal_editor');
        if (editor) {
            content = editor.getContent();
        } else {
            content = $('#'+inputid).val();
        }

        var form_data = $(this).closest('form').serialize();
        form_data += '&lesson_content='+content;

        $.ajax({
            url : ajaxurl,
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
                    $('.tutor-lesson-modal-wrap').removeClass('show');
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Lesson Video
     */
    $(document).on('change', '.tutor_lesson_video_source', function(e){
        var selector = $(this).val();
        $('[class^="video_source_wrap"]').hide();
        $('.video_source_wrap_'+selector).show();
    });

    $(document).on( 'click', '.video_source_wrap_html5 .video_upload_btn',  function( event ){
        event.preventDefault();

        var $that = $(this);
        var frame;
        // If the media frame already exists, reopen it.
        if ( frame ) {
            frame.open();
            return;
        }

        // Create a new media frame
        frame = wp.media({
            title: 'Select or Upload Media Of Your Chosen Persuasion',
            button: {
                text: 'Use this media'
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected in the media frame...
        frame.on( 'select', function() {
            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();
            $that.closest('.video_source_wrap_html5').find('span.video_media_id').text(attachment.id).closest('p').show();
            $that.closest('.video_source_wrap_html5').find('input').val(attachment.id);
        });
        // Finally, open the modal on click
        frame.open();
    });

    //tutor_video_poster_upload_btn
    $(document).on( 'click', '.tutor_video_poster_upload_btn',  function( event ){
        event.preventDefault();

        var $that = $(this);
        var frame;
        // If the media frame already exists, reopen it.
        if ( frame ) {
            frame.open();
            return;
        }

        // Create a new media frame
        frame = wp.media({
            title: 'Select or Upload Media Of Your Chosen Persuasion',
            button: {
                text: 'Use this media'
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected in the media frame...
        frame.on( 'select', function() {
            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();
            $that.closest('.tutor-video-poster-wrap').find('.video-poster-img').html('<img src="'+attachment.url+'" alt="" />');
            $that.closest('.tutor-video-poster-wrap').find('input').val(attachment.id);
        });
        // Finally, open the modal on click
        frame.open();
    });

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
            title: 'Select or Upload Media Of Your Chosen Persuasion',
            button: {
                text: 'Use this media'
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

                    var inputHtml = '<div class="tutor-added-attachment"><p> <a href="javascript:;" class="tutor-delete-attachment">×</a> <span> <a href="'+attachment.url+'">'+attachment.filename+'</a> </span> </p><input type="hidden" name="tutor_attachments[]" value="'+attachment.id+'"></div>';
                    $that.closest('.tutor-lesson-attachments-metabox').find('.tutor-added-attachments-wrap').append(inputHtml);
                }
            }
        });
        // Finally, open the modal on click
        frame.open();
    });

    /**
     * Open Sidebar Menu
     */
    if (tutor_data.open_tutor_admin_menu){
        var $adminMenu = $('#adminmenu');
        $adminMenu.find('[href="admin.php?page=tutor"]').closest('li.wp-has-submenu').addClass('wp-has-current-submenu');
        $adminMenu.find('[href="admin.php?page=tutor"]').closest('li.wp-has-submenu').find('a.wp-has-submenu').removeClass('wp-has-current-submenu').addClass('wp-has-current-submenu');
    }

    /**
     * Add question answer for quiz
     */

    $(document).on('change keyup paste', '.question_field_title', function(){
        var $that = $(this);
        $that.closest('.single-question-item').find('.tutor-question-item-head').find('.question-title').text($that.val());
    });

    $(document).on('change', '.question_type_field', function(){
        var $that = $(this);
        var question_type = $that.val();

        var option_text = $that.find('option[value="'+question_type+'"]').text();
        $that.closest('.single-question-item').find('.tutor-question-item-head').find('.question-type').text(option_text);

        var question_id = $that.closest('.single-question-item').attr('data-question-id');
        var data = {question_id: question_id, question_type : question_type, action: 'quiz_question_type_changed'};

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : data,
            beforeSend: function () {
                $that.closest('.single-question-item').find('.tutor-loading-icon-wrap').addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    $that.closest('.quiz-question-form-wrap').find('.answer-entry-wrap').html(data.data.multi_answer_options);

                    if (question_type === 'true_false' && $('.answer-option-row').length >= 2){
                        $('.add_answer_option_wrap').hide();
                    }else{
                        $('.add_answer_option_wrap').show();
                    }
                }
            },
            complete: function () {
                $that.closest('.single-question-item').find('.tutor-loading-icon-wrap').removeClass('tutor-updating-message');
            }
        });
    });

    $(document).on('click', '.add_answer_option_btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var question_id = $that.closest('.single-question-item').attr('data-question-id');
        var question_type = $that.closest('.quiz-question-form-wrap').find('select.question_type_field').val();
        var data = {question_id: question_id,  action: 'quiz_add_answer_to_question'};

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : data,
            beforeSend: function () {
                $that.removeClass('updated-message').addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    $that.closest('.answer-entry-wrap').find('table.multi-answers-options').append(data.data.data_tr);

                    //Hide add answer button if true false and 2 option exists
                    if (question_type === 'true_false' && $that.closest('.answer-entry-wrap').find('tr.answer-option-row').length >= 2){
                        $that.closest('.add_answer_option_wrap').hide();
                    }else{
                        $that.closest('.add_answer_option_wrap').show();
                    }
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message').addClass('updated-message');
            }
        });
    });

    $(document).on('click', '.add_question_btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var $title = $('[name="new_question_title"]');
        var question_title = $title.val();
        var question_type = $('[name="new_question_type"]').val();
        var quiz_id = $('#post_ID').val();

        //If no question title, stop here
        if ( ! question_title.length){
            $title.addClass('tutor-input-text-error');
            return;
        }else{
            $title.removeClass('tutor-input-text-error');
        }

        var  data = {question_title : question_title, question_type:question_type, quiz_id : quiz_id, action: 'quiz_page_add_new_question' };
        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : data,
            beforeSend: function () {
                $that.removeClass('updated-message').addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    $('.single-question-item .quiz-question-form-wrap').hide();
                    $('.tutor-quiz-questions-wrap').append(data.data.question_html);
                    $('.single-question-item:last-child .quiz-question-form-wrap').show();
                    $title.val('');
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message').addClass('updated-message');
            }
        });
    });

    //Show hide question settings
    $(document).on('click', '.question-action-btn.down', function(e){
        e.preventDefault();
        $(this).closest('.single-question-item').find('.quiz-question-form-wrap').toggle();
        $(this).find('i.dashicons').toggleClass('dashicons-arrow-up-alt2 dashicons-arrow-down-alt2');
    });

    $(document).on('change', '.single-question-item', function(e){
        e.preventDefault();

        var $that = $(this);
        var data = $(this).find("select, textarea, input").serialize()+'&action=update_tutor_question';
        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : data,
            beforeSend: function () {
                $that.find('.tutor-loading-icon-wrap').addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){

                }
            },
            complete: function () {
                $that.find('.tutor-loading-icon-wrap').removeClass('tutor-updating-message');
            }
        });
    });

    $(document).on('click', '.quiz-answer-option-delete-btn', function(e){
        e.preventDefault();
        var $that = $(this);
        var $closestTable = $that.closest('table');
        var $loadingIcon = $that.closest('.single-question-item').find('.tutor-loading-icon-wrap');

        var question_type = $that.closest('.quiz-question-form-wrap').find('select.question_type_field').val();
        var answer_option_id = $that.closest('tr').attr('data-answer-option-id');

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {answer_option_id:answer_option_id, action: 'quiz_delete_answer_option'},
            beforeSend: function () {
                $loadingIcon.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    $that.closest('tr').remove();
                    //Hide add answer button if true false and 2 option exists
                    if (question_type === 'true_false' && $closestTable.find('tr.answer-option-row').length >= 2){
                        $closestTable.closest('.answer-entry-wrap').find('.add_answer_option_wrap').hide();
                    }else{
                        $closestTable.closest('.answer-entry-wrap').find('.add_answer_option_wrap').show();
                    }
                }
            },
            complete: function () {
                $loadingIcon.removeClass('tutor-updating-message');
            }
        });
    });

    $(document).on('click', '.question-action-btn.trash', function(e){
        e.preventDefault();

        var $that = $(this);
        var question_id = $that.closest('.single-question-item').attr('data-question-id');
        var $loadingIcon = $that.closest('.single-question-item').find('.tutor-loading-icon-wrap');

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {question_id:question_id, action: 'quiz_question_delete'},
            beforeSend: function () {
                $loadingIcon.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    $that.closest('.single-question-item').remove();
                }
            },
            complete: function () {
                $loadingIcon.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Sort quiz questions
     */

    if (jQuery().sortable) {
        $(".tutor-quiz-questions-wrap").sortable({
            handle: ".question-short",
            start: function (e, ui) {
                ui.placeholder.css('visibility', 'visible');
            },
            stop: function (e, ui) {
                var questions = {};
                $('.single-question-item').each(function(index, item){
                    var $question = $(this);
                    var question_id = parseInt($question.attr('data-question-id').match(/\d+/)[0], 10);
                    questions[index] = { 'question_id' : question_id };
                });

                $.post(ajaxurl, {questions : questions, action: 'sorting_quiz_questions'});
            },
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

    /*
    $(document).on('click', '.tutor-add-quiz-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var quiz_for_post_id = $(this).closest('.tutor_add_quiz_wrap').attr('data-add-quiz-under');
        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {quiz_for_post_id : quiz_for_post_id, action: 'tutor_load_quiz_modal'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('.tutor-quiz-modal-wrap .modal-container').html(data.data.output);
                $('.tutor-quiz-modal-wrap').attr('quiz-for-post-id', quiz_for_post_id).addClass('show');
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });
    */



    $(document).on('click', '.tutor-add-quiz-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var quiz_for_post_id = $(this).closest('.tutor_add_quiz_wrap').attr('data-add-quiz-under');
        $.ajax({
            url : ajaxurl,
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

    $(document).on('click', '.quiz-modal-btn-first-step', function(e){
        e.preventDefault();

        var $that = $(this);
        var $quizTitle = $('[name="quiz_title"]');
        var quiz_title = $quizTitle.val();
        var quiz_description = $('[name="quiz_description"]').val();

        if ( ! quiz_title){
            $quizTitle.closest('.tutor-quiz-builder-form-row').find('.quiz_form_msg').html('<p class="quiz-form-warning">Please enter quiz title</p>');
            return;
        }else{
            $quizTitle.closest('.tutor-quiz-builder-form-row').find('.quiz_form_msg').html('');
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
                url : ajaxurl,
                type : 'POST',
                data : {quiz_title:quiz_title, quiz_description: quiz_description, quiz_id : quiz_id, topic_id : topic_id, action: 'tutor_quiz_builder_quiz_update'},
                beforeSend: function () {
                    $that.addClass('tutor-updating-message');
                },
                success: function (data) {
                    $('#tutor-quiz-'+quiz_id).html(data.data.output_quiz_row);
                    $('#tutor-quiz-modal-tab-items-wrap a[href="#quiz-builder-tab-questions"]').trigger('click');
                },
                complete: function () {
                    $that.removeClass('tutor-updating-message');
                }
            });

            return;
        }

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {quiz_title:quiz_title, quiz_description: quiz_description, course_id : course_id, topic_id : topic_id, action: 'tutor_create_quiz_and_load_modal'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('.tutor-quiz-builder-modal-wrap .modal-container').html(data.data.output);
                $('#tutor-topics-'+topic_id+' .tutor-lessons').append(data.data.output_quiz_row);
                $('#tutor-quiz-modal-tab-items-wrap a[href="#quiz-builder-tab-questions"]').trigger('click');
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
            }
        });
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
            url : ajaxurl,
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
                $that.closest('.quiz-questions-form').find('.open-tutor-quiz-modal').trigger('click');
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
            url : ajaxurl,
            type : 'POST',
            data : {question_id : question_id, action: 'tutor_quiz_builder_question_delete'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $that.closest('.quiz-builder-question-wrap').remove();
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
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
        var $formInput = $('.quiz_question_form :input').serialize()+'&question_id='+question_id+'&action=tutor_quiz_add_question_answers';

        $.ajax({
            url : ajaxurl,
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
     * Saving question answers options
     * Student should select the right answer at quiz attempts
     *
     * @since v.1.0.0
     */

    $(document).on('click', '#quiz-answer-save-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var $formInput = $('.quiz_question_form :input').serialize()+'&action=tutor_save_quiz_answer_options';

        $.ajax({
            url : ajaxurl,
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

    $(document).on('refresh', '#tutor_quiz_question_answers', function(e){
        e.preventDefault();

        var $that = $(this);
        var question_id = $that.attr('data-question-id');
        var question_type = $('.tutor_select_value_holder').val();

        $.ajax({
            url : ajaxurl,
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
            url : ajaxurl,
            type : 'POST',
            data : {answer_id : answer_id, action: 'tutor_quiz_builder_delete_answer'},
            beforeSend: function () {
                $that.closest('.tutor-quiz-answer-wrap').remove();
            },
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

        $.ajax({url : ajaxurl, type : 'POST',
            data : {sorted_answer_ids : answers, action: 'tutor_quiz_answer_sorting'},
        });
    }



    /**
     * Deprecated, should remove
     * @todo: should remove this
     */

    $(document).on('click', '.add_quiz_to_post_btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var $modal = $('.tutor-modal-wrap');

        var quiz_for_post_id = $modal.attr('quiz-for-post-id');
        var data = $modal.find('input').serialize()+'&action=tutor_add_quiz_to_post&parent_post_id='+quiz_for_post_id;

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : data,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    $('[data-add-quiz-under="'+quiz_for_post_id+'"] .tutor-available-quizzes').html(data.data.output);
                    $('.tutor-modal-wrap').removeClass('show');
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    $(document).on('change keyup', '.tutor-quiz-modal-wrap .tutor-modal-search-input', function(e){
        e.preventDefault();

        var $that = $(this);
        var $modal = $('.tutor-modal-wrap');

        tutor_delay(function(){
            var search_terms = $that.val();
            var quiz_for_post_id = $modal.attr('quiz-for-post-id');

            $.ajax({
                url : ajaxurl,
                type : 'POST',
                data : {quiz_for_post_id : quiz_for_post_id, search_terms : search_terms, action: 'tutor_load_quiz_modal'},
                beforeSend: function () {
                    $modal.addClass('loading');
                },
                success: function (data) {
                    if (data.success){
                        $('.tutor-modal-wrap .modal-container').html(data.data.output);
                    }
                },
                complete: function () {
                    $modal.removeClass('loading');
                }
            });

        }, 1000)
    });

    var tutor_delay = (function(){
        var timer = 0;
        return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();

    $(document).on('click', '.tutor-quiz-delete-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var quiz_id = $that.closest('.added-quiz-item').attr('data-quiz-id');

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {quiz_id:quiz_id, action: 'remove_quiz_from_post'},
            success: function (data) {
                if (data.success){
                    $that.closest('.added-quiz-item').remove();
                }
            }
        });
    });

    /**
     * Add instructor modal
     */
    $(document).on('click', '.tutor-add-instructor-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var course_id = $('#post_ID').val();

        $.ajax({
            url : ajaxurl,
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
                url : ajaxurl,
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
        var data = $modal.find('input').serialize()+'&course_id='+course_id+'&action=tutor_add_instructors_to_course';

        $.ajax({
            url : ajaxurl,
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
            url : ajaxurl,
            type : 'POST',
            data : {course_id:course_id, instructor_id:instructor_id, action : 'detach_instructor_from_course'},
            success: function (data) {
                if (data.success){
                    $that.closest('.added-instructor-item').remove();
                }
            }
        });
    });

    $(document).on('click', '.tutor-option-media-upload-btn', function(e){
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
            $that.closest('.option-media-wrap').find('.option-media-preview').html('<img src="'+attachment.url+'" alt="" />');
            $that.closest('.option-media-wrap').find('input').val(attachment.id);
        });
        frame.open();
    });

    $(document).on('change', '.tutor_addons_list_item', function(e) {
        var $that = $(this);

        var isEnable = $that.prop('checked') ? 1 : 0;
        var addonFieldName = $that.attr('name');

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {isEnable:isEnable, addonFieldName:addonFieldName, action : 'addon_enable_disable'},
            success: function (data) {
                if (data.success){
                    //Success
                }
            }
        });
    });

    /**
     * Tutor Custom Select
     */

    function tutor_select(){
        var obj = {
            init : function(){
                $(document).on('click', '.tutor-select .tutor-select-option', function(e){
                    e.preventDefault();

                    var $that = $(this);
                    var $html = $that.html().trim();
                    $that.closest('.tutor-select').find('.select-header .lead-option').html($html);
                    $that.closest('.tutor-select').find('.select-header input.tutor_select_value_holder').val($that.attr('data-value')).trigger('change');
                    $that.closest('.tutor-select-options').hide();
                });
                $(document).on('click', '.tutor-select .select-header', function(e){
                    e.preventDefault();

                    var $that = $(this);
                    $that.closest('.tutor-select').find('.tutor-select-options').slideToggle();
                });

                this.setValue();
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



});

jQuery(document).ready(function($){
    'use strict';

    if (jQuery().select2){
        $('.dozent_select2').select2();
    }

    /**
     * Option Settings Nav Tab
     */
    $('.dozent-option-nav-tabs li a').click(function(e){
        e.preventDefault();
        var tab_page_id = $(this).attr('href');
        $('.option-nav-item').removeClass('current');
        $(this).closest('li').addClass('current');
        $('.dozent-option-nav-page').hide();
        $(tab_page_id).addClass('current-page').show();
    });

    $('#save_dozent_option').click(function (e) {
        e.preventDefault();
        $(this).closest('form').submit();
    });
    $('#dozent-option-form').submit(function(e){
        e.preventDefault();

        var $form = $(this);
        var data = $form.serialize();

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : data,
            beforeSend: function () {
                $form.find('.button').addClass('updating-message');
            },
            success: function (data) {
                //
            },
            complete: function () {
                $form.find('.button').removeClass('updating-message');
                window.location.reload();
            }
        });
    });

    $('.dozent-field-slider').each(function(){
        var $slider = $(this);
        var $input = $slider.closest('.dozent-field-type-slider').find('input[type="hidden"]');
        var $showVal = $slider.closest('.dozent-field-type-slider').find('.dozent-field-type-slider-value');
        var min = parseFloat($slider.closest('.dozent-field-type-slider').attr('data-min'));
        var max = parseFloat($slider.closest('.dozent-field-type-slider').attr('data-max'));

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


    /**
     * Course and lesson sorting
     */

    if (jQuery().sortable) {
        $(".course-contents").sortable({
            handle: ".course-move-handle",
            start: function (e, ui) {
                ui.placeholder.css('visibility', 'visible');
            },
            stop: function (e, ui) {
                dozent_sorting_topics_and_lesson();
            },
        });
        $(".dozent-lessions:not(.drop-lessons)").sortable({
            connectWith: ".dozent-lessions",
            items: "div.dozent-lesson",
            start: function (e, ui) {
                ui.placeholder.css('visibility', 'visible');
            },
            stop: function (e, ui) {
                dozent_sorting_topics_and_lesson();
            },
        });
    }
    function dozent_sorting_topics_and_lesson(){
        var topics = {};
        $('.dozent-topics-wrap').each(function(index, item){
            var $topic = $(this);
            var topics_id = parseInt($topic.attr('id').match(/\d+/)[0], 10);
            var lessons = {};

            $topic.find('.dozent-lesson').each(function(lessonIndex, lessonItem){
                var $lesson = $(this);
                var lesson_id = parseInt($lesson.attr('id').match(/\d+/)[0], 10);

                lessons[lessonIndex] = lesson_id;
            });
            topics[index] = { 'topic_id' : topics_id, 'lesson_ids' : lessons };

            //Hide drop element
            if ($topic.find('.dozent-lesson').length){
                $topic.find('.drop-lessons').hide();
            }else{
                $topic.find('.drop-lessons').show();
            }

        });
        $('#dozent_topics_lessons_sorting').val(JSON.stringify(topics));
        //console.log(topics);
    }

    $(document).on('click', '.topic-edit-icon', function (e) {
        e.preventDefault();
        $(this).closest('.dozent-topics-top').find('.dozent-topics-edit-form').slideToggle();
    });

    $(document).on('click', '.dozent-topics-edit-button', function(e){
        e.preventDefault();
        var $button = $(this);
        var $topic = $button.closest('.dozent-topics-wrap');
        var topics_id = parseInt($topic.attr('id').match(/\d+/)[0], 10);
        var topic_title = $button.closest('.dozent-topics-wrap').find('[name="topic_title"]').val();
        var topic_summery = $button.closest('.dozent-topics-wrap').find('[name="topic_summery"]').val();

        var data = {topic_title: topic_title, topic_summery : topic_summery, topic_id : topics_id, action: 'dozent_update_topic'};
        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : data,
            beforeSend: function () {
                $button.addClass('updating-message');
            },
            success: function (data) {
                if (data.success){
                    $button.closest('.dozent-topics-wrap').find('span.topic-inner-title').text(topic_title);
                    $button.closest('.dozent-topics-wrap').find('.dozent-topics-edit-form').slideUp();
                }
            },
            complete: function () {
                $button.removeClass('updating-message');
            }
        });
    });

    /**
     * Confirmation for deleting Topic
     */
    $(document).on('click', '.topic-delete-btn a', function(e){
        if ( ! confirm('Are you sure to delete?')){
            e.preventDefault();
        }
    });


    /**
     * Lesson Video
     */

    $(document).on('change', '.dozent_lesson_video_source', function(e){
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


    //dozent_video_poster_upload_btn
    $(document).on( 'click', '.dozent_video_poster_upload_btn',  function( event ){
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
            $that.closest('.dozent-video-poster-wrap').find('.video-poster-img').html('<img src="'+attachment.url+'" alt="" />');
            $that.closest('.dozent-video-poster-wrap').find('input').val(attachment.id);
        });
        // Finally, open the modal on click
        frame.open();
    });

    $(document).on('click', 'a.dozent-delete-attachment', function(e){
        e.preventDefault();
        $(this).closest('.dozent-added-attachment').remove();
    });

    $(document).on('click', '.dozentUploadAttachmentBtn', function(e){
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

                    var inputHtml = '<div class="dozent-added-attachment"><p> <a href="javascript:;" class="dozent-delete-attachment">×</a> <span> <a href="'+attachment.url+'">'+attachment.filename+'</a> </span> </p><input type="hidden" name="dozent_attachments[]" value="'+attachment.id+'"></div>';
                    $that.closest('.dozent-lesson-attachments-metabox').find('.dozent-added-attachments-wrap').append(inputHtml);
                }
            }
        });
        // Finally, open the modal on click
        frame.open();
    });


    /**
     * Open Sidebar Menu
     */


    if (dozent_data.open_dozent_admin_menu){
        var $adminMenu = $('#adminmenu');
        $adminMenu.find('[href="admin.php?page=dozent"]').closest('li.wp-has-submenu').addClass('wp-has-current-submenu');
        $adminMenu.find('[href="admin.php?page=dozent"]').closest('li.wp-has-submenu').find('a.wp-has-submenu').removeClass('wp-has-current-submenu').addClass('wp-has-current-submenu');
    }


    /**
     * Add question answer for quiz
     */

    $(document).on('change keyup paste', '.question_field_title', function(){
        var $that = $(this);
        $that.closest('.single-question-item').find('.dozent-question-item-head').find('.question-title').text($that.val());
    });

    $(document).on('change', '.question_type_field', function(){
        var $that = $(this);
        var question_type = $that.val();

        var option_text = $that.find('option[value="'+question_type+'"]').text();
        $that.closest('.single-question-item').find('.dozent-question-item-head').find('.question-type').text(option_text);

        var question_id = $that.closest('.single-question-item').attr('data-question-id');
        var data = {question_id: question_id, question_type : question_type, action: 'quiz_question_type_changed'};


        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : data,

            beforeSend: function () {
                $that.closest('.single-question-item').find('.dozent-loading-icon-wrap').addClass('updating-message');
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
                $that.closest('.single-question-item').find('.dozent-loading-icon-wrap').removeClass('updating-message');
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
                $that.removeClass('updated-message').addClass('updating-message');
            },
            success: function (data) {
                if (data.success){
                    $that.closest('.answer-entry-wrap').find('table.multi-answers-options').append(data.data.data_tr);

                    //Hide add answer button if true false and 2 option exists
                    if (question_type === 'true_false' && $that.closest('.answer-entry-wrap').find('tr.answer-option-row').length >= 2){

                        console.log(question_type, $that.closest('.answer-entry-wrap').find('tr.answer-option-row').length);


                        $that.closest('.add_answer_option_wrap').hide();
                    }else{
                        $that.closest('.add_answer_option_wrap').show();
                    }

                }
            },
            complete: function () {
                $that.removeClass('updating-message').addClass('updated-message');
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
            $title.addClass('dozent-input-text-error');
            return;
        }else{
            $title.removeClass('dozent-input-text-error');
        }

        var  data = {question_title : question_title, question_type:question_type, quiz_id : quiz_id, action: 'quiz_page_add_new_question' };
        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : data,
            beforeSend: function () {
                $that.removeClass('updated-message').addClass('updating-message');
            },
            success: function (data) {
                if (data.success){
                    $('.single-question-item .quiz-question-form-wrap').hide();
                    $('.dozent-quiz-questions-wrap').append(data.data.question_html);
                    $('.single-question-item:last-child .quiz-question-form-wrap').show();
                    $title.val('');
                }
            },
            complete: function () {
                $that.removeClass('updating-message').addClass('updated-message');
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
        
        var data = $(this).find("select, textarea, input").serialize()+'&action=update_dozent_question';
        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : data,
            beforeSend: function () {
                $that.find('.dozent-loading-icon-wrap').addClass('updating-message');
            },
            success: function (data) {
                if (data.success){

                }
            },
            complete: function () {
                $that.find('.dozent-loading-icon-wrap').removeClass('updating-message');
            }
        });
    });

    $(document).on('click', '.quiz-answer-option-delete-btn', function(e){
        e.preventDefault();
        var $that = $(this);
        var $closestTable = $that.closest('table');
        var $loadingIcon = $that.closest('.single-question-item').find('.dozent-loading-icon-wrap');

        var question_type = $that.closest('.quiz-question-form-wrap').find('select.question_type_field').val();
        var answer_option_id = $that.closest('tr').attr('data-answer-option-id');

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {answer_option_id:answer_option_id, action: 'quiz_delete_answer_option'},
            beforeSend: function () {
                $loadingIcon.addClass('updating-message');
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
                $loadingIcon.removeClass('updating-message');
            }
        });
    });

    $(document).on('click', '.question-action-btn.trash', function(e){
        e.preventDefault();

        var $that = $(this);
        var question_id = $that.closest('.single-question-item').attr('data-question-id');
        var $loadingIcon = $that.closest('.single-question-item').find('.dozent-loading-icon-wrap');

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {question_id:question_id, action: 'quiz_question_delete'},
            beforeSend: function () {
                $loadingIcon.addClass('updating-message');
            },
            success: function (data) {
                if (data.success){
                    $that.closest('.single-question-item').remove();
                }
            },
            complete: function () {
                $loadingIcon.removeClass('updating-message');
            }
        });
    });

    /**
     * Sort quiz questions
     */

    if (jQuery().sortable) {
        $(".dozent-quiz-questions-wrap").sortable({
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
        $('.dozent-modal-wrap').removeClass('show');
    });
    $(document).on('keyup', function(e){
        if (e.keyCode === 27){
            $('.dozent-modal-wrap').removeClass('show');
        }
    });
    $(document).on('click', '.dozent-add-quiz-btn', function(e){
        e.preventDefault();
        
        var $that = $(this);
        var quiz_for_post_id = $(this).closest('.dozent_add_quiz_wrap').attr('data-add-quiz-under');
        
        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {quiz_for_post_id : quiz_for_post_id, action: 'dozent_load_quiz_modal'},
            beforeSend: function () {
                $that.addClass('updating-message');
            },
            success: function (data) {
                $('.dozent-quiz-modal-wrap .modal-container').html(data.data.output);
                $('.dozent-quiz-modal-wrap').attr('quiz-for-post-id', quiz_for_post_id).addClass('show');
            },
            complete: function () {
                $that.removeClass('updating-message');
            }
        });
    });

    $(document).on('click', '.add_quiz_to_post_btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var $modal = $('.dozent-modal-wrap');

        var quiz_for_post_id = $modal.attr('quiz-for-post-id');
        var data = $modal.find('input').serialize()+'&action=dozent_add_quiz_to_post';

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : data,
            beforeSend: function () {
                $that.addClass('updating-message');
            },
            success: function (data) {
                if (data.success){
                    $('[data-add-quiz-under="'+quiz_for_post_id+'"] .dozent-available-quizzes').html(data.data.output);
                    $('.dozent-modal-wrap').removeClass('show');
                }
            },
            complete: function () {
                $that.removeClass('updating-message');
            }
        });
    });


    $(document).on('change keyup', '.dozent-quiz-modal-wrap .dozent-modal-search-input', function(e){
        e.preventDefault();

        var $that = $(this);
        var $modal = $('.dozent-modal-wrap');

        dozent_delay(function(){
            var search_terms = $that.val();
            var quiz_for_post_id = $modal.attr('quiz-for-post-id');

            $.ajax({
                url : ajaxurl,
                type : 'POST',
                data : {quiz_for_post_id : quiz_for_post_id, search_terms : search_terms, action: 'dozent_load_quiz_modal'},
                beforeSend: function () {
                    $modal.addClass('loading');
                },
                success: function (data) {
                    if (data.success){
                        $('.dozent-modal-wrap .modal-container').html(data.data.output);
                    }
                },
                complete: function () {
                    $modal.removeClass('loading');
                }
            });

        }, 1000)
    });

    var dozent_delay = (function(){
        var timer = 0;
        return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();

    $(document).on('click', '.dozent-quiz-delete-btn', function(e){
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
     * Add teacher modal
     */
    $(document).on('click', '.dozent-add-teacher-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var course_id = $('#post_ID').val();

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {course_id : course_id, action: 'dozent_load_teachers_modal'},
            beforeSend: function () {
                $that.addClass('updating-message');
            },
            success: function (data) {
                if (data.success){
                    $('.dozent-teachers-modal-wrap .modal-container').html(data.data.output);
                    $('.dozent-teachers-modal-wrap').addClass('show');
                }
            },
            complete: function () {
                $that.removeClass('updating-message');
            }
        });
    });

    $(document).on('change keyup', '.dozent-teachers-modal-wrap .dozent-modal-search-input', function(e){
        e.preventDefault();

        var $that = $(this);
        var $modal = $('.dozent-modal-wrap');

        dozent_delay(function(){
            var search_terms = $that.val();
            var course_id = $('#post_ID').val();

            $.ajax({
                url : ajaxurl,
                type : 'POST',
                data : {course_id : course_id, search_terms : search_terms, action: 'dozent_load_teachers_modal'},
                beforeSend: function () {
                    $modal.addClass('loading');
                },
                success: function (data) {
                    if (data.success){
                        $('.dozent-teachers-modal-wrap .modal-container').html(data.data.output);
                        $('.dozent-teachers-modal-wrap').addClass('show');
                    }
                },
                complete: function () {
                    $modal.removeClass('loading');
                }
            });

        }, 1000)
    });
    $(document).on('click', '.add_teacher_to_course_btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var $modal = $('.dozent-modal-wrap');

        var course_id = $('#post_ID').val();
        var data = $modal.find('input').serialize()+'&course_id='+course_id+'&action=dozent_add_teachers_to_course';

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : data,
            beforeSend: function () {
                $that.addClass('updating-message');
            },
            success: function (data) {
                if (data.success){
                    $('.dozent-course-available-teachers').html(data.data.output);
                    $('.dozent-modal-wrap').removeClass('show');
                }
            },
            complete: function () {
                $that.removeClass('updating-message');
            }
        });
    });

    $(document).on('click', '.dozent-teacher-delete-btn', function(e){
        e.preventDefault();

        var $that = $(this);

        var course_id = $('#post_ID').val();
        var teacher_id = $that.closest('.added-teacher-item').attr('data-teacher-id');

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {course_id:course_id, teacher_id:teacher_id, action : 'detach_teacher_from_course'},
            success: function (data) {
                if (data.success){
                    $that.closest('.added-teacher-item').remove();
                }
            }
        });
    });


});

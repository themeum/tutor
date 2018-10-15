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
                tutor_sorting_topics_and_lesson();
            },
        });
        $(".tutor-lessions:not(.drop-lessons)").sortable({
            connectWith: ".tutor-lessions",
            items: "div.tutor-lesson",
            start: function (e, ui) {
                ui.placeholder.css('visibility', 'visible');
            },
            stop: function (e, ui) {
                tutor_sorting_topics_and_lesson();
            },
        });
    }
    function tutor_sorting_topics_and_lesson(){
        var topics = {};
        $('.tutor-topics-wrap').each(function(index, item){
            var $topic = $(this);
            var topics_id = parseInt($topic.attr('id').match(/\d+/)[0], 10);
            var lessons = {};

            $topic.find('.tutor-lesson').each(function(lessonIndex, lessonItem){
                var $lesson = $(this);
                var lesson_id = parseInt($lesson.attr('id').match(/\d+/)[0], 10);

                lessons[lessonIndex] = lesson_id;
            });
            topics[index] = { 'topic_id' : topics_id, 'lesson_ids' : lessons };

            //Hide drop element
            if ($topic.find('.tutor-lesson').length){
                $topic.find('.drop-lessons').hide();
            }else{
                $topic.find('.drop-lessons').show();
            }

        });
        $('#tutor_topics_lessons_sorting').val(JSON.stringify(topics));
        //console.log(topics);
    }

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
                $button.addClass('updating-message');
            },
            success: function (data) {
                if (data.success){
                    $button.closest('.tutor-topics-wrap').find('span.topic-inner-title').text(topic_title);
                    $button.closest('.tutor-topics-wrap').find('.tutor-topics-edit-form').slideUp();
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


});

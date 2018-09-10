jQuery(document).ready(function($){
    'use strict';

    if (jQuery().select2){
        $('.select2').select2();
    }

    /**
     * Option Settings Nav Tab
     */
    $('.lms-option-nav-tabs li a').click(function(e){
        e.preventDefault();
        var tab_page_id = $(this).attr('href');
        $('.option-nav-item').removeClass('current');
        $(this).closest('li').addClass('current');
        $('.lms-option-nav-page').hide();
        $(tab_page_id).addClass('current-page').show();
    });

    $('#save_lms_option').click(function (e) {
        e.preventDefault();
        $(this).closest('form').submit();
    });
    $('#lms-option-form').submit(function(e){
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
            }
        });
    });

    $('.lms-field-slider').each(function(){
        var $slider = $(this);
        var $input = $slider.closest('.lms-field-type-slider').find('input[type="hidden"]');
        var $showVal = $slider.closest('.lms-field-type-slider').find('.lms-field-type-slider-value');
        var min = parseFloat($slider.closest('.lms-field-type-slider').attr('data-min'));
        var max = parseFloat($slider.closest('.lms-field-type-slider').attr('data-max'));

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

    $( ".course-contents" ).sortable({
        handle: ".course-move-handle",
        start: function(e, ui){
            ui.placeholder.css('visibility', 'visible');
        },
        stop: function(e, ui){
            lms_sorting_topics_and_lesson();
        },
    });
    $( ".lms-lessions:not(.drop-lessons)" ).sortable({
        connectWith: ".lms-lessions",
        items: "div.lms-lesson",
        start: function(e, ui){
            ui.placeholder.css('visibility', 'visible');
        },
        stop: function(e, ui){
            lms_sorting_topics_and_lesson();
        },
    });

    function lms_sorting_topics_and_lesson(){
        var topics = {};
        $('.lms-topics-wrap').each(function(index, item){
            var $topic = $(this);
            var topics_id = parseInt($topic.attr('id').match(/\d+/)[0], 10);
            var lessons = {};

            $topic.find('.lms-lesson').each(function(lessonIndex, lessonItem){
                var $lesson = $(this);
                var lesson_id = parseInt($lesson.attr('id').match(/\d+/)[0], 10);

                lessons[lessonIndex] = lesson_id;
            });
            topics[index] = { 'topic_id' : topics_id, 'lesson_ids' : lessons };

            //Hide drop element
            if ($topic.find('.lms-lesson').length){
                $topic.find('.drop-lessons').hide();
            }else{
                $topic.find('.drop-lessons').show();
            }

        });
        $('#lms_topics_lessons_sorting').val(JSON.stringify(topics));
        //console.log(topics);
    }

    $(document).on('click', '.topic-edit-icon', function (e) {
        e.preventDefault();
        $(this).closest('.lms-topics-top').find('.lms-topics-edit-form').slideToggle();
    });

    $(document).on('click', '.lms-topics-edit-button', function(e){
        e.preventDefault();
        var $button = $(this);
        var $topic = $button.closest('.lms-topics-wrap');
        var topics_id = parseInt($topic.attr('id').match(/\d+/)[0], 10);
        var topic_title = $button.closest('.lms-topics-wrap').find('[name="topic_title"]').val();
        var topic_summery = $button.closest('.lms-topics-wrap').find('[name="topic_summery"]').val();

        var data = {topic_title: topic_title, topic_summery : topic_summery, topic_id : topics_id, action: 'lms_update_topic'};
        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : data,
            beforeSend: function () {
                $button.addClass('updating-message');
            },
            success: function (data) {
                if (data.success){
                    $button.closest('.lms-topics-wrap').find('span.topic-inner-title').text(topic_title);
                    $button.closest('.lms-topics-wrap').find('.lms-topics-edit-form').slideUp();
                }
            },
            complete: function () {
                $button.removeClass('updating-message');
            }
        });
    });


});


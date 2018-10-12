jQuery(document).ready(function($){
    'use strict';

    $(document).on('change', '.tutor-course-filter-form', function(e){
        e.preventDefault();
        $(this).closest('form').submit();
    });

    const videoPlayer = {
        nonce_key : _tutorobject.nonce_key,
        track_player : function(){
            var that = this;
            if (typeof Plyr !== 'undefined') {
                const player = new Plyr('#tutorPlayer');

                player.on('ready', function(event){
                    const instance = event.detail.plyr;
                    if (_tutorobject.best_watch_time > 0) {
                        instance.media.currentTime = _tutorobject.best_watch_time;
                    }
                    that.sync_time(instance);
                });

                var tempTimeNow = 0;
                var intervalSeconds = 60; //Send to tutor backend about video playing time in this interval
                player.on('timeupdate', function(event){
                    const instance = event.detail.plyr;

                    var tempTimeNowInSec = (tempTimeNow / 4); //timeupdate firing 250ms interval
                    if (tempTimeNowInSec >= intervalSeconds){
                        that.sync_time(instance);
                        tempTimeNow = 0;
                    }
                    tempTimeNow++;
                });

                player.on('ended', function(event){
                    const instance = event.detail.plyr;

                    var data = {is_ended:true};
                    that.sync_time(instance, data)
                });
            }
        },
        sync_time: function(instance, options){
            /**
             * TUTOR is sending about video playback information to server.
             */
            var data = {action: 'sync_video_playback', currentTime : instance.currentTime, duration:instance.duration,  post_id : _tutorobject.post_id};
            data[this.nonce_key] = _tutorobject[this.nonce_key];

            var data_send = data;

            if(options){
                data_send = Object.assign(data, options);
            }
            $.post(_tutorobject.ajaxurl, data_send);
        },
        init: function(){
            this.track_player();
        }
    };

    /**
     * Fire TUTOR video
     * @since v.1.0.0
     */
    videoPlayer.init();

    $(document).on('change keyup paste', '.tutor_user_name', function(){
        $(this).val(tutor_slugify($(this).val()));
    });

    function tutor_slugify(text) {
        return text.toString().toLowerCase()
            .replace(/\s+/g, '-')           // Replace spaces with -
            .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
            .replace(/\-\-+/g, '-')         // Replace multiple - with single -
            .replace(/^-+/, '')             // Trim - from start of text
            .replace(/-+$/, '');            // Trim - from end of text
    }

    /**
     * Hover tutor rating and set value
     */
    $(document).on('hover', '.tutor-ratings-wrap i', function(){
        $(this).closest('.tutor-ratings-wrap').find('i').removeClass('icon-star').addClass('icon-star-empty');
        var currentRateValue = $(this).attr('data-rating-value');
        for (var i = 1; i<= currentRateValue; i++){
            $(this).closest('.tutor-ratings-wrap').find('i[data-rating-value="'+i+'"]').removeClass('icon-star-empty').addClass('icon-star');
        }
    });

    $(document).on('click', '.tutor-ratings-wrap i', function(){
        var rating = $(this).attr('data-rating-value');
        var course_id = $('input[name="tutor_course_id"]').val();
        var data = {course_id : course_id, rating:rating, action: 'tutor_place_rating' };

        $.post(_tutorobject.ajaxurl, data);
    });


    $(document).on('click', '.tutor_submit_review_btn', function (e) {
        e.preventDefault();
        var review = $(this).closest('form').find('textarea[name="review"]').val();
        review = review.trim();

        var course_id = $('input[name="tutor_course_id"]').val();
        var data = {course_id : course_id, review:review, action: 'tutor_place_rating' };

        if (review) {
            $.ajax({
                url: _tutorobject.ajaxurl,
                type: 'POST',
                data: data,
                beforeSend: function () {
                    //
                },
                success: function (data) {
                    //
                },
                complete: function () {
                    //
                }
            });
        }
    });

    $(document).on('click', '.write-course-review-link-btn', function(e){
        e.preventDefault();
        $(this).closest('form').find('.tutor-write-review-box').slideToggle();
    });

    $(document).on('click', '.tutor-ask-question-btn', function(e){
        e.preventDefault();
        $('.tutor-add-question-wrap').slideToggle();
    });
    $(document).on('click', '.tutor_question_cancel', function(e){
        e.preventDefault();
        $('.tutor-add-question-wrap').toggle();
    });


    $(document).on('submit', '#tutor-ask-question-form', function(e){
        e.preventDefault();

        var $form = $(this);
        var data = $(this).serialize()+'&action=tutor_ask_question';

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $form.find('.tutor_ask_question_btn').addClass('updating-icon');
            },
            success: function (data) {
                if (data.success){
                    $('.tutor-add-question-wrap').hide();
                    window.location.reload();
                }
            },
            complete: function () {
                $form.find('.tutor_ask_question_btn').removeClass('updating-icon');
            }
        });
    });


    $(document).on('submit', '.tutor-add-answer-form', function(e){
        e.preventDefault();

        var $form = $(this);
        var data = $(this).serialize()+'&action=tutor_add_answer';

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $form.find('.tutor_add_answer_btn').addClass('updating-icon');
            },
            success: function (data) {
                if (data.success){
                    window.location.reload();
                }
            },
            complete: function () {
                $form.find('.tutor_add_answer_btn').removeClass('updating-icon');
            }
        });
    });

    $(document).on('focus', '.tutor_add_answer_textarea', function(e){
        e.preventDefault();

        var question_id = $(this).closest('.tutor_add_answer_wrap').attr('data-question-id');
        var conf = {
            tinymce: {
                wpautop:true,
                //plugins : 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
                toolbar1: 'bold italic underline bullist strikethrough numlist  blockquote  alignleft aligncenter alignright undo redo link unlink' +
                '  spellchecker fullscreen'
            },
        };
        wp.editor.initialize('tutor_answer_'+question_id, conf);
    });

    $(document).on('click', '.tutor_cancel_wp_editor', function(e){
        e.preventDefault();
        $(this).closest('.tutor_wp_editor_wrap').toggle();
        $(this).closest('.tutor_add_answer_wrap').find('.tutor_wp_editor_show_btn_wrap').toggle();
        var question_id = $(this).closest('.tutor_add_answer_wrap').attr('data-question-id');
        wp.editor.remove('tutor_answer_'+question_id);
    });

    $(document).on('click', '.tutor_wp_editor_show_btn', function(e){
        e.preventDefault();
        $(this).closest('.tutor_add_answer_wrap').find('.tutor_wp_editor_wrap').toggle();
        $(this).closest('.tutor_wp_editor_show_btn_wrap').toggle();
    });

    //tutor_wp_editor_show_btn

});


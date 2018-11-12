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
                toolbar1: 'bold italic underline bullist strikethrough numlist  blockquote  alignleft aligncenter alignright undo redo link unlink spellchecker fullscreen'
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

    /**
     * Quiz attempt
     */

    var $tutor_quiz_time_update = $('#tutor-quiz-time-update');
    var attempt_settings = null;
    if ($tutor_quiz_time_update.length){
        attempt_settings = JSON.parse($tutor_quiz_time_update.attr('data-attempt-settings'));
        var attempt_meta = JSON.parse($tutor_quiz_time_update.attr('data-attempt-meta'));

        var countDownDate = new Date(attempt_settings.quiz_started_at).getTime() + (attempt_meta.time_limit_seconds * 1000);
        var time_now = new Date(attempt_meta.date_time_now).getTime();

        var tutor_quiz_interval = setInterval(function() {
            var distance = countDownDate - time_now;

            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            var countdown_human = '';

            if (days){
                countdown_human += days + "d ";
            }
            if (hours){
                countdown_human += hours + "h ";
            }
            if (minutes){
                countdown_human += minutes + "m ";
            }
            if (seconds){
                countdown_human += seconds + "s ";
            }

            if (distance < 0) {
                clearInterval(tutor_quiz_interval);
                countdown_human = "EXPIRED";
                //Set the quiz attempt to timeout in ajax

                var quiz_id = $('#tutor_quiz_id').val();
                var tutor_quiz_remaining_time_secs = $('#tutor_quiz_remaining_time_secs').val();
                var quiz_timeout_data = { quiz_id : quiz_id,  action : 'tutor_quiz_timeout' };

                $.ajax({
                    url: _tutorobject.ajaxurl,
                    type: 'POST',
                    data: quiz_timeout_data,
                    success: function (data) {
                        if (data.success){
                            window.location.reload(true);
                        }
                    },
                    complete: function () {
                        $('#tutor-quiz-body').html('');
                        window.location.reload(true);
                    }
                });


            }
            time_now = time_now + 1000;
            $tutor_quiz_time_update.html(countdown_human);
        }, 1000);
    }

    // tutor course content accordion
    var $tutor_course_title = $('.tutor-course-title');

    $tutor_course_title.on('click', function () {
        var $lesson = $(this).siblings('.tutor-course-lessons');
        $lesson.slideToggle();
    });

});


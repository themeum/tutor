jQuery(document).ready(function($){
    'use strict';

    $(document).on('change', '.dozent-course-filter-form', function(e){
        e.preventDefault();
        $(this).closest('form').submit();
    });

    const videoPlayer = {
        nonce_key : _dozentobject.nonce_key,
        track_player : function(){
            var that = this;
            if (typeof Plyr !== 'undefined') {
                const player = new Plyr('#dozentPlayer');

                player.on('ready', function(event){
                    const instance = event.detail.plyr;
                    if (_dozentobject.best_watch_time > 0) {
                        instance.media.currentTime = _dozentobject.best_watch_time;
                    }
                    that.sync_time(instance);
                });

                var tempTimeNow = 0;
                var intervalSeconds = 60; //Send to dozent backend about video playing time in this interval
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
             * DOZENT is sending about video playback information to server.
             */
            var data = {action: 'sync_video_playback', currentTime : instance.currentTime, duration:instance.duration,  post_id : _dozentobject.post_id};
            data[this.nonce_key] = _dozentobject[this.nonce_key];

            var data_send = data;

            if(options){
                data_send = Object.assign(data, options);
            }
            $.post(_dozentobject.ajaxurl, data_send);
        },
        init: function(){
            this.track_player();
        }
    };

    /**
     * Fire DOZENT video
     * @since v.1.0.0
     */
    videoPlayer.init();

    $(document).on('change keyup paste', '.dozent_user_name', function(){
        $(this).val(dozent_slugify($(this).val()));
    });

    function dozent_slugify(text) {
        return text.toString().toLowerCase()
            .replace(/\s+/g, '-')           // Replace spaces with -
            .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
            .replace(/\-\-+/g, '-')         // Replace multiple - with single -
            .replace(/^-+/, '')             // Trim - from start of text
            .replace(/-+$/, '');            // Trim - from end of text
    }

    /**
     * Hover dozent rating and set value
     */
    $(document).on('hover', '.dozent-ratings-wrap i', function(){
        $(this).closest('.dozent-ratings-wrap').find('i').removeClass('dozent-icon-star-full').addClass('dozent-icon-star-line');
        var currentRateValue = $(this).attr('data-rating-value');
        for (var i = 1; i<= currentRateValue; i++){
            $(this).closest('.dozent-ratings-wrap').find('i[data-rating-value="'+i+'"]').removeClass('dozent-icon-star-line').addClass('dozent-icon-star-full');
        }
    });

    $(document).on('click', '.dozent-ratings-wrap i', function(){
        var rating = $(this).attr('data-rating-value');
        var course_id = $('input[name="dozent_course_id"]').val();
        var data = {course_id : course_id, rating:rating, action: 'dozent_place_rating' };

        $.post(_dozentobject.ajaxurl, data);
    });


    $(document).on('click', '.dozent_submit_review_btn', function (e) {
        e.preventDefault();
        var $that = $(this);
        var review = $(this).closest('form').find('textarea[name="review"]').val();
        review = review.trim();

        var course_id = $('input[name="dozent_course_id"]').val();
        var data = {course_id : course_id, review:review, action: 'dozent_place_rating' };

        if (review) {
            $.ajax({
                url: _dozentobject.ajaxurl,
                type: 'POST',
                data: data,
                beforeSend: function () {
                    $that.addClass('updating-icon');
                },
                success: function (data) {
                    var review_id = data.data.review_id;
                    var review = data.data.review;
                    $('.dozent-review-'+review_id+' .review-content').html(review);
                },
                complete: function () {
                    $('.dozent-write-review-form').slideUp();
                    $that.removeClass('updating-icon');
                }
            });
        }
    });

    $(document).on('click', '.write-course-review-link-btn', function(e){
        e.preventDefault();
        $(this).siblings('.dozent-write-review-form').slideToggle();
    });

    $(document).on('click', '.dozent-ask-question-btn', function(e){
        e.preventDefault();
        $('.dozent-add-question-wrap').slideToggle();
    });
    $(document).on('click', '.dozent_question_cancel', function(e){
        e.preventDefault();
        $('.dozent-add-question-wrap').toggle();
    });


    $(document).on('submit', '#dozent-ask-question-form', function(e){
        e.preventDefault();

        var $form = $(this);
        var data = $(this).serialize()+'&action=dozent_ask_question';

        $.ajax({
            url: _dozentobject.ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $form.find('.dozent_ask_question_btn').addClass('updating-icon');
            },
            success: function (data) {
                if (data.success){
                    $('.dozent-add-question-wrap').hide();
                    window.location.reload();
                }
            },
            complete: function () {
                $form.find('.dozent_ask_question_btn').removeClass('updating-icon');
            }
        });
    });


    $(document).on('submit', '.dozent-add-answer-form', function(e){
        e.preventDefault();

        var $form = $(this);
        var data = $(this).serialize()+'&action=dozent_add_answer';

        $.ajax({
            url: _dozentobject.ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $form.find('.dozent_add_answer_btn').addClass('updating-icon');
            },
            success: function (data) {
                if (data.success){
                    window.location.reload();
                }
            },
            complete: function () {
                $form.find('.dozent_add_answer_btn').removeClass('updating-icon');
            }
        });
    });

    $(document).on('focus', '.dozent_add_answer_textarea', function(e){
        e.preventDefault();

        var question_id = $(this).closest('.dozent_add_answer_wrap').attr('data-question-id');
        var conf = {
            tinymce: {
                wpautop:true,
                //plugins : 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
                toolbar1: 'bold italic underline bullist strikethrough numlist  blockquote  alignleft aligncenter alignright undo redo link unlink spellchecker fullscreen'
            },
        };
        wp.editor.initialize('dozent_answer_'+question_id, conf);
    });

    $(document).on('click', '.dozent_cancel_wp_editor', function(e){
        e.preventDefault();
        $(this).closest('.dozent_wp_editor_wrap').toggle();
        $(this).closest('.dozent_add_answer_wrap').find('.dozent_wp_editor_show_btn_wrap').toggle();
        var question_id = $(this).closest('.dozent_add_answer_wrap').attr('data-question-id');
        wp.editor.remove('dozent_answer_'+question_id);
    });

    $(document).on('click', '.dozent_wp_editor_show_btn', function(e){
        e.preventDefault();
        $(this).closest('.dozent_add_answer_wrap').find('.dozent_wp_editor_wrap').toggle();
        $(this).closest('.dozent_wp_editor_show_btn_wrap').toggle();
    });

    /**
     * Quiz attempt
     */

    var $dozent_quiz_time_update = $('#dozent-quiz-time-update');
    var attempt_settings = null;
    if ($dozent_quiz_time_update.length){
        attempt_settings = JSON.parse($dozent_quiz_time_update.attr('data-attempt-settings'));
        var attempt_meta = JSON.parse($dozent_quiz_time_update.attr('data-attempt-meta'));

        var countDownDate = new Date(attempt_settings.quiz_started_at).getTime() + (attempt_meta.time_limit_seconds * 1000);
        var time_now = new Date(attempt_meta.date_time_now).getTime();

        var dozent_quiz_interval = setInterval(function() {
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
                clearInterval(dozent_quiz_interval);
                countdown_human = "EXPIRED";
                //Set the quiz attempt to timeout in ajax

                var quiz_id = $('#dozent_quiz_id').val();
                var dozent_quiz_remaining_time_secs = $('#dozent_quiz_remaining_time_secs').val();
                var quiz_timeout_data = { quiz_id : quiz_id,  action : 'dozent_quiz_timeout' };

                $.ajax({
                    url: _dozentobject.ajaxurl,
                    type: 'POST',
                    data: quiz_timeout_data,
                    success: function (data) {
                        if (data.success){
                            window.location.reload(true);
                        }
                    },
                    complete: function () {
                        $('#dozent-quiz-body').html('');
                        window.location.reload(true);
                    }
                });
            }
            time_now = time_now + 1000;
            $dozent_quiz_time_update.html(countdown_human);
        }, 1000);
    }

    // dozent course content accordion
    $('.dozent-course-topic.dozent-active').find('.dozent-course-lessons').slideDown();
    $('.dozent-course-title').on('click', function () {
        var lesson = $(this).siblings('.dozent-course-lessons');
        $(this).closest('.dozent-course-topic').toggleClass('dozent-active');
        lesson.slideToggle();
    });

    $('.dozent-topics-title').on('click', function () {
        $(this).siblings('.dozent-topics-summery').slideToggle();
    });

    $(document).on('click', '.dozent-course-wishlist-btn', function (e) {
        e.preventDefault();

        var $that = $(this);
        var course_id = $that.attr('data-course-id');


        $.ajax({
            url: _dozentobject.ajaxurl,
            type: 'POST',
            data: {course_id : course_id, 'action': 'dozent_course_add_to_wishlist'},
            beforeSend: function () {
                $that.addClass('updating-icon');
            },
            success: function (data) {
                if (data.success){
                    if (data.data.status === 'added'){
                        $that.addClass('has-wish-listed');
                    }else{
                        $that.removeClass('has-wish-listed');
                    }
                }else{
                    window.location = data.data.redirect_to;

                }
            },
            complete: function () {
                $that.removeClass('updating-icon');
            }
        });

    });



});


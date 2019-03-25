jQuery(document).ready(function($){
    'use strict';

    $(document).on('change', '.tutor-course-filter-form', function(e){
        e.preventDefault();
        $(this).closest('form').submit();
    });

    const videoPlayer = {
        nonce_key : _tutorobject.nonce_key,
        video_track_data : $('#tutor_video_tracking_information').val(),
        track_player : function(){
            var that = this;

            var video_data = this.video_track_data ? JSON.parse(this.video_track_data) : {};

            if (typeof Plyr !== 'undefined') {
                const player = new Plyr('#tutorPlayer');

                player.on('ready', function(event){
                    const instance = event.detail.plyr;
                    if (video_data.best_watch_time > 0) {
                        instance.media.currentTime = video_data.best_watch_time;
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
            var video_data = this.video_track_data ? JSON.parse(this.video_track_data) : {};
            var data = {action: 'sync_video_playback', currentTime : instance.currentTime, duration:instance.duration,  post_id : video_data.post_id};
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
    if ($('#tutorPlayer').length){
        videoPlayer.init();
    }

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
    $(document).on('hover', '.tutor-write-review-box .tutor-star-rating-group i', function(){
        $(this).closest('.tutor-star-rating-group').find('i').removeClass('tutor-icon-star-full').addClass('tutor-icon-star-line');
        var currentRateValue = $(this).attr('data-rating-value');
        for (var i = 1; i<= currentRateValue; i++){
            $(this).closest('.tutor-star-rating-group').find('i[data-rating-value="'+i+'"]').removeClass('tutor-icon-star-line').addClass('tutor-icon-star-full');
        }
        $(this).closest('.tutor-star-rating-group').find('input[name="tutor_rating_gen_input"]').val(currentRateValue);
    });

    $(document).on('click', '.tutor-star-rating-group i', function(){
        var rating = $(this).attr('data-rating-value');
        $(this).closest('.tutor-star-rating-group').find('input[name="tutor_rating_gen_input"]').val(rating);
    });

    $(document).on('click', '.tutor_submit_review_btn', function (e) {
        e.preventDefault();
        var $that = $(this);
        var rating = $that.closest('form').find('input[name="tutor_rating_gen_input"]').val();
        var review = $that.closest('form').find('textarea[name="review"]').val();
        review = review.trim();

        var course_id = $('input[name="tutor_course_id"]').val();
        var data = {course_id : course_id, rating : rating, review:review, action: 'tutor_place_rating' };

        if (review) {
            $.ajax({
                url: _tutorobject.ajaxurl,
                type: 'POST',
                data: data,
                beforeSend: function () {
                    $that.addClass('updating-icon');
                },
                success: function (data) {
                    var review_id = data.data.review_id;
                    var review = data.data.review;
                    $('.tutor-review-'+review_id+' .review-content').html(review);
                },
                complete: function () {
                    $('.tutor-write-review-form').slideUp();
                    $that.removeClass('updating-icon');
                }
            });
        }
    });

    $(document).on('click', '.write-course-review-link-btn', function(e){
        e.preventDefault();
        $(this).siblings('.tutor-write-review-form').slideToggle();
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

        if (attempt_meta.time_limit.time_limit_seconds === 0) {
            //No time Zero limit for
            return;
        }

        var countDownDate = new Date(attempt_settings.attempt_started_at).getTime() + (attempt_meta.time_limit.time_limit_seconds * 1000);
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

                if (_tutorobject.options.quiz_when_time_expires === 'autosubmit'){
                    /**
                     * Auto Submit
                     */
                    $('form#tutor-answering-quiz').submit();

                } else if(_tutorobject.options.quiz_when_time_expires === 'autoabandon'){
                    /**
                     *
                     * @type {jQuery}
                     *
                     * Current attempt will be cancel with attempt status attempt_timeout
                     */

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

            }
            time_now = time_now + 1000;
            $tutor_quiz_time_update.html(countdown_human);
        }, 1000);
    }

    var $quiz_start_form = $('#tutor-quiz-body form#tutor-start-quiz');
    if ($quiz_start_form.length){
        if (_tutorobject.quiz_options.quiz_auto_start === '1'){
            $quiz_start_form.submit();
        }
    }

    // tutor course content accordion
    $('.tutor-course-topic.tutor-active').find('.tutor-course-lessons').slideDown();
    $('.tutor-course-title').on('click', function () {
        var lesson = $(this).siblings('.tutor-course-lessons');
        $(this).closest('.tutor-course-topic').toggleClass('tutor-active');
        lesson.slideToggle();
    });

    $('.tutor-topics-title').on('click', function () {
        $(this).siblings('.tutor-topics-summery').slideToggle();
    });

    $(document).on('click', '.tutor-course-wishlist-btn', function (e) {
        e.preventDefault();

        var $that = $(this);
        var course_id = $that.attr('data-course-id');

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: {course_id : course_id, 'action': 'tutor_course_add_to_wishlist'},
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

    $(document).on('click', '.tutor-single-lesson-a', function (e) {
        e.preventDefault();

        var $that = $(this);
        var lesson_id = $that.attr('data-lesson-id');
        var $wrap = $('#tutor-single-entry-content');

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: {lesson_id : lesson_id, 'action': 'tutor_render_lesson_content'},
            beforeSend: function () {
                var page_title = $that.find('.lesson_title').text();
                $('head title').text(page_title);
                window.history.pushState('obj', page_title, $that.attr('href'));
                $wrap.addClass('loading-lesson');
                $('.tutor-single-lesson-items').removeClass('active');
                $that.closest('.tutor-single-lesson-items').addClass('active');
            },
            success: function (data) {
                $wrap.html(data.data.html);
                videoPlayer.init();
            },
            complete: function () {
                $wrap.removeClass('loading-lesson');
            }
        });
    });

    $(document).on('click', '.sidebar-single-quiz-a', function (e) {
        e.preventDefault();

        var $that = $(this);
        var quiz_id = $that.attr('data-quiz-id');
        var page_title = $that.find('.lesson_title').text();
        var $wrap = $('#tutor-single-entry-content');

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: {quiz_id : quiz_id, 'action': 'tutor_render_quiz_content'},
            beforeSend: function () {
                $('head title').text(page_title);
                window.history.pushState('obj', page_title, $that.attr('href'));
                $wrap.addClass('loading-lesson');
                $('.tutor-single-lesson-items').removeClass('active');
                $that.closest('.tutor-single-lesson-items').addClass('active');
            },
            success: function (data) {
                $wrap.html(data.data.html);
            },
            complete: function () {
                $wrap.removeClass('loading-lesson');
            }
        });


    });

    /**
     * @date 05 Feb, 2019
     */

    $(document).on('click', '.tutor-lesson-sidebar-hide-bar', function(e){
        e.preventDefault();
        $('.tutor-lesson-sidebar').toggle();
    });

    $(document).on('click', '.tutor-tabs-btn-group a', function (e) {
        e.preventDefault();
        var $that = $(this);

        var tabSelector = $that.attr('href');
        $('.tutor-lesson-sidebar-tab-item').hide();
        $(tabSelector).show();

        $('.tutor-tabs-btn-group a').removeClass('active');
        $that.addClass('active');
    });
    /**
     * @date 18 Feb, 2019
     * @since v.1.0.0
     */

    if (jQuery().sortable) {
        $(".tutor-quiz-answers-wrap").sortable({
            handle: ".answer-sorting-bar",
            start: function (e, ui) {
                ui.placeholder.css('visibility', 'visible');
            },
            stop: function (e, ui) {

                //Sorting Stopped...
            },
        }).disableSelection();;


        $( ".quiz-draggable-rand-answers, .quiz-answer-matching-droppable" ).sortable({
            connectWith: ".quiz-answer-matching-droppable",
            placeholder: "drop-hover"

        }).disableSelection();
    }

    /**
     * Quiz view
     * @date 22 Feb, 2019
     * @since v.1.0.0
     */

    $(document).on('click', '.tutor-quiz-answer-next-btn', function (e) {
        e.preventDefault();
        var $that = $(this);
        var question_id = parseInt($that.closest('.quiz-attempt-single-question').attr('id').match(/\d+/)[0], 10);

        var next_question_id = $that.closest('.quiz-attempt-single-question').attr('data-next-question-id');

        if (next_question_id) {
            var $nextQuestion = $(next_question_id);
            if ($nextQuestion && $nextQuestion.length) {
                $('.quiz-attempt-single-question').hide();
                $nextQuestion.show();

                /**
                 * If pagination exists, set active class
                 */

                if ($('.tutor-quiz-questions-pagination').length){
                    $('.tutor-quiz-question-paginate-item').removeClass('active');
                    $('.tutor-quiz-questions-pagination a[href="'+next_question_id+'"]').addClass('active');
                }

            }
        }
    });
    $(document).on('click', '.tutor-quiz-question-paginate-item', function (e) {
        e.preventDefault();
        var $that = $(this);
        var $question = $($that.attr('href'));
        $('.quiz-attempt-single-question').hide();
        $question.show();

        //Active Class
        $('.tutor-quiz-question-paginate-item').removeClass('active');
        $that.addClass('active');
    });

    /**
     * Limit Short Answer Question Type
     */
    $(document).on('keyup', 'textarea.question_type_short_answer', function (e) {
        var $that = $(this);
        var value = $that.val();
        var limit = _tutorobject.quiz_options.short_answer_characters_limit;
        var remaining = limit - value.length;

        if (remaining < 1){
            $that.val(value.substr(0, limit));
            remaining = 0;
        }
        $that.closest('.tutor-quiz-answers-wrap').find('.characters_remaining').html(remaining);
    });

    /**
     * Add to cart in guest mode, show login form
     *
     * @since v.1.0.4
     */

    $(document).on('submit click', '.cart-required-login, .cart-required-login a, .cart-required-login form', function (e) {
        e.preventDefault();

        $('.tutor-cart-box-login-form').fadeIn(100);
    });

    $('.tutor-popup-form-close').on('click', function () {
        $('.tutor-cart-box-login-form').fadeOut(100);
    });

    $(document).on('keyup', function (e) {
        if (e.keyCode === 27) {
            $('.tutor-cart-box-login-form').fadeOut(100);
        }
    });
    /**
     * Share Link enable
     *
     * @since v.1.0.4
     */
    if($.fn.ShareLink){
        var $social_share_wrap = $('.tutor-social-share-wrap');
        if ($social_share_wrap.length) {
            var share_config = JSON.parse($social_share_wrap.attr('data-social-share-config'));

            $('.tutor_share').ShareLink({
                title: share_config.title,
                text: share_config.text,
                image: share_config.image,
                class_prefix: 's_',
                width: 640,
                height: 480,
            });
        }
    }

});
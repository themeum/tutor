jQuery(document).ready(function($){
    'use strict';

    /**
     * Initiate Select2
     * @since v.1.3.4
     */
    if (jQuery().select2){
        $('.tutor_select2').select2({
            escapeMarkup : function(markup) {
                return markup;
            }
        });
    }
    //END: select2


    /*!
     * jQuery UI Touch Punch 0.2.3
     *
     * Copyright 2011–2014, Dave Furfero
     * Dual licensed under the MIT or GPL Version 2 licenses.
     *
     * Depends:
     *  jquery.ui.widget.js
     *  jquery.ui.mouse.js
     */
    !function(a){function f(a,b){if(!(a.originalEvent.touches.length>1)){a.preventDefault();var c=a.originalEvent.changedTouches[0],d=document.createEvent("MouseEvents");d.initMouseEvent(b,!0,!0,window,1,c.screenX,c.screenY,c.clientX,c.clientY,!1,!1,!1,!1,0,null),a.target.dispatchEvent(d)}}if(a.support.touch="ontouchend"in document,a.support.touch){var e,b=a.ui.mouse.prototype,c=b._mouseInit,d=b._mouseDestroy;b._touchStart=function(a){var b=this;!e&&b._mouseCapture(a.originalEvent.changedTouches[0])&&(e=!0,b._touchMoved=!1,f(a,"mouseover"),f(a,"mousemove"),f(a,"mousedown"))},b._touchMove=function(a){e&&(this._touchMoved=!0,f(a,"mousemove"))},b._touchEnd=function(a){e&&(f(a,"mouseup"),f(a,"mouseout"),this._touchMoved||f(a,"click"),e=!1)},b._mouseInit=function(){var b=this;b.element.bind({touchstart:a.proxy(b,"_touchStart"),touchmove:a.proxy(b,"_touchMove"),touchend:a.proxy(b,"_touchEnd")}),c.call(b)},b._mouseDestroy=function(){var b=this;b.element.unbind({touchstart:a.proxy(b,"_touchStart"),touchmove:a.proxy(b,"_touchMove"),touchend:a.proxy(b,"_touchEnd")}),d.call(b)}}}(jQuery);

    /**
     * END jQuery UI Touch Punch
     */


    $(document).on('change', '.tutor-course-filter-form', function(e){
        e.preventDefault();
        $(this).closest('form').submit();
    });

    const videoPlayer = {
        ajaxurl: _tutorobject.ajaxurl,
        nonce_key: _tutorobject.nonce_key,
        video_data: function() {
            const video_track_data = $('#tutor_video_tracking_information').val();
            return video_track_data ? JSON.parse(video_track_data) : {};
        },
        track_player: function() {
            const that = this;
            if (typeof Plyr !== 'undefined') {
                const player = new Plyr('#tutorPlayer');
                const video_data = that.video_data();
                player.on('ready', function(event){
                    const instance = event.detail.plyr;
                    const { best_watch_time } = video_data;
                    if (best_watch_time > 0 && instance.duration > Math.round(best_watch_time)) {
                        instance.media.currentTime = best_watch_time;
                    }
                    that.sync_time(instance);
                });

                let tempTimeNow = 0;
                let intervalSeconds = 30; //Send to tutor backend about video playing time in this interval
                player.on('timeupdate', function(event) {
                    const instance = event.detail.plyr;
                    const tempTimeNowInSec = (tempTimeNow / 4); //timeupdate firing 250ms interval
                    if (tempTimeNowInSec >= intervalSeconds){
                        that.sync_time(instance);
                        tempTimeNow = 0;
                    }
                    tempTimeNow++;
                });

                player.on('ended', function(event) {
                    const video_data = that.video_data();
                    const instance = event.detail.plyr;
                    const data = {is_ended:true};
                    that.sync_time(instance, data);
                    if(video_data.autoload_next_course_content) {
                        that.autoload_content();
                    }
                });
            }
        },
        sync_time: function(instance, options) {
            const post_id = this.video_data().post_id;
            //TUTOR is sending about video playback information to server.
            let data = {action: 'sync_video_playback', currentTime: instance.currentTime, duration:instance.duration, post_id};
            data[this.nonce_key] = _tutorobject[this.nonce_key];
            let data_send = data;
            if(options){
                data_send = Object.assign(data, options);
            }
            $.post(this.ajaxurl, data_send);
        },
        autoload_content: function() {
            const post_id = this.video_data().post_id;
            const data = {action: 'autoload_next_course_content', post_id};
            data[this.nonce_key] = _tutorobject[this.nonce_key];
            $.post(this.ajaxurl, data).done(function(response) {
                if(response.success && response.data.next_url) {
                    location.href = response.data.next_url;
                }
            });
        },
        init: function() {
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
                    location.reload();
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

        if (attempt_meta.time_limit.time_limit_seconds > 0) {
            //No time Zero limit for
            var countDownDate = new Date(attempt_settings.attempt_started_at).getTime() + (attempt_meta.time_limit.time_limit_seconds * 1000);
            var time_now = new Date(attempt_meta.date_time_now).getTime();

            var tutor_quiz_interval = setInterval(function () {
                var distance = countDownDate - time_now;

                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                var countdown_human = '';

                if (days) {
                    countdown_human += days + "d ";
                }
                if (hours) {
                    countdown_human += hours + "h ";
                }
                if (minutes) {
                    countdown_human += minutes + "m ";
                }
                if (seconds) {
                    countdown_human += seconds + "s ";
                }

                if (distance < 0) {
                    clearInterval(tutor_quiz_interval);
                    countdown_human = "EXPIRED";
                    //Set the quiz attempt to timeout in ajax

                    if (_tutorobject.quiz_options.quiz_when_time_expires === 'autosubmit') {
                        /**
                         * Auto Submit
                         */
                        $('form#tutor-answering-quiz').submit();

                    } else if (_tutorobject.quiz_options.quiz_when_time_expires === 'autoabandon') {
                        /**
                         *
                         * @type {jQuery}
                         *
                         * Current attempt will be cancel with attempt status attempt_timeout
                         */

                        var quiz_id = $('#tutor_quiz_id').val();
                        var tutor_quiz_remaining_time_secs = $('#tutor_quiz_remaining_time_secs').val();
                        var quiz_timeout_data = {quiz_id: quiz_id, action: 'tutor_quiz_timeout'};

                        $.ajax({
                            url: _tutorobject.ajaxurl,
                            type: 'POST',
                            data: quiz_timeout_data,
                            success: function (data) {
                                if (data.success) {
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
        }else{
            $tutor_quiz_time_update.closest('.time-remaining').remove();
        }
    }

    var $quiz_start_form = $('#tutor-quiz-body form#tutor-start-quiz');
    if ($quiz_start_form.length){
        if (_tutorobject.quiz_options.quiz_auto_start === '1'){
            $quiz_start_form.submit();
        }
    }

    /**
     * Quiz Frontend Review Action
     * @since 1.4.0
     */
    $(document).on('click', '.quiz-manual-review-action', function(e){
        e.preventDefault();
        var $that = $(this),
            attempt_id = $that.attr('data-attempt-id'),
            attempt_answer_id = $that.attr('data-attempt-answer-id'),
            mark_as = $that.attr('data-mark-as');

        $.ajax({
            url : _tutorobject.ajaxurl,
            type : 'GET',
            data : {action:'review_quiz_answer', attempt_id: attempt_id, attempt_answer_id : attempt_answer_id, mark_as : mark_as },
            beforeSend: function () {
                $that.find('i').addClass('updating-icon');
            },
            success: function (data) {
                location.reload();
            },
            complete: function () {
                $that.find('i').removeClass('updating-icon');
            }
        });
    });

    // tutor course content accordion
    $('.tutor-course-topic.tutor-active').find('.tutor-course-lessons').slideDown();
    $('.tutor-course-title').on('click', function () {
        var lesson = $(this).siblings('.tutor-course-lessons');
        $(this).closest('.tutor-course-topic').toggleClass('tutor-active');
        lesson.slideToggle();
    });

    $(document).on('click', '.tutor-topics-title h3 .toogle-informaiton-icon', function (e) {
        $(this).closest('.tutor-topics-in-single-lesson').find('.tutor-topics-summery').slideToggle();
        e.stopPropagation();
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

    /**
     * Check if lesson has classic editor support
     * If classic editor support, stop ajax load on the lesson page.
     *
     * @since v.1.0.0
     *
     * @updated v.1.4.0
     */
    if ( ! _tutorobject.enable_lesson_classic_editor) {

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
                    $('.tutor-lesson-sidebar').css('display', '');
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
                    init_quiz_builder();
                    $('.tutor-lesson-sidebar').css('display', '');
                },
                complete: function () {
                    $wrap.removeClass('loading-lesson');
                }
            });
        });
    }

    /**
     * @date 05 Feb, 2019
     */

    $(document).on('click', '.tutor-lesson-sidebar-hide-bar', function(e){
        e.preventDefault();
        $('.tutor-lesson-sidebar').toggle();
        $('#tutor-single-entry-content').toggleClass("sidebar-hidden");

    });

    $(".tutor-tabs-btn-group a").on('click touchstart', function (e) {
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

    function init_quiz_builder() {
        if (jQuery().sortable) {
            $(".tutor-quiz-answers-wrap").sortable({
                handle: ".answer-sorting-bar",
                start: function (e, ui) {
                    ui.placeholder.css('visibility', 'visible');
                },
                stop: function (e, ui) {
                    //Sorting Stopped...
                },
            }).disableSelection();

            $(".quiz-draggable-rand-answers, .quiz-answer-matching-droppable").sortable({
                connectWith: ".quiz-answer-matching-droppable",
                placeholder: "drop-hover",
            }).disableSelection();
        }
    }
    init_quiz_builder();
    /**
     * Quiz view
     * @date 22 Feb, 2019
     * @since v.1.0.0
     */

    $(document).on('click', '.tutor-quiz-answer-next-btn', function (e) {
        e.preventDefault();

        var $that = $(this);
        var $question_wrap = $that.closest('.quiz-attempt-single-question');
        /**
         * Validating required answer
         * @type {jQuery}
         *
         * @since v.1.6.1
         */

        var validated = tutor_quiz_validation($question_wrap);
        if ( ! validated){
            return;
        }

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


    $(document).on('submit', '#tutor-answering-quiz', function (e) {
        var $questions_wrap = $('.quiz-attempt-single-question');
        var validated = true;
        if ($questions_wrap.length){
            $questions_wrap.each(function(index, question){
                validated = tutor_quiz_validation($(question));
            });
        }

        if ( ! validated){
            e.preventDefault();
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
     *
     * @type {jQuery}
     *
     * Improved Quiz draggable answers drop accessibility
     * Answers draggable wrap will be now same height.
     *
     * @since v.1.4.4
     */
    var countDraggableAnswers = $('.quiz-draggable-rand-answers').length;
    if (countDraggableAnswers){
        $('.quiz-draggable-rand-answers').each(function(){
            var $that = $(this);
            var draggableDivHeight = $that.height();

            $that.css({"height": draggableDivHeight});
        });
    }


    /**
     * Quiz Validation Helper
     *
     * @since v.1.6.1
     */

    function tutor_quiz_validation($question_wrap){
        var validated = true;

        var $required_answer_wrap = $question_wrap.find('.quiz-answer-required');


        if ($required_answer_wrap.length){

            /**
             * Radio field validation
             *
             * @type {jQuery}
             *
             * @since v.1.6.1
             */
            var $inputs = $required_answer_wrap.find('input');
            if ($inputs.length){
                var $type = $inputs.attr('type');
                if ($type === 'radio') {
                    if ($required_answer_wrap.find('input[type="radio"]:checked').length == 0) {
                        $question_wrap.find('.answer-help-block').html('<p style="color: #dc3545">Please select an option to answer</p>');
                        validated = false;
                    }
                }else if ($type === 'checkbox'){
                    if ($required_answer_wrap.find('input[type="checkbox"]:checked').length == 0) {
                        $question_wrap.find('.answer-help-block').html('<p style="color: #dc3545">Please select at least one option to answer.</p>');
                        validated = false;
                    }
                }else if($type === 'text'){
                    //Fill in the gaps if many, validation all
                    $inputs.each(function(index, input){
                        if ( ! $(input).val().trim().length){
                            $question_wrap.find('.answer-help-block').html('<p style="color: #dc3545">The answer for this question is required</p>');
                            validated = false;
                        }
                    });
                }

            }
            if ($required_answer_wrap.find('textarea').length) {
                if ($required_answer_wrap.find('textarea').val().trim().length < 1){
                    $question_wrap.find('.answer-help-block').html('<p style="color: #dc3545">The answer for this question is required</p>');
                    validated = false;
                }
            }

            /**
             * Matching Question
             */
            var $matchingDropable = $required_answer_wrap.find('.quiz-answer-matching-droppable');
            if ($matchingDropable.length){

                $matchingDropable.each(function(index, matching){
                    if ( ! $(matching).find('.quiz-draggable-answer-item').length){
                        $question_wrap.find('.answer-help-block').html('<p style="color: #dc3545">Please match all the items</p>');
                        validated = false;
                    }

                });

            }



        }

        return validated;
    }



    /**
     * Add to cart in guest mode, show login form
     *
     * @since v.1.0.4
     */

    $(document).on('submit click', '.cart-required-login, .cart-required-login a, .cart-required-login form', function (e) {
        e.preventDefault();

        $('.tutor-cart-box-login-form').fadeIn(100);
    });

    $('.tutor-popup-form-close, .login-overlay-close').on('click', function () {
        $('.tutor-cart-box-login-form').fadeOut(100);
    });

    $(document).on('keyup', function (e) {
        if (e.keyCode === 27) {
            $('.tutor-frontend-modal').hide();
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

    /**
     * Datepicker initiate
     *
     * @since v.1.1.2
     */
    if (jQuery.datepicker){
        $( ".tutor_report_datepicker" ).datepicker({"dateFormat" : 'yy-mm-dd'});
    }


    /**
     * Withdraw Form Tab/Toggle
     *
     * @since v.1.1.2
     */

    $(".withdraw-method-select-input").on('change', function(e){
        var $that = $(this);
        $('.withdraw-method-form').hide();
        $('#withdraw-method-form-'+$that.closest('.withdraw-method-select').attr('data-withdraw-method')).show();
    });

    $('.withdraw-method-select-input').each(function () {
        var $that = $(this);
        if($that.is(":checked")){
            $('.withdraw-method-form').hide();
            $('#withdraw-method-form-'+$that.closest('.withdraw-method-select').attr('data-withdraw-method')).show();
        }
    });



    /**
     * Setting account for withdraw earning
     *
     * @since v.1.2.0
     */
    $(document).on('submit', '#tutor-withdraw-account-set-form', function(e){
        e.preventDefault();

        var $form = $(this);
        var $btn = $form.find('.tutor_set_withdraw_account_btn');
        var data = $form.serialize();

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $form.find('.tutor-success-msg').remove();
                $btn.addClass('updating-icon');
            },
            success: function (data) {
                if (data.success){
                    var successMsg = '<div class="tutor-success-msg" style="display: none;"><i class="tutor-icon-mark"></i> '+data.data.msg+' </div>';
                    $btn.closest('.withdraw-account-save-btn-wrap').append(successMsg);
                    if ($form.find('.tutor-success-msg').length) {
                        $form.find('.tutor-success-msg').slideDown();
                    }
                    setTimeout(function () {
                        $form.find('.tutor-success-msg').slideUp();
                    }, 5000)
                }
            },
            complete: function () {
                $btn.removeClass('updating-icon');
            }
        });
    });

    /**
     * Make Withdraw Form
     *
     * @since v.1.2.0
     */

    $(document).on('click', 'a.open-withdraw-form-btn', function(e){
        e.preventDefault();
        $('.tutor-earning-withdraw-form-wrap').slideToggle();
    });

    $(document).on('submit', '#tutor-earning-withdraw-form', function(e){
        e.preventDefault();

        var $form = $(this);
        var $btn = $('#tutor-earning-withdraw-btn');
        var $responseDiv = $('#tutor-withdraw-form-response');
        var data = $form.serialize();

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $form.find('.tutor-success-msg').remove();
                $btn.addClass('updating-icon');
            },
            success: function (data) {
                var Msg;
                if (data.success){

                    if (data.data.available_balance !== 'undefined') {
                        $('.withdraw-balance-col .available_balance').html(data.data.available_balance);
                    }
                    Msg = '<div class="tutor-success-msg"><i class="tutor-icon-mark"></i> '+data.data.msg+' </div>';

                }else{
                    Msg = '<div class="tutor-error-msg"><i class="tutor-icon-line-cross"></i> '+data.data.msg+' </div>';
                }

                $responseDiv.html(Msg);
                setTimeout(function () {
                    $responseDiv.html('');
                }, 5000)
            },
            complete: function () {
                $btn.removeClass('updating-icon');
            }
        });
    });

    var frontEndModal = $('.tutor-frontend-modal');
    frontEndModal.each(function () {
        var modal = $(this),
            action = $(this).data('popup-rel');
        $('[href="'+action+'"]').on('click', function (e) {
            modal.fadeIn();
            e.preventDefault();
        });
    });
    $(document).on('click', '.tm-close, .tutor-frontend-modal-overlay, .tutor-modal-btn-cancel', function () {
        frontEndModal.fadeOut();
    });

    /**
     * Delete Course
     */
    $(document).on('click', '.tutor-mycourse-delete-btn', function (e) {
        e.preventDefault();
        var course_id = $(this).attr('data-course-id');
        $('#tutor-course-delete-id').val(course_id);
    });
    $(document).on('submit', '#tutor-delete-course-form', function (e) {
        e.preventDefault();

        var course_id = $('#tutor-course-delete-id').val();
        var $btn = $('.tutor-modal-course-delete-btn');
        var data = $(this).serialize();

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $btn.addClass('updating-icon');
            },
            success: function (data) {
                if (data.success){
                    $('#tutor-dashboard-course-'+course_id).remove();
                }
            },
            complete: function () {
                $btn.removeClass('updating-icon');
                $('.tutor-frontend-modal').hide();
            }
        });
    });

    /**
     * Frontend Profile
     */

    if (! $('#tutor_profile_photo_id').val()) {
        $('.tutor-profile-photo-delete-btn').hide();
    }

    $( document ).on( 'click', '.tutor-profile-photo-delete-btn', function() {
        $( '.tutor-profile-photo-upload-wrap' ).find( 'img' ).attr( 'src', _tutorobject.placeholder_img_src );
        $( '#tutor_profile_photo_id' ).val( '' );
        $( '.tutor-profile-photo-delete-btn' ).hide();

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: {'action' : 'tutor_profile_photo_remove'},
        });

        return false;
    });



    /**
     * Assignment
     *
     * @since v.1.3.3
     */

    $( document ).on( 'submit', '#tutor_assignment_start_form', function(e) {
        e.preventDefault();

        var $that = $(this);
        var form_data = $that.serialize()+'&action=tutor_start_assignment';

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: form_data,
            beforeSend: function () {
                $('#tutor_assignment_start_btn').addClass('updating-icon');
            },
            success: function (data) {
                if (data.success){
                    location.reload();
                }
            },
            complete : function () {
                $('#tutor_assignment_start_btn').removeClass('updating-icon');
            }
        });
    });

    /**
     * Assignment answer validation
     */
    $( document ).on( 'submit', '#tutor_assignment_submit_form', function(e) {
        var assignment_answer = $('textarea[name="assignment_answer"]').val();
        if (assignment_answer.trim().length < 1) {
            $('#form_validation_response').html('<div class="tutor-error-msg">'+_tutorobject.text.assignment_text_validation_msg+'</div>');
            e.preventDefault();
        }
    });

    /**
     * Course builder video
     * @since v.1.3.4
     */


    $(document).on( 'click', '.video_source_upload_wrap_html5 .video_upload_btn',  function( event ){
        event.preventDefault();

        var $that = $(this);
        var frame;
        // If the media frame already exists, reopen it.
        if ( frame ) {
            frame.open();
            return;
        }
        frame = wp.media({
            title: 'Select or Upload Media Of Your Chosen Persuasion',
            button: {
                text: 'Use this media'
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });
        frame.on( 'select', function() {
            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();
            $that.closest('.video_source_upload_wrap_html5').find('span.video_media_id').text(attachment.id).closest('p').show();
            $that.closest('.video_source_upload_wrap_html5').find('input').val(attachment.id);
        });
        frame.open();
    });


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
        form_data += '&lesson_content='+encodeURIComponent(content);

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
     * END: Tutor Course builder JS
     */

    /**
     * Attachment in forntend course builder
     * @since v.1.3.4
     */
    $(document).on('click', 'a.tutor-delete-attachment', function(e){
        e.preventDefault();
        $(this).closest('.tutor-added-attachment').remove();
    });
    $(document).on('click', '.tutorUploadAttachmentBtn', function(e){
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
            multiple: true  // Set to true to allow multiple files to be selected
        });
        frame.on( 'select', function() {
            var attachments = frame.state().get('selection').toJSON();
            if (attachments.length){
                for (var i=0; i < attachments.length; i++){
                    var attachment = attachments[i];

                    var inputHtml = '<div class="tutor-added-attachment"><i class="tutor-icon-archive"></i><a href="javascript:;" class="tutor-delete-attachment tutor-icon-line-cross"></a> <span> <a href="'+attachment.url+'">'+attachment.filename+'</a> </span> <input type="hidden" name="tutor_attachments[]" value="'+attachment.id+'"></div>';
                    $that.closest('.tutor-lesson-attachments-metabox').find('.tutor-added-attachments-wrap').append(inputHtml);
                }
            }
        });
        frame.open();
    });


    /**
     * Single Assignment Upload Button
     * @since v.1.3.4
     */
    $('form').on('change', '.tutor-assignment-file-upload', function () {
        $(this).siblings("label").find('span').html($(this).val().replace(/.*(\/|\\)/, ''));
    });

    /**
     * Lesson Sidebar Topic Toggle
     * @since v.1.3.4
     */

    $(document).on('click', '.tutor-topics-in-single-lesson .tutor-topics-title h3, .tutor-single-lesson-topic-toggle', function (e) {
        var $that = $(this);
        var $parent = $that.closest('.tutor-topics-in-single-lesson');
        $parent.toggleClass('tutor-topic-active');
        $parent.find('.tutor-lessons-under-topic').slideToggle();
    });

    $('.tutor-single-lesson-items.active').closest('.tutor-lessons-under-topic').show();
    $('.tutor-single-lesson-items.active').closest('.tutor-topics-in-single-lesson').addClass('tutor-topic-active');
    $('.tutor-course-lesson.active').closest('.tutor-lessons-under-topic').show();


    /**
     * Assignments Addons
     * @backend Support
     *
     */


    /**
     * Tutor Assignments JS
     * @since v.1.3.3
     */
    $(document).on('click', '.tutor-create-assignments-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var topic_id = $(this).attr('data-topic-id');
        var course_id = $('#post_ID').val();

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {topic_id : topic_id, course_id : course_id, action: 'tutor_load_assignments_builder_modal'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('.tutor-lesson-modal-wrap .modal-container').html(data.data.output);
                $('.tutor-lesson-modal-wrap').attr('data-topic-id', topic_id).addClass('show');

                $(document).trigger('assignment_modal_loaded', {topic_id : topic_id, course_id : course_id});

                tinymce.init(tinyMCEPreInit.mceInit.course_description);
                tinymce.execCommand( 'mceRemoveEditor', false, 'tutor_assignments_modal_editor' );
                tinyMCE.execCommand('mceAddEditor', false, "tutor_assignments_modal_editor");
            },
            complete: function () {
                quicktags({id : "tutor_assignments_modal_editor"});
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    $(document).on('click', '.open-tutor-assignment-modal', function(e){
        e.preventDefault();

        var $that = $(this);
        var assignment_id = $that.attr('data-assignment-id');
        var topic_id = $that.attr('data-topic-id');
        var course_id = $('#post_ID').val();

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {assignment_id : assignment_id, topic_id : topic_id, course_id : course_id, action: 'tutor_load_assignments_builder_modal'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('.tutor-lesson-modal-wrap .modal-container').html(data.data.output);
                $('.tutor-lesson-modal-wrap').attr({'data-assignment-id' : assignment_id, 'data-topic-id':topic_id}).addClass('show');

                $(document).trigger('assignment_modal_loaded', {assignment_id : assignment_id, topic_id : topic_id, course_id : course_id});

                tinymce.init(tinyMCEPreInit.mceInit.course_description);
                tinymce.execCommand( 'mceRemoveEditor', false, 'tutor_assignments_modal_editor' );
                tinyMCE.execCommand('mceAddEditor', false, "tutor_assignments_modal_editor");
            },
            complete: function () {
                quicktags({id : "tutor_assignments_modal_editor"});
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Update Assignment Data
     */
    $(document).on( 'click', '.update_assignment_modal_btn',  function( event ){
        event.preventDefault();

        var $that = $(this);
        var content;
        var editor = tinyMCE.get('tutor_assignments_modal_editor');
        if (editor) {
            content = editor.getContent();
        } else {
            content = $('#'+inputid).val();
        }

        var form_data = $(this).closest('form').serialize();
        form_data += '&assignment_content='+content;

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
     * Add Assignment
     */
    $(document).on( 'click', '.add-assignment-attachments',  function( event ){
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

            var  field_markup = '<div class="tutor-individual-attachment-file"><p class="attachment-file-name">'+attachment.filename+'</p><input type="hidden" name="tutor_assignment_attachments[]" value="'+attachment.id+'"><a href="javascript:;" class="remove-assignment-attachment-a text-muted"> &times; Remove</a></div>';

            $('#assignment-attached-file').append(field_markup);
            $that.closest('.video_source_upload_wrap_html5').find('input').val(attachment.id);
        });
        // Finally, open the modal on click
        frame.open();
    });

    $(document).on( 'click', '.remove-assignment-attachment-a',  function( event ){
        event.preventDefault();
        $(this).closest('.tutor-individual-attachment-file').remove();
    });


    /**
     *
     * @type {jQuery}
     *
     * Course builder auto draft save
     *
     * @since v.1.3.4
     */
    var tutor_course_builder = $('input[name="tutor_action"]').val();
    if (tutor_course_builder === 'tutor_add_course_builder'){
        setInterval(auto_draft_save_course_builder, 30000);
    }

    function auto_draft_save_course_builder(){
        var form_data = $('form#tutor-frontend-course-builder').serialize();
        $.ajax({
            //url : _tutorobject.ajaxurl,
            type : 'POST',
            data : form_data+'&tutor_ajax_action=tutor_course_builder_draft_save',
            beforeSend: function () {
                $('.tutor-dashboard-builder-draft-btn span').text('Saving...');
            },
            success: function (data) {

            },
            complete: function () {
                $('.tutor-dashboard-builder-draft-btn span').text('Save');
            }
        });
    }

    /**
     *
     * @type {jQuery}
     *
     * Course builder section toggle
     *
     * @since v.1.3.5
     */

    $('.tutor-course-builder-section-title').on('click', function () {
        if($(this).find('i').hasClass("tutor-icon-up")){
            $(this).find('i').removeClass('tutor-icon-up').addClass('tutor-icon-down');
        }else{
            $(this).find('i').removeClass('tutor-icon-down').addClass('tutor-icon-up');
        }
        $(this).next('div').slideToggle();
    });

    /**
     * Open Tutor Modal to edit review
     * @since v.1.4.0
     */
    $(document).on('click', '.open-tutor-edit-review-modal', function(e){
        e.preventDefault();

        var $that = $(this);
        var review_id = $that.attr('data-review-id');

        var nonce_key = _tutorobject.nonce_key;

        var json_data = {review_id : review_id, action: 'tutor_load_edit_review_modal'};
        json_data[nonce_key] = _tutorobject[nonce_key];

        $.ajax({
            url : _tutorobject.ajaxurl,
            type : 'POST',
            data : json_data,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (typeof data.data !== 'undefined') {
                    $('.tutor-edit-review-modal-wrap .modal-container').html(data.data.output);
                    $('.tutor-edit-review-modal-wrap').attr('data-review-id', review_id).addClass('show');
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Update the rating
     * @since v.1.4.0
     */
    $(document).on('submit', '#tutor_update_review_form', function(e){
        e.preventDefault();

        var $that = $(this);
        var review_id = $that.closest('.tutor-edit-review-modal-wrap ').attr('data-review-id');

        var nonce_key = _tutorobject.nonce_key;

        var rating = $that.find('input[name="tutor_rating_gen_input"]').val();
        var review = $that.find('textarea[name="review"]').val();
        review = review.trim();

        var json_data = {review_id : review_id, rating : rating, review : review, action: 'tutor_update_review_modal'};
        json_data[nonce_key] = _tutorobject[nonce_key];

        $.ajax({
            url : _tutorobject.ajaxurl,
            type : 'POST',
            data : json_data,
            beforeSend: function () {
                $that.find('button[type="submit"]').addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    //Close the modal
                    $('.tutor-edit-review-modal-wrap').removeClass('show');
                    location.reload(true);
                }
            },
            complete: function () {
                $that.find('button[type="submit"]').removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Profile photo upload
     * @since v.1.4.5
     */

    $(document).on('click', '#tutor_profile_photo_button', function(e){
        e.preventDefault();

        $('#tutor_profile_photo_file').trigger('click');
    });

    $(document).on('change', '#tutor_profile_photo_file', function(event){
        event.preventDefault();

        var $file = this;
        if ($file.files && $file.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $( '.tutor-profile-photo-upload-wrap' ).find( 'img' ).attr( 'src',  e.target.result);
            }
            reader.readAsDataURL($file.files[0]);
        }
    });

    /**
     * Addon, Tutor BuddyPress
     * Retrieve MetaInformation on BuddyPress message system
     * @for TutorLMS Pro
     * @since v.1.4.8
     */

    $(document).on('click', '.thread-content .subject', function(e){
        var $btn = $(this);

        var thread_id = parseInt($btn.closest('.thread-content').attr('data-thread-id'));

        var nonce_key = _tutorobject.nonce_key;
        var json_data = {thread_id : thread_id, action: 'tutor_bp_retrieve_user_records_for_thread'};
        json_data[nonce_key] = _tutorobject[nonce_key];

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: json_data,
            beforeSend: function(){
                $('#tutor-bp-thread-wrap').html('');
            },
            success: function (data) {
                if (data.success){
                    $('#tutor-bp-thread-wrap').html(data.data.thread_head_html);
                    tutor_bp_setting_enrolled_courses_list();
                }
            }
        });

    });


    function tutor_bp_setting_enrolled_courses_list(){
        $('ul.tutor-bp-enrolled-course-list').each(function(){
            var $that = $(this);
            var $li = $that.find(' > li');
            var itemShow = 3;

            if ($li.length > itemShow){
                var plusCourseCount = $li.length - itemShow;
                $li.each(function(liIndex, liItem){
                    var $liItem = $(this);

                    if (liIndex >= itemShow){
                        $liItem.hide();
                    }
                });

                var infoHtml = '<a href="javascript:;" class="tutor_bp_plus_courses"><strong>+'+plusCourseCount+' More </strong></a> Courses';
                $that.closest('.tutor-bp-enrolled-courses-wrap').find('.thread-participant-enrolled-info').html(infoHtml);
            }

            $that.show();
        });
    }
    tutor_bp_setting_enrolled_courses_list();

    $(document).on('click', 'a.tutor_bp_plus_courses', function(e){
        e.preventDefault();

        var $btn = $(this);
        $btn.closest('.tutor-bp-enrolled-courses-wrap').find('.tutor-bp-enrolled-course-list li').show();
        $btn.closest('.thread-participant-enrolled-info').html('');
    });



    /**
     * Addon, Tutor Certificate
     * Certificate dropdown content and copy link
     * @for TutorLMS Pro
     * @since v.1.5.1
     */
    $(document).on('click', '.tutor-dropbtn', function (e) {
        var $content = $(this).parent().find(".tutor-dropdown-content");
        $content.slideToggle(100);
    });
    $(document).on('click', '.tutor-copy-link', function (e) {
        var $btn = $(this);
        var copy = '<i class="tutor-icon-copy"></i> Copy Link';
        var copied = '<i class="tutor-icon-mark"></i> Copied';
        var dummy = document.createElement('input'),
            text = window.location.href;
        document.body.appendChild(dummy);
        dummy.value = text;
        dummy.select();
        document.execCommand('copy');
        document.body.removeChild(dummy);
        $btn.html(copied);
        setTimeout( function() {
            $btn.html(copy);
        }, 2500);
    });
    $(document).on('click', function(e) {
        var container = $(".tutor-dropdown");
        var $content = container.find('.tutor-dropdown-content');
        // if the target of the click isn't the container nor a descendant of the container
        if (!container.is(e.target) && container.has(e.target).length === 0)  {
            $content.slideUp(100);
        }
    });

});
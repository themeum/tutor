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

                    if (_tutorobject.options.quiz_when_time_expires === 'autosubmit') {
                        /**
                         * Auto Submit
                         */
                        $('form#tutor-answering-quiz').submit();

                    } else if (_tutorobject.options.quiz_when_time_expires === 'autoabandon') {
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
                $that.addClass('updating-icon');
            },
            success: function (data) {
                location.reload();
            },
            complete: function () {
                $that.removeClass('updating-icon');
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
            ;


            $(".quiz-draggable-rand-answers, .quiz-answer-matching-droppable").sortable({
                connectWith: ".quiz-answer-matching-droppable",
                placeholder: "drop-hover"

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
    // Uploading files
    var file_frame;
    $( document ).on( 'click', '.tutor-profile-photo-upload-btn', function( event ) {
        event.preventDefault();

        if ( file_frame ) {
            file_frame.open();
            return;
        }
        file_frame = wp.media.frames.downloadable_file = wp.media({
            title: 'Choose an image',
            button: {
                text: 'Use image'
            },
            multiple: false
        });
        file_frame.on( 'select', function() {
            var attachment           = file_frame.state().get( 'selection' ).first().toJSON();
            var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;

            $( '#tutor_profile_photo_id' ).val( attachment.id );
            $( '.tutor-profile-photo-upload-wrap' ).find( 'img' ).attr( 'src', attachment_thumbnail.url );
            $( '.tutor-profile-photo-delete-btn' ).show();
        });
        file_frame.open();
    });

    $( document ).on( 'click', '.tutor-profile-photo-delete-btn', function() {
        $( '.tutor-profile-photo-upload-wrap' ).find( 'img' ).attr( 'src', _tutorobject.placeholder_img_src );
        $( '#tutor_profile_photo_id' ).val( '' );
        $( '.tutor-profile-photo-delete-btn' ).hide();
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

                //Find lesson id variable and replace with actual id
                var $classic_editor_btn = $('.tutor-classic-editor-btn');
                if ($classic_editor_btn.length){
                    $classic_editor_btn.attr('href', $classic_editor_btn.attr('href').replace('{lesson_id}', lesson_id) );
                }

                tinymce.init(tinyMCEPreInit.mceInit.course_description);
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
                    $that.closest('.course-content-item').remove();
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Delete quiz
     */
    $(document).on('click', '.tutor-delete-quiz-btn', function(e){
        e.preventDefault();

        if( ! confirm('Are you sure?')){
            return;
        }

        var $that = $(this);
        var quiz_id = $that.attr('data-quiz-id');

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {quiz_id : quiz_id, action: 'tutor_delete_quiz_by_id'},
            beforeSend: function () {
                $that.closest('.course-content-item').remove();
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
     * END: Tutor Course builder JS
     */

    /**
     * Modal Close
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
    /**
     * END: Modal Close
     */


    /**
     * Instructor in the course builder frontend
     * @since v.1.3.4
     */


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

    /**
     * Frontend Course Builder
     * @backend Support
     *
     * @since v.1.3.4
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

    $(document).on('click', '.tutor-quiz-question-trash', function(e){
        e.preventDefault();

        var $that = $(this);
        var question_id = $that.attr('data-question-id');

        $.ajax({
            url : ajaxurl,
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
     * Get question answers option edit form
     *
     * @since v.1.0.0
     */
    $(document).on('click', '.tutor-quiz-answer-edit a', function(e){
        e.preventDefault();

        var $that = $(this);
        var answer_id = $that.closest('.tutor-quiz-answer-wrap').attr('data-answer-id');

        $.ajax({
            url : ajaxurl,
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

    /**
     * Updating Answer
     *
     * @since v.1.0.0
     */
    $(document).on('click', '#quiz-answer-edit-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var $formInput = $('.quiz_question_form :input').serialize()+'&action=tutor_update_quiz_answer_options';

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

    $(document).on('change', '.tutor-quiz-answers-mark-correct-wrap input', function(e){
        e.preventDefault();

        var $that = $(this);

        var answer_id = $that.val();
        var inputValue = 1;
        if ( ! $that.prop('checked')) {
            inputValue = 0;
        }

        $.ajax({
            url : ajaxurl,
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
     * Assignments Addons
     * @backend Support
     *
     */


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

});
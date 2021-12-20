import './dashboard';
import './_select_dd_search';
import './pages/instructor-list-filter';
import './pages/course-landing';
import './course/index';
import './dashboard/export-csv';

jQuery(document).ready(function ($) {
    'use strict';
    /**
     * wp.i18n translateable functions
     * @since 1.9.0
     */
    const { __, _x, _n, _nx } = wp.i18n;
    /**
     * Initiate Select2
     * @since v.1.3.4
     */
    if (jQuery().select2) {
        $('.tutor_select2').select2({
            escapeMarkup: function (markup) {
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
    ! function (a) {
        function f(a, b) {
            if (!(a.originalEvent.touches.length > 1)) {
                a.preventDefault();
                var c = a.originalEvent.changedTouches[0],
                    d = document.createEvent("MouseEvents");
                d.initMouseEvent(b, !0, !0, window, 1, c.screenX, c.screenY, c.clientX, c.clientY, !1, !1, !1, !1, 0, null), a.target.dispatchEvent(d)
            }
        }
        if (a.support.touch = "ontouchend" in document, a.support.touch) {
            var e, b = a.ui.mouse.prototype,
                c = b._mouseInit,
                d = b._mouseDestroy;
            b._touchStart = function (a) { var b = this; !e && b._mouseCapture(a.originalEvent.changedTouches[0]) && (e = !0, b._touchMoved = !1, f(a, "mouseover"), f(a, "mousemove"), f(a, "mousedown")) }, b._touchMove = function (a) { e && (this._touchMoved = !0, f(a, "mousemove")) }, b._touchEnd = function (a) { e && (f(a, "mouseup"), f(a, "mouseout"), this._touchMoved || f(a, "click"), e = !1) }, b._mouseInit = function () {
                var b = this;
                b.element.bind({ touchstart: a.proxy(b, "_touchStart"), touchmove: a.proxy(b, "_touchMove"), touchend: a.proxy(b, "_touchEnd") }), c.call(b)
            }, b._mouseDestroy = function () {
                var b = this;
                b.element.unbind({ touchstart: a.proxy(b, "_touchStart"), touchmove: a.proxy(b, "_touchMove"), touchend: a.proxy(b, "_touchEnd") }), d.call(b)
            }
        }
    }(jQuery);

    /**
     * END jQuery UI Touch Punch
     */

    const videoPlayer = {
        ajaxurl: window._tutorobject.ajaxurl,
        nonce_key: window._tutorobject.nonce_key,
        video_data: function () {
            const video_track_data = $('#tutor_video_tracking_information').val();
            return video_track_data ? JSON.parse(video_track_data) : {};
        },
        track_player: function () {
            const that = this;
            if (typeof Plyr !== 'undefined') {
                const player = new Plyr(this.player_DOM);
                const video_data = that.video_data();
                player.on('ready', function (event) {
                    const instance = event.detail.plyr;
                    const { best_watch_time } = video_data;
                    if (best_watch_time > 0 && instance.duration > Math.round(best_watch_time)) {
                        instance.media.currentTime = best_watch_time;
                    }
                    that.sync_time(instance);
                });

                let tempTimeNow = 0;
                let intervalSeconds = 30; //Send to tutor backend about video playing time in this interval
                player.on('timeupdate', function (event) {
                    const instance = event.detail.plyr;
                    const tempTimeNowInSec = (tempTimeNow / 4); //timeupdate firing 250ms interval
                    if (tempTimeNowInSec >= intervalSeconds) {
                        that.sync_time(instance);
                        tempTimeNow = 0;
                    }
                    tempTimeNow++;
                });

                player.on('ended', function (event) {
                    const video_data = that.video_data();
                    const instance = event.detail.plyr;
                    const data = { is_ended: true };
                    that.sync_time(instance, data);
                    if (video_data.autoload_next_course_content) {
                        that.autoload_content();
                    }
                });
            }
        },
        sync_time: function (instance, options) {
            const post_id = this.video_data().post_id;
            //TUTOR is sending about video playback information to server.
            let data = { action: 'sync_video_playback', currentTime: instance.currentTime, duration: instance.duration, post_id };
            data[this.nonce_key] = _tutorobject[this.nonce_key];
            let data_send = data;
            if (options) {
                data_send = Object.assign(data, options);
            }
            $.post(this.ajaxurl, data_send);
        },
        autoload_content: function () {
            const post_id = this.video_data().post_id;
            const data = { action: 'autoload_next_course_content', post_id };
            data[this.nonce_key] = _tutorobject[this.nonce_key];
            $.post(this.ajaxurl, data).done(function (response) {
                if (response.success && response.data.next_url) {
                    location.href = response.data.next_url;
                }
            });
        },
        init: function (element) {
            this.player_DOM = element;
            this.track_player();
        }
    };

    /**
     * Fire TUTOR video
     * @since v.1.0.0
     */
    $('.tutorPlayer').each(function () {
        console.log(this);
        videoPlayer.init(this);
    });

    $(document).on('change keyup paste', '.tutor_user_name', function () {
        $(this).val(tutor_slugify($(this).val()));
    });

    function tutor_slugify(text) {
        return text.toString().toLowerCase()
            .replace(/\s+/g, '-') // Replace spaces with -
            .replace(/[^\w\-]+/g, '') // Remove all non-word chars
            .replace(/\-\-+/g, '-') // Replace multiple - with single -
            .replace(/^-+/, '') // Trim - from start of text
            .replace(/-+$/, ''); // Trim - from end of text
    }

    $(document).on('click', '.tutor_question_cancel', function (e) {
        e.preventDefault();
        $('.tutor-add-question-wrap').toggle();
    });

    /**
     * Quiz attempt
     */

    var $tutor_quiz_time_update = $('#tutor-quiz-time-update');
    var attempt_settings = null;
    if ($tutor_quiz_time_update.length) {
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
                        var quiz_timeout_data = { quiz_id: quiz_id, action: 'tutor_quiz_timeout' };

                        var att = $("#tutor-quiz-time-expire-wrapper").attr('data-attempt-remaining');

                        //disable buttons
                        $(".tutor-quiz-answer-next-btn, .tutor-quiz-submit-btn, .tutor-quiz-answer-previous-btn").prop('disabled', true);

                        //add alert text
                        $(".time-remaining span").css('color', '#F44337');

                        $.ajax({
                            url: _tutorobject.ajaxurl,
                            type: 'POST',
                            data: quiz_timeout_data,
                            success: function (data) {

                                var attemptAllowed = $("#tutor-quiz-time-expire-wrapper").data('attempt-allowed');
                                var attemptRemaining = $("#tutor-quiz-time-expire-wrapper").data('attempt-remaining');

                                var alertDiv = "#tutor-quiz-time-expire-wrapper .tutor-alert";
                                $(alertDiv).addClass('tutor-alert-show');
                                if (att > 0) {
                                    $(`${alertDiv} .text`).html(
                                        __('Your time limit for this quiz has expired, please reattempt the quiz. Attempts remaining: ' + attemptRemaining + '/' + attemptAllowed, 'tutor')
                                    );
                                } else {
                                    $(alertDiv).addClass('tutor-alert-danger');
                                    $("#tutor-start-quiz").hide();
                                    $(`${alertDiv} .text`).html(
                                        `${__('Unfortunately, you are out of time and quiz attempts. ', 'tutor')}`
                                    );
                                }

                            },
                            complete: function () {

                            }
                        });
                    }

                }
                time_now = time_now + 1000;
                $tutor_quiz_time_update.html(countdown_human);
            }, 1000);
        } else {
            $tutor_quiz_time_update.closest('.time-remaining').remove();
        }
    }

    var $quiz_start_form = $('#tutor-quiz-body form#tutor-start-quiz');
    if ($quiz_start_form.length) {
        if (_tutorobject.quiz_options.quiz_auto_start === '1') {
            $quiz_start_form.submit();
        }
    }

    // Quiz Review : Tooltip
    $(".tooltip-btn").on("hover", function (e) {
        $(this).toggleClass("active");
    });

    // tutor course content accordion

    /**
     * Toggle topic summery
     * @since v.1.6.9
     */
    $('.tutor-course-title h4 .toggle-information-icon').on('click', function (e) {
        $(this).closest('.tutor-topics-in-single-lesson').find('.tutor-topics-summery').slideToggle();
        e.stopPropagation();
    });

    $('.tutor-course-topic.tutor-active').find('.tutor-course-lessons').slideDown();
    $('.tutor-course-title').on('click', function () {
        var lesson = $(this).siblings('.tutor-course-lessons');
        $(this).closest('.tutor-course-topic').toggleClass('tutor-active');
        lesson.slideToggle();
    });

    $(document).on('click', '.tutor-topics-title h3 .toggle-information-icon', function (e) {
        $(this).closest('.tutor-topics-in-single-lesson').find('.tutor-topics-summery').slideToggle();
        e.stopPropagation();
    });

    /**
     * Check if lesson has classic editor support
     * If classic editor support, stop ajax load on the lesson page.
     *
     * @since v.1.0.0
     *
     * @updated v.1.4.0
     */
    if (!_tutorobject.enable_lesson_classic_editor) {

        $(document).on('click', '.tutor-single-lesson-a', function (e) {
            e.preventDefault();

            var $that = $(this);
            var lesson_id = $that.attr('data-lesson-id');
            var $wrap = $('#tutor-single-entry-content');

            $.ajax({
                url: _tutorobject.ajaxurl,
                type: 'POST',
                data: { lesson_id: lesson_id, 'action': 'tutor_render_lesson_content' },
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
                    window.dispatchEvent(new window.Event('tutor_ajax_lesson_loaded')); // Some plugins like h5p needs notification on ajax load
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
                data: { quiz_id: quiz_id, 'action': 'tutor_render_quiz_content' },
                beforeSend: function () {
                    $('head title').text(page_title);
                    window.history.pushState('obj', page_title, $that.attr('href'));
                    $wrap.addClass('loading-lesson');
                    $('.tutor-single-lesson-items').removeClass('active');
                    $that.closest('.tutor-single-lesson-items').addClass('active');
                },
                success: function (data) {
                    $wrap.html(data.data.html);
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

    $(document).on('click', '.tutor-lesson-sidebar-hide-bar', function (e) {
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
    $(document).on('keyup', 'textarea.question_type_short_answer, textarea.question_type_open_ended', function (e) {
        var $that = $(this);
        var value = $that.val();
        var limit = $that.hasClass('question_type_short_answer') ? _tutorobject.quiz_options.short_answer_characters_limit : _tutorobject.quiz_options.open_ended_answer_characters_limit;
        var remaining = limit - value.length;

        if (remaining < 1) {
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
    if (countDraggableAnswers) {
        $('.quiz-draggable-rand-answers').each(function () {
            var $that = $(this);
            var draggableDivHeight = $that.height();

            $that.css({ "height": draggableDivHeight });
        });
    }


    /**
     * Datepicker initiate
     *
     * @since v.1.1.2
     */
    if (jQuery.datepicker) {
        $(".tutor_report_datepicker").datepicker({ "dateFormat": 'yy-mm-dd' });
    }

    /**
     * Setting account for withdraw earning
     *
     * @since v.1.2.0
     */
    $(document).on('submit', '#tutor-withdraw-account-set-form', function (e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $form.find('.tutor_set_withdraw_account_btn');
        var data = $form.serializeObject();

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $btn.addClass('updating-icon');
            },
            success: function (data) {
                if (data.success) {
                    tutor_toast('Success!', data.data.msg, 'success');
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

    $(document).on('click', '.open-withdraw-form-btn, .close-withdraw-form-btn', function (e) {
        e.preventDefault();

        if ($(this).data('reload') == 'yes') {
            window.location.reload();
            return;
        }

        $('.tutor-earning-withdraw-form-wrap').toggle().find('[name="tutor_withdraw_amount"]').val('');
        $('.tutor-withdrawal-pop-up-success').hide().next().show();
        $('html, body').css('overflow', ($('.tutor-earning-withdraw-form-wrap').is(':visible') ? 'hidden' : 'auto'));
    });

    $(document).on('submit', '#tutor-earning-withdraw-form', function (e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $('#tutor-earning-withdraw-btn');
        var $responseDiv = $('.tutor-withdraw-form-response');
        var data = $form.serializeObject();

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
                if (data.success) {

                    if (data.data.available_balance !== 'undefined') {
                        $('.withdraw-balance-col .available_balance').html(data.data.available_balance);
                    }

                    $('.tutor-withdrawal-pop-up-success').show().next().hide();

                } else {
                    Msg = '<div class="tutor-error-msg inline-image-text is-inline-block">\
                            <img src="'+ window._tutorobject.tutor_url + 'assets/images/icon-cross.svg"/> \
                            <div>\
                                <b>Error</b><br/>\
                                <span>' + data.data.msg + '</span>\
                            </div>\
                        </div>';

                    $responseDiv.html(Msg);
                    setTimeout(function () {
                        $responseDiv.html('');
                    }, 5000)
                }
            },
            complete: function () {
                $btn.removeClass('updating-icon');
            }
        });
    });

    /**
     * Delete Course
     */
    $(document).on('click', '.tutor-dashboard-element-delete-btn', function (e) {
        e.preventDefault();
        var element_id = $(this).attr('data-id');
        $('#tutor-dashboard-delete-element-id').val(element_id);
    });
    $(document).on('submit', '#tutor-dashboard-delete-element-form', function (e) {
        e.preventDefault();

        var element_id = $('#tutor-dashboard-delete-element-id').val();
        var $btn = $('.tutor-modal-element-delete-btn');
        var data = $(this).serializeObject();

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $btn.addClass('updating-icon');
            },
            success: function (res) {
                if (res.success) {
                    $('#tutor-dashboard-' + res.data.element + '-' + element_id).remove();
                }
            },
            complete: function () {
                $btn.removeClass('updating-icon');
            }
        });
    });

    /**
     * Assignment
     *
     * @since v.1.3.3
     */
    $(document).on('submit', '#tutor_assignment_start_form', function (e) {
        e.preventDefault();

        var $that = $(this);
        var form_data = $that.serializeObject();
        form_data.action = 'tutor_start_assignment';

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: form_data,
            beforeSend: function () {
                $('#tutor_assignment_start_btn').addClass('updating-icon');
            },
            success: function (data) {
                console.log('success');
                if (data.success) {
                    location.reload();
                }
            },
            complete: function () {
                $('#tutor_assignment_start_btn').removeClass('updating-icon');
            }
        });
    });

    /**
     * Assignment answer validation
     */
    $(document).on('submit', '#tutor_assignment_submit_form', function (e) {
        var assignment_answer = $('textarea[name="assignment_answer"]').val();
        if (assignment_answer.trim().length < 1) {
            $('#form_validation_response').html('<div class="tutor-error-msg">' + __('Assignment answer can not be empty', 'tutor') + '</div>');
            e.preventDefault();
        }
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
     *
     * @type {jQuery}
     *
     * Course builder auto draft save
     *
     * @since v.1.3.4
     */
    var tutor_course_builder = $('input[name="tutor_action"]').val();
    if (tutor_course_builder === 'tutor_add_course_builder') {
        setInterval(auto_draft_save_course_builder, 30000);
    }

    function auto_draft_save_course_builder() {

        var form_data = $('form#tutor-frontend-course-builder').serializeObject();
        form_data.tutor_ajax_action = 'tutor_course_builder_draft_save';

        $.ajax({
            //url : _tutorobject.ajaxurl,
            type: 'POST',
            data: form_data,
            beforeSend: function () {
                $('.tutor-dashboard-builder-draft-btn span').text(__('Saving...', 'tutor'));
            },
            success: function (data) {

            },
            complete: function () {
                $('.tutor-dashboard-builder-draft-btn span').text(__('Save', 'tutor'));
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
        if ($(this).find('i').hasClass("tutor-icon-up")) {
            $(this).find('i').removeClass('tutor-icon-up').addClass('tutor-icon-down');
        } else {
            $(this).find('i').removeClass('tutor-icon-down').addClass('tutor-icon-up');
        }
        $(this).next('div').slideToggle();
    });

    /**
     * Profile photo upload
     * @since v.1.4.5
     */

    $(document).on('click', '#tutor_profile_photo_button', function (e) {
        e.preventDefault();

        $('#tutor_profile_photo_file').trigger('click');
    });

    $(document).on('change', '#tutor_profile_photo_file', function (event) {
        event.preventDefault();

        var $file = this;
        if ($file.files && $file.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('.tutor-profile-photo-upload-wrap').find('img').attr('src', e.target.result);
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

    $(document).on('click', '.thread-content .subject', function (e) {
        var $btn = $(this);

        var thread_id = parseInt($btn.closest('.thread-content').attr('data-thread-id'));

        var nonce_key = _tutorobject.nonce_key;
        var json_data = { thread_id: thread_id, action: 'tutor_bp_retrieve_user_records_for_thread' };
        json_data[nonce_key] = _tutorobject[nonce_key];

        $.ajax({
            type: 'POST',
            url: window._tutorobject.ajaxurl,
            data: json_data,
            beforeSend: function () {
                $('#tutor-bp-thread-wrap').html('');
            },
            success: function (data) {
                if (data.success) {
                    $('#tutor-bp-thread-wrap').html(data.data.thread_head_html);
                    tutor_bp_setting_enrolled_courses_list();
                }
            }
        });

    });


    function tutor_bp_setting_enrolled_courses_list() {
        $('ul.tutor-bp-enrolled-course-list').each(function () {
            var $that = $(this);
            var $li = $that.find(' > li');
            var itemShow = 3;

            if ($li.length > itemShow) {
                var plusCourseCount = $li.length - itemShow;
                $li.each(function (liIndex, liItem) {
                    var $liItem = $(this);

                    if (liIndex >= itemShow) {
                        $liItem.hide();
                    }
                });

                var infoHtml = '<a href="javascript:;" class="tutor_bp_plus_courses"><strong>+' + plusCourseCount + ' More </strong></a> Courses';
                $that.closest('.tutor-bp-enrolled-courses-wrap').find('.thread-participant-enrolled-info').html(infoHtml);
            }

            $that.show();
        });
    }
    tutor_bp_setting_enrolled_courses_list();

    $(document).on('click', 'a.tutor_bp_plus_courses', function (e) {
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
    //$(document).on('click', '.tutor-dropbtn', function (e) {
    $('.tutor-dropbtn').click(function () {

        var $content = $(this).parent().find(".tutor-dropdown-content");
        $content.slideToggle(100);
    })

    $(document).on('click', function (e) {
        var container = $(".tutor-dropdown");
        var $content = container.find('.tutor-dropdown-content');
        // if the target of the click isn't the container nor a descendant of the container
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            $content.slideUp(100);
        }
    });

    /**
     * Show hide is course public checkbox (frontend dashboard editor)
     *
     * @since  v.1.7.2
     */
    var price_type = $('.tutor-frontend-builder-course-price [name="tutor_course_price_type"]');
    if (price_type.length == 0) {
        $('#_tutor_is_course_public_meta_checkbox').show();
    }
    else {
        price_type.change(function () {
            if ($(this).prop('checked')) {
                var method = $(this).val() == 'paid' ? 'hide' : 'show';
                $('#_tutor_is_course_public_meta_checkbox')[method]();
            }
        }).trigger('change');
    }

    /**
     * Withdrawal page tooltip
     *
     * @since  v.1.7.4
     */
    // Fully accessible tooltip jQuery plugin with delegation.
    // Ideal for view containers that may re-render content.
    (function ($) {
        $.fn.tutor_tooltip = function () {
            this

                // Delegate to tooltip, Hide if tooltip receives mouse or is clicked (tooltip may stick if parent has focus)
                .on('mouseenter click', '.tooltip', function (e) {
                    e.stopPropagation();
                    $(this).removeClass('isVisible');
                })
                // Delegate to parent of tooltip, Show tooltip if parent receives mouse or focus
                .on('mouseenter focus', ':has(>.tooltip)', function (e) {
                    if (!$(this).prop('disabled')) { // IE 8 fix to prevent tooltip on `disabled` elements
                        $(this)
                            .find('.tooltip')
                            .addClass('isVisible');
                    }
                })
                // Delegate to parent of tooltip, Hide tooltip if parent loses mouse or focus
                .on('mouseleave blur keydown', ':has(>.tooltip)', function (e) {
                    if (e.type === 'keydown') {
                        if (e.which === 27) {
                            $(this)
                                .find('.tooltip')
                                .removeClass('isVisible');
                        }
                    } else {
                        $(this)
                            .find('.tooltip')
                            .removeClass('isVisible');
                    }
                });
            return this;
        };
    }(jQuery));

    // Bind event listener to container element
    jQuery('.tutor-tooltip-inside').tutor_tooltip();



    /**
     * Manage course filter
     *
     * @since  v.1.7.2
     */
    var filter_container = $('.tutor-course-filter-container form');
    var loop_container = $('.tutor-course-filter-loop-container');
    var filter_modifier = {};

    // Sidebar checkbox value change
    filter_container.on('submit', function (e) {
        e.preventDefault();
    })
        .find('input').change(function (e) {

            var filter_criteria = Object.assign(filter_container.serializeObject(), filter_modifier);
            filter_criteria.action = 'tutor_course_filter_ajax';

            loop_container.html('<center><img src="' + window._tutorobject.loading_icon_url + '"/></center>');
            $(this).closest('form').find('.tutor-clear-all-filter').show();

            $.ajax({
                url: window._tutorobject.ajaxurl,
                type: 'POST',
                data: filter_criteria,
                success: function (r) {
                    loop_container.html(r).find('.tutor-pagination-wrap a').each(function () {
                        $(this).attr('data-href', $(this).attr('href')).attr('href', '#');
                    });
                }
            })
        });

    // Alter pagination
    loop_container.on('click', '.tutor-pagination-wrap a', function (e) {
        var url = $(this).data('href') || $(this).attr('href');

        if (url) {
            url = new URL(url);
            var page = url.searchParams.get("paged");

            if (page) {
                e.preventDefault();
                filter_modifier.page = page;
                filter_container.find('input:first').trigger('change');
            }
        }
    });

    // Alter sort filter
    loop_container.on('change', 'select[name="tutor_course_filter"]', function () {
        filter_modifier.tutor_course_filter = $(this).val();
        filter_container.find('input:first').trigger('change');
    });

    // Refresh page after coming back to course archive page from cart
    var archive_loop = $('.tutor-course-loop');
    if (archive_loop.length > 0) {
        window.sessionStorage.getItem('tutor_refresh_archive') === 'yes' ? window.location.reload() : 0;
        window.sessionStorage.removeItem('tutor_refresh_archive');
        archive_loop.on('click', '.tutor-loop-cart-btn-wrap', function () {
            window.sessionStorage.setItem('tutor_refresh_archive', 'yes');
        });
    }

    //warn user before leave page if quiz is running
    const crossQuiz = document.querySelector('.tutor-topbar-cross-icon');
    if (null !== crossQuiz && typeof crossQuiz !== 'undefined') {
        crossQuiz.addEventListener('click', function (event) {
            const target = event.target;
            const targetTag = target.tagName
            const parentTag = target.parentElement.tagName;

            if ($tutor_quiz_time_update.length > 0 && $tutor_quiz_time_update.html() != 'EXPIRED') {
                if (targetTag === 'A' || parentTag === 'A') {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    let popup;

                    let data = {
                        title: __('Abandon Quiz?', 'tutor'),
                        description: __('Do you want to abandon this quiz? The quiz will be submitted partially up to this question if you leave this page.', 'tutor'),
                        buttons: {
                            keep: {
                                title: __('Yes, leave quiz', 'tutor'),
                                id: 'leave',
                                class: 'tutor-btn tutor-is-outline tutor-is-default',
                                callback: function () {

                                    var formData = $('form#tutor-answering-quiz').serialize() + '&action=' + 'tutor_quiz_abandon';
                                    $.ajax({
                                        url: window._tutorobject.ajaxurl,
                                        type: 'POST',
                                        data: formData,
                                        beforeSend: function () {
                                            document.querySelector("#tutor-popup-leave").innerHTML = __('Leaving...', 'tutor');
                                        },
                                        success: function (response) {
                                            if (response.success) {
                                                if (target.href == undefined) {
                                                    location.href = target.parentElement.href
                                                } else {
                                                    location.href = target.href
                                                }
                                            } else {
                                                alert(__('Something went wrong', 'tutor'));
                                            }
                                        },
                                        error: function () {
                                            alert(__('Something went wrong', 'tutor'));
                                            popup.remove();
                                        }
                                    });
                                }
                            },
                            reset: {
                                title: __('Stay here', 'tutor'),
                                id: 'reset',
                                class: 'tutor-btn',
                                callback: function () {
                                    popup.remove();
                                }
                            },
                        }
                    };

                    popup = new window.tutor_popup($, '', 40).popup(data);
                }
            }
        });
    }
    /* Disable start quiz button  */
    $('body').on('submit', 'form#tutor-start-quiz', function () {
        $(this).find('button').prop('disabled', true);
    });
});
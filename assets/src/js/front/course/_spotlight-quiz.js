window.jQuery(document).ready($ => {
    const { __ } = window.wp.i18n;

    // Currently only these types of question supports answer reveal mode.
    const revealModeSupportedQuestions = ['true_false', 'single_choice', 'multiple_choice'];

    let quiz_options = _tutorobject.quiz_options
    let interactions = new Map();

    $('.tutor-sortable-list').on('sortchange', handleSort);

    function handleSort(e, ui) {
        let question_id = parseInt($(this).closest('.quiz-attempt-single-question').attr('id').match(/\d+/)[0], 10);

        if (!interactions.get(question_id)) {
            interactions.set(question_id, true);
        }
    }


    var revealTimeoutId = null;

    function clearRevealTimeout() {
        if (revealTimeoutId !== null) {
            clearTimeout(revealTimeoutId);
            revealTimeoutId = null;
        }
        $('.tutor-quiz-btn-countdown').removeClass('tutor-quiz-btn-countdown');
    }

    function get_reveal_wait_time() {
        const quizLevelWait = Number(quiz_options.answers_reveal_duration || 0);
        if (quizLevelWait > 0) {
            return quizLevelWait * 1000;
        }

        return Number(_tutorobject.quiz_answer_display_time) || 2000;
    }

    function is_reveal_mode() {
        return Number(quiz_options.enable_answer_reveal || 0) === 1;
    }

    function get_quiz_layout_view() {
        return _tutorobject.quiz_options.question_layout_view
    }

    function has_pagination_enabled() {
        return Number(_tutorobject.quiz_options.enable_pagination || 0) === 1;
    }

    function get_hint_markup(text) {
        return `<span class="tutor-quiz-answer-single-info tutor-color-success tutor-mt-8">
            <i class="tutor-icon-mark tutor-color-success" aria-hidden="true"></i>
            ${text}
        </span>`
    }

    function feedback_response($question_wrap, correct_answers, explanation) {
        if (get_quiz_layout_view() !== 'question_below_each_other') {
            $question_wrap.find('.tutor-quiz-answer-single-info').remove();
        }

        $question_wrap.find('.tutor-quiz-answer-single').removeClass('tutor-quiz-answer-single-correct tutor-quiz-answer-single-incorrect');

        var $inputs = $question_wrap.find('input');
        var correct_ids = Array.isArray(correct_answers) ? correct_answers.map(function (id) { return String(id); }) : [];

        if (correct_ids.length) {
            $inputs.each(function () {
                var $input = $(this);
                var $type = $input.attr('type');

                if ($type === 'radio' || $type === 'checkbox') {
                    var isTrue = correct_ids.indexOf(String($input.val())) > -1;
                    var checked = $input.is(':checked');

                    if (isTrue) {
                        $input
                            .closest('.tutor-quiz-answer-single')
                            .addClass('tutor-quiz-answer-single-correct')
                            .append(get_hint_markup(__('Correct Answer', 'tutor')))
                            .find('.tutor-quiz-answer-single-info:eq(1)')
                            .remove();
                    } else {
                        if (checked) {
                            $input.closest('.tutor-quiz-answer-single').addClass('tutor-quiz-answer-single-incorrect');
                        }
                    }
                }
            });
        }

        $question_wrap.attr('data-revealed', '1');
        $question_wrap.find('.quiz-question-ans-choice-area').css('pointer-events', 'none');
        $inputs.prop('disabled', true);

        if (explanation) {
            var $exp = $question_wrap.find('.tutor-quiz-explanation-wrapper');
            $exp.find('.tutor-quiz-explanation-content').html(explanation);
            $exp.removeClass('tutor-d-none');
        }

        return true;
    }

    /**
     *
     * Validate whether draggable required question has all answers
     * 
     * @since 2.7.2
     * @param {Object} required_answer_wrap 
     * @returns {boolean}
     */
    function draggableValidation(required_answer_wrap) {
        let validation = true;
        let element = required_answer_wrap[0];
        let dropzones = $(element).find('.tutor-dropzone');
        if (dropzones.length > 0) {
            Object.values(dropzones).forEach((dropzone) => {
                if (dropzone instanceof Element && dropzone.classList.contains('tutor-dropzone')) {
                    if ($(dropzone).has('input').length === 0) {
                        validation = false;
                    }
                }
            })
        }
        return validation;
    }

    /**
     * Quiz Validation Helper
     *
     * @since v.1.6.1
     */

    function tutor_quiz_validation($question_wrap, validated) {

        var $required_answer_wrap = $question_wrap.find('.quiz-answer-required');

        if ($required_answer_wrap.length) {

            let question_id = parseInt($question_wrap.attr('id').match(/\d+/)[0], 10);
            let interaction_times = interactions.get(question_id);
            let tutor_draggable = $question_wrap.find('.tutor-draggable');
            let is_sortable = $question_wrap.find('.ui-sortable');

            /**
             * Radio field validation
             *
             * @type {jQuery}
             *
             * @since v.1.6.1
             */
            var $inputs = $required_answer_wrap.find('input');
            if ($inputs.length) {
                var $type = $inputs.attr('type');
                if ($type === 'radio') {
                    if ($required_answer_wrap.find('input[type="radio"]:checked').length == 0) {
                        $question_wrap.find('.answer-help-block').html(`<p style="color: #dc3545">${__('Please select an option to answer', 'tutor')}</p>`);
                        validated = false;
                    }
                } else if ($type === 'checkbox') {
                    if ($required_answer_wrap.find('input[type="checkbox"]:checked').length == 0) {
                        $question_wrap.find('.answer-help-block').html(`<p style="color: #dc3545">${__('Please select at least one option to answer.', 'tutor')}</p>`);
                        validated = false;
                    }
                } else if ($type === 'text') {
                    //Fill in the gaps if many, validation all
                    $inputs.each(function (index, input) {
                        if (!$(input).val().trim().length) {
                            $question_wrap.find('.answer-help-block').html(`<p style="color: #dc3545">${__('The answer for this question is required', 'tutor')}</p>`);
                            validated = false;
                        }
                    });
                }

            }
            if ($required_answer_wrap.find('textarea').length) {
                if ($required_answer_wrap.find('textarea').val().trim().length < 1) {
                    $question_wrap.find('.answer-help-block').html(`<p style="color: #dc3545">${__('The answer for this question is required', 'tutor')}</p>`);
                    validated = false;
                }
            }
            //Validate draggable quiz questions
            if (tutor_draggable.length) {
                let isAnswered = draggableValidation($required_answer_wrap);
                if (!isAnswered) {
                    $question_wrap.find('.answer-help-block').html(`<p style="color: #dc3545">${__('The answer for this question is required', 'tutor')}</p>`);
                    validated = false;
                }

            }

            //Validate sortable quiz questions
            if (interaction_times === undefined && is_sortable.length) {
                $question_wrap.find('.answer-help-block').html(`<p style="color: #dc3545">${__('The answer for this question is required', 'tutor')}</p>`);
                validated = false;
            }
        }

        return validated;
    }

    /**
     * Quiz view
     * @date 22 Feb, 2019
     * @since v.1.0.0
     */
    $('.tutor-quiz-next-btn-all').prop('disabled', false);
    $('.quiz-attempt-single-question input').filter('[type="radio"], [type="checkbox"]').change(function () {
        if ($('.tutor-quiz-time-expired').length === 0) {
            $('.tutor-quiz-next-btn-all').prop('disabled', false);
        }
    });

    $(document).on('click', '.tutor-quiz-answer-next-btn, .tutor-quiz-answer-previous-btn', function (e) {
        e.preventDefault();

        let counter_el = $('.tutor-quiz-question-counter>span:first-child');
        let current_question = parseInt($(this).closest('[data-question_index]').data('question_index'));
        // Show previous quiz if press previous button
        if ($(this).hasClass('tutor-quiz-answer-previous-btn')) {
            clearRevealTimeout();
            var $prev = $(this).closest('.quiz-attempt-single-question').hide().prev();
            $prev.show();
            if ($prev.attr('data-revealed') === '1') {
                $prev.find('.tutor-quiz-next-btn-all').prop('disabled', false);
            }
            counter_el.text(current_question - 1);
            return;
        }

        var $that = $(this);
        var $question_wrap = $that.closest('.quiz-attempt-single-question');
        var question_id = parseInt($that.closest('.quiz-attempt-single-question').attr('id').match(/\d+/)[0], 10);
        var next_question_id = $that.closest('.quiz-attempt-single-question').attr('data-next-question-id');

        /**
         * Validating required answer
         * @type {jQuery}
         *
         * @since v.1.6.1
         */
        var validated = true;
        validated = tutor_quiz_validation($question_wrap, validated);
        if (!validated) {
            return;
        }

        var moveToNext = function () {
            clearRevealTimeout();
            if (next_question_id) {
                var $nextQuestion = $(next_question_id);
                if ($nextQuestion && $nextQuestion.length) {
                    $('.quiz-attempt-single-question').hide();
                    $nextQuestion.show();

                    if (has_pagination_enabled() && $('.tutor-quiz-questions-pagination').length) {
                        $('.tutor-quiz-question-paginate-item').removeClass('active');
                        $('.tutor-quiz-questions-pagination a[href="' + next_question_id + '"]').addClass('active');
                    }

                    if ($nextQuestion.attr('data-revealed') === '1') {
                        $nextQuestion.find('.tutor-quiz-next-btn-all').prop('disabled', false);
                    }

                    counter_el.text(current_question + 1);
                }
            }
        };

        if (
            is_reveal_mode() &&
            get_quiz_layout_view() === 'single_question' &&
            revealModeSupportedQuestions.includes($question_wrap.data('question-type'))
        ) {
            if ($question_wrap.attr('data-revealed') === '1') {
                moveToNext();
                return;
            }
            var $form = $('form#tutor-answering-quiz');
            var attemptId = $form.find('input[name="attempt_id"]').val();
            var quizId = $form.find('input[name="quiz_id"]').val();
            var checkedAnswers = [];
            $question_wrap.find('input[type="radio"]:checked, input[type="checkbox"]:checked').each(function () {
                checkedAnswers.push($(this).val());
            });

            if (!checkedAnswers.length) {
                moveToNext();
                return;
            }

            $that.prop('disabled', true).addClass('is-loading');

            $.ajax({
                url: window._tutorobject.ajaxurl,
                type: 'POST',
                data: {
                    action: 'tutor_quiz_check_answer',
                    _tutor_nonce: window._tutorobject._tutor_nonce,
                    attempt_id: attemptId,
                    quiz_id: quizId,
                    question_id: question_id,
                    answers: checkedAnswers,
                },
                success: function (res) {
                    if (res && res.data) {
                        feedback_response($question_wrap, res.data.correct_answer_ids, res.data.answer_explanation);
                    }
                },
                complete: function () {
                    $that.prop('disabled', false).removeClass('is-loading');
                    clearRevealTimeout();
                    $that.addClass('tutor-quiz-btn-countdown').css('--reveal-wait-duration', get_reveal_wait_time() + 'ms');
                    revealTimeoutId = setTimeout(function () {
                        revealTimeoutId = null;
                        $that.removeClass('tutor-quiz-btn-countdown');
                        moveToNext();
                    }, get_reveal_wait_time());
                },
            });
            return;
        }

        moveToNext();
    });

    $(document).on('click', '.tutor-quiz-question-paginate-item', function (e) {
        e.preventDefault();
        clearRevealTimeout();
        var $that = $(this);
        var $question = $($that.attr('href'));
        $('.quiz-attempt-single-question').hide();
        $question.show();
        if ($question.attr('data-revealed') === '1') {
            $question.find('.tutor-quiz-next-btn-all').prop('disabled', false);
        }

        //Active Class
        $('.tutor-quiz-question-paginate-item').removeClass('active');
        $that.addClass('active');

        var question_index = parseInt($question.data('question_index'), 10);
        if (!isNaN(question_index)) {
            $('.tutor-quiz-question-counter>span:first-child').text(question_index);
        }
    });

    /**
     * Limit Short Answer Question Type
     */
    $(document).on('keyup', 'textarea.question_type_short_answer, textarea.question_type_open_ended', function (e) {
        var $that = $(this);
        var value = $that.val();
        var limit = $that.hasClass('question_type_short_answer')
            ? _tutorobject.quiz_options.short_answer_characters_limit
            : _tutorobject.quiz_options.open_ended_answer_characters_limit;

        if (!limit) {
            return;
        }

        var remaining = limit - value.length;

        if (remaining < 1) {
            $that.val(value.substr(0, limit));
            remaining = 0;
        }

        $that
            .closest('.quiz-attempt-single-question')
            .find('.characters_remaining')
            .html(remaining);
    });

    $(document).on('submit', '#tutor-answering-quiz', function (e) {
        e.preventDefault();

        let $questions_wrap = $('.quiz-attempt-single-question');
        let quizSubmitBtn = document.querySelector('.tutor-quiz-submit-btn');
        let submitted_form = $(e.target)

        let quiz_validated = true;
        let feedback_validated = true;
        let hasAnyRevealModeQuestion = false;

        if ($questions_wrap.length) {
            $questions_wrap.each(function (index, question) {
                quiz_validated = tutor_quiz_validation($(question), quiz_validated);

                if (revealModeSupportedQuestions.includes($(question).data('question-type'))) {
                    hasAnyRevealModeQuestion = true;
                }
            });
        }
        //If auto submit option is enabled after time expire submit current progress
        if (_tutorobject.quiz_options.quiz_when_time_expires === 'auto_submit' && $('#tutor-quiz-time-update').hasClass('tutor-quiz-time-expired')) {
            quiz_validated = true;
        }

        if (quiz_validated) {
            let wait = 500
            if (is_reveal_mode() && get_quiz_layout_view() === 'question_below_each_other' && hasAnyRevealModeQuestion) {
                wait = get_reveal_wait_time()
                submitted_form.find(':submit').addClass('is-loading').attr('disabled', 'disabled')
            }
            setTimeout(() => {
                $('#tutor-answering-quiz').find('input, select, textarea').prop('disabled', false);
                e.target.submit();
            }, wait);
        } else {
            if (quizSubmitBtn) {
                quizSubmitBtn.classList.remove('is-loading')
                quizSubmitBtn.disabled = false;
            }
        }
    });

    $(".tutor-quiz-submit-btn").click(function (event) {
        event.preventDefault();
        var $btn = $(this);
        var $form = $("#tutor-answering-quiz");
        var $questions_wrap = $('.quiz-attempt-single-question');

        var validated = true;
        $questions_wrap.each(function (index, question) {
            validated = tutor_quiz_validation($(question), validated);
        });
        if (!validated) {
            return;
        }

        const lastQuestion = $questions_wrap[$questions_wrap.length - 1];
        const $lastQuestion = $(lastQuestion);
        const lastQuestionType = $lastQuestion.data('question-type');

        if (
            is_reveal_mode() &&
            get_quiz_layout_view() === 'single_question' &&
            revealModeSupportedQuestions.includes(lastQuestionType)
        ) {
            if ($lastQuestion.attr('data-revealed') === '1') {
                clearRevealTimeout();
                $btn.prop('disabled', true).addClass('is-loading');
                $('#tutor-answering-quiz').find('input, select, textarea').prop('disabled', false);
                document.getElementById('tutor-answering-quiz').submit();
                return;
            }

            var attemptId = $form.find('input[name="attempt_id"]').val();
            var quizId = $form.find('input[name="quiz_id"]').val();
            var question_id = parseInt($lastQuestion.attr('id').match(/\d+/)[0], 10);
            var checkedAnswers = [];
            $lastQuestion.find('input[type="radio"]:checked, input[type="checkbox"]:checked').each(function () {
                checkedAnswers.push($(this).val());
            });

            if (!checkedAnswers.length) {
                clearRevealTimeout();
                $btn.addClass('is-loading');
                $('#tutor-answering-quiz').find('input, select, textarea').prop('disabled', false);
                document.getElementById('tutor-answering-quiz').submit();
                return;
            }

            $btn.prop('disabled', true).addClass('is-loading');

            $.ajax({
                url: window._tutorobject.ajaxurl,
                type: 'POST',
                data: {
                    action: 'tutor_quiz_check_answer',
                    _tutor_nonce: window._tutorobject._tutor_nonce,
                    attempt_id: attemptId,
                    quiz_id: quizId,
                    question_id: question_id,
                    answers: checkedAnswers,
                },
                success: function (res) {
                    if (res && res.data) {
                        feedback_response($lastQuestion, res.data.correct_answer_ids, res.data.answer_explanation);
                    }
                },
                complete: function () {
                    $btn.prop('disabled', false).removeClass('is-loading');
                    clearRevealTimeout();
                    $btn.addClass('tutor-quiz-btn-countdown').css('--reveal-wait-duration', get_reveal_wait_time() + 'ms');
                    revealTimeoutId = setTimeout(function () {
                        revealTimeoutId = null;
                        $btn.removeClass('tutor-quiz-btn-countdown');
                        $btn.prop('disabled', true).addClass('is-loading');
                        $('#tutor-answering-quiz').find('input, select, textarea').prop('disabled', false);
                        document.getElementById('tutor-answering-quiz').submit();
                    }, get_reveal_wait_time());
                },
            });
            return;
        }

        $btn.prop('disabled', true).addClass('is-loading');
        $('#tutor-answering-quiz').find('input, select, textarea').prop('disabled', false);
        document.getElementById('tutor-answering-quiz').submit();
    });

    //warn user before leave page if quiz is running
    var $tutor_quiz_time_update = $('#tutor-quiz-time-update');
    // @todo: check the button class functionality

    $(document).on('click', 'a', function (event) {
        // if user click on ask question then return, no warning.
        if (event.target.classList.contains('sidebar-ask-new-qna-btn') || event.target.classList.contains('tutor-quiz-question-paginate-item')) {
            return;
        }

        if ($tutor_quiz_time_update.length > 0 && $tutor_quiz_time_update.text() != 'EXPIRED') {
            event.preventDefault();
            event.stopImmediatePropagation();
            let popup;

            let data = {
                title: __('Abandon Quiz?', 'tutor'),
                description: __('Do you want to abandon this quiz? The quiz will be submitted partially up to this question if you leave this page.', 'tutor'), // Don't break line in favour of pot file generating
                buttons: {
                    keep: {
                        title: __('Yes, leave quiz', 'tutor'),
                        id: 'leave',
                        class: 'tutor-btn tutor-btn-outline-primary',
                        callback: function () {
                            clearRevealTimeout();
                            $('#tutor-answering-quiz').find('input, select, textarea').prop('disabled', false);
                            var formData = $('form#tutor-answering-quiz').serialize() + '&action=' + 'tutor_quiz_abandon';
                            $.ajax({
                                url: window._tutorobject.ajaxurl,
                                type: 'POST',
                                data: formData,
                                beforeSend: function () {
                                    document.querySelector('#tutor-popup-leave').innerHTML = __('Leaving...', 'tutor');
                                },
                                success: function (response) {
                                    if (response.success) {
                                        location.reload(true);
                                    } else {
                                        alert(__('Something went wrong', 'tutor'));
                                    }
                                },
                                error: function () {
                                    alert(__('Something went wrong', 'tutor'));
                                    popup.find('[data-tutor-modal-close]').click();
                                },
                            });
                        },
                    },
                    reset: {
                        title: __('Stay here', 'tutor'),
                        id: 'reset',
                        class: 'tutor-btn tutor-btn-primary tutor-ml-20',
                        callback: function () {
                            popup.find('[data-tutor-modal-close]').click();
                        },
                    },
                },
            };

            popup = new window.tutor_popup($, '').popup(data);
        }
    });

    /* Disable start quiz button  */
    $('body').on('submit', 'form#tutor-start-quiz', function () {
        $(this)
            .find('button')
            .prop('disabled', true);
    });
});

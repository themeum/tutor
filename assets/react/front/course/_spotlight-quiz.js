window.jQuery(document).ready($ => {
    const { __ } = window.wp.i18n;

    let quiz_options = _tutorobject.quiz_options
    let interactions = new Map();

    $('.tutor-sortable-list').on('sortchange', handleSort);

    function handleSort(e, ui) {
        let question_id = parseInt($(this).closest('.quiz-attempt-single-question').attr('id').match(/\d+/)[0], 10);

        if (!interactions.get(question_id)) {
            interactions.set(question_id, true);
        }
    }


    function get_reveal_wait_time() {
        return Number(_tutorobject.quiz_answer_display_time) || 2000;
    }

    function is_reveal_mode() {
        return 'reveal' === quiz_options.feedback_mode
    }

    function get_quiz_layout_view() {
        return _tutorobject.quiz_options.question_layout_view
    }

    function get_hint_markup(text) {
        return `<span class="tutor-quiz-answer-single-info tutor-color-success tutor-mt-8">
            <i class="tutor-icon-mark tutor-color-success" area-hidden="true"></i>
            ${text}
        </span>`
    }

    function feedback_response($question_wrap) {
        var goNext = false;

        // Prepare answer array
        var quiz_answers = JSON.parse(window.tutor_quiz_context.split('').reverse().join(''));
        !Array.isArray(quiz_answers) ? quiz_answers = [] : 0;

        if (get_quiz_layout_view() !== 'question_below_each_other') {
            $('.tutor-quiz-answer-single-info').remove();
        }

        $('.tutor-quiz-answer-single').removeClass('tutor-quiz-answer-single-correct tutor-quiz-answer-single-incorrect');

        var validatedTrue = true;
        var $inputs = $question_wrap.find('input');
        var $checkedInputs = $question_wrap.find('input[type="radio"]:checked, input[type="checkbox"]:checked');

        if (is_reveal_mode()) {

            // Loop through every single checked radio/checkbox input field
            $checkedInputs.each(function () {
                var $input = $(this);
                var isTrue = quiz_answers.indexOf($input.val())>-1; // $input.attr('data-is-correct') == '1';

                // And check if the answer is correct
                if (!isTrue) {
                    validatedTrue = false;
                }
            });

            // Loop through all the inputs regardless of correct/incorrect
            $inputs.each(function () {
                var $input = $(this);
                var $type = $input.attr('type');

                // Reveal mode feature is currently available for only radio and checkbox type answers
                if ($type === 'radio' || $type === 'checkbox') {
                    var isTrue = quiz_answers.indexOf($input.val())>-1; // $input.attr('data-is-correct') == '1';
                    var checked = $input.is(':checked');

                    if (isTrue) {
                        $input
                            .closest('.tutor-quiz-answer-single')
                            .addClass('tutor-quiz-answer-single-correct')
                            .append(get_hint_markup(__('Correct Answer', 'tutor')))
                            .find('.tutor-quiz-answer-single-info:eq(1)')
                            .remove();
                    } else {
                        if ($input.prop("checked")) {
                            $input.closest('.tutor-quiz-answer-single').addClass('tutor-quiz-answer-single-incorrect');
                        }
                    }

                    if (isTrue && !checked) {
                        $input.attr('disabled','disabled');
                        validatedTrue = false;
                        goNext = true;
                    }
                }
            });
        }

        if (validatedTrue) {
            goNext = true;
        }

        return goNext;
    }

    /**
     *
     * Validate whether draggable required question has all answers
     * 
     * @since 2.8.0
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

    function tutor_quiz_validation($question_wrap,validated) {

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
    $('.quiz-attempt-single-question input').filter('[type="radio"], [type="checkbox"]').change(function(){
        $('.tutor-quiz-next-btn-all').prop('disabled', false);
    });

    $(document).on('click', '.tutor-quiz-answer-next-btn, .tutor-quiz-answer-previous-btn', function (e) {
        e.preventDefault();

        let counter_el = $('.tutor-quiz-question-counter>span:first-child');
        let current_question = parseInt($(this).closest('[data-question_index]').data('question_index'));
        // Show previous quiz if press previous button
        if ($(this).hasClass('tutor-quiz-answer-previous-btn')) {
            $(this).closest('.quiz-attempt-single-question').hide().prev().show();
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
        validated = tutor_quiz_validation($question_wrap,validated);
        if (!validated) {
            return;
        }

        var feedBackNext = feedback_response($question_wrap);
        /**
         * If not reveal mode then check feedback response
         * 
         * Since validation already checked above, now user's ans is correct 
         * or not they should move forward. In reveal mode if feedback response is false
         * then it was freezing the process.
         * 
         * @since v2.0.9
         */
        if (!is_reveal_mode()) {
            if (!feedBackNext) {
                return;
            }
        }


        if (next_question_id) {
            var $nextQuestion = $(next_question_id);
            if ($nextQuestion && $nextQuestion.length) {
                /**
                 * check if reveal mode wait for 500ms then
                 * hide question so that correct answer reveal
                 * @since 1.8.10
                 */

                if (is_reveal_mode()) {
                    setTimeout(() => {
                        $('.quiz-attempt-single-question').hide();
                        $nextQuestion.show();
                    }, get_reveal_wait_time());
                } else {
                    $('.quiz-attempt-single-question').hide();
                    $nextQuestion.show();
                }



                /**
                 * If pagination exists, set active class
                 */

                if ($('.tutor-quiz-questions-pagination').length) {
                    $('.tutor-quiz-question-paginate-item').removeClass('active');
                    $('.tutor-quiz-questions-pagination a[href="' + next_question_id + '"]').addClass('active');
                }

                // Increase counter
                counter_el.text(current_question + 1);
            }
        }
    });

    $(document).on('click', '.tutor-quiz-question-paginate-item', function(e) {
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
    $(document).on('keyup', 'textarea.question_type_short_answer, textarea.question_type_open_ended', function(e) {
        var $that = $(this);
        var value = $that.val();
        var limit = $that.hasClass('question_type_short_answer')
            ? _tutorobject.quiz_options.short_answer_characters_limit
            : _tutorobject.quiz_options.open_ended_answer_characters_limit;

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
        let quizSubmitBtn   = document.querySelector('.tutor-quiz-submit-btn');
        let submitted_form  = $(e.target)

        let quiz_validated      = true;
        let feedback_validated  = true;

        if ($questions_wrap.length) {
            $questions_wrap.each(function (index, question) {
                quiz_validated      = tutor_quiz_validation($(question), quiz_validated);
                feedback_validated  = feedback_response($(question));
            });
        }
        //If auto submit option is enabled after time expire submit current progress
        if (_tutorobject.quiz_options.quiz_when_time_expires === 'auto_submit' && $('#tutor-quiz-time-update').hasClass('tutor-quiz-time-expired')) {
            quiz_validated     = true;
            feedback_validated = true;
        }

        if (quiz_validated && feedback_validated) {
            let wait = 500
            if (is_reveal_mode() && get_quiz_layout_view() === 'question_below_each_other') {
                wait = get_reveal_wait_time()
                submitted_form.find(':submit').addClass('is-loading').attr('disabled', 'disabled')
            }
            setTimeout(() => { e.target.submit() }, wait);
        } else {
            if (quizSubmitBtn) {
                quizSubmitBtn.classList.remove('is-loading')
                quizSubmitBtn.disabled = false;
            }
        }
    });

    $(".tutor-quiz-submit-btn").click(function(event) {
        event.preventDefault();

        if (is_reveal_mode()) {
            var $questions_wrap = $('.quiz-attempt-single-question');
            var validated = true;
            if ($questions_wrap.length) {
                $questions_wrap.each(function (index, question) {
                    validated = tutor_quiz_validation($(question));
                    validated = feedback_response($(question));

                });
            }
            $(this).attr('disabled', 'disabled')
            setTimeout(() => {
                $(this).addClass('is-loading');
                $("#tutor-answering-quiz").submit();
            }, get_reveal_wait_time());

        } else {
            $(this).attr('disabled', 'disabled').addClass('is-loading');
            $("#tutor-answering-quiz").submit();
        }

    });

    //warn user before leave page if quiz is running
    var $tutor_quiz_time_update = $('#tutor-quiz-time-update');
    // @todo: check the button class functionality

    $(document).on('click', 'a',  function(event) {
        const href = $(this).attr('href');
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
                        callback: function() {
                            var formData = $('form#tutor-answering-quiz').serialize() + '&action=' + 'tutor_quiz_abandon';
                            $.ajax({
                                url: window._tutorobject.ajaxurl,
                                type: 'POST',
                                data: formData,
                                beforeSend: function() {
                                    document.querySelector('#tutor-popup-leave').innerHTML = __('Leaving...', 'tutor');
                                },
                                success: function(response) {
                                    if (response.success) {
                                        location.href = href;
                                    } else {
                                        alert(__('Something went wrong', 'tutor'));
                                    }
                                },
                                error: function() {
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
                        callback: function() {
                            popup.find('[data-tutor-modal-close]').click();
                        },
                    },
                },
            };

            popup = new window.tutor_popup($, '').popup(data);
        }
    });

    /* Disable start quiz button  */
    $('body').on('submit', 'form#tutor-start-quiz', function() {
        $(this)
            .find('button')
            .prop('disabled', true);
    });
});
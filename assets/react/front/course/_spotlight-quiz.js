window.jQuery(document).ready($=>{
    const {__} = window.wp.i18n;
    
    function feedback_response($question_wrap) {
        var goNext = false;

        // Prepare answer array
        var quiz_answers = JSON.parse(window.tutor_quiz_context.split('').reverse().join(''));
        !Array.isArray(quiz_answers) ? quiz_answers = [] : 0;

        // Evaluate result
        var feedBackMode = $question_wrap.attr('data-quiz-feedback-mode');
        $('.wrong-right-text').remove();
        $('.quiz-answer-input-bottom').removeClass('wrong-answer right-answer');

        var validatedTrue = true;
        var $inputs = $question_wrap.find('input');
        var $checkedInputs = $question_wrap.find('input[type="radio"]:checked, input[type="checkbox"]:checked');

        if (feedBackMode === 'retry') {
            $checkedInputs.each(function () {
                var $input = $(this);

                var $type = $input.attr('type');
                if ($type === 'radio' || $type === 'checkbox') {
                    var isTrue = quiz_answers.indexOf($input.val()) > -1; // $input.attr('data-is-correct') == '1';
                    if (!isTrue) {
                        if ($input.prop("checked")) {
                            $input.closest('.quiz-answer-input-bottom').addClass('wrong-answer').append(`<span class="wrong-right-text"><i class="tutor-icon-line-cross"></i> ${__('Incorrect, Please try again', 'tutor')}</span>`);
                        }
                        validatedTrue = false;
                    }
                }
            });

            $inputs.each(function () {
                var $input = $(this);
                var $type = $input.attr('type');
                if ($type === 'checkbox') {
                    var isTrue = quiz_answers.indexOf($input.val()) > -1; // $input.attr('data-is-correct') == '1';
                    var checked = $input.is(':checked');

                    if (isTrue && !checked) {
                        $question_wrap.find('.answer-help-block').html(`<p style="color: #dc3545">${__('More answer for this question is required', 'tutor')}</p>`);
                        validatedTrue = false;
                    }
                }
            });

        } else if (feedBackMode === 'reveal') {

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
                            .closest('.quiz-question-ans-choice')
                            .addClass('right-answer')
                            .append(`<span class="wrong-right-text">
                                        <i class="tutor-icon-checkbox-pen-outline"></i>
                                        ${__('Correct Answer', 'tutor')}
                                    </span>`)
                            .find('.wrong-right-text:eq(1)')
                            .remove();
                    } else {
                        if ($input.prop("checked")) {
                            $input.closest('.quiz-answer-input-bottom').addClass('wrong-answer');
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
     * Quiz Validation Helper
     *
     * @since v.1.6.1
     */

     function tutor_quiz_validation($question_wrap) {
        var validated = true;

        var $required_answer_wrap = $question_wrap.find('.quiz-answer-required');

        if ($required_answer_wrap.length) {
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
        }

        return validated;
    }

    /**
     * Quiz view
     * @date 22 Feb, 2019
     * @since v.1.0.0
     */

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
        /**
         * Validating required answer
         * @type {jQuery}
         *
         * @since v.1.6.1
         */

        var validated = tutor_quiz_validation($question_wrap);
        if (!validated) {
            return;
        }

        var feedBackNext = feedback_response($question_wrap);
        if (!feedBackNext) {
            return;
        }

        var question_id = parseInt($that.closest('.quiz-attempt-single-question').attr('id').match(/\d+/)[0], 10);
        var next_question_id = $that.closest('.quiz-attempt-single-question').attr('data-next-question-id');

        if (next_question_id) {
            var $nextQuestion = $(next_question_id);
            if ($nextQuestion && $nextQuestion.length) {
                /**
                 * check if reveal mode wait for 500ms then
                 * hide question so that correct answer reveal
                 * @since 1.8.10
                 */

                var feedBackMode = $question_wrap.attr('data-quiz-feedback-mode');
                if (feedBackMode === 'reveal') {
                    setTimeout(() => {
                        $('.quiz-attempt-single-question').hide();
                        $nextQuestion.show();
                    },
                        800);
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


    $(document).on('submit', '#tutor-answering-quiz', function (e) {
        var $questions_wrap = $('.quiz-attempt-single-question');
        const quizSubmitBtn = document.querySelector('.tutor-quiz-submit-btn');
        quizSubmitBtn.disabled = true;
        var validated = true;
        if ($questions_wrap.length) {
            $questions_wrap.each(function (index, question) {
                // !tutor_quiz_validation( $(question) ) ? validated = false : 0;
                // !feedback_response( $(question) ) ? validated = false : 0;
                validated = tutor_quiz_validation($(question));
                validated = feedback_response($(question));

            });
        }

        if (!validated) {
            e.preventDefault();
        }

        setTimeout(() => {
            quizSubmitBtn.disabled = true;
        }, 500);
    });
});
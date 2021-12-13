import { get_response_message } from "../helper/response";

window.jQuery(document).ready(function ($) {

    const { __ } = wp.i18n;

    // TAB switching
    var step_switch = function (modal, go_next, clear_next) {
        var element = modal.find('.tutor-modal-steps');
        var current = element.find('li[data-tab="' + modal.attr('data-target') + '"]');
        var next = current.next();
        var prev = current.prev();

        if (!go_next) {
            var new_tab = prev.data('tab');
            prev.length ? modal.attr('data-target', new_tab) : 0;
            clear_next ? element.find('li[data-tab="' + new_tab + '"]').nextAll().removeClass('tutor-is-completed') : 0;
            return;
        }

        if (next.length) {
            next.addClass('tutor-is-completed');
            modal.attr('data-target', next.data('tab'));
            return true;
        }

        // If there is no more next screen, it means quiz saved and show the toast
        tutor_toast(__('Success', 'tutor'), __('Quiz Updated'), 'success');
        modal.removeClass('tutor-is-active');
        return null;
    }

    // Slider initiator
    var tutor_slider_init = function () {
        $('.tutor-field-slider').each(function () {
            var $slider = $(this);
            var $input = $slider.closest('.tutor-field-type-slider').find('input[type="hidden"]');
            var $showVal = $slider.closest('.tutor-field-type-slider').find('.tutor-field-type-slider-value');
            var min = parseFloat($slider.closest('.tutor-field-type-slider').attr('data-min'));
            var max = parseFloat($slider.closest('.tutor-field-type-slider').attr('data-max'));

            $slider.slider({
                range: "max",
                min: min,
                max: max,
                value: $input.val(),
                slide: function (event, ui) {
                    $showVal.text(ui.value);
                    $input.val(ui.value);
                }
            });
        });
    }

    function tutor_save_sorting_quiz_questions_order() {
        var questions = {};
        $('.quiz-builder-question-wrap').each(function (index, item) {
            var $question = $(this);
            var question_id = parseInt($question.attr('data-question-id'), 10);
            questions[index] = question_id;
        });

        $.ajax({
            url: window._tutorobject.ajaxurl,
            type: 'POST',
            data: {
                sorted_question_ids: questions,
                action: 'tutor_quiz_question_sorting'
            },
        });
    }

    // Sort quiz question
    function enable_quiz_questions_sorting() {
        if (jQuery().sortable) {
            $(".quiz-builder-questions-wrap").sortable({
                handle: ".question-sorting",
                start: function (e, ui) {
                    ui.placeholder.css('visibility', 'visible');
                },
                stop: function (e, ui) {
                    tutor_save_sorting_quiz_questions_order();
                },
            });
        }
    }

    /**
     * Save answer sorting placement
     *
     * @since v.1.0.0
     */
    function enable_quiz_answer_sorting() {
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

    function tutor_save_sorting_quiz_answer_order() {
        var answers = {};
        $('.tutor-quiz-answer-wrap').each(function (index, item) {
            var $answer = $(this);
            var answer_id = parseInt($answer.attr('data-answer-id'), 10);
            answers[index] = answer_id;
        });

        $.ajax({
            url: window._tutorobject.ajaxurl, type: 'POST',
            data: { sorted_answer_ids: answers, action: 'tutor_quiz_answer_sorting' },
        });
    }

    function tutor_select() {
        var obj = {
            init: function () {
                $(document).on('click', '.question-type-select .tutor-select-option', function (e) {
                    e.preventDefault();

                    var $that = $(this);
                    if ($that.attr('data-is-pro') !== 'true') {
                        var $html = $that.html().trim();
                        $that.closest('.question-type-select').find('.select-header .lead-option').html($html);
                        $that.closest('.question-type-select').find('.select-header input.tutor_select_value_holder').val($that.attr('data-value')).trigger('change');
                        $that.closest('.tutor-select-options').hide();
                    } else {
                        alert('Tutor Pro version required');
                    }
                });
                $(document).on('click', '.question-type-select .select-header', function (e) {
                    e.preventDefault();

                    var $that = $(this);
                    $that.closest('.question-type-select').find('.tutor-select-options').slideToggle();
                });

                this.setValue();
                this.hideOnOutSideClick();
            },
            setValue: function () {
                $('.question-type-select').each(function () {
                    var $that = $(this);
                    var $option = $that.find('.tutor-select-option');

                    if ($option.length) {
                        $option.each(function () {
                            var $thisOption = $(this);

                            if ($thisOption.attr('data-selected') === 'selected') {
                                var $html = $thisOption.html().trim();
                                $thisOption.closest('.question-type-select').find('.select-header .lead-option').html($html);
                                $thisOption.closest('.question-type-select').find('.select-header input.tutor_select_value_holder').val($thisOption.attr('data-value'));
                            }
                        });
                    }
                });
            },
            hideOnOutSideClick: function () {
                $(document).mouseup(function (e) {
                    var $option_wrap = $(".tutor-select-options");
                    if (!$(e.target).closest('.select-header').length && !$option_wrap.is(e.target) && $option_wrap.has(e.target).length === 0) {
                        $option_wrap.hide();
                    }
                });
            },
            reInit: function () {
                this.setValue();
            }
        };

        return obj;
    }

    tutor_select().init();
    tutor_slider_init();

    // Create/Edit quiz opener
    $(document).on('click', '.tutor-add-quiz-btn, .open-tutor-quiz-modal, .back-to-quiz-questions-btn', function (e) {
        e.preventDefault();

        var $that = $(this);
        var step_1 = $(this).hasClass('open-tutor-quiz-modal') || $(this).hasClass('tutor-add-quiz-btn');
        var modal = $('.tutor-modal.tutor-quiz-builder-modal-wrap');
        var quiz_id = $that.hasClass('tutor-add-quiz-btn') ? 0 : $that.attr('data-quiz-id');
        var topic_id = $that.closest('.tutor-topics-wrap').data('topic-id');
        var course_id = $('#post_ID').val();

        $.ajax({
            url: window._tutorobject.ajaxurl,
            type: 'POST',
            data: {
                quiz_id: quiz_id,
                topic_id: topic_id,
                course_id: course_id,
                action: 'tutor_load_quiz_builder_modal'
            },
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (!data.success) {
                    tutor_toast('Error', get_response_message(data), 'error');
                    return;
                }

                $('.tutor-quiz-builder-modal-wrap').addClass('tutor-is-active');
                $('.tutor-quiz-builder-modal-wrap .modal-container').html(data.data.output);
                $('.tutor-quiz-builder-modal-wrap').attr('data-quiz-id', quiz_id).attr('data-topic-id-of-quiz', topic_id);

                modal.removeClass('tutor-has-question-from');

                if (step_1) {
                    step_switch(modal, false, true); // Back to second from third
                    step_switch(modal, false, true); // Back to first from second
                }

                window.dispatchEvent(new Event(_tutorobject.content_change_event));

                tutor_slider_init();
                enable_quiz_questions_sorting();
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    // Quiz modal next click
    $(document).on('click', '.tutor-quiz-builder-modal-wrap button', function (e) {

        // DOM findar
        var btn = $(this);
        var modal = btn.closest('.tutor-modal');
        var current_tab = modal.attr('data-target');
        var action = $(this).data('action');

        if (action == 'back') {
            step_switch(modal, false);
            return;
        } else if (action != 'next') {
            return;
        }

        // Quiz meta data
        var course_id = $('#post_ID').val();
        var topic_id = $(this).closest('.tutor-quiz-builder-modal-wrap').data('topic-id-of-quiz');
        var quiz_id = modal.find('[name="quiz_id"]').val();

        if (current_tab == 'quiz-builder-tab-quiz-info' || current_tab == 'quiz-builder-tab-settings') {

            // Save quiz info. Title and description
            var quiz_title = modal.find('[name="quiz_title"]').val();
            var quiz_description = modal.find('[name="quiz_description"]').val();

            var settings = modal.find('#quiz-builder-tab-settings :input, #quiz-builder-tab-advanced-options :input').serializeObject();
            var quiz_info_required = {
                quiz_title,
                course_id,
                quiz_id,
                topic_id
            }

            for (let k in quiz_info_required) {
                if (!quiz_info_required[k]) {
                    console.log(quiz_info_required);

                    if (k == 'quiz_title') {
                        tutor_toast('Error!', __('Quiz title required', 'tutor'), 'error');
                    }
                    return;
                }
            }

            $.ajax({
                url: window._tutorobject.ajaxurl,
                type: 'POST',
                data: {
                    ...settings,
                    ...quiz_info_required,
                    quiz_description,
                    action: 'tutor_quiz_save',
                },
                beforeSend: function () {
                    btn.addClass('tutor-updating-message');
                },
                success: function (data) {
                    console.log(quiz_id, quiz_id != 0);

                    if (quiz_id && quiz_id != 0) {
                        // Update if exists already
                        $('#tutor-quiz-' + quiz_id).replaceWith(data.data.output_quiz_row);
                        console.log($('#tutor-quiz-' + quiz_id))
                    } else {
                        // Otherwise create new row
                        $('#tutor-topics-' + topic_id + ' .tutor-lessons').append(data.data.output_quiz_row);
                        console.log($('#tutor-topics-' + topic_id + ' .tutor-lessons'))
                    }

                    // Update modal content
                    $('.tutor-quiz-builder-modal-wrap .modal-container').html(data.data.output);

                    window.dispatchEvent(new Event(_tutorobject.content_change_event));

                    tutor_slider_init();
                    step_switch(modal, true);

                    enable_quiz_questions_sorting();

                    // Trigger change to set background based on checked status
                    $('[name="quiz_option[feedback_mode]"]').trigger('change');
                },
                complete: function () {
                    btn.removeClass('tutor-updating-message');
                }
            });
        } else if (current_tab == 'quiz-builder-tab-questions') {
            step_switch(modal, true);
        }
    });

    // Add new or edit question button click
    $(document).on('click', '.tutor-quiz-open-question-form', function (e) {
        e.preventDefault();

        // Prepare related data for the question
        var $that       = $(this);
        var modal       = $that.closest('.tutor-modal');
        var quiz_id     = modal.find('[name="quiz_id"]').val();
        var topic_id    = modal.find('[name="topic_id"]').val();
        var course_id   = $('#post_ID').val();
        var question_id = $that.attr('data-question-id');

        var params = {
            quiz_id,
            topic_id,
            course_id,
            question_id,
            action : 'tutor_quiz_builder_get_question_form'
        };

        $.ajax({
            url: window._tutorobject.ajaxurl,
            type: 'POST',
            data: params,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                // Add the question form in modal
                modal.find('.modal-container').html(data.data.output);
                modal.addClass('tutor-has-question-from');

                // Enable quiz answer sorting for multi/radio select
                enable_quiz_answer_sorting();
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    // Trash question
    $(document).on('click', '.tutor-quiz-question-trash', function (e) {
        e.preventDefault();

        var $that = $(this);
        var question_id = $that.attr('data-question-id');

        $.ajax({
            url: window._tutorobject.ajaxurl,
            type: 'POST',
            data: {
                question_id: question_id,
                action: 'tutor_quiz_builder_question_delete'
            },
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function () {
                $that.closest('.quiz-builder-question-wrap').fadeOut(function () {
                    $(this).remove();
                });
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Get question answers option form to save/edit multiple/single/true-false options
     *
     * @since v.1.0.0
     */
    $(document).on('click', '.add_question_answers_option, .tutor-quiz-answer-edit a', function (e) {
        e.preventDefault();

        var $that = $(this);
        var question_id = $that.closest('[data-question-id]').attr('data-question-id');
		var answer_id = $(this).hasClass('add_question_answers_option') ? null : $that.closest('.tutor-quiz-answer-wrap').attr('data-answer-id');

        var $formInput = $('#tutor-quiz-question-wrapper :input').serializeObject();
        $formInput.question_id = question_id;
        $formInput.answer_id = answer_id;
        $formInput.action = 'tutor_quiz_question_answer_editor';

        $.ajax({
            url: window._tutorobject.ajaxurl,
            type: 'POST',
            data: $formInput,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('#tutor_quiz_builder_answer_wrapper').html(data.data.output);
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Quiz Question edit save and continue
     */
    $(document).on('click', '.quiz-modal-question-save-btn', function (e) {
        e.preventDefault();

        var $that = $(this);
        var modal = $that.closest('.tutor-modal');
        var $formInput = $('#tutor-quiz-question-wrapper :input').serializeObject();
        $formInput.action = 'tutor_quiz_modal_update_question';

        $.ajax({
            url: window._tutorobject.ajaxurl,
            type: 'POST',
            data: $formInput,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success) {
                    modal.find('.back-to-quiz-questions-btn').trigger('click');
                } else {
                    tutor_toast('Error', get_response_message(data), 'error');
                }
            },
            complete: function () {
                setTimeout(() => $that.removeClass('tutor-updating-message'), 2000);
            }
        });
    });

    /**
     * If change question type from quiz builder question
     *
     * @since v.1.0.0
     */
    $(document).on('change', 'input.tutor_select_value_holder', function (e) {
        // Firstly remove older content and show loading spinner
        var answer_wrapper = $('#tutor_quiz_builder_answer_wrapper');
        answer_wrapper.html(
            `<div style="text-align:center">
                <i class="tutor-updating-message"></i>
            </div>`
        );

        answer_wrapper.get(0).scrollIntoView({block: 'center', behavior:'smooth'});

        var question_id = $(this).closest('[data-question-id]').attr('data-question-id');
        var question_type = $(this).val();

        $.ajax({
            url: window._tutorobject.ajaxurl,
            type: 'POST',
            data: { 
                question_id: question_id, 
                question_type: question_type, 
                action: 'tutor_quiz_builder_change_type' 
            },
            success: function (data) {
                if (data.success) {
                    $('#tutor_quiz_builder_answer_wrapper').html(data.data.output);
                    answer_wrapper.get(0).scrollIntoView({block: 'center', behavior:'smooth'});
                } else {
                    tutor_toast('Error', get_response_message(data), 'error');
                }
            }
        });
    });


	/**
	 * Saving question answers options
	 * Student should select the right answer at quiz attempts
	 *
	 * @since v.1.0.0
	 */
	$(document).on('click', '#quiz-answer-save-btn', function (e) {
		e.preventDefault();
		var $that = $(this);
		var $formInput = $('#tutor-quiz-question-wrapper :input').serializeObject();
		$formInput.action = $formInput.tutor_quiz_answer_id ? 'tutor_update_quiz_answer_options' : 'tutor_save_quiz_answer_options';

		$.ajax({
			url: window._tutorobject.ajaxurl,
			type: 'POST',
			data: $formInput,
			beforeSend: function () {
				$that.addClass('tutor-updating-message');
			},
			success: function (data) {
                if(!data.success) {
                    tutor_toast('Error', get_response_message(data), 'error');
                    return;
                }
                
                $('.tutor_select_value_holder').trigger('change');
			},
			complete: function () {
				$that.removeClass('tutor-updating-message');
			},
		});
	});

	/**
	 * Updating Answer
	 *
	 * @since v.1.0.0
	 */
	$(document).on('change', '.tutor-quiz-answers-mark-correct-wrap input', function (e) {
		e.preventDefault();

		var $that = $(this);

		var answer_id = $that.val();
		var inputValue = 1;
		if (!$that.prop('checked')) {
			inputValue = 0;
		}

		$.ajax({
			url: window._tutorobject.ajaxurl,
			type: 'POST',
			data: { 
                answer_id: answer_id, 
                inputValue: inputValue, 
                action: 'tutor_mark_answer_as_correct' 
            },
		});
	});

	/**
	 * Delete answer for a question in quiz builder
	 *
	 * @since v.1.0.0
	 */
	$(document).on('click', '.tutor-quiz-answer-trash-wrap a.answer-trash-btn', function (e) {
		e.preventDefault();

		var $that = $(this);
		var answer_id = $that.attr('data-answer-id');

		$.ajax({
			url: window._tutorobject.ajaxurl,
			type: 'POST',
			data: { answer_id: answer_id, action: 'tutor_quiz_builder_delete_answer' },
			beforeSend: function () {
				$that.closest('.tutor-quiz-answer-wrap').remove();
			},
		});
	});

    // Collapse/expand advanced settings
    $(document).on('click', '.tutor-quiz-advance-settings .tutor-quiz-advance-header', function () {
        $(this).parent().toggleClass('tutor-is-active')
            .find('.ttr-angle-down-filled').toggleClass('ttr-angle-up-filled');
    });

    // Change background of quiz feedback mode
    $(document).on('change', '[name="quiz_option[feedback_mode]"]', function() {
        if($(this).prop('checked')) {
            $(this).parent()
                .addClass('tutor-bg-white')
                .removeClass('tutor-bg-transparent')
            .siblings().filter('.tutor-radio-select')
                .addClass('tutor-bg-transparent')
                .removeClass('tutor-bg-white');
        }
    });
});
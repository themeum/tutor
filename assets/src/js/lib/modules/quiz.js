import { get_response_message } from '../../helper/response';

window.jQuery(document).ready(($) => {
	const { __ } = wp.i18n;

	/**
	 * Quiz Frontend Review Action
	 * @since 1.4.0
	 */
	$(document).on('click', '.quiz-manual-review-action', function(e) {
		e.preventDefault();

		var $that = $(this);
		var attempt_id = $that.attr('data-attempt-id');
		var attempt_answer_id = $that.attr('data-attempt-answer-id');
		var question_id = $that.attr('data-question-id');
		var mark_as = $that.attr('data-mark-as');
		var context = $that.attr('data-context');
		var back_url = $that.attr('data-back-url');

		$.ajax({
			url: _tutorobject.ajaxurl,
			type: 'POST',
			data: {
				attempt_id,
				attempt_answer_id,
				question_id,
				mark_as,
				context,
				back_url,
				action: 'review_quiz_answer',
			},
			beforeSend: function() {
				$that.addClass('is-loading');
			},
			success: function(data) {
				if (data.success && (data.data || {}).html) {
					$that.closest('.tutor-quiz-attempt-details-wrapper').html(data.data.html);
					return;
				}

				tutor_toast(__('Error!', 'tutor'), get_response_message(data), 'error');
			},
			complete: function() {
				$that.removeClass('is-loading');
			},
		});
	});

	$(document).on('click', '.quiz-manual-mark-save', function(e) {
		e.preventDefault();

		var $that = $(this);
		var $wrapper = $that.closest('.tutor-manual-review-wrapper');
		var manual_mark = $wrapper.find('.quiz-manual-mark-input').val();

		$.ajax({
			url: _tutorobject.ajaxurl,
			type: 'POST',
			data: {
				attempt_id: $that.attr('data-attempt-id'),
				attempt_answer_id: $that.attr('data-attempt-answer-id'),
				question_id: $that.attr('data-question-id'),
				manual_mark,
				context: $that.attr('data-context'),
				back_url: $that.attr('data-back-url'),
				action: 'review_quiz_answer',
			},
			beforeSend: function() {
				$that.addClass('is-loading');
			},
			success: function(data) {
				if (data.success && (data.data || {}).html) {
					$that.closest('.tutor-quiz-attempt-details-wrapper').html(data.data.html);
					return;
				}

				tutor_toast(__('Error!', 'tutor'), get_response_message(data), 'error');
			},
			complete: function() {
				$that.removeClass('is-loading');
			},
		});
	});
});

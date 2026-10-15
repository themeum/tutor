import { get_response_message } from '../../helper/response';

window.jQuery(document).ready(($) => {
	const { __, sprintf } = wp.i18n;

	/**
	 * Get the validation message for a manual mark input value.
	 *
	 * @param {jQuery} $input The manual mark input element.
	 *
	 * @return {string} Empty string when the value is valid, otherwise a message.
	 */
	function getManualMarkValidationMessage($input) {
		var max = $input.attr('max');
		var value = parseFloat($input.val());

		if (isNaN(value)) {
			return __('Mark must be a valid number.', 'tutor');
		}

		if (value < 0) {
			return __('Mark cannot be negative.', 'tutor');
		}

		if ('' !== max && value > parseFloat(max)) {
			return sprintf(__('Mark cannot exceed %s.', 'tutor'), max);
		}

		return '';
	}

	/**
	 * Refresh the manual mark input visual state based on its value.
	 *
	 * @param {jQuery} $wrapper The tutorial manual review wrapper.
	 */
	function renderManualMarkState($wrapper) {
		var $input = $wrapper.find('.quiz-manual-mark-input');
		var $save = $wrapper.find('.quiz-manual-mark-save');
		var $error = $wrapper.find('.quiz-manual-mark-error');
		var value = $.trim($input.val());
		var message = '';

		if ('' !== value && !$input[0].checkValidity()) {
			message = getManualMarkValidationMessage($input);
		}

		$input.toggleClass('is-invalid', '' !== message);
		$input.attr('aria-invalid', '' !== message ? 'true' : 'false');
		$input.css('border-color', '' !== message ? 'var(--tutor-color-danger)' : '');

		$error.text(message).toggle('' !== message);

		$save.toggle('' !== value && '' === message);
	}

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

	$(document).on('keydown', '.quiz-manual-review-action[role="button"]', function(e) {
		if (13 === e.which || 32 === e.which) {
			e.preventDefault();
			$(this).trigger('click');
		}
	});

	$(document).on('click', '.quiz-manual-mark-save', function(e) {
		e.preventDefault();

		var $that = $(this);
		var $wrapper = $that.closest('.tutor-manual-review-wrapper');
		var $input = $wrapper.find('.quiz-manual-mark-input');

		if ('' !== $.trim($input.val()) && !$input[0].checkValidity()) {
			renderManualMarkState($wrapper);
			return;
		}

		var manual_mark = $input.val();

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

	$(document).on('input', '.quiz-manual-mark-input', function() {
		renderManualMarkState($(this).closest('.tutor-manual-review-wrapper'));
	});
});

window.jQuery(document).ready(function($) {
	const { __ } = wp.i18n;

	$(document).on('click', '.tutor-save-topic-btn', function(e) {
		e.preventDefault();

		let $button = $(this);
		let modal = $button.closest('.tutor-modal');

		let topic_id = modal.find('[name="topic_id"]').val();
		let topic_title = modal.find('[name="topic_title"]').val();
		let topic_summery = modal.find('[name="topic_summery"]').val();
		let topic_course_id = modal.find('[name="topic_course_id"]').val();

		let data = {
			topic_title,
			topic_summery,
			topic_id,
			topic_course_id,
			action: 'tutor_save_topic',
		};

		$.ajax({
			url: window._tutorobject.ajaxurl,
			type: 'POST',
			data: data,
			beforeSend: function() {
				$button.addClass('is-loading');
			},
			success: function(resp) {
				const { data = {}, success } = resp;
				const { message = __('Something Went Wrong!', 'tutor'), course_contents, topic_title } = data;

				if (!success) {
					tutor_toast('Error!', message, 'error');
					return;
				}

				// Close Modal
				// modal.removeClass('tutor-is-active', $('#tutor-course-content-wrap'));
				modal.removeClass('tutor-is-active');

				// Show updated contents
				if (topic_id) {
					// It's topic update
					$button
						.closest('.tutor-topics-wrap')
						.find('span.topic-inner-title')
						.text(topic_title);
				} else {
					// It's new topic creation
					$('#tutor-course-content-wrap').html(course_contents);
					modal.find('[name="topic_title"]').val('');
					modal.find('[name="topic_summery"]').val('');
				}

				window.dispatchEvent(new Event(_tutorobject.content_change_event));
			},
			complete: function() {
				$button.removeClass('is-loading');
				$('body').removeClass('tutor-modal-open');
			},
		});
	});

	/**
	 * Confirmation for deleting Topic
	 */
	$(document).on('click', '.tutor-topics-wrap .topic-delete-btn i', function(e) {
		var $that = $(this);
		var container = $(this).closest('.tutor-topics-wrap');
		var topic_id = container.attr('data-topic-id');

		if (!confirm(__('Are you sure to delete the topic?', 'tutor'))) {
			return;
		}

		$.ajax({
			url: window._tutorobject.ajaxurl,
			type: 'POST',
			data: {
				action: 'tutor_delete_topic',
				topic_id,
			},
			beforeSend: function() {
				$that.addClass('is-loading-v2');
			},
			success: function(data) {
				// To Do: Load updated topic list here
				if (data.success) {
					container.remove();
					return;
				}

				tutor_toast('Error!', (data.data || {}).message || __('Something Went Wrong', 'tutor'), 'error');
			},
			complete: function() {
				$that.removeClass('is-loading-v2');
			},
		});
	});

	$(document).on('click', '.topic-inner-title, .expand-collapse-wrap', function(e) {
		e.preventDefault();

		var wrapper = $(this).closest('.tutor-topics-wrap');
		wrapper.find('.tutor-topics-body').slideToggle();
		wrapper
			.find('.expand-collapse-wrap')
			.toggleClass('is-expanded')
			.find('i')
			.toggleClass('tutor-icon-angle-down tutor-icon-angle-up');
	});
});

window.jQuery(document).ready(($) => {
	const { __ } = wp.i18n;
	$(document).on('click', '.tutor-course-wishlist-btn', function (e) {
		e.preventDefault();
		var $that = $(this);
		var course_id = $that.attr('data-course-id');

		$.ajax({
			url: _tutorobject.ajaxurl,
			type: 'POST',
			data: {
				course_id,
				action: 'tutor_course_add_to_wishlist',
			},
			beforeSend: function () {
				$that.attr('disabled', 'disabled').addClass('is-loading');
			},
			success: function (data) {
				if (data.success) {
					if (data.data.status === 'added') {
						$that
							.find('i')
							.addClass('tutor-icon-bookmark-bold')
							.removeClass('tutor-icon-bookmark-line');
					} else {
						$that
							.find('i')
							.addClass('tutor-icon-bookmark-line')
							.removeClass('tutor-icon-bookmark-bold');
						$that.blur();
					}
				} else {
					tutor_toast(__('Error', 'tutor-pro'), data?.data?.message ?? 'Something went wrong!!', 'error');
					$('.tutor-login-modal').addClass('tutor-is-active');
					$that.blur();
				}
			},
			error: function (xhr, status, error) {
				tutor_toast(__('Error', 'tutor-pro'), 'Something went wrong!!', 'error');
				$that.blur();
			},
			complete: function () {
				$that.removeAttr('disabled').removeClass('is-loading');
			},
		});
	});
});

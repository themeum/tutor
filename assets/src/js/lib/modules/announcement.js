function urlPrams(type, val) {
	var url = new URL(window.location.href);
	var search_params = url.searchParams;
	search_params.set(type, val);

	url.search = search_params.toString();
	if (_tutorobject.is_admin) {
		search_params.set('paged', 1);
	} else {
		search_params.set('current_page', 1);
	}
	url.search = search_params.toString();

	return url.toString();
}

window.jQuery(document).ready(($) => {
	const { __ } = window.wp.i18n;

	//create announcement
	$('.tutor-announcements-form').on('submit', function(e) {
		e.preventDefault();
		var $btn = $(this).find('button[type="submit"]');
		let buttonContent = $btn.html().trim();
		var formData = $btn.closest('.tutor-announcements-form').serialize();

		$.ajax({
			url: window._tutorobject.ajaxurl,
			type: 'POST',
			data: formData,
			beforeSend: function() {
				$btn.text(__('Updating...', 'tutor')).attr('disabled', 'disabled').addClass('is-loading');
			},
			success: function(data) {
				if (!data.success) {
					const { message = __('Something Went Wrong!', 'tutor') } = data.data || {};
					tutor_toast(__('Error!', 'tutor'), message, 'error');
					return;
				}

				location.reload();
			},
			complete: function() {
				$btn.html(buttonContent).removeAttr('disabled').removeClass('is-loading');
			},
			error: function(data) {
				tutor_toast(__('Error!', 'tutor'), __('Something Went Wrong!', 'tutor'), 'error');
			},
		});
	});

	// Announcement filter
	$('.tutor-announcement-course-sorting').on('change', function(e) {
		window.location = urlPrams('course-id', $(this).val());
	});
	$('.tutor-announcement-order-sorting').on('change', function(e) {
		window.location = urlPrams('order', $(this).val());
	});
	$('.tutor-announcement-date-sorting').on('change', function(e) {
		window.location = urlPrams('date', $(this).val());
	});
	$('.tutor-announcement-search-sorting').on('click', function(e) {
		window.location = urlPrams('search', $('.tutor-announcement-search-field').val());
	});
});

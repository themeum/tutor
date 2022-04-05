window.jQuery(document).ready($=>{
    const {__} = window.wp.i18n;

	/**
	 * Manage course filter
	 *
	 * @since  v.1.7.2
	 */
	var filter_container = $('.tutor-course-filter-container form');
	var loop_container = $('.tutor-course-filter-loop-container');
	var archive_meta = $('.tutor-courses-wrap').data('tutor_courses_meta') || {};
	var filter_modifier = {};

	// Sidebar checkbox value change
	filter_container
		.on('submit', function (e) {
			e.preventDefault();
			console.log('Course filter form submission prevented');
		})
		.find('input')
		.change(function (e) {
			ajaxFilterArchive();
		});


	const ajaxFilterArchive = (criteria_object) => {
		var filter_criteria = criteria_object || Object.assign(filter_container.serializeObject(), filter_modifier, archive_meta);
		filter_criteria.current_page = 1;
		filter_criteria.action = 'tutor_course_filter_ajax';

		console.log(filter_criteria);
		// window.history.pushState({}, '', '#'+JSON.stringify(filter_criteria));

		loop_container.html('<div style="background-color: #fff;" class="loading-spinner"></div>');
		$(this)
			.closest('form')
			.find('.tutor-clear-all-filter')
			.show();

		$.ajax({
			url: window._tutorobject.ajaxurl,
			type: 'POST',
			data: filter_criteria,
			success: function (r) {
				if(!r.success){
					loop_container.html(__('Could not load courses', 'tutor'));
					return;
				}

				loop_container.html(r.data.html).find('nav').css('display', 'flex');
			}
		});
	}

	if(filter_container.length){
		window.addEventListener('popstate', ()=>{

		});
	}

	// Refresh page after coming back to course archive page from cart
	/* var archive_loop = $('.tutor-course-loop');
	if (archive_loop.length > 0) {
		window.sessionStorage.getItem('tutor_refresh_archive') === 'yes' ? window.location.reload() : 0;
		window.sessionStorage.removeItem('tutor_refresh_archive');
		archive_loop.on('click', '.tutor-loop-cart-btn-wrap', function () {
			window.sessionStorage.setItem('tutor_refresh_archive', 'yes');
		});
	} */
});
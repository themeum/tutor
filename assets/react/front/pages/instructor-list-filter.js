jQuery(document).ready(function($) {
	/**
	 *
	 * Instructor list filter
	 *
	 * @since  v.1.8.4
	 */
	// Get values on course category selection
	$('[tutor-instructors]').each(function() {
		var $this = $(this);
		var filter_args = {};
		var time_out;

		function run_instructor_filter(name, value, page_number) {
			// Prepare http payload
			var result_container = $this.find('[tutor-instructors-content]');
			var html_cache = result_container.html();
			var attributes = $this.data();
			attributes.current_page = page_number || 1;

			name ? (filter_args[name] = value) : (filter_args = {});
			filter_args.attributes = attributes;
			filter_args.action = 'load_filtered_instructor';

			// Append spinner
			result_container.html('<div class="tutor-spinner-wrap"><span class="tutor-spinner" area-hidden="true"></span></div>');

			$.ajax({
				url: window._tutorobject.ajaxurl,
				data: filter_args,
				type: 'POST',
				success: function(r) {
					result_container.html(r);
				},
				error: function() {
					result_container.html(html_cache);
					tutor_toast('Failed', 'Request Error', 'error');
				},
			});
		}

		$this
			.on('change', '[tutor-instructors-filter-category] [type="checkbox"]', function() {
				var values = {};

				$(this)
					.closest('[tutor-instructors-filter-category]')
					.find('input:checked')
					.each(function() {
						values[$(this).val()] = $(this).parent().text();
					});

				var cat_ids = Object.keys(values);
				run_instructor_filter($(this).attr('name'), cat_ids);
			})

			.on('click', '[tutor-instructors-filter-rating]', function(e) {
				var rating = e.target.dataset.value;
				run_instructor_filter('rating_filter', rating);
			})

			.on('change', '[tutor-instructors-filter-sort]', function(e) {
				var short_by = e.target.value;
				run_instructor_filter('short_by', short_by);
			})

			// Get values on search keyword change
			.on('input', '[tutor-instructors-filter-search]', function() {
				var val = $(this).val();
				time_out ? window.clearTimeout(time_out) : 0;
				time_out = window.setTimeout(function() {
					run_instructor_filter('keyword', val);
					time_out = null;
				}, 500);
			})

			.on('click', '[data-page_number]', function(e) {
				// On pagination click
				e.preventDefault();
				run_instructor_filter(null, null, $(this).data('page_number'));
			})

			// Clear filter
			.on('click', '[tutor-instructors-filter-clear]', function() {
				var $this = $(this).closest('[tutor-instructors-filters]');
				$this.find('input[type="checkbox"]').prop('checked', false);
				$this.find('[tutor-instructors-filter-search]').val('');
				const stars = document.querySelectorAll('[tutor-instructors-filter-rating]');
				//remove star selection
				for (let star of stars) {
					if (star.classList.contains('active')) {
						star.classList.remove('active');
					}
					if (star.classList.contains('tutor-icon-star-bold')) {
						star.classList.remove('tutor-icon-star-bold');
						star.classList.add('tutor-icon-star-line');
					}
				}
				rating_range.innerHTML = ``;
				run_instructor_filter();
			});
	});

	/**
	 * Show start active as per click
	 *
	 * @since v2.0.0
	 */
	const stars = document.querySelectorAll('[tutor-instructors-filter-rating]');
	const rating_range = document.querySelector('[tutor-instructors-filter-rating-count]');
	for (let star of stars) {
		star.onclick = (e) => {
			//remove active if has
			for (let star of stars) {
				if (star.classList.contains('is-active')) {
					star.classList.remove('is-active');
				}
				if (star.classList.contains('tutor-icon-star-bold')) {
					star.classList.remove('tutor-icon-star-bold');
					star.classList.add('tutor-icon-star-line');
				}
			}
			//show stars active as click
			const length = e.target.dataset.value;
			for (let i = 0; i < length; i++) {
				stars[i].classList.add('is-active');
				stars[i].classList.remove('tutor-icon-star-line');
				stars[i].classList.add('tutor-icon-star-bold');
			}
			rating_range.innerHTML = `0.0 - ${length}.0`;
		};
	}
});

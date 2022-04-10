const tutor_filters = [
	'keyword',
	'tutor-course-filter-level',
	'course_filter',
	'supported_filters',
	'current_page'
]

const pushFilterToState=data=>{

	const new_url = new URL(window.location.origin+window.location.pathname);
	const params = getAllUrlParams();

	// Include other params except tutors
	for(let k in params){
		if(tutor_filters.indexOf(k)==-1){
			new_url.searchParams.append(k, params[k]);
		}
	}

	// Add currently used tutor params to the state
	for(let k in data) {
		let is_array = Array.isArray(data[k]);
		let key = is_array ? k+'[]' : k;
		let values = is_array ? data[k] : [data[k]];

		console.log(key, values);

		values.forEach(v=>{
			new_url.searchParams.append(key, v);
		});
	}
	
	window.history.pushState({}, '', new_url);
}

const getAllUrlParams=()=>{
	let param_array = {};

	new URL(window.location).searchParams.forEach(function (value, key) {
		if(key.slice(-2)=='[]') {
			let name = key.slice(0, -2);
			!param_array[name] ? param_array[name]=[] : 0;
			!Array.isArray(param_array[name]) ? param_array[name]=[param_array[name]] : 0;
			param_array[name].push(value);
		} else {
			param_array[key]=value;
		}
	});

	return param_array;
}

const renderFilterFromState=(filter_container)=>{
	let filters = getAllUrlParams();

	filter_container.find('[type="checkbox"]').prop('checked', false);
	filter_container.find('[type="text"]').val('');

	// Loop through filter params array and change element state like check/uncheck/field value based on the filter
	for(let k in filters){
		let value = filters[k];
		let element = filter_container.find('[name="'+k+'"]');

		if(element.eq(0).attr('type')=='checkbox') {
			let values = !Array.isArray(value) ? [value] : value;
			element.each(function(){
				let checked = values.indexOf(window.jQuery(this).attr('value'))>-1;
				window.jQuery(this).prop('checked', checked);
			});
		} else {
			element.val(value);
		}
	}
}

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

	if(filter_container.length){
		renderFilterFromState(filter_container);
		window.addEventListener('popstate', ()=>{
			renderFilterFromState(filter_container);
			ajaxFilterArchive(false);
		});
	}

	const ajaxFilterArchive = (push_state=true) => {
		var filter_criteria = Object.assign(filter_container.serializeObject(), filter_modifier, archive_meta);
		filter_criteria.current_page = 1;
		filter_criteria.action = 'tutor_course_filter_ajax';

		if(push_state){
			pushFilterToState(filter_criteria);
		}
		
		loop_container.html('<div style="background-color: #fff;" class="loading-spinner"></div>');
		$(this).closest('form').find('.tutor-clear-all-filter').show();

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
});

// Reusable for Instructor list filter
export {pushFilterToState, renderFilterFromState}
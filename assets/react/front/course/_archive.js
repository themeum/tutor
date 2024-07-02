import ajaxHandler from "../../admin-dashboard/segments/filter";
import tutorFormData from "../../helper/tutor-formdata";

const {__} = wp.i18n;
const tutor_filters = [
    'keyword',
    'course_order',
    'tutor-course-filter-level',
    'tutor-course-filter-tag',
    'tutor-course-filter-category',
    'tutor-course-filter-price',
    'course_filter',
    'supported_filters',
    'current_page',
    'action'
];

const pushFilterToState = data => {
    const new_url = new URL(window.location.origin + window.location.pathname);
    const params = getAllUrlParams();

    // Include other params except tutors
    for (let k in params) {
        if (tutor_filters.indexOf(k) == -1) {
            new_url.searchParams.append(k, params[k]);
        }
    }

    // Add currently used tutor params to the state
    for (let k in data) {
        let is_array = Array.isArray(data[k]);
        let key = is_array ? k + '[]' : k;
        let values = is_array ? data[k] : [data[k]];

        values.forEach(v => {
            if (typeof v != 'object') {
                new_url.searchParams.append(key, v);
            }
        });
    }

    window.history.pushState({}, '', new_url);
}

const getAllUrlParams = () => {
    let param_array = {};

    new URL(window.location).searchParams.forEach(function(value, key) {
        if (key.slice(-2) == '[]') {
            let name = key.slice(0, -2);
            !param_array[name] ? param_array[name] = [] : 0;
            !Array.isArray(param_array[name]) ? param_array[name] = [param_array[name]] : 0;
            param_array[name].push(value);
        } else {
            param_array[key] = value;
        }
    });
    return param_array;
}

const renderFilterFromState = (filter_container) => {
    let filters = getAllUrlParams();

    filter_container.find('[type="checkbox"]').prop('checked', false);
    filter_container.find('[type="text"], select').val('');

    // Loop through filter params array and change element state like check/uncheck/field value based on the filter
    for (let k in filters) {
        let value = filters[k];
        let element = filter_container.find('[name="' + k + '"]');

        if (element.eq(0).attr('type') == 'checkbox') {
            let values = !Array.isArray(value) ? [value] : value;
            element.each(function() {
                let checked = values.indexOf(window.jQuery(this).attr('value')) > -1;
                window.jQuery(this).prop('checked', checked);
            });
        } else {
            element.val(value);
        }
    }
}

window.jQuery(document).ready($ => {
    const {
        __
    } = window.wp.i18n;

    /**
     * Manage course filter
     *
     * @since  v.1.7.2
     */
    var course_filter_container = $('[tutor-course-filter] form');
    if (!course_filter_container.length) {
        return;
    }

    var content_container = $('[tutor-course-list-container]');
    var archive_meta = $('.tutor-courses-wrap').data('tutor_courses_meta') || {};
    var filter_modifier = {};

    // Sidebar checkbox value change
    course_filter_container.on('submit', function(e) {
        e.preventDefault();
    }).find('input,select').on('change', function(e) {
        ajaxFilterArchive();
    });

    renderFilterFromState(course_filter_container);
    window.addEventListener('popstate', () => {
        renderFilterFromState(course_filter_container);
        ajaxFilterArchive(false, true);
    });

    const ajaxFilterArchive = (push_state = true, use_page_num=false) => {
        let params = getAllUrlParams();
        var filter_criteria = Object.assign(course_filter_container.serializeObject(), filter_modifier, archive_meta);
        filter_criteria.current_page = (use_page_num && params.current_page) ? params.current_page : 1;
        filter_criteria.action = 'tutor_course_filter_ajax';

        if (push_state) {
            pushFilterToState(filter_criteria);
        }

        content_container.html('<div class="tutor-spinner-wrap"><span class="tutor-spinner" area-hidden="true"></span></div>');
        course_filter_container.find('[action-tutor-clear-filter]').closest('.tutor-widget-course-filter').removeClass('tutor-d-none');
        
        if (!('category' in filter_criteria.supported_filters)) {
            const filter_property = 'tutor-course-filter-category';
            const category_keys = Object.keys(params).filter((val) => val.includes(filter_property));
            if (category_keys.length > 0) {
                const category_ids = [];
                category_keys.forEach((category_key) => {
                    category_ids.push(params[category_key]);
                });
                filter_criteria['tutor-course-filter-category'] = [...new Set(category_ids)];
            }
            else {
                filter_criteria['tutor-course-filter-category'] = JSON.parse($("#course_filter_categories").val());
            }

        }

        const exclude_ids_property = 'tutor-course-filter-exclude-ids';
        const exclude_id_keys = Object.keys(params).filter((val) => val.includes(exclude_ids_property));
        const exclude_ids = [];
        if (exclude_id_keys.length > 0) {
            exclude_id_keys.forEach((exclude_id) => {
                exclude_ids.push(params[exclude_id]);
            });
            filter_criteria['tutor-course-filter-exclude-ids'] = [...new Set(exclude_ids)];
        }
        else {
            if ($('#course_filter_exclude_ids').length) {
                filter_criteria['tutor-course-filter-exclude-ids'] = JSON.parse($("#course_filter_exclude_ids").val());
            }
        }

        const post_ids_property = 'tutor-course-filter-post-ids';
        const post_id_keys = Object.keys(params).filter((val) => val.includes(post_ids_property));
        const post_ids = [];
        if (post_id_keys.length > 0) {
            post_id_keys.forEach((post_id) => {
                post_ids.push(params[post_id]);
            });
            filter_criteria['tutor-course-filter-post-ids'] = [...new Set(post_ids)];
        }
        else {
            if ($('#course_filter_post_ids').length) {
                filter_criteria['tutor-course-filter-post-ids'] = JSON.parse($("#course_filter_post_ids").val());
            }
        }


        $.ajax({
            url: window._tutorobject.ajaxurl,
            type: 'POST',
            data: filter_criteria,
            success: function(r) {
                if (!r.success) {
                    content_container.html(__('Could not load courses', 'tutor'));
                    return;
                }

                content_container.html(r.data.html).find('nav').css('display', 'flex');
            }
        });
    };

    // Course Filter on Phone
    $('[tutor-toggle-course-filter]').on('click', function(event) {
        event.preventDefault();
        $('body').toggleClass('tutor-course-filter-open');

        if ($('.tutor-course-filter-backdrop').length == 0) {
            $('body').append($('<div class="tutor-course-filter-backdrop" area-hidden="true"></div>').hide().fadeIn(150));
        }
    });

    $('[tutor-hide-course-filter]').on('click', function(event) {
        event.preventDefault();
        $('body').removeClass('tutor-course-filter-open');
    });

    /**
     * Enroll student if user click on enroll course button
     * 
     * @since v2.1.0
     */
    const enrollButtons = document.querySelectorAll('.tutor-course-list-enroll');
    enrollButtons.forEach((enrollBtn) => {
        enrollBtn.onclick = async(e) => {
            e.preventDefault();
            const defaultErrorMsg = __('Something went wrong, please try again!', 'tutor');
            const target = e.target;
            const formFields = [
                {action: 'tutor_course_enrollment'},
                {course_id: target.dataset.courseId}
            ];
            const formData = tutorFormData(formFields);

            target.classList.add('is-loading');
            target.setAttribute('disabled', true);

            const post = await ajaxHandler(formData);
            if (post.ok) {
                const response = await post.json();
                console.log(response);
                const {success, data} = response;
                if (success) {
                    tutor_toast(
                        __('Success', 'tutor-pro'),
                        data,
                        'success',
                    );
                    window.location.href = target.href;
                } else {
                    tutor_toast(
                        __('Failed', 'tutor-pro'),
                        data ? data : defaultErrorMsg,
                        'error',
                    );
                }
            } else {
                tutor_toast(
                    __('Error', 'tutor-pro'),
                    __(defaultErrorMsg),
                    'error',
                );
            }
            target.classList.remove('is-loading');
            target.removeAttribute('disabled');
        }
    });
});

// Reusable for Instructor list filter
export {
    pushFilterToState,
    renderFilterFromState
};
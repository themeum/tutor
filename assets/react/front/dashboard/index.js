/**
 * Create new draft course
 *
 * @since v2.0.3
 */
import ajaxHandler from '../../admin-dashboard/segments/filter';
const { __, _x, _n, _nx } = wp.i18n;
document.addEventListener('DOMContentLoaded', function() {

    // Course save draft
    const tutor_course_save_draft = document.getElementById('tutor-course-save-draft');
    if (tutor_course_save_draft) {
        tutor_course_save_draft.onclick = (e) => {
            e.preventDefault();
            tutor_course_save_draft.setAttribute('disabled', 'disabled');
            tutor_course_save_draft.classList.add('is-loading');
            document.getElementById('tutor-frontend-course-builder').submit();
        };
    }

    /**
	 * Fix - Table last row context menu hidden for frontend dashboard.
	 *
	 * @since 2.2.4
	 */
	let tableDropdown = jQuery('.tutor-table-responsive .tutor-table .tutor-dropdown')
	if (tableDropdown.length) {
		let tableHeight = jQuery('.tutor-table-responsive .tutor-table').height()
		jQuery('.tutor-table-responsive').css('min-height', tableHeight + 110)
	}
});
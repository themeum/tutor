/**
 * Create new draft course
 *
 * @since v2.0.3
 */
import ajaxHandler from '../../admin-dashboard/segments/filter';
const { __, _x, _n, _nx } = wp.i18n;
document.addEventListener('DOMContentLoaded', function() {
    // Create new course
    const createNewCourse = document.getElementById('tutor-create-new-course');
    if (createNewCourse) {
        createNewCourse.onclick = async (e) => {
            e.preventDefault();
            createNewCourse.setAttribute('disabled', 'disabled');
            createNewCourse.classList.add('is-loading');
            const defaultErrorMessage = __('Something went wrong, please try again', 'tutor');
            const formData = new FormData();
            formData.set('action', 'tutor_create_new_draft_course');
            formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);
            const post = await ajaxHandler(formData);
            if (post.ok) {
                createNewCourse.classList.remove('is-loading');
                const response = await post.json();
                
                if (response.success) {
                    window.location = response.data.url;
                } else {
                    if (response.data.error_message) {
                        tutor_toast(__('Failed', 'tutor'), response.data.error_message, 'error');
                    } else {
                        tutor_toast(__('Failed', 'tutor'), defaultErrorMessage, 'error');
                    }
                }
            } else {
                tutor_toast(__('Failed', 'tutor'), defaultErrorMessage, 'error');
            }
        }
    }

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

    jQuery('#tutor-registration-form [name="password_confirmation"]').on('input', function(){
        let original = jQuery('[name="password"]');
        let val = (original.val() || '').trim();
        let matched = val && jQuery(this).val() === val;
        
        jQuery(this).parent().find('.tutor-validation-icon')[matched ? 'show' : 'hide']();
    });
});
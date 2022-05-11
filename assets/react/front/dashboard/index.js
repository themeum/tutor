/**
 * Create new draft course
 *
 * @since v2.0.3
 */
import ajaxHandler from '../../admin-dashboard/segments/filter';
const { __, _x, _n, _nx } = wp.i18n;
document.addEventListener('DOMContentLoaded', function() {
    const createNewCourse = document.getElementById('tutor-create-new-course');
    if (createNewCourse) {
        createNewCourse.onclick = async (e) => {
            e.preventDefault();
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
});
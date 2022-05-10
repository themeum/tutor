/**
 * Unset course id from session
 *
 * @since v2.0.3
 */
import ajaxHandler from '../../admin-dashboard/segments/filter';
const { __, _x, _n, _nx } = wp.i18n;
document.addEventListener('DOMContentLoaded', function() {
    /**
     * Unset course id from session on new course create button trigger
     */
    const createNewCourse = document.getElementById('tutor-create-new-course');
    if (createNewCourse) {
        createNewCourse.onclick = async (e) => {
            e.preventDefault();
            const tag = e.target.tagName;
            const target = e.target;
            let href;
            if (tag === 'A') {
                href = target.getAttribute('href');
            }
            if (tag === 'I') {
                href = target.closest('#tutor-create-new-course').getAttribute('href');
            }
            createNewCourse.classList.add('is-loading');
            const formData = new FormData();
            formData.set('action', 'tutor_unset_session_course_id');
            formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);
            const post = await ajaxHandler(formData);
            if (post.ok) {
                createNewCourse.classList.remove('is-loading');
                const response = await post.json();
                if (response.success) {
                    window.location = href;
                } else {
                    tutor_toast(__('Failed', 'tutor'), __('Something went wrong, please try again', 'tutor'), 'error');
                }
            } else {
                tutor_toast(__('Failed', 'tutor'), __('Something went wrong, please try again', 'tutor'), 'error');
            }
        }
    }
});
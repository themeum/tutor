/**
 * Get quiz attempts count data
 *
 * @since v2.0.6
 */
import ajaxHandler from "../helper/ajax-handler";
document.addEventListener('DOMContentLoaded', async function () {
    // Create new course
    const currentPage = _tutorobject.current_page;
    if (currentPage === 'tutor_quiz_attempts') {
        const formData = new FormData();
        formData.set('action', 'tutor_quiz_attempts_count');
        formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);

        const params = new URLSearchParams(window.location.search);
        const keys = ['course-id', 'date', 'search'];

        keys.forEach(key => {
            const value = params.get(key);
            if (value) {
                formData.set(key.replace('-', '_'), value);
            }
        });

        const post = await ajaxHandler(formData);
        if (post.ok) {
            const response = await post.json();
            if (response.success && response.data) {
                const selectField = document.querySelector('.tutor-form-control[name=data]');
                if (selectField) {
                    const labels = document.querySelectorAll('.tutor-form-control[name=data] + .tutor-form-select .tutor-form-select-label');
                    labels.forEach(label => {
                        label.innerHTML = label.innerHTML.replace('(0)', `(${response.data[selectField.value || 'all']})`);
                    });
                }

                const options = document.querySelectorAll('.tutor-form-control[name=data] + .tutor-form-select [tutor-dropdown-item]');
                options.forEach(option => {
                    option.innerHTML = option.innerHTML.replace('(0)', `(${response.data[option.dataset.key || 'all']})`);
                });
            }
        }
    }
});
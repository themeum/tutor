/**
 * Get quiz attempts count data
 *
 * @since v2.0.6
 */
import ajaxHandler from "./segments/filter";
const { __, _x, _n, _nx } = wp.i18n;
document.addEventListener('DOMContentLoaded', async function() {
    // Create new course
    const currentPage = _tutorobject.current_page;
    console.log(currentPage)
    if (currentPage === 'tutor_quiz_attempts') {
        const formData = new FormData();
        formData.set('action', 'tutor_quiz_attempts_count');
        formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);
        const post = await ajaxHandler(formData);
        if (post.ok) {
            const response = await post.json();
            if (response.success && response.data) {
                console.log(response.data);
                const navItems = document.querySelectorAll('.tutor-nav-item .tutor-ml-4');
                let i = 0;
                for (let [key, value] of Object.entries(response.data)) {
                    console.log(key+',,,'+value)
                    navItems[i].innerHTML = `(${value})`;
                    i++;
                }
            }
        }
    }
});
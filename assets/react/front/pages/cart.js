import ajaxHandler from "../../admin-dashboard/segments/filter";
const { __ } = wp.i18n;

document.addEventListener('DOMContentLoaded', function () {
    const cartPageWrapper = document.querySelector('.tutor-cart-page');

    if (cartPageWrapper) {
        // Remove course from card
        const deleteButtons = document.querySelectorAll('.tutor-cart-remove-button');
        deleteButtons.forEach((button) => {
            button.addEventListener('click', async (e) => {
                const defaultErrorMessage = __('Something went wrong, please try again', 'tutor');
                const formData = new FormData();
                formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);
                formData.set('action', 'tutor_course_from_cart');
                formData.set('course_id', button.dataset.courseId);

                try {
                    button.setAttribute('disabled', 'disabled');
                    button.classList.add('is-loading');

                    const post = await ajaxHandler(formData);
                    const { success, data = defaultErrorMessage } = await post.json();
                    if (success) {
                        button.closest('.tutor-cart-course-item').remove();
                        tutor_toast(__('Success', 'tutor'), data, 'success');
                        // @TODO: Update the cart summary.
                    } else {
                        tutor_toast(__('Failed', 'tutor'), data, 'error');
                    }
                } catch (error) {
                    tutor_toast(__('Failed', 'tutor'), defaultErrorMessage, 'error');
                } finally {
                    button.removeAttribute('disabled');
                    button.classList.remove('is-loading');
                }
            });
        });
    }
});

import ajaxHandler from "../../../admin-dashboard/segments/filter";
const { __ } = wp.i18n;

document.addEventListener('DOMContentLoaded', function () {
    const defaultErrorMessage = __('Something went wrong, please try again', 'tutor');
    const userBillingForm = document.querySelector('#user_billing_form');
    const button = userBillingForm.querySelector('button[type="submit"]');

    userBillingForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(e.target);

        try {
            button.setAttribute('disabled', 'disabled');
            button.classList.add('is-loading');

            const post = await ajaxHandler(formData);
            const { status_code, message = defaultErrorMessage } = await post.json();

            if (status_code === 200) {
                tutor_toast(__('Success', 'tutor'), message, 'success');
            } else {
                tutor_toast(__('Failed', 'tutor'), message, 'error');
            }
        } catch (error) {
            tutor_toast(__('Failed', 'tutor'), defaultErrorMessage, 'error');
        } finally {
            button.removeAttribute('disabled');
            button.classList.remove('is-loading');
        }
    });
});

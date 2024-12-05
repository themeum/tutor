import ajaxHandler from "../../../admin-dashboard/segments/filter";
const { __ } = wp.i18n;

document.addEventListener('DOMContentLoaded', function () {
    const defaultErrorMessage = __('Something went wrong, please try again', 'tutor');
    const notificationPrefForm = document.querySelector('#tutor_notification_pref_form');

    if (notificationPrefForm) {
        const buttonSubmit = notificationPrefForm.querySelector('button[type="submit"]');
        const checkboxDisableAll = document.querySelector('#tutor-disable-all-notification');
        const customizePref = document.querySelector('#tutor-customize-notification-preference')
        const iconBell = document.querySelector('.tutor-icon-image-bell')
        const iconBellSlash = document.querySelector('.tutor-icon-image-bell-slash')

        checkboxDisableAll.addEventListener('change', function (e) {
            if (e.target.checked) {
                customizePref.classList.add('tutor-d-none')
                iconBell.classList.add('tutor-d-none')
                iconBellSlash.classList.remove('tutor-d-none')
            } else {
                customizePref.classList.remove('tutor-d-none')
                iconBell.classList.remove('tutor-d-none')
                iconBellSlash.classList.add('tutor-d-none')
            }
        })

        notificationPrefForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(e.target);

            try {
                buttonSubmit.setAttribute('disabled', 'disabled');
                buttonSubmit.classList.add('is-loading');

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
                buttonSubmit.removeAttribute('disabled');
                buttonSubmit.classList.remove('is-loading');
            }
        });
    }
});

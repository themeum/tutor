
import ajaxHandler from './filter';

const {__} = wp.i18n;

document.addEventListener('DOMContentLoaded', async function() {
    const defaultErrMsg = __( "Something went wrong, please try again after refreshing page", "tutor" );
    const keysListWrapper = document.querySelector('.tutor-rest-api-keys-wrapper');
    const listTable = document.querySelector('.tutor-rest-api-keys-wrapper tbody');
    const apiKeysForm = document.getElementById('tutor-generate-api-keys');
    const submitBtn = document.querySelector('#tutor-generate-api-keys button[type=submit]');
    const modal = document.getElementById('tutor-add-new-api-keys');

    if (!keysListWrapper) {
        return;
    }

    apiKeysForm.onsubmit = async (e) => {
        e.preventDefault();

        const formData = new FormData(apiKeysForm);

        try {
            // Show loading
            submitBtn.classList.add('is-loading');
            submitBtn.setAttribute('disabled', true);

            const post = await ajaxHandler(formData);
            const res = await post.json();
            const {success, data} = res;

            if (success) {
                listTable.insertAdjacentHTML(
                    'beforeend',
                    `${data}`
                );
                tutor_toast(__('Success', 'tutor' ), __( 'API key & secret generated successfully'), 'success');
            } else {
                tutor_toast(__('Failed', 'tutor' ), data, 'error');
            }
        } catch (error) {
            tutor_toast(__('Failed', 'tutor' ), defaultErrMsg, 'error');
        } finally {
            submitBtn.classList.remove('is-loading');
            submitBtn.removeAttribute('disabled');
            modal.classList.remove('tutor-is-active');
        }
    }

})
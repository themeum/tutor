
import ajaxHandler from './filter';

const {__} = wp.i18n;

document.addEventListener('DOMContentLoaded', async function() {
    const keysListWrapper = document.querySelector('.tutor-rest-api-keys-wrapper');
    const listTable = document.querySelector('.tutor-rest-api-keys-wrapper tbody');
    const apiKeysForm = document.getElementById('tutor-generate-api-keys');

    if (!keysListWrapper) {
        return;
    }

    apiKeysForm.onsubmit = async (e) => {
        e.preventDefault();

        const formData = new FormData(apiKeysForm);
        const post = await ajaxHandler(formData);
        const res = await post.json();
        const {success, data} = res;

        console.log(data);
        if (success) {
            listTable.insertAdjacentHTML(
                'beforeend',
                data
            );
        } else {
            tutor_toast(__('Failed', 'tutor' ), data, 'error');
        }
    }

})
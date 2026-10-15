/**
 * Prepare custom form data, while setting form data
 * it will also set tutor nonce field.
 * 
 * @since v2.1.0
 * 
 * @param formId  form id attribute
 * @param data array of objects of form elements. Key value par
 * like: [{name: 'john doe'}, {age: 100}]
 * 
 * @return mixed formData on success, false on any error
 */


if (!window.tutor_get_nonce_data) {
    window.tutor_get_nonce_data = function(send_key_value) {
        var nonce_data = window._tutorobject || {};
        var nonce_key = nonce_data.nonce_key || '';
        var nonce_value = nonce_data[nonce_key] || '';
    
        if (send_key_value) {
            return { key: nonce_key, value: nonce_value };
        }
    
        return { [nonce_key]: nonce_value };
    };
}

function tutorFormData(data = []) {
        const formData = new FormData();
        data.forEach((item) => {
            for (const [key, value] of Object.entries(item)) {
                formData.set(key, value)
            }
        });
        formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);
        return formData;
}
export default tutorFormData;
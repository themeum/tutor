
const get_response_message=(response, def_message)=> {
    const {__} = wp.i18n;
    const {data={}} = response || {};
    const {message=(def_message || __('Something Went Wrong!', 'tutor'))} = data;
    return message;
}

export {get_response_message}
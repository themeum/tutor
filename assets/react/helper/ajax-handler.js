export default async function ajaxHandler(formData) {
    try {
        const post = await fetch(window._tutorobject.ajaxurl, {
            method: 'POST',
            body: formData,
        });
        return post;
    } catch (error) {
        tutor_toast(__('Operation failed', 'tutor'), error, 'error');
    }
}

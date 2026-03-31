import ajaxHandler from "../helper/ajax-handler";

/**
 * Manage quiz attempt page script
 * 
 * this script has imported inside common.js
 * because both front-end & back-end will use this
 * script
 *
 * @since v2.1.0
 */
window.addEventListener('DOMContentLoaded', function() {
    const { __ } = wp.i18n;
    let targetRow;
    const currentPage = _tutorobject.current_page;
    const modal = document.getElementById('tutor-common-confirmation-modal');
    // Check if it is quiz attempt page.
    if (currentPage === 'quiz-attempts' || currentPage === 'tutor_quiz_attempts' ) {
        const deleteButtons = document.querySelectorAll('.tutor-quiz-attempt-delete');
        const deleteForm = document.getElementById('tutor-common-confirmation-form');
        const defaultErrorMsg = __( 'Something went wrong, please try again', 'tutor' );
        deleteButtons.forEach((button) => {
            button.onclick = (e) => {
               
                const target = e.target;
                const attemptId = target.dataset.quizId;
                targetRow = target.closest('tr');
                if (deleteForm) {
                    deleteForm.querySelector('[name=id]').value = attemptId;
                    deleteForm.querySelector('[name=action]').value = "tutor_attempt_delete";
                }
            }
        });
        if (deleteForm) {
            deleteForm.onsubmit = async (e) => {
                e.preventDefault();
                const submitButton = deleteForm.querySelector('button[data-tutor-modal-submit]');
                const formData = new FormData(deleteForm);

                submitButton.classList.add('is-loading');
                submitButton.setAttribute('disabled', true);

                const post = await ajaxHandler(formData);
                try {
                    if (post.ok) {
                        const response = await post.json();
                        const {success, data} = response;
                        if (success) {
                            tutor_toast(__('Success', 'tutor'), data, 'success');
                            window.location.reload();
                            
                        } else {
                            tutor_toast(__('Failed', 'tutor'), data, 'error');
                        }
                    } else {
                        tutor_toast(__('Failed', 'tutor'), defaultErrorMsg, 'error');
                    }
                } catch(err) {
                    tutor_toast(__('Failed', 'tutor'), defaultErrorMsg, 'error');
                } finally {
                    submitButton.classList.remove('is-loading');
                    submitButton.removeAttribute('disabled');
                    modal.classList.remove('tutor-is-active');
                }
            }
        }
    }
});
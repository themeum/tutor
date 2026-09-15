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
    const defaultErrorMsg = __( 'Something went wrong, please try again', 'tutor' );

    // Question-level feedback handlers (task 6.5).
    initQuestionFeedbackHandlers(__, defaultErrorMsg);

    // Check if it is quiz attempt page.
    if (currentPage === 'quiz-attempts' || currentPage === 'tutor_quiz_attempts' ) {
        const deleteButtons = document.querySelectorAll('.tutor-quiz-attempt-delete');
        const deleteForm = document.getElementById('tutor-common-confirmation-form');
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

/**
 * Question-level feedback modal handlers.
 *
 * @param {Function} __ i18n translate.
 * @param {string}    defaultErrorMsg Fallback error text.
 *
 * @return {void}
 */
function initQuestionFeedbackHandlers(__, defaultErrorMsg) {
    const feedbackModal   = document.getElementById('tutor-question-feedback-modal');
    const deleteModal     = document.getElementById('tutor-question-feedback-delete-modal');

    if (!feedbackModal || !deleteModal) return;

    const attemptInput    = document.getElementById('tutor-question-feedback-attempt-id');
    const answerInput     = document.getElementById('tutor-question-feedback-answer-id');
    const textarea        = document.getElementById('tutor-question-feedback-content');
    const saveBtn         = document.getElementById('tutor-question-feedback-save');
    const deleteBtn       = document.getElementById('tutor-question-feedback-delete');
    const deleteConfirmBtn = document.getElementById('tutor-question-feedback-delete-confirm');

    // Open feedback modal when "Add Feedback" or "Show Feedback" is clicked.
    document.addEventListener('click', (e) => {
        const link = e.target.closest('.quiz-question-feedback-action');
        if (!link) return;
        e.preventDefault();

        attemptInput.value = link.dataset.attemptId || '';
        answerInput.value  = link.dataset.attemptAnswerId || '';
        textarea.value     = link.dataset.feedback || '';

        openModal(feedbackModal, link);
    });

    // Save feedback via AJAX.
    saveBtn.addEventListener('click', async () => {
        const attemptId     = attemptInput.value;
        const attemptAnswerId = answerInput.value;
        const feedback      = textarea.value.trim();

        if (!attemptId || !attemptAnswerId) return;

        const formData = new FormData();
        formData.append('action', 'tutor_save_question_feedback');
        formData.append('attempt_id', attemptId);
        formData.append('attempt_answer_id', attemptAnswerId);
        formData.append('feedback', feedback);
        formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);

        saveBtn.classList.add('is-loading');
        saveBtn.setAttribute('disabled', true);

        try {
            const response = await fetch(_tutorobject.ajaxurl, { method: 'POST', body: formData });
            const result   = await response.json();

            if (result.success) {
                tutor_toast(__('Saved', 'tutor'), result.data || __('Feedback saved', 'tutor'), 'success');
                closeModal(feedbackModal);
                window.location.reload();
            } else {
                tutor_toast(__('Error', 'tutor'), result.data || defaultErrorMsg, 'error');
            }
        } catch {
            tutor_toast(__('Error', 'tutor'), defaultErrorMsg, 'error');
        } finally {
            saveBtn.classList.remove('is-loading');
            saveBtn.removeAttribute('disabled');
        }
    });

    // Open delete confirmation modal.
    deleteBtn.addEventListener('click', () => {
        closeModal(feedbackModal, false);
        openModal(deleteModal, deleteBtn);
    });

    // Confirm delete via AJAX.
    deleteConfirmBtn.addEventListener('click', async () => {
        const attemptId       = attemptInput.value;
        const attemptAnswerId = answerInput.value;

        if (!attemptId || !attemptAnswerId) return;

        const formData = new FormData();
        formData.append('action', 'tutor_delete_question_feedback');
        formData.append('attempt_id', attemptId);
        formData.append('attempt_answer_id', attemptAnswerId);
        formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);

        deleteConfirmBtn.classList.add('is-loading');
        deleteConfirmBtn.setAttribute('disabled', true);

        try {
            const response = await fetch(_tutorobject.ajaxurl, { method: 'POST', body: formData });
            const result   = await response.json();

            if (result.success) {
                tutor_toast(__('Deleted', 'tutor'), result.data || __('Feedback deleted', 'tutor'), 'success');
                closeModal(deleteModal);
                window.location.reload();
            } else {
                tutor_toast(__('Error', 'tutor'), result.data || defaultErrorMsg, 'error');
            }
        } catch {
            tutor_toast(__('Error', 'tutor'), defaultErrorMsg, 'error');
        } finally {
            deleteConfirmBtn.classList.remove('is-loading');
            deleteConfirmBtn.removeAttribute('disabled');
        }
    });
}

/**
 * Open a modal element using the tutor-modal system.
 *
 * @param {HTMLElement} modal   The .tutor-modal element.
 * @param {HTMLElement} [trigger] The originating click target for focus restore.
 */
function openModal(modal, trigger) {
    if (!modal) return;
    modal.classList.add('tutor-is-active');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('tutor-modal-open');
    const autofocus = modal.querySelector('[autofocus]');
    const firstInput = modal.querySelector('input:not([type="hidden"]), textarea, select');
    const target = autofocus || firstInput;
    if (target) {
        requestAnimationFrame(() => target.focus());
    }
}

/**
 * Close a modal element.
 *
 * @param {HTMLElement} modal The .tutor-modal element.
 */
function closeModal(modal) {
    if (!modal) return;
    modal.classList.remove('tutor-is-active');
    modal.setAttribute('aria-hidden', 'true');
    if (!document.querySelector('.tutor-modal.tutor-is-active')) {
        document.body.classList.remove('tutor-modal-open');
    }
}
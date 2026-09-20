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

/**
 * Question-level feedback modal handlers.
 *
 * @param {Function} __ i18n translate.
 * @param {string}    defaultErrorMsg Fallback error text.
 *
 * @return {void}
 */
function initQuestionFeedbackHandlers(__, defaultErrorMsg) {
    let activeTriggerLink = null;

    // The attempt-details markup — including these modals — is re-rendered by AJAX
    // after a manual review action, so node references are re-queried on every
    // interaction and all handlers are bound via event delegation.
    const feedbackModalElements = () => ({
        feedbackModal:      document.getElementById('tutor-question-feedback-modal'),
        modalTitle:         document.getElementById('tutor-question-feedback-modal-title'),
        attemptInput:       document.getElementById('tutor-question-feedback-attempt-id'),
        answerInput:        document.getElementById('tutor-question-feedback-answer-id'),
        textarea:           document.getElementById('tutor-question-feedback-content'),
        deleteBtn:          document.getElementById('tutor-question-feedback-delete'),
        deleteConfirmModal: document.getElementById('tutor-question-feedback-delete-modal'),
    });

    // Helper to update the trigger button state.
    const updateTriggerLinkState = (link, hasFeedback, feedbackText = '') => {
        if (!link) return;
        link.dataset.feedback = feedbackText;
        const icon  = hasFeedback ? 'tutor-icon-eye-line' : 'tutor-icon-comment';
        const label = hasFeedback ? __('Show Feedback', 'tutor') : __('Add Feedback', 'tutor');
        link.innerHTML = `<span class="${icon} tutor-mr-4"></span>${label}`;
        link.setAttribute('title', hasFeedback ? __('Show feedback', 'tutor') : __('Add feedback', 'tutor'));
    };

    const openFeedbackModal = (link) => {
        const { feedbackModal, modalTitle, attemptInput, answerInput, textarea, deleteBtn } = feedbackModalElements();
        if (!feedbackModal || !attemptInput || !answerInput || !textarea) return;

        activeTriggerLink     = link;
        attemptInput.value    = link.dataset.attemptId || '';
        answerInput.value     = link.dataset.attemptAnswerId || '';
        const currentFeedback = (link.dataset.feedback || '').trim();
        textarea.value        = currentFeedback;
        if (modalTitle) {
            modalTitle.textContent = currentFeedback ? __('Edit feedback', 'tutor') : __('Write feedback', 'tutor');
        }
        if (deleteBtn) {
            deleteBtn.style.display = currentFeedback ? '' : 'none';
        }
        openModal(feedbackModal, link);
    };

    // Save or delete feedback via AJAX with shared loading state and error toast.
    const persistFeedback = async (action, button) => {
        const { attemptInput, answerInput, textarea } = feedbackModalElements();
        const attemptId       = attemptInput.value;
        const attemptAnswerId = answerInput.value;
        const feedback        = textarea.value.trim();

        if (!attemptId || !attemptAnswerId) return null;

        const formData = new FormData();
        formData.append('action', action);
        formData.append('attempt_id', attemptId);
        formData.append('attempt_answer_id', attemptAnswerId);
        formData.append('feedback', feedback);
        formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);

        button.classList.add('is-loading');
        button.setAttribute('disabled', true);

        try {
            const response = await fetch(_tutorobject.ajaxurl, { method: 'POST', body: formData });
            const result   = await response.json();
            if (result.success) return result;
            tutor_toast(__('Error', 'tutor'), result.data || defaultErrorMsg, 'error');
        } catch {
            tutor_toast(__('Error', 'tutor'), defaultErrorMsg, 'error');
        } finally {
            button.classList.remove('is-loading');
            button.removeAttribute('disabled');
        }

        return null;
    };

    // Open feedback modal when "Add Feedback" or "Show Feedback" is clicked.
    document.addEventListener('click', (e) => {
        const link = e.target.closest('.quiz-question-feedback-action');
        if (!link) return;
        e.preventDefault();
        openFeedbackModal(link);
    });

    // Save feedback via AJAX.
    document.addEventListener('click', async (e) => {
        const saveBtn = e.target.closest('#tutor-question-feedback-save');
        if (!saveBtn) return;

        const { textarea, feedbackModal } = feedbackModalElements();
        const feedback = textarea.value.trim();
        const result   = await persistFeedback('tutor_save_question_feedback', saveBtn);
        if (!result) return;

        tutor_toast(__('Saved', 'tutor'), result.data || __('Feedback saved', 'tutor'), 'success');
        updateTriggerLinkState(activeTriggerLink, '' !== feedback, feedback);
        closeModal(feedbackModal);
    });

    // Open delete confirmation modal.
    document.addEventListener('click', (e) => {
        const deleteBtn = e.target.closest('#tutor-question-feedback-delete');
        if (!deleteBtn) return;

        const { feedbackModal, deleteConfirmModal } = feedbackModalElements();
        closeModal(feedbackModal, false);
        openModal(deleteConfirmModal, deleteBtn);
    });

    // Confirm delete via AJAX.
    document.addEventListener('click', async (e) => {
        const deleteConfirmBtn = e.target.closest('#tutor-question-feedback-delete-confirm');
        if (!deleteConfirmBtn) return;

        const { textarea, deleteConfirmModal } = feedbackModalElements();
        const result = await persistFeedback('tutor_delete_question_feedback', deleteConfirmBtn);
        if (!result) return;

        tutor_toast(__('Deleted', 'tutor'), result.data || __('Feedback deleted', 'tutor'), 'success');
        updateTriggerLinkState(activeTriggerLink, false, '');
        textarea.value = '';
        closeModal(deleteConfirmModal);
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
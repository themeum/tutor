/**
 * Tutor Modal – Accessible & Keyboard-friendly
 *
 * Features:
 *  - Focus trap (Tab / Shift+Tab cycle within the modal)
 *  - Escape key closes topmost modal
 *  - Focus management: prioritizes [autofocus] then first input
 *  - Focus restoration on close (supports stacked modals)
 *  - Body scroll lock
 *  - Auto-initialization for modals active on page load or class toggle
 *
 * @since 1.0.0
 */

(function () {
	const FOCUSABLE_SELECTOR = [
		'a[href]:not([disabled]):not([tabindex="-1"])',
		'button:not([disabled]):not([tabindex="-1"])',
		'textarea:not([disabled]):not([tabindex="-1"])',
		'input:not([disabled]):not([tabindex="-1"]):not([type="hidden"])',
		'select:not([disabled]):not([tabindex="-1"])',
		'[tabindex]:not([tabindex="-1"])',
	].join(', ');

	/** @type {HTMLElement[]} Stack of elements that opened modals – for focus restoration */
	const triggerStack = [];

	/**
	 * Get the topmost active modal in the DOM.
	 *
	 * @returns {HTMLElement|null}
	 */
	function getTopmostModal() {
		const activeModals = document.querySelectorAll('.tutor-modal.tutor-is-active');
		return activeModals.length ? activeModals[activeModals.length - 1] : null;
	}

	/**
	 * Open a modal by element reference.
	 *
	 * @param {HTMLElement} modal  The `.tutor-modal` element to activate.
	 * @param {HTMLElement} [trigger] The element that triggered the open (for focus restore).
	 */
	function openModal(modal, trigger) {
		if (!modal) return;

		// Save the trigger element to the stack for focus restoration.
		const triggerToSave = trigger || document.activeElement;
		if (triggerToSave && !triggerStack.includes(triggerToSave)) {
			triggerStack.push(triggerToSave);
		}

		modal.classList.add('tutor-is-active');
		modal.setAttribute('aria-hidden', 'false');
		document.body.classList.add('tutor-modal-open');

		// Move focus into the modal.
		requestAnimationFrame(() => {
			const autofocus = modal.querySelector('[autofocus]');
			const firstInput = modal.querySelector('input:not([type="hidden"]), textarea, select');
			const firstFocusable = modal.querySelector(FOCUSABLE_SELECTOR);

			const target = autofocus || firstInput || firstFocusable;

			if (target) {
				target.focus();
			} else {
				const modalWindow = modal.querySelector('.tutor-modal-window');
				if (modalWindow) {
					modalWindow.setAttribute('tabindex', '-1');
					modalWindow.focus();
				}
			}
		});

		// Dispatch legacy custom event.
		window.dispatchEvent(new CustomEvent('tutor_modal_shown', { detail: trigger }));
	}

	/**
	 * Close a modal by element reference.
	 *
	 * @param {HTMLElement} modal          The `.tutor-modal` element to deactivate.
	 * @param {boolean}     [restoreFocus=true] Whether to restore focus to the trigger.
	 */
	function closeModal(modal, restoreFocus = true) {
		if (!modal) return;

		modal.classList.remove('tutor-is-active');
		modal.setAttribute('aria-hidden', 'true');

		// Only remove body class when no other modals are active.
		if (!document.querySelector('.tutor-modal.tutor-is-active')) {
			document.body.classList.remove('tutor-modal-open');
		}

		if (restoreFocus && triggerStack.length) {
			const lastTrigger = triggerStack.pop();
			if (lastTrigger && typeof lastTrigger.focus === 'function') {
				lastTrigger.focus();
			}
		}
	}

	/**
	 * Trap focus within the topmost active modal.
	 */
	function trapFocus(e) {
		const activeModal = getTopmostModal();
		if (!activeModal) return;

		const focusableEls = activeModal.querySelectorAll(FOCUSABLE_SELECTOR);
		if (!focusableEls.length) return;

		const firstEl = focusableEls[0];
		const lastEl = focusableEls[focusableEls.length - 1];

		if (e.shiftKey) {
			if (document.activeElement === firstEl) {
				e.preventDefault();
				lastEl.focus();
			}
		} else {
			if (document.activeElement === lastEl) {
				e.preventDefault();
				firstEl.focus();
			}
		}
	}

	// Keyboard Listeners
	document.addEventListener('keydown', (e) => {
		const activeModal = getTopmostModal();
		if (!activeModal) return;

		if (e.key === 'Escape' || e.key === 'Esc') {
			e.preventDefault();
			closeModal(activeModal);
		} else if (e.key === 'Tab') {
			trapFocus(e);
		}
	});

	// Click Listeners
	document.addEventListener('click', (e) => {
		const attr = 'data-tutor-modal-target';
		const closeAttr = 'data-tutor-modal-close';
		const overlay = 'tutor-modal-overlay';

		const openTrigger = e.target.hasAttribute(attr) ? e.target : e.target.closest(`[${attr}]`);
		if (openTrigger) {
			e.preventDefault();
			const id = openTrigger.getAttribute(attr);
			const modal = document.getElementById(id);
			if (modal) openModal(modal, openTrigger);
			return;
		}

		if (e.target.hasAttribute(closeAttr) || e.target.classList.contains(overlay) || e.target.closest(`[${closeAttr}]`)) {
			e.preventDefault();
			const modal = e.target.closest('.tutor-modal.tutor-is-active');
			if (modal) closeModal(modal);
		}
	});

	// MutationObserver to watch for class changes – Ultimate fix for legacy script interference.
	const observer = new MutationObserver((mutations) => {
		mutations.forEach((mutation) => {
			if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
				const modal = mutation.target;
				if (modal.classList.contains('tutor-is-active') && modal.getAttribute('aria-hidden') !== 'false') {
					openModal(modal);
				}
			}
		});
	});

	// Initial setup & start observing
	document.addEventListener('DOMContentLoaded', () => {
		const modals = document.querySelectorAll('.tutor-modal');
		modals.forEach(modal => {
			observer.observe(modal, { attributes: true, attributeFilter: ['class'] });

			// Initialize already-active modals.
			if (modal.classList.contains('tutor-is-active')) {
				openModal(modal);
			}
		});
	});
})();

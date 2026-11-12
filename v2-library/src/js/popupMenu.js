// dropdown
(function tutorDropdownMenu() {
	const dropdownCloseEvent = new Event("tutor_dropdown_closed");

	document.addEventListener('click', (e) => {
		const dropdownAttr = 'action-tutor-dropdown';
		const dropDownTarget = e.target.hasAttribute(dropdownAttr)
			? e.target
			: e.target.closest(`[${dropdownAttr}]`);

		if (dropDownTarget && dropDownTarget.hasAttribute(dropdownAttr)) {
			e.preventDefault();

			const dropdownParent = dropDownTarget.closest('.tutor-dropdown-parent');

			if (dropdownParent.classList.contains('is-open')) {
				dropdownParent.classList.remove('is-open');
				dropdownParent.dispatchEvent(dropdownCloseEvent);
			} else {
				document
					.querySelectorAll('.tutor-dropdown-parent')
					.forEach((dropdownParent) => {
						dropdownParent.classList.remove('is-open');
					});
				dropdownParent.classList.add('is-open');
			}
		} else {
			const restrictedDataAttributes = ['data-tutor-copy-target', 'data-tutor-dropdown-persistent'];
			const closeDataAttributes = 'data-tutor-dropdown-close';
			const isRestricted = restrictedDataAttributes.some(
				(restrictedDataAttribute) => {
					return (
						e.target.hasAttribute(restrictedDataAttribute) ||
						e.target.closest(`[${restrictedDataAttribute}]`) ||
						e.target.closest('.react-datepicker') ||
						e.target.classList.contains('react-datepicker__close-icon')
					);
				},
			);

			if (!isRestricted || e.target.hasAttribute(closeDataAttributes) || e.target.closest(`[${closeDataAttributes}]`)) {
				document
					.querySelectorAll('.tutor-dropdown-parent')
					.forEach((dropdownParent) => {
						if (dropdownParent.classList.contains('is-open')) {
							dropdownParent.classList.remove('is-open');
							dropdownParent.dispatchEvent(dropdownCloseEvent);
						}
					});
			}
		}
	});
})();

/**
 * Show clipboard feedback via toast when available, otherwise tooltip.
 *
 * @param {HTMLElement} targetEl Click target for tooltip fallback.
 * @param {string}      title    Toast title.
 * @param {string}      message  Toast / tooltip message.
 * @param {'success'|'error'|'warning'} [type='success'] Toast type.
 * @return {void}
 */
const showCopyFeedback = (targetEl, title, message, type = 'success') => {
	if (typeof window.tutor_toast === 'function') {
		window.tutor_toast(title, message, type);
		return;
	}

	showToolTip(targetEl, message);
};

/**
 * Copy to clipboard
 */
document.addEventListener('click', async (e) => {
	const btnTargetAttr = 'data-tutor-copy-target';
	const copyBtn = e.target.closest(`[${btnTargetAttr}]`);
	if (!copyBtn) {
		return;
	}

	const { __ } = wp.i18n;
	const id = copyBtn.getAttribute(btnTargetAttr);

	/* Get the text field */
	const content = document.getElementById(id).textContent.trim();

	try {
		await copyToClipboard(content);

		if (content) {
			showCopyFeedback(
				copyBtn,
				__('Copied!', 'tutor'),
				__('Copied to clipboard', 'tutor'),
				'success',
			);
		} else {
			showCopyFeedback(
				copyBtn,
				__('Failed', 'tutor'),
				__('Nothing Found!', 'tutor'),
				'error',
			);
		}
	} catch (err) {
		showCopyFeedback(
			copyBtn,
			__('Failed', 'tutor'),
			__('Failed to copy', 'tutor'),
			'error',
		);
	}
});

/**
 * Copy text to clipboard.
 *
 * Uses the Clipboard API when available (secure contexts), otherwise
 * falls back to execCommand for HTTP / unsupported browsers.
 *
 * @param {string} text Text to copy.
 * @return {Promise<void>}
 */
const copyToClipboard = (text) => {
	if (navigator.clipboard && window.isSecureContext) {
		return navigator.clipboard.writeText(text);
	}

	return new Promise((resolve, reject) => {
		const textArea = document.createElement('textarea');
		textArea.value = text;
		textArea.style.position = 'fixed';
		textArea.style.left = '-999999px';
		textArea.style.top = '-999999px';
		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();

		try {
			// Clipboard API is unavailable outside secure contexts; this is the only fallback.
			// Cast avoids the deprecated Document.execCommand DOM typing while keeping HTTP support.
			const legacyExecCommand = /** @type {(commandId: string) => boolean} */ (
				/** @type {Record<string, unknown>} */ (document).execCommand
			);
			legacyExecCommand.call(document, 'copy')
				? resolve()
				: reject(new Error('Copy command failed'));
		} catch (err) {
			reject(err);
		} finally {
			textArea.remove();
		}
	});
};

// Showing tooltip
const showToolTip = (targetEl, text = 'Copied!') => {
	const toolTip = `<span class="tutor-tooltip tooltip-wrap"><span class="tooltip-txt tooltip-top">${text}</span></span>`;
	targetEl.insertAdjacentHTML('afterbegin', toolTip);

	setTimeout(() => {
		document.querySelector('.tutor-tooltip').remove();
	}, 500);
};

/**
 * Input Field - Copy/Paste to/from clipboard
 */
document.addEventListener('click', async (e) => {
	const copyTargetAttr = 'data-tutor-clipboard-copy-target';
	const pasteTargetAttr = 'data-tutor-clipboard-paste-target';
	const { __ } = wp.i18n;

	const copyBtn = e.target.closest(`[${copyTargetAttr}]`);
	if (copyBtn) {
		const id = copyBtn.getAttribute(copyTargetAttr);

		/* Get the text field */
		const text = document.getElementById(id).value;

		/* Copy text into clipboard */
		if (text) {
			try {
				await copyToClipboard(text);
				showCopyFeedback(
					copyBtn,
					__('Copied!', 'tutor'),
					__('Copied to clipboard', 'tutor'),
					'success',
				);
			} catch (err) {
				showCopyFeedback(
					copyBtn,
					__('Failed', 'tutor'),
					__('Failed to copy', 'tutor'),
					'error',
				);
			}
		}
		return;
	}

	const pasteBtn = e.target.closest(`[${pasteTargetAttr}]`);
	if (!pasteBtn) {
		return;
	}

	if (!navigator.clipboard || !window.isSecureContext) {
		showCopyFeedback(
			pasteBtn,
			__('Failed', 'tutor'),
			__('Paste not supported', 'tutor'),
			'error',
		);
		return;
	}

	const id = pasteBtn.getAttribute(pasteTargetAttr);

	try {
		const text = await navigator.clipboard.readText();

		/* Pasting on the text field */
		if (text) {
			document.getElementById(id).value = text;
			showCopyFeedback(
				pasteBtn,
				__('Success', 'tutor'),
				__('Pasted', 'tutor'),
				'success',
			);
		}
	} catch (err) {
		showCopyFeedback(
			pasteBtn,
			__('Failed', 'tutor'),
			__('Paste failed', 'tutor'),
			'error',
		);
	}
});

/**
 * Toggle disabled .tutor-clipboard-input-field .tutor-btn
 * .tutor-clipboard-input-field .tutor-btn
 */
const copyBtn = document.querySelector('.tutor-clipboard-input-field .tutor-btn');
if (copyBtn) {
	document.querySelector('.tutor-clipboard-input-field .tutor-form-control').addEventListener('input', (e) => {
		e.target.value ? copyBtn.removeAttribute('disabled') : copyBtn.setAttribute('disabled', '');
	});
}

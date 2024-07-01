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
			const restrictedDataAttributes = ['data-tutor-copy-target'];
			const isRestricted = restrictedDataAttributes.some(
				(restrictedDataAttribute) => {
					return (
						e.target.hasAttribute(restrictedDataAttribute) ||
						e.target.closest(`[${restrictedDataAttribute}]`)
					);
				},
			);

			if (!isRestricted) {
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
 * Copy to clipboard
 */
document.addEventListener('click', async (e) => {
	const btnTargetAttr = 'data-tutor-copy-target';
	if (e.target.hasAttribute(btnTargetAttr)) {
		const id = e.target.getAttribute(btnTargetAttr);

		/* Get the text field */
		const content = document.getElementById(id).textContent.trim();
		await copyToClipboard(content);

		if (content) {
			showToolTip(e.target, 'Copied');
		} else {
			showToolTip(e.target, 'Nothing Found!');
		}
	}
});

// Copy text to clipboard
const copyToClipboard = (text) => {
	return new Promise((resolve) => {
		const textArea = document.createElement('textarea');
		textArea.value = text;
		document.body.appendChild(textArea);
		textArea.select();
		document.execCommand('copy');
		document.body.removeChild(textArea);
		resolve();
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

	if (e.target.hasAttribute(copyTargetAttr)) {
		const id = e.target.getAttribute(copyTargetAttr);

		/* Get the text field */
		const text = document.getElementById(id).value;

		/* Copy text into clipboard */
		if (text) {
			await navigator.clipboard.writeText(text);
			showToolTip(e.target, 'Copied');
		}
	}

	if (e.target.hasAttribute(pasteTargetAttr)) {
		const id = e.target.getAttribute(pasteTargetAttr);

		const text = await navigator.clipboard.readText();

		/* Pasting on the text field */
		if (text) {
			document.getElementById(id).value = text;
			showToolTip(e.target, 'Pasted');
		}
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

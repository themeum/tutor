// dropdown
(function tutorDropdownMenu() {
	document.addEventListener('click', (e) => {
		const attr = 'action-tutor-dropdown';

		if (e.target.hasAttribute(attr)) {
			e.preventDefault();

			const dropdownParent = e.target.closest('.tutor-dropdown-parent');

			if (dropdownParent.classList.contains('is-open')) {
				dropdownParent.classList.remove('is-open');
			} else {
				document.querySelectorAll('.tutor-dropdown-parent').forEach((dropdownParent) => {
					dropdownParent.classList.remove('is-open');
				});
				dropdownParent.classList.add('is-open');
			}
		} else {
			document.querySelectorAll('.tutor-dropdown-parent').forEach((dropdownParent) => {
				dropdownParent.classList.remove('is-open');
			});
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

		/* Copy the text inside the text field */
		await navigator.clipboard.writeText(content);
		const copiedTxt = await navigator.clipboard.readText();

		if (content) {
			showToolTip(e.target, 'Copied');
		} else {
			showToolTip(e.target, 'Nothing Found!');
		}
	}
});

// Showing tooltip
const showToolTip = (targetEl, text = 'Copied!') => {
	const toolTip = `<span class="tutor-tooltip">${text}</span>`;
	targetEl.insertAdjacentHTML('beforebegin', toolTip);

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

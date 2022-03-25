(function tutorPopupMenu() {
	/**
	 * Popup Menu Toggle .tutor-popup-opener
	 */

	document.addEventListener('click', (e) => {
		const attr = 'data-tutor-popup-target';

		if (e.target.hasAttribute(attr)) {
			e.preventDefault();
			const id = e.target.hasAttribute(attr)
				? e.target.getAttribute(attr)
				: e.target.closest(`[${attr}]`).getAttribute(attr);

			const popupMenu = document.getElementById(id);

			if (popupMenu.classList.contains('visible')) {
				popupMenu.classList.remove('visible');
			} else {
				document.querySelectorAll('.tutor-popup-opener .popup-menu').forEach((popupMenu) => {
					popupMenu.classList.remove('visible');
				});
				popupMenu.classList.add('visible');
			}
		} else {
			document.querySelectorAll('.tutor-popup-opener .popup-menu').forEach((popupMenu) => {
				popupMenu.classList.remove('visible');
			});
		}

		/**
		 * Popupover Menu Toggle .tutor-popover
		 */
		const popoverAttr = 'data-tutor-popover-target';
		if (e.target.hasAttribute(popoverAttr)) {
			e.preventDefault();
			const id = e.target.getAttribute(popoverAttr);
			const popoverMenu = document.getElementById(id);
			const popoverWrapper = popoverMenu.closest('.tutor-popover-wrapper');
			popoverMenu.classList.toggle('is-active');
			popoverWrapper.classList.toggle('is-active');
		} else {
			const backdropAttr = 'data-tutor-popover-backdrop';
			if (e.target.hasAttribute(backdropAttr)) {
				const activePopover = document.querySelectorAll('.tutor-popover.is-active, .tutor-popover-wrapper.is-active');
				activePopover.forEach((item) => {
					item.classList.remove('is-active');
				});
			}
		}
	});
})();

/**
 * Popupover - Copy to clipboard
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

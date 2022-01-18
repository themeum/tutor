let enterPressed = false;
document.addEventListener('keypress', function (e) {
	if (e.key === 'Enter') {
		enterPressed = true;
	}
});

document.addEventListener('click', (e) => {
	const attr = 'data-tutor-modal-target';
	const closeAttr = 'data-tutor-modal-close';
	const overlay = 'tutor-modal-overlay';

	if (enterPressed !== false) {
		enterPressed = false;
		return false;
	}

	if ((e.target.hasAttribute(attr) || e.target.closest(`[${attr}]`))) {
		// console.log(enterPressed);
		e.preventDefault();
		const id = e.target.hasAttribute(attr)
			? e.target.getAttribute(attr)
			: e.target.closest(`[${attr}]`).getAttribute(attr);
		const modal = document.getElementById(id);

		if (modal) {
			document
				.querySelectorAll('.tutor-modal.tutor-is-active')
				.forEach((item) => item.classList.remove('tutor-is-active'));
			modal.classList.add('tutor-is-active');
		}
	}

	if (
		e.target.hasAttribute(closeAttr) ||
		e.target.classList.contains(overlay) ||
		e.target.closest(`[${closeAttr}]`)
	) {
		e.preventDefault();
		const modal = document.querySelectorAll('.tutor-modal.tutor-is-active');
		modal.forEach((m) => {
			m.classList.remove('tutor-is-active');
		});
	}
});

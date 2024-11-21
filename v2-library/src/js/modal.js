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
		e.preventDefault();
		const id = e.target.hasAttribute(attr)
			? e.target.getAttribute(attr)
			: e.target.closest(`[${attr}]`).getAttribute(attr);
		const modal = document.getElementById(id);

		if (modal) {
			document.querySelectorAll('.tutor-modal.tutor-is-active').forEach((item) => item.classList.remove('tutor-is-active'));
			modal.classList.add('tutor-is-active');
			document.body.classList.add("tutor-modal-open");

			const customEvent = new CustomEvent('tutor_modal_shown', {detail: e.target});
			window.dispatchEvent(customEvent);
		}
	}

	if (
		e.target.hasAttribute(closeAttr) ||
		e.target.classList.contains(overlay) ||
		e.target.closest(`[${closeAttr}]`)
	) {
		e.preventDefault();
		const modal = document.querySelectorAll('.tutor-modal.tutor-is-active');
		const tutorBtns = document.querySelectorAll('.tutor-btn');
		modal.forEach((m) => {
			m.classList.remove('tutor-is-active');
		});
		tutorBtns.forEach((btn) => {
			btn.classList.remove('is-loading');
			btn.classList.remove('tutor-static-loader');
			btn.removeAttribute('disabled');
		});
		document.body.classList.remove("tutor-modal-open");
	}
});

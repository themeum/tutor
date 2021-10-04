/**
 * Tutor Off Canvas
 */
(function tutorOffCanvas() {
	document.addEventListener('click', (e) => {
		const attr = 'data-tutor-offcanvas-target';
		const closeAttr = 'data-tutor-offcanvas-close';
		const backdrop = 'tutor-offcanvas-backdrop';

		console.log(e.target);

		// Opening Offcanvas
		if (e.target.hasAttribute(attr)) {
			e.preventDefault();
			const id = e.target.hasAttribute(attr)
				? e.target.getAttribute(attr)
				: e.target.closest(`[${attr}]`).getAttribute(attr);

			const offcanvas = document.getElementById(id);
			if (offcanvas) {
				offcanvas.classList.add('is-active');
			}
		}

		// Closing Offcanvas
		if (
			e.target.hasAttribute(closeAttr) ||
			e.target.classList.contains(backdrop) ||
			e.target.closest(`[${closeAttr}]`)
		) {
			e.preventDefault();
			const activeOffcanvas = document.querySelectorAll('.tutor-offcanvas.is-active');
			activeOffcanvas.forEach((m) => {
				m.classList.remove('is-active');
			});
		}
	});

	// Closing Offcanvas on esc key
	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape') {
			const activeOffcanvas = document.querySelectorAll('.tutor-offcanvas.is-active');
			activeOffcanvas.forEach((m) => {
				m.classList.remove('is-active');
			});
		}
	});
})();

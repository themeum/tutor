/**
 * Tutor Default Tab
 */
(function tutorDefaultTab() {
	document.addEventListener('click', (e) => {
		const attr = 'data-tutor-tab-target';
		const activeItems = document.querySelectorAll('.tab-header-item.is-active, .tab-body-item.is-active');

		if (e.target.hasAttribute(attr)) {
			e.preventDefault();
			const id = e.target.hasAttribute(attr)
				? e.target.getAttribute(attr)
				: e.target.closest(`[${attr}]`).getAttribute(attr);

			const tabBodyItem = document.getElementById(id);

			if (e.target.hasAttribute(attr) && tabBodyItem) {
				activeItems.forEach((m) => {
					m.classList.remove('is-active');
				});

				e.target.classList.add('is-active');
				tabBodyItem.classList.add('is-active');
			}
		}
	});
})();

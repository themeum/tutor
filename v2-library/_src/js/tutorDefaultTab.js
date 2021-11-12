(function tutorDefaultTab() {
	document.addEventListener('click', (e) => {
		/**
		 * Tutor Default Tab
		 */
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

		/**
		 * Tutor Default Tab - see more
		 */
		const seeMoreAttr = 'data-seemore-target';
		if (e.target.hasAttribute(seeMoreAttr)) {
			const id = e.target.getAttribute(seeMoreAttr);
			document
				.getElementById(`${id}`)
				.closest('.tab-header-item-seemore')
				.classList.toggle('is-active');
		} else {
			document.querySelectorAll('.tab-header-item-seemore').forEach((item) => {
				console.log(item.classList.contains('is-active'));
				if (item.classList.contains('is-active')) {
					item.classList.remove('is-active');
				}
			});
		}
	});
})();

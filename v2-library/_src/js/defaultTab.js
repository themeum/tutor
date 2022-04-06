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

		// Nav
		const attrNav = 'data-tutor-nav-target';
		const activeNavItems = document.querySelectorAll('.tutor-nav-item > a.is-active, .tutor-tab-item.is-active');

		if (e.target.hasAttribute(attrNav)) {
			e.preventDefault();
			const id = e.target.hasAttribute(attrNav)
				? e.target.getAttribute(attrNav)
				: e.target.closest(`[${attrNav}]`).getAttribute(attrNav);

			const navTabBodyItem = document.getElementById(id);

			if (e.target.hasAttribute(attrNav) && navTabBodyItem) {
				activeNavItems.forEach((m) => {
					m.classList.remove('is-active');
				});

				if(e.target.closest('.tutor-nav-more') != undefined) {
					e.target.closest('.tutor-nav-more').classList.add('is-active');
				}

				e.target.classList.add('is-active');
				navTabBodyItem.classList.add('is-active');
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
				if (item.classList.contains('is-active')) {
					item.classList.remove('is-active');
				}
			});
		}
	});
})();

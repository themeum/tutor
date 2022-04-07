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
		const activeNavItems = document.querySelectorAll('.tutor-nav-item > .is-active, .tutor-tab-item.is-active');

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

				var activeNavItem = e.target;
				activeNavItem.classList.add('is-active');

				if(activeNavItem.hasAttribute('data-tutor-query-variable') && activeNavItem.hasAttribute('data-tutor-query-value')) {
					var queryVariable = activeNavItem.getAttribute('data-tutor-query-variable');
					var queryValue = activeNavItem.getAttribute('data-tutor-query-value');

					if(queryVariable && queryValue) {
						let url = new URL(window.location);
						url.searchParams.set(queryVariable, queryValue);
						window.history.pushState({}, '', url);
					}
				}

				navTabBodyItem.classList.add('is-active');
			}
		}
	});
})();

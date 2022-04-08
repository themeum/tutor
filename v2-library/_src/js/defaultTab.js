(function tutorDefaultTab() {
	document.addEventListener('click', (e) => {
		/**
		 * Tutor Default Tab
		 */
		const attr = 'data-tutor-tab-target';
		const activeItems = document.querySelectorAll(
			'.tab-header-item.is-active, .tab-body-item.is-active',
		);
		let elementWithAttr = null;

		if (e.target.hasAttribute(attr)) {
			elementWithAttr = e.target;
		} else if (e.target.closest(`[${attr}]`)?.hasAttribute(attr)) {
			elementWithAttr = e.target.closest(`[${attr}]`);
		}

		const id = elementWithAttr ? elementWithAttr.getAttribute(attr) : null;

		if (id) {
			e.preventDefault();
			const tabBodyItem = document.getElementById(id);

			if (tabBodyItem) {
				activeItems.forEach((m) => {
					m.classList.remove('is-active');
				});
				elementWithAttr.classList.add('is-active');
				tabBodyItem.classList.add('is-active');
			}
		}

		// Nav
		const attrNav = 'data-tutor-nav-target';
		const navTarget = e.target.hasAttribute(attrNav) ? e.target : e.target.closest(`[${attrNav}]`);
		const activeNavItems = document.querySelectorAll('.tutor-nav-item.is-active, .tutor-tab-item.is-active');

		if (navTarget && navTarget.hasAttribute(attrNav)) {
			e.preventDefault();

			const id = navTarget.getAttribute(attrNav);

			const navTabBodyItem = document.getElementById(id);

			if (navTabBodyItem) {
				activeNavItems.forEach((m) => {
					m.classList.remove('is-active');
				});

				if (navTarget.closest('.tutor-nav-more') != undefined) {
					navTarget.closest('.tutor-nav-more').querySelector('.tutor-nav-more-item').classList.add('is-active');
				}

				navTarget.classList.add('is-active');

				if (
					navTarget.hasAttribute('data-tutor-query-variable') &&
					navTarget.hasAttribute('data-tutor-query-value')
				) {
					var queryVariable = navTarget.getAttribute(
						'data-tutor-query-variable',
					);
					var queryValue = navTarget.getAttribute('data-tutor-query-value');

					if (queryVariable && queryValue) {
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

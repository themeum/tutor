/**
 * Tutor Notification Tab
 */
(function tutorNotificationTab() {
	document.addEventListener('click', (e) => {
		const attr = 'data-tutor-notification-tab-target';
		const tabItems = document.querySelectorAll('.tab-header-item, .tab-body-item');

		const activeItems = document.querySelectorAll('.tab-header-item.is-active, .tab-body-item.is-active');

		// Opening Offcanvas
		if (e.target.hasAttribute(attr)) {
			e.preventDefault();
			const id = e.target.hasAttribute(attr)
				? e.target.getAttribute(attr)
				: e.target.closest(`[${attr}]`).getAttribute(attr);

			activeItems.forEach((m) => {
				m.classList.remove('is-active');
			});

			const tabBodyItem = document.getElementById(id);
			if (tabBodyItem) {
				activeItems.forEach((m) => {
					m.classList.remove('is-active');
				});
				tabBodyItem.classList.add('is-active');
			}

			console.log(tabHeaderItem);
			console.log('getA', e.target.getAttribute(attr), 'clo', e.target.closest(`[${attr}]`).getAttribute(attr));
		}
	});
})();

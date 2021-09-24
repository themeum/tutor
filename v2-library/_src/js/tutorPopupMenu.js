(function tutorPopupMenu() {
	/**
	 * Popup Menu Toggle .tutor-popup-opener
	 */

	/*
	const popupToggleBtns = document.querySelectorAll('.tutor-popup-opener .popup-btn');
	const popupMenus = document.querySelectorAll('.tutor-popup-opener .popup-menu');

	 if (popupToggleBtns && popupMenus) {
		popupToggleBtns.forEach((btn) => {
			btn.addEventListener('click', (e) => {
				const popupClosest = e.target.closest('.tutor-popup-opener').querySelector('.popup-menu');
				popupClosest.classList.toggle('visible');

				popupMenus.forEach((popupMenu) => {
					if (popupMenu !== popupClosest) {
						popupMenu.classList.remove('visible');
					}
				});
			});
		});

		document.addEventListener('click', (e) => {
			if (!e.target.matches('.tutor-popup-opener .popup-btn')) {
				popupMenus.forEach((popupMenu) => {
					if (popupMenu.classList.contains('visible')) {
						popupMenu.classList.remove('visible');
					}
				});
			}
		});
	} */

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
	});
})();

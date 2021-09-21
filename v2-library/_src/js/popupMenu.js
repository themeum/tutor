(function popupMenuToggle() {
	/**
	 * Popup Menu Toggle tutor-popup-opener
	 */
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

		window.addEventListener('click', (e) => {
			if (!e.target.matches('.tutor-popup-opener .popup-btn')) {
				popupMenus.forEach((popupMenu) => {
					if (popupMenu.classList.contains('visible')) {
						popupMenu.classList.remove('visible');
					}
				});
			}
		});
	}
})();

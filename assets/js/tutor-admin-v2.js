const navTabLists = document.querySelectorAll('ul.tutor-option-nav');
const navTabItems = document.querySelectorAll('li.tutor-option-nav-item a');
const navPages = document.querySelectorAll('.tutor-option-nav-page');

navTabLists.forEach((list) => {
	list.addEventListener('click', (e) => {
		const dataTab = e.target.parentElement.dataset.tab || e.target.dataset.tab;
		if (dataTab) {
			// remove active from other buttons
			navTabItems.forEach((item) => {
				item.classList.remove('active');
				if (e.target.dataset.tab) {
					e.target.classList.add('active');
				} else {
					e.target.parentElement.classList.add('active');
				}
			});
			// hide other tab contents
			navPages.forEach((content) => {
				content.classList.remove('active');
			});
			// add active to the current content
			const currentContent = document.querySelector(`#${dataTab}`);
			currentContent.classList.add('active');
		}
	});
});

readyState_complete(() => {
	toggleCertificate();

	const tabHeaderItem = document.querySelectorAll('.tab-header-item');
	tabHeaderItem.forEach((tabItem) => {
		tabItem.onclick = (e) => {
			setTimeout(() => {
				toggleCertificate();
			}, 100);
		};
	});
});

const toggleCertificate = () => {
	const { __ } = wp.i18n;
	const moreButton = document.querySelector('.more_button');
	moreButton.style.display = 'block';
	const templateAlignment = document.querySelectorAll('[data-alignment]');

	templateAlignment.forEach((templateType) => {
		let templateItem = templateType && templateType.querySelector('.template-item');
		let templateItemWrap = templateItem && templateItem.closest('.tab-body-item');
		let activeView = templateItemWrap.classList.contains('is-active');

		if (true == activeView && templateItemWrap) {
			let templateItemAll = templateType && templateType.querySelectorAll('.template-item');
			console.log(Math.ceil(templateItemAll.length / 3));

			let rowItem = 0;
			templateItemAll.forEach((templateItem) => {
				if (0 == templateItem.offsetTop) {
					rowItem += 1;
				}
			});
			let primaryHeight = 2 * templateItem.offsetHeight;
			let targetHeight = Math.ceil(templateItemAll.length / rowItem) * templateItem.offsetHeight;
			console.log(primaryHeight);
			moreButton.style.height = templateItem.offsetHeight - 36 + 'px';
			if (!templateItemWrap.classList.contains('more-loaded')) {
				templateItemWrap.style.height = primaryHeight + 'px';
			}
			moreButton.onclick = (e) => {
				moreButton.querySelector('span').innerText = __("Show More", "tutor");
				templateItemWrap.classList.toggle('more-loaded');
				let moreLoaded = templateItemWrap.classList.contains('more-loaded');
				console.log(moreLoaded);
				if (moreLoaded) {
					templateItemWrap.style.height = primaryHeight + targetHeight + 'px';
					moreButton.querySelector('i').classList.remove('tutor-icon-plus');
					moreButton.querySelector('i').classList.add('tutor-icon-minus');
					moreButton.querySelector('span').innerText = __("Show Less", "tutor");
				} else {
					templateItemWrap.style.height = primaryHeight + 'px';
					moreButton.querySelector('i').classList.remove('tutor-icon-minus');
					moreButton.querySelector('i').classList.add('tutor-icon-plus');
					moreButton.querySelector('span').innerText = __("Show More", "tutor");
				}
			};
		}
	});
};

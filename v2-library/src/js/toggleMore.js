(function tutorToggleMore() {
    const { __ } = wp.i18n;
	document.addEventListener('click', (e) => {
		const toggleAttr = 'data-tutor-toggle-more';
		const toggleButton = e.target.hasAttribute(toggleAttr) ? e.target : e.target.closest(`[${toggleAttr}]`);

		if (toggleButton && toggleButton.hasAttribute(toggleAttr)) {
			e.preventDefault();
            const toggleTarget = toggleButton.getAttribute(toggleAttr);
            console.log(toggleTarget);
            const toggleContent = document.querySelector(toggleTarget);

            if (toggleContent.classList.contains('tutor-toggle-more-collapsed')) {
                toggleContent.classList.remove('tutor-toggle-more-collapsed');
                toggleContent.style.height = 'auto';

                toggleButton.classList.remove('is-active');
                toggleButton.querySelector('.tutor-toggle-btn-icon').classList.replace('tutor-icon-plus', 'tutor-icon-minus');
                toggleButton.querySelector('.tutor-toggle-btn-text').innerText = __("Show Less", "tutor");
            } else {
                toggleContent.classList.add('tutor-toggle-more-collapsed');
                toggleContent.style.height = toggleContent.getAttribute('data-toggle-height') + 'px';

                toggleButton.classList.add('is-active');
                toggleButton.querySelector('.tutor-toggle-btn-icon').classList.replace('tutor-icon-minus', 'tutor-icon-plus');
                toggleButton.querySelector('.tutor-toggle-btn-text').innerText = __("Show More", "tutor");
            }
		}
	});
})();

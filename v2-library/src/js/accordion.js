/**
 * Tutor accrodion
 */
(window.tutorAccordion = () => {
	(function($) {
		const accordionItemHeaders = document.querySelectorAll(
			'.tutor-accordion-item-header',
		);
		if (accordionItemHeaders.length) {
			accordionItemHeaders.forEach((accordionItemHeader) => {
				accordionItemHeader.addEventListener('click', () => {
					accordionItemHeader.classList.toggle('is-active');
					const accordionItemBody = accordionItemHeader.nextElementSibling;
					if (accordionItemHeader.classList.contains('is-active')) {
						$(accordionItemBody).slideDown();
					} else {
						$(accordionItemBody).slideUp();
					}
				});
			});
		}
	})(jQuery);
})();

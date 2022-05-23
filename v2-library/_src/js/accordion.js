/**
 * Tutor accrodion
 */
(window.tutorAccordion = ($) => {

	const accordionItemHeaders = document.querySelectorAll('.tutor-accordion-item-header');
	if (accordionItemHeaders.length) {
		// // initialize
		accordionItemHeaders.forEach((accordionItemHeader) => { 
			const accordionItemBody = accordionItemHeader.nextElementSibling;
			if (accordionItemHeader.classList.contains('is-active')) {
				$(accordionItemBody).slideDown();
			}
		});

		// click to toggle
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

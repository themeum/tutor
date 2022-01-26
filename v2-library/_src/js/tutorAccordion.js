/**
 * Tutor accrodion
 */
window.tutorAccordion = (func) => {
	const accordionItemHeaders = document.querySelectorAll('.tutor-accordion-item-header');
	if (accordionItemHeaders.length) {
		accordionItemHeaders.forEach((accordionItemHeader) => {
			accordionItemHeader.addEventListener('click', () => {
				accordionItemHeader.classList.toggle('is-active');
				const accordionItemBody = accordionItemHeader.nextElementSibling;
				if (accordionItemHeader.classList.contains('is-active')) {
					accordionItemBody.style.maxHeight = accordionItemBody.scrollHeight + 'px';
				} else {
					accordionItemBody.style.maxHeight = 0;
				}
			});
		});
	}
};

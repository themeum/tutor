import './tutorModal';
import './tutorThumbnailPreview';
import './tutorPopupMenu';
import './tutorOffcanvas';
import './tutorNotificationTab';
import './tutorDefaultTab';
import './tutorPasswordStrengthChecker';

document.addEventListener('click', function(e) {
	/**
	 * Table td/tr toggle
	 */
	const dataTdTarget = e.target.dataset.tdTarget;
	if (dataTdTarget) {
		e.target.closest('td').classList.toggle('is-active');
		document.getElementById(dataTdTarget).classList.toggle('is-active');
	}
	/**
	 * Course details showmore toggle
	 */
	const dataShomore = e.target.dataset.showmore;
	if (dataShomore) {
		e.target.closest('.tutor-has-showmore').classList.toggle('is-active');
	}
});

/**
 * Tutor accrodion
 */
const accordionItemHeaders = document.querySelectorAll('.tutor-accordion-item-header');
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

const hasExpandableCardInputs = document.querySelectorAll(
	'.tutor-course-sidebar-card-pick-plan.has-input-expandable .tutor-form-check-input'
);

hasExpandableCardInputs.forEach((el) => {
	console.log('input checked ->> ', el.checked);
	console.dir(el.closest('.tutor-course-sidebar-card-pick-plan-label').querySelector('.input-plan-details'));

	el.addEventListener('change', (e) => {
		el.classList.toggle('is-checked');
		const inputDetails = e.target
			.closest('.tutor-course-sidebar-card-pick-plan-label')
			.querySelector('.input-plan-details');

		console.log('input checked -> ', e.target.checked);

		if (e.target.checked) {
			inputDetails.style.maxHeight = inputDetails.scrollHeight + 'px';
		} else {
			inputDetails.style.maxHeight = 0;
		}
	});
});

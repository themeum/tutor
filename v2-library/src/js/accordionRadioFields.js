/**
 * Toggle Expandable -> .input-plan-details
 */

const hasExpandableCardInputs = document.querySelectorAll(
	'.tutor-course-sidebar-card-pick-plan.has-input-expandable .tutor-form-check-input'
);

if (hasExpandableCardInputs) {
	hasExpandableCardInputs.forEach((el) => {
		const detailItems = document.querySelectorAll('.tutor-course-sidebar-card-pick-plan-label .input-plan-details');

		if (el.checked) {
			el.parentElement.querySelector('.input-plan-details').style.maxHeight = 'max-content';
		}

		el.addEventListener('change', (e) => {
			const inputDetails = e.target
				.closest('.tutor-course-sidebar-card-pick-plan-label')
				.querySelector('.input-plan-details');

			detailItems.forEach((item) => {
				item.style.maxHeight = 0;
			});

			if (e.target.checked) {
				inputDetails.style.maxHeight = inputDetails.scrollHeight + 'px';
			}
		});
	});
}

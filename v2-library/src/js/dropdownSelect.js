const tutorDropdownSelect = document.querySelector('.tutor-dropdown-select');
if (tutorDropdownSelect) {
	const selected = document.querySelector('.tutor-dropdown-select-selected');
	const optionsContainer = document.querySelector('.tutor-dropdown-select-options-container');
	const optionsList = document.querySelectorAll('.tutor-dropdown-select-option');

	selected.addEventListener('click', (e) => {
		e.stopPropagation();
		optionsContainer.classList.toggle('is-active');
	});

	optionsList.forEach((option) => {
		option.addEventListener('click', (e) => {
			const key = e.target.dataset.key;
			if (key === 'custom') {
				document.querySelector('.tutor-v2-date-range-picker.inactive').classList.add('active');
				document.querySelector('.tutor-v2-date-range-picker.inactive input').click();
				document.querySelector('.tutor-v2-date-range-picker.inactive input').style.display = 'none';
				document.querySelector('.tutor-v2-date-range-picker.inactive .react-datepicker-popper').style.marginTop =
					'-40px';
			}
			selected.innerHTML = option.querySelector('label').innerHTML;
			optionsContainer.classList.remove('is-active');
		});
	});
}

// console.log(tutorDropdownSelect);

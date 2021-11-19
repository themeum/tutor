const tutorDropdownSelect = document.querySelector('.tutor-dropdown-select');
if (tutorDropdownSelect) {
	const selected = document.querySelector('.tutor-dropdown-select-selected');
	const optionsContainer = document.querySelector('.tutor-dropdown-select-options-container');
	const optionsList = document.querySelectorAll('.tutor-dropdown-select-option');

	selected.addEventListener('click', () => {
		optionsContainer.classList.toggle('is-active');
	});

	optionsList.forEach((option) => {
		option.addEventListener('click', () => {
			selected.innerHTML = option.querySelector('label').innerHTML;
			optionsContainer.classList.remove('is-active');
		});
	});
}

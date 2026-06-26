// Tutor setup page
document.addEventListener('DOMContentLoaded', () => {
	const setupWrapper = document.querySelector('#tutor-setup-wrapper');

	if (!setupWrapper) {
		return;
	}

	const activateScreen = (screenName) => {
		setupWrapper.querySelectorAll('.tutor-setup-screen').forEach((screen) => {
			screen.classList.remove('is-active');
		});

		const targetScreen = setupWrapper.querySelector(`.tutor-setup-screen[data-screen="${screenName}"]`);

		if (targetScreen) {
			targetScreen.classList.add('is-active');
		}
	};

	const syncSelectedCards = () => {
		setupWrapper.querySelectorAll('.tutor-setup-choice-wrapper').forEach((choiceWrapper) => {
			choiceWrapper.querySelectorAll('.tutor-setup-choice-card').forEach((card) => {
				card.classList.remove('is-selected');
			});

			choiceWrapper.querySelectorAll('.tutor-setup-choice-input:checked').forEach((input) => {
				const card = input.closest('.tutor-setup-choice-card');

				if (card) {
					card.classList.add('is-selected');
				}
			});
		});
	};

	setupWrapper.addEventListener('click', (event) => {
		const nextButton = event.target.closest('.tutor-setup-next-screen');

		if (!nextButton) {
			return;
		}

		const { target } = nextButton.dataset;

		if (target) {
			activateScreen(target);
		}
	});

	setupWrapper.addEventListener('change', (event) => {
		if (event.target.matches('.tutor-setup-choice-input')) {
			syncSelectedCards();
		}
	});

	syncSelectedCards();
});

// Tutor setup page
document.addEventListener('DOMContentLoaded', () => {
	const onboardWrapper = document.querySelector('#tutor-onboard-wrapper');

	if (!onboardWrapper) {
		return;
	}

	const activateScreen = (screenName) => {
		onboardWrapper.querySelectorAll('.tutor-onboard-screen').forEach((screen) => {
			screen.classList.remove('is-active');
		});

		const targetScreen = onboardWrapper.querySelector(`.tutor-onboard-screen[data-screen="${screenName}"]`);

		if (targetScreen) {
			targetScreen.classList.add('is-active');
		}
	};

	const syncSelectedCards = () => {
		onboardWrapper.querySelectorAll('.tutor-onboard-choice-wrapper').forEach((choiceWrapper) => {
			choiceWrapper.querySelectorAll('.tutor-onboard-choice-card').forEach((card) => {
				card.classList.remove('is-selected');
			});

			choiceWrapper.querySelectorAll('.tutor-onboard-choice-input:checked').forEach((input) => {
				const card = input.closest('.tutor-onboard-choice-card');

				if (card) {
					card.classList.add('is-selected');
				}
			});
		});
	};

	const loadingTextElement = onboardWrapper.querySelector('.tutor-onboard-loading-text');
	const loadingText = loadingTextElement?.dataset.text || loadingTextElement?.textContent?.trim() || '';
	let loadingTextTimer = null;

	const stopLoadingTextAnimation = () => {
		if (loadingTextTimer) {
			window.clearTimeout(loadingTextTimer);
			loadingTextTimer = null;
		}

		if (loadingTextElement) {
			loadingTextElement.textContent = loadingText;
		}
	};

	const startLoadingTextAnimation = () => {
		if (!loadingTextElement || !loadingText) {
			return;
		}

		stopLoadingTextAnimation();
		let visibleLength = 0;
		loadingTextElement.textContent = '';

		const animate = () => {
			if (!loadingTextElement) {
				return;
			}

			if (visibleLength < loadingText.length) {
				visibleLength += 1;
				loadingTextElement.textContent = loadingText.slice(0, visibleLength);
				loadingTextTimer = window.setTimeout(animate, 35);
			}
		};

		animate();
	};

	onboardWrapper.addEventListener('click', (event) => {
		const nextButton = event.target.closest('.tutor-onboard-next-screen');

		if (!nextButton) {
			return;
		}

		const { target } = nextButton.dataset;

		if (target) {
			activateScreen(target);
		}
	});

	onboardWrapper.addEventListener('change', (event) => {
		if (event.target.matches('.tutor-onboard-choice-input')) {
			syncSelectedCards();
		}
	});

	syncSelectedCards();

	// Onboarding setup form submit
	const onboardForm = onboardWrapper.querySelector('.tutor-onboard-setup-form');

	if (!onboardForm) {
		return;
	}

	onboardForm.addEventListener('submit', async (event) => {
		event.preventDefault();

		const formData = new FormData(onboardForm);
		formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
		const submitButton = onboardForm.querySelector('.tutor-onboard-submit-btn');
		const loadingScreen = submitButton?.dataset.screen || 'loading';

		if (submitButton) {
			submitButton.disabled = true;
		}

		activateScreen(loadingScreen);
		startLoadingTextAnimation();

		try {
			if (formData.get('tutor_onboard_load_sample_course')) {
				const importSuccess = await importSampleCourses();
				if (!importSuccess) {
					throw new Error('Sample course import failed.');
				}
			}

			const response = await fetch(_tutorobject.ajaxurl, {
				method: 'POST',
				body: formData,
			});

			if (!response.ok) {
				throw new Error('Onboarding setup request failed.');
			}

			const result = await response.json();
			if (result.status_code == 200) {
				window.location.href = _tutorobject.tutor_dashboard;
			}
		} catch (error) {
			console.log('Tutor on setup request failed:', error);
			activateScreen('preferences');
		} finally {
			stopLoadingTextAnimation();
			if (submitButton) {
				submitButton.disabled = false;
			}
		}
	});

	const importSampleCourses = async (jobId = 0) => {
		// import sample courses
		const formData = new FormData();
		formData.append('job_id', jobId);
		formData.append('action', 'tutor_pro_import');
		formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
		const courseDataUrl = 'https://tutor-lms.s3.us-east-1.amazonaws.com/courses/workademy/data.json';

		if (!jobId) {
			const data = await fetch(courseDataUrl);
			const json = await data.json();
			const limitedJson = {
				...json,
				data: Array.isArray(json.data)
					? json.data.map((section) => {
						if (section?.content_type === 'courses' && Array.isArray(section.data)) {
							return {
								...section,
								data: section.data.slice(0, 4),
							};
						}

						return section;
					})
					: [],
			};
			const blob = new Blob([JSON.stringify(limitedJson)], {
				type: 'application/json',
			});
			formData.append('data', blob, 'importer.json');
		}

		const post = await fetch(_tutorobject.ajaxurl, {
			method: 'POST',
			body: formData,
			credentials: 'same-origin',
		});

		if (post.ok) {
			const response = await post.json();
			if (response.status_code == 200) {
				const jobId = response.data.job_id;
				const jobProgress = response.data.job_progress;
				if (jobProgress != 100) {
					// showConsentProgress(Math.min(80 + jobProgress * 0.2, 99), __('Importing demo courses...', 'tutor-pro'));
					console.log('Job Progress', jobProgress);
					return await importSampleCourses(jobId);
				}
				if (jobProgress == 100) {
					// showConsentProgress(100, __('Demo courses imported.', 'tutor-pro'));
					console.log('100 Job Progress', jobProgress);
					return true;
				}
			}
		} else {
			return false;
		}
	};

});

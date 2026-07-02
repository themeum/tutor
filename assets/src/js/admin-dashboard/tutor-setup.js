// Tutor onboarding
document.addEventListener('DOMContentLoaded', () => {
	const { __ } = wp.i18n;
	const onboardWrapper = document.querySelector('#tutor-onboard-wrapper');

	if (!onboardWrapper) {
		return;
	}

	const activateScreen = (screenName) => {
		onboardWrapper.querySelectorAll('.tutor-onboard-screen').forEach((screen) => {
			screen.classList.remove('is-active');
			screen.classList.remove('is-fading-out');
		});

		const targetScreen = onboardWrapper.querySelector(`.tutor-onboard-screen[data-screen="${screenName}"]`);

		if (targetScreen) {
			targetScreen.classList.add('is-active');
		}
	};

	const fadeOutLoadingScreen = () => {
		const loadingSection = onboardWrapper.querySelector('.tutor-onboard-screen-loading');

		if (!loadingSection?.classList.contains('is-active')) {
			return Promise.resolve();
		}

		loadingSection.classList.add('is-fading-out');

		return new Promise((resolve) => {
			setTimeout(resolve, 300);
		});
	};

	const wait = (time) => new Promise((resolve) => {
		setTimeout(resolve, time);
	});

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
	let isLoadingTextLooping = false;

	const stopLoadingTextAnimation = () => {
		isLoadingTextLooping = false;

		if (loadingTextTimer) {
			clearTimeout(loadingTextTimer);
			loadingTextTimer = null;
		}

		if (loadingTextElement) {
			loadingTextElement.textContent = loadingText;
		}
	};

	const startLoadingTextDotsAnimation = () => {
		if (!loadingTextElement || !loadingText) {
			return;
		}

		let dotsCount = 0;
		isLoadingTextLooping = true;

		const animateDots = () => {
			if (!loadingTextElement || !isLoadingTextLooping) {
				return;
			}

			loadingTextElement.textContent = `${loadingText}${'.'.repeat(dotsCount)}`;
			dotsCount = (dotsCount + 1) % 4;
			loadingTextTimer = setTimeout(animateDots, 350);
		};

		animateDots();
	};

	const startLoadingTextAnimation = () => {
		if (!loadingTextElement || !loadingText) {
			return Promise.resolve();
		}

		stopLoadingTextAnimation();
		let visibleLength = 0;
		loadingTextElement.textContent = '';

		return new Promise((resolve) => {
			const animate = () => {
				if (!loadingTextElement) {
					resolve();
					return;
				}

				if (visibleLength < loadingText.length) {
					visibleLength += 1;
					loadingTextElement.textContent = loadingText.slice(0, visibleLength);
					loadingTextTimer = setTimeout(animate, 35);
					return;
				}

				loadingTextTimer = null;
				startLoadingTextDotsAnimation();
				resolve();
			};

			animate();
		});
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
		const loadingScreen = submitButton?.dataset.screen || __('Loading...', 'tutor');

		if (submitButton) {
			submitButton.disabled = true;
		}

		activateScreen(loadingScreen);
		const loadingTextAnimation = startLoadingTextAnimation();

		try {
			if (formData.get('tutor_onboard_load_sample_course')) {
				const importSuccess = await importSampleCourses();
				if (!importSuccess) {
					tutor_toast(__('Sample course import failed.', 'tutor'), '', 'error');
				}
			}

			const response = await fetch(_tutorobject.ajaxurl, {
				method: 'POST',
				body: formData,
			});

			if (!response.ok) {
				throw new Error(__('Onboarding setup request failed.', 'tutor'));
			}
		} catch (error) {
			await loadingTextAnimation;
			tutor_toast(error?.message || __('Something went wrong!', 'tutor'), '', 'error');
			await wait(1000);
		} finally {
			if (submitButton) {
				submitButton.disabled = false;
			}
			await loadingTextAnimation;
			await fadeOutLoadingScreen();
			location.href = _tutorOnboardObject.tutor_welcome_page;
		}
	});

	const importSampleCourses = async (jobId = 0) => {
		// import sample courses
		const formData = new FormData();
		formData.append('job_id', jobId);
		formData.append('action', 'tutor_pro_import');
		formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
		const courseDataUrl = _tutorOnboardObject.course_data_url;

		if (!jobId) {
			const data = await fetch(courseDataUrl);
			const courseJson = await data.json();
			const limitedCourseJson = {
				...courseJson,
				data: Array.isArray(courseJson.data)
					? courseJson.data.map((section) => {
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
			const blob = new Blob([JSON.stringify(limitedCourseJson)], {
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
					return await importSampleCourses(jobId);
				}
				if (jobProgress == 100) {
					return true;
				}
			}
		} else {
			return false;
		}
	};

});

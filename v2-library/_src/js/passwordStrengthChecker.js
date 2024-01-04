/**
 * Tutor Password Strength Checker
 */

(function tutorPasswordStrengthChecker() {
	const passwordCheckerInput = document.querySelectorAll('.tutor-password-field input.password-checker');
	const weak = document.querySelector('.tutor-password-strength-hint .weak');
	const medium = document.querySelector('.tutor-password-strength-hint .medium');
	const strong = document.querySelector('.tutor-password-strength-hint .strong');

	let regExpWeak = /[a-z]/;
	let regExpMedium = /\d+/;
	let regExpStrong = /.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/;

	if (passwordCheckerInput) {
		passwordCheckerInput.forEach((checkerInput) => {
			checkerInput.addEventListener('input', (e) => {
				let indicator, noticeText, no;

				const showBtn = checkerInput && checkerInput.closest('.tutor-password-field').querySelector('.show-hide-btn');
				let hintWrapper = checkerInput.closest('.tutor-password-strength-checker');
				if (hintWrapper) {
					indicator = hintWrapper && hintWrapper.querySelector('.indicator');
					noticeText = hintWrapper && hintWrapper.querySelector('.text');
				}

				const input = e.target;
				if (input.value != '') {

					if (indicator) {
						indicator.style.display = 'flex';
					}
					if (
						input.value.length <= 3 &&
						(input.value.match(regExpWeak) || input.value.match(regExpMedium) || input.value.match(regExpStrong))
					)
						no = 1;
					if (
						input.value.length >= 6 &&
						((input.value.match(regExpWeak) && input.value.match(regExpMedium)) ||
							(input.value.match(regExpMedium) && input.value.match(regExpStrong)) ||
							(input.value.match(regExpWeak) && input.value.match(regExpStrong)))
					)
						no = 2;
					if (
						input.value.length >= 6 &&
						input.value.match(regExpWeak) &&
						input.value.match(regExpMedium) &&
						input.value.match(regExpStrong)
					)
						no = 3;
					if (no == 1) {
						weak.classList.add('active');
						if (noticeText) {
							noticeText.style.display = 'block';
							noticeText.textContent = 'weak';
							// noticeText.classList.add('weak');
						}
					}
					if (no == 2) {
						medium.classList.add('active');
						if (noticeText) {
							noticeText.textContent = 'medium';
							// noticeText.classList.add('medium');
						}
					} else {
						medium.classList.remove('active');
						if (noticeText) {
							// noticeText.classList.remove('medium');
						}
					}
					if (no == 3) {
						weak.classList.add('active');
						medium.classList.add('active');
						strong.classList.add('active');
						if (noticeText) {
							noticeText.textContent = 'strong';
							// noticeText.classList.add('strong');
						}
					} else {
						strong.classList.remove('active');
						if (noticeText) {
							// noticeText.classList.remove('strong');
						}
					}

					if (showBtn) {
						showBtn.style.display = 'block';

						showBtn.onclick = function () {
							if (input.type == 'password') {
								input.type = 'text';
								showBtn.style.color = '#23ad5c';
								showBtn.classList.add('hide-btn');
							} else {
								input.type = 'password';
								showBtn.style.color = '#000';
								showBtn.classList.remove('hide-btn');
							}
						};
					}
				} else {
					if (indicator) {
						indicator.style.display = 'none';
					}
					if (noticeText) {
						indicator.style.display = 'none';
					}
					if (noticeText) {
						noticeText.style.display = 'none';
					}
					showBtn.style.display = 'none';
				}
			});
		});
	}
})();

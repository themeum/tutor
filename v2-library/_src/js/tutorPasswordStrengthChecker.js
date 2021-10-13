/**
 * Tutor Password Strength Checker
 */

(function tutorPasswordStrengthChecker() {
	const passwordCheckerInput = document.querySelector('.tutor-password-field input.password-checker');
	const indicator = document.querySelector('.tutor-passowrd-strength-hint .indicator');
	const weak = document.querySelector('.tutor-passowrd-strength-hint .weak');
	const medium = document.querySelector('.tutor-passowrd-strength-hint .medium');
	const strong = document.querySelector('.tutor-passowrd-strength-hint .strong');
	const text = document.querySelector('.tutor-passowrd-strength-hint .text');
	const showBtn = document.querySelector('.tutor-password-field .show-hide-btn');
	let regExpWeak = /[a-z]/;
	let regExpMedium = /\d+/;
	let regExpStrong = /.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/;

	if (passwordCheckerInput) {
		passwordCheckerInput.addEventListener('input', (e) => {
			const input = e.target;
			if (input.value != '') {
				indicator.style.display = 'flex';
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
					text.style.display = 'block';
					text.textContent = 'week';
					text.classList.add('weak');
				}
				if (no == 2) {
					medium.classList.add('active');
					text.textContent = 'medium';
					text.classList.add('medium');
				} else {
					medium.classList.remove('active');
					text.classList.remove('medium');
				}
				if (no == 3) {
					weak.classList.add('active');
					medium.classList.add('active');
					strong.classList.add('active');
					text.textContent = 'strong';
					text.classList.add('strong');
				} else {
					strong.classList.remove('active');
					text.classList.remove('strong');
				}

				showBtn.style.display = 'block';

				showBtn.onclick = function() {
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
			} else {
				indicator.style.display = 'none';
				text.style.display = 'none';
				showBtn.style.display = 'none';
			}
		});
	}
})();

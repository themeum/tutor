/*
Reset to default for settings individual page
*/


document.addEventListener('readystatechange', (event) => {
	if (event.target.readyState === 'interactive') {
	}
	if (event.target.readyState === 'complete') {
		typeof resetConfirmation === 'function' ? resetConfirmation() : '';
		typeof modalResetOpen === 'function' ? modalResetOpen() : '';
	}
});
const modalConfirmation = document.getElementById('tutor-modal-bulk-action');

const modalResetOpen = () => {
	const modalResetOpen = document.querySelectorAll('.modal-reset-open');
	let resetButton = modalConfirmation && modalConfirmation.querySelector('.reset_to_default');
	let modalHeading = modalConfirmation && modalConfirmation.querySelector('.tutor-modal-title');
	let modalMessage = modalConfirmation && modalConfirmation.querySelector('.tutor-modal-message');
	modalResetOpen.forEach((modalOpen, index) => {
		modalOpen.onclick = (e) => {
			resetButton.dataset.reset = modalOpen.dataset.reset;
			modalHeading.innerText = modalOpen.dataset.heading;
			resetButton.dataset.resetFor = modalOpen.previousElementSibling.innerText;
			modalMessage.innerText = modalOpen.dataset.message;
		}
	})
}


const resetConfirmation = () => {
	const resetDefaultBtn = document.querySelectorAll('.reset_to_default');
	resetDefaultBtn.forEach((resetBtn, index) => {
		resetBtn.onclick = (e) => {
			e.preventDefault();
			var resetPage = resetBtn.dataset.reset;
			let resetTitle = resetBtn.dataset.resetFor.replace('_', ' ').toUpperCase();
			console.log(resetTitle);
			var formData = new FormData();
			formData.append('action', 'reset_settings_data');
			formData.append('reset_page', resetPage);
			formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
			const xhttp = new XMLHttpRequest();
			xhttp.open('POST', _tutorobject.ajaxurl, true);
			xhttp.send(formData);
			xhttp.onreadystatechange = function () {
				if (xhttp.readyState === 4) {
					modalConfirmation.classList.remove('tutor-is-active');
					let pageData = JSON.parse(xhttp.response).data;
					pageData.forEach((item) => {
						const field_types_associate = ['toggle_switch', 'text', 'textarea', 'email', 'hidden', 'select', 'number'];
						if (field_types_associate.includes(item.type)) {
							let itemName = 'tutor_option[' + item.key + ']';
							let itemElement = elementByName(itemName)[0];
							if (item.type == 'select') {
								let sOptions = itemElement.options;
								[...sOptions].forEach((item) => {
									item.selected = false;
								});
							} else if (item.type == 'toggle_switch') {
								itemElement.value = item.default;
								itemElement.nextElementSibling.value = item.default;
								itemElement.nextElementSibling.checked = false;
							} else {
								itemElement.value = item.default;
							}
						}

						const field_types_multi = ['group_fields'];
						if (field_types_multi.includes(item.type)) {
							let groupFields = item.group_fields;
							console.log(typeof groupFields === 'object' && groupFields !== null);
							if (typeof groupFields === 'object' && groupFields !== null) {
								Object.keys(groupFields).forEach((item) => {
									const field_types_associate = ['toggle_switch', 'text', 'textarea', 'email', 'hidden', 'select', 'number'];
									if (field_types_associate.includes(item.type)) {
										let itemName = 'tutor_option[' + item.key + ']';
										let itemElement = elementByName(itemName)[0];
										if (item.type == 'select') {
											let sOptions = itemElement.options;
											[...sOptions].forEach((item) => {
												item.selected = false;
											});
										} else if (item.type == 'toggle_switch') {
											itemElement.value = item.default;
											itemElement.nextElementSibling.value = item.default;
											itemElement.nextElementSibling.checked = false;
										} else {
											itemElement.value = item.default;
										}
									}
								})
							}
						}
					});
					setTimeout(() => {
						tutor_toast('Reset Successful', 'Default data for ' + resetTitle + ' successfully!', 'success');
					}, 300)
				}
			};
		};
	});
}


const elementByName = (key) => {
	return document.getElementsByName(key);
};

const optionForm = document.querySelector('#tutor-option-form');
if (null !== optionForm) {
	optionForm.addEventListener('input', (event) => {
		document.getElementById('save_tutor_option').disabled = false;
	});
}
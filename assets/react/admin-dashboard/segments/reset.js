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

const titleReseter = document.querySelectorAll('.tutor-option-single-item');
titleReseter.forEach((item) => {
	item.querySelector('h4').onclick = (e) => {
		item.parentElement.querySelector('.modal-reset-open').click()
	}
})

const resetConfirmation = () => {
	const resetDefaultBtn = document.querySelectorAll('.reset_to_default');
	resetDefaultBtn.forEach((resetBtn, index) => {
		resetBtn.onclick = (e) => {
			e.preventDefault();
			var resetPage = resetBtn.dataset.reset;
			let resetTitle = resetBtn.dataset.resetFor.replace('_', ' ').toUpperCase();
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
						const field_types_associate = ['radio_horizontal_full', 'checkbox_vertical', 'toggle_switch', 'text', 'textarea', 'email', 'hidden', 'select', 'number'];
						if (field_types_associate.includes(item.type)) {
							let itemName = 'tutor_option[' + item.key + ']';
							let itemElement = elementByName(itemName)[0];
							if (item.type == 'select') {
								let sOptions = itemElement.options;
								[...sOptions].forEach((optElem) => {
									optElem.selected = false;
								});
							} else if (item.type == 'checkbox_vertical') {
								let checkElements = elementByName(`${itemName}`);
								[...checkElements].forEach((checkElem) => {
									checkElem.checked = item.default.includes(checkElem.value) ? true : false;
								});
							} else if (item.type == 'radio_horizontal_full') {
								let checkElements = elementByName(`${itemName}`);
								console.log(checkElements);
								[...checkElements].forEach((checkElem) => {
									checkElem.checked = item.default.includes(checkElem.value) ? true : false;
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
							let parentKey = item.key;
							let groupFields = item.group_fields;

							if (typeof groupFields === 'object' && groupFields !== null) {
								Object.keys(groupFields).forEach((elemKey, index) => {
									let itemChild = groupFields[elemKey];
									const field_types_associate = ['toggle_switch', 'text', 'textarea', 'email', 'hidden', 'select', 'number'];

									if (field_types_associate.includes(itemChild.type)) {
										let itemName = `tutor_option[${parentKey}][${elemKey}]`;
										// console.log(itemName);
										let itemElementChild = elementByName(itemName)[0];
										if (itemChild.type == 'select') {
											let sOptions = itemElementChild.options;
											[...sOptions].forEach((optElem) => {
												optElem.selected = itemChild.default === optElem.value ? true : false;
											});
										} else if (itemChild.type == 'toggle_switch') {
											itemElementChild.value = itemChild.default;
											itemElementChild.nextElementSibling.value = itemChild.default;
											itemElementChild.nextElementSibling.checked = false;
										} else {
											// console.log(itemChild);
											itemElementChild.value = itemChild.default;
										}
									}
								})
							}
						}
					});
					setTimeout(() => {
						tutor_toast('Reset Successful', 'All modified settings of ' + resetTitle + ' have been changed to default.', 'success');
						// tutor_toast('Reset Successful', 'Default data for ' + resetTitle + ' successfully!', 'success');
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
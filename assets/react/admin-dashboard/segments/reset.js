/**
 * Reset to default for settings individual page
 */
readyState_complete(() => {
	typeof resetConfirmation === 'function' ? resetConfirmation() : '';
	typeof modalResetOpen === 'function' ? modalResetOpen() : '';
});
const modalConfirmation = document.getElementById('tutor-modal-bulk-action');

const modalResetOpen = () => {
	const modalResetOpen = document.querySelectorAll('.modal-reset-open');
	let resetButton = modalConfirmation && modalConfirmation.querySelector('.reset_to_default');
	let modalHeading = modalConfirmation && modalConfirmation.querySelector('[data-modal-dynamic-title]');
	let modalMessage = modalConfirmation && modalConfirmation.querySelector('[data-modal-dynamic-content]');

	modalResetOpen.forEach((modalOpen, index) => {
		modalOpen.disabled = false;
		modalOpen.onclick = (e) => {
			resetButton.dataset.reset = modalOpen.dataset.reset;
			modalHeading.innerText = modalOpen.dataset.heading;
			resetButton.dataset.resetFor = modalOpen.previousElementSibling.innerText;
			modalMessage.innerText = modalOpen.dataset.message;
		}
	});
}

const resetConfirmation = () => {
	const { __, sprintf } = wp.i18n;
	const resetDefaultBtn = document.querySelectorAll('.reset_to_default');
	resetDefaultBtn.forEach((resetBtn, index) => {
		resetBtn.onclick = (event) => {
			if (!event.detail || event.detail == 1) {
				event.preventDefault();
				resetBtn.classList.add('is-loading');
				const resetPage = resetBtn.dataset.reset;
				const resetTitle = resetBtn.dataset.resetFor.replace('_', ' ').toUpperCase();

				const formData = new FormData();
				formData.append('action', 'reset_settings_data');
				formData.append('reset_page', resetPage);
				formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);

				const xhttp = new XMLHttpRequest();
				xhttp.open('POST', _tutorobject.ajaxurl, true);
				xhttp.send(formData);
				xhttp.onreadystatechange = function () {
					if (xhttp.readyState === 4) {
						let pageData = JSON.parse(xhttp.response).data;
						pageData.forEach((item) => {
							const field_types_associate = ['color_preset', 'upload_full', 'checkbox_notification', 'checkgroup', 'group_radio_full_3', 'group_radio', 'radio_vertical', 'checkbox_horizontal', 'radio_horizontal', 'radio_horizontal_full', 'checkbox_vertical', 'toggle_switch', 'toggle_switch_button', 'text', 'textarea', 'email', 'hidden', 'select', 'number'];

							if (field_types_associate.includes(item.type)) {
								let itemName = 'tutor_option[' + item.key + ']';
								let elementItem = elementByName(itemName)[0];

								if (item.type == 'select') {

									let elementOptions = elementItem.options;
									[...elementOptions].forEach((elementOption) => {
										elementOption.selected = typeof item.default === 'number' ? elementOption.value === item.default : item.default.includes(elementOption.value);
									});

								} else if (item.type == 'color_preset') {

									let presetItems = elementByName(itemName);
									presetItems.forEach((presetItem) => {
										let labelClasses = presetItem.parentElement.classList;
										item.default.includes(presetItem.value) ? labelClasses.add('is-checked') : labelClasses.remove('is-checked');
										presetItem.checked = item.default.includes(presetItem.value) ? true : false;
									})

									item.fields.forEach((fields) => {
										if (fields.key === item.default) {
											fields.colors.forEach((picker) => {
												let pickerName = 'tutor_option[' + picker.slug + ']';
												let pickerItem = elementByName(pickerName)[0];
												let pickerItemParent = pickerItem.parentElement;
												pickerItem.value = picker.value;
												pickerItem.nextElementSibling.innerText = picker.value;

												pickerItemParent.style.borderColor = picker.value;
												pickerItemParent.style.boxShadow = `inset 0 0 0 1px ${picker.value}`;

												setTimeout(() => {
													pickerItemParent.style.borderColor = '#cdcfd5';
													pickerItemParent.style.boxShadow = 'none';
												}, 5000);
											})
										}
									})

								} else if (item.type == 'checkbox_horizontal' || item.type == 'checkbox_vertical' || item.type == 'radio_horizontal' || item.type == 'radio_horizontal_full' || item.type == 'radio_vertical' || item.type == 'group_radio' || item.type == 'group_radio_full_3') {

									if (item.type == 'checkbox_horizontal') {
										Object.keys(item.options).forEach((optionKeys) => {
											itemName = 'tutor_option[' + item.key + '][' + optionKeys + ']';
											checkElements = elementByName(`${itemName}`);
											[...checkElements].forEach((elemCheck) => {
												elemCheck.checked = item.default.includes(elemCheck.value) ? true : false;
											});
										});
									} else {
										let checkElements = elementByName(`${itemName}`);
										[...checkElements].forEach((elemCheck) => {
											elemCheck.checked = item.default.includes(elemCheck.value) ? true : false;
										});
									}

								} else if (item.type == 'upload_full') {
									elementItem.value = '';
									elementItem.nextElementSibling.src = '';
									elementItem.parentNode.querySelector('.delete-btn').style.display = 'none';
								} else if (item.type == 'checkbox_notification') {
									Object.keys(item.options).forEach((optionKeys) => {
										itemName = 'tutor_option' + optionKeys;
										checkElements = elementByName(`${itemName}`);
										[...checkElements].forEach((elemCheck) => {
											elemCheck.checked = false;
										});
									});
								} else if (item.type == 'checkgroup') {

									Object.values(item.group_options).forEach((optionKeys) => {
										itemName = 'tutor_option[' + optionKeys.key + ']';
										checkElements = elementByName(`${itemName}`);
										[...checkElements].forEach((elemCheck) => {
											elemCheck.value = 'on' === optionKeys.default ? 'on' : 'off';
											elemCheck.nextElementSibling.checked = 'on' === optionKeys.default ? true : false;
										});
									});

								} else if (item.type == 'toggle_switch_button') {

									itemName = 'tutor_option[' + item.key + '][' + item.event + ']';
									checkElements = elementByName(`${itemName}`);
									[...checkElements].forEach((elemCheck) => {
										elemCheck.nextElementSibling.checked = 'on' === item.default ? true : false;
									});

								} else if (item.type == 'toggle_switch') {

									elementItem.value = elementItem.nextElementSibling.value = item.default;
									elementItem.nextElementSibling.checked = false;

								} else {
									elementItem.value = item.default;
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
							resetBtn.classList.remove('is-loading');
							tutor_toast(
								__('Reset Successful', 'tutor'),
								// translators: %s: Reset settings title
								sprintf(__('All modified settings of %s have been changed to default.', 'tutor'), resetTitle),
								'success',
							);
							modalConfirmation.classList.remove('tutor-is-active');
							document.body.classList.remove('tutor-modal-open');
							if (document.getElementById('save_tutor_option')) {
								document.getElementById('save_tutor_option').disabled = false;
							}
						}, 300);
					}
				}
			}
		}
	});
}

const elementByName = (key) => {
	return document.getElementsByName(key);
};

/**
 * Enable save button if any input changes
 */
const optionForm = document.querySelector('#tutor-option-form');
if (null !== optionForm) {
	optionForm.addEventListener('input', (event) => {
		if (document.getElementById('save_tutor_option')) {
			document.getElementById('save_tutor_option').disabled = false;
		}
	});
}
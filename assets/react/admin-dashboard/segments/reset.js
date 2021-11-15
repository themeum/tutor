/*
Reset to default for settings individual page
*/
console.log('reset-to-default');
const resetDefaultBtn = document.querySelectorAll('.reset_to_default');
resetDefaultBtn.forEach((resetBtn, index) => {

	resetBtn.onclick = (e) => {
		e.preventDefault();
		/* let spinReset = resetBtn.getElementsByClassName('btn-icon')[0];
		spinReset.style.animation = 'spinner 1s infinite linear'; */

		var resetPage = resetBtn.dataset.reset;
		var formData = new FormData();
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
					const field_types_associate = ['toggle_switch', 'text', 'textarea', 'email', 'select', 'number'];
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
								const field_types_associate = ['toggle_switch', 'text', 'textarea', 'email', 'select', 'number'];
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
					// spinReset.style.animation = '';
					tutor_toast('Reset Successful', 'Default data for ' + resetPage.toUpperCase() + ' successfully!', 'success');
					document.querySelector('[data-tutor-modal-close]').trigger = true;
				}, 600)
			}
		};
	};
});
const elementByName = (key) => {
	return document.getElementsByName(key);
};

const optionForm = document.querySelector('#tutor-option-form');
if (null !== optionForm) {
	optionForm.addEventListener('change', (event) => {
		document.getElementById('save_tutor_option').disabled = false;
	});
}
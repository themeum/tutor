/**
 * Settings logs, previously known as import/export
 * 
 * @since 3.6.0
 */

import ajaxHandler from '../../helper/ajax-handler';

const time_now = () => {
	return Math.ceil(Date.now() / 1000) + 6 * 60 * 60;
};

const load_saved_data = async () => {
	const formData = new FormData();
	formData.append('action', 'load_saved_data');
	formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);

	const response = await ajaxHandler(formData);
	const data = await response.json();
	tutor_option_history_load(Object.entries(data.data));
};

function tutor_option_history_load(dataset) {
	const { __ } = wp.i18n;
	let output = '';
	if (null !== dataset && 0 !== dataset.length) {
		dataset.forEach((value) => {
			let dataKey = value[0];
			let dataValue = value[1];

			let badgeStatus = dataValue.datatype == 'saved' ? ' label-primary' : dataValue.datatype === 'Imported' ? ' label-success' : ' label-default';
			output += `<div class="tutor-option-field-row">
				<div class="tutor-option-field-label">
					<div class="tutor-fs-7 tutor-fw-medium">${dataValue.history_date}
					<span class="tutor-badge-label tutor-text-capitalize tutor-ml-16${badgeStatus}"> ${dataValue.datatype}</span> </div>
				</div>
				<div class="tutor-option-field-input">
					<button class="tutor-btn tutor-btn-outline-primary tutor-btn-sm apply_settings" data-tutor-modal-target="tutor-modal-bulk-action" data-btntext="${__('Yes, Restore Settings" data-heading="Restore Previous Settings?', 'tutor')}" data-message="${__('WARNING! This will overwrite all existing settings, please proceed with caution.', 'tutor')}" data-id="${dataKey}">${__('Apply', 'tutor')}</button>
					<div class="tutor-dropdown-parent tutor-ml-16">
						<button type="button" class="tutor-iconic-btn" action-tutor-dropdown="toggle">
							<span class="tutor-icon-kebab-menu" area-hidden="true"></span>
						</button>
						<ul class="tutor-dropdown tutor-dropdown-dark tutor-text-left">
							<li>
								<a href="javascript:;" class="tutor-dropdown-item export_single_settings" data-id="${dataKey}">
									<span class="tutor-icon-archive tutor-mr-8" area-hidden="true"></span>
									<span>${__('Download', 'tutor')}</span>
								</a>
							</li>
							<li>
								<a href="javascript:;" class="tutor-dropdown-item delete_single_settings" data-tutor-modal-target="tutor-modal-bulk-action" data-btntext="Yes, Delete Settings" data-heading="Delete This Settings?" data-message="WARNING! This will remove the settings history data from your system, please proceed with caution." data-id="${dataKey}">
									<span class="tutor-icon-trash-can-bold tutor-mr-8" area-hidden="true"></span>
									<span>${__('Delete', 'tutor')}</span>
								</a>
							</li>
						</ul>
					</div>
          		</div>
        	</div>`;
		});
	} else {
		output += `<div class="tutor-option-field-row"><div class="tutor-option-field-label"><p class="tutor-fs-7 tutor-fw-medium">${__('No settings data found.', 'tutor')}</p></div></div>`;
	}
	const heading = `<div class="tutor-option-field-row"><div class="tutor-option-field-label">${__('Date', 'tutor')}</div></div>`;

	const historyData = document.querySelector('.history_data');
	if (historyData) {
		historyData.innerHTML = heading + output;
	}
}

const reset_all_settings_xhttp = async (modalOpener, modalElement, confirmButton) => {
	const { __ } = wp.i18n;
	var formData = new FormData();
	formData.append('action', 'tutor_option_default_save');
	formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);

	try {
		const response = await ajaxHandler(formData);
		const data = await response.json();

		if (data.success) {
			modalElement.classList.remove('tutor-is-active');
			document.body.classList.remove("tutor-modal-open");
			tutor_toast(__('Success', 'tutor'), __('Reset all settings to default successfully!', 'tutor'), 'success');
		} else {
			tutor_toast(__('Failed', 'tutor'), __('Something went wrong!', 'tutor'), 'error');
		}
	} catch (error) {
		tutor_toast(__('Failed', 'tutor'), __('Something went wrong!', 'tutor'), 'error');
	} finally {
		confirmButton.classList.remove('is-loading');
	}
};

const apply_settings_xhttp_request = async (modelOpener, modalElement, confirmButton) => {
	const { __ } = wp.i18n;
	const apply_id = modelOpener.dataset.id;

	const formData = new FormData();
	formData.append('action', 'tutor_apply_settings');
	formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
	formData.append('apply_id', apply_id);

	try {
		const response = await ajaxHandler(formData);
		const data = await response.json();

		if (data.success) {
			modalElement.classList.remove('tutor-is-active');
			document.body.classList.remove("tutor-modal-open");
			tutor_toast(__('Success', 'tutor'), __('Applied settings successfully!', 'tutor'), 'success');
		} else {
			tutor_toast(__('Failed', 'tutor'), __('Something went wrong!', 'tutor'), 'error');
		}
	} catch (error) {
		tutor_toast(__('Failed', 'tutor'), __('Something went wrong!', 'tutor'), 'error');
	} finally {
		confirmButton.classList.remove('is-loading');
	}
};

const delete_settings_xhttp_request = async (modelOpener, modalElement, confirmButton) => {
	const { __ } = wp.i18n;
	const delete_id = modelOpener.dataset.id;

	const formData = new FormData();
	formData.append('action', 'tutor_delete_single_settings');
	formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
	formData.append('time', Date.now());
	formData.append('delete_id', delete_id);

	try {
		const response = await ajaxHandler(formData);
		const data = await response.json();

		if (data.success) {
			modalElement.classList.remove('tutor-is-active');
			document.body.classList.remove("tutor-modal-open");
			tutor_toast(__('Success', 'tutor'), __('Data deleted successfully!', 'tutor'), 'success');

			tutor_option_history_load(Object.entries(data.data));
		} else {
			tutor_toast(__('Failed', 'tutor'), __('Something went wrong!', 'tutor'), 'error');
		}
	} catch (error) {
		tutor_toast(__('Failed', 'tutor'), __('Something went wrong!', 'tutor'), 'error');
	} finally {
		confirmButton.classList.remove('is-loading');
	}
};

const modalConfirmation = (modalOpener) => {
	let modalElement = document.getElementById(modalOpener.dataset.tutorModalTarget);
	let confirmButton = modalElement && modalElement.querySelector('[data-reset]');
	let modalHeading = modalElement && modalElement.querySelector('[data-modal-dynamic-title]');
	let modalMessage = modalElement && modalElement.querySelector('[data-modal-dynamic-content]');

	confirmButton.innerText = modalOpener.dataset.btntext;
	confirmButton.dataset.reset = '';
	modalHeading.innerText = modalOpener.dataset.heading;
	modalMessage.innerText = modalOpener.dataset.message;

	if (confirmButton._handleConfirmClick) {
		confirmButton.removeEventListener('click', confirmButton._handleConfirmClick);
	}

	const handleConfirmClick = () => {
		confirmButton.classList.add('is-loading');

		if (modalOpener.classList.contains('tutor-reset-all')) {
			reset_all_settings_xhttp(modalOpener, modalElement, confirmButton);
		}
		if (modalOpener.classList.contains('apply_settings')) {
			apply_settings_xhttp_request(modalOpener, modalElement, confirmButton);
		}
		if (modalOpener.classList.contains('delete_single_settings')) {
			delete_settings_xhttp_request(modalOpener, modalElement, confirmButton);
		}
	};

	confirmButton._handleConfirmClick = handleConfirmClick;
	confirmButton.addEventListener('click', handleConfirmClick);
};

document.addEventListener('DOMContentLoaded', function () {
	const toolPage = document.querySelector('.tutor-backend-tutor-tools');
	const confirmButton = document.querySelector('.tutor-modal-body button[data-reset]');
	if (toolPage && confirmButton) {
		confirmButton.removeAttribute('data-reset-for');
		confirmButton.classList.remove('reset_to_default');
	}
});

document.addEventListener('click', async function (e) {
	// Handle export all settings
	const exportAllBtn = e.target.closest('#tutor_export_settings');
	if (exportAllBtn) {
		const formData = new FormData();
		formData.append('action', 'tutor_export_settings');
		formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
		const fileName = 'tutor_options_' + time_now();

		try {
			exportAllBtn.classList.add('is-loading');

			const response = await ajaxHandler(formData);
			const data = await response.json();
			const exported_data = data?.data?.exported_data;
			if (exported_data) {
				json_download(JSON.stringify(exported_data), fileName);
				load_saved_data();
			} else {
				tutor_toast(__('Failed', 'tutor'), __('Something went wrong!', 'tutor'), 'error');
			}
		} catch (err) {
			tutor_toast(__('Failed', 'tutor'), __('Something went wrong!', 'tutor'), 'error');
		} finally {
			exportAllBtn.classList.remove('is-loading');
		}
	}

	// Handle export single settings
	const exportBtn = e.target.closest('.export_single_settings');
	if (exportBtn) {
		let export_id = exportBtn.dataset.id;

		const formData = new FormData();
		formData.append('action', 'tutor_export_single_settings');
		formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
		formData.append('time', Date.now());
		formData.append('export_id', export_id);

		const response = await ajaxHandler(formData);
		const data = await response.json();

		json_download(JSON.stringify(data.data), export_id);
	}

	// Handle apply single settings
	const applyBtn = e.target.closest('.apply_settings');
	if (applyBtn) {
		modalConfirmation(applyBtn);
	}

	// Handle delete single settings
	const deleteBtn = e.target.closest('.delete_single_settings');
	if (deleteBtn) {
		modalConfirmation(deleteBtn);
	}

	// Reset to default settings
	const resetAllBtn = e.target.closest('.tutor-reset-all');
	if (resetAllBtn) {
		modalConfirmation(resetAllBtn);
	}
});
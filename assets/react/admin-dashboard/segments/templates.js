
document.addEventListener('DOMContentLoaded', function () {
	const { __ } = wp.i18n;
	const installationWrapper = document.querySelector('.tutorowl-installation-progress-wrapper');
	const successWrapper = document.querySelector('.tutorowl-success-block-wrapper');
	const modalFooter = document.querySelector('.tutorowl-modal-footer');
	const modalWrapper = document.getElementById('tutorowl-import-modal-wrapper');
	const modalOverlay = document.querySelector('.tutorowl-modal-wrapper-overlay');
	const modalContent = document.querySelector('.tutorowl-modal-content');
	const importBtns = document.querySelectorAll('.tutor-template-import-btn');
	const importNowBtn = document.querySelector('.tutor-template-import-now-btn');
	const importCancelBtn = document.getElementById('tutorowl-import-cancel-btn');
	const modalHead = document.querySelector('.tutorowl-modal-head');
	const modalHeading = document.querySelector('.tutorowl-modal-head h5');
	const modalImg = document.querySelector('.tutorowl-modal-img img');
	const modalTitle = document.querySelector('.tutorowl-modal-head-subtitle');
	const importedTemplateName = document.querySelector('.tutorowl-imported-template-name');
	// const dangerBlock = document.querySelector('.tutorowl-danger-block');
	let isModalClosable = true;
	let templateId = null;

	[...importBtns]?.forEach((item) => {
		item.addEventListener('click', (event) => {
			templateId = item.dataset.template;
			modalWrapper.style.display = 'flex';
			const singleItem = item.closest('.tutorowl-single-template');
			const templateName = singleItem.querySelector('.tutorowl-template-name span');
			const templateImg = singleItem.querySelector('.tutorowl-template-preview-img img');
			modalImg?.setAttribute('src', templateImg.src);
			importedTemplateName.innerText = templateName.innerText + ' Template Successfully Imported!';
			modalHeading.innerText = templateName.innerText + ' Template';
		});
	});

	const importModalClose = document.querySelector('.tutorowl-import-modal-close');

	modalOverlay?.addEventListener('click', modalDisable);
	importCancelBtn?.addEventListener('click', modalDisable);
	importModalClose?.addEventListener('click', modalDisable);

	function modalDisable() {
		if (isModalClosable) {
			modalWrapper.style.display = 'none';
			resetModal();
		}
	}

	const importItem = document.querySelectorAll('.tutorowl-import-item');
	const importItemSpinner = document.querySelectorAll('.tutorowl-import-item .svg-spinner');
	const svgCircle = document.querySelectorAll('.tutorowl-import-item .svg-circle');
	const svgSpinner = document.querySelectorAll('.tutorowl-import-item .svg-spinner');
	const progressBar = document.querySelector('.tutorowl-progress-status');
	const progressNumberDiv = document.querySelector('.percentage-number');
	const contentDetails = document.getElementById('tutorowl-content-details');
	const importContentTitle = document.querySelector('.tutorowl-import-item-content-title');
	const plugins_array = ['tutorowl', 'droip'];

	importNowBtn?.addEventListener('click', plugin_installation);
	let progressBarInitialWidth = 0;
	let progressNumber = 0;
	async function plugin_installation() {
		progressBarInitialWidth = 10;
		progressNumber = 10;
		progressBar.style.width = `${progressBarInitialWidth}%`;
		progressNumberDiv.innerText = `${progressNumber}%`;
		let pluginInstallationDone = false;
		isModalClosable = false;
		// dangerBlock.innerText = '';
		// dangerBlock.style.display = 'none';

		// installationWrapper.style.display = 'flex';
		modalContent?.classList.add('tutorowl-template-importing');
		// modalTitle.style.display = 'none';
		importNowBtn.setAttribute('disabled', 'disabled');
		importCancelBtn.setAttribute('disabled', 'disabled');
		importNowBtn.innerText = 'Importing';

		for (let i = 0; i < plugins_array.length; i++) {
			// for (let i = 0; i < 100; i++) {
			importItemSpinner[i].classList.add('active');
			let data = new FormData();
			data.append('action', 'install_plugins');
			data.append('_tutor_nonce', _tutorobject._tutor_nonce);
			data.append('plugin_name', plugins_array[i]);

			pluginInstallationDone = false;
			let response = await fetch(_tutorobject.ajaxurl, {
				method: 'POST',
				body: data,
			});
			let res = await response.json();

			if (res.success) {
				progressUpgrader();
				svgSpinner[i].style.display = 'none';
				svgCircle[i].style.display = 'block';
				pluginInstallationDone = true;
				// tutor_toast(__('Success', 'tutor'), __(`${res?.message}`, 'tutor'), 'success');
			} else {
				tutor_toast(__('Error', 'tutor'), __(`${res?.message}`, 'tutor'), 'error');
				retryImportDomUpdate();
				return;
			}
		}

		if (pluginInstallationDone) {
			try {
				importItemSpinner[importItemSpinner.length - 1].classList.add('active');
				const templateImportResponse = await importContent();
				const importRes = await templateImportResponse.json();
				if (templateImportResponse.ok && importRes.success) {
					importContentTitle.innerHTML = `Importing <span style="color: #5FAC23; font-weight: 600;">assets...</span>`;
					processImportedTemplate();
				} else {
					if ('License missing' == importRes.message) {
						retryImportDomUpdate('License missing, Upgrade to pro.');
						return;
					}
					pluginInstallationDone = false;
				}
			} catch (error) {
				pluginInstallationDone = false;
			}
		}
		if (!pluginInstallationDone) {
			retryImportDomUpdate();
		}
	}

	const importContent = async () => {
		let importFormData = new FormData();
		importFormData.append('action', 'import_droip_template');
		// importFormData.append('nonce_value', _tutorobject.nonce_value);
		importFormData.append('_tutor_nonce', _tutorobject._tutor_nonce);
		importFormData.append('template_id', templateId);
		let response = await fetch(_tutorobject.ajaxurl, {
			method: 'POST',
			body: importFormData,
		});
		return response;
	};

	const processImportedTemplate = () => {
		let importFormData = new FormData();
		importFormData.append('action', 'process_droip_template');
		// importFormData.append('nonce_value', _tutorobject.nonce_value);
		importFormData.append('_tutor_nonce', _tutorobject._tutor_nonce);
		fetch(_tutorobject.ajaxurl, {
			method: 'POST',
			body: importFormData,
		})
			.then((res) => res.json()) // Parse response as JSON
			.then((res) => {
				if (res.success) {
					let data = res.success;
					if (data.status === 'importing') {
						if (data.queue.length) {
							importContentTitle.innerHTML = `Importing <span style="color: #5FAC23; font-weight: 600;"> ${data.queue[0]}... </span>`;
						}
						setTimeout(() => {
							processImportedTemplate();
						}, 10);
					} else if (data.status === 'done') {
						console.log('import done!');
						importContentTitle.innerHTML = ``;
						progressUpgrader();
						setTimeout(() => {
							successModal();
						}, 300);
					}
				}
			});
	};

	const progressUpgrader = () => {
		progressBarInitialWidth = progressBarInitialWidth + 30;
		progressNumber = progressNumber + 30;
		progressBar.style.width = `${progressBarInitialWidth}%`;
		progressNumberDiv.innerText = `${progressNumber}%`;
	};

	const successModal = () => {
		modalContent?.classList.remove('tutorowl-template-importing');
		modalContent?.classList.add('tutorowl-template-imported');
		svgSpinner[svgSpinner.length - 1].style.display = 'none';
		svgCircle[svgCircle.length - 1].style.display = 'block';
		// installationWrapper.style.display = 'none';
		// successWrapper.style.display = 'flex';
		// modalFooter.style.display = 'none';
		importNowBtn.removeAttribute('disabled');
		importCancelBtn.setAttribute('disabled', 'disabled');
		pluginInstallationDone = true;
		isModalClosable = true;
		importNowBtn.innerText = 'Import';
		importContentTitle.innerText = 'Contents';
	};

	const resetModal = () => {
		modalContent?.classList.remove('tutorowl-template-importing');
		modalContent?.classList.remove('tutorowl-template-imported');
		importContentTitle.innerText = 'Contents';
		importNowBtn.innerText = 'Import';
		progressBarInitialWidth = 0;
		progressNumber = 0;
		progressNumberDiv.innerText = `0%`;
		progressBar.style.width = `0%`;
		// installationWrapper.style.display = 'flex';
		// successWrapper.style.display = 'none';
		// modalFooter.style.display = 'flex';
		// dangerBlock.innerText = '';
		// dangerBlock.style.display = 'none';
		pluginInstallationDone = false;
		isModalClosable = true;
		importNowBtn.removeAttribute('disabled');
		importCancelBtn.setAttribute('disabled', 'disabled');
		importItemSpinner.forEach((spinner) => {
			spinner.classList.remove('active');
			spinner.style.display = 'block';
		});
		svgCircle.forEach((circle) => {
			circle.style.display = 'none';
		});
	};

	const retryImportDomUpdate = (message = 'Something went wrong!!, plz try again!') => {
		modalContent?.classList.remove('tutorowl-template-importing');
		modalContent?.classList.remove('tutorowl-template-imported');
		importContentTitle.innerText = 'Contents';
		importNowBtn.innerText = 'Import';
		isModalClosable = true;
		importNowBtn.removeAttribute('disabled');
		importCancelBtn.setAttribute('disabled', 'disabled');
		// importNowBtn.innerText = 'Import Now';
		// // dangerBlock.innerText = message;
		// // dangerBlock.style.display = 'block';
		importItemSpinner.forEach((item) => {
			if (item.classList.contains('active')) {
				item.classList.remove('active');
			}
		});
	};
});

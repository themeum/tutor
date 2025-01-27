
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
	const visitLaterBtn = document.getElementById('tutorowl-visit-later-btn');
	const modalHead = document.querySelector('.tutorowl-modal-head');
	const modalHeading = document.querySelector('.tutorowl-modal-head h5');
	const modalImg = document.querySelector('.tutorowl-modal-img img');
	const modalTitle = document.querySelector('.tutorowl-modal-head-subtitle');
	const importedTemplateName = document.querySelector('.tutorowl-imported-template-name');

	const searchKey = document?.querySelector('.tutorowl-template-search-wrapper');
	const templateList = document.querySelector('.tutorowl-demo-importer-list');

	searchKey?.addEventListener('click', async () => {
		let data = new FormData();
		data.append('action', 'tutor_template_list');
		data.append('_tutor_nonce', _tutorobject._tutor_nonce);
		let response = await fetch(_tutorobject.ajaxurl, {
			method: 'POST',
			body: data,
		});
		let res = await response.json();
		templateList.innerHTML = `${res.data}`;
		console.log(res);
	});
	let isModalClosable = true;
	let templateId = null;

	// Event delegation: Attach a click listener to `templateList`
	templateList.addEventListener('click', (event) => {
		// Check if the clicked element matches the selector for your buttons
		if (event.target && event.target.matches('.tutor-template-import-btn')) {
			let template = event.target;
			console.log(event.target);
			templateId = template.dataset.template;
			modalWrapper.style.display = 'flex';
			const singleItem = template.closest('.tutorowl-single-template');
			const templateName = singleItem.querySelector('.tutorowl-template-name span');
			const templateImg = singleItem.querySelector('.tutorowl-template-preview-img img');
			modalImg?.setAttribute('src', templateImg.src);
			importedTemplateName.innerText = templateName.innerText + ' Template Successfully Imported!';
			modalHeading.innerText = templateName.innerText + ' Template';
		}
	});

	const importModalClose = document.querySelector('.tutorowl-import-modal-close');

	modalOverlay?.addEventListener('click', modalDisable);
	importCancelBtn?.addEventListener('click', modalDisable);
	importModalClose?.addEventListener('click', modalDisable);
	visitLaterBtn?.addEventListener('click', modalDisable);

	// Detect 'Escape' key and close modal
	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' || event.keyCode === 27) {
			modalDisable();
		}
	});

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
	const progressNumberDiv = document.querySelector('.tutorowl-import-percentage-number');
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
		modalContent?.classList.add('tutorowl-template-importing');
		importNowBtn.setAttribute('disabled', 'disabled');
		importCancelBtn.setAttribute('disabled', 'disabled');
		importNowBtn.innerText = 'Importing';

		for (let i = 0; i < plugins_array.length; i++) {
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
				tutor_toast(__('Error, Please try again!', 'tutor'), __(`${res?.message}`, 'tutor'), 'error');
				// retryImportDomUpdate();
				resetModal();
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
		importFormData.append('_tutor_nonce', _tutorobject._tutor_nonce);
		fetch(_tutorobject.ajaxurl, {
			method: 'POST',
			body: importFormData,
		})
			.then((res) => res.json())
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
						importContentTitle.innerHTML = __('Content import done', 'tutor');
						progressUpgrader();
						setTimeout(() => {
							successModal();
						}, 100);
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
		resetModal();
		modalContent?.classList.add('tutorowl-template-imported');
	};

	const resetModal = () => {
		modalContent?.classList.remove('tutorowl-template-importing');
		modalContent?.classList.remove('tutorowl-template-imported');
		importContentTitle.innerText = 'Contents';
		progressBarInitialWidth = 0;
		progressNumber = 0;
		progressNumberDiv.innerText = `0%`;
		progressBar.style.width = `0%`;
		pluginInstallationDone = false;
		isModalClosable = true;
		importNowBtn.innerText = 'Import';
		importNowBtn.removeAttribute('disabled');
		importCancelBtn.removeAttribute('disabled', 'disabled');
		importItemSpinner.forEach((spinner) => {
			spinner.classList.remove('active');
			spinner.style.display = 'block';
		});
		svgCircle.forEach((circle) => {
			circle.style.display = 'none';
		});
	};
});

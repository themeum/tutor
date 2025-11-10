/**
 * Template Import Script
 *
 * This file contains utilities and high-level documentation for the template import
 * workflow and the live preview UI. It describes behavior implemented below:
 * - tutor course data import (supports .zip and JSON import via chunked job)
 * - plugin installation and template import flow (fetch/AJAX POSTs, processing loop)
 * - preview modal management (open/close, shimmer loading state)
 * - device preview switching and iframe resizing/scale handling
 * - communication with the preview iframe (postMessage) for Droip color presets and mode
 *
 * Notes:
 * - This script relies on a global _tutorobject (nonce, ajax URL, site_url, etc.).
 * - Keep DOM selectors and event handlers idempotent because the admin UI may be re-rendered.
 *
 * @file template-import-script.js
 * @since v3.9.2
 */

document.addEventListener('DOMContentLoaded', function () {
	const templateDemoImportRoot = document.querySelector(".tutor-template-import-area");
	const livePreviewModal = document.querySelector(".tutor-template-preview-modal");
	const iframeParent = document.querySelector(".tutor-template-preview-iframe-parent");
	const iframe = document.getElementById("tutor-template-preview-iframe");
	const livePreviewCloseModal = document.querySelector(".tutor-template-preview-modal-back-link");
	const deviceSwitchers = document.querySelectorAll(".tutor-template-preview-device-switcher li");
	const previewTemplateName = document.querySelector(".tutor-preview-template-name");
	const tutorTemplateShimmerEffect = document.querySelector(".tutor-template-shimmer-effect");
	const importBtn = document.querySelector('.tutor-template-import-btn');
	const tutorTemplateCourseDataUrl = document.getElementById("tutor_template_course_data_url");
	let colorPresetBlock = document.getElementById("droip-color-presets");
	const presetHeading = document.querySelector('.tutor-droip-color-presets-heading');

	if (templateDemoImportRoot) {
		// Open live preview modal
		templateDemoImportRoot.addEventListener('click', (event) => {
			if (event.target.closest('.tutor-template-preview-btn')) {
				document.body.style.overflow = 'hidden';
				tutorTemplateShimmerEffect.style.display = "block";
				iframeParent.classList.remove('tutor-divider');
				livePreviewModal.style.display = "flex";
				previewTemplateName.innerText = event.target.dataset.template_name;
				if ( tutorTemplateCourseDataUrl ) {
					tutorTemplateCourseDataUrl.value = event.target.dataset.template_course_data_url;
				}
				iframe.src = event.target.dataset.template_url;
				importBtn.setAttribute('data-import_template_id', event.target.dataset.template_id);
			}
		});

		// Hide loading indicator when iframe is fully loaded
		iframe.addEventListener('load', function () {
			tutorTemplateShimmerEffect.style.display = "none";
			iframeParent.classList.add('tutor-divider');
		});

		livePreviewCloseModal?.addEventListener("click", function () {
			resetPreviewModal();
			const icon = importBtn.querySelector('i');
			importBtn.classList.remove('is-loading');
			importBtn.classList.add('tutor-template-import-btn');
			importBtn.classList.remove('tutor-template-view-template-btn');
			icon.classList.add('tutor-icon-import');
			icon.classList.remove('tutor-icon-circle-mark');
		});

		// Device switcher
		deviceSwitchers.forEach((deviceSwitcher) => {
			deviceSwitcher.addEventListener("click", function () {
				removeActiveClassFromDeviceList(deviceSwitchers);
				deviceSwitcher.classList.add("active");
				let width = this.getAttribute("data-width");
				iframe.style.width = width + 'px';
				resizeIframe();
			});
		});

		// Reset preview modal
		function resetPreviewModal() {
			livePreviewModal.style.display = "none";
			iframe.src = "";
			removeActiveClassFromDeviceList(deviceSwitchers);
			deviceSwitchers[0].classList.add("active");
			tutorTemplateShimmerEffect.style.display = "none";
			document.body.style.overflow = 'visible';
			iframe.style.width = "1400px";
			iframe.style.transformOrigin = "left top";
			colorPresetBlock.style.display = "none";
			presetHeading.style.display = 'none';
		}

		// Remove active class from device list
		function removeActiveClassFromDeviceList(deviceSwitchers) {
			deviceSwitchers.forEach((deviceSwitcher) => {
				deviceSwitcher.classList.remove("active");
			});
		}
	}
});

function resizeIframe() {
	const wrapper = document.querySelector('.tutor-template-preview-iframe-parent');
	const iframe = wrapper.querySelector('.tutor-template-preview-iframe-parent iframe');
	const activeSwitcher = document.querySelector('.tutor-template-preview-device-switcher li.active');

	const designWidth = activeSwitcher.getAttribute('data-width') || 1400;
	const containerWidth = wrapper.offsetWidth;
	if (containerWidth < Number(designWidth)) {
		const scale = containerWidth / Number(designWidth);
		if (scale > 0) {
			iframe.style.transform = `scale(${scale})`;
			iframe.style.transformOrigin = 'left top';
			iframe.style.height = `${100 / scale}%`;
		}
	} else {
		iframe.style.transformOrigin = 'center top';
	}
}

window.addEventListener('resize', resizeIframe);


document.addEventListener('DOMContentLoaded', function () {
	const { __ } = wp.i18n;
	const templateDemoImportRoot = document.querySelector(".tutor-template-import-area");
	const tutorTemplateDemoImportBtn = document.querySelector('.tutor-template-import-btn');
	const includeDemoCourses = document.getElementById('include-demo-courses');
	let templateId;
	let isPluginInstallationDone = false;

	if (templateDemoImportRoot) {
		document.addEventListener('click', (event) => {
			if (event.target.closest('.tutor-template-import-btn')) {
				tutorTemplateDemoImportBtn.classList.add('is-loading');
				tutorTemplateDemoImportBtn.setAttribute('disabled', 'disabled');
				const icon = tutorTemplateDemoImportBtn.querySelector('i');
				tutorTemplateDemoImportBtn.innerHTML = `${icon.outerHTML} Importing`;
				install_plugin();
			}
		});

		document.addEventListener('click', (event) => {
			if (event.target.closest('.tutor-template-view-template-btn')) {
				window.open(_tutorobject.site_url, '_blank');
			}
		});

		const plugins_array = ['tutorbase', 'droip'];

		// install required plugin.
		async function install_plugin() {
			isPluginInstallationDone = false;
			
			for (let i = 0; i < plugins_array.length; i++) {
				try {
					let data = new FormData();
					data.append('action', 'tutor_template_required_plugin_install');
					data.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
					data.append('plugin_name', plugins_array[i]);

					let response = await fetch(_tutorobject.ajaxurl, {
						method: 'POST',
						body: data,
					});
					let res = await response.json();
					if (200 === res.status_code) {
						isPluginInstallationDone = true;
					} else {
						resetImportBtn()
						tutor_toast(__('Error', 'tutor-pro'), res?.message, 'error');
						return;
					}
				} catch (error) {
					resetImportBtn()
					tutor_toast(__('Something went wrong!', 'tutor-pro'), '', 'error');
					return;
				}
			}

			// if plugin installation done start importing template content.
			if (isPluginInstallationDone) {
				templateId = tutorTemplateDemoImportBtn.getAttribute('data-import_template_id');
				try {
					const templateImportResponse = await importContent();
					const importRes = await templateImportResponse.json();
					if (templateImportResponse.ok && 200 === importRes.status_code) {
						processImportedTemplate();
					} else {
						throw new Error(importRes?.message || __('Template import failed!', 'tutor-pro'));
					}
				} catch (error) {
					resetImportBtn()
					tutor_toast(__('Error', 'tutor-pro'), __(error?.message, 'tutor-pro'), 'error');
					return;
				}
			}
		}

		// import template zip file.
		const importContent = async () => {
			let selectedMode = document.querySelector('.color-palette.active')?.getAttribute('data-mode');
			if (!selectedMode) selectedMode = 'default';
			let importFormData = new FormData();
			importFormData.append('action', 'import_droip_template');
			importFormData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
			importFormData.append('template_id', templateId);
			importFormData.append('selected_mode', selectedMode);

			let response = await fetch(_tutorobject.ajaxurl, {
				method: 'POST',
				body: importFormData,
			});
			return response;
		}

		// import template content.
		const processImportedTemplate = async () => {
			let importFormData = new FormData();
			importFormData.append('action', 'process_droip_template');
			importFormData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);

			try {
				const response = await fetch(_tutorobject.ajaxurl, {
					method: 'POST',
					body: importFormData,
				});
				const res = await response.json();
				if (200 === res.status_code) {
					let data = res?.data;
					if (data.status === 'importing') {
						setTimeout(() => {
							processImportedTemplate();
						}, 10);
					} else if (data.status === 'done') {
						if (includeDemoCourses) {
							const isChecked = includeDemoCourses.checked;
							if (isChecked) {
								await importTutorCourseData();
							}
						}
						setImportBtnToViewTemplateBtn();
						tutor_toast(__('Success', 'tutor-pro'), __('Template imported successfully.', 'tutor-pro'), 'success');
						return true;
					}
				}
			} catch (error) {
				resetImportBtn()
				tutor_toast(__('Error', 'tutor-pro'), __(`Error while processing imported template!`, 'tutor-pro'), 'error');
				return false;
			}
		};

		const importTutorCourseData = async (jobId = 0) => {
			const tutorTemplateCourseDataUrl = document.getElementById("tutor_template_course_data_url");
			const courseDataUrl = tutorTemplateCourseDataUrl?.value;
			if (!courseDataUrl) return false;

			const formData = new FormData();
			formData.append('job_id', jobId);
			formData.append('action', 'tutor_pro_import');
			formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
			if (!jobId) {
				const isZipFile = courseDataUrl.toLowerCase().endsWith('.zip');
				const data = await fetch(courseDataUrl);
				const blob = await data.blob();
				if (isZipFile) {
					const filename = courseDataUrl.split('/').pop() || 'importer.zip';
					formData.append('data', blob, filename);
				} else {
					formData.append('data', blob, 'importer.json');
				}
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
						await importTutorCourseData(jobId);
					}
					if (jobProgress == 100) {
						return true;
					}
				}
			} else {
				return false;
			}
		}

		function resetImportBtn() {
			const icon = tutorTemplateDemoImportBtn.querySelector('i');
			tutorTemplateDemoImportBtn.classList.remove('is-loading');
			tutorTemplateDemoImportBtn.classList.add('tutor-template-import-btn');
			tutorTemplateDemoImportBtn.classList.remove('tutor-template-view-template-btn');
			tutorTemplateDemoImportBtn.removeAttribute('disabled');
			icon.classList.add('tutor-icon-import');
			icon.classList.remove('tutor-icon-circle-mark');
		}

		function setImportBtnToViewTemplateBtn() {
			const icon = tutorTemplateDemoImportBtn.querySelector('i');
			tutorTemplateDemoImportBtn.classList.remove('is-loading');
			tutorTemplateDemoImportBtn.classList.remove('tutor-template-import-btn');
			tutorTemplateDemoImportBtn.removeAttribute('disabled');
			tutorTemplateDemoImportBtn.classList.add('tutor-template-view-template-btn');
			icon.classList.remove('tutor-icon-import');
			icon.classList.add('tutor-icon-circle-mark');
			tutorTemplateDemoImportBtn.innerHTML = `${icon.outerHTML} View Template`;
		}
	}
});

(function () {
	const iframe = document.querySelector("#tutor-template-preview-iframe");
	let colorPresetBlock = document.getElementById("droip-color-presets");
	document.addEventListener("DOMContentLoaded", () => {
		// Wait for the iframe to load before interacting with it
		iframe.addEventListener("load", () => {
			resizeIframe();
			window.addEventListener("message", handleMessage);
			const effect2 = document.querySelector('.tutor-template-preview-import-area .tutor-template-shimmer-effect-2');
			effect2.style.display = 'none';
			requestDroipVariableData(); // Safe to send message now
			requestDroipActiveMode();
		});
	});

	const handleMessage = (event) => {
		const { type } = event.data;
		switch (type) {
			case "RETURN_DROIP_VARIABLE_DATA": {
				if (event.data.droipCSSVariable?.data?.[0]?.modes.length > 1) {
					const presetHeading = document.querySelector('.tutor-droip-color-presets-heading');
					presetHeading.style.display = 'block';
					const templateColorPresets = document.querySelector('#droip-color-modes');
					templateColorPresets.style.display = 'block';

					const variables = event.data.droipCSSVariable.data[0];
					const modes = variables.modes;

					// Hide #color-preset if 1 or fewer modes
					colorPresetBlock = document.getElementById("droip-color-presets");
					if (templateColorPresets && colorPresetBlock) {
						if (modes.length <= 1) {
							colorPresetBlock.style.display = "none";
						} else {
							colorPresetBlock.style.display = "flex";
							colorPresetBlock.style.justifyContent = "center";
							colorPresetBlock.style.alignItems = "center";
						}
					}

					const allColors = document.createElement("div");
					allColors.classList.add("all-colors-wrapper");
					allColors.style.display = 'flex';

					modes.forEach((mode, index) => {
						const colors = getPaletteColors(mode.key, variables);
						const singleMode = document.createElement("div");
						singleMode.classList.add("color-palette");
						singleMode.setAttribute("data-mode", mode.key);

						colors.forEach((c, i) => {
							if (i < 3) {
								const singleColor = `<div style="background-color:${c};" data-index="${i}"></div>`;
								singleMode.innerHTML += singleColor;
							}
						});

						allColors.append(singleMode);
					});

					document.querySelector("#droip-color-modes").innerHTML =
						allColors.outerHTML;

					document
						.querySelectorAll(".color-palette")
						.forEach((singleMode) => {
							singleMode.addEventListener("click", () => {
								const selectedMode = singleMode.getAttribute("data-mode");
								handlePaletteSelect(selectedMode);
								addActiveClassOnModeChange(selectedMode);
							});
						});
				}
				break;
			}

			case "RETURN_DROIP_ACTIVE_MODE": {
				const selectedMode = event?.data?.activeMode || 'default'
				addActiveClassOnModeChange(selectedMode);
				break;
			}
		}
	};

	const addActiveClassOnModeChange = (mode) => {
		const elements = document.querySelectorAll(".color-palette");
		if (!elements) return;
		elements.forEach((palette) => {
			palette.classList.remove("active");
		});
		document.querySelector('[data-mode="' + mode + '"]')?.classList?.add('active')
	}

	const sendMessageToIframe = (message) => {
		iframe.contentWindow.postMessage(message, "*"); // You can replace '*' with the exact origin
	};

	const requestDroipVariableData = () => {
		sendMessageToIframe({ type: "GET_DROIP_VARIABLE_DATA" });
	};

	const requestDroipActiveMode = () => {
		sendMessageToIframe({ type: "GET_DROIP_ACTIVE_MODE" });
	};

	const handlePaletteSelect = (mode) => {
		sendMessageToIframe({ type: "APPLY_DROIP_VARIABLE_PALETTE", mode });
	};

	const getPaletteColors = (mode, variables) => {
		if (!variables || !variables.variables) {
			return [];
		}

		const colors = variables.variables
			.filter((v) => v.type === "color")
			.map((color) => {
				if (color.value?.[mode]) {
					return color.value[mode];
				}
				if (color.value?.default) {
					return color.value.default;
				}
				return "#000000";
			});
		return colors;
	};
})();




document.addEventListener('DOMContentLoaded', function () {
	const templateDemoImportRoot = document.querySelector(".tutor-template-import-area");
	const livePreviewModal = document.querySelector(".tutor-template-preview-modal");
	const livePreviewModalOverlay = document.querySelector(".tutor-template-preview-modal-overlay");
	const iframeWrapper = document.querySelector(".tutor-template-preview-iframe-wrapper");
	const iframe = document.getElementById("tutor-template-preview-iframe");
	const livePreviewCloseModal = document.querySelector(".tutor-template-preview-modal-back-link");
	const deviceSwitchers = document.querySelectorAll(".tutor-template-preview-device-switcher li");
	const previewTemplateName = document.querySelector(".tutor-preview-template-name");
	const tutorTemplateShimmerEffect = document.querySelector(".tutor-template-shimmer-effect");
	const importBtn = document.querySelector('.tutor-template-import-btn');

	if (templateDemoImportRoot) {
		// Open live preview modal
		templateDemoImportRoot.addEventListener('click', (event) => {
			if (event.target && event.target.matches('.tutor-template-preview-btn')) {
				tutorTemplateShimmerEffect.style.display = "block";
				livePreviewModal.style.display = "flex";
				previewTemplateName.innerText = event.target.dataset.template_name;
				// iframe.src = event.target.dataset.template_url;
				iframe.src = 'https://pixage.droip.io';
				if (_tutorobject?.tutor_pro_url) {
					importBtn.setAttribute('data-import_template_id', event.target.dataset.template_id);
				}
			}
		});

		// Hide loading indicator when iframe is fully loaded
		iframe.addEventListener('load', function () {
			tutorTemplateShimmerEffect.style.display = "none";
		});

		livePreviewCloseModal?.addEventListener("click", function () {
			resetPreviewModal();
			const icon = importBtn.querySelector('i');
			importBtn.classList.remove('is-loading');
			importBtn.classList.add('tutor-template-import-btn');
			importBtn.classList.remove('tutor-template-view-template-btn');
			icon.classList.add('tutor-icon-import');
			icon.classList.remove('tutor-icon-circle-mark');
			importBtn.innerHTML = `${icon.outerHTML} import`;
		});

		// Device switcher
		deviceSwitchers.forEach((deviceSwitcher) => {
			deviceSwitcher.addEventListener("click", function () {
				removeActiveClassFromDeviceList(deviceSwitchers);
				deviceSwitcher.classList.add("active");
				let width = this.getAttribute("data-width");
				let height = this.getAttribute("data-height");
				iframe.style.width = width;
			});
		});

		// Reset preview modal
		function resetPreviewModal() {
			livePreviewModal.style.display = "none";
			iframe.src = "";
			iframeWrapper.style.width = "100%";
			iframeWrapper.style.height = "100%";
			removeActiveClassFromDeviceList(deviceSwitchers);
			deviceSwitchers[0].classList.add("active");
			tutorTemplateShimmerEffect.style.display = "none";
		}

		// Remove active class from device list
		function removeActiveClassFromDeviceList(deviceSwitchers) {
			deviceSwitchers.forEach((deviceSwitcher) => {
				deviceSwitcher.classList.remove("active");
			});
		}
	}
});


(function () {
	const iframe = document.querySelector("#tutor-template-preview-iframe");
	document.addEventListener("DOMContentLoaded", () => {
		// Wait for the iframe to load before interacting with it
		iframe.addEventListener("load", () => {
			window.addEventListener("message", handleMessage);
			requestDroipVariableData(); // Safe to send message now
			requestDroipActiveMode();
		});
	});

	const handleMessage = (event) => {
		const { type } = event.data;

		switch (type) {
			case "RETURN_DROIP_VARIABLE_DATA": {
				if (event.data.droipCSSVariable?.data?.length > 0) {

					const presetHeading = document.querySelector('.tutor-droip-color-presets-heading');
					presetHeading.style.display = 'block';
					const presetWrapper = document.querySelector('.tutor-template-preview-import-area');
					presetWrapper.style.display = 'flex';

					// setVariables(event.data.droipCSSVariable.data[0]);
					const variables = event.data.droipCSSVariable.data[0];
					const modes = variables.modes;

					// Hide #color-preset if 1 or fewer modes
					const colorPresetBlock = document.getElementById("droip-color-presets");
					if (modes.length <= 1) {
						colorPresetBlock.style.display = "none";
					} else {
						colorPresetBlock.style.display = "flex";
						colorPresetBlock.style.justifyContent = "center";
						colorPresetBlock.style.alignItems = "center";
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
								//
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
		document.querySelectorAll(".color-palette")
			.forEach((palette) => {
				palette.classList.remove("active");
			});
		document.querySelector('[data-mode="' + mode + '"]').classList.add('active')
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

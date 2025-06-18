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
				document.body.style.overflow = 'hidden';
				tutorTemplateShimmerEffect.style.display = "block";
				livePreviewModal.style.display = "flex";
				previewTemplateName.innerText = event.target.dataset.template_name;
				iframe.src = event.target.dataset.template_url;
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
			const proBadge = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" role="presentation" aria-hidden="true" class="css-xron3k-svg-SVGIcon"><rect width="16" height="16" rx="8" fill="#E5803C"></rect><path d="M12.252 7.042c0 .004 0 .008-.003.012l-.862 3.951a.609.609 0 0 1-.598.495H5.213a.61.61 0 0 1-.598-.495l-.862-3.95c0-.005-.002-.009-.003-.013a.609.609 0 0 1 1.056-.51l1.28 1.38 1.362-3.054v-.004a.609.609 0 0 1 1.106.004l1.362 3.054 1.28-1.38a.609.609 0 0 1 1.055.51h.001Z" fill="#fff"></path></svg>`;
			importBtn.innerHTML = `${icon.outerHTML} import ${_tutorobject.tutor_pro_url ? '' : proBadge}`;
		});

		// Device switcher
		deviceSwitchers.forEach((deviceSwitcher) => {
			deviceSwitcher.addEventListener("click", function () {
				removeActiveClassFromDeviceList(deviceSwitchers);
				deviceSwitcher.classList.add("active");
				let width = this.getAttribute("data-width");
				let height = this.getAttribute("data-height");
				let device = this.getAttribute("data-device");
				iframe.style.width = width;
				if ('desktop' !== device) {
					iframe.style.transform = 'scale(0.8085714286)';
					iframe.style.transformOrigin = 'center top';
				} else {
					iframe.style.transform = 'scale(0.8085714286)';
					iframe.style.transformOrigin = 'left top';
				}
			});
		});

		// Reset preview modal
		function resetPreviewModal() {
			livePreviewModal.style.display = "none";
			iframe.src = "";
			// iframeWrapper.style.width = "100%";
			// iframeWrapper.style.height = "100%";
			removeActiveClassFromDeviceList(deviceSwitchers);
			deviceSwitchers[0].classList.add("active");
			tutorTemplateShimmerEffect.style.display = "none";
			document.body.style.overflow = 'visible';
			iframe.style.width = "1400px";
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
				if (event.data.droipCSSVariable?.data?.length > 0) {
					// const presetWrapper = document.querySelector('.tutor-template-preview-template-details');
					// presetWrapper.style.display = 'block';
					const presetHeading = document.querySelector('.tutor-droip-color-presets-heading');
					presetHeading.style.display = 'block';


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

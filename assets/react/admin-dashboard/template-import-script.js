document.addEventListener('DOMContentLoaded', function () {
	// template preview variables
	const templatesDemoImportRoot = document.querySelector(".tutor-templates-demo-import");
	const livePreviewModal = document.querySelector(".tutor-template-live-preview-modal");
	const iframeWrapper = document.querySelector(".tutor-template-preview-iframe-wrapper");
	const iframe = document.getElementById("tutor-template-preview-iframe");
	const livePreviewCloseModal = document.querySelector(".tutor-template-live-preview-close-modal");
	const deviceSwitchers = document.querySelectorAll(".tutor-template-preview-device-switcher li");
	const previewTemplateName = document.querySelector(".tutor-preview-modal-template-name");
	const loadingIndicator = document.querySelector(".tutor-template-loading-indicator");

	if (templatesDemoImportRoot) {
		// Open live preview modal
		templatesDemoImportRoot.addEventListener('click', (event) => {
			if (event.target && event.target.matches('.open-template-live-preview')) {
				loadingIndicator.style.display = "block";
				let previewBtn = event.target;
				const url = previewBtn.getAttribute("data-url");
				const singleTemplate = previewBtn.closest(".tutor-import-single-template");
				const templateName = singleTemplate.querySelector('.tutor-import-template-name span');
				previewTemplateName.innerText = templateName.innerText;
				livePreviewModal.style.display = "flex";
				iframe.src = url;
			}
		});

		// Hide loading indicator when iframe is fully loaded
		iframe.addEventListener('load', function () {
			setTimeout(() => {
				loadingIndicator.style.display = "none";
			}, 10);
		});

		// Close live preview modal
		livePreviewCloseModal?.addEventListener("click", function () {
			resetPreviewModal();
		});

		// Close modal when clicking outside content
		livePreviewModal?.addEventListener("click", function (event) {
			if (event.target === livePreviewModal) {
				resetPreviewModal();
			}
		});

		// Device switcher
		deviceSwitchers.forEach((deviceSwitcher) => {
			deviceSwitcher.addEventListener("click", function () {
				removeActiveClassFromDeviceList(deviceSwitchers);
				deviceSwitcher.classList.add("active");
				let width = this.getAttribute("data-width");
				let height = this.getAttribute("data-height");
				iframeWrapper.style.width = width;
				iframeWrapper.style.height = height;
				// loadingIndicator.style.display = "block";
			});
		});

		// Detect 'Escape' key and close modal
		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' || event.keyCode === 27) {
				resetPreviewModal();
			}
		});

		// Reset preview modal
		function resetPreviewModal() {
			livePreviewModal.style.display = "none";
			iframe.src = "";
			iframeWrapper.style.width = "100%";
			iframeWrapper.style.height = "100%";
			removeActiveClassFromDeviceList(deviceSwitchers);
			deviceSwitchers[0].classList.add("active");
			loadingIndicator.style.display = "none";
		}

		// Remove active class from device list
		function removeActiveClassFromDeviceList(deviceSwitchers) {
			deviceSwitchers.forEach((deviceSwitcher) => {
				deviceSwitcher.classList.remove("active");
			});
		}
	}
});

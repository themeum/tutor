document.addEventListener('DOMContentLoaded', function () {
	// template preview
	const templatesDemoImportRoot = document.querySelector(".tutor-templates-demo-import");
	const previewButtons = document.querySelectorAll(".open-template-live-preview");
	const livePreviewModal = document.querySelector(".template-live-preview-modal");
	const iframe = document.getElementById("template-preview-iframe");
	const livePreviewCloseModal = document.querySelector(".live-preview-close-modal");

	if (templatesDemoImportRoot) {
		templatesDemoImportRoot.addEventListener('click', (event) => {
			if (event.target && event.target.matches('.open-template-live-preview')) {
				let previewBtn = event.target;
				const url = previewBtn.getAttribute("data-url");
				iframe.src = url;
				livePreviewModal.style.display = "flex";
			}
		});

		livePreviewCloseModal?.addEventListener("click", function () {
			livePreviewModal.style.display = "none";
			iframe.src = "";
		});

		// Close modal when clicking outside content
		livePreviewModal?.addEventListener("click", function (event) {
			if (event.target === livePreviewModal) {
				livePreviewModal.style.display = "none";
				iframe.src = "";
			}
		});
	}
});
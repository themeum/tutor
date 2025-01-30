document.addEventListener('DOMContentLoaded', function () {
	// template preview
	const previewButtons = document.querySelectorAll(".open-template-live-preview");
	const livePreviewModal = document.querySelector(".template-live-preview-modal");
	const iframe = document.getElementById("template-preview-iframe");
	const livePreviewCloseModal = document.querySelector(".live-preview-close-modal");

	previewButtons?.forEach(button => {
		button.addEventListener("click", function () {
			const url = this.getAttribute("data-url");
			iframe.src = url;
			console.log(url);
			livePreviewModal.style.display = "flex";
		});
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
});
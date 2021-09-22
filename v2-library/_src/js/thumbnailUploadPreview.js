(function thumbnailUploadPreview() {
	/**
	 * Image Preview : Logo and Signature Upload
	 * Selector -> .tutor-option-field-input.image-previewer
	 */
	const imgPreviewers = document.querySelectorAll('.tutor-thumbnail-uploader');
	const imgPreviews = document.querySelectorAll('.tutor-thumbnail-uploader img');
	const imgPrevInputs = document.querySelectorAll('.tutor-thumbnail-uploader input[type=file]');
	const imgPrevDelBtns = document.querySelectorAll('.tutor-thumbnail-uploader .delete-btn');

	if (imgPrevInputs && imgPrevDelBtns) {
		// Checking Img Src when document loads
		document.addEventListener('DOMContentLoaded', () => {
			imgPreviewers.forEach((previewer) => {
				imgPreviews.forEach((img) => {
					if (img.getAttribute('src')) {
						img.closest('.image-previewer').classList.add('is-selected');
					} else {
						previewer.classList.remove('is-selected');
					}

					console.log(img);
				});
			});
		});

		// Updating Image Preview
		imgPrevInputs.forEach((input) => {
			input.addEventListener('change', function(e) {
				const file = this.files[0];
				const parentEl = input.closest('.image-previewer');
				const targetImg = parentEl.querySelector('img');
				const prevLoader = parentEl.querySelector('.preview-loading');

				if (file) {
					prevLoader.classList.add('is-loading');
					getImageAsDataURL(file, targetImg);
					parentEl.classList.add('is-selected');

					setTimeout(() => {
						prevLoader.classList.remove('is-loading');
					}, 200);
				}
			});
		});

		// Deleting Image Preview
		imgPrevDelBtns.forEach((delBtn) => {
			delBtn.addEventListener('click', function(e) {
				const parentEl = this.closest('.image-previewer');
				const targetImg = parentEl.querySelector('img');

				targetImg.setAttribute('src', '');
				parentEl.classList.remove('is-selected');
			});
		});
	}

	// Get Image file as Data URL
	const getImageAsDataURL = (file, imgSrc) => {
		const reader = new FileReader();
		reader.onload = function() {
			imgSrc.setAttribute('src', this.result);
		};
		reader.readAsDataURL(file);
	};
})();

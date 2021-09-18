(function() {
	'use strict';

	// modal
	tutorModal();
})();

function tutorModal() {
	document.addEventListener('click', (e) => {
		const attr = 'data-tutor-modal-target';
		const closeAttr = 'data-tutor-modal-close';
		const overlay = 'tutor-modal-overlay';

		if (e.target.hasAttribute(attr) || e.target.closest(`[${attr}]`)) {
			e.preventDefault();
			const id = e.target.hasAttribute(attr)
				? e.target.getAttribute(attr)
				: e.target.closest(`[${attr}]`).getAttribute(attr);
			const modal = document.getElementById(id);
			if (modal) {
				modal.classList.add('tutor-is-active');
			}
		}

		if (
			e.target.hasAttribute(closeAttr) ||
			e.target.classList.contains(overlay) ||
			e.target.closest(`[${closeAttr}]`)
		) {
			e.preventDefault();
			const modal = document.querySelectorAll('.tutor-modal.tutor-is-active');
			modal.forEach((m) => {
				m.classList.remove('tutor-is-active');
			});
		}
	});

	// open
	// const modalButton = document.querySelectorAll("[data-tutor-modal-target]");
	// modalButton.forEach(b => {
	//     const id = b.getAttribute("data-tutor-modal-target");
	//     const modal = document.getElementById(id);
	//     if (modal) {
	//         b.addEventListener("click", e => {
	//             e.preventDefault();
	//             modal.classList.add("tutor-is-active");
	//         })
	//     }
	// })

	// close
	// const close = document.querySelectorAll("[data-tutor-modal-close], .tutor-modal-overlay");
	// close.forEach(c => {
	//     c.addEventListener("click", e => {
	//         e.preventDefault();
	//         const modal = document.querySelectorAll(".tutor-modal.tutor-is-active");
	//         modal.forEach(m => {
	//             m.classList.remove("tutor-is-active");
	//         })
	//     })
	// })
}

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

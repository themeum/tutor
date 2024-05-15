(function () {
	'use strict';

	toolTipOnWindowResize();
})();

/**
 * Toggle disable input fields
 * Selecetor -> .tutor-option-single-item.monetization-fees
 */
const moniFees = document.querySelector('.monetization-fees');
const feesToggle = document.querySelector('.monetization-fees input[name=deduct-fees]');

if (moniFees && feesToggle) {
	window.addEventListener('load', () => toggleDisableClass(feesToggle, moniFees));
	feesToggle.addEventListener('change', () => toggleDisableClass(feesToggle, moniFees));
}

const toggleDisableClass = (input, parent) => {
	if (input.checked) {
		parent.classList.remove('is-disable');
		toggleDisableAttribute(moniFees, false);
	} else {
		parent.classList.add('is-disable');
		toggleDisableAttribute(moniFees, true);
	}
};

const toggleDisableAttribute = (elem, state) => {
	const inputArr = elem.querySelectorAll(
		'.tutor-option-field-row:nth-child(2) textarea, .tutor-option-field-row:nth-child(3) select, .tutor-option-field-row:nth-child(3) input',
	);
	inputArr.forEach((item) => (item.disabled = state));
};

/**
 * Image Preview : Logo and Signature Upload
 * Selector -> .tutor-option-field-input.image-previewer
 */
const imgPreviewers = document.querySelectorAll('.image-previewer');
const imgPreviews = document.querySelectorAll('.image-previewer img');
const imgPrevInputs = document.querySelectorAll('.image-previewer input[type=file]');
const imgPrevDelBtns = document.querySelectorAll('.image-previewer .delete-btn');

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
			});
		});
	});

	// Updating Image Preview
	imgPrevInputs.forEach((input) => {
		input.addEventListener('change', function (e) {
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
		delBtn.addEventListener('click', function (e) {
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
	reader.onload = function () {
		imgSrc.setAttribute('src', this.result);
	};
	reader.readAsDataURL(file);
};

/**
 * Sharing Percentage : Monitization > Option
 */
const insInput = document.querySelector('input[type=number]#revenue-instructor');
const adminInput = document.querySelector('input[type=number]#revenue-admin');
const revenueInputs = document.querySelectorAll('.revenue-percentage input[type=number]');
const save_button = document.getElementById('save_tutor_option');
const { __, _x, _n, _nx } = wp.i18n;
const disableSaveButton = (time) => {
	setTimeout(() => {
		if (save_button) save_button.disabled = true;
	}, time)
}

if (insInput && adminInput && revenueInputs) {
	insInput.addEventListener('input', (e) => {

		if (e.target.value <= 100) {
			adminInput.value = 100 - e.target.value;
		}
		else {
			adminInput.value = 0;
			tutor_toast(__('Error', 'tutor'), __('Amount must be less than 100', 'tutor'), 'error');
			disableSaveButton(50);
		}
	});

	adminInput.addEventListener('input', (e) => {

		if (e.target.value <= 100) {
			insInput.value = 100 - e.target.value;
		}
		else {
			insInput.value = 0;
			tutor_toast(__('Error', 'tutor'), __('Amount must be less than 100', 'tutor'), 'error');
			disableSaveButton(50);

		}
	});
}

/**
 * Copy to clipboard : Email > Server Cron
 */
const codeTexarea = document.querySelector('.input-field-code textarea');
const copyBtn = document.querySelector('.code-copy-btn');

if (copyBtn && codeTexarea) {
	copyBtn.addEventListener('click', function (e) {
		e.preventDefault();

		this.focus();
		codeTexarea.select();
		document.execCommand('copy');
		const btnEl = this.innerHTML;
		setTimeout(() => {
			this.innerHTML = btnEl;
		}, 3000);

		// @todo: remove las icon
		this.innerHTML = `
			<span class="tutor-btn-icon las la-clipboard-list"></span>
			<span>Copied to Clipboard!</span>
		`;
	});
}



/**
 * Drag and Drop files -> Import/Export > .import-setting
 */

const dropZoneInputs = document.querySelectorAll('.drag-drop-zone input[type=file]');

dropZoneInputs.forEach((inputEl) => {
	const dropZone = inputEl.closest('.drag-drop-zone');

	['dragover', 'dragleave', 'dragend'].forEach((dragEvent) => {
		if (dragEvent === 'dragover') {
			dropZone.addEventListener(dragEvent, (e) => {
				e.preventDefault();
				dropZone.classList.add('dragover');
			});
		} else {
			dropZone.addEventListener(dragEvent, (e) => {
				dropZone.classList.remove('dragover');
			});
		}
	});

	dropZone.addEventListener('drop', (e) => {
		e.preventDefault();
		const files = e.dataTransfer.files;
		getFilesAndUpdateDOM(files, inputEl, dropZone);
		dropZone.classList.remove('dragover');
	});

	inputEl.addEventListener('change', (e) => {
		const files = e.target.files;
		getFilesAndUpdateDOM(files, inputEl, dropZone);
	});
});

const getFilesAndUpdateDOM = (files, inputEl, dropZone) => {
	if (files.length) {
		inputEl.files = files;
		dropZone.classList.add('file-attached');
		dropZone.querySelector('.file-info').innerHTML = `File attached - ${files[0].name}`;
	} else {
		dropZone.classList.remove('file-attached');
		dropZone.querySelector('.file-info').innerHTML = '';
	}
};

/**
 * Tooltip direction change on smaller devices -> .tooltip-right
 */
function toolTipOnWindowResize() {
	const mediaQuery = window.matchMedia('(max-width: 992px)');
	const hasClass = document.querySelectorAll('.tooltip-responsive');

	if (hasClass.length) {
		if (mediaQuery.matches) {
			const toolTips = document.querySelectorAll('.tooltip-right');
			toolTips.forEach((toolTip) => {
				toolTip.classList.replace('tooltip-right', 'tooltip-left');
			});
		} else {
			const toolTips = document.querySelectorAll('.tooltip-left');
			toolTips.forEach((toolTip) => {
				toolTip.classList.replace('tooltip-left', 'tooltip-right');
			});
		}
	}
}

window.addEventListener('resize', toolTipOnWindowResize);
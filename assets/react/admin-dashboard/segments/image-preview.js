(function () {
	'use strict';

	toolTipOnWindowResize();
})();

/**
 * Navigation tab
 */
const navTabLists = document.querySelectorAll('ul.tutor-option-nav');
const navTabItems = document.querySelectorAll('li.tutor-option-nav-item a');
const navPages = document.querySelectorAll('.tutor-option-nav-page');

navTabLists.forEach((list) => {
	list.addEventListener('click', (e) => {
		const dataTab = e.target.parentElement.dataset.tab || e.target.dataset.tab;
		const pageSlug = e.target.parentElement.dataset.page || e.target.dataset.page;

		if (dataTab) {
			// remove active from other buttons
			navTabItems.forEach((item) => {
				item.classList.remove('active');
				if (e.target.dataset.tab) {
					e.target.classList.add('active');
				} else {
					e.target.parentElement.classList.add('active');
				}
			});
			// hide other tab contents
			navPages.forEach((content) => {
				content.classList.remove('active');
			});
			// add active to the current content
			const currentContent = document.querySelector(`#${dataTab}`);
			currentContent.classList.add('active');

			// History push
			const url = new URL(window.location);

			const params = new URLSearchParams({ page: pageSlug, tab_page: dataTab });
			const pushUrl = `${url.origin + url.pathname}?${params.toString()}`;

			window.history.pushState({}, '', pushUrl);
		}
	});
});



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

if (insInput && adminInput && revenueInputs) {
	insInput.addEventListener('input', (e) => {
		e.target.value <= 100 && (adminInput.value = 100 - e.target.value);
		revenueInputValidation(e.target.value);
	});

	adminInput.addEventListener('input', (e) => {
		e.target.value <= 100 && (insInput.value = 100 - e.target.value);
		revenueInputValidation(e.target.value);
	});
}
const revenueInputValidation = (value) => {
	value > 100
		? revenueInputs.forEach((input) => input.classList.add('warning'))
		: revenueInputs.forEach((input) => input.classList.remove('warning'));
};

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
		this.innerHTML = `
			<span class="tutor-btn-icon las la-clipboard-list"></span>
			<span>Copied to Clipboard!</span>
		`;
	});
}

/**
 * Popup Menu Toggle -> Import/Export > .settings-history
 */
const popupToggle = () => {

 const popupToggleBtns = document.querySelectorAll('.popup-opener .popup-btn');
 const popupMenus = document.querySelectorAll('.popup-opener .popup-menu');

 if (popupToggleBtns && popupMenus) {
	 popupToggleBtns.forEach((btn) => {
		 btn.addEventListener('click', (e) => {
			 const popupClosest = e.target.closest('.popup-opener').querySelector('.popup-menu');
			 popupClosest.classList.toggle('visible');

			 popupMenus.forEach((popupMenu) => {
				 if (popupMenu !== popupClosest) {
					 popupMenu.classList.remove('visible');
				 }
			 });
		 });
	 });

	 window.addEventListener('click', (e) => {
		 if (!e.target.matches('.popup-opener .popup-btn')) {
			 popupMenus.forEach((popupMenu) => {
				 if (popupMenu.classList.contains('visible')) {
					 popupMenu.classList.remove('visible');
				 }
			 });
		 }
	 });
 }
}
popupToggle();

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
window.addEventListener('resize', toolTipOnWindowResize);

/**
 * Search Suggestion box
 */

/* const searchInput = document.querySelector('.search-field input[type=search]');
const searchPopupOpener = document.querySelector('.search-popup-opener');

searchInput.addEventListener('input', (e) => {
	if (e.target.value) {
		searchPopupOpener.classList.add('visible');
	} else {
		searchPopupOpener.classList.remove('visible');
	}
}); */




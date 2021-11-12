/**
 * Color PRESET and PICKER manipulation
 */

const colorPresetInputs = document.querySelectorAll("label.color-preset-input input[type='radio']");
const colorPickerInputs = document.querySelectorAll("label.color-picker-input input[type='color']");
const pickerView = document.querySelectorAll('.color-picker-wrapper [data-key]');
const moreButton = document.querySelector('.more_button');
const otherColors = document.querySelector('.other_colors');
const otherColorRows = otherColors && otherColors.querySelectorAll('.tutor-option-field-row');
const otherColorsExpanded = document.querySelector('.other_colors.expanded');

document.addEventListener('readystatechange', (event) => {
	if (event.target.readyState === 'interactive') {
	}
	if (event.target.readyState === 'complete') {
		if (typeof otherColorsPreview === 'function') {
			otherColorsPreview();
		}
	}
});

const otherColorsPreview = () => {
	let itemsHeight = (initHeight = 0);
	if (otherColors && otherColorRows) {
		otherColorRows.forEach((item, index) => {
			if (0 == index) {
				initHeight = item.offsetHeight;
				if (otherColors) {
					otherColors.style.height = initHeight - 10 + 'px';
				}
			}
			itemsHeight = itemsHeight + item.offsetHeight;
		});
	}
	if (moreButton && otherColors) {
		const toggleHeight = itemsHeight + moreButton.offsetHeight + 'px';
		moreButton.onclick = () => {
			otherColors.classList.toggle('expanded');
			if (otherColors.classList.contains('expanded')) {
				otherColors.style.height = toggleHeight;
				moreButton.querySelector('i').classList.remove('ttr-plus-filled');
				moreButton.querySelector('i').classList.add('ttr-minus-filled');
				moreButton.querySelector('span').innerText = 'Show Less';
			} else {
				otherColors.style.height = initHeight - 10 + 'px';
				moreButton.querySelector('i').classList.remove('ttr-minus-filled');
				moreButton.querySelector('i').classList.add('ttr-plus-filled');
				moreButton.querySelector('span').innerText = 'Show More';
			}
		};
	}
};

// Color PRESET Slecetion (color inputs)
if (colorPresetInputs) {
	colorPresetInputs.forEach((preset) => {
		const presetItem = preset.parentElement.querySelector(".preset-item");
		const presetColors = presetItem.querySelectorAll(".header span");
		const presetInput = preset.closest(".color-preset-input");
		// listening preset input events
		if (true === preset.checked) {
			presetInput.classList.add("is-checked");
		}
		preset.addEventListener("input", (e) => {
			presetInput.classList.add("is-checked");
			presetColors.forEach((color) => {
				let presetKey = color.dataset.preset;
				let presetColor = color.dataset.color;

				pickerView.forEach((toPicker) => {
					let pickerInput = toPicker.dataset.key;
					if (pickerInput == presetKey) {
						toPicker.querySelector('input').value = presetColor;
						toPicker.querySelector('.picker-value').innerHTML = presetColor;

						toPicker.style.borderColor = presetColor;
						toPicker.style.boxShadow = `inset 0 0 0 1px ${presetColor}`;

						setTimeout(() => {
							toPicker.style.borderColor = '#cdcfd5';
							toPicker.style.boxShadow = 'none';
						}, 5000);
					}
				});
			});
		});
	});
}
// Updating Custom Color PRESET
const updateCustomPreset = (picker) => {
	const customPresetEl = document.querySelector("label.color-preset-input[for='custom']");

	// listening picker input events
	picker.addEventListener("input", function (e) {
		const presetColors =
			customPresetEl && customPresetEl.querySelectorAll(".header span");
		const presetItem =
			customPresetEl && customPresetEl.querySelector('input[type="radio"]');
		const pickerCode = picker.nextElementSibling;
		pickerCode.innerText = picker.value;

		colorPickerInputs.forEach((picker) => {
			let preset = picker.dataset.picker;
			presetColors.forEach((toPreset) => {
				if (toPreset.dataset.preset == preset) {
					toPreset.dataset.color = picker.value;
					toPreset.style.backgroundColor = picker.value;
				}
			});
			presetItem.checked = true;
		});
	});
};
// listening color pickers input event
if (colorPickerInputs) {
	colorPickerInputs.forEach((picker) => {
		updateCustomPreset(picker);
	});
}

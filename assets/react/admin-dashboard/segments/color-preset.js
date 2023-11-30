/**
 * Color PRESET and PICKER manipulation
 */

(() => {
	const colorPresetInputs = document.querySelectorAll(
		"label.color-preset-input input[type='radio']",
	);
	const colorPickerInputs = document.querySelectorAll(
		"label.color-picker-input input[type='color']",
	);
	const colorPickerTextInputs = document.querySelectorAll(
		"label.color-picker-input input[type='text']",
	);
	const pickerView = document.querySelectorAll(
		'.color-picker-wrapper [data-key]',
	);

	// Color PRESET selection (color inputs)
	if (colorPresetInputs) {
		colorPresetInputs.forEach((preset) => {
			const presetItem = preset.parentElement.querySelector('.preset-item');
			const presetColors = presetItem.querySelectorAll('.header span');
			const presetInput = preset.closest('.color-preset-input');
			const presetInputLabels = presetInput.parentElement.querySelectorAll(
				'label.color-preset-input',
			);

			// listening preset input events
			if (preset.checked) {
				presetInput.classList.add('is-checked');
			}
			preset.addEventListener('input', (e) => {
				presetInputLabels.forEach((presetInputLabel) =>
					presetInputLabel.classList.remove('is-checked'),
				);
				presetInput.classList.add('is-checked');
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
		const customPresetEl = document.querySelector(
			"label.color-preset-input[for='tutor_preset_custom']",
		);

		// listening picker input events
		picker.addEventListener('input', function(e) {
			const presetColors =
				customPresetEl && customPresetEl.querySelectorAll('.header span');
			const presetItem =
				customPresetEl && customPresetEl.querySelector('input[type="radio"]');
			const pickerCode = picker.nextElementSibling;
			pickerCode.value = picker.value;

			if (presetColors) {
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
		}
		});
	};
	// listening color pickers input event
	if (colorPickerInputs) {
		colorPickerInputs.forEach((picker) => {
			updateCustomPreset(picker);
		});
	}
	if (colorPickerTextInputs) {
		colorPickerTextInputs.forEach((picker) => {
			picker.addEventListener('input', function(e) {
				if (e.target.value.length === 7) {
					picker.previousElementSibling.value = e.target.value;
					picker.previousElementSibling.dispatchEvent(new Event('input', { bubbles: true }));
				}
			});
		});
	}
})();

/**
 * Color PRESET and PICKER manipulation
 */

const colorPresetInputs = document.querySelectorAll(
  "label.color-preset-input input[type='radio']"
);
const colorPickerInputs = document.querySelectorAll(
  ".color-picker-input input[type='color']"
);
const presetView = document.querySelectorAll(
  ".color-picker-wrapper [data-key]"
);
// Color PRESET Slecetion (color inputs)
colorPresetInputs.forEach((preset) => {
  // listening preset input events
  preset.addEventListener("input", (e) => {
    const presetItem = preset.parentElement.querySelector(".preset-item");
    const presetColors = presetItem.querySelectorAll(".header span");

    for (let i = 0; i < presetColors.length; i++) {
      let presetKey = presetColors[i].dataset.key;
      let presetColor = presetColors[i].dataset.color;

      presetView[i].querySelector("input").value = presetColor;
      presetView[i].querySelector(".picker-value").innerHTML = presetColor;

      presetView[i].style.borderColor = presetColor;
      presetView[i].style.boxShadow = `inset 0 0 0 1px ${presetColor}`;

      setTimeout(() => {
        presetView[i].style.borderColor = "#cdcfd5";
        presetView[i].style.boxShadow = "none";
      }, 5000);
    }
  });
});

// Updating Custom Color PRESET
const updateCustomPreset = (picker) => {
  const customPresetEl = document.querySelector(
    "label.color-preset-input:last-child"
  );

  // listening picker input events
  picker.addEventListener("input", function(e) {
    const presetColors = customPresetEl.querySelectorAll(".header span");
    colorPickerInputs.forEach((picker, i) => {
      presetColors[i].dataset.color = picker.value;
      presetColors[i].style.backgroundColor = picker.value;
      presetView[i].querySelector(".picker-value").innerHTML = picker.value;
      customPresetEl.querySelector('input[type="radio"]').checked = true;
    });
  });
};

// listening color pickers input event
colorPickerInputs.forEach((picker) => {
  updateCustomPreset(picker);
});

/**
 * Color PRESET and PICKER manipulation
 */

const colorPreset1 = {
  primary: "#1973AA",
  hover: "#5B616F",
  text: "#CDCFD5",
  light: "#EFF1F6",
};

const colorPreset2 = {
  primary: "#43AA8B",
  hover: "#4D908E",
  text: "#90BE6D",
  light: "#F9C74F",
};

const colorPreset3 = {
  primary: "#4EA8DE",
  hover: "#5A18C2",
  text: "#5E60CE",
  light: "#64DFDF",
};

const colorPresetCustom = {
  primary: "#000000",
  hover: "#5d5d5d",
  text: "#a2a2a2",
  light: "#d8d8d8",
};

const colorPresetInputs = document.querySelectorAll(
  "label.color-preset-input input[type='radio']"
);
const colorPickerInputs = document.querySelectorAll(
  ".color-picker-input input[type='color']"
);

// Setting PRESET values on label > [data-preset=""]
const setColorPreset = (preset) => {
  switch (preset.id) {
    case "color-preset-1":
      for (const [key, value] of Object.entries(colorPreset1)) {
        preset.parentNode.querySelector(
          `[data-preset=${key}]`
        ).style.backgroundColor = value;
      }
      break;
    case "color-preset-2":
      for (const [key, value] of Object.entries(colorPreset2)) {
        preset.parentNode.querySelector(
          `[data-preset=${key}]`
        ).style.backgroundColor = value;
      }
      break;
    case "color-preset-3":
      for (const [key, value] of Object.entries(colorPreset3)) {
        preset.parentNode.querySelector(
          `[data-preset=${key}]`
        ).style.backgroundColor = value;
      }
      break;
    case "color-preset-4":
      for (const [key, value] of Object.entries(colorPresetCustom)) {
        preset.parentNode.querySelector(
          `[data-preset=${key}]`
        ).style.backgroundColor = value;
      }
      break;
    default:
      break;
  }
};

// Color PRESET Slecetion (color inputs)
colorPresetInputs.forEach((preset) => {
  setColorPreset(preset);

  // adding is-checked on label
  if (preset.checked) {
    preset.parentNode.classList.add("is-checked");
  }

  // listening preset input events
  preset.addEventListener("input", (e) => {
    const presetName = e.target.value;

    // toggling is-checked on label
    document.querySelectorAll("label.color-preset-input").forEach((item) => {
      item.classList.remove("is-checked");
    });
    e.target.parentNode.classList.add("is-checked");

    // Updating color picker values
    colorPickerInputs.forEach((picker) => {
      const dataKey = picker.parentNode.dataset.key;

      switch (presetName) {
        case "color-preset-1":
          for (const [key, value] of Object.entries(colorPreset1)) {
            if (dataKey === key) {
              changePickerValues(picker, key, value);
            }
          }
          break;
        case "color-preset-2":
          for (const [key, value] of Object.entries(colorPreset2)) {
            if (dataKey === key) {
              changePickerValues(picker, key, value);
            }
          }
          break;
        case "color-preset-3":
          for (const [key, value] of Object.entries(colorPreset3)) {
            if (dataKey === key) {
              changePickerValues(picker, key, value);
            }
          }
          break;
        case "color-preset-4":
          for (const [key, value] of Object.entries(colorPresetCustom)) {
            if (dataKey === key) {
              changePickerValues(picker, key, value);
            }
          }
          break;
        default:
          break;
      }

      // updating custom preset
      updateCustomPreset(picker);
    });
  });
});

// Updating Custom Color PRESET
const updateCustomPreset = (picker) => {
  const customPresetEl = document.querySelector(
    ".color-preset-input #color-preset-4"
  );

  // listening picker input events
  picker.addEventListener("input", function(e) {
    const dataKey = e.target.parentNode.dataset.key;

    document.querySelector(`[data-key="${dataKey}"] .picker-value`).innerHTML =
      picker.value;

    customPresetEl.checked = true;
    // customPresetEl.disabled = false;

    colorPickerInputs.forEach((picker) => {
      colorPresetCustom[picker.parentNode.dataset.key] = picker.value;
    });

    for (const [key, value] of Object.entries(colorPresetCustom)) {
      customPresetEl.parentElement.querySelector(
        `.preset-item [data-preset=${key}]`
      ).style.backgroundColor = value;
    }

    // toggling is-checked on label
    document.querySelectorAll("label.color-preset-input").forEach((item) => {
      item.classList.remove("is-checked");
      if (item.querySelector("input").checked) {
        item.classList.add("is-checked");
      } else {
        item.classList.remove("is-checked");
      }
    });
  });

  // customPresetEl.checked = false;
  // customPresetEl.disabled = true;
};

// Changing Color PICKER, on Preset selections
const changePickerValues = (picker, key, value) => {
  const label = picker.parentNode;
  const input = label.querySelector("input[type=color]");
  const span = label.querySelector(".picker-value");

  input.value = value;
  span.innerText = value;

  label.style.borderColor = value;
  label.style.boxShadow = `inset 0 0 0 1px ${value}`;

  setTimeout(() => {
    label.style.borderColor = "#cdcfd5";
    label.style.boxShadow = "none";
  }, 5000);
};

// listening color pickers input event
colorPickerInputs.forEach((picker) => {
  updateCustomPreset(picker);
});

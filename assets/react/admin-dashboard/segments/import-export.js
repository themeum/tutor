readyState_complete(() => {
	// typeof resetConfirmation === 'function' ? resetConfirmation() : '';
	// typeof modalResetOpen === 'function' ? modalResetOpen() : '';
});
const modalConfirmation = document.getElementById('tutor-modal-bulk-action');

console.log(modalConfirmation);

document.addEventListener("readystatechange", (event) => {
  if (event.target.readyState === "interactive") {
    export_settings_all();
  }
  if (event.target.readyState === "complete") {
    delete_history_data();
    import_history_data();
    export_single_settings();
    reset_default_options();
    apply_single_settings();
    const historyData = document.querySelector('.history_data');
    if (typeof historyData !== 'undefined' && null !== historyData) {
      setInterval(() => { load_saved_data() }, 100000);
    }
  }
});

/**
 * Highlight items form search suggestion
 */
function highlightSearchedItem(dataKey) {
  const target = document.querySelector(`#${dataKey}`);
  const targetEl =
    target && target.querySelector(`.tutor-option-field-label label`);
  const scrollTargetEl =
    target && target.parentNode.querySelector(".tutor-option-field-row");

  console.log(`target -> ${target} scrollTarget -> ${scrollTargetEl}`);

  if (scrollTargetEl) {
    targetEl.classList.add("isHighlighted");
    setTimeout(() => {
      targetEl.classList.remove("isHighlighted");
    }, 6000);

    scrollTargetEl.scrollIntoView({
      behavior: "smooth",
      block: "center",
      inline: "nearest",
    });
  } else {
    console.warn(`scrollTargetEl Not found!`);
  }
}

const load_saved_data = () => {
  var formData = new FormData();
  formData.append("action", "load_saved_data");
  formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
  const xhttp = new XMLHttpRequest();
  xhttp.open("POST", _tutorobject.ajaxurl, true);
  xhttp.send(formData);
  xhttp.onreadystatechange = function () {
    if (xhttp.readyState === 4) {
      tutor_option_history_load(xhttp.response);
      delete_history_data();
    }
  };
};

function tutor_option_history_load(history_data) {
  var dataset = JSON.parse(history_data).data;
  var output = "";
  if (null !== dataset && 0 !== dataset.length) {
    Object.entries(dataset).forEach(([key, value]) => {
      let badgeStatus =
        value.datatype == "saved" ? " label-primary-wp" : " label-refund";
      output = `<div class="tutor-option-field-row">
					<div class="tutor-option-field-label">
						<p class="text-medium-small">${value.history_date}
						<span class="tutor-badge-label tutor-ml-15${badgeStatus}"> ${value.datatype}</span> </p>
					</div>
					<div class="tutor-option-field-input">
						<button class="tutor-btn tutor-is-outline tutor-is-default tutor-is-xs apply_settings" data-id="${key}">Apply</button>

          <div class="tutor-popup-opener tutor-ml-16">
            <button
            type="button"
            class="popup-btn"
            data-tutor-popup-target="popup-${key}"
            >
            <span class="toggle-icon"></span>
            </button>
            <ul id="popup-${key}" class="popup-menu">
            <li>
              <a class="export_single_settings" data-id="${key}">
                <span class="icon ttr-msg-archive-filled tutor-color-design-white"></span>
                <span class="text-regular-body tutor-color-text-white">Download</span>
              </a>
            </li>
            <li>
              <a class="delete_single_settings" data-id="${key}">
                <span class="icon ttr-delete-fill-filled tutor-color-design-white"></span>
                <span class="text-regular-body tutor-color-text-white">Delete</span>
              </a>
            </li>
            </ul>
          </div>
          </div>
        </div>`+ output;
    });
  } else {
    output += `<div class="tutor-option-field-row"><div class="tutor-option-field-label"><p class="text-medium-small">No settings data found.</p></div></div>`;
  }
  const heading = `<div class="tutor-option-field-row"><div class="tutor-option-field-label"><p>Date</p></div></div>`;

  const historyData = selectorElement(".history_data");
  null !== historyData ? historyData.innerHTML = heading + output : '';
  export_single_settings();
  // popupToggle();
  apply_single_settings();
}
/* import and list dom */

const export_settings_all = () => {
  const export_settings = selectorElement("#export_settings"); //document.querySelector("#export_settings");
  if (export_settings) {
    export_settings.onclick = (e) => {
      if (!e.detail || e.detail == 1) {
        e.preventDefault();
        fetch(_tutorobject.ajaxurl, {
          method: "POST",
          credentials: "same-origin",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
            "Cache-Control": "no-cache",
          },
          body: new URLSearchParams({
            action: "tutor_export_settings",
          }),
        })
          .then((response) => response.json())
          .then((response) => {
            let fileName = "tutor_options_" + time_now();
            json_download(JSON.stringify(response), fileName);
          })
          .catch((err) => console.log(err));
      };
    }
  }
};
/**
 *
 * @returns time by second
 */
const time_now = () => {
  return Math.ceil(Date.now() / 1000) + 6 * 60 * 60;
};

const reset_default_options = () => {
  const reset_options = selectorElement("#reset_options");
  if (reset_options) {
    reset_options.onclick = function () {
      var formData = new FormData();
      formData.append("action", "tutor_option_default_save");
      formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
      const xhttp = new XMLHttpRequest();
      xhttp.open("POST", _tutorobject.ajaxurl, true);
      xhttp.send(formData);
      xhttp.onreadystatechange = function () {
        if (xhttp.readyState === 4) {
          setTimeout(function () {
            tutor_toast("Success", "Reset all settings to default successfully!", "success");
          }, 200);
        }
      };
    };
  }
};

const import_history_data = () => {
  const import_options = selectorElement("#import_options");
  if (import_options) {
    import_options.onclick = (e) => {
      if (!e.detail || e.detail == 1) {
        var fileElem = selectorElement("#drag-drop-input");
        var files = fileElem.files;
        if (files.length <= 0) {
          tutor_toast('Failed', 'Please add a correctly formated json file', 'error');
          return false;
        }
        var fr = new FileReader();
        fr.readAsText(files.item(0));
        fr.onload = function (e) {
          var tutor_options = e.target.result;
          var formData = new FormData();
          formData.append("action", "tutor_import_settings");
          formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
          formData.append("time", time_now());
          formData.append("tutor_options", tutor_options);
          const xhttp = new XMLHttpRequest();
          xhttp.open("POST", _tutorobject.ajaxurl);
          xhttp.send(formData);
          xhttp.onreadystatechange = function () {
            if (xhttp.readyState === 4) {
              tutor_option_history_load(xhttp.responseText);
              delete_history_data();
              // import_history_data();
              setTimeout(function () {
                tutor_toast("Success", "Data imported successfully!", "success");
                fileElem.parentNode.parentNode.querySelector('.file-info').innerText = '';
                fileElem.value = '';
              }, 200);
            }
          };
        };
      };
    };
  }
};

const export_single_settings = () => {
  const single_settings = selectorElements(".export_single_settings");
  if (single_settings) {
    for (let i = 0; i < single_settings.length; i++) {
      single_settings[i].onclick = function () {
        let export_id = single_settings[i].dataset.id;
        var formData = new FormData();
        formData.append("action", "tutor_export_single_settings");
        formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
        formData.append("time", Date.now());
        formData.append("export_id", export_id);

        const xhttp = new XMLHttpRequest();
        xhttp.open("POST", _tutorobject.ajaxurl, true);
        xhttp.send(formData);

        xhttp.onreadystatechange = function () {
          if (xhttp.readyState === 4) {
            // let fileName = "tutor_options_" + _tutorobject.tutor_time_now;
            let fileName = export_id;
            json_download(xhttp.response, fileName);
          }
        };
      };
    }
  }
};


const modalResetOpen = () => {
	const modalResetOpen = document.querySelectorAll('.apply_settings');
	let confirmButton = modalConfirmation && modalConfirmation.querySelector('.reset_to_default');
	let modalHeading = modalConfirmation && modalConfirmation.querySelector('.tutor-modal-title');
	let modalMessage = modalConfirmation && modalConfirmation.querySelector('.tutor-modal-message');
	modalResetOpen.forEach((modalOpen, index) => {
		modalOpen.disabled = false;
		modalOpen.onclick = (e) => {
			confirmButton.dataset.reset = modalOpen.dataset.reset;
			modalHeading.innerText = modalOpen.dataset.heading;
			confirmButton.dataset.resetFor = modalOpen.previousElementSibling.innerText;
			modalMessage.innerText = modalOpen.dataset.message;
		}
	})
}
//data-tutor-modal-target="tutor-modal-bulk-action"
// data-reset="general" data-heading="Reset to Default Settings?"
// data-message="WARNING! This will overwrite all customized settings of this section and reset them to default. Proceed with caution."
const apply_single_settings = () => {
  const apply_settings = selectorElements(".apply_settings");
  if (apply_settings) {
    for (let i = 0; i < apply_settings.length; i++) {

      apply_settings[i].onclick = function () {
        // modalResetOpen();
        // modalConfirmation.
        let apply_id = apply_settings[i].dataset.id;
        var formData = new FormData();
        formData.append("action", "tutor_apply_settings");
        formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
        formData.append("apply_id", apply_id);

        const xhttp = new XMLHttpRequest();
        xhttp.open("POST", _tutorobject.ajaxurl, true);
        xhttp.send(formData);

        xhttp.onreadystatechange = function () {
          if (xhttp.readyState === 4) {
            tutor_toast("Success", "Applied settings successfully!", "success");
            console.log(xhttp.response);
          }
        };
      };
    }
  }
};

const delete_history_data = () => {
  const delete_settings = selectorElements(".delete_single_settings");
  if (delete_settings) {
    for (let i = 0; i < delete_settings.length; i++) {
      delete_settings[i].onclick = function () {
        let delete_id = delete_settings[i].dataset.id;
        var formData = new FormData();
        formData.append("action", "tutor_delete_single_settings");
        formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
        formData.append("time", Date.now());
        formData.append("delete_id", delete_id);

        const xhttp = new XMLHttpRequest();
        xhttp.open("POST", _tutorobject.ajaxurl, true);
        xhttp.send(formData);
        xhttp.onreadystatechange = function () {
          if (xhttp.readyState === 4) {
            console.log(JSON.parse(xhttp.response));

            tutor_option_history_load(xhttp.responseText);
            delete_history_data();

            setTimeout(function () {
              tutor_toast('Success', "Data deleted successfully!", 'success');
            }, 200);
          }
        };
      };
    }
  }
};

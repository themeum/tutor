import { element, elements, notice_message, json_download } from "./lib";
import popupToggle from "./popupToggle";

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

    // load_saved_data();
    // setInterval(function () {
    //   console.log("working");
    // }, 10000);
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

/**
 * Email Manage template - live Preview
 */

const emailManagePageInputs = document.querySelectorAll(
  '.email-manage-page input[type="file"], .email-manage-page input[type="text"], .email-manage-page textarea'
);

const dataSourceEls = document.querySelectorAll(
  ".email-manage-page [data-source]"
);

emailManagePageInputs.forEach((input) => {
  input.addEventListener("input", (e) => {
    const { name, value } = e.target;

    if (e.target.files) {
      const file = e.target.files[0];
      console.dir(e.target.files[0]);

      const reader = new FileReader();
      reader.onload = function() {
        document
          .querySelector('img[data-source="email-title-logo"]')
          .setAttribute("src", this.result);
      };
      reader.readAsDataURL(file);
    }

    const dataSourceEl = document.querySelector(
      `.email-manage-page [data-source=${name}]`
    );

    if (dataSourceEl) {
      if (dataSourceEl.href) {
        dataSourceEl.href = value;
      } else {
        dataSourceEl.innerHTML = value;
      }
    }
  });
});

const load_saved_data = () => {
  var formData = new FormData();
  formData.append("action", "load_saved_data");
  formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
  const xhttp = new XMLHttpRequest();
  xhttp.open("POST", _tutorobject.ajaxurl, true);
  xhttp.send(formData);
  xhttp.onreadystatechange = function() {
    if (xhttp.readyState === 4) {
      tutor_option_history_load(xhttp.response);
    }
  };
};

function tutor_option_history_load(history_data) {
  var dataset = JSON.parse(history_data).data;
  var output = "";
  if (0 !== dataset.length) {
    Object.entries(dataset).forEach(([key, value]) => {
      output += `<div class="tutor-option-field-row">
          <div class="tutor-option-field-label">
            <p class="text-medium-small">${value.history_date}
            <span className="tutor-badge-label label-success">${value.datatype}</span>
            </p>
          </div>
          <div class="tutor-option-field-input"><button class="tutor-btn tutor-is-outline tutor-is-default tutor-is-xs apply_settings" data-id="${key}">Apply</button>
            <div class="popup-opener"><button type="button" class="popup-btn"><span class="toggle-icon"></span></button><ul class="popup-menu"><li><a class="export_single_settings" data-id="${key}"><span class="icon tutor-v2-icon-test icon-msg-archive-filled"></span><span>Download</span></a></li><li><a class="delete_single_settings" data-id="${key}"><span class="icon tutor-v2-icon-test icon-delete-fill-filled"></span><span>Delete</span></a></li></ul></div></div>
        </div>`;
    });
  } else {
    output += `<div class="tutor-option-field-row"><div class="tutor-option-field-label"><p class="text-medium-small">No settings data found.</p></div></div>`;
  }
  const heading = `<div class="tutor-option-field-row"><div class="tutor-option-field-label"><p>Date</p></div></div>`;

  element(".history_data").innerHTML = heading + output;
  export_single_settings();
  popupToggle();
  apply_single_settings();
}
/* import and list dom */

const export_settings_all = () => {
  const export_settings = element("#export_settings"); //document.querySelector("#export_settings");
  if (export_settings) {
    export_settings.onclick = (e) => {
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
};
/**
 *
 * @returns time by second
 */
const time_now = () => {
  return Math.ceil(Date.now() / 1000) + 6 * 60 * 60;
};

const reset_default_options = () => {
  const reset_options = element("#reset_options");
  if (reset_options) {
    reset_options.onclick = function() {
      var formData = new FormData();
      formData.append("action", "tutor_option_default_save");
      formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
      const xhttp = new XMLHttpRequest();
      xhttp.open("POST", _tutorobject.ajaxurl, true);
      xhttp.send(formData);
      xhttp.onreadystatechange = function() {
        if (xhttp.readyState === 4) {
          setTimeout(function() {
            notice_message("Reset all settings to default successfully!");
          }, 200);
        }
      };
    };
  }
};

const import_history_data = () => {
  const import_options = element("#import_options");
  if (import_options) {
    import_options.onclick = function() {
      var files = element("#drag-drop-input").files;
      if (files.length <= 0) {
        return false;
      }
      var fr = new FileReader();
      fr.readAsText(files.item(0));
      fr.onload = function(e) {
        var tutor_options = e.target.result;
        var formData = new FormData();
        formData.append("action", "tutor_import_settings");
        formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
        formData.append("time", time_now());
        formData.append("tutor_options", tutor_options);
        const xhttp = new XMLHttpRequest();
        xhttp.open("POST", _tutorobject.ajaxurl);
        xhttp.send(formData);
        xhttp.onreadystatechange = function() {
          if (xhttp.readyState === 4) {
            tutor_option_history_load(xhttp.responseText);
            delete_history_data();
            import_history_data();
            setTimeout(function() {
              notice_message("Data imported successfully!");
            }, 200);
          }
        };
      };
    };
  }
};

const export_single_settings = () => {
  const single_settings = elements(".export_single_settings");
  for (let i = 0; i < single_settings.length; i++) {
    single_settings[i].onclick = function() {
      let export_id = single_settings[i].dataset.id;
      var formData = new FormData();
      formData.append("action", "tutor_export_single_settings");
      formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
      formData.append("time", Date.now());
      formData.append("export_id", export_id);

      const xhttp = new XMLHttpRequest();
      xhttp.open("POST", _tutorobject.ajaxurl, true);
      xhttp.send(formData);

      xhttp.onreadystatechange = function() {
        if (xhttp.readyState === 4) {
          console.log(xhttp.response);
          // let fileName = "tutor_options_" + _tutorobject.tutor_time_now;
          let fileName = export_id;
          json_download(xhttp.response, fileName);
        }
      };
    };
  }
};

const apply_single_settings = () => {
  const apply_settings = elements(".apply_settings");
  for (let i = 0; i < apply_settings.length; i++) {
    apply_settings[i].onclick = function() {
      let apply_id = apply_settings[i].dataset.id;
      var formData = new FormData();
      formData.append("action", "tutor_apply_settings");
      formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
      formData.append("apply_id", apply_id);

      const xhttp = new XMLHttpRequest();
      xhttp.open("POST", _tutorobject.ajaxurl, true);
      xhttp.send(formData);

      xhttp.onreadystatechange = function() {
        if (xhttp.readyState === 4) {
          notice_message("Applied settings successfully!");
          console.log(xhttp.response);
        }
      };
    };
  }
};

const delete_history_data = () => {
  const noticeMessage = element(".tutor-notification");
  const delete_settings = elements(".delete_single_settings");
  for (let i = 0; i < delete_settings.length; i++) {
    delete_settings[i].onclick = function() {
      let delete_id = delete_settings[i].dataset.id;
      var formData = new FormData();
      formData.append("action", "tutor_delete_single_settings");
      formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
      formData.append("time", Date.now());
      formData.append("delete_id", delete_id);

      noticeMessage.classList.add("show");
      const xhttp = new XMLHttpRequest();
      xhttp.open("POST", _tutorobject.ajaxurl, true);
      xhttp.send(formData);
      xhttp.onreadystatechange = function() {
        if (xhttp.readyState === 4) {
          tutor_option_history_load(xhttp.responseText);
          delete_history_data();

          setTimeout(function() {
            notice_message("Data deleted successfully!");
          }, 200);
        }
      };
    };
  }
};

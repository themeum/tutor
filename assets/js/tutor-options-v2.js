"use strict";
// Tutor v2 icons
const { angleRight, magnifyingGlass, warning } = tutorIconsV2;
jQuery(document).ready(function ($) {
  "use strict";

  let image_uploader = document.querySelectorAll(".image_upload_button");
  // let image_input = document.getElementById("image_url_field");

  for (let i = 0; i < image_uploader.length; ++i) {
    let image_upload_wrap = image_uploader[i].closest(".image-previewer");
    let input_file = image_upload_wrap.querySelector(".input_file");
    let upload_preview = image_upload_wrap.querySelector(".upload_preview");
    let image_delete = image_upload_wrap.querySelector(".delete-btn");

    image_uploader[i].onclick = function (e) {
      e.preventDefault();

      var image_frame = wp.media({
        title: "Upload Image",
        library: {
          type: "image",
        },
        multiple: false,
        frame: "post",
        state: "insert",
      });

      image_frame.open();

      /* image_frame.on("select", function (e) {
        console.log("image size");
        console.log(image.state().get("selection").first().toJSON());

        var image_url = image_frame.state().get("selection").first().toJSON().url;

        upload_previewer.src = image_input.value = image_url;
      }); */

      image_frame.on("insert", function (selection) {
        var state = image_frame.state();
        selection = selection || state.get("selection");
        if (!selection) return;
        // We set multiple to false so only get one image from the uploader
        var attachment = selection.first();
        var display = state.display(attachment).toJSON(); // <-- additional properties
        attachment = attachment.toJSON();
        // Do something with attachment.id and/or attachment.url here
        var image_url = attachment.sizes[display.size].url;

        upload_preview.src = input_file.value = image_url;
      });
    };

    image_delete.onclick = function () {
      input_file.value = "";
    };
  }

  $(window).on("click", function (e) {
    $(".tutor-notification, .search_result").removeClass("show");
  });

  $(".tutor-notification-close").click(function (e) {
    $(".tutor-notification").removeClass("show");
  });

  $("#save_tutor_option").click(function (e) {
    e.preventDefault();
    $("#tutor-option-form").submit();
  });

  $("#tutor-option-form").submit(function (e) {
    e.preventDefault();

    var $form = $(this);
    var data = $form.serializeObject();
    console.log(data);
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: "POST",
      data: data,
      beforeSend: function () {},
      success: function (data) {
        notice_message("Data Saved Successfully");
      },
      complete: function () {},
    });
  });

  function titleCase(str) {
    var splitStr = str.toLowerCase().split(" ");
    for (var i = 0; i < splitStr.length; i++) {
      // You do not need to check if i is larger than splitStr length, as your for does that for you
      // Assign it back to the array
      splitStr[i] =
        splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);
    }
    // Directly return the joined string
    return splitStr.join(" ");
  }

  function view_item(text, section_slug, section, block, field_key) {
    var navTrack = block ? `${angleRight} ${block}` : "";

    var output = `
      <a data-tab="${section_slug}" data-key="field_${field_key}">
        <div class="search_result_title">
          ${magnifyingGlass}
          <span class="text-regular-caption">${text}</span>
        </div>
        <div class="search_navigation">
          <div class="nav-track text-regular-small">
            <span>${section}</span>
            <span>${navTrack}</span>
          </div>
        </div>
      </a>`;

    return output;
  }

  $("#search_settings").on("input", function (e) {
    e.preventDefault();

    if (e.target.value) {
      var searchKey = this.value;
      $.ajax({
        url: window._tutorobject.ajaxurl,
        type: "POST",
        data: {
          action: "tutor_option_search",
          keyword: searchKey,
        },
        // beforeSend: function () {},
        success: function (data) {
          var output = "",
            wrapped_item = "",
            notfound = true,
            item_text = "",
            section_slug = "",
            section_label = "",
            block_label = "",
            matchedText = "",
            searchKeyRegex = "",
            field_key = "",
            result = data.data.fields;

          Object.values(result).forEach(function (item, index, arr) {
            item_text = item.label;
            section_slug = item.section_slug;
            section_label = item.section_label;
            block_label = item.block_label;
            field_key = item.key;
            searchKeyRegex = new RegExp(searchKey, "ig");
            // console.log(item_text.match(searchKeyRegex));
            matchedText = item_text.match(searchKeyRegex)?.[0];

            if (matchedText) {
              wrapped_item = item_text.replace(
                searchKeyRegex,
                `<span style='color: #212327; font-weight:500'>${matchedText}</span>`
              );
              output += view_item(
                wrapped_item,
                section_slug,
                section_label,
                block_label,
                field_key
              );
              notfound = false;
            }
          });
          if (notfound) {
            output += `<div class="no_item"> ${warning} No Results Found</div>`;
          }
          $(".search_result").html(output).addClass("show");
          output = "";
          // console.log("working");
        },
        complete: function () {
          // Active navigation element
          navigationTrigger();
        },
      });
    } else {
      document.querySelector(".search-popup-opener").classList.remove("show");
    }
  });
});

const notice_message = (message = "") => {
  let noticeElement = element(".tutor-notification");
  noticeElement.classList.add("show");
  if (message) {
    noticeElement.querySelector(".tutor-notification-content p").innerText =
      message;
  }
  setTimeout(() => {
    noticeElement.classList.remove("show");
  }, 4000);
};

const element = (selector) => {
  return document.querySelector(selector);
};
const elements = (selector) => {
  return document.querySelectorAll(selector);
};
/**
 * Search suggestion, navigation trigger
 */
function navigationTrigger() {
  const suggestionLinks = elements(".search-field .search-popup-opener a");
  //document.querySelectorAll(".search-field .search-popup-opener a");
  const navTabItems = element("li.tutor-option-nav-item a");
  //document.querySelectorAll("li.tutor-option-nav-item a");
  const navPages = elements(".tutor-option-nav-page");
  //document.querySelectorAll(".tutor-option-nav-page");

  suggestionLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      const dataTab = e.target.closest("[data-tab]").dataset.tab;
      const dataKey = e.target.closest("[data-key]").dataset.key;
      if (dataTab) {
        // remove active from other buttons
        navTabItems.forEach((item) => {
          item.classList.remove("active");
        });
        // add active to the current nav item
        document
          .querySelector(`.tutor-option-tabs [data-tab=${dataTab}]`)
          .classList.add("active");

        // hide other tab contents
        navPages.forEach((content) => {
          content.classList.remove("active");
        });
        // add active to the current content
        document
          .querySelector(`.tutor-option-tab-pages #${dataTab}`)
          .classList.add("active");

        // History push
        const url = new URL(window.location);
        url.searchParams.set("tab_page", dataTab);
        window.history.pushState({}, "", url);
      }

      // Reset + Hide Suggestion box
      document.querySelector(".search-popup-opener").classList.remove("show");
      document.querySelector('.search-field input[type="search"]').value = "";

      // Highlight selected element
      highlightSearchedItem(dataKey);
    });
  });
}

document.addEventListener("readystatechange", (event) => {
  if (event.target.readyState === "interactive") {
    export_settings_all();
  }
  if (event.target.readyState === "complete") {
    import_history_data();
    export_single_settings();
    delete_history_data();
    reset_default_options();
    apply_single_settings();
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
 * Function to download json file
 * @param {json} response
 * @param {string} fileName
 */
function json_download(response, fileName) {
  const fileToSave = new Blob([response], {
    type: "application/json",
  });
  const el = document.createElement("a");
  el.href = URL.createObjectURL(fileToSave);
  el.download = fileName;
  el.click();
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
      reader.onload = function () {
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

function tutor_option_history_load(history_data) {
  // console.log(history_data);
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
            notice_message("Reset all settings to default successfully!");
          }, 200);
        }
      };
    };
  }
};

const import_history_data = () => {
  const import_options = element("#import_options"); //document.querySelector("#import_options");
  if (import_options) {
    import_options.onclick = function () {
      var files = element("#drag-drop-input").files;
      if (files.length <= 0) {
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

        console.log(tutor_options);

        const xhttp = new XMLHttpRequest();
        xhttp.open("POST", _tutorobject.ajaxurl);
        xhttp.send(formData);
        xhttp.onreadystatechange = function () {
          if (xhttp.readyState === 4) {
            // tutor_option_history_load(xhttp.responseText);
            delete_history_data();
            import_history_data();
            setTimeout(function () {
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
    apply_settings[i].onclick = function () {
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
    delete_settings[i].onclick = function () {
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
      xhttp.onreadystatechange = function () {
        if (xhttp.readyState === 4) {
          tutor_option_history_load(xhttp.responseText);
          delete_history_data();

          setTimeout(function () {
            notice_message("Data deleted successfully!");
          }, 200);
        }
      };
    };
  }
};

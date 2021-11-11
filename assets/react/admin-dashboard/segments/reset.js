/*
Reset to default for settings individual page
*/
console.log("reset-to-default");
const resetDefaultBtn = document.querySelectorAll(".reset_to_default");
resetDefaultBtn.forEach((resetBtn, index) => {
  resetBtn.onclick = (e) => {
    e.preventDefault();
    var formData = new FormData();
    formData.append("action", "reset_settings_data");
    formData.append("reset_page", resetBtn.dataset.reset);
    formData.append(_tutorobject.nonce_key, _tutorobject._tutor_nonce);
    const xhttp = new XMLHttpRequest();
    xhttp.open("POST", _tutorobject.ajaxurl, true);
    xhttp.send(formData);
    xhttp.onreadystatechange = function() {
      if (xhttp.readyState === 4) {
        let pageData = JSON.parse(xhttp.response).data;
        pageData.forEach((item) => {
          const field_types = [
            "toggle_switch",
            "text",
            "textarea",
            "email",
            "select",
            "number",
          ];
          if (field_types.includes(item.type)) {
            let itemName = "tutor_option[" + item.key + "]";
            let itemElem = fieldByName(itemName)[0];
            if (item.type == "select") {
              let sOptions = itemElem.options;
              for (var i = 0; i < sOptions.length; i++) {
                sOptions[i].selected = false;
              }
            } else {
              itemElem.value = item.default;
              itemElem.nextElementSibling.value = item.default;
              itemElem.nextElementSibling.checked = false;
            }
          }
        });
      }
    };
  };
});
const fieldByName = (key) => {
  return document.getElementsByName(key);
};

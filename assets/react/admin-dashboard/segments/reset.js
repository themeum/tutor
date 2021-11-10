/*
Reset to default for settings individual page
*/
console.log("reset-to-default");
const resetDefaultBtn = document.querySelectorAll(".reset_to_default");
resetDefaultBtn.forEach = (item) => {
  item.onclick = (e) => {
    console.log("reset");
    e.preventDefault();
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
};

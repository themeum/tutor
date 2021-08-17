jQuery(document).ready(function ($) {
  "use strict";

  $("#save_tutor_option").click(function (e) {
    console.log("not working");
    e.preventDefault();
    $("#tutor-option-form").submit();
  });

  $("#tutor-option-form").submit(function (e) {
    e.preventDefault();

    var $form = $(this);
    var data = $form.serializeObject();

    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: "POST",
      data: data,
      beforeSend: function () {},
      success: function (data) {
        console.log(data);
      },
      complete: function () {},
    });
  });
});

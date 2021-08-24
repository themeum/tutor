jQuery(document).ready(function ($) {
  "use strict";

  $(window).on("click", function (e) {
    $(".tutor-notification").removeClass("show");
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
        // console.log(data.data);
        $(".tutor-notification").addClass("show");
      },
      complete: function () {},
    });
  });

  $("#search_settings").on("keyup", function (e) {
    e.preventDefault();
    var keyword = this.value;
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: "POST",
      data: keyword,
      beforeSend: function () {},
      success: function (data) {
        console.log(data);
      },
      complete: function () {},
    });
  });
});

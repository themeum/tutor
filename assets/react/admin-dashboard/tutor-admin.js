import "../lib/common";
import "./segments/image-preview";
import "./segments/options";
import "./segments/import-export";
// import "./segments/addonlist-data";
import "./segments/addonlist";

jQuery(document).ready(function($) {
  "use strict";

  const { __, _x, _n, _nx } = wp.i18n;
  const search_student_placeholder = __("Search students", "tutor");
  /**
   * Color Picker
   * @since v.1.2.21
   */
  if (jQuery().wpColorPicker) {
    $(".tutor_colorpicker").wpColorPicker();
  }

  if (jQuery().select2) {
    $(".tutor_select2").select2();
  }

  /**
   * Option Settings Nav Tab
   */
  $(".tutor-option-nav-tabs li a").click(function(e) {
    e.preventDefault();
    var tab_page_id = $(this).attr("data-tab");
    $(".option-nav-item").removeClass("current");
    $(this)
      .closest("li")
      .addClass("current");
    $(".tutor-option-nav-page").hide();
    $(tab_page_id)
      .addClass("current-page")
      .show();
    window.history.pushState("obj", "", $(this).attr("href"));
  });

  $(".tutor-form-toggle-input").on("change", function(e) {
    var toggleInput = $(this).siblings("input");
    $(this).prop("checked") ? toggleInput.val("on") : toggleInput.val("off");
  });

  $("#tutor-option-form").submit(function(e) {
    e.preventDefault();

    var $form = $(this);
    var data = $form.serializeObject();
    console.log(data);
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: "POST",
      data: data,
      beforeSend: function() {
        $form.find(".button").addClass("tutor-updating-message");
      },
      success: function(data) {
        data.success
          ? tutor_toast(
              __("Saved", "tutor"),
              $form.data("toast_success_message"),
              "success"
            )
          : tutor_toast(
              __("Request Error", "tutor"),
              __("Could not save", "tutor"),
              "error"
            );
      },
      complete: function() {
        $form.find(".button").removeClass("tutor-updating-message");
      },
    });
  });

  /**
   * End Withdraw nav tabs
   */

  $(document).on(
    "click",
    ".video_source_wrap_html5 .video_upload_btn",
    function(event) {
      event.preventDefault();

      var $that = $(this);
      var frame;
      // If the media frame already exists, reopen it.
      if (frame) {
        frame.open();
        return;
      }

      // Create a new media frame
      frame = wp.media({
        title: __("Select or Upload Media Of Your Choice", "tutor"),
        button: {
          text: __("Upload media", "tutor"),
        },
        library: { type: "video" },
        multiple: false, // Set to true to allow multiple files to be selected
      });

      // When an image is selected in the media frame...
      frame.on("select", function() {
        // Get media attachment details from the frame state
        var attachment = frame
          .state()
          .get("selection")
          .first()
          .toJSON();
        $that
          .closest(".video_source_wrap_html5")
          .find("span.video_media_id")
          .data("video_url", attachment.url)
          .text(attachment.id)
          .trigger("paste")
          .closest("p")
          .show();
        $that
          .closest(".video_source_wrap_html5")
          .find("input.input_source_video_id")
          .val(attachment.id);
      });
      // Finally, open the modal on click
      frame.open();
    }
  );

  /**
   * Open Sidebar Menu
   */
  if (_tutorobject.open_tutor_admin_menu) {
    var $adminMenu = $("#adminmenu");
    $adminMenu
      .find('[href="admin.php?page=tutor"]')
      .closest("li.wp-has-submenu")
      .addClass("wp-has-current-submenu");
    $adminMenu
      .find('[href="admin.php?page=tutor"]')
      .closest("li.wp-has-submenu")
      .find("a.wp-has-submenu")
      .removeClass("wp-has-current-submenu")
      .addClass("wp-has-current-submenu");
  }

  $(document).on("click", ".tutor-option-media-upload-btn", function(e) {
    e.preventDefault();

    var $that = $(this);
    var frame;
    if (frame) {
      frame.open();
      return;
    }
    frame = wp.media({
      title: __("Select or Upload Media Of Your Choice", "tutor"),
      button: {
        text: __("Upload media", "tutor"),
      },
      multiple: false,
    });
    frame.on("select", function() {
      var attachment = frame
        .state()
        .get("selection")
        .first()
        .toJSON();
      $that
        .closest(".option-media-wrap")
        .find(".option-media-preview")
        .html('<img src="' + attachment.url + '" alt="" />');
      $that
        .closest(".option-media-wrap")
        .find("input")
        .val(attachment.id);
      $that
        .closest(".option-media-wrap")
        .find(".tutor-media-option-trash-btn")
        .show();
    });
    frame.open();
  });

  /**
   * Remove option media
   * @since v.1.4.3
   */
  $(document).on("click", ".tutor-media-option-trash-btn", function(e) {
    e.preventDefault();

    var $that = $(this);
    $that
      .closest(".option-media-wrap")
      .find("img")
      .remove();
    $that
      .closest(".option-media-wrap")
      .find("input")
      .val("");
    $that
      .closest(".option-media-wrap")
      .find(".tutor-media-option-trash-btn")
      .hide();
  });

  $(document).on("change", ".tutor_addons_list_item", function(e) {
    var $that = $(this);

    var isEnable = $that.prop("checked") ? 1 : 0;
    var addonFieldName = $that.attr("name");

    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: "POST",
      data: {
        isEnable: isEnable,
        addonFieldName: addonFieldName,
        action: "addon_enable_disable",
      },
      success: function(data) {
        if (data.success) {
          //Success
        }
      },
    });
  });

  /**
   * Add instructor
   * @since v.1.0.3
   */
  $(document).on("submit", "#new-instructor-form", function(e) {
    e.preventDefault();

    var $that = $(this);
    var formData = $that.serializeObject();
    formData.action = "tutor_add_instructor";

    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: "POST",
      data: formData,
      success: function(data) {
        if (data.success) {
          $that.trigger("reset");
          $("#form-response").html(
            '<p class="tutor-status-approved-context">' + data.data.msg + "</p>"
          );
        } else {
          var errorMsg = "";

          var errors = data.data.errors;
          if (errors && Object.keys(errors).length) {
            $.each(data.data.errors, function(index, value) {
              if (
                value &&
                typeof value === "object" &&
                value.constructor === Object
              ) {
                $.each(value, function(key, value1) {
                  errorMsg +=
                    '<p class="tutor-required-fields">' + value1[0] + "</p>";
                });
              } else {
                errorMsg +=
                  '<p class="tutor-required-fields">' + value + "</p>";
              }
            });
            $("#form-response").html(errorMsg);
          }
        }
      },
    });
  });

  /**
   * Instructor block unblock action
   * @since v.1.5.3
   */

  $(document).on("click", "a.instructor-action", function(e) {
    e.preventDefault();

    var $that = $(this);
    var action = $that.attr("data-action");
    var instructor_id = $that.attr("data-instructor-id");

    var prompt_message = $that.attr("data-prompt-message");
    if (prompt_message && !confirm(prompt_message)) {
      // Avoid Accidental CLick
      return;
    }

    var nonce_key = _tutorobject.nonce_key;
    var json_data = {
      instructor_id: instructor_id,
      action_name: action,
      action: "instructor_approval_action",
    };
    json_data[nonce_key] = _tutorobject[nonce_key];

    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: "POST",
      data: json_data,
      beforeSend: function() {
        $that.addClass("tutor-updating-message");
      },
      success: function(data) {
        location.reload(true);
      },
      complete: function() {
        $that.removeClass("tutor-updating-message");
      },
    });
  });

  /**
   * Add Assignment
   */
  $(document).on("click", ".add-assignment-attachments", function(event) {
    event.preventDefault();

    var $that = $(this);
    var frame;
    // If the media frame already exists, reopen it.
    if (frame) {
      frame.open();
      return;
    }

    // Create a new media frame
    frame = wp.media({
      title: __("Select or Upload Media Of Your Choice", "tutor"),
      button: {
        text: __("Upload media", "tutor"),
      },
      multiple: false, // Set to true to allow multiple files to be selected
    });

    // When an image is selected in the media frame...
    frame.on("select", function() {
      // Get media attachment details from the frame state
      var attachment = frame
        .state()
        .get("selection")
        .first()
        .toJSON();

      var field_markup =
        '<div class="tutor-individual-attachment-file"><p class="attachment-file-name">' +
        attachment.filename +
        '</p><input type="hidden" name="tutor_assignment_attachments[]" value="' +
        attachment.id +
        '"><a href="javascript:;" class="remove-assignment-attachment-a text-muted"> &times; Remove</a></div>';

      $("#assignment-attached-file").append(field_markup);
      $that
        .closest(".video_source_wrap_html5")
        .find("input")
        .val(attachment.id);
    });
    // Finally, open the modal on click
    frame.open();
  });

  $(document).on("click", ".remove-assignment-attachment-a", function(event) {
    event.preventDefault();
    $(this)
      .closest(".tutor-individual-attachment-file")
      .remove();
  });

  /**
   * Used for backend profile photo upload.
   */

  //tutor_video_poster_upload_btn
  $(document).on("click", ".tutor_video_poster_upload_btn", function(event) {
    event.preventDefault();

    var $that = $(this);
    var frame;
    // If the media frame already exists, reopen it.
    if (frame) {
      frame.open();
      return;
    }

    // Create a new media frame
    frame = wp.media({
      title: __("Select or Upload Media Of Your Choice", "tutor"),
      button: {
        text: __("Upload media", "tutor"),
      },
      multiple: false, // Set to true to allow multiple files to be selected
    });

    // When an image is selected in the media frame...
    frame.on("select", function() {
      // Get media attachment details from the frame state
      var attachment = frame
        .state()
        .get("selection")
        .first()
        .toJSON();
      $that
        .closest(".tutor-video-poster-wrap")
        .find(".video-poster-img")
        .html('<img src="' + attachment.sizes.thumbnail.url + '" alt="" />');
      $that
        .closest(".tutor-video-poster-wrap")
        .find("input")
        .val(attachment.id);
    });
    // Finally, open the modal on click
    frame.open();
  });

  /**
   * Tutor Memberships toggle in Paid Membership Pro panel
   * @since v.1.3.6
   */

  $(document).on("change", "#tutor_pmpro_membership_model_select", function(e) {
    e.preventDefault();

    var $that = $(this);

    if ($that.val() === "category_wise_membership") {
      $(".membership_course_categories").show();
    } else {
      $(".membership_course_categories").hide();
    }
  });

  $(document).on("change", "#tutor_pmpro_membership_model_select", function(e) {
    e.preventDefault();

    var $that = $(this);

    if ($that.val() === "category_wise_membership") {
      $(".membership_course_categories").show();
    } else {
      $(".membership_course_categories").hide();
    }
  });

  // Require category selection
  $(document).on("submit", ".pmpro_admin form", function(e) {
    var form = $(this);

    if (!form.find('input[name="tutor_action"]').length) {
      // Level editor or tutor action not necessary
      return;
    }

    if (
      form.find('[name="tutor_pmpro_membership_model"]').val() ==
        "category_wise_membership" &&
      !form.find(".membership_course_categories input:checked").length
    ) {
      if (!confirm(__("Do you want to save without any category?", "tutor"))) {
        e.preventDefault();
      }
    }
  });

  /**
   * Find user/student from select2
   * @since v.1.4.0
   */
  $("#select2_search_user_ajax").select2({
    allowClear: true,

    minimumInputLength: 1,
    placeholder: search_student_placeholder,
    language: {
      inputTooShort: function() {
        return __("Please add 1 or more character", "tutor");
      },
    },
    escapeMarkup: function(m) {
      return m;
    },
    ajax: {
      url: window._tutorobject.ajaxurl,
      type: "POST",
      dataType: "json",
      delay: 1000,
      data: function(params) {
        return {
          term: params.term,
          action: "tutor_json_search_students",
        };
      },
      processResults: function(data) {
        var terms = [];
        if (data) {
          $.each(data, function(id, text) {
            terms.push({
              id: id,
              text: text,
            });
          });
        }
        return {
          results: terms,
        };
      },
      cache: true,
    },
  });

  /**
   * Confirm Alert for deleting enrollments data
   *
   * @since v.1.4.0
   */
  $(document).on("click", "table.enrolments .delete a", function(e) {
    e.preventDefault();

    var url = $(this).attr("href");
    var popup;

    var data = {
      title: __("Delete this enrolment", "tutor"),
      description: __(
        "All of the course data like quiz attempts, assignment, lesson <br/>progress will be deleted if you delete this student's enrollment.",
        "tutor"
      ),
      buttons: {
        reset: {
          title: __("Cancel", "tutor"),
          class: "secondary",

          callback: function() {
            popup.remove();
          },
        },
        keep: {
          title: __("Yes, Delete This", "tutor"),
          class: "primary",
          callback: function() {
            window.location.replace(url);
          },
        },
      },
    };

    popup = new window.tutor_popup($, "icon-trash", 40).popup(data);
  });

  /**
   * Show hide is course public checkbox (backend dashboard editor)
   *
   * @since  v.1.7.2
   */
  var price_type = $('#tutor-attach-product [name="tutor_course_price_type"]');
  if (price_type.length == 0) {
    $("#_tutor_is_course_public_meta_checkbox").show();
  } else {
    price_type
      .change(function() {
        if ($(this).prop("checked")) {
          var method = $(this).val() == "paid" ? "hide" : "show";
          $("#_tutor_is_course_public_meta_checkbox")[method]();
        }
      })
      .trigger("change");
  }

  /**
   * Focus selected instructor layout in setting page
   *
   * @since  v.1.7.5
   */
  $(document).on("click", ".instructor-layout-template", function() {
    $(".instructor-layout-template").removeClass("selected-template");
    $(this).addClass("selected-template");
  });

  /**
   * Programmatically open preview link. For some reason it's not working normally.
   *
   * @since  v.1.7.9
   */
  $("#preview-action a.preview").click(function(e) {
    var href = $(this).attr("href");

    if (href) {
      e.preventDefault();
      window.open(href, "_blank");
    }
  });
});

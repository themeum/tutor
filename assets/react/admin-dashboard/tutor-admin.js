import "./segments/lib";
import "./segments/navigation";
import "./segments/image-preview";
import "./segments/options";
import "./segments/import-export";
import "./segments/addonlist";
import "./segments/color-preset";
import "./segments/reset";
import "./addons-list/addons-list-main";
import "./segments/filter";
import "./segments/withdraw";
import ajaxHandler from './segments/filter';
import "./segments/editor_full";
import '../front/_select_dd_search';

const toggleChange = document.querySelectorAll(".tutor-form-toggle-input");
toggleChange.forEach((element) => {
  element.addEventListener("change", (e) => {
    let check_value = element.previousElementSibling;
    if (check_value) {
      check_value.value == "on"
        ? (check_value.value = "off")
        : (check_value.value = "on");
    }
  });
});

jQuery(document).ready(function ($) {
  "use strict";

  const { __, _x, _n, _nx } = wp.i18n;
  const search_student_placeholder = __("Search students", "tutor");
  /**i
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
   * /
  $(".tutor-option-nav-tabs li a").click(function (e) {
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

  /**
   * End Withdraw nav tabs
   */

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

  $(document).on("click", ".tutor-option-media-upload-btn", function (e) {
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
    frame.on("select", function () {
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
  $(document).on("click", ".tutor-media-option-trash-btn", function (e) {
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

  // $(document).on("change", ".tutor-form-toggle-input", function(e) {
  //   var $that = $(this);

  //   var isEnable = $that.prop("checked") ? 1 : 0;
  //   var addonFieldName = $that.attr("name");

  //   $.ajax({
  //     url: window._tutorobject.ajaxurl,
  //     type: "POST",
  //     data: {
  //       isEnable: isEnable,
  //       addonFieldName: addonFieldName,
  //       action: "addon_enable_disable",
  //     },
  //     success: function(data) {
  //       if (data.success) {
  //         //Success
  //       }
  //     },
  //   });
  // });

  /**
   * Add instructor
   * @since v.1.0.3
   */
  $(document).on("submit", "#tutor-new-instructor-form", function (e) {
    e.preventDefault();
    var $that = $(this);
    var formData = $that.serializeObject();
    var loadingButton = $("#tutor-new-instructor-form .tutor-btn-loading");
    var prevText = loadingButton.html();
    var responseContainer = $("#tutor-new-instructor-form-response");
    formData.action = "tutor_add_instructor";
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: "POST",
      data: formData,
      beforeSend: function () {
        responseContainer.html('');
        loadingButton.html(`<div class="ball"></div>
        <div class="ball"></div>
        <div class="ball"></div>
        <div class="ball"></div>`);
      },
      success: function success(data) {
        if (!data.success) {
          if (data.data.errors.errors) {
            for (let v of Object.values(data.data.errors.errors)) {
              responseContainer.append(`<div class='tutor-bs-col'><li class='tutor-alert tutor-alert-warning'>${v}</li></div>`);
            }
          } else {
            for (let v of Object.values(data.data.errors)) {
              responseContainer.append(`<div class='tutor-bs-col'><li class='tutor-alert tutor-alert-warning'>${v}</li></div>`);
            }
          }

        } else {
          $('#tutor-new-instructor-form').trigger("reset");
          tutor_toast(__("Success", "tutor"), __("New Instructor Added", "tutor"), "success");
          location.reload();
        }
      },
      complete: function () {
        loadingButton.html(prevText);
      }
    });
  });

  /**
   * Instructor block unblock action
   * @since v.1.5.3
   */
  $(document).on("click", "a.instructor-action", async function (e) {
    e.preventDefault();

    const $that = $(this);
    const action = $that.attr("data-action");
    const instructorId = $that.attr("data-instructor-id");
    const loadingButton = e.target;
    const prevHtml = loadingButton.innerHTML;
    loadingButton.innerHTML = '';
    loadingButton.classList.add('tutor-updating-message');

    // prepare form data
    const formData = new FormData();
    formData.set('action', 'instructor_approval_action');
    formData.set('action_name', action);
    formData.set('instructor_id', instructorId);
    formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);

    try {
      const post = await ajaxHandler(formData);
      const response = await post.json();
      if (loadingButton.classList.contains('tutor-updating-message')) {
        loadingButton.classList.remove('tutor-updating-message');
        loadingButton.innerHTML = action.charAt(0).toUpperCase() + action.slice(1);;
      }

      if (post.ok && response.success) {
        let message = '';
        if (action == 'approve') {
          message = 'Instructor approved!';
        }
        if (action == 'blocked') {
          message = 'Instructor blocked!';
        }
        /**
         * If it is instructor modal for approve or blocked
         * hide modal then show toast then reload
         *
         * @since v2.0.0
         */
        const instructorModal = document.querySelector('.tutor-modal-ins-approval');
        if (instructorModal) {
          if (instructorModal.classList.contains('tutor-is-active')) {
            instructorModal.classList.remove('tutor-is-active');
          }
          tutor_toast(__("Success", "tutor"), __(message, 'tutor'), "success");
          location.href = `${window._tutorobject.home_url}/wp-admin/admin.php?page=tutor-instructors`;
        } else {
          tutor_toast(__("Success", "tutor"), __(message, 'tutor'), "success");
          location.reload();
        }
      } else {
        tutor_toast(__("Failed", "tutor"), __('Something went wrong!', 'tutor'), "error");
      }
    } catch (error) {
      loadingButton.innerHTML = prevHtml;
      tutor_toast(__("Operation failed", "tutor"), error, "error");
    }
  });

  /**
   * If click on close instructor approve or modal then redirect to main URL
   * if not redirect then it will not work with pagination.
   */
  const instructorModal =  document.querySelector('.tutor-modal-ins-approval .tutor-icon-56.ttr-line-cross-line');
  if (instructorModal) {
    instructorModal.addEventListener('click', function(){
      console.log('ckk')
      location.href = `${window._tutorobject.home_url}/wp-admin/admin.php?page=tutor-instructors`;
    })
  }

  /**
   * On form submit block | approve instructor
   *
   * @since v.2.0.0
   */
  // if (instructorActionForm) {
  //   instructorActionForm.onsubmit = async (e) => {
  //     e.preventDefault();
  //     const formData = new FormData(instructorActionForm);
  //     const loadingButton = instructorActionForm.querySelector('#tutor-instructor-confirm-btn.tutor-btn-loading');
  //     const prevHtml = loadingButton.innerHTML;
  //     loadingButton.innerHTML = `<div class="ball"></div>
  //     <div class="ball"></div>
  //     <div class="ball"></div>
  //     <div class="ball"></div>`;
  //     try {
  //       const post = await ajaxHandler(formData);
  //       const response = await post.json();
  //       loadingButton.innerHTML = prevHtml;
  //       if (post.ok && response.success) {
  //         location.reload();
  //       } else {
  //         tutor_toast(__("Failed", "tutor"), __('Something went wrong!', 'tutor'), "error");
  //       }
  //     } catch (error) {
  //       loadingButton.innerHTML = prevHtml;
  //       tutor_toast(__("Operation failed", "tutor"), error, "error");
  //     }
  //   }
  // }

  /**
   * Password Reveal
   */
  $(document).on('click', ".tutor-password-reveal", function (e) {
    //toggle icon
    $(this).toggleClass('ttr-eye-filled ttr-eye-fill-filled');
    //toggle attr
    $(this).next().attr('type', function (index, attr) {
      return attr == 'password' ? 'text' : 'password';
    });
  });

  /**
   * Used for backend profile photo upload.
   */

  //tutor_video_poster_upload_btn
  $(document).on("click", ".tutor_video_poster_upload_btn", function (event) {
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
    frame.on("select", function () {
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

  $(document).on("change", "#tutor_pmpro_membership_model_select", function (e) {
    e.preventDefault();

    var $that = $(this);

    if ($that.val() === "category_wise_membership") {
      $(".membership_course_categories").show();
    } else {
      $(".membership_course_categories").hide();
    }
  });

  $(document).on("change", "#tutor_pmpro_membership_model_select", function (e) {
    e.preventDefault();

    var $that = $(this);

    if ($that.val() === "category_wise_membership") {
      $(".membership_course_categories").show();
    } else {
      $(".membership_course_categories").hide();
    }
  });

  // Require category selection
  $(document).on("submit", ".pmpro_admin form", function (e) {
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
      inputTooShort: function () {
        return __("Please add 1 or more character", "tutor");
      },
    },
    escapeMarkup: function (m) {
      return m;
    },
    ajax: {
      url: window._tutorobject.ajaxurl,
      type: "POST",
      dataType: "json",
      delay: 1000,
      data: function (params) {
        return {
          term: params.term,
          action: "tutor_json_search_students",
        };
      },
      processResults: function (data) {
        var terms = [];
        if (data) {
          $.each(data, function (id, text) {
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
  $(document).on("click", "table.enrolments .delete a", function (e) {
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
          class: "tutor-btn tutor-is-outline tutor-is-default",

          callback: function () {
            popup.remove();
          },
        },
        keep: {
          title: __("Yes, Delete This", "tutor"),
          class: "tutor-btn",
          callback: function () {
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
      .change(function () {
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
  $(document).on("click", ".instructor-layout-template", function () {
    $(".instructor-layout-template").removeClass("selected-template");
    $(this).addClass("selected-template");
  });

  /**
   * Programmatically open preview link. For some reason it's not working normally.
   *
   * @since  v.1.7.9
   */
  $("#preview-action a.preview").click(function (e) {
    var href = $(this).attr("href");

    if (href) {
      e.preventDefault();
      window.open(href, "_blank");
    }
  });

  //add checkbox class for style
  var tutorCheckbox = $(".tutor-ui-table .tutor-form-check-input");
  if (tutorCheckbox) {
    tutorCheckbox.parent().addClass('tutor-option-field-row');
  }
});

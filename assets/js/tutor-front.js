/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/react/front/dashboard.js":
/*!*****************************************!*\
  !*** ./assets/react/front/dashboard.js ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _dashboard_mobile_nav__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./dashboard/mobile-nav */ "./assets/react/front/dashboard/mobile-nav.js");
/* harmony import */ var _dashboard_mobile_nav__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_dashboard_mobile_nav__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _dashboard_withdrawal__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./dashboard/withdrawal */ "./assets/react/front/dashboard/withdrawal.js");
/* harmony import */ var _dashboard_withdrawal__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_dashboard_withdrawal__WEBPACK_IMPORTED_MODULE_1__);



/***/ }),

/***/ "./assets/react/front/dashboard/mobile-nav.js":
/*!****************************************************!*\
  !*** ./assets/react/front/dashboard/mobile-nav.js ***!
  \****************************************************/
/***/ (() => {

document.addEventListener("DOMContentLoaded", function () {
  // Toggle menu in mobile view
  $(".tutor-dashboard .tutor-dashboard-menu-toggler").click(function () {
    var el = $(".tutor-dashboard-left-menu");
    el.closest(".tutor-dashboard").toggleClass("is-sidebar-expanded");

    if (el.css("display") !== "none") {
      el.get(0).scrollIntoView({
        block: "start"
      });
    }
  });
});

/***/ }),

/***/ "./assets/react/front/dashboard/withdrawal.js":
/*!****************************************************!*\
  !*** ./assets/react/front/dashboard/withdrawal.js ***!
  \****************************************************/
/***/ (() => {

document.addEventListener('DOMContentLoaded', function () {
  var $ = window.jQuery;
  /**
   * Withdraw Form Tab/Toggle
   *
   * @since v.1.1.2
   */

  $('.tutor-dashboard-setting-withdraw input[name="tutor_selected_withdraw_method"]').on('change', function (e) {
    var $that = $(this);
    var form = $that.closest('form');
    form.find('.withdraw-method-form').hide();
    form.find('.withdraw-method-form').hide().filter('[data-withdraw-form="' + $that.val() + '"]').show();
  });
});

/***/ }),

/***/ "./assets/react/lib/common.js":
/*!************************************!*\
  !*** ./assets/react/lib/common.js ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _tutor__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./tutor */ "./assets/react/lib/tutor.js");
/* harmony import */ var _media_chooser__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./media-chooser */ "./assets/react/lib/media-chooser.js");
/* harmony import */ var _media_chooser__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_media_chooser__WEBPACK_IMPORTED_MODULE_1__);



/***/ }),

/***/ "./assets/react/lib/media-chooser.js":
/*!*******************************************!*\
  !*** ./assets/react/lib/media-chooser.js ***!
  \*******************************************/
/***/ (() => {

window.jQuery(document).ready(function ($) {
  var __ = window.wp.i18n.__;
  /**
   * Lesson upload thumbnail
   */

  $(document).on('click', '.tutor-thumbnail-uploader .tutor-thumbnail-upload-button', function (event) {
    event.preventDefault();
    var wrapper = $(this).closest('.tutor-thumbnail-uploader');
    var frame;

    if (frame) {
      frame.open();
      return;
    }

    frame = wp.media({
      title: wrapper.data('media-heading'),
      button: {
        text: wrapper.data('button-text')
      },
      multiple: false
    });
    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      wrapper.find('img').attr('src', attachment.url);
      wrapper.find('input[type="hidden"].tutor-tumbnail-id-input').val(attachment.id);
      wrapper.find('.delete-btn').show();
    });
    frame.open();
  });
  /**
   * Lesson Feature Image Delete
   * @since v.1.5.6
   */

  $(document).on('click', '.tutor-thumbnail-uploader .delete-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var wrapper = $that.closest('.tutor-thumbnail-uploader');
    wrapper.find('input[type="hidden"].tutor-tumbnail-id-input').val('');
    wrapper.find('img').attr('src', '');
    $that.hide();
  });
});

/***/ }),

/***/ "./assets/react/lib/tutor.js":
/*!***********************************!*\
  !*** ./assets/react/lib/tutor.js ***!
  \***********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _v2_library_src_js_main__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../v2-library/_src/js/main */ "./v2-library/_src/js/main.js");
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }



window.tutor_get_nonce_data = function (send_key_value) {
  var nonce_data = window._tutorobject || {};
  var nonce_key = nonce_data.nonce_key || '';
  var nonce_value = nonce_data[nonce_key] || '';

  if (send_key_value) {
    return {
      key: nonce_key,
      value: nonce_value
    };
  }

  return _defineProperty({}, nonce_key, nonce_value);
};

window.tutor_popup = function ($, icon, padding) {
  var $this = this;
  var element;

  this.popup_wrapper = function (wrapper_tag) {
    var img_tag = icon === '' ? '' : '<img class="tutor-pop-icon" src="' + window._tutorobject.tutor_url + 'assets/images/' + icon + '.svg"/>';
    return '<' + wrapper_tag + ' class="tutor-component-popup-container">\
            <div class="tutor-component-popup-' + padding + '">\
                <div class="tutor-component-content-container">' + img_tag + '</div>\
                <div class="tutor-component-button-container"></div>\
            </div>\
        </' + wrapper_tag + '>';
  };

  this.popup = function (data) {
    var title = data.title ? '<h3>' + data.title + '</h3>' : '';
    var description = data.description ? '<p>' + data.description + '</p>' : '';
    var buttons = Object.keys(data.buttons || {}).map(function (key) {
      var button = data.buttons[key];
      var button_id = button.id ? 'tutor-popup-' + button.id : '';
      return $('<button id="' + button_id + '" class="tutor-button tutor-button-' + button["class"] + '">' + button.title + '</button>').click(button.callback);
    });
    element = $($this.popup_wrapper(data.wrapper_tag || 'div'));
    var content_wrapper = element.find('.tutor-component-content-container');
    content_wrapper.append(title);
    data.after_title ? content_wrapper.append(data.after_title) : 0;
    content_wrapper.append(description);
    data.after_description ? content_wrapper.append(data.after_description) : 0; // Assign close event on click black overlay

    element.click(function () {
      $(this).remove();
    }).children().click(function (e) {
      e.stopPropagation();
    }); // Append action button

    for (var i = 0; i < buttons.length; i++) {
      element.find('.tutor-component-button-container').append(buttons[i]);
    }

    $('body').append(element);
    return element;
  };

  return {
    popup: this.popup
  };
};

window.tutorDotLoader = function (loaderType) {
  return "    \n    <div class=\"tutor-dot-loader ".concat(loaderType ? loaderType : '', "\">\n        <span class=\"dot dot-1\"></span>\n        <span class=\"dot dot-2\"></span>\n        <span class=\"dot dot-3\"></span>\n        <span class=\"dot dot-4\"></span>\n    </div>");
};

window.tutor_date_picker = function () {
  if (jQuery.datepicker) {
    var format = _tutorobject.wp_date_format;

    if (!format) {
      format = "yy-mm-dd";
    }

    $(".tutor_date_picker").datepicker({
      "dateFormat": format
    });
  }
};

jQuery(document).ready(function ($) {
  'use strict';

  var _wp$i18n = wp.i18n,
      __ = _wp$i18n.__,
      _x = _wp$i18n._x,
      _n = _wp$i18n._n,
      _nx = _wp$i18n._nx;
  /**
   * Global date_picker selector 
   * 
   * @since 1.9.7
   */

  function load_date_picker() {
    if (jQuery.datepicker) {
      var format = _tutorobject.wp_date_format;

      if (!format) {
        format = "yy-mm-dd";
      }

      $(".tutor_date_picker").datepicker({
        "dateFormat": format
      });
    }
    /** Disable typing on datePicker field */


    $(document).on('keydown', '.hasDatepicker, .tutor_date_picker', function (e) {
      if (e.keyCode !== 8) {
        e.preventDefault();
      }
    });
  }

  ;
  load_date_picker();
  /**
   * Video source tabs
   */

  if (jQuery().select2) {
    $('.videosource_select2').select2({
      width: "100%",
      templateSelection: iformat,
      templateResult: iformat,
      allowHtml: true
    });
  } //videosource_select2


  function iformat(icon) {
    var originalOption = icon.element;
    return $('<span><i class="tutor-icon-' + $(originalOption).data('icon') + '"></i> ' + icon.text + '</span>');
  }
  /**
   * Course Builder
   *
   * @since v.1.3.4
   */


  $(document).on('click', '.tutor-course-thumbnail-upload-btn', function (event) {
    event.preventDefault();
    var $that = $(this);
    var frame;

    if (frame) {
      frame.open();
      return;
    }

    frame = wp.media({
      title: __('Select or Upload Media Of Your Chosen Persuasion', 'tutor'),
      button: {
        text: __('Use this media', 'tutor')
      },
      multiple: false
    });
    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      $that.closest('.tutor-thumbnail-wrap').find('.thumbnail-img').attr('src', attachment.url);
      $that.closest('.tutor-thumbnail-wrap').find('input').val(attachment.id);
      $('.tutor-course-thumbnail-delete-btn').show();
    });
    frame.open();
  }); //Delete Thumbnail

  $(document).on('click', '.tutor-course-thumbnail-delete-btn', function (event) {
    event.preventDefault();
    var $that = $(this);
    var placeholder_src = $that.closest('.tutor-thumbnail-wrap').find('.thumbnail-img').attr('data-placeholder-src');
    $that.closest('.tutor-thumbnail-wrap').find('.thumbnail-img').attr('src', placeholder_src);
    $that.closest('.tutor-thumbnail-wrap').find('input').val('');
    $('.tutor-course-thumbnail-delete-btn').hide();
  });
  $(document).on('change keyup', '.course-edit-topic-title-input', function (e) {
    e.preventDefault();
    $(this).closest('.tutor-topics-top').find('.topic-inner-title').html($(this).val());
  });
  $(document).on('click', '.tutor-topics-edit-button', function (e) {
    e.preventDefault();
    var $button = $(this);
    var topics_id = $button.closest('.tutor-topics-wrap').find('[name="topic_id"]').val();
    ;
    var topic_title = $button.closest('.tutor-topics-wrap').find('[name="topic_title"]').val();
    var topic_summery = $button.closest('.tutor-topics-wrap').find('[name="topic_summery"]').val();
    var data = {
      topic_title: topic_title,
      topic_summery: topic_summery,
      topic_id: topics_id,
      action: 'tutor_update_topic'
    };
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: data,
      beforeSend: function beforeSend() {
        $button.addClass('tutor-updating-message');
      },
      success: function success(data) {
        if (data.success) {
          $button.closest('.tutor-topics-wrap').find('span.topic-inner-title').text(topic_title);
          $button.closest('.tutor-modal').removeClass('tutor-is-active');
        }
      },
      complete: function complete() {
        $button.removeClass('tutor-updating-message');
      }
    });
  });
  /**
   * Delete Lesson from course builder
   */

  $(document).on('click', '.tutor-delete-lesson-btn', function (e) {
    e.preventDefault();

    if (!confirm(__('Are you sure?', 'tutor'))) {
      return;
    }

    var $that = $(this);
    var lesson_id = $that.attr('data-lesson-id');
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        lesson_id: lesson_id,
        action: 'tutor_delete_lesson_by_id'
      },
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        if (data.success) {
          $that.closest('.course-content-item').remove();
        }
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  /**
   * Quiz Modal
   */

  $(document).on('click', '.modal-close-btn', function (e) {
    e.preventDefault();
    $('.tutor-modal-wrap').removeClass('show');
  });
  $(document).on('keyup', function (e) {
    if (e.keyCode === 27) {
      $('.tutor-modal-wrap').removeClass('show');
    }
  });
  /**
   * Quiz Builder Modal Tabs
   */

  $(document).on('click', '.tutor-quiz-modal-tab-item', function (e) {
    e.preventDefault();
    var $that = $(this);
    var $quizTitle = $('[name="quiz_title"]');
    var quiz_title = $quizTitle.val();

    if (!quiz_title) {
      $quizTitle.closest('.tutor-quiz-builder-form-row').find('.quiz_form_msg').html('<p class="quiz-form-warning">Please save the quiz' + ' first</p>');
      return;
    } else {
      $quizTitle.closest('.tutor-quiz-builder-form-row').find('.quiz_form_msg').html('');
    }

    var tabSelector = $that.attr('href');
    $('.quiz-builder-tab-container').hide();
    $(tabSelector).show();
    $('a.tutor-quiz-modal-tab-item').removeClass('active');
    $that.addClass('active');
  });
  $(document).on('click', '.quiz-modal-tab-navigation-btn.quiz-modal-btn-cancel', function (e) {
    e.preventDefault();
    $('.tutor-modal-wrap').removeClass('show');
  });
  /**
   * Get question answers option edit form
   *
   * @since v.1.0.0
   */

  $(document).on('click', '.tutor-quiz-answer-edit a', function (e) {
    e.preventDefault();
    var $that = $(this);
    var answer_id = $that.closest('.tutor-quiz-answer-wrap').attr('data-answer-id');
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        answer_id: answer_id,
        action: 'tutor_quiz_edit_question_answer'
      },
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        $('#tutor_quiz_question_answer_form').html(data.data.output);
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  /**
   * Saving question answers options
   * Student should select the right answer at quiz attempts
   *
   * @since v.1.0.0
   */

  $(document).on('click', '#quiz-answer-save-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var $formInput = $('#tutor-quiz-question-wrapper :input').serializeObject();
    $formInput.action = 'tutor_save_quiz_answer_options';
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: $formInput,
      beforeSend: function beforeSend() {
        $('#quiz_validation_msg_wrap').html("");
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        $('#tutor_quiz_question_answers').trigger('refresh');
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  /**
   * Updating Answer
   *
   * @since v.1.0.0
   */

  $(document).on('click', '#quiz-answer-edit-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var $formInput = $('#tutor-quiz-question-wrapper :input').serializeObject();
    $formInput.action = 'tutor_update_quiz_answer_options';
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: $formInput,
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        $('#tutor_quiz_question_answers').trigger('refresh');
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  $(document).on('change', '.tutor-quiz-answers-mark-correct-wrap input', function (e) {
    e.preventDefault();
    var $that = $(this);
    var answer_id = $that.val();
    var inputValue = 1;

    if (!$that.prop('checked')) {
      inputValue = 0;
    }

    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        answer_id: answer_id,
        inputValue: inputValue,
        action: 'tutor_mark_answer_as_correct'
      }
    });
  });
  /**
   * Delete answer for a question in quiz builder
   *
   * @since v.1.0.0
   */

  $(document).on('click', '.tutor-quiz-answer-trash-wrap a.answer-trash-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var answer_id = $that.attr('data-answer-id');
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        answer_id: answer_id,
        action: 'tutor_quiz_builder_delete_answer'
      },
      beforeSend: function beforeSend() {
        $that.closest('.tutor-quiz-answer-wrap').remove();
      }
    });
  });
  /**
   * Delete Quiz
   * @since v.1.0.0
   */

  $(document).on('click', '.tutor-delete-quiz-btn', function (e) {
    e.preventDefault();

    if (!confirm(__('Are you sure?', 'tutor'))) {
      return;
    }

    var $that = $(this);
    var quiz_id = $that.attr('data-quiz-id');
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        quiz_id: quiz_id,
        action: 'tutor_delete_quiz_by_id'
      },
      beforeSend: function beforeSend() {
        $that.closest('.course-content-item').remove();
      }
    });
  });
  $(document).on('click', '.tutor-media-upload-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var frame;

    if (frame) {
      frame.open();
      return;
    }

    frame = wp.media({
      title: __('Select or Upload Media Of Your Chosen Persuasion', 'tutor'),
      button: {
        text: __('Use this media', 'tutor')
      },
      multiple: false
    });
    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      $that.html('<img src="' + attachment.url + '" alt="" />');
      $that.closest('.tutor-media-upload-wrap').find('input').val(attachment.id);
    });
    frame.open();
  });
  $(document).on('click', '.tutor-media-upload-trash', function (e) {
    e.preventDefault();
    var $that = $(this);
    $that.closest('.tutor-media-upload-wrap').find('.tutor-media-upload-btn').html('<i class="tutor-icon-image1"></i>');
    $that.closest('.tutor-media-upload-wrap').find('input').val('');
  });
  /**
   * Delay Function
   */

  var tutor_delay = function () {
    var timer = 0;
    return function (callback, ms) {
      clearTimeout(timer);
      timer = setTimeout(callback, ms);
    };
  }();
  /**
   * Add instructor modal
   */


  $(document).on('click', '.tutor-add-instructor-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var course_id = $('#post_ID').val();
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        course_id: course_id,
        action: 'tutor_load_instructors_modal'
      },
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        if (data.success) {
          $('.tutor-instructors-modal-wrap .modal-container').html(data.data.output);
          $('.tutor-instructors-modal-wrap').addClass('show');
        }
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  $(document).on('change keyup', '.tutor-instructors-modal-wrap .tutor-modal-search-input', function (e) {
    e.preventDefault();
    var $that = $(this);
    var $modal = $('.tutor-modal-wrap');
    tutor_delay(function () {
      var search_terms = $that.val();
      var course_id = $('#post_ID').val();
      $.ajax({
        url: window._tutorobject.ajaxurl,
        type: 'POST',
        data: {
          course_id: course_id,
          search_terms: search_terms,
          action: 'tutor_load_instructors_modal'
        },
        beforeSend: function beforeSend() {
          $modal.addClass('loading');
        },
        success: function success(data) {
          if (data.success) {
            $('.tutor-instructors-modal-wrap .modal-container').html(data.data.output);
            $('.tutor-instructors-modal-wrap').addClass('show');
          }
        },
        complete: function complete() {
          $modal.removeClass('loading');
        }
      });
    }, 1000);
  });
  $(document).on('click', '.add_instructor_to_course_btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var $modal = $('.tutor-modal-wrap');
    var course_id = $('#post_ID').val();
    var data = $modal.find('input').serializeObject();
    data.course_id = course_id;
    data.action = 'tutor_add_instructors_to_course';
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: data,
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        if (data.success) {
          $('.tutor-course-available-instructors').html(data.data.output);
          $('.tutor-modal-wrap').removeClass('show');
        }
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  $(document).on('click', '.tutor-instructor-delete-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var course_id = $('#post_ID').val();
    var instructor_id = $that.closest('.added-instructor-item').attr('data-instructor-id');
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        course_id: course_id,
        instructor_id: instructor_id,
        action: 'detach_instructor_from_course'
      },
      success: function success(data) {
        if (data.success) {
          $that.closest('.added-instructor-item').remove();
        }
      }
    });
  });
  $(document).on('click', '.settings-tabs-navs li', function (e) {
    e.preventDefault();
    var $that = $(this);
    var data_target = $that.find('a').attr('data-target');
    var url = $that.find('a').attr('href');
    $that.addClass('active').siblings('li.active').removeClass('active');
    $('.settings-tab-wrap').removeClass('active').hide();
    $(data_target).addClass('active').show();
    window.history.pushState({}, '', url);
  });
  /**
   * Re init required
   * Modal Loaded...
   */

  $(document).on('lesson_modal_loaded quiz_modal_loaded assignment_modal_loaded', function (e, obj) {
    if (jQuery().select2) {
      $('.select2_multiselect').select2({
        dropdownCssClass: 'increasezindex'
      });
    }

    load_date_picker();
  });
  /**
   * Tutor number validation
   *
   * @since v.1.6.3
   */

  $(document).on('keyup change', '.tutor-number-validation', function (e) {
    var input = $(this);
    var val = parseInt(input.val());
    var min = parseInt(input.attr('data-min'));
    var max = parseInt(input.attr('data-max'));

    if (val < min) {
      input.val(min);
    } else if (val > max) {
      input.val(max);
    }
  });
  /*
  * @since v.1.6.4
  * Quiz Attempts Instructor Feedback 
  */

  $(document).on('click', '.tutor-instructor-feedback', function (e) {
    e.preventDefault();
    var $that = $(this);
    $.ajax({
      url: window.ajaxurl || _tutorobject.ajaxurl,
      type: 'POST',
      data: {
        attempts_id: $that.data('attemptid'),
        feedback: $('.tutor-instructor-feedback-content').val(),
        action: 'tutor_instructor_feedback'
      },
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        if (data.success) {
          $that.closest('.course-content-item').remove();
          tutor_toast(__('Success', 'tutor'), $that.data('toast_success_message'), 'success');
        }
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  /**
   * Since 1.7.9
   * Announcements scripts
   */

  var add_new_button = $(".tutor-announcement-add-new");
  var update_button = $(".tutor-announcement-edit");
  var delete_button = $(".tutor-announcement-delete");
  var details_button = $(".tutor-announcement-details");
  var close_button = $(".tutor-announcement-close-btn");
  var create_modal = $(".tutor-accouncement-create-modal");
  var update_modal = $(".tutor-accouncement-update-modal");
  var details_modal = $(".tutor-accouncement-details-modal"); //open create modal

  $(add_new_button).click(function () {
    create_modal.addClass("show");
    $("#tutor-annoucement-backend-create-modal").addClass('show');
  });
  $(details_button).click(function () {
    var announcement_date = $(this).attr('announcement-date');
    var announcement_id = $(this).attr('announcement-id');
    var course_id = $(this).attr('course-id');
    var course_name = $(this).attr('course-name');
    var announcement_title = $(this).attr('announcement-title');
    var announcement_summary = $(this).attr('announcement-summary');
    $(".tutor-announcement-detail-content").html("<h3>".concat(announcement_title, "</h3><p>").concat(announcement_summary, "</p>"));
    $(".tutor-announcement-detail-course-info p").html("".concat(course_name));
    $(".tutor-announcement-detail-date-info p").html("".concat(announcement_date)); //set attr on edit button

    $("#tutor-announcement-edit-from-detail").attr('announcement-id', announcement_id);
    $("#tutor-announcement-edit-from-detail").attr('course-id', course_id);
    $("#tutor-announcement-edit-from-detail").attr('announcement-title', announcement_title);
    $("#tutor-announcement-edit-from-detail").attr('announcement-summary', announcement_summary);
    $("#tutor-announcement-delete-from-detail").attr('announcement-id', announcement_id);
    details_modal.addClass("show");
  }); //open update modal

  $(update_button).click(function () {
    if (details_modal) {
      details_modal.removeClass('show');
    }

    var announcement_id = $(this).attr('announcement-id');
    var course_id = $(this).attr('course-id');
    var announcement_title = $(this).attr('announcement-title');
    var announcement_summary = $(this).attr('announcement-summary');
    $("#tutor-announcement-course-id").val(course_id);
    $("#announcement_id").val(announcement_id);
    $("#tutor-announcement-title").val(announcement_title);
    $("#tutor-announcement-summary").val(announcement_summary);
    update_modal.addClass("show");
  }); //close create and update modal

  $(close_button).click(function () {
    create_modal.removeClass("show");
    update_modal.removeClass("show");
    details_modal.removeClass("show");
    $("#tutor-annoucement-backend-create-modal").removeClass('show');
  }); //create announcement

  $(".tutor-announcements-form").on('submit', function (e) {
    e.preventDefault();
    var $btn = $(this).find('button[type="submit"]');
    var formData = $(".tutor-announcements-form").serialize() + '&action=tutor_announcement_create' + '&action_type=create';
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: formData,
      beforeSend: function beforeSend() {
        $btn.addClass('tutor-updating-message');
      },
      success: function success(data) {
        $(".tutor-alert").remove();

        if (data.status == "success") {
          location.reload();
        }

        if (data.status == "validation_error") {
          $(".tutor-announcements-create-alert").append("<div class=\"tutor-alert alert-warning\"></div>");

          for (var _i = 0, _Object$entries = Object.entries(data.message); _i < _Object$entries.length; _i++) {
            var _Object$entries$_i = _slicedToArray(_Object$entries[_i], 2),
                key = _Object$entries$_i[0],
                value = _Object$entries$_i[1];

            $(".tutor-announcements-create-alert .tutor-alert").append("<li>".concat(value, "</li>"));
          }
        }

        if (data.status == "fail") {
          $(".tutor-announcements-create-alert").html("<li>".concat(data.message, "</li>"));
        }
      },
      error: function error(data) {
        console.log(data);
      }
    });
  }); //update announcement

  $(".tutor-announcements-update-form").on('submit', function (e) {
    e.preventDefault();
    var $btn = $(this).find('button[type="submit"]');
    var formData = $(".tutor-announcements-update-form").serialize() + '&action=tutor_announcement_create' + '&action_type=update';
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: formData,
      beforeSend: function beforeSend() {
        $btn.addClass('tutor-updating-message');
      },
      success: function success(data) {
        $(".tutor-alert").remove();

        if (data.status == "success") {
          location.reload();
        }

        if (data.status == "validation_error") {
          $(".tutor-announcements-update-alert").append("<div class=\"tutor-alert alert-warning\"></div>");

          for (var _i2 = 0, _Object$entries2 = Object.entries(data.message); _i2 < _Object$entries2.length; _i2++) {
            var _Object$entries2$_i = _slicedToArray(_Object$entries2[_i2], 2),
                key = _Object$entries2$_i[0],
                value = _Object$entries2$_i[1];

            $(".tutor-announcements-update-alert > .tutor-alert").append("<li>".concat(value, "</li>"));
          }
        }

        if (data.status == "fail") {
          $(".tutor-announcements-create-alert").html("<li>".concat(data.message, "</li>"));
        }
      },
      error: function error() {}
    });
  });
  $(delete_button).click(function () {
    var announcement_id = $(this).attr('announcement-id');
    var whichtr = $("#tutor-announcement-tr-" + announcement_id);

    if (confirm("Do you want to delete?")) {
      $.ajax({
        url: window._tutorobject.ajaxurl,
        type: 'POST',
        data: {
          action: 'tutor_announcement_delete',
          announcement_id: announcement_id
        },
        beforeSend: function beforeSend() {},
        success: function success(data) {
          whichtr.remove();

          if (details_modal.length) {
            details_modal.removeClass('show');
          }

          if (data.status == "fail") {
            console.log(data.message);
          }
        },
        error: function error() {}
      });
    }
  }); //sorting 
  // if (jQuery.datepicker){
  //     $( "#tutor-announcement-datepicker" ).datepicker({"dateFormat" : 'yy-mm-dd'});
  // }

  function urlPrams(type, val) {
    var url = new URL(window.location.href);
    var search_params = url.searchParams;
    search_params.set(type, val);
    url.search = search_params.toString();
    search_params.set('paged', 1);
    url.search = search_params.toString();
    return url.toString();
  }

  $('.tutor-announcement-course-sorting').on('change', function (e) {
    window.location = urlPrams('course-id', $(this).val());
  });
  $('.tutor-announcement-order-sorting').on('change', function (e) {
    window.location = urlPrams('order', $(this).val());
  });
  $('.tutor-announcement-date-sorting').on('change', function (e) {
    window.location = urlPrams('date', $(this).val());
  });
  $('.tutor-announcement-search-sorting').on('click', function (e) {
    window.location = urlPrams('search', $(".tutor-announcement-search-field").val());
  }); //dropdown toggle

  $(document).click(function () {
    $(".tutor-dropdown").removeClass('show');
  });
  $(".tutor-dropdown").click(function (e) {
    e.stopPropagation();

    if ($('.tutor-dropdown').hasClass('show')) {
      $('.tutor-dropdown').removeClass('show');
    }

    $(this).addClass('show');
  }); //announcement end

  /**
   * @since v.1.8.6
   * SUbmit form through ajax
   */

  $('.tutor-form-submit-through-ajax').submit(function (e) {
    e.preventDefault();
    var $that = $(this);
    var url = $(this).attr('action') || window.location.href;
    var type = $(this).attr('method') || 'GET';
    var data = $(this).serializeObject();
    $that.find('button').addClass('tutor-updating-message');
    $.ajax({
      url: url,
      type: type,
      data: data,
      success: function success() {
        tutor_toast(__('Success', 'tutor'), $that.data('toast_success_message'), 'success');
      },
      complete: function complete() {
        $that.find('button').removeClass('tutor-updating-message');
      }
    });
  });
  /*
  * @since v.1.7.9
  * Send wp nonce to every ajax request
  */

  $.ajaxSetup({
    data: tutor_get_nonce_data()
  });
});

jQuery.fn.serializeObject = function () {
  var values = {};
  var array = this.serializeArray();
  jQuery.each(array, function () {
    if (values[this.name]) {
      if (!values[this.name].push) {
        values[this.name] = [values[this.name]];
      }

      values[this.name].push(this.value || '');
    } else {
      values[this.name] = this.value || '';
    }
  });
  return values;
};

window.tutor_toast = function (title, description, type) {
  var tutor_ob = window._tutorobject || {};
  var asset = (tutor_ob.tutor_url || '') + 'assets/images/';

  if (!jQuery('.tutor-toast-parent').length) {
    jQuery('body').append('<div class="tutor-toast-parent"></div>');
  }

  var icons = {
    success: asset + 'icon-check.svg',
    error: asset + 'icon-cross.svg'
  };
  var content = jQuery('\
        <div>\
            <div>\
                <img src="' + icons[type] + '"/>\
            </div>\
            <div>\
                <div>\
                    <b>' + title + '</b>\
                    <span>' + description + '</span>\
                </div>\
            </div>\
            <div>\
                <i class="tutor-toast-close tutor-icon-line-cross"></i>\
            </div>\
        </div>');
  content.find('.tutor-toast-close').click(function () {
    content.remove();
  });
  jQuery('.tutor-toast-parent').append(content);
  setTimeout(function () {
    if (content) {
      content.fadeOut('fast', function () {
        jQuery(this).remove();
      });
    }
  }, 5000);
};

/***/ }),

/***/ "./v2-library/_src/js/main.js":
/*!************************************!*\
  !*** ./v2-library/_src/js/main.js ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _tutorModal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./tutorModal */ "./v2-library/_src/js/tutorModal.js");
/* harmony import */ var _tutorModal__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_tutorModal__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _tutorThumbnailPreview__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./tutorThumbnailPreview */ "./v2-library/_src/js/tutorThumbnailPreview.js");
/* harmony import */ var _tutorThumbnailPreview__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_tutorThumbnailPreview__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _tutorPopupMenu__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./tutorPopupMenu */ "./v2-library/_src/js/tutorPopupMenu.js");
/* harmony import */ var _tutorPopupMenu__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_tutorPopupMenu__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _tutorOffcanvas__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./tutorOffcanvas */ "./v2-library/_src/js/tutorOffcanvas.js");
/* harmony import */ var _tutorOffcanvas__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_tutorOffcanvas__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _tutorNotificationTab__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./tutorNotificationTab */ "./v2-library/_src/js/tutorNotificationTab.js");
/* harmony import */ var _tutorNotificationTab__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_tutorNotificationTab__WEBPACK_IMPORTED_MODULE_4__);






/***/ }),

/***/ "./v2-library/_src/js/tutorModal.js":
/*!******************************************!*\
  !*** ./v2-library/_src/js/tutorModal.js ***!
  \******************************************/
/***/ (() => {

(function () {
  'use strict'; // modal

  tutorModal();
})();

function tutorModal() {
  document.addEventListener('click', function (e) {
    var attr = 'data-tutor-modal-target';
    var closeAttr = 'data-tutor-modal-close';
    var overlay = 'tutor-modal-overlay';

    if (e.target.hasAttribute(attr) || e.target.closest("[".concat(attr, "]"))) {
      e.preventDefault();
      var id = e.target.hasAttribute(attr) ? e.target.getAttribute(attr) : e.target.closest("[".concat(attr, "]")).getAttribute(attr);
      var modal = document.getElementById(id);

      if (modal) {
        modal.classList.add('tutor-is-active');
      }
    }

    if (e.target.hasAttribute(closeAttr) || e.target.classList.contains(overlay) || e.target.closest("[".concat(closeAttr, "]"))) {
      e.preventDefault();

      var _modal = document.querySelectorAll('.tutor-modal.tutor-is-active');

      _modal.forEach(function (m) {
        m.classList.remove('tutor-is-active');
      });
    }
  }); // open
  // const modalButton = document.querySelectorAll("[data-tutor-modal-target]");
  // modalButton.forEach(b => {
  //     const id = b.getAttribute("data-tutor-modal-target");
  //     const modal = document.getElementById(id);
  //     if (modal) {
  //         b.addEventListener("click", e => {
  //             e.preventDefault();
  //             modal.classList.add("tutor-is-active");
  //         })
  //     }
  // })
  // close
  // const close = document.querySelectorAll("[data-tutor-modal-close], .tutor-modal-overlay");
  // close.forEach(c => {
  //     c.addEventListener("click", e => {
  //         e.preventDefault();
  //         const modal = document.querySelectorAll(".tutor-modal.tutor-is-active");
  //         modal.forEach(m => {
  //             m.classList.remove("tutor-is-active");
  //         })
  //     })
  // })
}

/***/ }),

/***/ "./v2-library/_src/js/tutorNotificationTab.js":
/*!****************************************************!*\
  !*** ./v2-library/_src/js/tutorNotificationTab.js ***!
  \****************************************************/
/***/ (() => {

/**
 * Tutor Notification Tab
 */
(function tutorNotificationTab() {
  document.addEventListener('click', function (e) {
    var attr = 'data-tutor-notification-tab-target';
    var activeItems = document.querySelectorAll('.tab-header-item.is-active, .tab-body-item.is-active');

    if (e.target.hasAttribute(attr)) {
      e.preventDefault();
      var id = e.target.hasAttribute(attr) ? e.target.getAttribute(attr) : e.target.closest("[".concat(attr, "]")).getAttribute(attr);
      var tabBodyItem = document.getElementById(id);

      if (e.target.hasAttribute(attr) && tabBodyItem) {
        activeItems.forEach(function (m) {
          m.classList.remove('is-active');
        });
        e.target.classList.add('is-active');
        tabBodyItem.classList.add('is-active');
      }
    }
  });
})();

/***/ }),

/***/ "./v2-library/_src/js/tutorOffcanvas.js":
/*!**********************************************!*\
  !*** ./v2-library/_src/js/tutorOffcanvas.js ***!
  \**********************************************/
/***/ (() => {

/**
 * Tutor Off Canvas
 */
(function tutorOffCanvas() {
  document.addEventListener('click', function (e) {
    var attr = 'data-tutor-offcanvas-target';
    var closeAttr = 'data-tutor-offcanvas-close';
    var backdrop = 'tutor-offcanvas-backdrop';
    console.log(e.target); // Opening Offcanvas

    if (e.target.hasAttribute(attr)) {
      e.preventDefault();
      var id = e.target.hasAttribute(attr) ? e.target.getAttribute(attr) : e.target.closest("[".concat(attr, "]")).getAttribute(attr);
      var offcanvas = document.getElementById(id);

      if (offcanvas) {
        offcanvas.classList.add('is-active');
      }
    } // Closing Offcanvas


    if (e.target.hasAttribute(closeAttr) || e.target.classList.contains(backdrop) || e.target.closest("[".concat(closeAttr, "]"))) {
      e.preventDefault();
      var activeOffcanvas = document.querySelectorAll('.tutor-offcanvas.is-active');
      activeOffcanvas.forEach(function (m) {
        m.classList.remove('is-active');
      });
    }
  }); // Closing Offcanvas on esc key

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      var activeOffcanvas = document.querySelectorAll('.tutor-offcanvas.is-active');
      activeOffcanvas.forEach(function (m) {
        m.classList.remove('is-active');
      });
    }
  });
})();

/***/ }),

/***/ "./v2-library/_src/js/tutorPopupMenu.js":
/*!**********************************************!*\
  !*** ./v2-library/_src/js/tutorPopupMenu.js ***!
  \**********************************************/
/***/ (() => {

(function tutorPopupMenu() {
  /**
   * Popup Menu Toggle .tutor-popup-opener
   */

  /*
  const popupToggleBtns = document.querySelectorAll('.tutor-popup-opener .popup-btn');
  const popupMenus = document.querySelectorAll('.tutor-popup-opener .popup-menu');
  	 if (popupToggleBtns && popupMenus) {
  	popupToggleBtns.forEach((btn) => {
  		btn.addEventListener('click', (e) => {
  			const popupClosest = e.target.closest('.tutor-popup-opener').querySelector('.popup-menu');
  			popupClosest.classList.toggle('visible');
  				popupMenus.forEach((popupMenu) => {
  				if (popupMenu !== popupClosest) {
  					popupMenu.classList.remove('visible');
  				}
  			});
  		});
  	});
  		document.addEventListener('click', (e) => {
  		if (!e.target.matches('.tutor-popup-opener .popup-btn')) {
  			popupMenus.forEach((popupMenu) => {
  				if (popupMenu.classList.contains('visible')) {
  					popupMenu.classList.remove('visible');
  				}
  			});
  		}
  	});
  } */
  document.addEventListener('click', function (e) {
    var attr = 'data-tutor-popup-target';

    if (e.target.hasAttribute(attr)) {
      e.preventDefault();
      var id = e.target.hasAttribute(attr) ? e.target.getAttribute(attr) : e.target.closest("[".concat(attr, "]")).getAttribute(attr);
      var popupMenu = document.getElementById(id);

      if (popupMenu.classList.contains('visible')) {
        popupMenu.classList.remove('visible');
      } else {
        document.querySelectorAll('.tutor-popup-opener .popup-menu').forEach(function (popupMenu) {
          popupMenu.classList.remove('visible');
        });
        popupMenu.classList.add('visible');
      }
    } else {
      document.querySelectorAll('.tutor-popup-opener .popup-menu').forEach(function (popupMenu) {
        popupMenu.classList.remove('visible');
      });
    }
  });
})();

/***/ }),

/***/ "./v2-library/_src/js/tutorThumbnailPreview.js":
/*!*****************************************************!*\
  !*** ./v2-library/_src/js/tutorThumbnailPreview.js ***!
  \*****************************************************/
/***/ (() => {

(function thumbnailUploadPreview() {
  /**
   * Image Preview : Logo and Signature Upload
   * Selector -> .tutor-option-field-input.image-previewer
   */
  var imgPreviewers = document.querySelectorAll('.tutor-thumbnail-uploader');
  var imgPreviews = document.querySelectorAll('.tutor-thumbnail-uploader img');
  var imgPrevInputs = document.querySelectorAll('.tutor-thumbnail-uploader input[type=file]');
  var imgPrevDelBtns = document.querySelectorAll('.tutor-thumbnail-uploader .delete-btn');

  if (imgPrevInputs && imgPrevDelBtns) {
    // Checking Img Src when document loads
    document.addEventListener('DOMContentLoaded', function () {
      imgPreviewers.forEach(function (previewer) {
        imgPreviews.forEach(function (img) {
          if (img.getAttribute('src')) {
            img.closest('.image-previewer').classList.add('is-selected');
          } else {
            previewer.classList.remove('is-selected');
          }

          console.log(img);
        });
      });
    }); // Updating Image Preview

    imgPrevInputs.forEach(function (input) {
      input.addEventListener('change', function (e) {
        var file = this.files[0];
        var parentEl = input.closest('.image-previewer');
        var targetImg = parentEl.querySelector('img');
        var prevLoader = parentEl.querySelector('.preview-loading');

        if (file) {
          prevLoader.classList.add('is-loading');
          getImageAsDataURL(file, targetImg);
          parentEl.classList.add('is-selected');
          setTimeout(function () {
            prevLoader.classList.remove('is-loading');
          }, 200);
        }
      });
    }); // Deleting Image Preview

    imgPrevDelBtns.forEach(function (delBtn) {
      delBtn.addEventListener('click', function (e) {
        var parentEl = this.closest('.image-previewer');
        var targetImg = parentEl.querySelector('img');
        targetImg.setAttribute('src', '');
        parentEl.classList.remove('is-selected');
      });
    });
  } // Get Image file as Data URL


  var getImageAsDataURL = function getImageAsDataURL(file, imgSrc) {
    var reader = new FileReader();

    reader.onload = function () {
      imgSrc.setAttribute('src', this.result);
    };

    reader.readAsDataURL(file);
  };
})();

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
(() => {
"use strict";
/*!*******************************************!*\
  !*** ./assets/react/front/tutor-front.js ***!
  \*******************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _lib_common__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../lib/common */ "./assets/react/lib/common.js");
/* harmony import */ var _dashboard__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./dashboard */ "./assets/react/front/dashboard.js");
function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }



jQuery(document).ready(function ($) {
  'use strict';
  /**
   * wp.i18n translateable functions 
   * @since 1.9.0
  */

  var _wp$i18n = wp.i18n,
      __ = _wp$i18n.__,
      _x = _wp$i18n._x,
      _n = _wp$i18n._n,
      _nx = _wp$i18n._nx;
  /**
   * Initiate Select2
   * @since v.1.3.4
   */

  if (jQuery().select2) {
    $('.tutor_select2').select2({
      escapeMarkup: function escapeMarkup(markup) {
        return markup;
      }
    });
  } //END: select2

  /*!
   * jQuery UI Touch Punch 0.2.3
   *
   * Copyright 2011–2014, Dave Furfero
   * Dual licensed under the MIT or GPL Version 2 licenses.
   *
   * Depends:
   *  jquery.ui.widget.js
   *  jquery.ui.mouse.js
   */


  !function (a) {
    function f(a, b) {
      if (!(a.originalEvent.touches.length > 1)) {
        a.preventDefault();
        var c = a.originalEvent.changedTouches[0],
            d = document.createEvent("MouseEvents");
        d.initMouseEvent(b, !0, !0, window, 1, c.screenX, c.screenY, c.clientX, c.clientY, !1, !1, !1, !1, 0, null), a.target.dispatchEvent(d);
      }
    }

    if (a.support.touch = "ontouchend" in document, a.support.touch) {
      var e,
          b = a.ui.mouse.prototype,
          c = b._mouseInit,
          d = b._mouseDestroy;
      b._touchStart = function (a) {
        var b = this;
        !e && b._mouseCapture(a.originalEvent.changedTouches[0]) && (e = !0, b._touchMoved = !1, f(a, "mouseover"), f(a, "mousemove"), f(a, "mousedown"));
      }, b._touchMove = function (a) {
        e && (this._touchMoved = !0, f(a, "mousemove"));
      }, b._touchEnd = function (a) {
        e && (f(a, "mouseup"), f(a, "mouseout"), this._touchMoved || f(a, "click"), e = !1);
      }, b._mouseInit = function () {
        var b = this;
        b.element.bind({
          touchstart: a.proxy(b, "_touchStart"),
          touchmove: a.proxy(b, "_touchMove"),
          touchend: a.proxy(b, "_touchEnd")
        }), c.call(b);
      }, b._mouseDestroy = function () {
        var b = this;
        b.element.unbind({
          touchstart: a.proxy(b, "_touchStart"),
          touchmove: a.proxy(b, "_touchMove"),
          touchend: a.proxy(b, "_touchEnd")
        }), d.call(b);
      };
    }
  }(jQuery);
  /**
   * END jQuery UI Touch Punch
   */

  var videoPlayer = {
    ajaxurl: window._tutorobject.ajaxurl,
    nonce_key: window._tutorobject.nonce_key,
    video_data: function video_data() {
      var video_track_data = $('#tutor_video_tracking_information').val();
      return video_track_data ? JSON.parse(video_track_data) : {};
    },
    track_player: function track_player() {
      var that = this;

      if (typeof Plyr !== 'undefined') {
        var player = new Plyr('#tutorPlayer');
        var video_data = that.video_data();
        player.on('ready', function (event) {
          var instance = event.detail.plyr;
          var best_watch_time = video_data.best_watch_time;

          if (best_watch_time > 0 && instance.duration > Math.round(best_watch_time)) {
            instance.media.currentTime = best_watch_time;
          }

          that.sync_time(instance);
        });
        var tempTimeNow = 0;
        var intervalSeconds = 30; //Send to tutor backend about video playing time in this interval

        player.on('timeupdate', function (event) {
          var instance = event.detail.plyr;
          var tempTimeNowInSec = tempTimeNow / 4; //timeupdate firing 250ms interval

          if (tempTimeNowInSec >= intervalSeconds) {
            that.sync_time(instance);
            tempTimeNow = 0;
          }

          tempTimeNow++;
        });
        player.on('ended', function (event) {
          var video_data = that.video_data();
          var instance = event.detail.plyr;
          var data = {
            is_ended: true
          };
          that.sync_time(instance, data);

          if (video_data.autoload_next_course_content) {
            that.autoload_content();
          }
        });
      }
    },
    sync_time: function sync_time(instance, options) {
      var post_id = this.video_data().post_id; //TUTOR is sending about video playback information to server.

      var data = {
        action: 'sync_video_playback',
        currentTime: instance.currentTime,
        duration: instance.duration,
        post_id: post_id
      };
      data[this.nonce_key] = _tutorobject[this.nonce_key];
      var data_send = data;

      if (options) {
        data_send = Object.assign(data, options);
      }

      $.post(this.ajaxurl, data_send);
    },
    autoload_content: function autoload_content() {
      var post_id = this.video_data().post_id;
      var data = {
        action: 'autoload_next_course_content',
        post_id: post_id
      };
      data[this.nonce_key] = _tutorobject[this.nonce_key];
      $.post(this.ajaxurl, data).done(function (response) {
        if (response.success && response.data.next_url) {
          location.href = response.data.next_url;
        }
      });
    },
    init: function init() {
      this.track_player();
    }
  };
  /**
   * Fire TUTOR video
   * @since v.1.0.0
   */

  if ($('#tutorPlayer').length) {
    videoPlayer.init();
  }

  $(document).on('change keyup paste', '.tutor_user_name', function () {
    $(this).val(tutor_slugify($(this).val()));
  });

  function tutor_slugify(text) {
    return text.toString().toLowerCase().replace(/\s+/g, '-') // Replace spaces with -
    .replace(/[^\w\-]+/g, '') // Remove all non-word chars
    .replace(/\-\-+/g, '-') // Replace multiple - with single -
    .replace(/^-+/, '') // Trim - from start of text
    .replace(/-+$/, ''); // Trim - from end of text
  }

  function toggle_star_(star) {
    star.add(star.prevAll()).filter('i').addClass('tutor-icon-star-full').removeClass('tutor-icon-star-line');
    star.nextAll().filter('i').removeClass('tutor-icon-star-full').addClass('tutor-icon-star-line');
  }
  /**
   * Hover tutor rating and set value
   */


  $(document).on('mouseover', '.tutor-star-rating-container .tutor-star-rating-group i', function () {
    toggle_star_($(this));
  });
  $(document).on('click', '.tutor-star-rating-container .tutor-star-rating-group i', function () {
    var rating = $(this).attr('data-rating-value');
    $(this).closest('.tutor-star-rating-group').find('input[name="tutor_rating_gen_input"]').val(rating);
    toggle_star_($(this));
  });
  $(document).on('mouseout', '.tutor-star-rating-container .tutor-star-rating-group', function () {
    var value = $(this).find('input[name="tutor_rating_gen_input"]').val();
    var rating = parseInt(value);
    var selected = $(this).find('[data-rating-value="' + rating + '"]');
    rating && selected && selected.length > 0 ? toggle_star_(selected) : $(this).find('i').removeClass('tutor-icon-star-full').addClass('tutor-icon-star-line');
  });
  $(document).on('click', '.tutor_submit_review_btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var rating = $that.closest('form').find('input[name="tutor_rating_gen_input"]').val();
    var review = $that.closest('form').find('textarea[name="review"]').val();
    review = review.trim();
    var course_id = $('input[name="tutor_course_id"]').val();
    var data = {
      course_id: course_id,
      rating: rating,
      review: review,
      action: 'tutor_place_rating'
    };

    if (!rating || rating == 0 || !review) {
      alert(__('Rating and review required', 'tutor'));
      return;
    }

    if (review) {
      $.ajax({
        url: _tutorobject.ajaxurl,
        type: 'POST',
        data: data,
        beforeSend: function beforeSend() {
          $that.addClass('updating-icon');
        },
        success: function success(data) {
          var review_id = data.data.review_id;
          var review = data.data.review;
          $('.tutor-review-' + review_id + ' .review-content').html(review); // Show thank you

          new window.tutor_popup($, 'icon-rating', 40).popup({
            title: __('Thank You for Rating This Course!', 'tutor'),
            description: __('Your rating will now be visible in the course page', 'tutor')
          });
          setTimeout(function () {
            location.reload();
          }, 3000);
        }
      });
    }
  }).on('click', '.tutor_cancel_review_btn', function () {
    // Hide the pop up review form on cancel click
    $(this).closest('form').hide();
  });
  $(document).on('click', '.write-course-review-link-btn', function (e) {
    e.preventDefault();
    $(this).siblings('.tutor-write-review-form').slideToggle();
  });
  $(document).on('click', '.tutor-ask-question-btn', function (e) {
    e.preventDefault();
    $('.tutor-add-question-wrap').slideToggle();
  });
  $(document).on('click', '.tutor_question_cancel', function (e) {
    e.preventDefault();
    $('.tutor-add-question-wrap').toggle();
  });
  $(document).on('submit', '#tutor-ask-question-form', function (e) {
    e.preventDefault();
    var $form = $(this);
    var data = $(this).serializeObject();
    data.action = 'tutor_ask_question';
    $.ajax({
      url: _tutorobject.ajaxurl,
      type: 'POST',
      data: data,
      beforeSend: function beforeSend() {
        $form.find('.tutor_ask_question_btn').addClass('updating-icon');
      },
      success: function success(data) {
        if (data.success) {
          $('.tutor-add-question-wrap').hide();
          window.location.reload();
        }
      },
      complete: function complete() {
        $form.find('.tutor_ask_question_btn').removeClass('updating-icon');
      }
    });
  });
  $(document).on('submit', '.tutor-add-answer-form', function (e) {
    e.preventDefault();
    var $form = $(this);
    var data = $(this).serializeObject();
    data.action = 'tutor_add_answer';
    $.ajax({
      url: _tutorobject.ajaxurl,
      type: 'POST',
      data: data,
      beforeSend: function beforeSend() {
        $form.find('.tutor_add_answer_btn').addClass('updating-icon');
      },
      success: function success(data) {
        if (data.success) {
          window.location.reload();
        }
      },
      complete: function complete() {
        $form.find('.tutor_add_answer_btn').removeClass('updating-icon');
      }
    });
  });
  $(document).on('focus', '.tutor_add_answer_textarea', function (e) {
    e.preventDefault();
    var question_id = $(this).closest('.tutor_add_answer_wrap').attr('data-question-id');
    var conf = {
      tinymce: {
        wpautop: true,
        //plugins : 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
        toolbar1: 'bold italic underline bullist strikethrough numlist  blockquote  alignleft aligncenter alignright undo redo link unlink spellchecker fullscreen'
      }
    };
    wp.editor.initialize('tutor_answer_' + question_id, conf);
  });
  $(document).on('click', '.tutor_cancel_wp_editor', function (e) {
    e.preventDefault();
    $(this).closest('.tutor_wp_editor_wrap').toggle();
    $(this).closest('.tutor_add_answer_wrap').find('.tutor_wp_editor_show_btn_wrap').toggle();
    var question_id = $(this).closest('.tutor_add_answer_wrap').attr('data-question-id');
    wp.editor.remove('tutor_answer_' + question_id);
  });
  $(document).on('click', '.tutor_wp_editor_show_btn', function (e) {
    e.preventDefault();
    $(this).closest('.tutor_add_answer_wrap').find('.tutor_wp_editor_wrap').toggle();
    $(this).closest('.tutor_wp_editor_show_btn_wrap').toggle();
  });
  /**
   * Quiz attempt
   */

  var $tutor_quiz_time_update = $('#tutor-quiz-time-update');
  var attempt_settings = null;

  if ($tutor_quiz_time_update.length) {
    attempt_settings = JSON.parse($tutor_quiz_time_update.attr('data-attempt-settings'));
    var attempt_meta = JSON.parse($tutor_quiz_time_update.attr('data-attempt-meta'));

    if (attempt_meta.time_limit.time_limit_seconds > 0) {
      //No time Zero limit for
      var countDownDate = new Date(attempt_settings.attempt_started_at).getTime() + attempt_meta.time_limit.time_limit_seconds * 1000;
      var time_now = new Date(attempt_meta.date_time_now).getTime();
      var tutor_quiz_interval = setInterval(function () {
        var distance = countDownDate - time_now;
        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor(distance % (1000 * 60 * 60 * 24) / (1000 * 60 * 60));
        var minutes = Math.floor(distance % (1000 * 60 * 60) / (1000 * 60));
        var seconds = Math.floor(distance % (1000 * 60) / 1000);
        var countdown_human = '';

        if (days) {
          countdown_human += days + "d ";
        }

        if (hours) {
          countdown_human += hours + "h ";
        }

        if (minutes) {
          countdown_human += minutes + "m ";
        }

        if (seconds) {
          countdown_human += seconds + "s ";
        }

        if (distance < 0) {
          clearInterval(tutor_quiz_interval);
          countdown_human = "EXPIRED"; //Set the quiz attempt to timeout in ajax

          if (_tutorobject.quiz_options.quiz_when_time_expires === 'autosubmit') {
            /**
             * Auto Submit
             */
            $('form#tutor-answering-quiz').submit();
          } else if (_tutorobject.quiz_options.quiz_when_time_expires === 'autoabandon') {
            /**
             *
             * @type {jQuery}
             *
             * Current attempt will be cancel with attempt status attempt_timeout
             */
            var quiz_id = $('#tutor_quiz_id').val();
            var tutor_quiz_remaining_time_secs = $('#tutor_quiz_remaining_time_secs').val();
            var quiz_timeout_data = {
              quiz_id: quiz_id,
              action: 'tutor_quiz_timeout'
            };
            var att = $("#tutor-quiz-time-expire-wrapper").attr('data-attempt-remaining'); //disable buttons

            $(".tutor-quiz-answer-next-btn, .tutor-quiz-submit-btn, .tutor-quiz-answer-previous-btn").prop('disabled', true); //add alert text

            $(".time-remaining span").css('color', '#F44337');
            $.ajax({
              url: _tutorobject.ajaxurl,
              type: 'POST',
              data: quiz_timeout_data,
              success: function success(data) {
                var attemptAllowed = $("#tutor-quiz-time-expire-wrapper").data('attempt-allowed');
                var attemptRemaining = $("#tutor-quiz-time-expire-wrapper").data('attempt-remaining');
                var alertDiv = "#tutor-quiz-time-expire-wrapper .tutor-alert";
                $(alertDiv).addClass('show');

                if (att > 0) {
                  $("".concat(alertDiv, " .text")).html(__('Your time limit for this quiz has expired, please reattempt the quiz. Attempts remaining: ' + attemptRemaining + '/' + attemptAllowed, 'tutor'));
                } else {
                  $(alertDiv).addClass('tutor-alert-danger');
                  $("#tutor-start-quiz").hide();
                  $("".concat(alertDiv, " .text")).html("".concat(__('Unfortunately, you are out of time and quiz attempts. ', 'tutor')));
                }
              },
              complete: function complete() {}
            });
          }
        }

        time_now = time_now + 1000;
        $tutor_quiz_time_update.html(countdown_human);
      }, 1000);
    } else {
      $tutor_quiz_time_update.closest('.time-remaining').remove();
    }
  }

  var $quiz_start_form = $('#tutor-quiz-body form#tutor-start-quiz');

  if ($quiz_start_form.length) {
    if (_tutorobject.quiz_options.quiz_auto_start === '1') {
      $quiz_start_form.submit();
    }
  }
  /**
   * Quiz Frontend Review Action
   * @since 1.4.0
   */


  $(document).on('click', '.quiz-manual-review-action', function (e) {
    e.preventDefault();
    var $that = $(this),
        attempt_id = $that.attr('data-attempt-id'),
        attempt_answer_id = $that.attr('data-attempt-answer-id'),
        mark_as = $that.attr('data-mark-as');
    $.ajax({
      url: _tutorobject.ajaxurl,
      type: 'GET',
      data: {
        action: 'review_quiz_answer',
        attempt_id: attempt_id,
        attempt_answer_id: attempt_answer_id,
        mark_as: mark_as
      },
      beforeSend: function beforeSend() {
        $that.find('i').addClass('updating-icon');
      },
      success: function success(data) {
        location.reload();
      },
      complete: function complete() {
        $that.find('i').removeClass('updating-icon');
      }
    });
  }); // Quiz Review : Tooltip

  $(".tooltip-btn").on("hover", function (e) {
    $(this).toggleClass("active");
  }); // tutor course content accordion

  /**
  * Toggle topic summery
  * @since v.1.6.9
  */

  $('.tutor-course-title h4 .toggle-information-icon').on('click', function (e) {
    $(this).closest('.tutor-topics-in-single-lesson').find('.tutor-topics-summery').slideToggle();
    e.stopPropagation();
  });
  $('.tutor-course-topic.tutor-active').find('.tutor-course-lessons').slideDown();
  $('.tutor-course-title').on('click', function () {
    var lesson = $(this).siblings('.tutor-course-lessons');
    $(this).closest('.tutor-course-topic').toggleClass('tutor-active');
    lesson.slideToggle();
  });
  $(document).on('click', '.tutor-topics-title h3 .toggle-information-icon', function (e) {
    $(this).closest('.tutor-topics-in-single-lesson').find('.tutor-topics-summery').slideToggle();
    e.stopPropagation();
  });
  $(document).on('click', '.tutor-course-wishlist-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var course_id = $that.attr('data-course-id');
    $.ajax({
      url: _tutorobject.ajaxurl,
      type: 'POST',
      data: {
        course_id: course_id,
        'action': 'tutor_course_add_to_wishlist'
      },
      beforeSend: function beforeSend() {
        $that.addClass('updating-icon');
      },
      success: function success(data) {
        if (data.success) {
          if (data.data.status === 'added') {
            $that.addClass('has-wish-listed');
          } else {
            $that.removeClass('has-wish-listed');
          }
        } else {
          window.location = data.data.redirect_to;
        }
      },
      complete: function complete() {
        $that.removeClass('updating-icon');
      }
    });
  });
  /**
   * Check if lesson has classic editor support
   * If classic editor support, stop ajax load on the lesson page.
   *
   * @since v.1.0.0
   *
   * @updated v.1.4.0
   */

  if (!_tutorobject.enable_lesson_classic_editor) {
    $(document).on('click', '.tutor-single-lesson-a', function (e) {
      e.preventDefault();
      var $that = $(this);
      var lesson_id = $that.attr('data-lesson-id');
      var $wrap = $('#tutor-single-entry-content');
      $.ajax({
        url: _tutorobject.ajaxurl,
        type: 'POST',
        data: {
          lesson_id: lesson_id,
          'action': 'tutor_render_lesson_content'
        },
        beforeSend: function beforeSend() {
          var page_title = $that.find('.lesson_title').text();
          $('head title').text(page_title);
          window.history.pushState('obj', page_title, $that.attr('href'));
          $wrap.addClass('loading-lesson');
          $('.tutor-single-lesson-items').removeClass('active');
          $that.closest('.tutor-single-lesson-items').addClass('active');
        },
        success: function success(data) {
          $wrap.html(data.data.html);
          videoPlayer.init();
          $('.tutor-lesson-sidebar').css('display', '');
          window.dispatchEvent(new window.Event('tutor_ajax_lesson_loaded')); // Some plugins like h5p needs notification on ajax load
        },
        complete: function complete() {
          $wrap.removeClass('loading-lesson');
        }
      });
    });
    $(document).on('click', '.sidebar-single-quiz-a', function (e) {
      e.preventDefault();
      var $that = $(this);
      var quiz_id = $that.attr('data-quiz-id');
      var page_title = $that.find('.lesson_title').text();
      var $wrap = $('#tutor-single-entry-content');
      $.ajax({
        url: _tutorobject.ajaxurl,
        type: 'POST',
        data: {
          quiz_id: quiz_id,
          'action': 'tutor_render_quiz_content'
        },
        beforeSend: function beforeSend() {
          $('head title').text(page_title);
          window.history.pushState('obj', page_title, $that.attr('href'));
          $wrap.addClass('loading-lesson');
          $('.tutor-single-lesson-items').removeClass('active');
          $that.closest('.tutor-single-lesson-items').addClass('active');
        },
        success: function success(data) {
          $wrap.html(data.data.html);
          init_quiz_builder();
          $('.tutor-lesson-sidebar').css('display', '');
        },
        complete: function complete() {
          $wrap.removeClass('loading-lesson');
        }
      });
    });
  }
  /**
   * @date 05 Feb, 2019
   */


  $(document).on('click', '.tutor-lesson-sidebar-hide-bar', function (e) {
    e.preventDefault();
    $('.tutor-lesson-sidebar').toggle();
    $('#tutor-single-entry-content').toggleClass("sidebar-hidden");
  });
  $(".tutor-tabs-btn-group a").on('click touchstart', function (e) {
    e.preventDefault();
    var $that = $(this);
    var tabSelector = $that.attr('href');
    $('.tutor-lesson-sidebar-tab-item').hide();
    $(tabSelector).show();
    $('.tutor-tabs-btn-group a').removeClass('active');
    $that.addClass('active');
  });
  /**
   * @date 18 Feb, 2019
   * @since v.1.0.0
   */

  function init_quiz_builder() {
    if (jQuery().sortable) {
      $(".tutor-quiz-answers-wrap").sortable({
        handle: ".answer-sorting-bar",
        start: function start(e, ui) {
          ui.placeholder.css('visibility', 'visible');
        },
        stop: function stop(e, ui) {//Sorting Stopped...
        }
      }).disableSelection();
      $(".quiz-draggable-rand-answers, .quiz-answer-matching-droppable").sortable({
        connectWith: ".quiz-answer-matching-droppable",
        placeholder: "drop-hover"
      }).disableSelection();
    }
  }

  init_quiz_builder();
  /**
   * Quiz view
   * @date 22 Feb, 2019
   * @since v.1.0.0
   */

  $(document).on('click', '.tutor-quiz-answer-next-btn, .tutor-quiz-answer-previous-btn', function (e) {
    e.preventDefault(); // Show previous quiz if press previous button

    if ($(this).hasClass('tutor-quiz-answer-previous-btn')) {
      $(this).closest('.quiz-attempt-single-question').hide().prev().show();
      return;
    }

    var $that = $(this);
    var $question_wrap = $that.closest('.quiz-attempt-single-question');
    /**
     * Validating required answer
     * @type {jQuery}
     *
     * @since v.1.6.1
     */

    var validated = tutor_quiz_validation($question_wrap);

    if (!validated) {
      return;
    }

    var feedBackNext = feedback_response($question_wrap);

    if (!feedBackNext) {
      return;
    }

    var question_id = parseInt($that.closest('.quiz-attempt-single-question').attr('id').match(/\d+/)[0], 10);
    var next_question_id = $that.closest('.quiz-attempt-single-question').attr('data-next-question-id');

    if (next_question_id) {
      var $nextQuestion = $(next_question_id);

      if ($nextQuestion && $nextQuestion.length) {
        /**
         * check if reveal mode wait for 500ms then
         * hide question so that correct answer reveal
         * @since 1.8.10
        */
        var feedBackMode = $question_wrap.attr('data-quiz-feedback-mode');

        if (feedBackMode === 'reveal') {
          setTimeout(function () {
            $('.quiz-attempt-single-question').hide();
            $nextQuestion.show();
          }, 800);
        } else {
          $('.quiz-attempt-single-question').hide();
          $nextQuestion.show();
        }
        /**
         * If pagination exists, set active class
         */


        if ($('.tutor-quiz-questions-pagination').length) {
          $('.tutor-quiz-question-paginate-item').removeClass('active');
          $('.tutor-quiz-questions-pagination a[href="' + next_question_id + '"]').addClass('active');
        }
      }
    }
  });
  $(document).on('submit', '#tutor-answering-quiz', function (e) {
    var $questions_wrap = $('.quiz-attempt-single-question');
    var validated = true;

    if ($questions_wrap.length) {
      $questions_wrap.each(function (index, question) {
        // !tutor_quiz_validation( $(question) ) ? validated = false : 0;
        // !feedback_response( $(question) ) ? validated = false : 0;
        validated = tutor_quiz_validation($(question));
        validated = feedback_response($(question));
      });
    }

    if (!validated) {
      e.preventDefault();
    }
  });
  $(document).on('click', '.tutor-quiz-question-paginate-item', function (e) {
    e.preventDefault();
    var $that = $(this);
    var $question = $($that.attr('href'));
    $('.quiz-attempt-single-question').hide();
    $question.show(); //Active Class

    $('.tutor-quiz-question-paginate-item').removeClass('active');
    $that.addClass('active');
  });
  /**
   * Limit Short Answer Question Type
   */

  $(document).on('keyup', 'textarea.question_type_short_answer, textarea.question_type_open_ended', function (e) {
    var $that = $(this);
    var value = $that.val();
    var limit = $that.hasClass('question_type_short_answer') ? _tutorobject.quiz_options.short_answer_characters_limit : _tutorobject.quiz_options.open_ended_answer_characters_limit;
    var remaining = limit - value.length;

    if (remaining < 1) {
      $that.val(value.substr(0, limit));
      remaining = 0;
    }

    $that.closest('.tutor-quiz-answers-wrap').find('.characters_remaining').html(remaining);
  });
  /**
   *
   * @type {jQuery}
   *
   * Improved Quiz draggable answers drop accessibility
   * Answers draggable wrap will be now same height.
   *
   * @since v.1.4.4
   */

  var countDraggableAnswers = $('.quiz-draggable-rand-answers').length;

  if (countDraggableAnswers) {
    $('.quiz-draggable-rand-answers').each(function () {
      var $that = $(this);
      var draggableDivHeight = $that.height();
      $that.css({
        "height": draggableDivHeight
      });
    });
  }
  /**
   * Quiz Validation Helper
   *
   * @since v.1.6.1
   */


  function tutor_quiz_validation($question_wrap) {
    var validated = true;
    var $required_answer_wrap = $question_wrap.find('.quiz-answer-required');

    if ($required_answer_wrap.length) {
      /**
       * Radio field validation
       *
       * @type {jQuery}
       *
       * @since v.1.6.1
       */
      var $inputs = $required_answer_wrap.find('input');

      if ($inputs.length) {
        var $type = $inputs.attr('type');

        if ($type === 'radio') {
          if ($required_answer_wrap.find('input[type="radio"]:checked').length == 0) {
            $question_wrap.find('.answer-help-block').html("<p style=\"color: #dc3545\">".concat(__('Please select an option to answer', 'tutor'), "</p>"));
            validated = false;
          }
        } else if ($type === 'checkbox') {
          if ($required_answer_wrap.find('input[type="checkbox"]:checked').length == 0) {
            $question_wrap.find('.answer-help-block').html("<p style=\"color: #dc3545\">".concat(__('Please select at least one option to answer.', 'tutor'), "</p>"));
            validated = false;
          }
        } else if ($type === 'text') {
          //Fill in the gaps if many, validation all
          $inputs.each(function (index, input) {
            if (!$(input).val().trim().length) {
              $question_wrap.find('.answer-help-block').html("<p style=\"color: #dc3545\">".concat(__('The answer for this question is required', 'tutor'), "</p>"));
              validated = false;
            }
          });
        }
      }

      if ($required_answer_wrap.find('textarea').length) {
        if ($required_answer_wrap.find('textarea').val().trim().length < 1) {
          $question_wrap.find('.answer-help-block').html("<p style=\"color: #dc3545\">".concat(__('The answer for this question is required', 'tutor'), "</p>"));
          validated = false;
        }
      }
      /**
       * Matching Question
       */


      var $matchingDropable = $required_answer_wrap.find('.quiz-answer-matching-droppable');

      if ($matchingDropable.length) {
        $matchingDropable.each(function (index, matching) {
          if (!$(matching).find('.quiz-draggable-answer-item').length) {
            $question_wrap.find('.answer-help-block').html("<p style=\"color: #dc3545\">".concat(__('Please match all the items', 'tutor'), "</p>"));
            validated = false;
          }
        });
      }
    }

    return validated;
  }

  function feedback_response($question_wrap) {
    var goNext = false; // Prepare answer array            

    var quiz_answers = JSON.parse(atob(window.tutor_quiz_context.split('').reverse().join('')));
    !Array.isArray(quiz_answers) ? quiz_answers = [] : 0; // Evaluate result

    var feedBackMode = $question_wrap.attr('data-quiz-feedback-mode');
    $('.wrong-right-text').remove();
    $('.quiz-answer-input-bottom').removeClass('wrong-answer right-answer');
    var validatedTrue = true;
    var $inputs = $question_wrap.find('input');
    var $checkedInputs = $question_wrap.find('input[type="radio"]:checked, input[type="checkbox"]:checked');

    if (feedBackMode === 'retry') {
      $checkedInputs.each(function () {
        var $input = $(this);
        var $type = $input.attr('type');

        if ($type === 'radio' || $type === 'checkbox') {
          var isTrue = quiz_answers.indexOf($input.val()) > -1; // $input.attr('data-is-correct') == '1';

          if (!isTrue) {
            if ($input.prop("checked")) {
              $input.closest('.quiz-answer-input-bottom').addClass('wrong-answer').append("<span class=\"wrong-right-text\"><i class=\"tutor-icon-line-cross\"></i> ".concat(__('Incorrect, Please try again', 'tutor'), "</span>"));
            }

            validatedTrue = false;
          }
        }
      });
      $inputs.each(function () {
        var $input = $(this);
        var $type = $input.attr('type');

        if ($type === 'checkbox') {
          var isTrue = quiz_answers.indexOf($input.val()) > -1; // $input.attr('data-is-correct') == '1';

          var checked = $input.is(':checked');

          if (isTrue && !checked) {
            $question_wrap.find('.answer-help-block').html("<p style=\"color: #dc3545\">".concat(__('More answer for this question is required', 'tutor'), "</p>"));
            validatedTrue = false;
          }
        }
      });
    } else if (feedBackMode === 'reveal') {
      $checkedInputs.each(function () {
        var $input = $(this);
        var isTrue = quiz_answers.indexOf($input.val()) > -1; // $input.attr('data-is-correct') == '1';

        if (!isTrue) {
          validatedTrue = false;
        }
      });
      $inputs.each(function () {
        var $input = $(this);
        var $type = $input.attr('type');

        if ($type === 'radio' || $type === 'checkbox') {
          var isTrue = quiz_answers.indexOf($input.val()) > -1; // $input.attr('data-is-correct') == '1';

          var checked = $input.is(':checked');

          if (isTrue) {
            $input.closest('.quiz-answer-input-bottom').addClass('right-answer').append("<span class=\"wrong-right-text\"><i class=\"tutor-icon-checkbox-pen-outline\"></i>".concat(__('Correct Answer', 'tutor'), "</span>"));
          } else {
            if ($input.prop("checked")) {
              $input.closest('.quiz-answer-input-bottom').addClass('wrong-answer');
            }
          }

          if (isTrue && !checked) {
            $input.attr('disabled', 'disabled');
            validatedTrue = false;
            goNext = true;
          }
        }
      });
    }

    if (validatedTrue) {
      goNext = true;
    }

    return goNext;
  }
  /**
   * Add to cart in guest mode, show login form
   *
   * @since v.1.0.4
   */


  $(document).on('submit click', '.cart-required-login, .cart-required-login a, .cart-required-login form', function (e) {
    e.preventDefault();
    var login_url = $(this).data('login_page_url');
    login_url ? window.location.assign(login_url) : $('.tutor-cart-box-login-form').fadeIn(100);
  });
  $('.tutor-popup-form-close, .login-overlay-close').on('click', function () {
    $('.tutor-cart-box-login-form').fadeOut(100);
  });
  $(document).on('keyup', function (e) {
    if (e.keyCode === 27) {
      $('.tutor-frontend-modal').hide();
      $('.tutor-cart-box-login-form').fadeOut(100);
    }
  });
  /**
   * Share Link enable
   *
   * @since v.1.0.4
   */

  if ($.fn.ShareLink) {
    var $social_share_wrap = $('.tutor-social-share-wrap');

    if ($social_share_wrap.length) {
      var share_config = JSON.parse($social_share_wrap.attr('data-social-share-config'));
      $('.tutor_share').ShareLink({
        title: share_config.title,
        text: share_config.text,
        image: share_config.image,
        class_prefix: 's_',
        width: 640,
        height: 480
      });
    }
  }
  /**
   * Datepicker initiate
   *
   * @since v.1.1.2
   */


  if (jQuery.datepicker) {
    $(".tutor_report_datepicker").datepicker({
      "dateFormat": 'yy-mm-dd'
    });
  }
  /**
   * Setting account for withdraw earning
   *
   * @since v.1.2.0
   */


  $(document).on('submit', '#tutor-withdraw-account-set-form', function (e) {
    e.preventDefault();
    var $form = $(this);
    var $btn = $form.find('.tutor_set_withdraw_account_btn');
    var data = $form.serializeObject();
    $.ajax({
      url: _tutorobject.ajaxurl,
      type: 'POST',
      data: data,
      beforeSend: function beforeSend() {
        $form.find('.tutor-success-msg').remove();
        $btn.addClass('updating-icon');
      },
      success: function success(data) {
        if (data.success) {
          var successMsg = '<div class="tutor-success-msg" style="display: none;"><i class="tutor-icon-mark"></i> ' + data.data.msg + ' </div>';
          $btn.closest('.withdraw-account-save-btn-wrap').append(successMsg);

          if ($form.find('.tutor-success-msg').length) {
            $form.find('.tutor-success-msg').slideDown();
          }

          setTimeout(function () {
            $form.find('.tutor-success-msg').slideUp();
          }, 5000);
        }
      },
      complete: function complete() {
        $btn.removeClass('updating-icon');
      }
    });
  });
  /**
   * Make Withdraw Form
   *
   * @since v.1.2.0
   */

  $(document).on('click', '.open-withdraw-form-btn, .close-withdraw-form-btn', function (e) {
    e.preventDefault();

    if ($(this).data('reload') == 'yes') {
      window.location.reload();
      return;
    }

    $('.tutor-earning-withdraw-form-wrap').toggle().find('[name="tutor_withdraw_amount"]').val('');
    $('.tutor-withdrawal-pop-up-success').hide().next().show();
    $('html, body').css('overflow', $('.tutor-earning-withdraw-form-wrap').is(':visible') ? 'hidden' : 'auto');
  });
  $(document).on('submit', '#tutor-earning-withdraw-form', function (e) {
    e.preventDefault();
    var $form = $(this);
    var $btn = $('#tutor-earning-withdraw-btn');
    var $responseDiv = $('.tutor-withdraw-form-response');
    var data = $form.serializeObject();
    $.ajax({
      url: _tutorobject.ajaxurl,
      type: 'POST',
      data: data,
      beforeSend: function beforeSend() {
        $form.find('.tutor-success-msg').remove();
        $btn.addClass('updating-icon');
      },
      success: function success(data) {
        var Msg;

        if (data.success) {
          if (data.data.available_balance !== 'undefined') {
            $('.withdraw-balance-col .available_balance').html(data.data.available_balance);
          }

          $('.tutor-withdrawal-pop-up-success').show().next().hide();
        } else {
          Msg = '<div class="tutor-error-msg inline-image-text is-inline-block">\
                            <img src="' + window._tutorobject.tutor_url + 'assets/images/icon-cross.svg"/> \
                            <div>\
                                <b>Error</b><br/>\
                                <span>' + data.data.msg + '</span>\
                            </div>\
                        </div>';
          $responseDiv.html(Msg);
          setTimeout(function () {
            $responseDiv.html('');
          }, 5000);
        }
      },
      complete: function complete() {
        $btn.removeClass('updating-icon');
      }
    });
  });
  var frontEndModal = $('.tutor-frontend-modal');
  frontEndModal.each(function () {
    var modal = $(this),
        action = $(this).data('popup-rel');
    $('[href="' + action + '"]').on('click', function (e) {
      modal.fadeIn();
      e.preventDefault();
    });
  });
  $(document).on('click', '.tm-close, .tutor-frontend-modal-overlay, .tutor-modal-btn-cancel', function () {
    frontEndModal.fadeOut();
  });
  /**
   * Delete Course
   */

  $(document).on('click', '.tutor-dashboard-element-delete-btn', function (e) {
    e.preventDefault();
    var element_id = $(this).attr('data-id');
    $('#tutor-dashboard-delete-element-id').val(element_id);
  });
  $(document).on('submit', '#tutor-dashboard-delete-element-form', function (e) {
    e.preventDefault();
    var element_id = $('#tutor-dashboard-delete-element-id').val();
    var $btn = $('.tutor-modal-element-delete-btn');
    var data = $(this).serializeObject();
    $.ajax({
      url: _tutorobject.ajaxurl,
      type: 'POST',
      data: data,
      beforeSend: function beforeSend() {
        $btn.addClass('updating-icon');
      },
      success: function success(res) {
        if (res.success) {
          $('#tutor-dashboard-' + res.data.element + '-' + element_id).remove();
        }
      },
      complete: function complete() {
        $btn.removeClass('updating-icon');
        $('.tutor-frontend-modal').hide();
      }
    });
  });
  /**
   * Frontend Profile
   */

  if (!$('#tutor_profile_photo_id').val()) {
    $('.tutor-profile-photo-delete-btn').hide();
  }

  $(document).on('click', '.tutor-profile-photo-delete-btn', function () {
    $('.tutor-profile-photo-upload-wrap').find('img').attr('src', _tutorobject.placeholder_img_src);
    $('#tutor_profile_photo_id').val('');
    $('.tutor-profile-photo-delete-btn').hide();
    $.ajax({
      url: _tutorobject.ajaxurl,
      type: 'POST',
      data: {
        'action': 'tutor_profile_photo_remove'
      }
    });
    return false;
  });
  /**
   * Assignment
   *
   * @since v.1.3.3
   */

  $(document).on('submit', '#tutor_assignment_start_form', function (e) {
    e.preventDefault();
    var $that = $(this);
    var form_data = $that.serializeObject();
    form_data.action = 'tutor_start_assignment';
    $.ajax({
      url: _tutorobject.ajaxurl,
      type: 'POST',
      data: form_data,
      beforeSend: function beforeSend() {
        $('#tutor_assignment_start_btn').addClass('updating-icon');
      },
      success: function success(data) {
        if (data.success) {
          location.reload();
        }
      },
      complete: function complete() {
        $('#tutor_assignment_start_btn').removeClass('updating-icon');
      }
    });
  });
  /**
   * Assignment answer validation
   */

  $(document).on('submit', '#tutor_assignment_submit_form', function (e) {
    var assignment_answer = $('textarea[name="assignment_answer"]').val();

    if (assignment_answer.trim().length < 1) {
      $('#form_validation_response').html('<div class="tutor-error-msg">' + __('Assignment answer can not be empty', 'tutor') + '</div>');
      e.preventDefault();
    }
  });
  /**
   * Course builder video
   * @since v.1.3.4
   */

  $(document).on('click', '.video_source_upload_wrap_html5 .video_upload_btn', function (event) {
    event.preventDefault();
    var $that = $(this);
    var frame; // If the media frame already exists, reopen it.

    if (frame) {
      frame.open();
      return;
    }

    frame = wp.media({
      title: __('Select / Upload Media Of Your Chosen Persuasion', 'tutor'),
      button: {
        text: __('Use media', 'tutor')
      },
      library: {
        type: 'video'
      },
      multiple: false // Set to true to allow multiple files to be selected

    });
    frame.on('select', function () {
      // Get media attachment details from the frame state
      var attachment = frame.state().get('selection').first().toJSON();
      $that.closest('.video_source_upload_wrap_html5').find('span.video_media_id').data('video_url', attachment.url).text(attachment.id).trigger('paste').closest('p').show();
      $that.closest('.video_source_upload_wrap_html5').find('input').val(attachment.id);
    });
    frame.open();
  });
  /**
   * END: Tutor Course builder JS
   */

  /**
   * Single Assignment Upload Button
   * @since v.1.3.4
   */

  $('form').on('change', '.tutor-assignment-file-upload', function () {
    $(this).siblings("label").find('span').html($(this).val().replace(/.*(\/|\\)/, ''));
  });
  /**
   * Lesson Sidebar Topic Toggle
   * @since v.1.3.4
   */

  $(document).on('click', '.tutor-topics-in-single-lesson .tutor-topics-title h3, .tutor-single-lesson-topic-toggle', function (e) {
    var $that = $(this);
    var $parent = $that.closest('.tutor-topics-in-single-lesson');
    $parent.toggleClass('tutor-topic-active');
    $parent.find('.tutor-lessons-under-topic').slideToggle();
  });
  $('.tutor-single-lesson-items.active').closest('.tutor-lessons-under-topic').show();
  $('.tutor-single-lesson-items.active').closest('.tutor-topics-in-single-lesson').addClass('tutor-topic-active');
  $('.tutor-course-lesson.active').closest('.tutor-lessons-under-topic').show();
  /**
   * Add Assignment
   */

  $(document).on('click', '.add-assignment-attachments', function (event) {
    event.preventDefault();
    var $that = $(this);
    var frame; // If the media frame already exists, reopen it.

    if (frame) {
      frame.open();
      return;
    } // Create a new media frame


    frame = wp.media({
      title: __('Select / Upload Media Of Your Chosen Persuasion', 'tutor'),
      button: {
        text: __('Use media', 'tutor')
      },
      multiple: false // Set to true to allow multiple files to be selected

    }); // When an image is selected in the media frame...

    frame.on('select', function () {
      // Get media attachment details from the frame state
      var attachment = frame.state().get('selection').first().toJSON();
      var field_markup = '<div class="tutor-individual-attachment-file"><p class="attachment-file-name">' + attachment.filename + '</p><input type="hidden" name="tutor_assignment_attachments[]" value="' + attachment.id + '"><a href="javascript:;" class="remove-assignment-attachment-a text-muted"> &times; Remove</a></div>';
      $('#assignment-attached-file').append(field_markup);
      $that.closest('.video_source_upload_wrap_html5').find('input').val(attachment.id);
    }); // Finally, open the modal on click

    frame.open();
  });
  $(document).on('click', '.remove-assignment-attachment-a', function (event) {
    event.preventDefault();
    $(this).closest('.tutor-individual-attachment-file').remove();
  });
  /**
   *
   * @type {jQuery}
   *
   * Course builder auto draft save
   *
   * @since v.1.3.4
   */

  var tutor_course_builder = $('input[name="tutor_action"]').val();

  if (tutor_course_builder === 'tutor_add_course_builder') {
    setInterval(auto_draft_save_course_builder, 30000);
  }

  function auto_draft_save_course_builder() {
    var form_data = $('form#tutor-frontend-course-builder').serializeObject();
    form_data.tutor_ajax_action = 'tutor_course_builder_draft_save';
    $.ajax({
      //url : _tutorobject.ajaxurl,
      type: 'POST',
      data: form_data,
      beforeSend: function beforeSend() {
        $('.tutor-dashboard-builder-draft-btn span').text(__('Saving...', 'tutor'));
      },
      success: function success(data) {},
      complete: function complete() {
        $('.tutor-dashboard-builder-draft-btn span').text(__('Save', 'tutor'));
      }
    });
  }
  /**
   *
   * @type {jQuery}
   *
   * Course builder section toggle
   *
   * @since v.1.3.5
   */


  $('.tutor-course-builder-section-title').on('click', function () {
    if ($(this).find('i').hasClass("tutor-icon-up")) {
      $(this).find('i').removeClass('tutor-icon-up').addClass('tutor-icon-down');
    } else {
      $(this).find('i').removeClass('tutor-icon-down').addClass('tutor-icon-up');
    }

    $(this).next('div').slideToggle();
  });
  /**
   * Open Tutor Modal to edit review
   * @since v.1.4.0
   */

  $(document).on('click', '.open-tutor-edit-review-modal', function (e) {
    e.preventDefault();
    var $that = $(this);
    var review_id = $that.attr('data-review-id');
    var nonce_key = _tutorobject.nonce_key;
    var json_data = {
      review_id: review_id,
      action: 'tutor_load_edit_review_modal'
    };
    json_data[nonce_key] = _tutorobject[nonce_key];
    $.ajax({
      url: _tutorobject.ajaxurl,
      type: 'POST',
      data: json_data,
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        if (typeof data.data !== 'undefined') {
          $('.tutor-edit-review-modal-wrap .modal-container').html(data.data.output);
          $('.tutor-edit-review-modal-wrap').attr('data-review-id', review_id).addClass('show');
        }
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  /**
   * Update the rating
   * @since v.1.4.0
   */

  $(document).on('submit', '#tutor_update_review_form', function (e) {
    e.preventDefault();
    var $that = $(this);
    var review_id = $that.closest('.tutor-edit-review-modal-wrap ').attr('data-review-id');
    var nonce_key = _tutorobject.nonce_key;
    var rating = $that.find('input[name="tutor_rating_gen_input"]').val();
    var review = $that.find('textarea[name="review"]').val();
    review = review.trim();
    var json_data = {
      review_id: review_id,
      rating: rating,
      review: review,
      action: 'tutor_update_review_modal'
    };
    json_data[nonce_key] = _tutorobject[nonce_key];
    $.ajax({
      url: _tutorobject.ajaxurl,
      type: 'POST',
      data: json_data,
      beforeSend: function beforeSend() {
        $that.find('button[type="submit"]').addClass('tutor-updating-message');
      },
      success: function success(data) {
        if (data.success) {
          //Close the modal
          $('.tutor-edit-review-modal-wrap').removeClass('show');
          location.reload(true);
        }
      },
      complete: function complete() {
        $that.find('button[type="submit"]').removeClass('tutor-updating-message');
      }
    });
  });
  /**
   * Profile photo upload
   * @since v.1.4.5
   */

  $(document).on('click', '#tutor_profile_photo_button', function (e) {
    e.preventDefault();
    $('#tutor_profile_photo_file').trigger('click');
  });
  $(document).on('change', '#tutor_profile_photo_file', function (event) {
    event.preventDefault();
    var $file = this;

    if ($file.files && $file.files[0]) {
      var reader = new FileReader();

      reader.onload = function (e) {
        $('.tutor-profile-photo-upload-wrap').find('img').attr('src', e.target.result);
      };

      reader.readAsDataURL($file.files[0]);
    }
  });
  /**
   * Addon, Tutor BuddyPress
   * Retrieve MetaInformation on BuddyPress message system
   * @for TutorLMS Pro
   * @since v.1.4.8
   */

  $(document).on('click', '.thread-content .subject', function (e) {
    var $btn = $(this);
    var thread_id = parseInt($btn.closest('.thread-content').attr('data-thread-id'));
    var nonce_key = _tutorobject.nonce_key;
    var json_data = {
      thread_id: thread_id,
      action: 'tutor_bp_retrieve_user_records_for_thread'
    };
    json_data[nonce_key] = _tutorobject[nonce_key];
    $.ajax({
      type: 'POST',
      url: window._tutorobject.ajaxurl,
      data: json_data,
      beforeSend: function beforeSend() {
        $('#tutor-bp-thread-wrap').html('');
      },
      success: function success(data) {
        if (data.success) {
          $('#tutor-bp-thread-wrap').html(data.data.thread_head_html);
          tutor_bp_setting_enrolled_courses_list();
        }
      }
    });
  });

  function tutor_bp_setting_enrolled_courses_list() {
    $('ul.tutor-bp-enrolled-course-list').each(function () {
      var $that = $(this);
      var $li = $that.find(' > li');
      var itemShow = 3;

      if ($li.length > itemShow) {
        var plusCourseCount = $li.length - itemShow;
        $li.each(function (liIndex, liItem) {
          var $liItem = $(this);

          if (liIndex >= itemShow) {
            $liItem.hide();
          }
        });
        var infoHtml = '<a href="javascript:;" class="tutor_bp_plus_courses"><strong>+' + plusCourseCount + ' More </strong></a> Courses';
        $that.closest('.tutor-bp-enrolled-courses-wrap').find('.thread-participant-enrolled-info').html(infoHtml);
      }

      $that.show();
    });
  }

  tutor_bp_setting_enrolled_courses_list();
  $(document).on('click', 'a.tutor_bp_plus_courses', function (e) {
    e.preventDefault();
    var $btn = $(this);
    $btn.closest('.tutor-bp-enrolled-courses-wrap').find('.tutor-bp-enrolled-course-list li').show();
    $btn.closest('.thread-participant-enrolled-info').html('');
  });
  /**
   * Addon, Tutor Certificate
   * Certificate dropdown content and copy link
   * @for TutorLMS Pro
   * @since v.1.5.1
   */
  //$(document).on('click', '.tutor-dropbtn', function (e) {

  $('.tutor-dropbtn').click(function () {
    var $content = $(this).parent().find(".tutor-dropdown-content");
    $content.slideToggle(100);
  }); //$(document).on('click', '.tutor-copy-link', function (e) {

  $('.tutor-copy-link').click(function (e) {
    var $btn = $(this);
    var copy = '<i class="tutor-icon-copy"></i> Copy Link';
    var copied = '<i class="tutor-icon-mark"></i> Copied';
    var dummy = document.createElement('input'),
        text = window.location.href;
    document.body.appendChild(dummy);
    dummy.value = text;
    dummy.select();
    document.execCommand('copy');
    document.body.removeChild(dummy);
    $btn.html(copied);
    setTimeout(function () {
      $btn.html(copy);
    }, 2500);
  });
  $(document).on('click', function (e) {
    var container = $(".tutor-dropdown");
    var $content = container.find('.tutor-dropdown-content'); // if the target of the click isn't the container nor a descendant of the container

    if (!container.is(e.target) && container.has(e.target).length === 0) {
      $content.slideUp(100);
    }
  });
  /**
   * Tutor ajax login
   *
   * @since v.1.6.3
   */

  $(document).on('submit', '.tutor-login-form-wrap #loginform', function (e) {
    e.preventDefault();
    var $that = $(this);
    var $form_wrapper = $('.tutor-login-form-wrap');
    var form_data = $that.serializeObject();
    form_data.action = 'tutor_user_login';
    $.ajax({
      url: _tutorobject.ajaxurl,
      type: 'POST',
      data: form_data,
      success: function success(response) {
        if (response.success) {
          location.assign(response.data.redirect);
          location.reload();
        } else {
          var error_message = response.data || __('Invalid username or password!', 'tutor');

          if ($form_wrapper.find('.tutor-alert').length) {
            $form_wrapper.find('.tutor-alert').html(error_message);
          } else {
            $form_wrapper.prepend('<div class="tutor-alert tutor-alert-warning">' + error_message + '</div>');
          }
        }
      }
    });
  });
  /**
   * Show hide is course public checkbox (frontend dashboard editor)
   * 
   * @since  v.1.7.2
  */

  var price_type = $('.tutor-frontend-builder-course-price [name="tutor_course_price_type"]');

  if (price_type.length == 0) {
    $('#_tutor_is_course_public_meta_checkbox').show();
  } else {
    price_type.change(function () {
      if ($(this).prop('checked')) {
        var method = $(this).val() == 'paid' ? 'hide' : 'show';
        $('#_tutor_is_course_public_meta_checkbox')[method]();
      }
    }).trigger('change');
  }
  /**
   * Withdrawal page tooltip
   * 
   * @since  v.1.7.4
  */
  // Fully accessible tooltip jQuery plugin with delegation.
  // Ideal for view containers that may re-render content.


  (function ($) {
    $.fn.tutor_tooltip = function () {
      this // Delegate to tooltip, Hide if tooltip receives mouse or is clicked (tooltip may stick if parent has focus)
      .on('mouseenter click', '.tooltip', function (e) {
        e.stopPropagation();
        $(this).removeClass('isVisible');
      }) // Delegate to parent of tooltip, Show tooltip if parent receives mouse or focus
      .on('mouseenter focus', ':has(>.tooltip)', function (e) {
        if (!$(this).prop('disabled')) {
          // IE 8 fix to prevent tooltip on `disabled` elements
          $(this).find('.tooltip').addClass('isVisible');
        }
      }) // Delegate to parent of tooltip, Hide tooltip if parent loses mouse or focus
      .on('mouseleave blur keydown', ':has(>.tooltip)', function (e) {
        if (e.type === 'keydown') {
          if (e.which === 27) {
            $(this).find('.tooltip').removeClass('isVisible');
          }
        } else {
          $(this).find('.tooltip').removeClass('isVisible');
        }
      });
      return this;
    };
  })(jQuery); // Bind event listener to container element


  jQuery('.tutor-tooltip-inside').tutor_tooltip();
  /**
   * Manage course filter
   * 
   * @since  v.1.7.2
  */

  var filter_container = $('.tutor-course-filter-container form');
  var loop_container = $('.tutor-course-filter-loop-container');
  var filter_modifier = {}; // Sidebar checkbox value change

  filter_container.on('submit', function (e) {
    e.preventDefault();
  }).find('input').change(function (e) {
    var filter_criteria = Object.assign(filter_container.serializeObject(), filter_modifier);
    filter_criteria.action = 'tutor_course_filter_ajax';
    loop_container.html('<center><img src="' + window._tutorobject.loading_icon_url + '"/></center>');
    $(this).closest('form').find('.tutor-clear-all-filter').show();
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: filter_criteria,
      success: function success(r) {
        loop_container.html(r).find('.tutor-pagination-wrap a').each(function () {
          $(this).attr('data-href', $(this).attr('href')).attr('href', '#');
        });
      }
    });
  }); // Alter pagination

  loop_container.on('click', '.tutor-pagination-wrap a', function (e) {
    var url = $(this).data('href') || $(this).attr('href');

    if (url) {
      url = new URL(url);
      var page = url.searchParams.get("paged");

      if (page) {
        e.preventDefault();
        filter_modifier.page = page;
        filter_container.find('input:first').trigger('change');
      }
    }
  }); // Alter sort filter

  loop_container.on('change', 'select[name="tutor_course_filter"]', function () {
    filter_modifier.tutor_course_filter = $(this).val();
    filter_container.find('input:first').trigger('change');
  }); // Refresh page after coming back to course archive page from cart

  var archive_loop = $('.tutor-course-loop');

  if (archive_loop.length > 0) {
    window.sessionStorage.getItem('tutor_refresh_archive') === 'yes' ? window.location.reload() : 0;
    window.sessionStorage.removeItem('tutor_refresh_archive');
    archive_loop.on('click', '.tutor-loop-cart-btn-wrap', function () {
      window.sessionStorage.setItem('tutor_refresh_archive', 'yes');
    });
  }
  /**
   * Profile Photo and Cover Photo editor
   * 
   * @since  v.1.7.5
  */


  var PhotoEditor = function PhotoEditor(photo_editor) {
    this.dialogue_box = photo_editor.find('#tutor_photo_dialogue_box');

    this.open_dialogue_box = function (name) {
      this.dialogue_box.attr('name', name);
      this.dialogue_box.trigger('click');
    };

    this.validate_image = function (file) {
      return true;
    };

    this.upload_selected_image = function (name, file) {
      if (!file || !this.validate_image(file)) {
        return;
      }

      var nonce = tutor_get_nonce_data(true);
      var context = this;
      context.toggle_loader(name, true); // Prepare payload to upload

      var form_data = new FormData();
      form_data.append('action', 'tutor_user_photo_upload');
      form_data.append('photo_type', name);
      form_data.append('photo_file', file, file.name);
      form_data.append(nonce.key, nonce.value);
      $.ajax({
        url: window._tutorobject.ajaxurl,
        data: form_data,
        type: 'POST',
        processData: false,
        contentType: false,
        error: context.error_alert,
        complete: function complete() {
          context.toggle_loader(name, false);
        }
      });
    };

    this.accept_upload_image = function (context, e) {
      var file = e.currentTarget.files[0] || null;
      context.update_preview(e.currentTarget.name, file);
      context.upload_selected_image(e.currentTarget.name, file);
      $(e.currentTarget).val('');
    };

    this.delete_image = function (name) {
      var context = this;
      context.toggle_loader(name, true);
      $.ajax({
        url: window._tutorobject.ajaxurl,
        data: {
          action: 'tutor_user_photo_remove',
          photo_type: name
        },
        type: 'POST',
        error: context.error_alert,
        complete: function complete() {
          context.toggle_loader(name, false);
        }
      });
    };

    this.update_preview = function (name, file) {
      var renderer = photo_editor.find(name == 'cover_photo' ? '#tutor_cover_area' : '#tutor_profile_area');

      if (!file) {
        renderer.css('background-image', 'url(' + renderer.data('fallback') + ')');
        this.delete_image(name);
        return;
      }

      var reader = new FileReader();

      reader.onload = function (e) {
        renderer.css('background-image', 'url(' + e.target.result + ')');
      };

      reader.readAsDataURL(file);
    };

    this.toggle_profile_pic_action = function (show) {
      var method = show === undefined ? 'toggleClass' : show ? 'addClass' : 'removeClass';
      photo_editor[method]('pop-up-opened');
    };

    this.error_alert = function () {
      alert('Something Went Wrong.');
    };

    this.toggle_loader = function (name, show) {
      photo_editor.find('#tutor_photo_meta_area .loader-area').css('display', show ? 'block' : 'none');
    };

    this.initialize = function () {
      var context = this;
      this.dialogue_box.change(function (e) {
        context.accept_upload_image(context, e);
      });
      photo_editor.find('#tutor_profile_area .tutor_overlay, #tutor_pp_option>div:last-child').click(function () {
        context.toggle_profile_pic_action();
      }); // Upload new

      photo_editor.find('.tutor_cover_uploader').click(function () {
        context.open_dialogue_box('cover_photo');
      });
      photo_editor.find('.tutor_pp_uploader').click(function () {
        context.open_dialogue_box('profile_photo');
      }); // Delete existing

      photo_editor.find('.tutor_cover_deleter').click(function () {
        context.update_preview('cover_photo', null);
      });
      photo_editor.find('.tutor_pp_deleter').click(function () {
        context.update_preview('profile_photo', null);
      });
    };
  };

  var photo_editor = $('#tutor_profile_cover_photo_editor');
  photo_editor.length > 0 ? new PhotoEditor(photo_editor).initialize() : 0;
  /**
   * 
   * Instructor list filter
   * 
   * @since  v.1.8.4
  */
  // Get values on course category selection

  $('.tutor-instructor-filter').each(function () {
    var root = $(this);
    var filter_args = {};
    var time_out;

    function run_instructor_filter(name, value, page_number) {
      // Prepare http payload
      var result_container = root.find('.filter-result-container');
      var html_cache = result_container.html();
      var attributes = root.data();
      attributes.current_page = page_number || 1;
      name ? filter_args[name] = value : filter_args = {};
      filter_args.attributes = attributes;
      filter_args.action = 'load_filtered_instructor'; // Show loading icon

      result_container.html('<div style="text-align:center"><img src="' + window._tutorobject.loading_icon_url + '"/></div>');
      $.ajax({
        url: window._tutorobject.ajaxurl,
        data: filter_args,
        type: 'POST',
        success: function success(r) {
          result_container.html(r);
        },
        error: function error() {
          result_container.html(html_cache);
          tutor_toast('Failed', 'Request Error', 'error');
        }
      });
    }

    root.on('change', '.course-category-filter [type="checkbox"]', function () {
      var values = {};
      $(this).closest('.course-category-filter').find('input:checked').each(function () {
        values[$(this).val()] = $(this).parent().text();
      }); // Show selected cat list

      var cat_parent = root.find('.selected-cate-list').empty();
      var cat_ids = Object.keys(values);
      cat_ids.forEach(function (value) {
        cat_parent.append('<span>' + values[value] + ' <span class="tutor-icon-line-cross" data-cat_id="' + value + '"></span></span>');
      });
      cat_ids.length ? cat_parent.append('<span data-cat_id="0">Clear All</span>') : 0;
      run_instructor_filter($(this).attr('name'), cat_ids);
    }).on('click', '.tutor-instructor-ratings i', function (e) {
      var rating = e.target.dataset.value;
      run_instructor_filter('rating_filter', rating);
    }).on('click', '.selected-cate-list [data-cat_id]', function () {
      var id = $(this).data('cat_id');
      var inputs = root.find('.mobile-filter-popup [type="checkbox"]');
      id ? inputs = inputs.filter('[value="' + id + '"]') : 0;
      inputs.prop('checked', false).trigger('change');
    }).on('input', '.filter-pc [name="keyword"]', function () {
      // Get values on search keyword change
      var val = $(this).val();
      time_out ? window.clearTimeout(time_out) : 0;
      time_out = window.setTimeout(function () {
        run_instructor_filter('keyword', val);
        time_out = null;
      }, 500);
    }).on('click', '[data-page_number]', function (e) {
      // On pagination click
      e.preventDefault();
      run_instructor_filter(null, null, $(this).data('page_number'));
    }).on('click', '.clear-instructor-filter', function () {
      // Clear filter
      var root = $(this).closest('.tutor-instructor-filter');
      root.find('input[type="checkbox"]').prop('checked', false);
      root.find('[name="keyword"]').val('');
      run_instructor_filter();
    }).on('click', '.mobile-filter-container i', function () {
      // Open mobile screen filter
      $(this).parent().next().addClass('is-opened');
    }).on('click', '.mobile-filter-popup button', function () {
      $('.mobile-filter-popup [type="checkbox"]').trigger('change'); // Close mobile screen filter

      $(this).closest('.mobile-filter-popup').removeClass('is-opened');
    }).on('input', '.filter-mobile [name="keyword"]', function () {
      // Sync keyword with two screen
      root.find('.filter-pc [name="keyword"]').val($(this).val()).trigger('input');
    }).on('change', '.mobile-filter-popup [type="checkbox"]', function (e) {
      if (e.originalEvent) {
        return;
      } // Sync category with two screen


      var name = $(this).attr('name');
      var val = $(this).val();
      var checked = $(this).prop('checked');
      root.find('.course-category-filter [name="' + name + '"]').filter('[value="' + val + '"]').prop('checked', checked).trigger('change');
    }).on('mousedown touchstart', '.expand-instructor-filter', function (e) {
      var window_height = $(window).height();
      var el = root.find('.mobile-filter-popup>div');
      var el_top = window_height - el.height();
      var plus = ((e.originalEvent.touches || [])[0] || e).clientY - el_top;
      root.on('mousemove touchmove', function (e) {
        var y = ((e.originalEvent.touches || [])[0] || e).clientY;
        var height = window_height - y + plus;
        height > 200 && height <= window_height ? el.css('height', height + 'px') : 0;
      });
    }).on('mouseup touchend', function () {
      root.off('mousemove touchmove');
    }).on('click', '.mobile-filter-popup>div', function (e) {
      e.stopImmediatePropagation();
    }).on('click', '.mobile-filter-popup', function (e) {
      $(this).removeClass('is-opened');
      ;
    });
  });
  /**
   * Load more categories instructor list
   * 
   * @package Instructor List
   * @sice v2.0.0
   */

  document.querySelector(".tutor-instructor-category-show-more > .text-medium-caption").onclick = function (e) {
    var term_id = e.target.parentNode.dataset.id;
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        action: 'show_more',
        term_id: term_id
      },
      success: function success(response) {
        console.log(response);

        if (response.success && response.data.categories.length) {
          document.querySelector(".tutor-instructor-category-show-more").style.display = "block";

          var _iterator = _createForOfIteratorHelper(response.data.categories),
              _step;

          try {
            for (_iterator.s(); !(_step = _iterator.n()).done;) {
              var res = _step.value;
              var wrapper = $(".tutor-instructor-categories-wrapper .course-category-filter");
              document.querySelector(".tutor-instructor-categories-wrapper .text-medium-caption").dataset.id = res.term_id;
              wrapper.append("<div class=\"tutor-form-check tutor-mb-25\">\n                                <input\n                                    id=\"item-a\"\n                                    type=\"checkbox\"\n                                    class=\"tutor-form-check-input tutor-form-check-square\"\n                                    name=\"category\"\n                                    value=\"".concat(res.term_id, "\"/>\n                                <label for=\"item-a\">\n                                    ").concat(res.name, "\n                                </label>\n                            </div>\n                            "));
            }
          } catch (err) {
            _iterator.e(err);
          } finally {
            _iterator.f();
          }
        }

        if (false === response.data.show_more) {
          document.querySelector(".tutor-instructor-category-show-more").style.display = "none";
        }
      },
      complete: function complete() {},
      error: function error(err) {
        alert(err);
      }
    });
  };
  /**
   * Show start active as per click
   * 
   * @since v2.0.0
   */


  var stars = document.querySelectorAll(".tutor-instructor-ratings i");
  var rating_range = document.querySelector(".tutor-instructor-rating-filter");

  var _iterator2 = _createForOfIteratorHelper(stars),
      _step2;

  try {
    for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
      var star = _step2.value;

      star.onclick = function (e) {
        //remove active if has
        var _iterator3 = _createForOfIteratorHelper(stars),
            _step3;

        try {
          for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
            var _star = _step3.value;

            if (_star.classList.contains('active')) {
              _star.classList.remove('active');
            }
          } //show stars active as click

        } catch (err) {
          _iterator3.e(err);
        } finally {
          _iterator3.f();
        }

        var length = e.target.dataset.value;

        for (var i = 0; i < length; i++) {
          stars[i].classList.add('active');
          stars[i].classList.remove('ttr-star-line-filled');
          stars[i].classList.add('ttr-star-full-filled');
        }

        rating_range.innerHTML = "0.0 - ".concat(length, ".0");
      };
    }
    /**
     * Retake course
     * 
     * @since v1.9.5
     */

  } catch (err) {
    _iterator2.e(err);
  } finally {
    _iterator2.f();
  }

  $('.tutor-course-retake-button').click(function (e) {
    e.preventDefault();
    var button = $(this);
    var url = button.attr('href');
    var course_id = button.data('course_id');
    var popup;
    var data = {
      title: __('Override Previous Progress', 'tutor'),
      description: __('Before continue, please decide whether to keep progress or reset.', 'tutor'),
      buttons: {
        reset: {
          title: __('Reset Data', 'tutor'),
          "class": 'secondary',
          callback: function callback() {
            var button = popup.find('.tutor-button-secondary');
            button.prop('disabled', true).append('<img style="margin-left: 7px" src="' + window._tutorobject.loading_icon_url + '"/>');
            $.ajax({
              url: window._tutorobject.ajaxurl,
              type: 'POST',
              data: {
                action: 'tutor_reset_course_progress',
                course_id: course_id
              },
              success: function success(response) {
                if (response.success) {
                  window.location.assign(response.data.redirect_to);
                } else {
                  alert((response.data || {}).message || __('Something went wrong', 'tutor'));
                }
              },
              complete: function complete() {
                button.prop('disabled', false).find('img').remove();
              }
            });
          }
        },
        keep: {
          title: __('Keep Data', 'tutor'),
          "class": 'primary',
          callback: function callback() {
            window.location.assign(url);
          }
        }
      }
    };
    popup = new window.tutor_popup($, 'icon-gear', 40).popup(data);
  }); //warn user before leave page if quiz is running

  document.body.addEventListener('click', function (event) {
    var target = event.target;
    var targetTag = target.tagName;
    var parentTag = target.parentElement.tagName;

    if ($tutor_quiz_time_update.length > 0 && $tutor_quiz_time_update.html() != 'EXPIRED') {
      if (targetTag === 'A' || parentTag === 'A') {
        event.preventDefault();
        event.stopImmediatePropagation();
        var popup;
        var data = {
          title: __('Abandon Quiz?', 'tutor'),
          description: __('Do you want to abandon this quiz? The quiz will be submitted partially up to this question if you leave this page.', 'tutor'),
          buttons: {
            keep: {
              title: __('Yes, leave quiz', 'tutor'),
              id: 'leave',
              "class": 'secondary',
              callback: function callback() {
                var formData = $('form#tutor-answering-quiz').serialize() + '&action=' + 'tutor_quiz_abandon';
                $.ajax({
                  url: window._tutorobject.ajaxurl,
                  type: 'POST',
                  data: formData,
                  beforeSend: function beforeSend() {
                    document.querySelector("#tutor-popup-leave").innerHTML = __('Leaving...', 'tutor');
                  },
                  success: function success(response) {
                    if (response.success) {
                      if (target.href == undefined) {
                        location.href = target.parentElement.href;
                      } else {
                        location.href = target.href;
                      }
                    } else {
                      alert(__('Something went wrong', 'tutor'));
                    }
                  },
                  error: function error() {
                    alert(__('Something went wrong', 'tutor'));
                    popup.remove();
                  }
                });
              }
            },
            reset: {
              title: __('Stay here', 'tutor'),
              id: 'reset',
              "class": 'primary',
              callback: function callback() {
                popup.remove();
              }
            }
          }
        };
        popup = new window.tutor_popup($, '', 40).popup(data);
      }
    }
  });
  /* Disable start quiz button  */

  $('body').on('submit', 'form#tutor-start-quiz', function () {
    $(this).find('button').prop('disabled', true);
  });
});
})();

/******/ })()
;
//# sourceMappingURL=tutor-front.js.map
/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/react/lib/common.js":
/*!************************************!*\
  !*** ./assets/react/lib/common.js ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _utilities__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./utilities */ "./assets/react/lib/utilities.js");
/* harmony import */ var _utilities__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_utilities__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _tutor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./tutor */ "./assets/react/lib/tutor.js");
/* harmony import */ var _tutor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_tutor__WEBPACK_IMPORTED_MODULE_1__);



/***/ }),

/***/ "./assets/react/lib/tutor.js":
/*!***********************************!*\
  !*** ./assets/react/lib/tutor.js ***!
  \***********************************/
/***/ (() => {

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

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
   * Slider bar
   */

  function tutor_slider_init() {
    $('.tutor-field-slider').each(function () {
      var $slider = $(this);
      var $input = $slider.closest('.tutor-field-type-slider').find('input[type="hidden"]');
      var $showVal = $slider.closest('.tutor-field-type-slider').find('.tutor-field-type-slider-value');
      var min = parseFloat($slider.closest('.tutor-field-type-slider').attr('data-min'));
      var max = parseFloat($slider.closest('.tutor-field-type-slider').attr('data-max'));
      $slider.slider({
        range: "max",
        min: min,
        max: max,
        value: $input.val(),
        slide: function slide(event, ui) {
          $showVal.text(ui.value);
          $input.val(ui.value);
        }
      });
    });
  }

  tutor_slider_init();
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

  $(document).on('change', '.tutor_lesson_video_source', function (e) {
    var $that = $(this);
    var selector = $(this).val();

    if (selector) {
      $('.video-metabox-source-input-wrap').show();
    } else {
      $('.video-metabox-source-input-wrap').hide();
    }

    $that.closest('.tutor-option-field').find('.video-metabox-source-item').hide();
    $that.closest('.tutor-option-field').find('.video_source_wrap_' + selector).show();
  });
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
  /**
   * Quiz Builder
   */

  $(document).on('click', '.create_new_topic_btn', function (e) {
    e.preventDefault();
    $('.tutor-metabox-add-topics').slideToggle();
  });
  $(document).on('click', '#tutor-add-topic-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var form_data = $that.closest('.tutor-metabox-add-topics').find('input, textarea').serializeObject();
    form_data.action = 'tutor_add_course_topic';
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: form_data,
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        if (data.success) {
          $('#tutor-course-content-wrap').html(data.data.course_contents);
          $that.closest('.tutor-metabox-add-topics').find('input[type!="hidden"], textarea').each(function () {
            $(this).val('');
          });
          $that.closest('.tutor-metabox-add-topics').slideUp();
          enable_sorting_topic_lesson();
        }
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  /**
   * Zoom Meeting js
   * here for support enable_sorting_topic_lesson function
   * @since 1.7.1
   */

  $('.tutor-zoom-meeting-modal-wrap').on('submit', '.tutor-meeting-modal-form', function (e) {
    e.preventDefault();
    var $form = $(this);
    var data = $form.serializeObject();
    var timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    data.timezone = timezone;
    var $btn = $form.find('button[type="submit"]');
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: data,
      beforeSend: function beforeSend() {
        $btn.addClass('tutor-updating-message');
      },
      success: function success(data) {
        data.success ? tutor_toast(__('Success', 'tutor'), $btn.data('toast_success_message'), 'success') : tutor_toast(__('Update Error', 'tutor'), __('Meeting Update Failed', 'tutor'), 'error');

        if (data.course_contents) {
          $(data.selector).html(data.course_contents);

          if (data.selector == '#tutor-course-content-wrap') {
            enable_sorting_topic_lesson();
          } //Close the modal


          $('.tutor-zoom-meeting-modal-wrap').removeClass('show');
        } else {
          location.reload();
        }
      },
      complete: function complete() {
        $btn.removeClass('tutor-updating-message');
      }
    });
  });
  /**
   * Resorting...
   */

  function enable_sorting_topic_lesson() {
    if (jQuery().sortable) {
      $(".course-contents").sortable({
        handle: ".course-move-handle",
        start: function start(e, ui) {
          ui.placeholder.css('visibility', 'visible');
        },
        stop: function stop(e, ui) {
          tutor_sorting_topics_and_lesson();
        }
      });
      $(".tutor-lessons:not(.drop-lessons)").sortable({
        connectWith: ".tutor-lessons",
        items: "div.course-content-item",
        start: function start(e, ui) {
          ui.placeholder.css('visibility', 'visible');
        },
        stop: function stop(e, ui) {
          tutor_sorting_topics_and_lesson();
        }
      });
    }
  }

  function tutor_sorting_topics_and_lesson() {
    var topics = {};
    $('.tutor-topics-wrap').each(function (index, item) {
      var $topic = $(this);
      var topics_id = parseInt($topic.attr('id').match(/\d+/)[0], 10);
      var lessons = {};
      $topic.find('.course-content-item').each(function (lessonIndex, lessonItem) {
        var $lesson = $(this);
        var lesson_id = parseInt($lesson.attr('id').match(/\d+/)[0], 10);
        lessons[lessonIndex] = lesson_id;
      });
      topics[index] = {
        'topic_id': topics_id,
        'lesson_ids': lessons
      };
    });
    $('#tutor_topics_lessons_sorting').val(JSON.stringify(topics));
  }

  $(document).on('change keyup', '.course-edit-topic-title-input', function (e) {
    e.preventDefault();
    $(this).closest('.tutor-topics-top').find('.topic-inner-title').html($(this).val());
  });
  $(document).on('click', '.topic-edit-icon', function (e) {
    e.preventDefault();
    $(this).closest('.tutor-topics-top').find('.tutor-topics-edit-form').slideToggle();
  });
  $(document).on('click', '.tutor-topics-edit-button', function (e) {
    e.preventDefault();
    var $button = $(this);
    var $topic = $button.closest('.tutor-topics-wrap');
    var topics_id = parseInt($topic.attr('id').match(/\d+/)[0], 10);
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
          $button.closest('.tutor-topics-wrap').find('.tutor-topics-edit-form').slideUp();
        }
      },
      complete: function complete() {
        $button.removeClass('tutor-updating-message');
      }
    });
  });
  /**
   * Update Lesson Modal
   */

  $(document).on('click', '.open-tutor-lesson-modal', function (e) {
    e.preventDefault();
    var $that = $(this);
    var lesson_id = $that.attr('data-lesson-id');
    var topic_id = $that.attr('data-topic-id');
    var course_id = $('#post_ID').val();
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        lesson_id: lesson_id,
        topic_id: topic_id,
        course_id: course_id,
        action: 'tutor_load_edit_lesson_modal'
      },
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        $('.tutor-lesson-modal-wrap .modal-container').html(data.data.output);
        $('.tutor-lesson-modal-wrap').attr({
          'data-lesson-id': lesson_id,
          'data-topic-id': topic_id
        }).addClass('show');
        var tinymceConfig = tinyMCEPreInit.mceInit.tutor_editor_config;

        if (!tinymceConfig) {
          tinymceConfig = tinyMCEPreInit.mceInit.course_description;
        }

        tinymce.init(tinymceConfig);
        tinymce.execCommand('mceRemoveEditor', false, 'tutor_lesson_modal_editor');
        tinyMCE.execCommand('mceAddEditor', false, "tutor_lesson_modal_editor");
        $(document).trigger('lesson_modal_loaded', {
          lesson_id: lesson_id,
          topic_id: topic_id,
          course_id: course_id
        });
      },
      complete: function complete() {
        quicktags({
          id: "tutor_lesson_modal_editor"
        });
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  /**
   * Lesson upload thumbnail
   */

  $(document).on('click', '.lesson_thumbnail_upload_btn', function (event) {
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
      $that.closest('.tutor-thumbnail-wrap').find('.thumbnail-img').html('<img src="' + attachment.url + '" alt="" /><a href="javascript:;" class="tutor-lesson-thumbnail-delete-btn"><i class="tutor-icon-line-cross"></i></a>');
      $that.closest('.tutor-thumbnail-wrap').find('input').val(attachment.id);
      $('.tutor-lesson-thumbnail-delete-btn').show();
    });
    frame.open();
  });
  /**
   * Lesson Feature Image Delete
   * @since v.1.5.6
   */

  $(document).on('click', '.tutor-lesson-thumbnail-delete-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    $that.closest('.tutor-thumbnail-wrap').find('._lesson_thumbnail_id').val('');
    $that.closest('.tutor-thumbnail-wrap').find('.thumbnail-img').html('');
    $that.hide();
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
   * Confirmation for deleting Topic
   */

  $(document).on('click', '.topic-delete-btn a', function (e) {
    var topic_id = $(this).attr('data-topic-id');

    if (!confirm(__('Are you sure to delete?', 'tutor'))) {
      e.preventDefault();
    }
  });
  $(document).on('click', '.tutor-expand-all-topic', function (e) {
    e.preventDefault();
    $('.tutor-topics-body').slideDown();
    $('.expand-collapse-wrap i').removeClass('tutor-icon-light-down').addClass('tutor-icon-light-up');
  });
  $(document).on('click', '.tutor-collapse-all-topic', function (e) {
    e.preventDefault();
    $('.tutor-topics-body').slideUp();
    $('.expand-collapse-wrap i').removeClass('tutor-icon-light-up').addClass('tutor-icon-light-down');
  });
  $(document).on('click', '.topic-inner-title, .expand-collapse-wrap', function (e) {
    e.preventDefault();
    var $that = $(this);
    $that.closest('.tutor-topics-wrap').find('.tutor-topics-body').slideToggle();
    $that.closest('.tutor-topics-wrap').find('.expand-collapse-wrap i').toggleClass('tutor-icon-light-down tutor-icon-light-up');
  });
  /**
   * Create new quiz
   */

  $(document).on('click', '.quiz-modal-btn-first-step', function (e) {
    e.preventDefault();
    var $that = $(this);
    var $quizTitle = $('[name="quiz_title"]');
    var quiz_title = $quizTitle.val();
    var quiz_description = $('[name="quiz_description"]').val();

    if (!quiz_title) {
      $quizTitle.closest('.tutor-quiz-builder-group').find('.quiz_form_msg').html('Please enter quiz title');
      return;
    } else {
      $quizTitle.closest('.tutor-quiz-builder-group').find('.quiz_form_msg').html('');
    }

    var course_id = $('#post_ID').val();
    var topic_id = $that.closest('.tutor-modal-wrap').attr('quiz-for-post-id');

    if ($('#tutor_quiz_builder_quiz_id').length) {
      /**
       *
       * @type {jQuery}
       *
       * if quiz id exists, we are sending it to update quiz
       */
      var quiz_id = $('#tutor_quiz_builder_quiz_id').val();
      $.ajax({
        url: window._tutorobject.ajaxurl,
        type: 'POST',
        data: {
          quiz_title: quiz_title,
          quiz_description: quiz_description,
          quiz_id: quiz_id,
          topic_id: topic_id,
          action: 'tutor_quiz_builder_quiz_update'
        },
        beforeSend: function beforeSend() {
          $that.addClass('tutor-updating-message');
        },
        success: function success(data) {
          $('#tutor-quiz-' + quiz_id).html(data.data.output_quiz_row);
          $('#tutor-quiz-modal-tab-items-wrap a[href="#quiz-builder-tab-questions"]').trigger('click');
          tutor_slider_init();
        },
        complete: function complete() {
          $that.removeClass('tutor-updating-message');
        }
      });
      return;
    }

    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        quiz_title: quiz_title,
        quiz_description: quiz_description,
        course_id: course_id,
        topic_id: topic_id,
        action: 'tutor_create_quiz_and_load_modal'
      },
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        $('.tutor-quiz-builder-modal-wrap .modal-container').html(data.data.output);
        $('#tutor-topics-' + topic_id + ' .tutor-lessons').append(data.data.output_quiz_row);
        $('#tutor-quiz-modal-tab-items-wrap a[href="#quiz-builder-tab-questions"]').trigger('click');
        tutor_slider_init();
        $(document).trigger('quiz_modal_loaded', {
          topic_id: topic_id,
          course_id: course_id
        });
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  /**
   * Ope modal for edit quiz
   */

  $(document).on('click', '.open-tutor-quiz-modal', function (e) {
    e.preventDefault();
    var $that = $(this);
    var quiz_id = $that.attr('data-quiz-id');
    var topic_id = $that.attr('data-topic-id');

    if (topic_id == undefined) {
      topic_id = $that.closest('.tutor-modal-wrap').attr('quiz-for-post-id');
    }

    var course_id = $('#post_ID').val();
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        quiz_id: quiz_id,
        topic_id: topic_id,
        course_id: course_id,
        action: 'tutor_load_edit_quiz_modal'
      },
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        $('.tutor-quiz-builder-modal-wrap .modal-container').html(data.data.output);
        $('.tutor-quiz-builder-modal-wrap').attr('data-quiz-id', quiz_id).attr('quiz-for-post-id', topic_id).addClass('show'); //Back to question Tab if exists

        if ($that.attr('data-back-to-tab')) {
          var tabSelector = $that.attr('data-back-to-tab');
          $('#tutor-quiz-modal-tab-items-wrap a[href="' + tabSelector + '"]').trigger('click');
        }

        $(document).trigger('quiz_modal_loaded', {
          quiz_id: quiz_id,
          topic_id: topic_id,
          course_id: course_id
        });
        tutor_slider_init();
        enable_quiz_questions_sorting();
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  $(document).on('click', '.quiz-modal-settings-save-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var quiz_id = $('.tutor-quiz-builder-modal-wrap').attr('data-quiz-id');
    var current_topic_id = $("#current_topic_id_for_quiz").val();
    var $formInput = $('#quiz-builder-tab-settings :input, #quiz-builder-tab-advanced-options :input').serializeObject();
    $formInput.topic_id = current_topic_id;
    $formInput.quiz_id = quiz_id;
    $formInput.action = 'tutor_quiz_modal_update_settings';
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: $formInput,
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        if (data.success) {
          $('#tutor-course-content-wrap').html(data.data.course_contents);
        }

        data.success ? tutor_toast(__('Success', 'tutor'), $that.data('toast_success_message'), 'success') : tutor_toast(__('Update Error', 'tutor'), __('Quiz Update Failed', 'tutor'), 'error');
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');

        if ($that.attr('data-action') === 'modal_close') {
          $('.tutor-modal-wrap').removeClass('show');
        }
      }
    });
  });
  /**
   * Quiz Question edit save and continue
   */

  $(document).on('click', '.quiz-modal-question-save-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var $formInput = $('.quiz_question_form :input').serializeObject();
    $formInput.action = 'tutor_quiz_modal_update_question';
    var topic_id = $that.closest('.tutor-modal-wrap').attr('quiz-for-post-id');
    $formInput.topic_id = topic_id;
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: $formInput,
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        if (data.success) {
          //ReOpen questions
          $that.closest('.tutor-quiz-builder-modal-contents').find('.open-tutor-quiz-modal').trigger('click');
        } else {
          if (typeof data.data !== 'undefined') {
            $('#quiz_validation_msg_wrap').html(data.data.validation_msg);
          }
        }
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  /**
   * Sort quiz questions
   */

  function enable_quiz_questions_sorting() {
    if (jQuery().sortable) {
      $(".quiz-builder-questions-wrap").sortable({
        handle: ".question-sorting",
        start: function start(e, ui) {
          ui.placeholder.css('visibility', 'visible');
        },
        stop: function stop(e, ui) {
          tutor_save_sorting_quiz_questions_order();
        }
      });
    }
  }

  function tutor_save_sorting_quiz_questions_order() {
    var questions = {};
    $('.quiz-builder-question-wrap').each(function (index, item) {
      var $question = $(this);
      var question_id = parseInt($question.attr('data-question-id'), 10);
      questions[index] = question_id;
    });
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        sorted_question_ids: questions,
        action: 'tutor_quiz_question_sorting'
      }
    });
  }
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
  $(document).on('click', '.tutor-add-quiz-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var quiz_for_post_id = $(this).closest('.tutor_add_quiz_wrap').attr('data-add-quiz-under');
    var current_topic_id = $(this).data('topic-id');
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        quiz_for_post_id: quiz_for_post_id,
        current_topic_id: current_topic_id,
        action: 'tutor_load_quiz_builder_modal'
      },
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        $('.tutor-quiz-builder-modal-wrap .modal-container').html(data.data.output);
        $('.tutor-quiz-builder-modal-wrap').attr('quiz-for-post-id', quiz_for_post_id).addClass('show');
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
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
  }); //Next Prev Tab

  $(document).on('click', '.quiz-modal-btn-next, .quiz-modal-btn-back', function (e) {
    e.preventDefault();
    var tabSelector = $(this).attr('href');
    $('#tutor-quiz-modal-tab-items-wrap a[href="' + tabSelector + '"]').trigger('click');
  });
  $(document).on('click', '.quiz-modal-tab-navigation-btn.quiz-modal-btn-cancel', function (e) {
    e.preventDefault();
    $('.tutor-modal-wrap').removeClass('show');
  });
  /**
   * Add Question to quiz modal
   */

  $(document).on('click', '.tutor-quiz-open-question-form', function (e) {
    e.preventDefault();
    var $that = $(this);
    var quiz_id = $('#tutor_quiz_builder_quiz_id').val();
    var course_id = $('#post_ID').val();
    var question_id = $that.attr('data-question-id');
    var params = {
      quiz_id: quiz_id,
      course_id: course_id,
      action: 'tutor_quiz_builder_get_question_form'
    };

    if (question_id) {
      params.question_id = question_id;
    }

    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: params,
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        $('.tutor-quiz-builder-modal-contents').html(data.data.output); //Initializing Tutor Select

        tutor_select().reInit();
        enable_quiz_answer_sorting();
        disableAddoption();
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  $(document).on('click', '.tutor-quiz-question-trash', function (e) {
    e.preventDefault();
    var $that = $(this);
    var question_id = $that.attr('data-question-id');
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        question_id: question_id,
        action: 'tutor_quiz_builder_question_delete'
      },
      beforeSend: function beforeSend() {
        $that.closest('.quiz-builder-question-wrap').remove();
      }
    });
  });
  /**
   * Get question answers option form to save multiple/single/true-false options
   *
   * @since v.1.0.0
   */

  $(document).on('click', '.add_question_answers_option:not(.disabled)', function (e) {
    e.preventDefault();
    var $that = $(this);
    var question_id = $that.attr('data-question-id');
    var $formInput = $('.quiz_question_form :input').serializeObject();
    $formInput.question_id = question_id;
    $formInput.action = 'tutor_quiz_add_question_answers';
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: $formInput,
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        $('#tutor_quiz_question_answer_form').html(data.data.output);
        disableAddoption();
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
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
    var $formInput = $('.quiz_question_form :input').serializeObject();
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
    var $formInput = $('.quiz_question_form :input').serializeObject();
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
  $(document).on('refresh', '#tutor_quiz_question_answers', function (e) {
    e.preventDefault();
    var $that = $(this);
    var question_id = $that.attr('data-question-id');
    var question_type = $('.tutor_select_value_holder').val();
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        question_id: question_id,
        question_type: question_type,
        action: 'tutor_quiz_builder_get_answers_by_question'
      },
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
        $('#tutor_quiz_question_answer_form').html('');
      },
      success: function success(data) {
        if (data.success) {
          $that.html(data.data.output);
        }
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
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
  /**
   * Save answer sorting placement
   *
   * @since v.1.0.0
   */

  function enable_quiz_answer_sorting() {
    if (jQuery().sortable) {
      $("#tutor_quiz_question_answers").sortable({
        handle: ".tutor-quiz-answer-sort-icon",
        start: function start(e, ui) {
          ui.placeholder.css('visibility', 'visible');
        },
        stop: function stop(e, ui) {
          tutor_save_sorting_quiz_answer_order();
        }
      });
    }
  }

  function tutor_save_sorting_quiz_answer_order() {
    var answers = {};
    $('.tutor-quiz-answer-wrap').each(function (index, item) {
      var $answer = $(this);
      var answer_id = parseInt($answer.attr('data-answer-id'), 10);
      answers[index] = answer_id;
    });
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        sorted_answer_ids: answers,
        action: 'tutor_quiz_answer_sorting'
      }
    });
  }
  /**
   * Tutor Custom Select
   */


  function tutor_select() {
    var obj = {
      init: function init() {
        $(document).on('click', '.tutor-select .tutor-select-option', function (e) {
          e.preventDefault();
          var $that = $(this);

          if ($that.attr('data-is-pro') !== 'true') {
            var $html = $that.html().trim();
            $that.closest('.tutor-select').find('.select-header .lead-option').html($html);
            $that.closest('.tutor-select').find('.select-header input.tutor_select_value_holder').val($that.attr('data-value')).trigger('change');
            $that.closest('.tutor-select-options').hide();
            disableAddoption();
          } else {
            alert('Tutor Pro version required');
          }
        });
        $(document).on('click', '.tutor-select .select-header', function (e) {
          e.preventDefault();
          var $that = $(this);
          $that.closest('.tutor-select').find('.tutor-select-options').slideToggle();
        });
        this.setValue();
        this.hideOnOutSideClick();
      },
      setValue: function setValue() {
        $('.tutor-select').each(function () {
          var $that = $(this);
          var $option = $that.find('.tutor-select-option');

          if ($option.length) {
            $option.each(function () {
              var $thisOption = $(this);

              if ($thisOption.attr('data-selected') === 'selected') {
                var $html = $thisOption.html().trim();
                $thisOption.closest('.tutor-select').find('.select-header .lead-option').html($html);
                $thisOption.closest('.tutor-select').find('.select-header input.tutor_select_value_holder').val($thisOption.attr('data-value'));
              }
            });
          }
        });
      },
      hideOnOutSideClick: function hideOnOutSideClick() {
        $(document).mouseup(function (e) {
          var $option_wrap = $(".tutor-select-options");

          if (!$(e.target).closest('.select-header').length && !$option_wrap.is(e.target) && $option_wrap.has(e.target).length === 0) {
            $option_wrap.hide();
          }
        });
      },
      reInit: function reInit() {
        this.setValue();
      }
    };
    return obj;
  }

  tutor_select().init();
  /**
   * If change question type from quiz builder question
   *
   * @since v.1.0.0
   */

  $(document).on('change', 'input.tutor_select_value_holder', function (e) {
    var $that = $(this); //$('#tutor_quiz_question_answer_form').html('');

    $('.add_question_answers_option').trigger('click');
    $('#tutor_quiz_question_answers').trigger('refresh');
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
  $(document).on('lesson_modal_loaded', function (e, obj) {
    $('.tutor-lesson-modal-wrap .modal-title h1').html(__('Lesson', 'tutor'));
  });
  $(document).on('assignment_modal_loaded', function (e, obj) {
    $('.tutor-lesson-modal-wrap .modal-title h1').html(__('Assignment', 'tutor'));
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

    if (_tutorobject.is_admin) {
      search_params.set('paged', 1);
    } else {
      search_params.set('current_page', 1);
    }

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
   * @since v.1.9.0
   * Parse and show video duration on link paste in lesson video 
   */

  var video_url_input = '.video_source_wrap_external_url input, .video_source_wrap_vimeo input, .video_source_wrap_youtube input, .video_source_wrap_html5, .video_source_upload_wrap_html5';
  var autofill_url_timeout;
  $('body').on('paste', video_url_input, function (e) {
    e.stopImmediatePropagation();
    var root = $(this).closest('.lesson-modal-form-wrap').find('.tutor-option-field-video-duration');
    var duration_label = root.find('label');
    var is_wp_media = $(this).hasClass('video_source_wrap_html5') || $(this).hasClass('video_source_upload_wrap_html5');
    var autofill_url = $(this).data('autofill_url');
    $(this).data('autofill_url', null);
    var video_url = is_wp_media ? $(this).find('span').data('video_url') : autofill_url || e.originalEvent.clipboardData.getData('text');

    var toggle_loading = function toggle_loading(show) {
      if (!show) {
        duration_label.find('img').remove();
        return;
      } // Show loading icon


      if (duration_label.find('img').length == 0) {
        duration_label.append(' <img src="' + window._tutorobject.loading_icon_url + '" style="display:inline-block"/>');
      }
    };

    var set_duration = function set_duration(sec_num) {
      var hours = Math.floor(sec_num / 3600);
      var minutes = Math.floor((sec_num - hours * 3600) / 60);
      var seconds = Math.round(sec_num - hours * 3600 - minutes * 60);

      if (hours < 10) {
        hours = "0" + hours;
      }

      if (minutes < 10) {
        minutes = "0" + minutes;
      }

      if (seconds < 10) {
        seconds = "0" + seconds;
      }

      var fragments = [hours, minutes, seconds];
      var time_fields = root.find('input');

      for (var i = 0; i < 3; i++) {
        time_fields.eq(i).val(fragments[i]);
      }
    };

    var yt_to_seconds = function yt_to_seconds(duration) {
      var match = duration.match(/PT(\d+H)?(\d+M)?(\d+S)?/);
      match = match.slice(1).map(function (x) {
        if (x != null) {
          return x.replace(/\D/, '');
        }
      });
      var hours = parseInt(match[0]) || 0;
      var minutes = parseInt(match[1]) || 0;
      var seconds = parseInt(match[2]) || 0;
      return hours * 3600 + minutes * 60 + seconds;
    };

    if (is_wp_media || $(this).parent().hasClass('video_source_wrap_external_url')) {
      var player = document.createElement('video');
      player.addEventListener('loadedmetadata', function () {
        set_duration(player.duration);
        toggle_loading(false);
      });
      toggle_loading(true);
      player.src = video_url;
    } else if ($(this).parent().hasClass('video_source_wrap_vimeo')) {
      var regExp = /^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/;
      var match = video_url.match(regExp);
      var video_id = match ? match[5] : null;

      if (video_id) {
        toggle_loading(true);
        $.getJSON('http://vimeo.com/api/v2/video/' + video_id + '/json', function (data) {
          if (Array.isArray(data) && data[0] && data[0].duration !== undefined) {
            set_duration(data[0].duration);
          }

          toggle_loading(false);
        });
      }
    } else if ($(this).parent().hasClass('video_source_wrap_youtube')) {
      var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/;
      var match = video_url.match(regExp);
      var video_id = match && match[7].length == 11 ? match[7] : false;
      var api_key = $(this).data('youtube_api_key');

      if (video_id && api_key) {
        var result_url = 'https://www.googleapis.com/youtube/v3/videos?id=' + video_id + '&key=' + api_key + '&part=contentDetails';
        toggle_loading(true);
        $.getJSON(result_url, function (data) {
          if (_typeof(data) == 'object' && data.items && data.items[0] && data.items[0].contentDetails && data.items[0].contentDetails.duration) {
            set_duration(yt_to_seconds(data.items[0].contentDetails.duration));
          }

          toggle_loading(false);
        });
      }
    }
  }).on('input', video_url_input, function () {
    if (autofill_url_timeout) {
      clearTimeout(autofill_url_timeout);
    }

    var $this = $(this);
    autofill_url_timeout = setTimeout(function () {
      var val = $this.val();
      val = val ? val.trim() : '';
      console.log('Trigger', val);
      val ? $this.data('autofill_url', val).trigger('paste') : 0;
    }, 700);
  });
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
/**
 * Add option disable when don't need to add an option
 * 
 * @since 1.9.7
 */


window.disableAddoption = function () {
  var selected_question_type = document.querySelector(".tutor_select_value_holder").value;
  var question_answers = document.getElementById("tutor_quiz_question_answers");
  var question_answer_form = document.getElementById("tutor_quiz_question_answer_form");
  var add_question_answer_option = document.querySelector(".add_question_answers_option");

  var addDisabledClass = function addDisabledClass(elem) {
    if (!elem.classList.contains("disabled")) {
      elem.classList.add('disabled');
    }
  };

  var removeDisabledClass = function removeDisabledClass(elem) {
    if (elem.classList.contains("disabled")) {
      elem.classList.remove('disabled');
    }
  }; //dont need add option for open_ended & short_answer


  if (selected_question_type === 'open_ended' || selected_question_type === 'short_answer') {
    addDisabledClass(add_question_answer_option);
  } else if (selected_question_type === 'true_false' || selected_question_type === 'fill_in_the_blank') {
    //if already have options then dont need to show add option
    if (question_answer_form.hasChildNodes() || question_answers.hasChildNodes()) {
      addDisabledClass(add_question_answer_option);
    } else {
      removeDisabledClass(add_question_answer_option);
    }
  } else {
    //if other question type then remove disabled
    removeDisabledClass(add_question_answer_option);
  }
};

/***/ }),

/***/ "./assets/react/lib/utilities.js":
/*!***************************************!*\
  !*** ./assets/react/lib/utilities.js ***!
  \***************************************/
/***/ (() => {

window.jQuery(document).ready(function ($) {
  var __ = wp.i18n.__;
  $(document).on('click', '.tutor-copy-text', function (e) {
    // Prevent default action
    e.stopImmediatePropagation();
    e.preventDefault(); // Get the text

    var text = $(this).data('text'); // Create input to place texts in

    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val(text).select();
    document.execCommand("copy");
    $temp.remove();
    tutor_toast(__('Copied!', 'tutor'), text, 'success');
  });
});

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
/*!*****************************************************!*\
  !*** ./assets/react/admin-dashboard/tutor-setup.js ***!
  \*****************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _lib_common__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../lib/common */ "./assets/react/lib/common.js");


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

jQuery(document).ready(function ($) {
  "use strict";

  var url = window.location.href;

  if (url.indexOf('#') > 0) {
    $(".tutor-wizard-container > div").removeClass("active");
    $(".tutor-wizard-container > div.tutor-setup-wizard-settings").addClass("active");
    var split_data = url.split("#");

    if (split_data[1]) {
      var _length = $(".tutor-setup-title li." + split_data[1]).index();

      $(".tutor-setup-title li").removeClass("current");
      $(".tutor-setup-content li").removeClass("active");

      for (var index = 0; index <= _length; index++) {
        $(".tutor-setup-title li").eq(index).addClass('active');

        if (_length == index) {
          $(".tutor-setup-title li").eq(index).addClass("current");
          $(".tutor-setup-content li").eq(index).addClass("active");
        }
      }
    }

    var enable = $("input[name='enable_course_marketplace'").val();
    showHide(enable ? enable : 0);
  }

  $(".tutor-setup-title li").on("click", function (e) {
    e.preventDefault();

    var _length = $(this).closest("li").index();

    $(".tutor-setup-title li").removeClass("active current");
    $(".tutor-setup-title li").eq(_length).addClass("active current");
    $(".tutor-setup-content li").removeClass("active");
    $(".tutor-setup-content li").eq(_length).addClass("active");
    window.location.hash = $("ul.tutor-setup-title li").eq(_length).data("url");

    for (var _index2 = 0; _index2 <= _length; _index2++) {
      $(".tutor-setup-title li").eq(_index2).addClass('active');
    }
  });
  /* ---------------------
  * Wizard Skip
  * ---------------------- */

  $(".tutor-boarding-next, .tutor-boarding-skip").on("click", function (e) {
    e.preventDefault();
    $(".tutor-setup-wizard-boarding").removeClass("active");
    $(".tutor-setup-wizard-type").addClass("active");
  });
  $(".tutor-type-next, .tutor-type-skip").on("click", function (e) {
    e.preventDefault();
    $(".tutor-setup-wizard-type").removeClass("active");
    $(".tutor-setup-wizard-settings").addClass("active");
    $('.tutor-setup-title li').eq(0).addClass('active');
    window.location.hash = "general";
    showHide($("input[name='enable_course_marketplace_setup']:checked").val());
  });
  /* ---------------------
  * Marketplace Type
  * ---------------------- */

  $("input[type=radio][name=enable_course_marketplace_setup]").change(function () {
    if (this.value == "0") {
      $("input[name=enable_course_marketplace]").val("");
      $("input[name=enable_tutor_earning]").val("");
    } else if (this.value == "1") {
      $("input[name=enable_course_marketplace]").val("1");
      $("input[name=enable_tutor_earning]").val("1");
    }
  });
  /* ---------------------
  * Wizard Action
  * ---------------------- */

  $(".tutor-setup-previous").on("click", function (e) {
    e.preventDefault();

    var _index = $(this).closest("li").index();

    $("ul.tutor-setup-title li").eq(_index).removeClass("active");

    if (_index > 0 && _index == $('.tutor-setup-title li.instructor').index() + 1 && $('.tutor-setup-title li.instructor').hasClass('hide-this')) {
      _index = _index - 1;
    }

    if (_index > 0) {
      $("ul.tutor-setup-title li").eq(_index - 1).addClass("active");
      $("ul.tutor-setup-content li").removeClass("active").eq(_index - 1).addClass("active");
      $("ul.tutor-setup-title li").removeClass("current").eq(_index - 1).addClass("current");
      window.location.hash = $("ul.tutor-setup-title li").eq(_index - 1).data('url');
    } else {
      $('.tutor-setup-wizard-settings').removeClass('active');
      $('.tutor-setup-wizard-type').addClass('active');
      window.location.hash = '';
    }

    setpSet();
  });
  $('.tutor-setup-type-previous').on("click", function (e) {
    $('.tutor-setup-wizard-type').removeClass('active');
    $('.tutor-setup-wizard-boarding').addClass('active');
  });
  $(".tutor-setup-skip, .tutor-setup-next").on("click", function (e) {
    e.preventDefault();

    var _index = $(this).closest("li").index() + 1;

    if (_index == $('.tutor-setup-title li.instructor').index() && $('.tutor-setup-title li.instructor').hasClass('hide-this')) {
      _index = _index + 1;
    }

    $("ul.tutor-setup-title li").eq(_index).addClass("active");
    $("ul.tutor-setup-content li").removeClass("active").eq(_index).addClass("active");
    $("ul.tutor-setup-title li").removeClass("current").eq(_index).addClass("current");
    window.location.hash = $("ul.tutor-setup-title li").eq(_index).data("url");
    setpSet();
  });
  /* ---------------------
  * Wizard Skip
  * ---------------------- */

  $(".tutor-boarding-next, .tutor-boarding-skip").on("click", function (e) {
    e.preventDefault();
    $(".tutor-setup-wizard-boarding").removeClass("active");
    $(".tutor-setup-wizard-type").addClass("active");
  });
  /* ---------------------
  * Wizard Slick Slider
  * ---------------------- */

  $(".tutor-boarding").slick({
    speed: 1000,
    centerMode: true,
    centerPadding: "19.5%",
    slidesToShow: 1,
    arrows: false,
    dots: true,
    responsive: [{
      breakpoint: 768,
      settings: {
        arrows: false,
        centerMode: true,
        centerPadding: "50px",
        slidesToShow: 1
      }
    }, {
      breakpoint: 480,
      settings: {
        arrows: false,
        centerMode: true,
        centerPadding: "30px",
        slidesToShow: 1
      }
    }]
  });
  /* ---------------------
  * Form Submit and Redirect after Finished
  * ---------------------- */

  $(".tutor-redirect").on("click", function (e) {
    var that = $(this);
    e.preventDefault();
    var formData = $("#tutor-setup-form").serializeObject();
    $.ajax({
      url: _tutorobject.ajaxurl,
      type: "POST",
      data: formData,
      success: function success(data) {
        if (data.success) {
          window.location = that.data("url");
        }
      }
    });
  });
  /* ---------------------
  * Reset Section
  * ---------------------- */

  $(".tutor-reset-section").on("click", function (e) {
    $(this).closest("li").find("input").val(function () {
      switch (this.type) {
        case "text":
          return this.defaultValue;
          break;

        case "checkbox":
        case "radio":
          this.checked = this.defaultChecked;
          break;

        case "range":
          var rangeval = $(this).closest(".limit-slider");

          if (rangeval.find(".range-input").hasClass("double-range-slider")) {
            rangeval.find(".range-value-1").html(this.defaultValue + "%");
            $(".range-value-data-1").val(this.defaultValue);
            rangeval.find(".range-value-2").html(100 - this.defaultValue + "%");
            $(".range-value-data-2").val(100 - this.defaultValue);
          } else {
            rangeval.find(".range-value").html(this.defaultValue);
            return this.defaultValue;
          }

          break;

        case "hidden":
          return this.value;
          break;
      }
    });
  });
  /* ---------------------
  * Wizard Tooltip
  * ---------------------- */

  $(".tooltip-btn").on("click", function (e) {
    e.preventDefault();
    $(this).toggleClass("active");
  });
  /* ---------------------
  * on/of emphasizing after input check click
  * ---------------------- */

  $(".input-switchbox").each(function () {
    inputCheckEmphasizing($(this));
  });

  function inputCheckEmphasizing(th) {
    var checkboxRoot = th.parent().parent();

    if (th.prop("checked")) {
      checkboxRoot.find(".label-on").addClass("active");
      checkboxRoot.find(".label-off").removeClass("active");
    } else {
      checkboxRoot.find(".label-on").removeClass("active");
      checkboxRoot.find(".label-off").addClass("active");
    }
  }

  $(".input-switchbox").click(function () {
    inputCheckEmphasizing($(this));
  });
  /* ---------------------
  * Select Option
  * ---------------------- */

  $(".selected").on("click", function () {
    $(".options-container").toggleClass("active");
  });
  $(".option").each(function () {
    $(this).on("click", function () {
      $(".selected").html($(this).find("label").html());
      $(".options-container").removeClass("active");
    });
  });
  /* ---------------------
  * Time Limit sliders
  * ---------------------- */

  $(".range-input").on("change mousemove", function (e) {
    var rangeInput = $(this).val();
    var rangeValue = $(this).parent().parent().find(".range-value");
    rangeValue.text(rangeInput);
  });
  $(".double-range-slider").on("change mousemove", function () {
    var selector = $(this).closest(".settings");
    selector.find(".range-value-1").text($(this).val() + "%");
    selector.find('input[name="earning_instructor_commission"]').val($(this).val());
    selector.find(".range-value-2").text(100 - $(this).val() + "%");
    selector.find('input[name="earning_admin_commission"]').val(100 - $(this).val());
  });
  $("#attempts-allowed-1").on("click", function (e) {
    if ($("#attempts-allowed-numer").prop("disabled", true)) {
      $(this).parent().parent().parent().addClass("active");
      $("#attempts-allowed-numer").prop("disabled", false);
    }
  });
  $("#attempts-allowed-2").on("click", function (e) {
    if ($("#attempts-allowed-2").is(":checked")) {
      $(this).parent().parent().parent().removeClass("active");
      $("#attempts-allowed-numer").prop("disabled", true);
    }
  });
  $('.wizard-type-item').on('click', function (e) {
    showHide($(this).find('input').val());
  });

  function showHide(val) {
    if (val == 1) {
      $('.tutor-show-hide').addClass('active');
      $('.tutor-setup-title li.instructor').removeClass('hide-this');
      $('.tutor-setup-content li').eq($('.tutor-setup-title li.instructor')).removeClass('hide-this');
    } else {
      $('.tutor-show-hide').removeClass('active');
      $('.tutor-setup-title li.instructor').addClass('hide-this');
      $('.tutor-setup-content li').eq($('.tutor-setup-title li.instructor')).addClass('hide-this');
    }
  }

  setpSet();

  function setpSet() {
    if ($('.tutor-setup-title li.instructor').hasClass('hide-this')) {
      $('.tutor-steps').html(5);

      var _index = $('.tutor-setup-title li.current').index();

      if (_index > 2) {
        $('.tutor-setup-content li.active .tutor-steps-current').html(_index);
      }
    } else {
      $('.tutor-steps').html(6);
      $(".tutor-setup-content li").each(function () {
        $(this).find('.tutor-steps-current').html($(this).index() + 1);
      });
    }
  }
  /* ---------------------
  * Attempt Allowed
  * ---------------------- */


  $("input[name='attempts-allowed']").on('change', function (e) {
    var _val = $(this).filter(':checked').val();

    if (_val == 'unlimited') {
      $("input[name='quiz_attempts_allowed']").val(0);
    } else {
      $("input[name='quiz_attempts_allowed']").val($("input[name='attempts-allowed-number").val());
    }
  });
  $("input[name='attempts-allowed-number']").on('change', function (e) {
    $("input[name='quiz_attempts_allowed']").val($(this).val());
  });
  $("input[name='attempts-allowed-number']").on('focus', function (e) {
    $("input[name='attempts-allowed'][value='single']").attr('checked', true);
  });
});
})();

/******/ })()
;
//# sourceMappingURL=tutor-setup.js.map
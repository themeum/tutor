/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/react/course-builder/assignment.js":
/*!***************************************************!*\
  !*** ./assets/react/course-builder/assignment.js ***!
  \***************************************************/
/***/ (() => {

window.jQuery(document).ready(function ($) {
  var _wp$i18n = wp.i18n,
      __ = _wp$i18n.__,
      _x = _wp$i18n._x,
      _n = _wp$i18n._n,
      _nx = _wp$i18n._nx;
  /**
   * Update Assignment Data
   */

  $(document).on('click', '.update_assignment_modal_btn', function (event) {
    event.preventDefault();
    var $that = $(this);
    var content;
    var inputid = 'tutor_assignments_modal_editor';
    var editor = tinyMCE.get(inputid);

    if (editor) {
      content = editor.getContent();
    } else {
      content = $('#' + inputid).val();
    }

    var form_data = $(this).closest('form').serializeObject();
    form_data.assignment_content = content;
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
          enable_sorting_topic_lesson(); //Close the modal

          $('.tutor-lesson-modal-wrap').removeClass('show');
          tutor_toast(__('Assignment Updated', 'tutor'), $that.data('toast_success_message'), 'success');
        }
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
});

/***/ }),

/***/ "./assets/react/course-builder/attachment.js":
/*!***************************************************!*\
  !*** ./assets/react/course-builder/attachment.js ***!
  \***************************************************/
/***/ (() => {

window.jQuery(document).ready(function ($) {
  var _wp$i18n = wp.i18n,
      __ = _wp$i18n.__,
      _x = _wp$i18n._x,
      _n = _wp$i18n._n,
      _nx = _wp$i18n._nx;
  $(document).on('click', '.tutor-attachment-cards .tutor-delete-attachment', function (e) {
    e.preventDefault();
    $(this).closest('[data-attachment_id]').remove();
  });
  $(document).on('click', '.tutorUploadAttachmentBtn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var frame; // If the media frame already exists, reopen it.

    if (frame) {
      frame.open();
      return;
    } // Create a new media frame


    frame = wp.media({
      title: __('Select or Upload Media Of Your Choice', 'tutor'),
      button: {
        text: __('Upload media', 'tutor')
      },
      multiple: true // Set to true to allow multiple files to be selected

    }); // When an image is selected in the media frame...

    frame.on('select', function () {
      // Get media attachment details from the frame state
      var attachments = frame.state().get('selection').toJSON();

      if (attachments.length) {
        for (var i = 0; i < attachments.length; i++) {
          var attachment = attachments[i];
          var inputHtml = "<div data-attachment_id=\"".concat(attachment.id, "\">\n                        <div>\n                            <a href=\"").concat(attachment.url, "\" target=\"_blank\">\n                                ").concat(attachment.filename, "\n                            </a>\n                            <input type=\"hidden\" name=\"tutor_attachments[]\" value=\"").concat(attachment.id, "\">\n                        </div>\n                        <div>\n                            <span class=\"filesize\">").concat(__('Size', 'tutor'), ": ").concat(attachment.filesizeHumanReadable, "</span>\n                            <span class=\"tutor-delete-attachment tutor-icon-line-cross\"></span>\n                        </div>\n                    </div>");
          $that.closest('.tutor-attachments-metabox').find('.tutor-attachment-cards').append(inputHtml);
        }
      }
    }); // Finally, open the modal on click

    frame.open();
  });
});

/***/ }),

/***/ "./assets/react/course-builder/lesson.js":
/*!***********************************************!*\
  !*** ./assets/react/course-builder/lesson.js ***!
  \***********************************************/
/***/ (() => {

(function ($) {
  window.enable_sorting_topic_lesson = function () {
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
  };

  window.tutor_sorting_topics_and_lesson = function () {
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
  };
})(window.jQuery);

window.jQuery(document).ready(function ($) {
  var _wp$i18n = wp.i18n,
      __ = _wp$i18n.__,
      _x = _wp$i18n._x,
      _n = _wp$i18n._n,
      _nx = _wp$i18n._nx;
  enable_sorting_topic_lesson();
  /**
   * Open Lesson Modal
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
        $('.tutor-lesson-modal-wrap').addClass('tutor-is-active');
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
  }); // Video source 

  $(document).on('change', '.tutor_lesson_video_source', function (e) {
    $(this).nextAll().hide().filter('.video_source_wrap_' + $(this).val()).show();
  }); // Update lesson

  $(document).on('click', '.update_lesson_modal_btn', function (event) {
    event.preventDefault();
    var $that = $(this);
    var content;
    var inputid = 'tutor_lesson_modal_editor';
    var editor = tinyMCE.get(inputid);

    if (editor) {
      content = editor.getContent();
    } else {
      content = $('#' + inputid).val();
    }

    var form_data = $(this).closest('.tutor-modal').find('form').serializeObject();
    form_data.lesson_content = content;
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
          enable_sorting_topic_lesson(); //Close the modal

          $that.closest('.tutor-modal').removeClass('tutor-is-active');
          tutor_toast(__('Success', 'tutor'), __('Lesson Updated', 'tutor'), 'success');
        }
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
});

/***/ }),

/***/ "./assets/react/course-builder/quiz.js":
/*!*********************************************!*\
  !*** ./assets/react/course-builder/quiz.js ***!
  \*********************************************/
/***/ (() => {

window.jQuery(document).ready(function ($) {
  // Quiz modal next click
  $(document).on('click', '.tutor-quiz-builder-modal-wrap [data-action="next"]', function (e) {
    var container = $(this).closest('.tutor-modal');
    var quiz_title = container.find('[name="quiz_title"]').val();
    var topic_id = container.find('[name="current_topic_id_for_quiz"]').val();
    var course_id;

    switch ($(this).closest('.tutor-modal').attr('data-target')) {
      // Save quiz title and description
      case 'quiz-builder-tab-quiz-info': // 

    }

    var $that = $(this);
    var quiz_description = $('[name="quiz_description"]').val();
    var course_id = $('#post_ID').val();

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
});

/***/ }),

/***/ "./assets/react/course-builder/topic.js":
/*!**********************************************!*\
  !*** ./assets/react/course-builder/topic.js ***!
  \**********************************************/
/***/ (() => {

window.jQuery(document).ready(function ($) {
  $(document).on('click', '#tutor-add-topic-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var container = $that.closest('.tutor-metabox-add-topics');
    var form_data = container.find('input, textarea').serializeObject();
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
          container.find('input[type!="hidden"], textarea').each(function () {
            $(this).val('');
          });
          container.removeClass('tutor-is-active');
          enable_sorting_topic_lesson();
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
/*!**********************************************!*\
  !*** ./assets/react/course-builder/index.js ***!
  \**********************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _topic__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./topic */ "./assets/react/course-builder/topic.js");
/* harmony import */ var _topic__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_topic__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _lesson__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./lesson */ "./assets/react/course-builder/lesson.js");
/* harmony import */ var _lesson__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_lesson__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _quiz__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./quiz */ "./assets/react/course-builder/quiz.js");
/* harmony import */ var _quiz__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_quiz__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _assignment__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./assignment */ "./assets/react/course-builder/assignment.js");
/* harmony import */ var _assignment__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_assignment__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _attachment__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./attachment */ "./assets/react/course-builder/attachment.js");
/* harmony import */ var _attachment__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_attachment__WEBPACK_IMPORTED_MODULE_4__);





})();

/******/ })()
;
//# sourceMappingURL=tutor-course-builder.js.map
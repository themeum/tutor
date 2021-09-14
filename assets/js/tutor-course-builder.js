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
/* harmony import */ var _assignment__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./assignment */ "./assets/react/course-builder/assignment.js");
/* harmony import */ var _assignment__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_assignment__WEBPACK_IMPORTED_MODULE_2__);



})();

/******/ })()
;
//# sourceMappingURL=tutor-course-builder.js.map
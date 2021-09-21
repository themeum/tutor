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
      _nx = _wp$i18n._nx; // Create/edit assignment opener

  $(document).on('click', '.open-tutor-assignment-modal, .tutor-create-assignments-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var assignment_id = $that.hasClass('tutor-create-assignments-btn') ? 0 : $that.attr('data-assignment-id');
    var topic_id = $that.closest('.tutor-topics-wrap').data('topic-id');
    var course_id = $('#post_ID').val();
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        assignment_id: assignment_id,
        topic_id: topic_id,
        course_id: course_id,
        action: 'tutor_load_assignments_builder_modal'
      },
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        $('.tutor-assignment-modal-wrap .modal-container').html(data.data.output);
        $('.tutor-assignment-modal-wrap').addClass('tutor-is-active');
        $(document).trigger('assignment_modal_loaded');
        tinymce.init(tinyMCEPreInit.mceInit.course_description);
        tinymce.execCommand('mceRemoveEditor', false, 'tutor_assignments_modal_editor');
        tinyMCE.execCommand('mceAddEditor', false, "tutor_assignments_modal_editor");
      },
      complete: function complete() {
        quicktags({
          id: "tutor_assignments_modal_editor"
        });
        $that.removeClass('tutor-updating-message');
      }
    });
  });
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

    var form_data = $(this).closest('.tutor-modal').find('form.tutor_assignment_modal_form').serializeObject();
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

          $('.tutor-assignment-modal-wrap').removeClass('tutor-is-active');
          tutor_toast(__('Success', 'tutor'), __('Assignment Updated', 'tutor'), 'success');
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
    var name = $that.data('name');
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
          var inputHtml = "<div data-attachment_id=\"".concat(attachment.id, "\">\n                        <div>\n                            <a href=\"").concat(attachment.url, "\" target=\"_blank\">\n                                ").concat(attachment.filename, "\n                            </a>\n                            <input type=\"hidden\" name=\"").concat(name, "\" value=\"").concat(attachment.id, "\">\n                        </div>\n                        <div>\n                            <span class=\"filesize\">\n                                ").concat(__('Size', 'tutor'), ": ").concat(attachment.filesizeHumanReadable, "\n                            </span>\n                            <span class=\"tutor-delete-attachment tutor-icon-line-cross\"></span>\n                        </div>\n                    </div>");
          $that.parent().find('.tutor-attachment-cards').append(inputHtml);
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

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) { symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); } keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/**
 * Add option disable when don't need to add an option
 * 
 * @since 1.9.7
 */
var disableAddoption = function disableAddoption() {
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

window.jQuery(document).ready(function ($) {
  var __ = wp.i18n.__; // TAB switching

  var step_switch = function step_switch(modal, go_next, clear_next) {
    var element = modal.find('.tutor-modal-steps');
    var current = element.find('li[data-tab="' + modal.attr('data-target') + '"]');
    var next = current.next();
    var prev = current.prev();

    if (!go_next) {
      var new_tab = prev.data('tab');
      prev.length ? modal.attr('data-target', new_tab) : 0;
      clear_next ? element.find('li[data-tab="' + new_tab + '"]').nextAll().removeClass('tutor-is-completed') : 0;
      return;
    }

    if (next.length) {
      next.addClass('tutor-is-completed');
      modal.attr('data-target', next.data('tab'));
      return true;
    }

    tutor_toast(__('Success', 'tutor'), __('Quiz Updated'), 'success');
    modal.removeClass('tutor-is-active');
    return null;
  }; // Slider initiator


  var tutor_slider_init = function tutor_slider_init() {
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
  };

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
  } // Sort quiz question


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

  function tutor_select() {
    var obj = {
      init: function init() {
        $(document).on('click', '.question-type-select .tutor-select-option', function (e) {
          e.preventDefault();
          var $that = $(this);

          if ($that.attr('data-is-pro') !== 'true') {
            var $html = $that.html().trim();
            $that.closest('.question-type-select').find('.select-header .lead-option').html($html);
            $that.closest('.question-type-select').find('.select-header input.tutor_select_value_holder').val($that.attr('data-value')).trigger('change');
            $that.closest('.tutor-select-options').hide();
            disableAddoption();
          } else {
            alert('Tutor Pro version required');
          }
        });
        $(document).on('click', '.question-type-select .select-header', function (e) {
          e.preventDefault();
          var $that = $(this);
          $that.closest('.question-type-select').find('.tutor-select-options').slideToggle();
        });
        this.setValue();
        this.hideOnOutSideClick();
      },
      setValue: function setValue() {
        $('.question-type-select').each(function () {
          var $that = $(this);
          var $option = $that.find('.tutor-select-option');

          if ($option.length) {
            $option.each(function () {
              var $thisOption = $(this);

              if ($thisOption.attr('data-selected') === 'selected') {
                var $html = $thisOption.html().trim();
                $thisOption.closest('.question-type-select').find('.select-header .lead-option').html($html);
                $thisOption.closest('.question-type-select').find('.select-header input.tutor_select_value_holder').val($thisOption.attr('data-value'));
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
  tutor_slider_init(); // Create/Edit quiz opener

  $(document).on('click', '.tutor-add-quiz-btn, .open-tutor-quiz-modal, .back-to-quiz-questions-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var step_1 = $(this).hasClass('open-tutor-quiz-modal');
    var modal = $('.tutor-modal.tutor-quiz-builder-modal-wrap');
    var quiz_id = $that.hasClass('tutor-add-quiz-btn') ? 0 : $that.attr('data-quiz-id');
    var topic_id = $that.closest('.tutor-topics-wrap').data('topic-id');
    var course_id = $('#post_ID').val();
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        quiz_id: quiz_id,
        topic_id: topic_id,
        course_id: course_id,
        action: 'tutor_load_quiz_builder_modal'
      },
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        $('.tutor-quiz-builder-modal-wrap').addClass('tutor-is-active');
        $('.tutor-quiz-builder-modal-wrap .modal-container').html(data.data.output);
        $('.tutor-quiz-builder-modal-wrap').attr('data-quiz-id', quiz_id).attr('quiz-for-post-id', topic_id).addClass('show');
        modal.removeClass('tutor-has-question-from');

        if (step_1) {
          step_switch(modal, false, true);
          step_switch(modal, false, true);
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
  }); // Quiz modal next click

  $(document).on('click', '.tutor-quiz-builder-modal-wrap button', function (e) {
    // DOM findar
    var btn = $(this);
    var modal = btn.closest('.tutor-modal');
    var current_tab = modal.attr('data-target');
    var action = $(this).data('action');

    if (action == 'back') {
      step_switch(modal, false);
      return;
    } else if (action != 'next') {
      return;
    } // Quiz meta data


    var course_id = $('#post_ID').val();
    var topic_id = modal.find('[name="topic_id"]').val();
    var quiz_id = modal.find('[name="quiz_id"]').val();

    if (current_tab == 'quiz-builder-tab-quiz-info' || current_tab == 'quiz-builder-tab-settings') {
      // Save quiz info. Title and description
      var quiz_title = modal.find('[name="quiz_title"]').val();
      var quiz_description = modal.find('[name="quiz_description"]').val();
      var settings = modal.find('#quiz-builder-tab-settings :input, #quiz-builder-tab-advanced-options :input').serializeObject();
      $.ajax({
        url: window._tutorobject.ajaxurl,
        type: 'POST',
        data: _objectSpread(_objectSpread({}, settings), {}, {
          quiz_title: quiz_title,
          quiz_description: quiz_description,
          course_id: course_id,
          quiz_id: quiz_id,
          topic_id: topic_id,
          action: 'tutor_quiz_save'
        }),
        beforeSend: function beforeSend() {
          btn.addClass('tutor-updating-message');
        },
        success: function success(data) {
          if (quiz_id) {
            // Update if exists already
            $('#tutor-quiz-' + quiz_id).html(data.data.output_quiz_row);
          } else {
            // Otherwise create new row
            $('#tutor-topics-' + topic_id + ' .tutor-lessons').append(data.data.output_quiz_row);
          } // Update modal content


          $('.tutor-quiz-builder-modal-wrap .modal-container').html(data.data.output);
          $(document).trigger('quiz_modal_loaded', {
            topic_id: topic_id,
            course_id: course_id
          });
          tutor_slider_init();
          step_switch(modal, true);
        },
        complete: function complete() {
          btn.removeClass('tutor-updating-message');
        }
      });
    } else if (current_tab == 'quiz-builder-tab-questions') {
      step_switch(modal, true);
    }
  }); // Add question

  $(document).on('click', '.tutor-quiz-open-question-form', function (e) {
    e.preventDefault();
    var $that = $(this);
    var modal = $that.closest('.tutor-modal');
    var quiz_id = modal.find('[name="quiz_id"]').val();
    var topic_id = modal.find('[name="topic_id"]').val();
    var course_id = $('#post_ID').val();
    var question_id = $that.attr('data-question-id');
    var params = {
      quiz_id: quiz_id,
      topic_id: topic_id,
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
        modal.find('.modal-container').html(data.data.output);
        modal.addClass('tutor-has-question-from'); //Initializing Tutor Select
        // tutor_select().reInit();

        enable_quiz_answer_sorting();
        disableAddoption();
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  }); // Trash question

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
    var $formInput = $('#tutor-quiz-question-wrapper :input').serializeObject();
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
   * Quiz Question edit save and continue
   */

  $(document).on('click', '.quiz-modal-question-save-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var modal = $that.closest('.tutor-modal');
    var $formInput = $('#tutor-quiz-question-wrapper :input').serializeObject();
    $formInput.action = 'tutor_quiz_modal_update_question';
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
          modal.find('.back-to-quiz-questions-btn').trigger('click');
        } else {
          if (typeof data.data !== 'undefined') {
            $('#quiz_validation_msg_wrap').html(data.data.validation_msg);
          }
        }
      },
      complete: function complete() {
        setTimeout(function () {
          return $that.removeClass('tutor-updating-message');
        }, 2000);
      }
    });
  }); // Quiz question answer refresh

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
   * If change question type from quiz builder question
   *
   * @since v.1.0.0
   */

  $(document).on('change', 'input.tutor_select_value_holder', function (e) {
    $('.add_question_answers_option').trigger('click');
    $('#tutor_quiz_question_answers').trigger('refresh');
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
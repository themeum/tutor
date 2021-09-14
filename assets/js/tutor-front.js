<<<<<<< HEAD
(()=>{var o={12:()=>{!function(){"use strict";document.addEventListener("click",function(t){var e="data-tutor-modal-target",o="data-tutor-modal-close";(t.target.hasAttribute(e)||t.target.closest("[".concat(e,"]")))&&(t.preventDefault(),e=(t.target.hasAttribute(e)?t.target:t.target.closest("[".concat(e,"]"))).getAttribute(e),(e=document.getElementById(e))&&e.classList.add("tutor-is-active")),(t.target.hasAttribute(o)||t.target.classList.contains("tutor-modal-overlay")||t.target.closest("[".concat(o,"]")))&&(t.preventDefault(),document.querySelectorAll(".tutor-modal.tutor-is-active").forEach(function(t){t.classList.remove("tutor-is-active")}))})}()}},n={};function a(t){var e=n[t];if(void 0!==e)return e.exports;e=n[t]={exports:{}};return o[t](e,e.exports,a),e.exports}(()=>{"use strict";a(12);function w(t){return(w="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}function b(t,e){return function(t){if(Array.isArray(t))return t}(t)||function(t,e){var o=null==t?null:"undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(null!=o){var n,a,i=[],r=!0,s=!1;try{for(o=o.call(t);!(r=(n=o.next()).done)&&(i.push(n.value),!e||i.length!==e);r=!0);}catch(t){s=!0,a=t}finally{try{r||null==o.return||o.return()}finally{if(s)throw a}}return i}}(t,e)||function(t,e){if(t){if("string"==typeof t)return n(t,e);var o=Object.prototype.toString.call(t).slice(8,-1);return"Map"===(o="Object"===o&&t.constructor?t.constructor.name:o)||"Set"===o?Array.from(t):"Arguments"===o||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(o)?n(t,e):void 0}}(t,e)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function n(t,e){(null==e||e>t.length)&&(e=t.length);for(var o=0,n=new Array(e);o<e;o++)n[o]=t[o];return n}function y(t){var e=window._tutorobject||{},o=e.nonce_key||"",e=e[o]||"";return t?{key:o,value:e}:(t=e,(e=o)in(o={})?Object.defineProperty(o,e,{value:t,enumerable:!0,configurable:!0,writable:!0}):o[e]=t,o)}function q(){function t(t){t.classList.contains("disabled")||t.classList.add("disabled")}function e(t){t.classList.contains("disabled")&&t.classList.remove("disabled")}var o=document.querySelector(".tutor_select_value_holder").value,n=document.getElementById("tutor_quiz_question_answers"),a=document.getElementById("tutor_quiz_question_answer_form"),i=document.querySelector(".add_question_answers_option");("open_ended"===o||"short_answer"===o||("true_false"===o||"fill_in_the_blank"===o)&&(a.hasChildNodes()||n.hasChildNodes())?t:e)(i)}window.tutor_popup=function(r,o,n){var s,u=this;return this.popup_wrapper=function(t){var e=""===o?"":'<img class="tutor-pop-icon" src="'+window._tutorobject.tutor_url+"assets/images/"+o+'.svg"/>';return"<"+t+' class="tutor-component-popup-container">            <div class="tutor-component-popup-'+n+'">                <div class="tutor-component-content-container">'+e+'</div>                <div class="tutor-component-button-container"></div>            </div>        </'+t+">"},this.popup=function(o){var t=o.title?"<h3>"+o.title+"</h3>":"",e=o.description?"<p>"+o.description+"</p>":"",n=Object.keys(o.buttons||{}).map(function(t){var e=o.buttons[t],t=e.id?"tutor-popup-"+e.id:"";return r('<button id="'+t+'" class="tutor-button tutor-button-'+e.class+'">'+e.title+"</button>").click(e.callback)}),a=(s=r(u.popup_wrapper(o.wrapper_tag||"div"))).find(".tutor-component-content-container");a.append(t),o.after_title&&a.append(o.after_title),a.append(e),o.after_description&&a.append(o.after_description),s.click(function(){r(this).remove()}).children().click(function(t){t.stopPropagation()});for(var i=0;i<n.length;i++)s.find(".tutor-component-button-container").append(n[i]);return r("body").append(s),s},{popup:this.popup}},window.tutorDotLoader=function(t){return'    \n    <div class="tutor-dot-loader '.concat(t||"",'">\n        <span class="dot dot-1"></span>\n        <span class="dot dot-2"></span>\n        <span class="dot dot-3"></span>\n        <span class="dot dot-4"></span>\n    </div>')},window.tutor_date_picker=function(){var t;jQuery.datepicker&&(t=_tutorobject.wp_date_format||"yy-mm-dd",$(".tutor_date_picker").datepicker({dateFormat:t}))},jQuery(document).ready(function(l){var t=wp.i18n,a=t.__;t._x,t._n,t._nx;function o(){var t;jQuery.datepicker&&(t=_tutorobject.wp_date_format||"yy-mm-dd",l(".tutor_date_picker").datepicker({dateFormat:t}))}function s(){l(".tutor-field-slider").each(function(){var t=l(this),o=t.closest(".tutor-field-type-slider").find('input[type="hidden"]'),n=t.closest(".tutor-field-type-slider").find(".tutor-field-type-slider-value"),e=parseFloat(t.closest(".tutor-field-type-slider").attr("data-min")),a=parseFloat(t.closest(".tutor-field-type-slider").attr("data-max"));t.slider({range:"max",min:e,max:a,value:o.val(),slide:function(t,e){n.text(e.value),o.val(e.value)}})})}function e(t){var e=t.element;return l('<span><i class="tutor-icon-'+l(e).data("icon")+'"></i> '+t.text+"</span>")}function i(){var i={};l(".tutor-topics-wrap").each(function(t,e){var o=l(this),n=parseInt(o.attr("id").match(/\d+/)[0],10),a={};o.find(".course-content-item").each(function(t,e){var o=l(this),o=parseInt(o.attr("id").match(/\d+/)[0],10);a[t]=o}),i[t]={topic_id:n,lesson_ids:a}}),l("#tutor_topics_lessons_sorting").val(JSON.stringify(i))}function r(){return{init:function(){l(document).on("click",".tutor-select .tutor-select-option",function(t){t.preventDefault();var e=l(this);"true"!==e.attr("data-is-pro")?(t=e.html().trim(),e.closest(".tutor-select").find(".select-header .lead-option").html(t),e.closest(".tutor-select").find(".select-header input.tutor_select_value_holder").val(e.attr("data-value")).trigger("change"),e.closest(".tutor-select-options").hide(),q()):alert("Tutor Pro version required")}),l(document).on("click",".tutor-select .select-header",function(t){t.preventDefault(),l(this).closest(".tutor-select").find(".tutor-select-options").slideToggle()}),this.setValue(),this.hideOnOutSideClick()},setValue:function(){l(".tutor-select").each(function(){var t=l(this).find(".tutor-select-option");t.length&&t.each(function(){var t,e=l(this);"selected"===e.attr("data-selected")&&(t=e.html().trim(),e.closest(".tutor-select").find(".select-header .lead-option").html(t),e.closest(".tutor-select").find(".select-header input.tutor_select_value_holder").val(e.attr("data-value")))})})},hideOnOutSideClick:function(){l(document).mouseup(function(t){var e=l(".tutor-select-options");l(t.target).closest(".select-header").length||e.is(t.target)||0!==e.has(t.target).length||e.hide()})},reInit:function(){this.setValue()}}}o(),s(),jQuery().select2&&l(".videosource_select2").select2({width:"100%",templateSelection:e,templateResult:e,allowHtml:!0}),l(document).on("change",".tutor_lesson_video_source",function(t){var e=l(this),o=l(this).val();o?l(".video-metabox-source-input-wrap").show():l(".video-metabox-source-input-wrap").hide(),e.closest(".tutor-option-field").find(".video-metabox-source-item").hide(),e.closest(".tutor-option-field").find(".video_source_wrap_"+o).show()}),l(document).on("click",".tutor-course-thumbnail-upload-btn",function(t){t.preventDefault();var e,o=l(this);e||(e=wp.media({title:a("Select or Upload Media Of Your Chosen Persuasion","tutor"),button:{text:a("Use this media","tutor")},multiple:!1})).on("select",function(){var t=e.state().get("selection").first().toJSON();o.closest(".tutor-thumbnail-wrap").find(".thumbnail-img").attr("src",t.url),o.closest(".tutor-thumbnail-wrap").find("input").val(t.id),l(".tutor-course-thumbnail-delete-btn").show()}),e.open()}),l(document).on("click",".tutor-course-thumbnail-delete-btn",function(t){t.preventDefault();var e=l(this),t=e.closest(".tutor-thumbnail-wrap").find(".thumbnail-img").attr("data-placeholder-src");e.closest(".tutor-thumbnail-wrap").find(".thumbnail-img").attr("src",t),e.closest(".tutor-thumbnail-wrap").find("input").val(""),l(".tutor-course-thumbnail-delete-btn").hide()}),l(".tutor-zoom-meeting-modal-wrap").on("submit",".tutor-meeting-modal-form",function(t){t.preventDefault();var e=l(this),o=e.serializeObject(),t=Intl.DateTimeFormat().resolvedOptions().timeZone;o.timezone=t;var n=e.find('button[type="submit"]');l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:o,beforeSend:function(){n.addClass("tutor-updating-message")},success:function(t){t.success?tutor_toast(a("Success","tutor"),n.data("toast_success_message"),"success"):tutor_toast(a("Update Error","tutor"),a("Meeting Update Failed","tutor"),"error"),t.course_contents?(l(t.selector).html(t.course_contents),"#tutor-course-content-wrap"==t.selector&&jQuery().sortable&&(l(".course-contents").sortable({handle:".course-move-handle",start:function(t,e){e.placeholder.css("visibility","visible")},stop:function(t,e){i()}}),l(".tutor-lessons:not(.drop-lessons)").sortable({connectWith:".tutor-lessons",items:"div.course-content-item",start:function(t,e){e.placeholder.css("visibility","visible")},stop:function(t,e){i()}})),l(".tutor-zoom-meeting-modal-wrap").removeClass("show")):location.reload()},complete:function(){n.removeClass("tutor-updating-message")}})}),l(document).on("change keyup",".course-edit-topic-title-input",function(t){t.preventDefault(),l(this).closest(".tutor-topics-top").find(".topic-inner-title").html(l(this).val())}),l(document).on("click",".tutor-topics-edit-button",function(t){t.preventDefault();var e=l(this),o=e.closest(".tutor-topics-wrap").find('[name="topic_id"]').val(),n=e.closest(".tutor-topics-wrap").find('[name="topic_title"]').val(),t=e.closest(".tutor-topics-wrap").find('[name="topic_summery"]').val();l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{topic_title:n,topic_summery:t,topic_id:o,action:"tutor_update_topic"},beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){t.success&&(e.closest(".tutor-topics-wrap").find("span.topic-inner-title").text(n),e.closest(".tutor-modal").removeClass("tutor-is-active"))},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("click",".lesson_thumbnail_upload_btn",function(t){t.preventDefault();var e,o=l(this);e||(e=wp.media({title:a("Select or Upload Media Of Your Chosen Persuasion","tutor"),button:{text:a("Use this media","tutor")},multiple:!1})).on("select",function(){var t=e.state().get("selection").first().toJSON();o.closest(".tutor-thumbnail-wrap").find(".thumbnail-img").html('<img src="'+t.url+'" alt="" /><a href="javascript:;" class="tutor-lesson-thumbnail-delete-btn"><i class="tutor-icon-line-cross"></i></a>'),o.closest(".tutor-thumbnail-wrap").find("input").val(t.id),l(".tutor-lesson-thumbnail-delete-btn").show()}),e.open()}),l(document).on("click",".tutor-lesson-thumbnail-delete-btn",function(t){t.preventDefault();t=l(this);t.closest(".tutor-thumbnail-wrap").find("._lesson_thumbnail_id").val(""),t.closest(".tutor-thumbnail-wrap").find(".thumbnail-img").html(""),t.hide()}),l(document).on("click",".tutor-delete-lesson-btn",function(t){var e;t.preventDefault(),confirm(a("Are you sure?","tutor"))&&(t=(e=l(this)).attr("data-lesson-id"),l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{lesson_id:t,action:"tutor_delete_lesson_by_id"},beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){t.success&&e.closest(".course-content-item").remove()},complete:function(){e.removeClass("tutor-updating-message")}}))}),l(document).on("click",".quiz-modal-btn-first-step",function(t){t.preventDefault();var e,o,n,a=l(this),i=l('[name="quiz_title"]'),r=i.val(),t=l('[name="quiz_description"]').val();r?(i.closest(".tutor-quiz-builder-group").find(".quiz_form_msg").html(""),e=l("#post_ID").val(),o=a.closest(".tutor-modal-wrap").attr("quiz-for-post-id"),l("#tutor_quiz_builder_quiz_id").length?(n=l("#tutor_quiz_builder_quiz_id").val(),l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{quiz_title:r,quiz_description:t,quiz_id:n,topic_id:o,action:"tutor_quiz_builder_quiz_update"},beforeSend:function(){a.addClass("tutor-updating-message")},success:function(t){l("#tutor-quiz-"+n).html(t.data.output_quiz_row),l('#tutor-quiz-modal-tab-items-wrap a[href="#quiz-builder-tab-questions"]').trigger("click"),s()},complete:function(){a.removeClass("tutor-updating-message")}})):l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{quiz_title:r,quiz_description:t,course_id:e,topic_id:o,action:"tutor_create_quiz_and_load_modal"},beforeSend:function(){a.addClass("tutor-updating-message")},success:function(t){l(".tutor-quiz-builder-modal-wrap .modal-container").html(t.data.output),l("#tutor-topics-"+o+" .tutor-lessons").append(t.data.output_quiz_row),l('#tutor-quiz-modal-tab-items-wrap a[href="#quiz-builder-tab-questions"]').trigger("click"),s(),l(document).trigger("quiz_modal_loaded",{topic_id:o,course_id:e})},complete:function(){a.removeClass("tutor-updating-message")}})):i.closest(".tutor-quiz-builder-group").find(".quiz_form_msg").html("Please enter quiz title")}),l(document).on("click",".open-tutor-quiz-modal",function(t){t.preventDefault();var e=l(this),o=e.attr("data-quiz-id"),n=e.attr("data-topic-id");null==n&&(n=e.closest(".tutor-modal-wrap").attr("quiz-for-post-id"));var a=l("#post_ID").val();l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{quiz_id:o,topic_id:n,course_id:a,action:"tutor_load_edit_quiz_modal"},beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){l(".tutor-quiz-builder-modal-wrap .modal-container").html(t.data.output),l(".tutor-quiz-builder-modal-wrap").attr("data-quiz-id",o).attr("quiz-for-post-id",n).addClass("show"),e.attr("data-back-to-tab")&&(t=e.attr("data-back-to-tab"),l('#tutor-quiz-modal-tab-items-wrap a[href="'+t+'"]').trigger("click")),l(document).trigger("quiz_modal_loaded",{quiz_id:o,topic_id:n,course_id:a}),s(),jQuery().sortable&&l(".quiz-builder-questions-wrap").sortable({handle:".question-sorting",start:function(t,e){e.placeholder.css("visibility","visible")},stop:function(t,e){var n;n={},l(".quiz-builder-question-wrap").each(function(t,e){var o=l(this),o=parseInt(o.attr("data-question-id"),10);n[t]=o}),l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{sorted_question_ids:n,action:"tutor_quiz_question_sorting"}})}})},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("click",".quiz-modal-settings-save-btn",function(t){t.preventDefault();var e=l(this),o=l(".tutor-quiz-builder-modal-wrap").attr("data-quiz-id"),n=l("#current_topic_id_for_quiz").val(),t=l("#quiz-builder-tab-settings :input, #quiz-builder-tab-advanced-options :input").serializeObject();t.topic_id=n,t.quiz_id=o,t.action="tutor_quiz_modal_update_settings",l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){t.success&&l("#tutor-course-content-wrap").html(t.data.course_contents),t.success?tutor_toast(a("Success","tutor"),e.data("toast_success_message"),"success"):tutor_toast(a("Update Error","tutor"),a("Quiz Update Failed","tutor"),"error")},complete:function(){e.removeClass("tutor-updating-message"),"modal_close"===e.attr("data-action")&&l(".tutor-modal-wrap").removeClass("show")}})}),l(document).on("click",".quiz-modal-question-save-btn",function(t){t.preventDefault();var e=l(this),o=l(".quiz_question_form :input").serializeObject();o.action="tutor_quiz_modal_update_question";t=e.closest(".tutor-modal-wrap").attr("quiz-for-post-id");o.topic_id=t,l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:o,beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){t.success?e.closest(".tutor-quiz-builder-modal-contents").find(".open-tutor-quiz-modal").trigger("click"):void 0!==t.data&&l("#quiz_validation_msg_wrap").html(t.data.validation_msg)},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("click",".modal-close-btn",function(t){t.preventDefault(),l(".tutor-modal-wrap").removeClass("show")}),l(document).on("keyup",function(t){27===t.keyCode&&l(".tutor-modal-wrap").removeClass("show")}),l(document).on("click",".tutor-add-quiz-btn",function(t){t.preventDefault();var e=l(this),o=l(this).closest(".tutor_add_quiz_wrap").attr("data-add-quiz-under"),t=l(this).data("topic-id");l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{quiz_for_post_id:o,current_topic_id:t,action:"tutor_load_quiz_builder_modal"},beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){l(".tutor-quiz-builder-modal-wrap .modal-container").html(t.data.output),l(".tutor-quiz-builder-modal-wrap").attr("quiz-for-post-id",o).addClass("show")},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("click",".tutor-quiz-modal-tab-item",function(t){t.preventDefault();var e=l(this),o=l('[name="quiz_title"]');o.val()?(o.closest(".tutor-quiz-builder-form-row").find(".quiz_form_msg").html(""),t=e.attr("href"),l(".quiz-builder-tab-container").hide(),l(t).show(),l("a.tutor-quiz-modal-tab-item").removeClass("active"),e.addClass("active")):o.closest(".tutor-quiz-builder-form-row").find(".quiz_form_msg").html('<p class="quiz-form-warning">Please save the quiz first</p>')}),l(document).on("click",".quiz-modal-btn-next, .quiz-modal-btn-back",function(t){t.preventDefault();t=l(this).attr("href");l('#tutor-quiz-modal-tab-items-wrap a[href="'+t+'"]').trigger("click")}),l(document).on("click",".quiz-modal-tab-navigation-btn.quiz-modal-btn-cancel",function(t){t.preventDefault(),l(".tutor-modal-wrap").removeClass("show")}),l(document).on("click",".tutor-quiz-open-question-form",function(t){t.preventDefault();var e=l(this),o=l("#tutor_quiz_builder_quiz_id").val(),n=l("#post_ID").val(),t=e.attr("data-question-id"),n={quiz_id:o,course_id:n,action:"tutor_quiz_builder_get_question_form"};t&&(n.question_id=t),l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:n,beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){l(".tutor-quiz-builder-modal-contents").html(t.data.output),r().reInit(),jQuery().sortable&&l("#tutor_quiz_question_answers").sortable({handle:".tutor-quiz-answer-sort-icon",start:function(t,e){e.placeholder.css("visibility","visible")},stop:function(t,e){var n;n={},l(".tutor-quiz-answer-wrap").each(function(t,e){var o=l(this),o=parseInt(o.attr("data-answer-id"),10);n[t]=o}),l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{sorted_answer_ids:n,action:"tutor_quiz_answer_sorting"}})}}),q()},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("click",".tutor-quiz-question-trash",function(t){t.preventDefault();var e=l(this),t=e.attr("data-question-id");l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{question_id:t,action:"tutor_quiz_builder_question_delete"},beforeSend:function(){e.closest(".quiz-builder-question-wrap").remove()}})}),l(document).on("click",".add_question_answers_option:not(.disabled)",function(t){t.preventDefault();var e=l(this),o=e.attr("data-question-id"),t=l(".quiz_question_form :input").serializeObject();t.question_id=o,t.action="tutor_quiz_add_question_answers",l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){l("#tutor_quiz_question_answer_form").html(t.data.output),q()},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("click",".tutor-quiz-answer-edit a",function(t){t.preventDefault();var e=l(this),t=e.closest(".tutor-quiz-answer-wrap").attr("data-answer-id");l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{answer_id:t,action:"tutor_quiz_edit_question_answer"},beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){l("#tutor_quiz_question_answer_form").html(t.data.output)},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("click","#quiz-answer-save-btn",function(t){t.preventDefault();var e=l(this),t=l(".quiz_question_form :input").serializeObject();t.action="tutor_save_quiz_answer_options",l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){l("#quiz_validation_msg_wrap").html(""),e.addClass("tutor-updating-message")},success:function(t){l("#tutor_quiz_question_answers").trigger("refresh")},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("click","#quiz-answer-edit-btn",function(t){t.preventDefault();var e=l(this),t=l(".quiz_question_form :input").serializeObject();t.action="tutor_update_quiz_answer_options",l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){l("#tutor_quiz_question_answers").trigger("refresh")},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("change",".tutor-quiz-answers-mark-correct-wrap input",function(t){t.preventDefault();var e=l(this),o=e.val(),t=1;e.prop("checked")||(t=0),l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{answer_id:o,inputValue:t,action:"tutor_mark_answer_as_correct"}})}),l(document).on("refresh","#tutor_quiz_question_answers",function(t){t.preventDefault();var e=l(this),o=e.attr("data-question-id"),t=l(".tutor_select_value_holder").val();l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{question_id:o,question_type:t,action:"tutor_quiz_builder_get_answers_by_question"},beforeSend:function(){e.addClass("tutor-updating-message"),l("#tutor_quiz_question_answer_form").html("")},success:function(t){t.success&&e.html(t.data.output)},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("click",".tutor-quiz-answer-trash-wrap a.answer-trash-btn",function(t){t.preventDefault();var e=l(this),t=e.attr("data-answer-id");l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{answer_id:t,action:"tutor_quiz_builder_delete_answer"},beforeSend:function(){e.closest(".tutor-quiz-answer-wrap").remove()}})}),l(document).on("click",".tutor-delete-quiz-btn",function(t){var e;t.preventDefault(),confirm(a("Are you sure?","tutor"))&&(t=(e=l(this)).attr("data-quiz-id"),l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{quiz_id:t,action:"tutor_delete_quiz_by_id"},beforeSend:function(){e.closest(".course-content-item").remove()}}))}),r().init(),l(document).on("change","input.tutor_select_value_holder",function(t){l(this);l(".add_question_answers_option").trigger("click"),l("#tutor_quiz_question_answers").trigger("refresh")}),l(document).on("click",".tutor-media-upload-btn",function(t){t.preventDefault();var e,o=l(this);e||(e=wp.media({title:a("Select or Upload Media Of Your Chosen Persuasion","tutor"),button:{text:a("Use this media","tutor")},multiple:!1})).on("select",function(){var t=e.state().get("selection").first().toJSON();o.html('<img src="'+t.url+'" alt="" />'),o.closest(".tutor-media-upload-wrap").find("input").val(t.id)}),e.open()}),l(document).on("click",".tutor-media-upload-trash",function(t){t.preventDefault();t=l(this);t.closest(".tutor-media-upload-wrap").find(".tutor-media-upload-btn").html('<i class="tutor-icon-image1"></i>'),t.closest(".tutor-media-upload-wrap").find("input").val("")});var n,u=(n=0,function(t,e){clearTimeout(n),n=setTimeout(t,e)});l(document).on("click",".tutor-add-instructor-btn",function(t){t.preventDefault();var e=l(this),t=l("#post_ID").val();l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{course_id:t,action:"tutor_load_instructors_modal"},beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){t.success&&(l(".tutor-instructors-modal-wrap .modal-container").html(t.data.output),l(".tutor-instructors-modal-wrap").addClass("show"))},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("change keyup",".tutor-instructors-modal-wrap .tutor-modal-search-input",function(t){t.preventDefault();var o=l(this),n=l(".tutor-modal-wrap");u(function(){var t=o.val(),e=l("#post_ID").val();l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{course_id:e,search_terms:t,action:"tutor_load_instructors_modal"},beforeSend:function(){n.addClass("loading")},success:function(t){t.success&&(l(".tutor-instructors-modal-wrap .modal-container").html(t.data.output),l(".tutor-instructors-modal-wrap").addClass("show"))},complete:function(){n.removeClass("loading")}})},1e3)}),l(document).on("click",".add_instructor_to_course_btn",function(t){t.preventDefault();var e=l(this),o=l(".tutor-modal-wrap"),t=l("#post_ID").val(),o=o.find("input").serializeObject();o.course_id=t,o.action="tutor_add_instructors_to_course",l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:o,beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){t.success&&(l(".tutor-course-available-instructors").html(t.data.output),l(".tutor-modal-wrap").removeClass("show"))},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("click",".tutor-instructor-delete-btn",function(t){t.preventDefault();var e=l(this),o=l("#post_ID").val(),t=e.closest(".added-instructor-item").attr("data-instructor-id");l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{course_id:o,instructor_id:t,action:"detach_instructor_from_course"},success:function(t){t.success&&e.closest(".added-instructor-item").remove()}})}),l(document).on("click",".settings-tabs-navs li",function(t){t.preventDefault();var e=l(this),o=e.find("a").attr("data-target"),t=e.find("a").attr("href");e.addClass("active").siblings("li.active").removeClass("active"),l(".settings-tab-wrap").removeClass("active").hide(),l(o).addClass("active").show(),window.history.pushState({},"",t)}),l(document).on("lesson_modal_loaded quiz_modal_loaded assignment_modal_loaded",function(t,e){jQuery().select2&&l(".select2_multiselect").select2({dropdownCssClass:"increasezindex"}),o()}),l(document).on("keyup change",".tutor-number-validation",function(t){var e=l(this),o=parseInt(e.val()),n=parseInt(e.attr("data-min")),a=parseInt(e.attr("data-max"));o<n?e.val(n):a<o&&e.val(a)}),l(document).on("click",".tutor-instructor-feedback",function(t){t.preventDefault();var e=l(this);l.ajax({url:window.ajaxurl||_tutorobject.ajaxurl,type:"POST",data:{attempts_id:e.data("attemptid"),feedback:l(".tutor-instructor-feedback-content").val(),action:"tutor_instructor_feedback"},beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){t.success&&(e.closest(".course-content-item").remove(),tutor_toast(a("Success","tutor"),e.data("toast_success_message"),"success"))},complete:function(){e.removeClass("tutor-updating-message")}})});var c=l(".tutor-announcement-add-new"),d=l(".tutor-announcement-edit"),p=l(".tutor-announcement-delete"),m=l(".tutor-announcement-details"),t=l(".tutor-announcement-close-btn"),f=l(".tutor-accouncement-create-modal"),_=l(".tutor-accouncement-update-modal"),h=l(".tutor-accouncement-details-modal");function v(t,e){var o=new URL(window.location.href),n=o.searchParams;return n.set(t,e),o.search=n.toString(),n.set("paged",1),o.search=n.toString(),o.toString()}l(c).click(function(){f.addClass("show"),l("#tutor-annoucement-backend-create-modal").addClass("show")}),l(m).click(function(){var t=l(this).attr("announcement-date"),e=l(this).attr("announcement-id"),o=l(this).attr("course-id"),n=l(this).attr("course-name"),a=l(this).attr("announcement-title"),i=l(this).attr("announcement-summary");l(".tutor-announcement-detail-content").html("<h3>".concat(a,"</h3><p>").concat(i,"</p>")),l(".tutor-announcement-detail-course-info p").html("".concat(n)),l(".tutor-announcement-detail-date-info p").html("".concat(t)),l("#tutor-announcement-edit-from-detail").attr("announcement-id",e),l("#tutor-announcement-edit-from-detail").attr("course-id",o),l("#tutor-announcement-edit-from-detail").attr("announcement-title",a),l("#tutor-announcement-edit-from-detail").attr("announcement-summary",i),l("#tutor-announcement-delete-from-detail").attr("announcement-id",e),h.addClass("show")}),l(d).click(function(){h&&h.removeClass("show");var t=l(this).attr("announcement-id"),e=l(this).attr("course-id"),o=l(this).attr("announcement-title"),n=l(this).attr("announcement-summary");l("#tutor-announcement-course-id").val(e),l("#announcement_id").val(t),l("#tutor-announcement-title").val(o),l("#tutor-announcement-summary").val(n),_.addClass("show")}),l(t).click(function(){f.removeClass("show"),_.removeClass("show"),h.removeClass("show"),l("#tutor-annoucement-backend-create-modal").removeClass("show")}),l(".tutor-announcements-form").on("submit",function(t){t.preventDefault();var e=l(this).find('button[type="submit"]'),t=l(".tutor-announcements-form").serialize()+"&action=tutor_announcement_create&action_type=create";l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){if(l(".tutor-alert").remove(),"success"==t.status&&location.reload(),"validation_error"==t.status){l(".tutor-announcements-create-alert").append('<div class="tutor-alert alert-warning"></div>');for(var e=0,o=Object.entries(t.message);e<o.length;e++){var n=b(o[e],2),n=(n[0],n[1]);l(".tutor-announcements-create-alert .tutor-alert").append("<li>".concat(n,"</li>"))}}"fail"==t.status&&l(".tutor-announcements-create-alert").html("<li>".concat(t.message,"</li>"))},error:function(t){console.log(t)}})}),l(".tutor-announcements-update-form").on("submit",function(t){t.preventDefault();var e=l(this).find('button[type="submit"]'),t=l(".tutor-announcements-update-form").serialize()+"&action=tutor_announcement_create&action_type=update";l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){if(l(".tutor-alert").remove(),"success"==t.status&&location.reload(),"validation_error"==t.status){l(".tutor-announcements-update-alert").append('<div class="tutor-alert alert-warning"></div>');for(var e=0,o=Object.entries(t.message);e<o.length;e++){var n=b(o[e],2),n=(n[0],n[1]);l(".tutor-announcements-update-alert > .tutor-alert").append("<li>".concat(n,"</li>"))}}"fail"==t.status&&l(".tutor-announcements-create-alert").html("<li>".concat(t.message,"</li>"))},error:function(){}})}),l(p).click(function(){var t=l(this).attr("announcement-id"),e=l("#tutor-announcement-tr-"+t);confirm("Do you want to delete?")&&l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{action:"tutor_announcement_delete",announcement_id:t},beforeSend:function(){},success:function(t){e.remove(),h.length&&h.removeClass("show"),"fail"==t.status&&console.log(t.message)},error:function(){}})}),l(".tutor-announcement-course-sorting").on("change",function(t){window.location=v("course-id",l(this).val())}),l(".tutor-announcement-order-sorting").on("change",function(t){window.location=v("order",l(this).val())}),l(".tutor-announcement-date-sorting").on("change",function(t){window.location=v("date",l(this).val())}),l(".tutor-announcement-search-sorting").on("click",function(t){window.location=v("search",l(".tutor-announcement-search-field").val())}),l(document).click(function(){l(".tutor-dropdown").removeClass("show")}),l(".tutor-dropdown").click(function(t){t.stopPropagation(),l(".tutor-dropdown").hasClass("show")&&l(".tutor-dropdown").removeClass("show"),l(this).addClass("show")});var g,p=".video_source_wrap_external_url input, .video_source_wrap_vimeo input, .video_source_wrap_youtube input, .video_source_wrap_html5, .video_source_upload_wrap_html5";l("body").on("paste",p,function(t){t.stopImmediatePropagation();var r=l(this).closest(".lesson-modal-form-wrap").find(".tutor-option-field-video-duration"),e=r.find("label"),o=l(this).hasClass("video_source_wrap_html5")||l(this).hasClass("video_source_upload_wrap_html5"),n=l(this).data("autofill_url");l(this).data("autofill_url",null);function a(t){t?0==e.find("img").length&&e.append(' <img src="'+window._tutorobject.loading_icon_url+'" style="display:inline-block"/>'):e.find("img").remove()}function i(t){for(var e=Math.floor(t/3600),o=Math.floor((t-3600*e)/60),t=Math.round(t-3600*e-60*o),n=[e=e<10?"0"+e:e,o=o<10?"0"+o:o,t=t<10?"0"+t:t],a=r.find("input"),i=0;i<3;i++)a.eq(i).val(n[i])}var s,u,c,t=o?l(this).find("span").data("video_url"):n||t.originalEvent.clipboardData.getData("text");o||l(this).parent().hasClass("video_source_wrap_external_url")?((s=document.createElement("video")).addEventListener("loadedmetadata",function(){i(s.duration),a(!1)}),a(!0),s.src=t):l(this).parent().hasClass("video_source_wrap_vimeo")?(u=(c=t.match(/^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/))?c[5]:null)&&(a(!0),l.getJSON("http://vimeo.com/api/v2/video/"+u+"/json",function(t){Array.isArray(t)&&t[0]&&void 0!==t[0].duration&&i(t[0].duration),a(!1)})):l(this).parent().hasClass("video_source_wrap_youtube")&&(u=!(!(c=t.match(/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/))||11!=c[7].length)&&c[7],c=l(this).data("youtube_api_key"),u&&c&&(c="https://www.googleapis.com/youtube/v3/videos?id="+u+"&key="+c+"&part=contentDetails",a(!0),l.getJSON(c,function(t){"object"==w(t)&&t.items&&t.items[0]&&t.items[0].contentDetails&&t.items[0].contentDetails.duration&&i(function(t){t=(t=t.match(/PT(\d+H)?(\d+M)?(\d+S)?/)).slice(1).map(function(t){if(null!=t)return t.replace(/\D/,"")});return 3600*(parseInt(t[0])||0)+60*(parseInt(t[1])||0)+(parseInt(t[2])||0)}(t.items[0].contentDetails.duration)),a(!1)})))}).on("input",p,function(){g&&clearTimeout(g);var e=l(this);g=setTimeout(function(){var t=(t=e.val())?t.trim():"";console.log("Trigger",t),t&&e.data("autofill_url",t).trigger("paste")},700)}),l(".tutor-form-submit-through-ajax").submit(function(t){t.preventDefault();var e=l(this),o=l(this).attr("action")||window.location.href,n=l(this).attr("method")||"GET",t=l(this).serializeObject();e.find("button").addClass("tutor-updating-message"),l.ajax({url:o,type:n,data:t,success:function(){tutor_toast(a("Success","tutor"),e.data("toast_success_message"),"success")},complete:function(){e.find("button").removeClass("tutor-updating-message")}})}),l.ajaxSetup({data:y()})}),jQuery.fn.serializeObject=function(){var t={},e=this.serializeArray();return jQuery.each(e,function(){t[this.name]?(t[this.name].push||(t[this.name]=[t[this.name]]),t[this.name].push(this.value||"")):t[this.name]=this.value||""}),t},window.tutor_toast=function(t,e,o){var n=((window._tutorobject||{}).tutor_url||"")+"assets/images/";jQuery(".tutor-toast-parent").length||jQuery("body").append('<div class="tutor-toast-parent"></div>');var a=jQuery('        <div>            <div>                <img src="'+{success:n+"icon-check.svg",error:n+"icon-cross.svg"}[o]+'"/>            </div>            <div>                <div>                    <b>'+t+"</b>                    <span>"+e+'</span>                </div>            </div>            <div>                <i class="tutor-toast-close tutor-icon-line-cross"></i>            </div>        </div>');a.find(".tutor-toast-close").click(function(){a.remove()}),jQuery(".tutor-toast-parent").append(a),setTimeout(function(){a&&a.fadeOut("fast",function(){jQuery(this).remove()})},5e3)},jQuery(document).ready(function(u){var e,o,n,a,t=wp.i18n,s=t.__;t._x,t._n,t._nx;function i(t,e){var o,n;1<t.originalEvent.touches.length||(t.preventDefault(),o=t.originalEvent.changedTouches[0],(n=document.createEvent("MouseEvents")).initMouseEvent(e,!0,!0,window,1,o.screenX,o.screenY,o.clientX,o.clientY,!1,!1,!1,!1,0,null),t.target.dispatchEvent(n))}jQuery().select2&&u(".tutor_select2").select2({escapeMarkup:function(t){return t}}),(e=jQuery).support.touch="ontouchend"in document,e.support.touch&&(f=e.ui.mouse.prototype,n=f._mouseInit,a=f._mouseDestroy,f._touchStart=function(t){!o&&this._mouseCapture(t.originalEvent.changedTouches[0])&&(o=!0,this._touchMoved=!1,i(t,"mouseover"),i(t,"mousemove"),i(t,"mousedown"))},f._touchMove=function(t){o&&(this._touchMoved=!0,i(t,"mousemove"))},f._touchEnd=function(t){o&&(i(t,"mouseup"),i(t,"mouseout"),this._touchMoved||i(t,"click"),o=!1)},f._mouseInit=function(){var t=this;t.element.bind({touchstart:e.proxy(t,"_touchStart"),touchmove:e.proxy(t,"_touchMove"),touchend:e.proxy(t,"_touchEnd")}),n.call(t)},f._mouseDestroy=function(){var t=this;t.element.unbind({touchstart:e.proxy(t,"_touchStart"),touchmove:e.proxy(t,"_touchMove"),touchend:e.proxy(t,"_touchEnd")}),a.call(t)});var r={ajaxurl:window._tutorobject.ajaxurl,nonce_key:window._tutorobject.nonce_key,video_data:function(){var t=u("#tutor_video_tracking_information").val();return t?JSON.parse(t):{}},track_player:function(){var t,o,e,n=this;"undefined"!=typeof Plyr&&(t=new Plyr("#tutorPlayer"),o=n.video_data(),t.on("ready",function(t){var e=t.detail.plyr,t=o.best_watch_time;0<t&&e.duration>Math.round(t)&&(e.media.currentTime=t),n.sync_time(e)}),e=0,t.on("timeupdate",function(t){t=t.detail.plyr;30<=e/4&&(n.sync_time(t),e=0),e++}),t.on("ended",function(t){var e=n.video_data(),t=t.detail.plyr;n.sync_time(t,{is_ended:!0}),e.autoload_next_course_content&&n.autoload_content()}))},sync_time:function(t,e){var o=this.video_data().post_id,t={action:"sync_video_playback",currentTime:t.currentTime,duration:t.duration,post_id:o};t[this.nonce_key]=_tutorobject[this.nonce_key];o=t;e&&(o=Object.assign(t,e)),u.post(this.ajaxurl,o)},autoload_content:function(){var t={action:"autoload_next_course_content",post_id:this.video_data().post_id};t[this.nonce_key]=_tutorobject[this.nonce_key],u.post(this.ajaxurl,t).done(function(t){t.success&&t.data.next_url&&(location.href=t.data.next_url)})},init:function(){this.track_player()}};function c(t){t.add(t.prevAll()).filter("i").addClass("tutor-icon-star-full").removeClass("tutor-icon-star-line"),t.nextAll().filter("i").removeClass("tutor-icon-star-full").addClass("tutor-icon-star-line")}u("#tutorPlayer").length&&r.init(),u(document).on("change keyup paste",".tutor_user_name",function(){u(this).val(u(this).val().toString().toLowerCase().replace(/\s+/g,"-").replace(/[^\w\-]+/g,"").replace(/\-\-+/g,"-").replace(/^-+/,"").replace(/-+$/,""))}),u(document).on("mouseover",".tutor-star-rating-container .tutor-star-rating-group i",function(){c(u(this))}),u(document).on("click",".tutor-star-rating-container .tutor-star-rating-group i",function(){var t=u(this).attr("data-rating-value");u(this).closest(".tutor-star-rating-group").find('input[name="tutor_rating_gen_input"]').val(t),c(u(this))}),u(document).on("mouseout",".tutor-star-rating-container .tutor-star-rating-group",function(){var t=u(this).find('input[name="tutor_rating_gen_input"]').val(),e=parseInt(t),t=u(this).find('[data-rating-value="'+e+'"]');e&&t&&0<t.length?c(t):u(this).find("i").removeClass("tutor-icon-star-full").addClass("tutor-icon-star-line")}),u(document).on("click",".tutor_submit_review_btn",function(t){t.preventDefault();var e=u(this),o=e.closest("form").find('input[name="tutor_rating_gen_input"]').val(),n=(n=e.closest("form").find('textarea[name="review"]').val()).trim(),t=u('input[name="tutor_course_id"]').val();o&&0!=o&&n?n&&u.ajax({url:_tutorobject.ajaxurl,type:"POST",data:{course_id:t,rating:o,review:n,action:"tutor_place_rating"},beforeSend:function(){e.addClass("updating-icon")},success:function(t){var e=t.data.review_id,t=t.data.review;u(".tutor-review-"+e+" .review-content").html(t),new window.tutor_popup(u,"icon-rating",40).popup({title:s("Thank You for Rating This Course!","tutor"),description:s("Your rating will now be visible in the course page","tutor")}),setTimeout(function(){location.reload()},3e3)}}):alert(s("Rating and review required","tutor"))}).on("click",".tutor_cancel_review_btn",function(){u(this).closest("form").hide()}),u(document).on("click",".write-course-review-link-btn",function(t){t.preventDefault(),u(this).siblings(".tutor-write-review-form").slideToggle()}),u(document).on("click",".tutor-ask-question-btn",function(t){t.preventDefault(),u(".tutor-add-question-wrap").slideToggle()}),u(document).on("click",".tutor_question_cancel",function(t){t.preventDefault(),u(".tutor-add-question-wrap").toggle()}),u(document).on("submit","#tutor-ask-question-form",function(t){t.preventDefault();var e=u(this),t=u(this).serializeObject();t.action="tutor_ask_question",u.ajax({url:_tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){e.find(".tutor_ask_question_btn").addClass("updating-icon")},success:function(t){t.success&&(u(".tutor-add-question-wrap").hide(),window.location.reload())},complete:function(){e.find(".tutor_ask_question_btn").removeClass("updating-icon")}})}),u(document).on("submit",".tutor-add-answer-form",function(t){t.preventDefault();var e=u(this),t=u(this).serializeObject();t.action="tutor_add_answer",u.ajax({url:_tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){e.find(".tutor_add_answer_btn").addClass("updating-icon")},success:function(t){t.success&&window.location.reload()},complete:function(){e.find(".tutor_add_answer_btn").removeClass("updating-icon")}})}),u(document).on("focus",".tutor_add_answer_textarea",function(t){t.preventDefault();t=u(this).closest(".tutor_add_answer_wrap").attr("data-question-id");wp.editor.initialize("tutor_answer_"+t,{tinymce:{wpautop:!0,toolbar1:"bold italic underline bullist strikethrough numlist  blockquote  alignleft aligncenter alignright undo redo link unlink spellchecker fullscreen"}})}),u(document).on("click",".tutor_cancel_wp_editor",function(t){t.preventDefault(),u(this).closest(".tutor_wp_editor_wrap").toggle(),u(this).closest(".tutor_add_answer_wrap").find(".tutor_wp_editor_show_btn_wrap").toggle();t=u(this).closest(".tutor_add_answer_wrap").attr("data-question-id");wp.editor.remove("tutor_answer_"+t)}),u(document).on("click",".tutor_wp_editor_show_btn",function(t){t.preventDefault(),u(this).closest(".tutor_add_answer_wrap").find(".tutor_wp_editor_wrap").toggle(),u(this).closest(".tutor_wp_editor_show_btn_wrap").toggle()});var l,d,p,m=u("#tutor-quiz-time-update"),f=null;m.length&&(f=JSON.parse(m.attr("data-attempt-settings")),0<(_=JSON.parse(m.attr("data-attempt-meta"))).time_limit.time_limit_seconds?(l=new Date(f.attempt_started_at).getTime()+1e3*_.time_limit.time_limit_seconds,d=new Date(_.date_time_now).getTime(),p=setInterval(function(){var a,t=l-d,e=Math.floor(t/864e5),o=Math.floor(t%864e5/36e5),n=Math.floor(t%36e5/6e4),i=Math.floor(t%6e4/1e3),r="";e&&(r+=e+"d "),o&&(r+=o+"h "),n&&(r+=n+"m "),i&&(r+=i+"s "),t<0&&(clearInterval(p),r="EXPIRED","autosubmit"===_tutorobject.quiz_options.quiz_when_time_expires?u("form#tutor-answering-quiz").submit():"autoabandon"===_tutorobject.quiz_options.quiz_when_time_expires&&(t=u("#tutor_quiz_id").val(),u("#tutor_quiz_remaining_time_secs").val(),t={quiz_id:t,action:"tutor_quiz_timeout"},a=u("#tutor-quiz-time-expire-wrapper").attr("data-attempt-remaining"),u(".tutor-quiz-answer-next-btn, .tutor-quiz-submit-btn, .tutor-quiz-answer-previous-btn").prop("disabled",!0),u(".time-remaining span").css("color","#F44337"),u.ajax({url:_tutorobject.ajaxurl,type:"POST",data:t,success:function(t){var e=u("#tutor-quiz-time-expire-wrapper").data("attempt-allowed"),o=u("#tutor-quiz-time-expire-wrapper").data("attempt-remaining"),n="#tutor-quiz-time-expire-wrapper .tutor-alert";u(n).addClass("show"),0<a?u("".concat(n," .text")).html(s("Your time limit for this quiz has expired, please reattempt the quiz. Attempts remaining: "+o+"/"+e,"tutor")):(u(n).addClass("tutor-alert-danger"),u("#tutor-start-quiz").hide(),u("".concat(n," .text")).html("".concat(s("Unfortunately, you are out of time and quiz attempts. ","tutor"))))},complete:function(){}}))),d+=1e3,m.html(r)},1e3)):m.closest(".time-remaining").remove());var _=u("#tutor-quiz-body form#tutor-start-quiz");function h(){jQuery().sortable&&(u(".tutor-quiz-answers-wrap").sortable({handle:".answer-sorting-bar",start:function(t,e){e.placeholder.css("visibility","visible")},stop:function(t,e){}}).disableSelection(),u(".quiz-draggable-rand-answers, .quiz-answer-matching-droppable").sortable({connectWith:".quiz-answer-matching-droppable",placeholder:"drop-hover"}).disableSelection())}function v(o){var t,e,n=!0,a=o.find(".quiz-answer-required");return a.length&&((t=a.find("input")).length&&("radio"===(e=t.attr("type"))?0==a.find('input[type="radio"]:checked').length&&(o.find(".answer-help-block").html('<p style="color: #dc3545">'.concat(s("Please select an option to answer","tutor"),"</p>")),n=!1):"checkbox"===e?0==a.find('input[type="checkbox"]:checked').length&&(o.find(".answer-help-block").html('<p style="color: #dc3545">'.concat(s("Please select at least one option to answer.","tutor"),"</p>")),n=!1):"text"===e&&t.each(function(t,e){u(e).val().trim().length||(o.find(".answer-help-block").html('<p style="color: #dc3545">'.concat(s("The answer for this question is required","tutor"),"</p>")),n=!1)})),a.find("textarea").length&&a.find("textarea").val().trim().length<1&&(o.find(".answer-help-block").html('<p style="color: #dc3545">'.concat(s("The answer for this question is required","tutor"),"</p>")),n=!1),(a=a.find(".quiz-answer-matching-droppable")).length&&a.each(function(t,e){u(e).find(".quiz-draggable-answer-item").length||(o.find(".answer-help-block").html('<p style="color: #dc3545">'.concat(s("Please match all the items","tutor"),"</p>")),n=!1)})),n}function g(o){var t=!1,n=JSON.parse(atob(window.tutor_quiz_context.split("").reverse().join("")));Array.isArray(n)||(n=[]);var e=o.attr("data-quiz-feedback-mode");u(".wrong-right-text").remove(),u(".quiz-answer-input-bottom").removeClass("wrong-answer right-answer");var a=!0,i=o.find("input"),r=o.find('input[type="radio"]:checked, input[type="checkbox"]:checked');return"retry"===e?(r.each(function(){var t=u(this),e=t.attr("type");"radio"!==e&&"checkbox"!==e||-1<n.indexOf(t.val())||(t.prop("checked")&&t.closest(".quiz-answer-input-bottom").addClass("wrong-answer").append('<span class="wrong-right-text"><i class="tutor-icon-line-cross"></i> '.concat(s("Incorrect, Please try again","tutor"),"</span>")),a=!1)}),i.each(function(){var t,e=u(this);"checkbox"===e.attr("type")&&(t=-1<n.indexOf(e.val()),e=e.is(":checked"),t&&!e&&(o.find(".answer-help-block").html('<p style="color: #dc3545">'.concat(s("More answer for this question is required","tutor"),"</p>")),a=!1))})):"reveal"===e&&(r.each(function(){var t=u(this);-1<n.indexOf(t.val())||(a=!1)}),i.each(function(){var t,e=u(this),o=e.attr("type");"radio"!==o&&"checkbox"!==o||(t=-1<n.indexOf(e.val()),o=e.is(":checked"),t?e.closest(".quiz-answer-input-bottom").addClass("right-answer").append('<span class="wrong-right-text"><i class="tutor-icon-checkbox-pen-outline"></i>'.concat(s("Correct Answer","tutor"),"</span>")):e.prop("checked")&&e.closest(".quiz-answer-input-bottom").addClass("wrong-answer"),t&&!o&&(a=!1))})),t=a?!0:t}_.length&&"1"===_tutorobject.quiz_options.quiz_auto_start&&_.submit(),u(document).on("click",".quiz-manual-review-action",function(t){t.preventDefault();var e=u(this),o=e.attr("data-attempt-id"),n=e.attr("data-attempt-answer-id"),t=e.attr("data-mark-as");u.ajax({url:_tutorobject.ajaxurl,type:"GET",data:{action:"review_quiz_answer",attempt_id:o,attempt_answer_id:n,mark_as:t},beforeSend:function(){e.find("i").addClass("updating-icon")},success:function(t){location.reload()},complete:function(){e.find("i").removeClass("updating-icon")}})}),u(".tooltip-btn").on("hover",function(t){u(this).toggleClass("active")}),u(".tutor-course-title h4 .toggle-information-icon").on("click",function(t){u(this).closest(".tutor-topics-in-single-lesson").find(".tutor-topics-summery").slideToggle(),t.stopPropagation()}),u(".tutor-course-topic.tutor-active").find(".tutor-course-lessons").slideDown(),u(".tutor-course-title").on("click",function(){var t=u(this).siblings(".tutor-course-lessons");u(this).closest(".tutor-course-topic").toggleClass("tutor-active"),t.slideToggle()}),u(document).on("click",".tutor-topics-title h3 .toggle-information-icon",function(t){u(this).closest(".tutor-topics-in-single-lesson").find(".tutor-topics-summery").slideToggle(),t.stopPropagation()}),u(document).on("click",".tutor-course-wishlist-btn",function(t){t.preventDefault();var e=u(this),t=e.attr("data-course-id");u.ajax({url:_tutorobject.ajaxurl,type:"POST",data:{course_id:t,action:"tutor_course_add_to_wishlist"},beforeSend:function(){e.addClass("updating-icon")},success:function(t){t.success?"added"===t.data.status?e.addClass("has-wish-listed"):e.removeClass("has-wish-listed"):window.location=t.data.redirect_to},complete:function(){e.removeClass("updating-icon")}})}),_tutorobject.enable_lesson_classic_editor||(u(document).on("click",".tutor-single-lesson-a",function(t){t.preventDefault();var e=u(this),t=e.attr("data-lesson-id"),o=u("#tutor-single-entry-content");u.ajax({url:_tutorobject.ajaxurl,type:"POST",data:{lesson_id:t,action:"tutor_render_lesson_content"},beforeSend:function(){var t=e.find(".lesson_title").text();u("head title").text(t),window.history.pushState("obj",t,e.attr("href")),o.addClass("loading-lesson"),u(".tutor-single-lesson-items").removeClass("active"),e.closest(".tutor-single-lesson-items").addClass("active")},success:function(t){o.html(t.data.html),r.init(),u(".tutor-lesson-sidebar").css("display",""),window.dispatchEvent(new window.Event("tutor_ajax_lesson_loaded"))},complete:function(){o.removeClass("loading-lesson")}})}),u(document).on("click",".sidebar-single-quiz-a",function(t){t.preventDefault();var e=u(this),t=e.attr("data-quiz-id"),o=e.find(".lesson_title").text(),n=u("#tutor-single-entry-content");u.ajax({url:_tutorobject.ajaxurl,type:"POST",data:{quiz_id:t,action:"tutor_render_quiz_content"},beforeSend:function(){u("head title").text(o),window.history.pushState("obj",o,e.attr("href")),n.addClass("loading-lesson"),u(".tutor-single-lesson-items").removeClass("active"),e.closest(".tutor-single-lesson-items").addClass("active")},success:function(t){n.html(t.data.html),h(),u(".tutor-lesson-sidebar").css("display","")},complete:function(){n.removeClass("loading-lesson")}})})),u(document).on("click",".tutor-lesson-sidebar-hide-bar",function(t){t.preventDefault(),u(".tutor-lesson-sidebar").toggle(),u("#tutor-single-entry-content").toggleClass("sidebar-hidden")}),u(".tutor-tabs-btn-group a").on("click touchstart",function(t){t.preventDefault();var e=u(this),t=e.attr("href");u(".tutor-lesson-sidebar-tab-item").hide(),u(t).show(),u(".tutor-tabs-btn-group a").removeClass("active"),e.addClass("active")}),h(),u(document).on("click",".tutor-quiz-answer-next-btn, .tutor-quiz-answer-previous-btn",function(t){var e,o;t.preventDefault(),u(this).hasClass("tutor-quiz-answer-previous-btn")?u(this).closest(".quiz-attempt-single-question").hide().prev().show():v(t=(e=u(this)).closest(".quiz-attempt-single-question"))&&g(t)&&(parseInt(e.closest(".quiz-attempt-single-question").attr("id").match(/\d+/)[0],10),!(e=e.closest(".quiz-attempt-single-question").attr("data-next-question-id"))||(o=u(e))&&o.length&&("reveal"===t.attr("data-quiz-feedback-mode")?setTimeout(function(){u(".quiz-attempt-single-question").hide(),o.show()},500):(u(".quiz-attempt-single-question").hide(),o.show()),u(".tutor-quiz-questions-pagination").length&&(u(".tutor-quiz-question-paginate-item").removeClass("active"),u('.tutor-quiz-questions-pagination a[href="'+e+'"]').addClass("active"))))}),u(document).on("submit","#tutor-answering-quiz",function(t){var e=u(".quiz-attempt-single-question"),o=!0;e.length&&e.each(function(t,e){o=v(u(e)),o=g(u(e))}),o||t.preventDefault()}),u(document).on("click",".tutor-quiz-question-paginate-item",function(t){t.preventDefault();var e=u(this),t=u(e.attr("href"));u(".quiz-attempt-single-question").hide(),t.show(),u(".tutor-quiz-question-paginate-item").removeClass("active"),e.addClass("active")}),u(document).on("keyup","textarea.question_type_short_answer, textarea.question_type_open_ended",function(t){var e=u(this),o=e.val(),n=e.hasClass("question_type_short_answer")?_tutorobject.quiz_options.short_answer_characters_limit:_tutorobject.quiz_options.open_ended_answer_characters_limit,a=n-o.length;a<1&&(e.val(o.substr(0,n)),a=0),e.closest(".tutor-quiz-answers-wrap").find(".characters_remaining").html(a)}),u(".quiz-draggable-rand-answers").length&&u(".quiz-draggable-rand-answers").each(function(){var t=u(this),e=t.height();t.css({height:e})}),u(document).on("submit click",".cart-required-login, .cart-required-login a, .cart-required-login form",function(t){t.preventDefault();t=u(this).data("login_page_url");t?window.location.assign(t):u(".tutor-cart-box-login-form").fadeIn(100)}),u(".tutor-popup-form-close, .login-overlay-close").on("click",function(){u(".tutor-cart-box-login-form").fadeOut(100)}),u(document).on("keyup",function(t){27===t.keyCode&&(u(".tutor-frontend-modal").hide(),u(".tutor-cart-box-login-form").fadeOut(100))}),!u.fn.ShareLink||(_=u(".tutor-social-share-wrap")).length&&(q=JSON.parse(_.attr("data-social-share-config")),u(".tutor_share").ShareLink({title:q.title,text:q.text,image:q.image,class_prefix:"s_",width:640,height:480})),jQuery.datepicker&&u(".tutor_report_datepicker").datepicker({dateFormat:"yy-mm-dd"}),u(".withdraw-method-select-input").on("change",function(t){var e=u(this);u(".withdraw-method-form").hide(),u("#withdraw-method-form-"+e.closest(".withdraw-method-select").attr("data-withdraw-method")).show()}),u(".withdraw-method-select-input").each(function(){var t=u(this);t.is(":checked")&&(u(".withdraw-method-form").hide(),u("#withdraw-method-form-"+t.closest(".withdraw-method-select").attr("data-withdraw-method")).show())}),u(document).on("submit","#tutor-withdraw-account-set-form",function(t){t.preventDefault();var e=u(this),o=e.find(".tutor_set_withdraw_account_btn"),t=e.serializeObject();u.ajax({url:_tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){e.find(".tutor-success-msg").remove(),o.addClass("updating-icon")},success:function(t){t.success&&(t='<div class="tutor-success-msg" style="display: none;"><i class="tutor-icon-mark"></i> '+t.data.msg+" </div>",o.closest(".withdraw-account-save-btn-wrap").append(t),e.find(".tutor-success-msg").length&&e.find(".tutor-success-msg").slideDown(),setTimeout(function(){e.find(".tutor-success-msg").slideUp()},5e3))},complete:function(){o.removeClass("updating-icon")}})}),u(document).on("click",".open-withdraw-form-btn, .close-withdraw-form-btn",function(t){t.preventDefault(),"yes"!=u(this).data("reload")?(u(".tutor-earning-withdraw-form-wrap").toggle().find('[name="tutor_withdraw_amount"]').val(""),u(".tutor-withdrawal-pop-up-success").hide().next().show(),u("html, body").css("overflow",u(".tutor-earning-withdraw-form-wrap").is(":visible")?"hidden":"auto")):window.location.reload()}),u(document).on("submit","#tutor-earning-withdraw-form",function(t){t.preventDefault();var e=u(this),o=u("#tutor-earning-withdraw-btn"),n=u(".tutor-withdraw-form-response"),t=e.serializeObject();u.ajax({url:_tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){e.find(".tutor-success-msg").remove(),o.addClass("updating-icon")},success:function(t){t.success?("undefined"!==t.data.available_balance&&u(".withdraw-balance-col .available_balance").html(t.data.available_balance),u(".tutor-withdrawal-pop-up-success").show().next().hide()):(t='<div class="tutor-error-msg inline-image-text is-inline-block">                            <img src="'+window._tutorobject.tutor_url+'assets/images/icon-cross.svg"/>                             <div>                                <b>Error</b><br/>                                <span>'+t.data.msg+"</span>                            </div>                        </div>",n.html(t),setTimeout(function(){n.html("")},5e3))},complete:function(){o.removeClass("updating-icon")}})});var w=u(".tutor-frontend-modal");function b(){u("ul.tutor-bp-enrolled-course-list").each(function(){var t,e=u(this),o=e.find(" > li");3<o.length&&(t=o.length-3,o.each(function(t,e){var o=u(this);3<=t&&o.hide()}),t='<a href="javascript:;" class="tutor_bp_plus_courses"><strong>+'+t+" More </strong></a> Courses",e.closest(".tutor-bp-enrolled-courses-wrap").find(".thread-participant-enrolled-info").html(t)),e.show()})}w.each(function(){var e=u(this),t=u(this).data("popup-rel");u('[href="'+t+'"]').on("click",function(t){e.fadeIn(),t.preventDefault()})}),u(document).on("click",".tm-close, .tutor-frontend-modal-overlay, .tutor-modal-btn-cancel",function(){w.fadeOut()}),u(document).on("click",".tutor-dashboard-element-delete-btn",function(t){t.preventDefault();t=u(this).attr("data-id");u("#tutor-dashboard-delete-element-id").val(t)}),u(document).on("submit","#tutor-dashboard-delete-element-form",function(t){t.preventDefault();var e=u("#tutor-dashboard-delete-element-id").val(),o=u(".tutor-modal-element-delete-btn"),t=u(this).serializeObject();u.ajax({url:_tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){o.addClass("updating-icon")},success:function(t){t.success&&u("#tutor-dashboard-"+t.data.element+"-"+e).remove()},complete:function(){o.removeClass("updating-icon"),u(".tutor-frontend-modal").hide()}})}),u("#tutor_profile_photo_id").val()||u(".tutor-profile-photo-delete-btn").hide(),u(document).on("click",".tutor-profile-photo-delete-btn",function(){return u(".tutor-profile-photo-upload-wrap").find("img").attr("src",_tutorobject.placeholder_img_src),u("#tutor_profile_photo_id").val(""),u(".tutor-profile-photo-delete-btn").hide(),u.ajax({url:_tutorobject.ajaxurl,type:"POST",data:{action:"tutor_profile_photo_remove"}}),!1}),u(document).on("submit","#tutor_assignment_start_form",function(t){t.preventDefault();t=u(this).serializeObject();t.action="tutor_start_assignment",u.ajax({url:_tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){u("#tutor_assignment_start_btn").addClass("updating-icon")},success:function(t){t.success&&location.reload()},complete:function(){u("#tutor_assignment_start_btn").removeClass("updating-icon")}})}),u(document).on("submit","#tutor_assignment_submit_form",function(t){u('textarea[name="assignment_answer"]').val().trim().length<1&&(u("#form_validation_response").html('<div class="tutor-error-msg">'+s("Assignment answer can not be empty","tutor")+"</div>"),t.preventDefault())}),u(document).on("click",".video_source_upload_wrap_html5 .video_upload_btn",function(t){t.preventDefault();var e,o=u(this);e||(e=wp.media({title:s("Select / Upload Media Of Your Chosen Persuasion","tutor"),button:{text:s("Use media","tutor")},library:{type:"video"},multiple:!1})).on("select",function(){var t=e.state().get("selection").first().toJSON();o.closest(".video_source_upload_wrap_html5").find("span.video_media_id").data("video_url",t.url).text(t.id).trigger("paste").closest("p").show(),o.closest(".video_source_upload_wrap_html5").find("input").val(t.id)}),e.open()}),u(document).on("click","a.tutor-delete-attachment",function(t){t.preventDefault(),u(this).closest(".tutor-added-attachment").remove()}),u(document).on("click",".tutorUploadAttachmentBtn",function(t){t.preventDefault();var n,a=u(this);n||(n=wp.media({title:s("Select / Upload Media Of Your Chosen Persuasion","tutor"),button:{text:s("Use media","tutor")},multiple:!0})).on("select",function(){var t=n.state().get("selection").toJSON();if(t.length)for(var e=0;e<t.length;e++){var o=t[e],o='<div class="tutor-added-attachment"><i class="tutor-icon-archive"></i><a href="javascript:;" class="tutor-delete-attachment tutor-icon-line-cross"></a> <span> <a href="'+o.url+'">'+o.filename+'</a> </span> <input type="hidden" name="tutor_attachments[]" value="'+o.id+'"></div>';a.closest(".tutor-lesson-attachments-metabox").find(".tutor-added-attachments-wrap").append(o)}}),n.open()}),u("form").on("change",".tutor-assignment-file-upload",function(){u(this).siblings("label").find("span").html(u(this).val().replace(/.*(\/|\\)/,""))}),u(document).on("click",".tutor-topics-in-single-lesson .tutor-topics-title h3, .tutor-single-lesson-topic-toggle",function(t){var e=u(this).closest(".tutor-topics-in-single-lesson");e.toggleClass("tutor-topic-active"),e.find(".tutor-lessons-under-topic").slideToggle()}),u(".tutor-single-lesson-items.active").closest(".tutor-lessons-under-topic").show(),u(".tutor-single-lesson-items.active").closest(".tutor-topics-in-single-lesson").addClass("tutor-topic-active"),u(".tutor-course-lesson.active").closest(".tutor-lessons-under-topic").show(),u(document).on("click",".tutor-create-assignments-btn",function(t){t.preventDefault();var e=u(this),o=u(this).attr("data-topic-id"),n=u("#post_ID").val();u.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{topic_id:o,course_id:n,action:"tutor_load_assignments_builder_modal"},beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){u(".tutor-lesson-modal-wrap .modal-container").html(t.data.output),u(".tutor-lesson-modal-wrap").attr("data-topic-id",o).addClass("show"),u(document).trigger("assignment_modal_loaded",{topic_id:o,course_id:n}),tinymce.init(tinyMCEPreInit.mceInit.course_description),tinymce.execCommand("mceRemoveEditor",!1,"tutor_assignments_modal_editor"),tinyMCE.execCommand("mceAddEditor",!1,"tutor_assignments_modal_editor")},complete:function(){quicktags({id:"tutor_assignments_modal_editor"}),e.removeClass("tutor-updating-message")}})}),u(document).on("click",".open-tutor-assignment-modal",function(t){t.preventDefault();var e=u(this),o=e.attr("data-assignment-id"),n=e.attr("data-topic-id"),a=u("#post_ID").val();u.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{assignment_id:o,topic_id:n,course_id:a,action:"tutor_load_assignments_builder_modal"},beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){u(".tutor-lesson-modal-wrap .modal-container").html(t.data.output),u(".tutor-lesson-modal-wrap").attr({"data-assignment-id":o,"data-topic-id":n}).addClass("show"),u(document).trigger("assignment_modal_loaded",{assignment_id:o,topic_id:n,course_id:a}),tinymce.init(tinyMCEPreInit.mceInit.course_description),tinymce.execCommand("mceRemoveEditor",!1,"tutor_assignments_modal_editor"),tinyMCE.execCommand("mceAddEditor",!1,"tutor_assignments_modal_editor")},complete:function(){quicktags({id:"tutor_assignments_modal_editor"}),e.removeClass("tutor-updating-message")}})}),u(document).on("click",".add-assignment-attachments",function(t){t.preventDefault();var o,n=u(this);o||(o=wp.media({title:s("Select / Upload Media Of Your Chosen Persuasion","tutor"),button:{text:s("Use media","tutor")},multiple:!1})).on("select",function(){var t=o.state().get("selection").first().toJSON(),e='<div class="tutor-individual-attachment-file"><p class="attachment-file-name">'+t.filename+'</p><input type="hidden" name="tutor_assignment_attachments[]" value="'+t.id+'"><a href="javascript:;" class="remove-assignment-attachment-a text-muted"> &times; Remove</a></div>';u("#assignment-attached-file").append(e),n.closest(".video_source_upload_wrap_html5").find("input").val(t.id)}),o.open()}),u(document).on("click",".remove-assignment-attachment-a",function(t){t.preventDefault(),u(this).closest(".tutor-individual-attachment-file").remove()}),"tutor_add_course_builder"===u('input[name="tutor_action"]').val()&&setInterval(function(){var t=u("form#tutor-frontend-course-builder").serializeObject();t.tutor_ajax_action="tutor_course_builder_draft_save",u.ajax({type:"POST",data:t,beforeSend:function(){u(".tutor-dashboard-builder-draft-btn span").text(s("Saving...","tutor"))},success:function(t){},complete:function(){u(".tutor-dashboard-builder-draft-btn span").text(s("Save","tutor"))}})},3e4),u(".tutor-course-builder-section-title").on("click",function(){u(this).find("i").hasClass("tutor-icon-up")?u(this).find("i").removeClass("tutor-icon-up").addClass("tutor-icon-down"):u(this).find("i").removeClass("tutor-icon-down").addClass("tutor-icon-up"),u(this).next("div").slideToggle()}),u(document).on("click",".open-tutor-edit-review-modal",function(t){t.preventDefault();var e=u(this),o=e.attr("data-review-id"),n=_tutorobject.nonce_key,t={review_id:o,action:"tutor_load_edit_review_modal"};t[n]=_tutorobject[n],u.ajax({url:_tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){void 0!==t.data&&(u(".tutor-edit-review-modal-wrap .modal-container").html(t.data.output),u(".tutor-edit-review-modal-wrap").attr("data-review-id",o).addClass("show"))},complete:function(){e.removeClass("tutor-updating-message")}})}),u(document).on("submit","#tutor_update_review_form",function(t){t.preventDefault();var e=u(this),o=e.closest(".tutor-edit-review-modal-wrap ").attr("data-review-id"),t=_tutorobject.nonce_key,o={review_id:o,rating:e.find('input[name="tutor_rating_gen_input"]').val(),review:e.find('textarea[name="review"]').val().trim(),action:"tutor_update_review_modal"};o[t]=_tutorobject[t],u.ajax({url:_tutorobject.ajaxurl,type:"POST",data:o,beforeSend:function(){e.find('button[type="submit"]').addClass("tutor-updating-message")},success:function(t){t.success&&(u(".tutor-edit-review-modal-wrap").removeClass("show"),location.reload(!0))},complete:function(){e.find('button[type="submit"]').removeClass("tutor-updating-message")}})}),u(document).on("click","#tutor_profile_photo_button",function(t){t.preventDefault(),u("#tutor_profile_photo_file").trigger("click")}),u(document).on("change","#tutor_profile_photo_file",function(t){t.preventDefault();this.files&&this.files[0]&&((t=new FileReader).onload=function(t){u(".tutor-profile-photo-upload-wrap").find("img").attr("src",t.target.result)},t.readAsDataURL(this.files[0]))}),u(document).on("click",".thread-content .subject",function(t){var e=u(this),o=parseInt(e.closest(".thread-content").attr("data-thread-id")),e=_tutorobject.nonce_key,o={thread_id:o,action:"tutor_bp_retrieve_user_records_for_thread"};o[e]=_tutorobject[e],u.ajax({type:"POST",url:window._tutorobject.ajaxurl,data:o,beforeSend:function(){u("#tutor-bp-thread-wrap").html("")},success:function(t){t.success&&(u("#tutor-bp-thread-wrap").html(t.data.thread_head_html),b())}})}),b(),u(document).on("click","a.tutor_bp_plus_courses",function(t){t.preventDefault();t=u(this);t.closest(".tutor-bp-enrolled-courses-wrap").find(".tutor-bp-enrolled-course-list li").show(),t.closest(".thread-participant-enrolled-info").html("")}),u(".tutor-dropbtn").click(function(){u(this).parent().find(".tutor-dropdown-content").slideToggle(100)}),u(".tutor-copy-link").click(function(t){var e=u(this),o=document.createElement("input"),n=window.location.href;document.body.appendChild(o),o.value=n,o.select(),document.execCommand("copy"),document.body.removeChild(o),e.html('<i class="tutor-icon-mark"></i> Copied'),setTimeout(function(){e.html('<i class="tutor-icon-copy"></i> Copy Link')},2500)}),u(document).on("click",function(t){var e=u(".tutor-dropdown"),o=e.find(".tutor-dropdown-content");e.is(t.target)||0!==e.has(t.target).length||o.slideUp(100)}),u(document).on("submit",".tutor-login-form-wrap #loginform",function(t){t.preventDefault();var t=u(this),e=u(".tutor-login-form-wrap"),t=t.serializeObject();t.action="tutor_user_login",u.ajax({url:_tutorobject.ajaxurl,type:"POST",data:t,success:function(t){t.success?(location.assign(t.data.redirect),location.reload()):(t=t.data||s("Invalid username or password!","tutor"),e.find(".tutor-alert").length?e.find(".tutor-alert").html(t):e.prepend('<div class="tutor-alert tutor-alert-warning">'+t+"</div>"))}})});var y,q=u('.tutor-frontend-builder-course-price [name="tutor_course_price_type"]');0==q.length?u("#_tutor_is_course_public_meta_checkbox").show():q.change(function(){var t;u(this).prop("checked")&&(t="paid"==u(this).val()?"hide":"show",u("#_tutor_is_course_public_meta_checkbox")[t]())}).trigger("change"),(y=jQuery).fn.tutor_tooltip=function(){return this.on("mouseenter click",".tooltip",function(t){t.stopPropagation(),y(this).removeClass("isVisible")}).on("mouseenter focus",":has(>.tooltip)",function(t){y(this).prop("disabled")||y(this).find(".tooltip").addClass("isVisible")}).on("mouseleave blur keydown",":has(>.tooltip)",function(t){("keydown"!==t.type||27===t.which)&&y(this).find(".tooltip").removeClass("isVisible")}),this},jQuery(".tutor-tooltip-inside").tutor_tooltip();var j=u(".tutor-course-filter-container form"),k=u(".tutor-course-filter-loop-container"),x={};j.on("submit",function(t){t.preventDefault()}).find("input").change(function(t){var e=Object.assign(j.serializeObject(),x);e.action="tutor_course_filter_ajax",k.html('<center><img src="'+window._tutorobject.loading_icon_url+'"/></center>'),u(this).closest("form").find(".tutor-clear-all-filter").show(),u.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:e,success:function(t){k.html(t).find(".tutor-pagination-wrap a").each(function(){u(this).attr("data-href",u(this).attr("href")).attr("href","#")})}})}),k.on("click",".tutor-pagination-wrap a",function(t){var e=u(this).data("href")||u(this).attr("href");!e||(e=(e=new URL(e)).searchParams.get("paged"))&&(t.preventDefault(),x.page=e,j.find("input:first").trigger("change"))}),k.on("change",'select[name="tutor_course_filter"]',function(){x.tutor_course_filter=u(this).val(),j.find("input:first").trigger("change")});q=u(".tutor-course-loop");0<q.length&&("yes"===window.sessionStorage.getItem("tutor_refresh_archive")&&window.location.reload(),window.sessionStorage.removeItem("tutor_refresh_archive"),q.on("click",".tutor-loop-cart-btn-wrap",function(){window.sessionStorage.setItem("tutor_refresh_archive","yes")}));q=u("#tutor_profile_cover_photo_editor");0<q.length&&new function(n){this.dialogue_box=n.find("#tutor_photo_dialogue_box"),this.open_dialogue_box=function(t){this.dialogue_box.attr("name",t),this.dialogue_box.trigger("click")},this.validate_image=function(t){return!0},this.upload_selected_image=function(t,e){var o,n,a;e&&this.validate_image(e)&&(o=tutor_get_nonce_data(!0),(n=this).toggle_loader(t,!0),(a=new FormData).append("action","tutor_user_photo_upload"),a.append("photo_type",t),a.append("photo_file",e,e.name),a.append(o.key,o.value),u.ajax({url:window._tutorobject.ajaxurl,data:a,type:"POST",processData:!1,contentType:!1,error:n.error_alert,complete:function(){n.toggle_loader(t,!1)}}))},this.accept_upload_image=function(t,e){var o=e.currentTarget.files[0]||null;t.update_preview(e.currentTarget.name,o),t.upload_selected_image(e.currentTarget.name,o),u(e.currentTarget).val("")},this.delete_image=function(t){var e=this;e.toggle_loader(t,!0),u.ajax({url:window._tutorobject.ajaxurl,data:{action:"tutor_user_photo_remove",photo_type:t},type:"POST",error:e.error_alert,complete:function(){e.toggle_loader(t,!1)}})},this.update_preview=function(t,e){var o=n.find("cover_photo"==t?"#tutor_cover_area":"#tutor_profile_area");if(!e)return o.css("background-image","url("+o.data("fallback")+")"),void this.delete_image(t);t=new FileReader;t.onload=function(t){o.css("background-image","url("+t.target.result+")")},t.readAsDataURL(e)},this.toggle_profile_pic_action=function(t){n[void 0===t?"toggleClass":t?"addClass":"removeClass"]("pop-up-opened")},this.error_alert=function(){alert("Something Went Wrong.")},this.toggle_loader=function(t,e){n.find("#tutor_photo_meta_area .loader-area").css("display",e?"block":"none")},this.initialize=function(){var e=this;this.dialogue_box.change(function(t){e.accept_upload_image(e,t)}),n.find("#tutor_profile_area .tutor_overlay, #tutor_pp_option>div:last-child").click(function(){e.toggle_profile_pic_action()}),n.find(".tutor_cover_uploader").click(function(){e.open_dialogue_box("cover_photo")}),n.find(".tutor_pp_uploader").click(function(){e.open_dialogue_box("profile_photo")}),n.find(".tutor_cover_deleter").click(function(){e.update_preview("cover_photo",null)}),n.find(".tutor_pp_deleter").click(function(){e.update_preview("profile_photo",null)})}}(q).initialize(),u(".tutor-instructor-filter").each(function(){var e,r=u(this),s={};function n(t,e,o){var n=r.find(".filter-result-container"),a=n.html(),i=r.data();i.current_page=o||1,t?s[t]=e:s={},s.attributes=i,s.action="load_filtered_instructor",n.html('<div style="text-align:center"><img src="'+window._tutorobject.loading_icon_url+'"/></div>'),u.ajax({url:window._tutorobject.ajaxurl,data:s,type:"POST",success:function(t){n.html(t)},error:function(){n.html(a),tutor_toast("Failed","Request Error","error")}})}r.on("change",'.course-category-filter [type="checkbox"]',function(){var e={};u(this).closest(".course-category-filter").find("input:checked").each(function(){e[u(this).val()]=u(this).parent().text()});var o=r.find(".selected-cate-list").empty(),t=Object.keys(e);t.forEach(function(t){o.append("<span>"+e[t]+' <span class="tutor-icon-line-cross" data-cat_id="'+t+'"></span></span>')}),t.length&&o.append('<span data-cat_id="0">Clear All</span>'),n(u(this).attr("name"),t)}).on("click",".selected-cate-list [data-cat_id]",function(){var t=u(this).data("cat_id"),e=r.find('.mobile-filter-popup [type="checkbox"]');(e=t?e.filter('[value="'+t+'"]'):e).prop("checked",!1).trigger("change")}).on("input",'.filter-pc [name="keyword"]',function(){var t=u(this).val();e&&window.clearTimeout(e),e=window.setTimeout(function(){n("keyword",t),e=null},500)}).on("click","[data-page_number]",function(t){t.preventDefault(),n(null,null,u(this).data("page_number"))}).on("click",".clear-instructor-filter",function(){var t=u(this).closest(".tutor-instructor-filter");t.find('input[type="checkbox"]').prop("checked",!1),t.find('[name="keyword"]').val(""),n()}).on("click",".mobile-filter-container i",function(){u(this).parent().next().addClass("is-opened")}).on("click",".mobile-filter-popup button",function(){u('.mobile-filter-popup [type="checkbox"]').trigger("change"),u(this).closest(".mobile-filter-popup").removeClass("is-opened")}).on("input",'.filter-mobile [name="keyword"]',function(){r.find('.filter-pc [name="keyword"]').val(u(this).val()).trigger("input")}).on("change",'.mobile-filter-popup [type="checkbox"]',function(t){var e,o;t.originalEvent||(e=u(this).attr("name"),o=u(this).val(),t=u(this).prop("checked"),r.find('.course-category-filter [name="'+e+'"]').filter('[value="'+o+'"]').prop("checked",t).trigger("change"))}).on("mousedown touchstart",".expand-instructor-filter",function(t){var e=u(window).height(),o=r.find(".mobile-filter-popup>div"),n=e-o.height(),a=((t.originalEvent.touches||[])[0]||t).clientY-n;r.on("mousemove touchmove",function(t){t=((t.originalEvent.touches||[])[0]||t).clientY,t=e-t+a;200<t&&t<=e&&o.css("height",t+"px")})}).on("mouseup touchend",function(){r.off("mousemove touchmove")}).on("click",".mobile-filter-popup>div",function(t){t.stopImmediatePropagation()}).on("click",".mobile-filter-popup",function(t){u(this).removeClass("is-opened")})}),u(".tutor-course-retake-button").click(function(t){t.preventDefault();var t=u(this),e=t.attr("href"),o=t.data("course_id"),t={title:s("Override Previous Progress","tutor"),description:s("Before continue, please decide whether to keep progress or reset.","tutor"),buttons:{reset:{title:s("Reset Data","tutor"),class:"secondary",callback:function(){var t=n.find(".tutor-button-secondary");t.prop("disabled",!0).append('<img style="margin-left: 7px" src="'+window._tutorobject.loading_icon_url+'"/>'),u.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{action:"tutor_reset_course_progress",course_id:o},success:function(t){t.success?window.location.assign(t.data.redirect_to):alert((t.data||{}).message||s("Something went wrong","tutor"))},complete:function(){t.prop("disabled",!1).find("img").remove()}})}},keep:{title:s("Keep Data","tutor"),class:"primary",callback:function(){window.location.assign(e)}}}},n=new window.tutor_popup(u,"icon-gear",40).popup(t)}),document.body.addEventListener("click",function(t){var e,o=t.target,n=o.tagName,a=o.parentElement.tagName;0<m.length&&"EXPIRED"!=m.html()&&("A"!==n&&"A"!==a||(t.preventDefault(),t.stopImmediatePropagation(),t={title:s("Abandon Quiz?","tutor"),description:s("Do you want to abandon this quiz? The quiz will be submitted partially up to this question if you leave this page.","tutor"),buttons:{keep:{title:s("Yes, leave quiz","tutor"),id:"leave",class:"secondary",callback:function(){var t=u("form#tutor-answering-quiz").serialize()+"&action=tutor_quiz_abandon";u.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){document.querySelector("#tutor-popup-leave").innerHTML=s("Leaving...","tutor")},success:function(t){t.success?null==o.href?location.href=o.parentElement.href:location.href=o.href:alert(s("Something went wrong","tutor"))},error:function(){alert(s("Something went wrong","tutor")),e.remove()}})}},reset:{title:s("Stay here","tutor"),id:"reset",class:"primary",callback:function(){e.remove()}}}},e=new window.tutor_popup(u,"",40).popup(t)))}),u("body").on("submit","form#tutor-start-quiz",function(){u(this).find("button").prop("disabled",!0)})})})()})();
=======

jQuery(document).ready(function ($) {
    'use strict';
    /**
     * wp.i18n translateable functions 
     * @since 1.9.0
    */
    const { __, _x, _n, _nx } = wp.i18n;
    /**
     * Initiate Select2
     * @since v.1.3.4
     */
    if (jQuery().select2) {
        $('.tutor_select2').select2({
            escapeMarkup: function (markup) {
                return markup;
            }
        });
    }
    //END: select2


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
    !function (a) { function f(a, b) { if (!(a.originalEvent.touches.length > 1)) { a.preventDefault(); var c = a.originalEvent.changedTouches[0], d = document.createEvent("MouseEvents"); d.initMouseEvent(b, !0, !0, window, 1, c.screenX, c.screenY, c.clientX, c.clientY, !1, !1, !1, !1, 0, null), a.target.dispatchEvent(d) } } if (a.support.touch = "ontouchend" in document, a.support.touch) { var e, b = a.ui.mouse.prototype, c = b._mouseInit, d = b._mouseDestroy; b._touchStart = function (a) { var b = this; !e && b._mouseCapture(a.originalEvent.changedTouches[0]) && (e = !0, b._touchMoved = !1, f(a, "mouseover"), f(a, "mousemove"), f(a, "mousedown")) }, b._touchMove = function (a) { e && (this._touchMoved = !0, f(a, "mousemove")) }, b._touchEnd = function (a) { e && (f(a, "mouseup"), f(a, "mouseout"), this._touchMoved || f(a, "click"), e = !1) }, b._mouseInit = function () { var b = this; b.element.bind({ touchstart: a.proxy(b, "_touchStart"), touchmove: a.proxy(b, "_touchMove"), touchend: a.proxy(b, "_touchEnd") }), c.call(b) }, b._mouseDestroy = function () { var b = this; b.element.unbind({ touchstart: a.proxy(b, "_touchStart"), touchmove: a.proxy(b, "_touchMove"), touchend: a.proxy(b, "_touchEnd") }), d.call(b) } } }(jQuery);

    /**
     * END jQuery UI Touch Punch
     */

    const videoPlayer = {
        ajaxurl: window._tutorobject.ajaxurl,
        nonce_key: window._tutorobject.nonce_key,
        video_data: function () {
            const video_track_data = $('#tutor_video_tracking_information').val();
            return video_track_data ? JSON.parse(video_track_data) : {};
        },
        track_player: function () {
            const that = this;
            if (typeof Plyr !== 'undefined') {
                const player = new Plyr('#tutorPlayer');
                const video_data = that.video_data();
                player.on('ready', function (event) {
                    const instance = event.detail.plyr;
                    const { best_watch_time } = video_data;
                    if (best_watch_time > 0 && instance.duration > Math.round(best_watch_time)) {
                        instance.media.currentTime = best_watch_time;
                    }
                    that.sync_time(instance);
                });

                let tempTimeNow = 0;
                let intervalSeconds = 30; //Send to tutor backend about video playing time in this interval
                player.on('timeupdate', function (event) {
                    const instance = event.detail.plyr;
                    const tempTimeNowInSec = (tempTimeNow / 4); //timeupdate firing 250ms interval
                    if (tempTimeNowInSec >= intervalSeconds) {
                        that.sync_time(instance);
                        tempTimeNow = 0;
                    }
                    tempTimeNow++;
                });

                player.on('ended', function (event) {
                    const video_data = that.video_data();
                    const instance = event.detail.plyr;
                    const data = { is_ended: true };
                    that.sync_time(instance, data);
                    if (video_data.autoload_next_course_content) {
                        that.autoload_content();
                    }
                });
            }
        },
        sync_time: function (instance, options) {
            const post_id = this.video_data().post_id;
            //TUTOR is sending about video playback information to server.
            let data = { action: 'sync_video_playback', currentTime: instance.currentTime, duration: instance.duration, post_id };
            data[this.nonce_key] = _tutorobject[this.nonce_key];
            let data_send = data;
            if (options) {
                data_send = Object.assign(data, options);
            }
            $.post(this.ajaxurl, data_send);
        },
        autoload_content: function () {
            const post_id = this.video_data().post_id;
            const data = { action: 'autoload_next_course_content', post_id };
            data[this.nonce_key] = _tutorobject[this.nonce_key];
            $.post(this.ajaxurl, data).done(function (response) {
                if (response.success && response.data.next_url) {
                    location.href = response.data.next_url;
                }
            });
        },
        init: function () {
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
        return text.toString().toLowerCase()
            .replace(/\s+/g, '-')           // Replace spaces with -
            .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
            .replace(/\-\-+/g, '-')         // Replace multiple - with single -
            .replace(/^-+/, '')             // Trim - from start of text
            .replace(/-+$/, '');            // Trim - from end of text
    }

    function toggle_star_(star){
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

    $(document).on('mouseout', '.tutor-star-rating-container .tutor-star-rating-group', function(){
        var value = $(this).find('input[name="tutor_rating_gen_input"]').val();
        var rating = parseInt(value);
        
        var selected = $(this).find('[data-rating-value="'+rating+'"]');
        (rating && selected && selected.length>0) ? toggle_star_(selected) : $(this).find('i').removeClass('tutor-icon-star-full').addClass('tutor-icon-star-line');
    });

    $(document).on('click', '.tutor_submit_review_btn', function (e) {
        e.preventDefault();
        var $that = $(this);
        var rating = $that.closest('form').find('input[name="tutor_rating_gen_input"]').val();
        var review = $that.closest('form').find('textarea[name="review"]').val();
        review = review.trim();

        var course_id = $('input[name="tutor_course_id"]').val();
        var data = { course_id: course_id, rating: rating, review: review, action: 'tutor_place_rating' };

        if(!rating || rating==0 || !review) {
            alert(__('Rating and review required', 'tutor'));
            return;
        }

        if (review) {
            $.ajax({
                url: _tutorobject.ajaxurl,
                type: 'POST',
                data: data,
                beforeSend: function () {
                    $that.addClass('updating-icon');
                },
                success: function (data) {
                    var review_id = data.data.review_id;
                    var review = data.data.review;
                    $('.tutor-review-' + review_id + ' .review-content').html(review);
                    
                    // Show thank you
                    new window.tutor_popup($, 'icon-rating', 40).popup({
                        title: __('Thank You for Rating This Course!', 'tutor'),
                        description : __('Your rating will now be visible in the course page', 'tutor'),
                    });

                    setTimeout(function(){
                        location.reload();
                    }, 3000);
                }
            });
        }
    }).on('click', '.tutor_cancel_review_btn', function() {
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
            beforeSend: function () {
                $form.find('.tutor_ask_question_btn').addClass('updating-icon');
            },
            success: function (data) {
                if (data.success) {
                    $('.tutor-add-question-wrap').hide();
                    window.location.reload();
                }
            },
            complete: function () {
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
            beforeSend: function () {
                $form.find('.tutor_add_answer_btn').addClass('updating-icon');
            },
            success: function (data) {
                if (data.success) {
                    window.location.reload();
                }
            },
            complete: function () {
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
            },
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
            var countDownDate = new Date(attempt_settings.attempt_started_at).getTime() + (attempt_meta.time_limit.time_limit_seconds * 1000);
            var time_now = new Date(attempt_meta.date_time_now).getTime();

            var tutor_quiz_interval = setInterval(function () {
                var distance = countDownDate - time_now;

                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

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
                    countdown_human = "EXPIRED";
                    //Set the quiz attempt to timeout in ajax
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
                        var quiz_timeout_data = { quiz_id: quiz_id, action: 'tutor_quiz_timeout' };
        
                        var att = $("#tutor-quiz-time-expire-wrapper").attr('data-attempt-remaining');

                        //disable buttons
                        $(".tutor-quiz-answer-next-btn, .tutor-quiz-submit-btn, .tutor-quiz-answer-previous-btn").prop('disabled', true);

                        //add alert text
                        $(".time-remaining span").css('color', '#F44337');
                        
                        $.ajax({
                            url: _tutorobject.ajaxurl,
                            type: 'POST',
                            data: quiz_timeout_data,
                            success: function (data) {

                                var attemptAllowed = $("#tutor-quiz-time-expire-wrapper").data('attempt-allowed');
                                var attemptRemaining = $("#tutor-quiz-time-expire-wrapper").data('attempt-remaining');

                                var alertDiv = "#tutor-quiz-time-expire-wrapper .tutor-alert";
                                $(alertDiv).addClass('show');
                                if ( att > 0 ) {
                                    $(`${alertDiv} .text`).html(
                                        __( 'Your time limit for this quiz has expired, please reattempt the quiz. Attempts remaining: '+ attemptRemaining+'/'+attemptAllowed, 'tutor' )
                                    );                            
                                } else {
                                    $(alertDiv).addClass('tutor-alert-danger');
                                    $("#tutor-start-quiz").hide();
                                    $(`${alertDiv} .text`).html(
                                        `${__( 'Unfortunately, you are out of time and quiz attempts. ', 'tutor' )}`
                                    );
                                }

                            },
                            complete: function () {

                            }
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
            data: { action: 'review_quiz_answer', attempt_id: attempt_id, attempt_answer_id: attempt_answer_id, mark_as: mark_as },
            beforeSend: function () {
                $that.find('i').addClass('updating-icon');
            },
            success: function (data) {
                location.reload();
            },
            complete: function () {
                $that.find('i').removeClass('updating-icon');
            }
        });
    });

    // Quiz Review : Tooltip
    $(".tooltip-btn").on("hover", function (e) {
        $(this).toggleClass("active");
    });

    // tutor course content accordion

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
            data: { course_id: course_id, 'action': 'tutor_course_add_to_wishlist' },
            beforeSend: function () {
                $that.addClass('updating-icon');
            },
            success: function (data) {
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
            complete: function () {
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
                data: { lesson_id: lesson_id, 'action': 'tutor_render_lesson_content' },
                beforeSend: function () {
                    var page_title = $that.find('.lesson_title').text();
                    $('head title').text(page_title);
                    window.history.pushState('obj', page_title, $that.attr('href'));
                    $wrap.addClass('loading-lesson');
                    $('.tutor-single-lesson-items').removeClass('active');
                    $that.closest('.tutor-single-lesson-items').addClass('active');
                },
                success: function (data) {
                    $wrap.html(data.data.html);
                    videoPlayer.init();
                    $('.tutor-lesson-sidebar').css('display', '');
                    window.dispatchEvent(new window.Event('tutor_ajax_lesson_loaded')); // Some plugins like h5p needs notification on ajax load
                },
                complete: function () {
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
                data: { quiz_id: quiz_id, 'action': 'tutor_render_quiz_content' },
                beforeSend: function () {
                    $('head title').text(page_title);
                    window.history.pushState('obj', page_title, $that.attr('href'));
                    $wrap.addClass('loading-lesson');
                    $('.tutor-single-lesson-items').removeClass('active');
                    $that.closest('.tutor-single-lesson-items').addClass('active');
                },
                success: function (data) {
                    $wrap.html(data.data.html);
                    init_quiz_builder();
                    $('.tutor-lesson-sidebar').css('display', '');
                },
                complete: function () {
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
                start: function (e, ui) {
                    ui.placeholder.css('visibility', 'visible');
                },
                stop: function (e, ui) {
                    //Sorting Stopped...
                },
            }).disableSelection();

            $(".quiz-draggable-rand-answers, .quiz-answer-matching-droppable").sortable({
                connectWith: ".quiz-answer-matching-droppable",
                placeholder: "drop-hover",
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
        e.preventDefault();

        // Show previous quiz if press previous button
        if($(this).hasClass('tutor-quiz-answer-previous-btn')) {
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
                if(feedBackMode === 'reveal') {
                    setTimeout(()=>{
                        $('.quiz-attempt-single-question').hide();
                         $nextQuestion.show();
                    }, 
                    800);  
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
        $question.show();

        //Active Class
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

            $that.css({ "height": draggableDivHeight });
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
                        $question_wrap.find('.answer-help-block').html(`<p style="color: #dc3545">${__('Please select an option to answer', 'tutor')}</p>`);
                        validated = false;
                    }
                } else if ($type === 'checkbox') {
                    if ($required_answer_wrap.find('input[type="checkbox"]:checked').length == 0) {
                        $question_wrap.find('.answer-help-block').html(`<p style="color: #dc3545">${__('Please select at least one option to answer.', 'tutor')}</p>`);
                        validated = false;
                    }
                } else if ($type === 'text') {
                    //Fill in the gaps if many, validation all
                    $inputs.each(function (index, input) {
                        if (!$(input).val().trim().length) {
                            $question_wrap.find('.answer-help-block').html(`<p style="color: #dc3545">${__('The answer for this question is required', 'tutor')}</p>`);
                            validated = false;
                        }
                    });
                }

            }
            if ($required_answer_wrap.find('textarea').length) {
                if ($required_answer_wrap.find('textarea').val().trim().length < 1) {
                    $question_wrap.find('.answer-help-block').html(`<p style="color: #dc3545">${__('The answer for this question is required', 'tutor')}</p>`);
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
                        $question_wrap.find('.answer-help-block').html(`<p style="color: #dc3545">${__('Please match all the items', 'tutor')}</p>`);
                        validated = false;
                    }
                });
            }
        }

        return validated;
    }

    function feedback_response($question_wrap) {
        var goNext = false;

        // Prepare answer array            
        var quiz_answers = JSON.parse(atob(window.tutor_quiz_context.split('').reverse().join('')));
        !Array.isArray(quiz_answers) ? quiz_answers=[] : 0;
        
        // Evaluate result
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
                    var isTrue = quiz_answers.indexOf($input.val())>-1; // $input.attr('data-is-correct') == '1';
                    if ( !isTrue) {
                        if ($input.prop("checked")) {
                            $input.closest('.quiz-answer-input-bottom').addClass('wrong-answer').append(`<span class="wrong-right-text"><i class="tutor-icon-line-cross"></i> ${__('Incorrect, Please try again', 'tutor')}</span>`);
                        }
                        validatedTrue = false;
                    }
                }
            });

            $inputs.each(function () {
                var $input = $(this);
                var $type = $input.attr('type');
                if ($type === 'checkbox') {
                    var isTrue = quiz_answers.indexOf($input.val())>-1; // $input.attr('data-is-correct') == '1';
                    var checked = $input.is(':checked');
                
                    if (isTrue && !checked) {
                        $question_wrap.find('.answer-help-block').html(`<p style="color: #dc3545">${__('More answer for this question is required', 'tutor')}</p>`);
                        validatedTrue = false;
                    }
                }
            });

        } else if (feedBackMode === 'reveal') {
            $checkedInputs.each(function () {
                var $input = $(this);
                var isTrue = quiz_answers.indexOf($input.val())>-1; // $input.attr('data-is-correct') == '1';
                if (!isTrue) {
                    validatedTrue = false;
                }
            });

            $inputs.each(function () {
                var $input = $(this);

                var $type = $input.attr('type');
                if ($type === 'radio' || $type === 'checkbox') {
                    var isTrue = quiz_answers.indexOf($input.val())>-1; // $input.attr('data-is-correct') == '1';
                    var checked = $input.is(':checked');

                    if (isTrue) {
                        $input.closest('.quiz-answer-input-bottom').addClass('right-answer').append(`<span class="wrong-right-text"><i class="tutor-icon-checkbox-pen-outline"></i>${__('Correct Answer', 'tutor')}</span>`);
                    } else {
                        if ($input.prop("checked")) {
                            $input.closest('.quiz-answer-input-bottom').addClass('wrong-answer');
                        }
                    }

                    if (isTrue && !checked) {
                        $input.attr('disabled','disabled');
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
                height: 480,
            });
        }
    }

    /**
     * Datepicker initiate
     *
     * @since v.1.1.2
     */
    if (jQuery.datepicker) {
        $(".tutor_report_datepicker").datepicker({ "dateFormat": 'yy-mm-dd' });
    }


    /**
     * Withdraw Form Tab/Toggle
     *
     * @since v.1.1.2
     */

    $(".withdraw-method-select-input").on('change', function (e) {
        var $that = $(this);
        $('.withdraw-method-form').hide();
        $('#withdraw-method-form-' + $that.closest('.withdraw-method-select').attr('data-withdraw-method')).show();
    });

    $('.withdraw-method-select-input').each(function () {
        var $that = $(this);
        if ($that.is(":checked")) {
            $('.withdraw-method-form').hide();
            $('#withdraw-method-form-' + $that.closest('.withdraw-method-select').attr('data-withdraw-method')).show();
        }
    });



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
            beforeSend: function () {
                $form.find('.tutor-success-msg').remove();
                $btn.addClass('updating-icon');
            },
            success: function (data) {
                if (data.success) {
                    var successMsg = '<div class="tutor-success-msg" style="display: none;"><i class="tutor-icon-mark"></i> ' + data.data.msg + ' </div>';
                    $btn.closest('.withdraw-account-save-btn-wrap').append(successMsg);
                    if ($form.find('.tutor-success-msg').length) {
                        $form.find('.tutor-success-msg').slideDown();
                    }
                    setTimeout(function () {
                        $form.find('.tutor-success-msg').slideUp();
                    }, 5000)
                }
            },
            complete: function () {
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

        if($(this).data('reload')=='yes'){
            window.location.reload();
            return;
        }

        $('.tutor-earning-withdraw-form-wrap').toggle().find('[name="tutor_withdraw_amount"]').val('');
        $('.tutor-withdrawal-pop-up-success').hide().next().show();
        $('html, body').css('overflow', ($('.tutor-earning-withdraw-form-wrap').is(':visible') ? 'hidden' : 'auto'));
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
            beforeSend: function () {
                $form.find('.tutor-success-msg').remove();
                $btn.addClass('updating-icon');
            },
            success: function (data) {
                var Msg;
                if (data.success) {

                    if (data.data.available_balance !== 'undefined') {
                        $('.withdraw-balance-col .available_balance').html(data.data.available_balance);
                    }

                    $('.tutor-withdrawal-pop-up-success').show().next().hide();

                } else {
                    Msg = '<div class="tutor-error-msg inline-image-text is-inline-block">\
                            <img src="'+window._tutorobject.tutor_url+'assets/images/icon-cross.svg"/> \
                            <div>\
                                <b>Error</b><br/>\
                                <span>'+ data.data.msg + '</span>\
                            </div>\
                        </div>';

                    $responseDiv.html(Msg);
                    setTimeout(function () {
                        $responseDiv.html('');
                    }, 5000)
                }
            },
            complete: function () {
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
            beforeSend: function () {
                $btn.addClass('updating-icon');
            },
            success: function (res) {
                if (res.success) {
                    $('#tutor-dashboard-' + res.data.element + '-' + element_id).remove();
                }
            },
            complete: function () {
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
            data: { 'action': 'tutor_profile_photo_remove' },
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
            beforeSend: function () {
                $('#tutor_assignment_start_btn').addClass('updating-icon');
            },
            success: function (data) {
                if (data.success) {
                    location.reload();
                }
            },
            complete: function () {
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
        var frame;
        // If the media frame already exists, reopen it.
        if (frame) {
            frame.open();
            return;
        }
        frame = wp.media({
            title: __( 'Select / Upload Media Of Your Chosen Persuasion', 'tutor' ),
            button: {
                text: __( 'Use media', 'tutor' )
            },
            library: { type: 'video' },
            multiple: false  // Set to true to allow multiple files to be selected
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
     * Course and lesson sorting
     */

    function enable_sorting_topic_lesson() {
        if (jQuery().sortable) {
            $(".course-contents").sortable({
                handle: ".course-move-handle",
                start: function (e, ui) {
                    ui.placeholder.css('visibility', 'visible');
                },
                stop: function (e, ui) {
                    tutor_sorting_topics_and_lesson();
                },
            });
            $(".tutor-lessons:not(.drop-lessons)").sortable({
                connectWith: ".tutor-lessons",
                items: "div.course-content-item",
                start: function (e, ui) {
                    ui.placeholder.css('visibility', 'visible');
                },
                stop: function (e, ui) {
                    tutor_sorting_topics_and_lesson();
                },
            });
        }
    }
    enable_sorting_topic_lesson();
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
            topics[index] = { 'topic_id': topics_id, 'lesson_ids': lessons };
        });
        $('#tutor_topics_lessons_sorting').val(JSON.stringify(topics));
    }

    /**
     * Lesson Update or Create Modal
     */
    $(document).on('click', '.update_lesson_modal_btn', function (event) {
        event.preventDefault();

        var $that = $(this);
        var content;
        var editor = tinyMCE.get('tutor_lesson_modal_editor');
        if (editor) {
            content = editor.getContent();
        } else {
            content = $('#' + inputid).val();
        }

        var form_data = $(this).closest('form').serializeObject();
        form_data.lesson_content = content;

        $.ajax({
            url: window._tutorobject.ajaxurl,
            type: 'POST',
            data: form_data,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success) {
                    $('#tutor-course-content-wrap').html(data.data.course_contents);
                    enable_sorting_topic_lesson();

                    //Close the modal
                    $('.tutor-lesson-modal-wrap').removeClass('show');
                    
                    tutor_toast(__('Done', 'tutor'), $that.data('toast_success_message'), 'success');
                }
                else {
                    tutor_toast(__('Failed', 'tutor'), __('Lesson Update Failed', 'tutor'), 'error');
                }
            },
            error: function() {
                tutor_toast(__('Failed', 'tutor'), __('Lesson Update Failed', 'tutor'), 'error');
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * END: Tutor Course builder JS
     */

    /**
     * Attachment in forntend course builder
     * @since v.1.3.4
     */
    $(document).on('click', 'a.tutor-delete-attachment', function (e) {
        e.preventDefault();
        $(this).closest('.tutor-added-attachment').remove();
    });
    $(document).on('click', '.tutorUploadAttachmentBtn', function (e) {
        e.preventDefault();

        var $that = $(this);
        var frame;
        if (frame) {
            frame.open();
            return;
        }
        frame = wp.media({
            title: __( 'Select / Upload Media Of Your Chosen Persuasion', 'tutor' ),
            button: {
                text: __( 'Use media', 'tutor' )
            },
            multiple: true  // Set to true to allow multiple files to be selected
        });
        frame.on('select', function () {
            var attachments = frame.state().get('selection').toJSON();
            if (attachments.length) {
                for (var i = 0; i < attachments.length; i++) {
                    var attachment = attachments[i];

                    var inputHtml = '<div class="tutor-added-attachment"><i class="tutor-icon-archive"></i><a href="javascript:;" class="tutor-delete-attachment tutor-icon-line-cross"></a> <span> <a href="' + attachment.url + '">' + attachment.filename + '</a> </span> <input type="hidden" name="tutor_attachments[]" value="' + attachment.id + '"></div>';
                    $that.closest('.tutor-lesson-attachments-metabox').find('.tutor-added-attachments-wrap').append(inputHtml);
                }
            }
        });
        frame.open();
    });


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
     * Assignments Addons
     * @backend Support
     *
     */


    /**
     * Tutor Assignments JS
     * @since v.1.3.3
     */
    $(document).on('click', '.tutor-create-assignments-btn', function (e) {
        e.preventDefault();

        var $that = $(this);
        var topic_id = $(this).attr('data-topic-id');
        var course_id = $('#post_ID').val();

        $.ajax({
            url: window._tutorobject.ajaxurl,
            type: 'POST',
            data: { topic_id: topic_id, course_id: course_id, action: 'tutor_load_assignments_builder_modal' },
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('.tutor-lesson-modal-wrap .modal-container').html(data.data.output);
                $('.tutor-lesson-modal-wrap').attr('data-topic-id', topic_id).addClass('show');

                $(document).trigger('assignment_modal_loaded', { topic_id: topic_id, course_id: course_id });

                tinymce.init(tinyMCEPreInit.mceInit.course_description);
                tinymce.execCommand('mceRemoveEditor', false, 'tutor_assignments_modal_editor');
                tinyMCE.execCommand('mceAddEditor', false, "tutor_assignments_modal_editor");
            },
            complete: function () {
                quicktags({ id: "tutor_assignments_modal_editor" });
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    $(document).on('click', '.open-tutor-assignment-modal', function (e) {
        e.preventDefault();

        var $that = $(this);
        var assignment_id = $that.attr('data-assignment-id');
        var topic_id = $that.attr('data-topic-id');
        var course_id = $('#post_ID').val();

        $.ajax({
            url: window._tutorobject.ajaxurl,
            type: 'POST',
            data: { assignment_id: assignment_id, topic_id: topic_id, course_id: course_id, action: 'tutor_load_assignments_builder_modal' },
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('.tutor-lesson-modal-wrap .modal-container').html(data.data.output);
                $('.tutor-lesson-modal-wrap').attr({ 'data-assignment-id': assignment_id, 'data-topic-id': topic_id }).addClass('show');

                $(document).trigger('assignment_modal_loaded', { assignment_id: assignment_id, topic_id: topic_id, course_id: course_id });

                tinymce.init(tinyMCEPreInit.mceInit.course_description);
                tinymce.execCommand('mceRemoveEditor', false, 'tutor_assignments_modal_editor');
                tinyMCE.execCommand('mceAddEditor', false, "tutor_assignments_modal_editor");
            },
            complete: function () {
                quicktags({ id: "tutor_assignments_modal_editor" });
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
        var editor = tinyMCE.get('tutor_assignments_modal_editor');
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
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success) {
                    $('#tutor-course-content-wrap').html(data.data.course_contents);
                    enable_sorting_topic_lesson();

                    //Close the modal
                    $('.tutor-lesson-modal-wrap').removeClass('show');

                    tutor_toast(__('Done', 'tutor'), $that.data('toast_success_message'), 'success');
                }
                else {
                    tutor_toast(__('Failed', 'tutor'), __('Assignment Update Failed', 'tutor'), 'error');
                }
            },
            error: function() {
                tutor_toast(__('Failed', 'tutor'), __('Assignment Update Failed', 'tutor'), 'error');
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Add Assignment
     */
    $(document).on('click', '.add-assignment-attachments', function (event) {
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
            title: __( 'Select / Upload Media Of Your Chosen Persuasion', 'tutor' ),
            button: {
                text: __( 'Use media', 'tutor' )
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected in the media frame...
        frame.on('select', function () {
            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();

            var field_markup = '<div class="tutor-individual-attachment-file"><p class="attachment-file-name">' + attachment.filename + '</p><input type="hidden" name="tutor_assignment_attachments[]" value="' + attachment.id + '"><a href="javascript:;" class="remove-assignment-attachment-a text-muted"> &times; Remove</a></div>';

            $('#assignment-attached-file').append(field_markup);
            $that.closest('.video_source_upload_wrap_html5').find('input').val(attachment.id);
        });
        // Finally, open the modal on click
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
            beforeSend: function () {
                $('.tutor-dashboard-builder-draft-btn span').text( __( 'Saving...', 'tutor' ) );
            },
            success: function (data) {

            },
            complete: function () {
                $('.tutor-dashboard-builder-draft-btn span').text( __( 'Save', 'tutor' ) );
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

        var json_data = { review_id: review_id, action: 'tutor_load_edit_review_modal' };
        json_data[nonce_key] = _tutorobject[nonce_key];

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: json_data,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (typeof data.data !== 'undefined') {
                    $('.tutor-edit-review-modal-wrap .modal-container').html(data.data.output);
                    $('.tutor-edit-review-modal-wrap').attr('data-review-id', review_id).addClass('show');
                }
            },
            complete: function () {
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

        var json_data = { review_id: review_id, rating: rating, review: review, action: 'tutor_update_review_modal' };
        json_data[nonce_key] = _tutorobject[nonce_key];

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: json_data,
            beforeSend: function () {
                $that.find('button[type="submit"]').addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success) {
                    //Close the modal
                    $('.tutor-edit-review-modal-wrap').removeClass('show');
                    location.reload(true);
                }
            },
            complete: function () {
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
            }
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
        var json_data = { thread_id: thread_id, action: 'tutor_bp_retrieve_user_records_for_thread' };
        json_data[nonce_key] = _tutorobject[nonce_key];

        $.ajax({
            type: 'POST',
            url: window._tutorobject.ajaxurl,
            data: json_data,
            beforeSend: function () {
                $('#tutor-bp-thread-wrap').html('');
            },
            success: function (data) {
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
    $('.tutor-dropbtn').click(function(){
       
        var $content = $(this).parent().find(".tutor-dropdown-content");
        $content.slideToggle(100);
    })


    //$(document).on('click', '.tutor-copy-link', function (e) {
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
        var $content = container.find('.tutor-dropdown-content');
        // if the target of the click isn't the container nor a descendant of the container
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
            success: function (response) {
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
            },
        });
    });

    /**
     * Show hide is course public checkbox (frontend dashboard editor)
     * 
     * @since  v.1.7.2
    */
    var price_type = $('.tutor-frontend-builder-course-price [name="tutor_course_price_type"]');
    if(price_type.length==0){
        $('#_tutor_is_course_public_meta_checkbox').show();
    }
    else{
        price_type.change(function(){
            if($(this).prop('checked')){
                var method = $(this).val()=='paid' ? 'hide' : 'show';
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
        this
    
        // Delegate to tooltip, Hide if tooltip receives mouse or is clicked (tooltip may stick if parent has focus)
            .on('mouseenter click', '.tooltip', function (e) {
            e.stopPropagation();
            $(this).removeClass('isVisible');
            })
            // Delegate to parent of tooltip, Show tooltip if parent receives mouse or focus
            .on('mouseenter focus', ':has(>.tooltip)', function (e) {
            if (!$(this).prop('disabled')) { // IE 8 fix to prevent tooltip on `disabled` elements
                $(this)
                .find('.tooltip')
                .addClass('isVisible');
            }
            })
            // Delegate to parent of tooltip, Hide tooltip if parent loses mouse or focus
            .on('mouseleave blur keydown', ':has(>.tooltip)', function (e) {
            if (e.type === 'keydown') {
                if(e.which === 27) {
                $(this)
                    .find('.tooltip')
                    .removeClass('isVisible');
                }
            } else {
                $(this)
                .find('.tooltip')
                .removeClass('isVisible');
            }
            });
        return this;
        };
    }(jQuery));
    
    // Bind event listener to container element
    jQuery('.tutor-tooltip-inside').tutor_tooltip();
    

    
    /**
     * Manage course filter
     * 
     * @since  v.1.7.2
    */
    var filter_container = $('.tutor-course-filter-container form');
    var loop_container = $('.tutor-course-filter-loop-container');
    var filter_modifier = {};
    
    // Sidebar checkbox value change
    filter_container.on('submit', function(e) {
        e.preventDefault();
    })
    .find('input').change(function(e){
        
        var filter_criteria = Object.assign( filter_container.serializeObject(), filter_modifier);
        filter_criteria.action = 'tutor_course_filter_ajax';

        loop_container.html('<center><img src="'+window._tutorobject.loading_icon_url+'"/></center>');
        $(this).closest('form').find('.tutor-clear-all-filter').show();

        $.ajax({
            url:window._tutorobject.ajaxurl,
            type:'POST',
            data:filter_criteria,
            success:function(r) {
                loop_container.html(r).find('.tutor-pagination-wrap a').each(function(){
                    $(this).attr('data-href', $(this).attr('href')).attr('href', '#');
                });
            }
        })
    });

    // Alter pagination
    loop_container.on('click', '.tutor-pagination-wrap a', function(e){
        var url = $(this).data('href') || $(this).attr('href');

        if(url){
            url = new URL(url);
            var page = url.searchParams.get("paged");
            
            if(page){
                e.preventDefault();
                filter_modifier.page = page;
                filter_container.find('input:first').trigger('change');
            }
        }
    });

    // Alter sort filter
    loop_container.on('change', 'select[name="tutor_course_filter"]', function() {
        filter_modifier.tutor_course_filter = $(this).val();
        filter_container.find('input:first').trigger('change');
    });

    // Refresh page after coming back to course archive page from cart
    var archive_loop = $('.tutor-course-loop');
    if(archive_loop.length>0){
        window.sessionStorage.getItem('tutor_refresh_archive')==='yes' ? window.location.reload() : 0;
        window.sessionStorage.removeItem('tutor_refresh_archive');
        archive_loop.on('click', '.tutor-loop-cart-btn-wrap', function(){
            window.sessionStorage.setItem('tutor_refresh_archive', 'yes');
        });
    }
    
    /**
     * Profile Photo and Cover Photo editor
     * 
     * @since  v.1.7.5
    */
    var PhotoEditor=function(photo_editor){

        this.dialogue_box = photo_editor.find('#tutor_photo_dialogue_box');

        
        this.open_dialogue_box = function(name){
            this.dialogue_box.attr('name', name);
            this.dialogue_box.trigger('click');
        }

        this.validate_image = function(file){
            return true;
        }

        this.upload_selected_image = function(name, file){
            if(!file || !this.validate_image(file)){
                return;
            }

            var nonce = tutor_get_nonce_data(true);

            var context = this;
            context.toggle_loader(name, true);

            // Prepare payload to upload
            var form_data = new FormData();
            form_data.append('action', 'tutor_user_photo_upload');
            form_data.append('photo_type', name);
            form_data.append('photo_file', file, file.name);
            form_data.append(nonce.key, nonce.value);
            
            $.ajax({
                url:window._tutorobject.ajaxurl,
                data:form_data,
                type:'POST',
                processData: false,
                contentType: false,
                error:context.error_alert,
                complete:function(){
                    context.toggle_loader(name, false);
                }
            })
        }

        this.accept_upload_image=function(context, e){
            var file = e.currentTarget.files[0] || null;
            context.update_preview(e.currentTarget.name, file);
            context.upload_selected_image(e.currentTarget.name, file);
            $(e.currentTarget).val('');
        }

        this.delete_image=function(name){
            var context = this;
            context.toggle_loader(name, true);
            
            $.ajax({
                url:window._tutorobject.ajaxurl,
                data:{action:'tutor_user_photo_remove', photo_type:name},
                type:'POST',
                error:context.error_alert,
                complete:function(){
                    context.toggle_loader(name, false);
                }
            });
        }

        this.update_preview=function(name, file){
            var renderer = photo_editor.find(name=='cover_photo' ? '#tutor_cover_area' : '#tutor_profile_area');

            if(!file){
                renderer.css('background-image', 'url('+renderer.data('fallback')+')');
                this.delete_image(name);
                return;
            }
            
            var reader = new FileReader();
            reader.onload = function(e) {
                renderer.css('background-image', 'url('+e.target.result+')');
            }
            
            reader.readAsDataURL(file); 
        }

        this.toggle_profile_pic_action=function(show){
            var method = show===undefined ? 'toggleClass' : (show ? 'addClass' : 'removeClass');
            photo_editor[method]('pop-up-opened');
        }

        this.error_alert=function(){
            alert('Something Went Wrong.');
        }

        this.toggle_loader = function(name, show){
            photo_editor.find('#tutor_photo_meta_area .loader-area').css('display', (show ? 'block' : 'none'));
        }

        this.initialize = function(){
            var context = this;

            this.dialogue_box.change(function(e){context.accept_upload_image(context, e)});

            photo_editor.find('#tutor_profile_area .tutor_overlay, #tutor_pp_option>div:last-child').click(function(){context.toggle_profile_pic_action()});

            // Upload new
            photo_editor.find('.tutor_cover_uploader').click(function(){context.open_dialogue_box('cover_photo')});
            photo_editor.find('.tutor_pp_uploader').click(function(){context.open_dialogue_box('profile_photo')});

            // Delete existing
            photo_editor.find('.tutor_cover_deleter').click(function(){context.update_preview('cover_photo', null)});
            photo_editor.find('.tutor_pp_deleter').click(function(){context.update_preview('profile_photo', null)});
        }
    }

    var photo_editor = $('#tutor_profile_cover_photo_editor');
    photo_editor.length>0 ? new PhotoEditor(photo_editor).initialize() : 0;


    /**
     * 
     * Instructor list filter
     * 
     * @since  v.1.8.4
    */
    // Get values on course category selection
    $('.tutor-instructor-filter').each(function() {

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
            filter_args.action = 'load_filtered_instructor';
            
            // Show loading icon
            result_container.html('<div style="text-align:center"><img src="'+window._tutorobject.loading_icon_url+'"/></div>');

            $.ajax({
                url: window._tutorobject.ajaxurl,
                data: filter_args,
                type: 'POST',
                success: function(r) {
                    result_container.html(r);
                },
                error: function() {
                    result_container.html(html_cache);
                    tutor_toast('Failed', 'Request Error', 'error');
                }
            })
        }

        root.on('change', '.course-category-filter [type="checkbox"]', function() {

            var values = {};

            $(this).closest('.course-category-filter').find('input:checked').each(function() {
                values[$(this).val()] = $(this).parent().text();
            });

            // Show selected cat list
            var cat_parent = root.find('.selected-cate-list').empty();
            var cat_ids = Object.keys(values);

            cat_ids.forEach(function(value) {
                cat_parent.append('<span>'+values[value]+' <span class="tutor-icon-line-cross" data-cat_id="'+value+'"></span></span>');
            });

            cat_ids.length ? cat_parent.append('<span data-cat_id="0">Clear All</span>') : 0;

            run_instructor_filter($(this).attr('name'), cat_ids);
        })
        .on('click', '.selected-cate-list [data-cat_id]', function() {

            var id = $(this).data('cat_id');
            var inputs = root.find('.mobile-filter-popup [type="checkbox"]');
            id ? inputs = inputs.filter('[value="'+id+'"]') : 0;
            
            inputs.prop('checked', false).trigger('change');
        })
        .on('input', '.filter-pc [name="keyword"]', function() {
            // Get values on search keyword change
            
            var val = $(this).val();

            time_out ? window.clearTimeout(time_out) : 0;

            time_out = window.setTimeout(function() {

                run_instructor_filter('keyword', val);
                time_out = null;

            }, 500);
        })
        .on('click', '[data-page_number]', function(e) {

            // On pagination click
            e.preventDefault();
            
            run_instructor_filter(null, null, $(this).data( 'page_number' ) );

        }).on('click', '.clear-instructor-filter', function() {

            // Clear filter
            var root = $(this).closest('.tutor-instructor-filter');
            
            root.find('input[type="checkbox"]').prop('checked', false);

            root.find('[name="keyword"]').val('');
            
            run_instructor_filter();
        })
        .on('click', '.mobile-filter-container i', function () {
            // Open mobile screen filter
            $(this).parent().next().addClass('is-opened');
        })
        .on('click', '.mobile-filter-popup button', function() {
            
            $('.mobile-filter-popup [type="checkbox"]').trigger('change');
            
            // Close mobile screen filter
            $(this).closest('.mobile-filter-popup').removeClass('is-opened');

        }).on('input', '.filter-mobile [name="keyword"]', function() {

            // Sync keyword with two screen
            
            root.find('.filter-pc [name="keyword"]').val($(this).val()).trigger('input');

        }).on('change', '.mobile-filter-popup [type="checkbox"]', function(e) {

            if(e.originalEvent) {
                return;
            }

            // Sync category with two screen
            var name = $(this).attr('name');
            var val = $(this).val();
            var checked = $(this).prop('checked');

            root.find('.course-category-filter [name="'+name+'"]').filter('[value="'+val+'"]').prop('checked', checked).trigger('change');
        
        }).on('mousedown touchstart', '.expand-instructor-filter', function(e) {
            
            var window_height = $(window).height();
            var el = root.find('.mobile-filter-popup>div');
            var el_top = window_height-el.height();
            var plus = ((e.originalEvent.touches || [])[0] || e).clientY - el_top;

            root.on('mousemove touchmove', function(e){

                var y = ((e.originalEvent.touches || [])[0] || e).clientY;

                var height = (window_height-y)+plus;
                
                (height>200 && height<=window_height) ? el.css('height', height+'px') : 0;
            });
        
        }).on('mouseup touchend', function(){

            root.off('mousemove touchmove');
        })
        .on('click', '.mobile-filter-popup>div', function(e) {
            e.stopImmediatePropagation();
        }).on('click', '.mobile-filter-popup', function(e) {
            $(this).removeClass('is-opened');;
        });
    });

    /**
     * Retake course
     * 
     * @since v1.9.5
     */
    $('.tutor-course-retake-button').click(function(e) {
        e.preventDefault();

        var button = $(this);
        var url = button.attr('href');
        var course_id = button.data('course_id');

        var popup;

        var data = {
            title: __('Override Previous Progress', 'tutor'),
            description : __('Before continue, please decide whether to keep progress or reset.', 'tutor'),
            buttons : {
                reset: {
                    title: __('Reset Data', 'tutor'),
                    class: 'secondary',

                    callback: function() {

                        var button = popup.find('.tutor-button-secondary');
                        button.prop('disabled', true).append('<img style="margin-left: 7px" src="'+ window._tutorobject.loading_icon_url +'"/>');

                        $.ajax({
                            url: window._tutorobject.ajaxurl,
                            type: 'POST',
                            data: {action: 'tutor_reset_course_progress', course_id: course_id},
                            success: function(response) {
                                if(response.success) {
                                    window.location.assign(response.data.redirect_to);
                                } else {
                                    alert((response.data || {}).message || __('Something went wrong', 'tutor'));
                                }
                            },
                            complete: function() {
                                button.prop('disabled', false).find('img').remove();
                            }
                        });
                    }
                },
                keep: {
                    title: __('Keep Data', 'tutor'),
                    class: 'primary',
                    callback: function() {
                        window.location.assign(url);
                    }
                }
            } 
        };

        popup = new window.tutor_popup($, 'icon-gear', 40).popup(data);
    });


    //warn user before leave page if quiz is running
    document.body.addEventListener('click', function(event){
        const target      = event.target;
        const targetTag   = target.tagName 
        const parentTag   = target.parentElement.tagName;

        if ( $tutor_quiz_time_update.length > 0 && $tutor_quiz_time_update.html() != 'EXPIRED' ) {
            if ( targetTag === 'A' || parentTag === 'A' ) {
                event.preventDefault();
                event.stopImmediatePropagation();
                let popup;

                let data = {
                    title: __( 'Abandon Quiz?', 'tutor' ),
                    description : __( 'Do you want to abandon this quiz? The quiz will be submitted partially up to this question if you leave this page.', 'tutor' ),
                    buttons : {
                        keep: {
                            title: __( 'Yes, leave quiz', 'tutor' ),
                            id: 'leave',
                            class: 'secondary',
                            callback: function() {

                                var formData = $('form#tutor-answering-quiz').serialize()+'&action='+'tutor_quiz_abandon';
                                $.ajax({
                                    url: window._tutorobject.ajaxurl,
                                    type: 'POST',
                                    data: formData,
                                    beforeSend: function() {
                                       document.querySelector("#tutor-popup-leave").innerHTML = __( 'Leaving...', 'tutor' ); 
                                    },
                                    success: function(response) {
                                        if(response.success) {
                                            if ( target.href == undefined ) {
                                                location.href = target.parentElement.href
                                            } else {
                                                location.href = target.href
                                            }
                                        } else {
                                            alert( __( 'Something went wrong', 'tutor' ) );
                                        }
                                    },
                                    error: function() {
                                        alert( __( 'Something went wrong', 'tutor' ) );
                                        popup.remove();
                                    }
                                });
                            }
                        },
                        reset: {
                            title: __('Stay here', 'tutor'),
                            id: 'reset',
                            class: 'primary',
                            callback: function() {
                                popup.remove();
                            }
                        },
                    } 
                };
        
                popup = new window.tutor_popup($, '', 40).popup(data);
            }
        }
    });

    /* Disable start quiz button  */
    $('body').on('submit', 'form#tutor-start-quiz', function() {
        $(this).find('button').prop('disabled', true);
    });

    /** Disable typing on datePicker field */
    $('.hasDatepicker, .tutor_date_picker').keydown( function( e ) {
        if ( e.keyCode !== 8 && e.keyCode !== 46 ) {
            e.preventDefault();
        }
    });

});
>>>>>>> jk

import { get_response_message } from '../helper/response';
import codeSampleLang from '../lib/codesample-lang';

(function($) {
	window.enable_sorting_topic_lesson = function() {
		const { __ } = wp.i18n;

		if (jQuery().sortable) {
			$('.course-contents').sortable({
				handle: '.course-move-handle',
				start: function(e, ui) {
					ui.placeholder.css('visibility', 'visible');
				},
				stop: function(e, ui) {
					console.log('e1', e, ui);
					tutor_sorting_topics_and_lesson();
				},
			});
			$('.tutor-lessons:not(.drop-lessons)').sortable({
				connectWith: '.tutor-lessons',
				items: 'div.course-content-item',
				start: function(e, ui) {
					ui.placeholder.css('visibility', 'visible');
				},
				stop: function(e, ui) {
					// Store new updated order as input value
					tutor_sorting_topics_and_lesson(ui);
				},
			});
		}
	};

	window.tutor_sorting_topics_and_lesson = function(ui) {
		var topics = {};
		$('.tutor-topics-wrap').each(function(index, item) {
			var $topic = $(this);
			var topics_id = parseInt($topic.attr('id').match(/\d+/)[0], 10);
			var lessons = {};

			$topic
				.find('.course-content-item')
				.each(function(lessonIndex, lessonItem) {
					var $lesson = $(this);
					var lesson_id = parseInt($lesson.attr('id').match(/\d+/)[0], 10);

					lessons[lessonIndex] = lesson_id;
				});
			topics[index] = {
				topic_id: topics_id,
				lesson_ids: lessons,
			};
		});
		$('#tutor_topics_lessons_sorting').val(JSON.stringify(topics));

		let request_data = {
			tutor_topics_lessons_sorting: JSON.stringify(topics),
			action: 'tutor_update_course_content_order',
		};

		if (ui) {
			// Update parent topic id fro the dropped content
			let parent_topic_id = ui.item
				.closest('[data-topic-id]')
				.attr('data-topic-id');
			let content_id = ui.item.attr('data-course_content_id');

			request_data.content_parent = {
				parent_topic_id,
				content_id,
			};
		}

		$.ajax({
			url: window._tutorobject.ajaxurl,
			type: 'POST',
			data: request_data,
			success: function(r) {
				if (!r.success) {
					tutor_toast(__('Error', 'tutor'), get_response_message(r), 'error');
				}
			},
			error: function() {},
		});
	};
})(window.jQuery);

window.jQuery(document).ready(function($) {
	const { __ } = wp.i18n;

	enable_sorting_topic_lesson();

	/**
	 * Open Lesson Modal
	 */
	$(document).on('click', '.open-tutor-lesson-modal', function(e) {
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
				action: 'tutor_load_edit_lesson_modal',
			},
			beforeSend: function() {
				$that.addClass('is-loading').attr('disabled', true);
			},
			success: function(data) {
				if (!data.success) {
					tutor_toast(
						__('Error', 'tutor'),
						get_response_message(data),
						'error',
					);
					return;
				}

				$('.tutor-lesson-modal-wrap .tutor-modal-container').html(data.data.output);
				$('.tutor-lesson-modal-wrap').attr({
					'data-lesson-id': lesson_id,
					'data-topic-id': topic_id,
				});

				$('.tutor-lesson-modal-wrap').addClass('tutor-is-active');

				let editor_id = 'tutor_lesson_modal_editor',
					editor_wrap_selector = '#wp-tutor_lesson_modal_editor-wrap',
					tinymceConfig = tinyMCEPreInit.mceInit.tutor_lesson_editor_config;

				if (!tinymceConfig) {
					tinymceConfig = tinyMCEPreInit.mceInit.course_description;
				}

				if ($(editor_wrap_selector).hasClass('html-active')) {
					$(editor_wrap_selector).removeClass('html-active');
				}
				$(editor_wrap_selector).addClass('tmce-active');

				/**
				 * Code snippet support for PRO user.
				 *
				 * @since 2.0.9
				 */
				if (_tutorobject.tutor_pro_url && tinymceConfig && !tinymceConfig.plugins.includes('codesample')) {
					tinymceConfig.plugins = `${tinymceConfig.plugins}, codesample`;
					tinymceConfig.codesample_languages = codeSampleLang;
					tinymceConfig.toolbar1 = `${tinymceConfig.toolbar1}, codesample`;
				}

				tinymceConfig.wpautop = false;
				tinymce.init(tinymceConfig);
				tinymce.execCommand('mceRemoveEditor', false, editor_id);
				tinyMCE.execCommand('mceAddEditor', false, editor_id);
				quicktags({ id: editor_id });

				window.dispatchEvent(new Event(_tutorobject.content_change_event));
				window.dispatchEvent(new CustomEvent('tutor_modal_shown', { detail: e.target }));
			},
			complete: function() {
				$that.removeClass('is-loading').attr('disabled', false);
			},
		});
	});

	// Video source
	$(document).on('change', '.tutor_lesson_video_source', function(e) {
		let val = $(this).val();
		$(this)
			.nextAll()
			.hide()
			.filter('.video_source_wrap_' + val)
			.show();
		$(this)
			.prevAll()
			.filter('[data-video_source]')
			.attr('data-video_source', val);
	});

	/**
	 * Lesson Update From Lesson Modal
	 */
	$(document).on('click', '.update_lesson_modal_btn', function(event) {
		event.preventDefault();

		let $that = $(this),
			editor = tinyMCE.get('tutor_lesson_modal_editor'),
			editorWrap = document.getElementById('wp-tutor_lesson_modal_editor-wrap'),
			isHtmlActive = editorWrap.classList.contains('html-active'),
			content = editor.getContent({ format: 'html' });

		// removing <br data-mce-bogus="1">
		if (content === '<p><br data-mce-bogus="1"></p>') {
			content = '';
		}

		let form_data = $(this)
			.closest('.tutor-modal')
			.find('form')
			.serializeObject();
		form_data.lesson_content = content;
		form_data.is_html_active = isHtmlActive;

		$.ajax({
			url: window._tutorobject.ajaxurl,
			type: 'POST',
			data: form_data,
			beforeSend: function() {
				$that.addClass('is-loading').attr('disabled', true);
			},
			success: function(data) {
				if (data.success) {
					$('#tutor-course-content-wrap').html(data.data.course_contents);
					enable_sorting_topic_lesson();

					//Close the modal
					$that.closest('.tutor-modal').removeClass('tutor-is-active');

					tutor_toast(
						__('Success', 'tutor'),
						__('Lesson Updated', 'tutor'),
						'success',
					);

					window.dispatchEvent(new Event(_tutorobject.content_change_event));
				}
			},
			complete: function() {
				$that.removeClass('is-loading').attr('disabled', false);
			},
		});
	});

	/**
	 * @since v.1.9.0
	 * Parse and show video duration on link paste in lesson video
	 */
	var video_url_input = [
		'.video_source_wrap_external_url input',
		'.video_source_wrap_vimeo input',
		'.video_source_wrap_youtube input',
		'.video_source_wrap_html5 input.input_source_video_id',
	].join(',');

	var autofill_url_timeout;
	$(document)
		.on('blur', video_url_input, function() {
			var url = $(this).val();
			var regex = /(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
			if (url && regex.test(url) == false) {
				$(this).val('');
				tutor_toast('Error!', __('Invalid Video URL', 'tutor'), 'error');
			}
		})
		.on('paste', video_url_input, function(e) {
			e.stopImmediatePropagation();

			var root = $(this)
				.closest('.tutor-lesson-modal-wrap')
				.find('.tutor-option-field-video-duration');
			var duration_label = root.find('label');
			var is_wp_media = $(this).hasClass('input_source_video_id');
			var autofill_url = $(this).data('autofill_url');
			$(this).data('autofill_url', null);

			var video_url = is_wp_media
				? $(this).data('video_url')
				: autofill_url || e.originalEvent.clipboardData.getData('text');

			var toggle_loading = function(show) {
				if (!show) {
					duration_label.find('img').remove();
					return;
				}

				// Show loading icon
				if (duration_label.find('img').length == 0) {
					duration_label.append(
						' <img src="' +
							window._tutorobject.loading_icon_url +
							'" style="display:inline-block"/>',
					);
				}
			};

			var set_duration = function(sec_num) {
				var hours = Math.floor(sec_num / 3600);
				var minutes = Math.floor((sec_num - hours * 3600) / 60);
				var seconds = Math.round(sec_num - hours * 3600 - minutes * 60);

				if (hours < 10) {
					hours = '0' + hours;
				}
				if (minutes < 10) {
					minutes = '0' + minutes;
				}
				if (seconds < 10) {
					seconds = '0' + seconds;
				}

				var fragments = [hours, minutes, seconds];
				var time_fields = root.find('input');
				for (var i = 0; i < 3; i++) {
					time_fields.eq(i).val(fragments[i]);
				}
			};

			var yt_to_seconds = function(duration) {
				var match = duration.match(/PT(\d+H)?(\d+M)?(\d+S)?/);

				match = match.slice(1).map(function(x) {
					if (x != null) {
						return x.replace(/\D/, '');
					}
				});

				var hours = parseInt(match[0]) || 0;
				var minutes = parseInt(match[1]) || 0;
				var seconds = parseInt(match[2]) || 0;

				return hours * 3600 + minutes * 60 + seconds;
			};

			if (
				is_wp_media ||
				$(this)
					.parent()
					.hasClass('video_source_wrap_external_url')
			) {
				var player = document.createElement('video');
				player.addEventListener('loadedmetadata', function() {
					set_duration(player.duration);
					toggle_loading(false);
				});

				toggle_loading(true);
				player.src = video_url;
			} else if (
				$(this)
					.parent()
					.hasClass('video_source_wrap_vimeo')
			) {
				var regExp = /^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/;
				var match = video_url.match(regExp);
				var video_id = match ? match[5] : null;
				var is_ssl = _tutorobject.is_ssl ? 's' : '';
;				var vimeo_api_url = `http${is_ssl}://vimeo.com/api/v2/video/${video_id}/json`;

				if (video_id) {
					toggle_loading(true);

					$.getJSON(
						vimeo_api_url,
						function(data) {
							if (
								Array.isArray(data) &&
								data[0] &&
								data[0].duration !== undefined
							) {
								set_duration(data[0].duration);
							}

							toggle_loading(false);
						},
					);
				}
			} else if (
				$(this)
					.parent()
					.hasClass('video_source_wrap_youtube')
			) {
				var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/;
				var match = video_url.match(regExp);
				var video_id = match && match[7].length == 11 ? match[7] : false;
				var api_key = $(this).data('youtube_api_key');

				if (video_id && api_key) {
					var result_url =
						'https://www.googleapis.com/youtube/v3/videos?id=' +
						video_id +
						'&key=' +
						api_key +
						'&part=contentDetails';
					toggle_loading(true);

					$.getJSON(result_url, function(data) {
						if (
							typeof data == 'object' &&
							data.items &&
							data.items[0] &&
							data.items[0].contentDetails &&
							data.items[0].contentDetails.duration
						) {
							set_duration(
								yt_to_seconds(data.items[0].contentDetails.duration),
							);
						}

						toggle_loading(false);
					});
				}
			}
		})
		.on('input', video_url_input, function() {
			if (autofill_url_timeout) {
				clearTimeout(autofill_url_timeout);
			}

			var $this = $(this);
			autofill_url_timeout = setTimeout(function() {
				var val = $this.val();
				val = val ? val.trim() : '';
				console.log('Trigger', val);
				val ? $this.data('autofill_url', val).trigger('paste') : 0;
			}, 700);
		});
});

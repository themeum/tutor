import '../admin-dashboard/segments/lib';
import './course/index';
import './dashboard';
import './dashboard/export-csv';
import './pages/course-landing';
import './pages/instructor-list-filter';
import './_select_dd_search';
/**
 * Codes from this file should be decentralized according to relavent file/folder structure.
 * It's a legacy file.
 */

readyState_complete(() => {
	Object.entries(document.getElementsByTagName('a')).forEach((item) => {
		let urlString = item[1].getAttribute('href');
		if (urlString?.includes('/logout') || urlString?.includes('logout')) {
			item[1].setAttribute('data-no-instant', '');
		}
	});
});

jQuery(document).ready(function($) {
	'use strict';
	/**
	 * wp.i18n translatable functions
	 * @since 1.9.0
	 */
	const { __, _x, _n, _nx } = wp.i18n;
	/**
	 * Initiate Select2
	 * @since v.1.3.4
	 */
	if (jQuery().select2) {
		$('.tutor_select2').select2({
			escapeMarkup: function(markup) {
				return markup;
			},
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
	!(function(a) {
		function f(a, b) {
			if (!(a.originalEvent.touches.length > 1)) {
				a.preventDefault();
				var c = a.originalEvent.changedTouches[0],
					d = document.createEvent('MouseEvents');
				d.initMouseEvent(
					b,
					!0,
					!0,
					window,
					1,
					c.screenX,
					c.screenY,
					c.clientX,
					c.clientY,
					!1,
					!1,
					!1,
					!1,
					0,
					null,
				),
					a.target.dispatchEvent(d);
			}
		}
		if (((a.support.touch = 'ontouchend' in document), a.support.touch)) {
			var e,
				b = a.ui.mouse.prototype,
				c = b._mouseInit,
				d = b._mouseDestroy;
			(b._touchStart = function(a) {
				var b = this;
				!e &&
					b._mouseCapture(a.originalEvent.changedTouches[0]) &&
					((e = !0),
					(b._touchMoved = !1),
					f(a, 'mouseover'),
					f(a, 'mousemove'),
					f(a, 'mousedown'));
			}),
				(b._touchMove = function(a) {
					e && ((this._touchMoved = !0), f(a, 'mousemove'));
				}),
				(b._touchEnd = function(a) {
					e &&
						(f(a, 'mouseup'),
						f(a, 'mouseout'),
						this._touchMoved || f(a, 'click'),
						(e = !1));
				}),
				(b._mouseInit = function() {
					var b = this;
					b.element.bind({
						touchstart: a.proxy(b, '_touchStart'),
						touchmove: a.proxy(b, '_touchMove'),
						touchend: a.proxy(b, '_touchEnd'),
					}),
						c.call(b);
				}),
				(b._mouseDestroy = function() {
					var b = this;
					b.element.unbind({
						touchstart: a.proxy(b, '_touchStart'),
						touchmove: a.proxy(b, '_touchMove'),
						touchend: a.proxy(b, '_touchEnd'),
					}),
						d.call(b);
				});
		}
	})(jQuery);

	/**
	 * END jQuery UI Touch Punch
	 */

	const videoPlayer = {
		ajaxurl: window._tutorobject.ajaxurl,
		nonce_key: window._tutorobject.nonce_key,
		played_once: false,
		max_seek_time: 0,
		video_data: function() {
			const video_track_data = $('#tutor_video_tracking_information').val();
			return video_track_data ? JSON.parse(video_track_data) : {};
		},
		track_player: function() {
			const that = this;
			if (typeof Plyr !== 'undefined') {
				let syncTimeInterval;
				const video_data = that.video_data();
				const player = new Plyr(this.player_DOM, {
					keyboard: {
						focused: that.isRequiredPercentage() ? false : true,
						global: false,
					},
					listeners: {
						...(that.isRequiredPercentage() && {
							seek(e) {
								const newTime = that.getTargetTime(player, e);
								const currentTime = player.currentTime;
								const max_seek_time = currentTime > that.max_seek_time ? currentTime : that.max_seek_time;
								// Disallow moving forward
								if (newTime > max_seek_time) {
									e.preventDefault();
									tutor_toast(__('Warning', 'tutor'), __(`Forward seeking is disabled.`, 'tutor'), 'error');
									return false;
								}
								return true;
							},
						}),
					}
				});
				player.on('ready', function(event) {
					const instance = event.detail.plyr;
					const { best_watch_time = 0 } = video_data || {};
					if (_tutorobject.tutor_pro_url && best_watch_time > 0) {
						var previous_duration = Math.round(best_watch_time);
						var previousTimeSetter = setTimeout(function(){
							if (player.playing !== true && player.currentTime !== previous_duration) {
								if (instance.provider === 'youtube') {
									instance.embed.seekTo(best_watch_time);
								} else {
									instance.media.currentTime = previous_duration;
								}
							} else {
								clearTimeout(previousTimeSetter);
							}
						});
					}
					that.sync_time(instance);
				});
				
				player.on('play', (event) => {
					that.played_once = true;

					// Send to tutor backend about video playing time in this interval
					const intervalSeconds = 10;
					const instance = event.detail.plyr;
					syncTimeInterval = setInterval(() => {
						that.sync_time(instance);
					}, intervalSeconds * 1000);

					if (_tutorobject.tutor_pro_url && player.provider === 'youtube') {
						$('.plyr--youtube.plyr__poster-enabled .plyr__poster').css('opacity', 0);
					}
				});

				player.on('pause', (event) => {
					clearInterval(syncTimeInterval);
					const instance = event.detail.plyr;
					that.sync_time(instance);
				});

				player.on('ended', function(event) {
					clearInterval(syncTimeInterval);
					const video_data = that.video_data();
					const instance = event.detail.plyr;
					const data = { is_ended: true };
					that.sync_time(instance, data);
					if (video_data.autoload_next_course_content && that.played_once) {
						that.autoload_content();
					}

					if (_tutorobject.tutor_pro_url && player.provider === 'youtube') {
						$('.plyr--youtube.plyr__poster-enabled .plyr__poster').css('opacity', 1);
					}
				});
			}
		},
		sync_time: function(instance, options) {
			const video_data = this.video_data();
			if (!video_data) {
				return;
			}

			if (this.isRequiredPercentage()) {
				this.enable_complete_lesson_btn(instance);
			}

			//TUTOR is sending about video playback information to server.
			let data = {
				action: 'sync_video_playback',
				currentTime: instance.currentTime,
				duration: instance.duration,
				post_id: video_data.post_id,
			};
			data[this.nonce_key] = _tutorobject[this.nonce_key];
			let data_send = data;
			if (options) {
				data_send = Object.assign(data, options);
			}
			$.post(this.ajaxurl, data_send);
			
			const seekTime = video_data.best_watch_time > instance.currentTime ? video_data.best_watch_time : instance.currentTime;
			if (seekTime > this.max_seek_time) {
				this.max_seek_time = seekTime;
			}
		},
		autoload_content: function() {
			console.log('Autoloader called');
			const post_id = this.video_data().post_id;
			const data = { action: 'autoload_next_course_content', post_id };
			data[this.nonce_key] = _tutorobject[this.nonce_key];
			$.post(this.ajaxurl, data).done(function(response) {
				console.log(response);
				if (response.success && response.data.next_url) {
					location.href = response.data.next_url;
				}
			});
		},
		isRequiredPercentage: function() {
			const video_data = this.video_data();
			if (!video_data) {
				return false;
			}

			const { strict_mode, control_video_lesson_completion, lesson_completed, is_enrolled } = video_data;
			if (_tutorobject.tutor_pro_url && is_enrolled && !lesson_completed && strict_mode && control_video_lesson_completion) {
				return true;
			}
			return false;
		},
		enable_complete_lesson_btn: function(instance) {
			const complete_lesson_btn = $('button[name="complete_lesson_btn"]');
			const video_data = this.video_data();
			const completedPercentage = this.getPercentage(Number(instance.currentTime), Number(instance.duration));
			
			if (completedPercentage >= video_data.required_percentage) {
				complete_lesson_btn.attr('disabled', false);
				complete_lesson_btn.next().remove();
			}
		},
		disable_complete_lesson_btn: function() {
			const video_data = this.video_data();
			if (!video_data) {
				return;
			}

			const { best_watch_time, video_duration, required_percentage } = video_data;
			const completedPercentage = this.getPercentage(Number(best_watch_time), Number(video_duration));
			
			if (completedPercentage < required_percentage) {
				const complete_lesson_btn = $('button[name="complete_lesson_btn"]');
				complete_lesson_btn.attr('disabled', true);
				complete_lesson_btn.wrap('<div class="tooltip-wrap"></div>').after(`<span class="tooltip-txt tooltip-bottom">${__(`Watch at least ${video_data.required_percentage}% to complete the lesson.`, 'tutor')}</span>`);
			}
		},
		getPercentage: function(value, total) {
			if (value > 0 && total > 0) {
				return Math.round((value / total) * 100);
			}
			return 0;
		},
		getTargetTime: function(player, input) {
			if (
			  typeof input === "object" &&
			  (input.type === "input" || input.type === "change")
			) {
			  return input.target.value / input.target.max * player.media.duration;
			} else {
			  return Number(input);
			}
		},
		init: function(element) {
			this.player_DOM = element;
			this.track_player();
			if (this.isRequiredPercentage()) {
				this.disable_complete_lesson_btn();
			}
		},
	};

	/**
	 * Fire TUTOR video
	 * @since v.1.0.0
	 */
	$('.tutorPlayer').each(function() {
		videoPlayer.init(this);
	});

	$(document).on('change keyup paste', '.tutor_user_name', function() {
		$(this).val(tutor_slugify($(this).val()));
	});

	function tutor_slugify(text) {
		return text
			.toString()
			.toLowerCase()
			.replace(/\s+/g, '-') // Replace spaces with -
			.replace(/[^\w\-]+/g, '') // Remove all non-word chars
			.replace(/\-\-+/g, '-') // Replace multiple - with single -
			.replace(/^-+/, '') // Trim - from start of text
			.replace(/-+$/, ''); // Trim - from end of text
	}

	$(document).on('click', '.tutor_question_cancel', function(e) {
		e.preventDefault();
		$('.tutor-add-question-wrap').toggle();
	});

	// Quiz Review : Tooltip
	$('.tooltip-btn').on('hover', function(e) {
		$(this).toggleClass('active');
	});

	// tutor course content accordion

	/**
	 * Toggle topic summery
	 * @since v.1.6.9
	 */
	$('.tutor-course-title h4 .toggle-information-icon').on('click', function(
		e,
	) {
		$(this)
			.closest('.tutor-topics-in-single-lesson')
			.find('.tutor-topics-summery')
			.slideToggle();
		e.stopPropagation();
	});

	$('.tutor-course-topic.tutor-active')
		.find('.tutor-course-lessons')
		.slideDown();
	$('.tutor-course-title').on('click', function() {
		var lesson = $(this).siblings('.tutor-course-lessons');
		$(this)
			.closest('.tutor-course-topic')
			.toggleClass('tutor-active');
		lesson.slideToggle();
	});

	$(document).on(
		'click',
		'.tutor-topics-title h3 .toggle-information-icon',
		function(e) {
			$(this)
				.closest('.tutor-topics-in-single-lesson')
				.find('.tutor-topics-summery')
				.slideToggle();
			e.stopPropagation();
		},
	);

	// toggle topics sidebar
	$(document).on('click', '[tutor-course-topics-sidebar-toggler]', function(
		e,
	) {
		e.preventDefault();
		$('.tutor-course-single-content-wrapper').toggleClass(
			'tutor-course-single-sidebar-hidden',
		);
	});

	$('[tutor-course-topics-sidebar-offcanvas-toggler]').on('click', function(
		event,
	) {
		event.preventDefault();
		$('.tutor-course-single-content-wrapper').toggleClass(
			'tutor-course-single-sidebar-open',
		);
		$('body').toggleClass('tutor-overflow-hidden');
	});

	$('[tutor-hide-course-single-sidebar]').on('click', function(event) {
		event.preventDefault();
		console.log('Hello');
		$('.tutor-course-single-content-wrapper').removeClass(
			'tutor-course-single-sidebar-open',
		);
		$('body').removeClass('tutor-overflow-hidden');
	});

	//@todo: to be removed
	$('.tutor-tabs-btn-group a').on('click touchstart', function(e) {
		e.preventDefault();
		var $that = $(this);
		var tabSelector = $that.attr('href');
		$('.tutor-lesson-sidebar-tab-item').hide();
		$(tabSelector).show();

		$('.tutor-tabs-btn-group a').removeClass('active');
		$that.addClass('active');
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
		$('.quiz-draggable-rand-answers').each(function() {
			var $that = $(this);
			var draggableDivHeight = $that.height();

			$that.css({ height: draggableDivHeight });
		});
	}

	/**
	 * Datepicker initiate
	 *
	 * @since v.1.1.2
	 */
	if (jQuery.datepicker) {
		$('.tutor_report_datepicker').datepicker({ dateFormat: 'yy-mm-dd' });
	}

	/**
	 * Setting account for withdraw earning
	 *
	 * @since v.1.2.0
	 */
	$(document).on('submit', '#tutor-withdraw-account-set-form', function(e) {
		if (!e.detail || e.detail == 1) {
			e.preventDefault();
			var $form = $(this);
			var $btn = $form.find('.tutor_set_withdraw_account_btn');
			var data = $form.serializeObject();
			$btn.prop('disabled', true);

			$.ajax({
				url: _tutorobject.ajaxurl,
				type: 'POST',
				data: data,
				beforeSend: function() {
					$btn.addClass('is-loading');
				},
				success: function(data) {
					if (data.success) {
						tutor_toast('Success!', data.data.msg, 'success');
					}
				},
				complete: function() {
					$btn.removeClass('is-loading');
					setTimeout(() => {
						$btn.prop('disabled', false);
					}, 2000);
				},
			});
		}
	});

	/**
	 * Make Withdraw Form
	 *
	 * @since v.1.2.0
	 */

	$(document).on('submit', '#tutor-earning-withdraw-form', function(e) {
		e.preventDefault();

		var $form = $(this);
		var $btn = $('#tutor-earning-withdraw-btn');
		var $responseDiv = $('.tutor-withdraw-form-response');
		var data = $form.serializeObject();

		$.ajax({
			url: _tutorobject.ajaxurl,
			type: 'POST',
			data: data,
			beforeSend: function() {
				$form.find('.tutor-success-msg').remove();
				$btn.attr('disabled', 'disabled').addClass('is-loading');
			},
			success: function(data) {
				var Msg;
				$('.tutor-earning-withdraw-form-wrap').hide();
				if (data.success) {
					console.log(data.data.available_balance);

					if (data.data.available_balance !== 'undefined') {
						$('.withdraw-balance-col .available_balance').html(
							data.data.available_balance,
						);
					}

					tutor_toast(
						__('Request Successful', 'tutor'),
						__(
							"Your request has been submitted. Please wait for the administrator's response.",
							'tutor',
						),
						'success',
					);
					setTimeout(function() {
						location.reload();
					}, 500);
				} else {
					tutor_toast('Error', data.data.msg, 'error');
					Msg =
						'<div class="tutor-error-msg inline-image-text is-inline-block">\
                            <img src="' +
						window._tutorobject.tutor_url +
						'assets/images/icon-cross.svg"/> \
                            <div>\
                                <b>Error</b><br/>\
                                <span>' +
						data.data.msg +
						'</span>\
                            </div>\
                        </div>';

					setTimeout(function() {
						$responseDiv.html('');
					}, 5000);
					return false;
				}
			},
			complete: function() {
				$btn.removeAttr('disabled').removeClass('is-loading');
			},
		});
	});

	/**
	 * Delete Course
	 */
	$(document).on('click', '.tutor-dashboard-element-delete-btn', function(e) {
		e.preventDefault();
		var element_id = $(this).attr('data-id');
		$('#tutor-dashboard-delete-element-id').val(element_id);
	});
	$(document).on('submit', '#tutor-dashboard-delete-element-form', function(
		e,
	) {
		e.preventDefault();

		var element_id = $('#tutor-dashboard-delete-element-id').val();
		var $btn = $('.tutor-modal-element-delete-btn');
		var data = $(this).serializeObject();

		$.ajax({
			url: _tutorobject.ajaxurl,
			type: 'POST',
			data: data,
			beforeSend: function() {
				$btn.addClass('is-loading');
			},
			success: function(res) {
				if (res.success) {
					$(
						'#tutor-dashboard-' + res.data.element + '-' + element_id,
					).remove();
				}
			},
			complete: function() {
				$btn.removeClass('is-loading');
			},
		});
	});

	/**
	 * Assignment
	 *
	 * @since v.1.3.3
	 */
	$(document).on('submit', '#tutor_assignment_start_form', function(e) {
		e.preventDefault();

		var $that = $(this);
		var form_data = $that.serializeObject();
		form_data.action = 'tutor_start_assignment';

		$.ajax({
			url: _tutorobject.ajaxurl,
			type: 'POST',
			data: form_data,
			beforeSend: function() {
				$('#tutor_assignment_start_btn').addClass('is-loading');
			},
			success: function(data) {
				if (data.success) {
					/**
					 * The `true` value will force reload current page
					 * from server instead of browser cache.
					 */
					location.reload(true);
				}
			},
			error: function(xhr, status, error) {
				 console.log('assignment start error: ' + error ); 
			},
			complete: function() {
				$('#tutor_assignment_start_btn').removeClass('is-loading');
			},
		});
	});

	/**
	 * Assignment answer validation
	 */
	$(document).on('submit', '#tutor_assignment_submit_form', function(e) {
		var assignment_answer = tinymce.activeEditor.getContent();
		if (assignment_answer.trim().length < 1) {
			e.preventDefault();
			tutor_toast(
				__('Warning', 'tutor'),
				__('Assignment answer is required.', 'tutor'),
				'error',
			);

			setTimeout(() => {
				jQuery('button#tutor_assignment_submit_btn').removeClass('is-loading').removeAttr('disabled')
			}, 500)
		}
	});

	/**
	 * Single Assignment Upload Button
	 * @since v.1.3.4
	 */
	$('form').on('change', '.tutor-assignment-file-upload', function() {
		$(this)
			.siblings('label')
			.find('span')
			.html(
				$(this)
					.val()
					.replace(/.*(\/|\\)/, ''),
			);
	});

	/**
	 * Open the first lesson if all the lessons are closed
	 */
	if ($('.tutor-accordion-item-header.is-active').length === 0) {
		$('.tutor-accordion-item-header')
			.first()
			.trigger('click');
	}

	/**
	 *
	 * @type {jQuery}
	 *
	 * Course builder section toggle
	 *
	 * @since v.1.3.5
	 */

	$('.tutor-course-builder-section-title').on('click', function() {
		if (
			$(this)
				.find('i')
				.hasClass('tutor-icon-angle-up')
		) {
			$(this)
				.find('i')
				.removeClass('tutor-icon-angle-up')
				.addClass('tutor-icon-angle-down');
		} else {
			$(this)
				.find('i')
				.removeClass('tutor-icon-angle-down')
				.addClass('tutor-icon-angle-up');
		}
		$(this)
			.next('div')
			.slideToggle();
	});

	/**
	 * Profile photo upload
	 * @since v.1.4.5
	 */

	$(document).on('click', '#tutor_profile_photo_button', function(e) {
		e.preventDefault();

		$('#tutor_profile_photo_file').trigger('click');
	});

	$(document).on('change', '#tutor_profile_photo_file', function(event) {
		event.preventDefault();

		var $file = this;
		if ($file.files && $file.files[0]) {
			var reader = new FileReader();
			reader.onload = function(e) {
				$('.tutor-profile-photo-upload-wrap')
					.find('img')
					.attr('src', e.target.result);
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

	$(document).on('click', '.thread-content .subject', function(e) {
		var $btn = $(this);

		var thread_id = parseInt(
			$btn.closest('.thread-content').attr('data-thread-id'),
		);

		var nonce_key = _tutorobject.nonce_key;
		var json_data = {
			thread_id: thread_id,
			action: 'tutor_bp_retrieve_user_records_for_thread',
		};
		json_data[nonce_key] = _tutorobject[nonce_key];

		$.ajax({
			type: 'POST',
			url: window._tutorobject.ajaxurl,
			data: json_data,
			beforeSend: function() {
				$('#tutor-bp-thread-wrap').html('');
			},
			success: function(data) {
				if (data.success) {
					$('#tutor-bp-thread-wrap').html(data.data.thread_head_html);
					tutor_bp_setting_enrolled_courses_list();
				}
			},
		});
	});

	function tutor_bp_setting_enrolled_courses_list() {
		$('ul.tutor-bp-enrolled-course-list').each(function() {
			var $that = $(this);
			var $li = $that.find(' > li');
			var itemShow = 3;

			if ($li.length > itemShow) {
				var plusCourseCount = $li.length - itemShow;
				$li.each(function(liIndex, liItem) {
					var $liItem = $(this);

					if (liIndex >= itemShow) {
						$liItem.hide();
					}
				});

				var infoHtml =
					'<a href="javascript:;" class="tutor_bp_plus_courses"><strong>+' +
					plusCourseCount +
					' More </strong></a> Courses';
				$that
					.closest('.tutor-bp-enrolled-courses-wrap')
					.find('.thread-participant-enrolled-info')
					.html(infoHtml);
			}

			$that.show();
		});
	}
	tutor_bp_setting_enrolled_courses_list();

	$(document).on('click', 'a.tutor_bp_plus_courses', function(e) {
		e.preventDefault();

		var $btn = $(this);
		$btn
			.closest('.tutor-bp-enrolled-courses-wrap')
			.find('.tutor-bp-enrolled-course-list li')
			.show();
		$btn.closest('.thread-participant-enrolled-info').html('');
	});

	/**
	 * Addon, Tutor Certificate
	 * Certificate dropdown content and copy link
	 * @for TutorLMS Pro
	 * @since v.1.5.1
	 */
	//$(document).on('click', '.tutor-dropbtn', function (e) {
	$('.tutor-dropbtn').click(function() {
		var $content = $(this)
			.parent()
			.find('.tutor-dropdown-content');
		$content.slideToggle(100);
	});

	$(document).on('click', function(e) {
		var container = $('.tutor-dropdown');
		var $content = container.find('.tutor-dropdown-content');
		// if the target of the click isn't the container nor a descendant of the container
		if (!container.is(e.target) && container.has(e.target).length === 0) {
			$content.slideUp(100);
		}
	});

	/**
	 * Show hide is course public checkbox (frontend dashboard editor)
	 *
	 * @since  v.1.7.2
	 */
	var price_type = $(
		'.tutor-frontend-builder-course-price [name="tutor_course_price_type"]',
	);
	if (price_type.length == 0) {
		$('#_tutor_is_course_public_meta_checkbox').show();
	} else {
		price_type
			.change(function() {
				if ($(this).prop('checked')) {
					var method = $(this).val() == 'paid' ? 'hide' : 'show';
					$('#_tutor_is_course_public_meta_checkbox')[method]();
				}
			})
			.trigger('change');
	}

	/**
	 * Withdrawal page tooltip
	 *
	 * @since  v.1.7.4
	 */
	// Fully accessible tooltip jQuery plugin with delegation.
	// Ideal for view containers that may re-render content.
	(function($) {
		$.fn.tutor_tooltip = function() {
			this

				// Delegate to tooltip, Hide if tooltip receives mouse or is clicked (tooltip may stick if parent has focus)
				.on('mouseenter click', '.tooltip', function(e) {
					e.stopPropagation();
					$(this).removeClass('isVisible');
				})
				// Delegate to parent of tooltip, Show tooltip if parent receives mouse or focus
				.on('mouseenter focus', ':has(>.tooltip)', function(e) {
					if (!$(this).prop('disabled')) {
						// IE 8 fix to prevent tooltip on `disabled` elements
						$(this)
							.find('.tooltip')
							.addClass('isVisible');
					}
				})
				// Delegate to parent of tooltip, Hide tooltip if parent loses mouse or focus
				.on('mouseleave blur keydown', ':has(>.tooltip)', function(e) {
					if (e.type === 'keydown') {
						if (e.which === 27) {
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
	})(jQuery);

	// Bind event listener to container element
	jQuery('.tutor-tooltip-inside').tutor_tooltip();

	jQuery('.tutor-static-loader').click(function() {
		setTimeout(() => {
			jQuery(this)
				.addClass('is-loading')
				.attr('disabled', 'disabled');
		}, 100);
	});

	// Show the snackbar
	const snackbar = document.getElementById('tutor-reuseable-snackbar');
	if (snackbar) {
		// Apply the animation class after a short delay
		setTimeout(function() {
			snackbar.classList.add('tutor-snackbar-show');
		}, 1000); // Adjust the delay (in milliseconds) as needed
	}
	
	jQuery('#tutor-registration-form [name="password_confirmation"]').on('input', function(){
        let original = jQuery('[name="password"]');
        let val = (original.val() || '').trim();
        let matched = val && jQuery(this).val() === val;
        
        jQuery(this).parent().find('.tutor-validation-icon')[matched ? 'show' : 'hide']();
    });
});

import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';
import { LessonPlaybackController, readPlayerData } from '@LearningArea/lesson/player';

import '../admin-dashboard/segments/filter';
import '../admin-dashboard/segments/lib';
import { isMobileDevice } from '../helper/utils';
import './_select_dd_search';
import './course/index';
import './pages/billing';
import './pages/cart';
import './pages/checkout';
import './pages/course-landing';
import './pages/instructor-list-filter';
import './tutor-cart-button';

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
	const { __, _x, _n, _nx, sprintf } = wp.i18n;

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

	const createLegacyPlayerEnvironment = (videoData) => {
		const tutorObject = window._tutorobject || {};
		const nonceKey = tutorObject.nonce_key;
		const ajaxurl = tutorObject.ajaxurl;

		const post = (action, payload) =>
			$.post(ajaxurl, Object.assign({ action, [nonceKey]: tutorObject[nonceKey] }, payload));

		return {
			proActive: Boolean(tutorObject.tutor_pro_url),
			autoPlayNext: Boolean(videoData.autoload_next_course_content),
			isMobile: isMobileDevice(),
			syncIntervalSeconds: 10,
			syncPlayback: (payload) => {
				if (!videoData.is_enrolled) {
					return;
				}
				post('sync_video_playback', payload);
			},
			fetchNextUrl: (postId) =>
				post('autoload_next_course_content', { post_id: postId }).then((response) =>
					response && response.success && response.data && response.data.next_url ? response.data.next_url : null,
				),
			toastWarning: (message) => {
				tutor_toast(__('Warning', 'tutor'), message, 'error');
			},
			openUrl: (url) => {
				window.location.href = url;
			},
			notifyReady: (player) => {
				if (player.element.closest('.tutor-course-topic-single-body .tutor-video-player-wrapper')) {
					window.dispatchEvent(
						new CustomEvent(TUTOR_CUSTOM_EVENTS.LESSON_PLAYER_READY, { detail: { player } }),
					);
					window.TutorLessonPlayer = player;
				}
			},
			document,
		};
	};

	const videoPlayer = {
		init: function(element) {
			const start = () => {
				if (typeof window.TutorCore === 'undefined' || typeof window.TutorCore.player === 'undefined') {
					return;
				}

				const videoData = readPlayerData(document);
				const player = window.TutorCore.player.create(element);

				new LessonPlaybackController(player, videoData, createLegacyPlayerEnvironment(videoData)).start();
			};

			if (window.TutorCore && window.TutorCore.player) {
				start();
			} else {
				window.addEventListener(TUTOR_CUSTOM_EVENTS.PLAYER_ENGINE_READY, start, { once: true });
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

import '../../../v2-library/_src/js/main';

window.tutor_get_nonce_data = function(send_key_value) {
	var nonce_data = window._tutorobject || {};
	var nonce_key = nonce_data.nonce_key || '';
	var nonce_value = nonce_data[nonce_key] || '';

	if (send_key_value) {
		return { key: nonce_key, value: nonce_value };
	}

	return { [nonce_key]: nonce_value };
};

window.tutor_popup = function($, icon) {
	const { __ } = wp.i18n;
	var $this = this;
	var element;

	this.popup_wrapper = function(wrapper_tag) {
		var html = '<'+ wrapper_tag +' id="tutor-legacy-modal" class="tutor-modal tutor-is-active">';
			html += '<div class="tutor-modal-overlay"></div>';
			html += '<div class="tutor-modal-window">';
				html += '<div class="tutor-modal-content tutor-modal-content-white">';
					html += '<button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close><span class="tutor-icon-times" area-hidden="true"></span></button>';
					html += '<div class="tutor-modal-body tutor-text-center">';
						html += '<div class="tutor-px-lg-48 tutor-py-lg-24">';
						
							if (icon) {
								html += '<div class="tutor-mt-24"><img class="tutor-d-inline-block" src="' + window._tutorobject.tutor_url + 'assets/images/' + icon + '.svg" /></div>';
							}

							html += '<div class="tutor-modal-content-container"></div>';

							// Buttons
							html += '<div class="tutor-d-flex tutor-justify-center tutor-mt-48 tutor-mb-24 tutor-modal-actions"></div>';
						
						html += '</div>';
					html += '</div>';
				html += '</div>';
			html += '</div>';
		html += '</'+ wrapper_tag +'>';

		return html;
	};

	this.popup = function(data) {
		var title = data.title ? '<div class="tutor-fs-3 tutor-fw-medium tutor-color-black tutor-mb-12">'+ data.title +'</div>' : '';
		var description = data.description ? '<div class="tutor-fs-6 tutor-color-muted">'+ data.description +'</div>' : '';

		var buttons = Object.keys(data.buttons || {}).map(function(key) {
			var button = data.buttons[key];
			var button_id = button.id ? 'tutor-popup-' + button.id : '';
			var button_attr = button.attr ? ' ' + button.attr : '';
			return $('<button id="' + button_id + '" class="' + button.class + '"' + button_attr + '>' + button.title + '</button>').click(
				function() {
					button.callback($(this));
				}
			);
		});

		element = $($this.popup_wrapper(data.wrapper_tag || 'div'));
		var content_wrapper = element.find('.tutor-modal-content-container');

		content_wrapper.append(title);
		content_wrapper.append(description);

		$('body').append(element);
		$('body').addClass('tutor-modal-open');

		for (var i = 0; i < buttons.length; i++) {
			element.find('.tutor-modal-actions').append(buttons[i]);
		}

		return element;
	};

	return { popup: this.popup };
};

window.tutor_date_picker = () => {
	if (jQuery.datepicker) {
		var format = _tutorobject.wp_date_format;
		if (!format) {
			format = 'yy-mm-dd';
		}
		$('.tutor_date_picker').datepicker({ dateFormat: format });
	}
};

jQuery(document).ready(function($) {
	'use strict';
	const { __, _x, _n, _nx } = wp.i18n;

	/**
	 * Video source tabs
	 */

	if (jQuery().select2) {
		$('.videosource_select2').select2({
			width: '100%',
			templateSelection: iformat,
			templateResult: iformat,
			allowHtml: true,
		});
	}
	//videosource_select2

	function iformat(icon) {
		var originalOption = icon.element;
		return $('<span><i class="tutor-icon-' + $(originalOption).data('icon') + '"></i> ' + icon.text + '</span>');
	}

	/**
	 * Course Builder
	 *
	 * @since v.1.3.4
	 */

	$(document).on('click', '.tutor-course-thumbnail-upload-btn', function(event) {
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
				text: __('Use this media', 'tutor'),
			},
			multiple: false,
		});
		frame.on('select', function() {
			var attachment = frame
				.state()
				.get('selection')
				.first()
				.toJSON();
			$that
				.closest('.tutor-thumbnail-wrap')
				.find('.thumbnail-img')
				.attr('src', attachment.url);
			$that
				.closest('.tutor-thumbnail-wrap')
				.find('input')
				.val(attachment.id);
			$('.tutor-course-thumbnail-delete-btn').show();
		});
		frame.open();
	});

	//Delete Thumbnail
	$(document).on('click', '.tutor-course-thumbnail-delete-btn', function(event) {
		event.preventDefault();
		var $that = $(this);

		var placeholder_src = $that
			.closest('.tutor-thumbnail-wrap')
			.find('.thumbnail-img')
			.attr('data-placeholder-src');
		$that
			.closest('.tutor-thumbnail-wrap')
			.find('.thumbnail-img')
			.attr('src', placeholder_src);
		$that
			.closest('.tutor-thumbnail-wrap')
			.find('input')
			.val('');
		$('.tutor-course-thumbnail-delete-btn').hide();
	});

	$(document).on('change keyup', '.course-edit-topic-title-input', function(e) {
		e.preventDefault();
		$(this)
			.closest('.tutor-topics-top')
			.find('.topic-inner-title')
			.html($(this).val());
	});

	/**
	 * Delete Lesson from course builder
	 */
	$(document).on('click', '.tutor-delete-lesson-btn', function(e) {
		e.preventDefault();

		if (!confirm(__('Are you sure to delete?', 'tutor'))) {
			return;
		}

		var $that = $(this);
		var lesson_id = $that.attr('data-lesson-id');

		$.ajax({
			url: window._tutorobject.ajaxurl,
			type: 'POST',
			data: { lesson_id: lesson_id, action: 'tutor_delete_lesson_by_id' },
			beforeSend: function() {
				$that.addClass('is-loading');
			},
			success: function(data) {
				if (data.success) {
					$that.closest('.course-content-item').remove();
				}
			},
			complete: function() {
				$that.removeClass('is-loading');
			},
		});
	});

	/**
	 * Delete Quiz
	 * @since v.1.0.0
	 */

	$(document).on('click', '.tutor-delete-quiz-btn', function(e) {
		e.preventDefault();

		if (!confirm(__('Are you sure to delete?', 'tutor'))) {
			return;
		}

		var $that = $(this);
		var quiz_id = $that.attr('data-quiz-id');

		$.ajax({
			url: window._tutorobject.ajaxurl,
			type: 'POST',
			data: {
				quiz_id: quiz_id,
				action: 'tutor_delete_quiz_by_id',
			},
			beforeSend: function() {
				$that.addClass('is-loading');
			},
			success: function(resp) {
				const { data = {}, success } = resp || {};
				const { message = __('Something Went Wrong!') } = data;

				if (success) {
					$that.closest('.course-content-item').remove();
					return;
				}

				tutor_toast('Error!', message, 'error');
			},
			complete: function() {
				$that.removeClass('is-loading');
			},
		});
	});

	// @todo: find out the usage
	$(document).on('click', '.settings-tabs-navs li', function(e) {
		e.preventDefault();

		var $that = $(this);
		var data_target = $that.find('a').attr('data-target');
		var url = $that.find('a').attr('href');

		$that
			.addClass('active')
			.siblings('li.active')
			.removeClass('active');
		$('.settings-tab-wrap')
			.removeClass('active')
			.hide();
		$(data_target)
			.addClass('active')
			.show();

		window.history.pushState({}, '', url);
	});

	/**
	 * Tutor number validation
	 *
	 * @since v.1.6.3
	 */
	$(document).on('keyup change', '.tutor-number-validation', function(e) {
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
	$(document).on('click', '.tutor-instructor-feedback', function(e) {
		e.preventDefault();
		var $that = $(this);
		let btnContent = $that.html();
		console.log(tinymce.activeEditor.getContent());
		$.ajax({
			url: window.ajaxurl || _tutorobject.ajaxurl,
			type: 'POST',
			data: {
				attempt_id: $that.data('attempt-id'),
				feedback: tinymce.activeEditor.getContent(),
				action: 'tutor_instructor_feedback',
			},
			beforeSend: function() {
				$that.text(__('Updating...', 'tutor')).attr('disabled', 'disabled').addClass('is-loading');
			},
			success: function(data) {
				if (data.success) {
					$that.closest('.course-content-item').remove();
					tutor_toast(__('Success', 'tutor'), $that.data('toast_success_message'), 'success');
				}
			},
			complete: function() {
				$that.html(btnContent).removeAttr('disabled').removeClass('is-loading');
			},
		});
	});

	/**
	 * @since v.1.8.6
	 * SUbmit form through ajax
	 */
	$('.tutor-form-submit-through-ajax').submit(function(e) {
		e.preventDefault();

		var $that = $(this);
		var url = $(this).attr('action') || window.location.href;
		var type = $(this).attr('method') || 'GET';
		var data = $(this).serializeObject();

		$.ajax({
			url: url,
			type: type,
			data: data,
			beforeSend: function() {
				$that.find('button').attr('disabled', 'disabled').addClass('is-loading');
			},
			success: function() {
				tutor_toast(__('Success', 'tutor'), $that.data('toast_success_message'), 'success');
			},
			complete: function() {
				$that.find('button').removeAttr('disabled').removeClass('is-loading');
			},
		});
	});

	/*
	 * @since v.1.7.9
	 * Send wp nonce to every ajax request
	 */
	$.ajaxSetup({ data: tutor_get_nonce_data() });
});

jQuery.fn.serializeObject = function() {
	var values = {};
	var array = this.serializeArray();

	jQuery.each(array, function() {
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

window.tutor_toast = function(title, description, type) {
	var tutor_ob = window._tutorobject || {};
	var asset = (tutor_ob.tutor_url || '') + 'assets/images/';

	if (!jQuery('.tutor-toast-parent').length) {
		jQuery('body').append('<div class="tutor-toast-parent tutor-toast-right"></div>');
	}

	// var icons = {
	//     success: asset + 'icon-check.svg',
	//     error: asset + 'icon-cross.svg'
	// }
	var alert = type == 'success' ? 'success' : type == 'error' ? 'danger' : 'primary';
	var icon =
		type == 'success'
			? 'tutor-icon-mark'
			: type == 'error'
			? 'tutor-icon-times'
			: 'tutor-icon-circle-info-o';
	var contentS = jQuery(`
        <div class="tutor-large-notification tutor-large-notification-${alert}">
            <div class="tutor-large-notification-icon">
                <span class="tutor-icon-48 ${icon} tutor-mr-12"></span>
            </div>
            <div class="tutor-large-notification-content tutor-ml-5">
                <div class="tutor-large-notification-title tutor-fs-6 tutor-fw-bold tutor-mt-12">
                    ${title}
                </div>
                <div class="tutor-fs-7 tutor-mt-8">
                    ${description}
                </div>
            </div>
            <span class="tutor-toast-close tutor-noti-close tutor-icon-20 color-black-40 tutor-icon-times"></span>
        </div>
    `);

	var content = jQuery(`
		<div class="tutor-notification tutor-is-${alert} tutor-mb-16">
			<div class="tutor-notification-icon">
				<i class="${icon}"></i>
			</div>
			<div class="tutor-notification-content">
			<h5>${title}</h5>
			<p>${description}</p>
			</div>
			<button class="tutor-notification-close">
				<i class="fas fa-times"></i>
			</button>
		</div>
    `);

	content.find('.tutor-noti-close').click(function() {
		content.remove();
	});

	jQuery('.tutor-toast-parent').append(content);

	setTimeout(function() {
		if (content) {
			content.fadeOut('fast', function() {
				jQuery(this).remove();
			});
		}
	}, 5000);
};


// enable custom selector when modal opens
window.addEventListener('tutor_modal_shown', (e) => {
	selectSearchField('.tutor-form-select');
})

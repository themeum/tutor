import '../front/_select_dd_search';
import './addons-list/addons-list-main';
import './segments/addonlist';
import './segments/color-preset';
import './segments/editor_full';
import './segments/filter';
import ajaxHandler from './segments/filter';
import './segments/image-preview';
import './segments/import-export';
import './segments/lib';
import './segments/navigation';
import './segments/options';
import './segments/reset';
import './segments/withdraw';
import './segments/column-filter';
import './segments/multiple_email_input';
import './quiz-attempts';
import './wp-events-subscriber';
import './segments/manage-api-keys';

const toggleChange = document.querySelectorAll('.tutor-form-toggle-input');
toggleChange.forEach((element) => {
	element.addEventListener('change', (e) => {
		let check_value = element.previousElementSibling;
		if (check_value) {
			check_value.value == 'on' ? (check_value.value = 'off') : (check_value.value = 'on');
		}
	});
});

jQuery(document).ready(function($) {
	'use strict';

	const { __ } = wp.i18n;
	/**i
	 * Color Picker
	 * @since v.1.2.21
	 */
	if (jQuery().wpColorPicker) {
		$('.tutor_colorpicker').wpColorPicker();
	}

	if (jQuery().select2) {
		$('.tutor_select2').select2();
	}

	/**
	 * Open Sidebar Menu
	 */
	if (_tutorobject.open_tutor_admin_menu) {
		var $adminMenu = $('#adminmenu');
		$adminMenu
			.find('[href="admin.php?page=tutor"]')
			.closest('li.wp-has-submenu')
			.addClass('wp-has-current-submenu');
		$adminMenu
			.find('[href="admin.php?page=tutor"]')
			.closest('li.wp-has-submenu')
			.find('a.wp-has-submenu')
			.removeClass('wp-has-current-submenu')
			.addClass('wp-has-current-submenu');
	}

	$(document).on('click', '.tutor-option-media-upload-btn', function(e) {
		e.preventDefault();

		var $that = $(this);
		var frame;
		if (frame) {
			frame.open();
			return;
		}
		frame = wp.media({
			title: __('Select or Upload Media Of Your Choice', 'tutor'),
			button: {
				text: __('Upload media', 'tutor'),
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
				.closest('.option-media-wrap')
				.find('.option-media-preview')
				.html('<img src="' + attachment.url + '" alt="" />');
			$that
				.closest('.option-media-wrap')
				.find('input')
				.val(attachment.id);
			$that
				.closest('.option-media-wrap')
				.find('.tutor-media-option-trash-btn')
				.show();
		});
		frame.open();
	});

	/**
	 * Remove option media
	 * @since v.1.4.3
	 */
	$(document).on('click', '.tutor-media-option-trash-btn', function(e) {
		e.preventDefault();

		var $that = $(this);
		$that
			.closest('.option-media-wrap')
			.find('img')
			.remove();
		$that
			.closest('.option-media-wrap')
			.find('input')
			.val('');
		$that
			.closest('.option-media-wrap')
			.find('.tutor-media-option-trash-btn')
			.hide();
	});

	// $(document).on("change", ".tutor-form-toggle-input", function(e) {
	//   var $that = $(this);

	//   var isEnable = $that.prop("checked") ? 1 : 0;
	//   var addonFieldName = $that.attr("name");

	//   $.ajax({
	//     url: window._tutorobject.ajaxurl,
	//     type: "POST",
	//     data: {
	//       isEnable: isEnable,
	//       addonFieldName: addonFieldName,
	//       action: "addon_enable_disable",
	//     },
	//     success: function(data) {
	//       if (data.success) {
	//         //Success
	//       }
	//     },
	//   });
	// });

	/**
	 * Add instructor
	 * @since v.1.0.3
	 */
	$(document).on('submit', '#tutor-new-instructor-form', function(e) {
		e.preventDefault();
		var $that = $(this);
		var formData = $that.serializeObject();
		var submitButton = $('#tutor-new-instructor-form [data-tutor-modal-submit]');
		var responseContainer = $('#tutor-new-instructor-form-response');
		formData.action = 'tutor_add_instructor';
		$.ajax({
			url: window._tutorobject.ajaxurl,
			type: 'POST',
			data: formData,
			beforeSend: function() {
				submitButton.attr('disabled', 'disable').addClass('is-loading');
				responseContainer.html('');
			},

			success: function success(data) {
				if (!data.success) {
					if (data?.data?.errors.errors) {
						for (let v of Object.values(data.data.errors.errors)) {
							responseContainer.append(`
								<div class='tutor-col'>
									<div class="tutor-alert tutor-warning">
									<div class="tutor-alert-text">
										<span class="tutor-alert-icon tutor-icon-circle-info tutor-mr-8"></span>
										<span>
											${v}
										</span>
									</div>
									</div>
								</div>
              				`);
						}
					} else {
						for (let v of Object.values(data.data.errors)) {
							responseContainer.append(`
								<div class='tutor-col'>
									<div class="tutor-alert tutor-warning">
									<div class="tutor-alert-text">
										<span class="tutor-alert-icon tutor-icon-circle-info tutor-mr-8"></span>
										<span>
											${v}
										</span>
									</div>
									</div>
								</div>
							`);
						}
					}
				} else {
					$('#tutor-new-instructor-form').trigger('reset');
					tutor_toast(__('Success', 'tutor'), __('New Instructor Added', 'tutor'), 'success');
					location.reload();
				}
			},
			complete: function() {
				submitButton.removeAttr('disabled').removeClass('is-loading');
			},
		});
	});

	/**
	 * Instructor block unblock action
	 * @since v.1.5.3
	 */
	$(document).on('click', 'a.instructor-action', async function(e) {
		e.preventDefault();

		const $that = $(this);
		const action = $that.attr('data-action');
		const instructorId = $that.attr('data-instructor-id');
		const loadingButton = e.target;
		const prevHtml = loadingButton.innerHTML;
		loadingButton.innerHTML = '';
		loadingButton.classList.add('is-loading');

		// prepare form data
		const formData = new FormData();
		formData.set('action', 'instructor_approval_action');
		formData.set('action_name', action);
		formData.set('instructor_id', instructorId);
		formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);

		try {
			const post = await ajaxHandler(formData);
			const response = await post.json();
			if (loadingButton.classList.contains('is-loading')) {
				loadingButton.classList.remove('is-loading');
				loadingButton.innerHTML = action.charAt(0).toUpperCase() + action.slice(1);
			}

			if (post.ok && response.success) {
				let message = '';
				if (action == 'approve') {
					message = 'Instructor approved!';
				}
				if (action == 'blocked') {
					message = 'Instructor blocked!';
				}
				/**
				 * If it is instructor modal for approve or blocked
				 * hide modal then show toast then reload
				 *
				 * @since v2.0.0
				 */
				const instructorModal = document.querySelector('.tutor-modal-ins-approval');
				if (instructorModal) {
					if (instructorModal.classList.contains('tutor-is-active')) {
						instructorModal.classList.remove('tutor-is-active');
					}
					tutor_toast(__('Success', 'tutor'), __(message, 'tutor'), 'success');
					location.href = `${window._tutorobject.home_url}/wp-admin/admin.php?page=tutor-instructors`;
				} else {
					tutor_toast(__('Success', 'tutor'), __(message, 'tutor'), 'success');
					location.reload();
				}
			} else {
				tutor_toast(__('Failed', 'tutor'), __('Something went wrong!', 'tutor'), 'error');
			}
		} catch (error) {
			loadingButton.innerHTML = prevHtml;
			tutor_toast(__('Operation failed', 'tutor'), error, 'error');
		}
	});

	/**
	 * If click on close instructor approve or modal then redirect to main URL
	 * if not redirect then it will not work with pagination.
	 */
	const instructorModal = document.querySelector('.tutor-modal-ins-approval .tutor-icon-56.tutor-icon-line-cross-line');
	if (instructorModal) {
		instructorModal.addEventListener('click', function() {
			console.log('ckk');
			location.href = `${window._tutorobject.home_url}/wp-admin/admin.php?page=tutor-instructors`;
		});
	}

	/**
	 * On form submit block | approve instructor
	 *
	 * @since v.2.0.0
	 */
	// if (instructorActionForm) {
	//   instructorActionForm.onsubmit = async (e) => {
	//     e.preventDefault();
	//     const formData = new FormData(instructorActionForm);
	//     const loadingButton = instructorActionForm.querySelector('#tutor-instructor-confirm-btn.tutor-btn-loading');
	//     const prevHtml = loadingButton.innerHTML;
	//     loadingButton.innerHTML = `<div class="ball"></div>
	//     <div class="ball"></div>
	//     <div class="ball"></div>
	//     <div class="ball"></div>`;
	//     try {
	//       const post = await ajaxHandler(formData);
	//       const response = await post.json();
	//       loadingButton.innerHTML = prevHtml;
	//       if (post.ok && response.success) {
	//         location.reload();
	//       } else {
	//         tutor_toast(__("Failed", "tutor"), __('Something went wrong!', 'tutor'), "error");
	//       }
	//     } catch (error) {
	//       loadingButton.innerHTML = prevHtml;
	//       tutor_toast(__("Operation failed", "tutor"), error, "error");
	//     }
	//   }
	// }

	/**
	 * Password Reveal
	 */
	$(document).on('click', '.tutor-password-reveal', function(e) {
		//toggle icon
		$(this).toggleClass('tutor-icon-eye-line tutor-icon-eye-bold');
		//toggle attr
		$(this)
			.next()
			.attr('type', function(index, attr) {
				return attr == 'password' ? 'text' : 'password';
			});
	});

	/**
	 * Used for backend profile photo upload.
	 */

	//tutor_video_poster_upload_btn
	$(document).on('click', '.tutor_video_poster_upload_btn', function(event) {
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
			title: __('Select or Upload Media Of Your Choice', 'tutor'),
			button: {
				text: __('Upload media', 'tutor'),
			},
			multiple: false, // Set to true to allow multiple files to be selected
		});

		// When an image is selected in the media frame...
		frame.on('select', function() {
			// Get media attachment details from the frame state
			var attachment = frame
				.state()
				.get('selection')
				.first()
				.toJSON();
			$that
				.closest('.tutor-video-poster-wrap')
				.find('.video-poster-img')
				.html('<img src="' + attachment.sizes.thumbnail.url + '" alt="" />');
			$that
				.closest('.tutor-video-poster-wrap')
				.find('input')
				.val(attachment.id);
		});
		// Finally, open the modal on click
		frame.open();
	});

	/**
	 * Tutor Memberships toggle in Paid Membership Pro panel
	 * @since v.1.3.6
	 */

	$(document).on('change', '#tutor_pmpro_membership_model_select', function(e) {
		e.preventDefault();

		var $that = $(this);

		if ($that.val() === 'category_wise_membership') {
			$('.membership_course_categories').show();
		} else {
			$('.membership_course_categories').hide();
		}
	});

	$(document).on('change', '#tutor_pmpro_membership_model_select', function(e) {
		e.preventDefault();

		var $that = $(this);

		if ($that.val() === 'category_wise_membership') {
			$('.membership_course_categories').show();
		} else {
			$('.membership_course_categories').hide();
		}
	});

	// Require category selection
	$(document).on('submit', '.pmpro_admin form', function(e) {
		var form = $(this);

		if (!form.find('input[name="tutor_action"]').length) {
			// Level editor or tutor action not necessary
			return;
		}

		if (
			form.find('[name="tutor_pmpro_membership_model"]').val() == 'category_wise_membership' &&
			!form.find('.membership_course_categories input:checked').length
		) {
			if (!confirm(__('Do you want to save without any category?', 'tutor'))) {
				e.preventDefault();
			}
		}
	});

	/**
	 * Show hide is course public checkbox (backend dashboard editor)
	 *
	 * @since  v.1.7.2
	 */
	var price_type = $('#tutor-attach-product [name="tutor_course_price_type"]');
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
	 * Focus selected instructor layout in setting page
	 *
	 * @since  v.1.7.5
	 */
	$(document).on('click', '.instructor-layout-template', function() {
		$('.instructor-layout-template').removeClass('selected-template');
		$(this).addClass('selected-template');
	});

	/**
	 * Programmatically open preview link. For some reason it's not working normally.
	 *
	 * @since  v.1.7.9
	 */
	$('#preview-action a.preview').click(function(e) {
		var href = $(this).attr('href');

		if (href) {
			e.preventDefault();
			window.open(href, '_blank');
		}
	});

	//add checkbox class for style
	var tutorCheckbox = $('.tutor-table .tutor-form-check-input');
	if (tutorCheckbox) {
		tutorCheckbox.parent().addClass('tutor-option-field-row');
	}
	const tdWithRadio = document.querySelectorAll("td[id^='tutor-student-course-'] .tutor-form-check");
	tdWithRadio.forEach((item) => {
		if (item) {
			if (item.classList.contains('tutor-option-field-row')) {
				item.classList.remove('tutor-option-field-row');
			}
		}
	});
	/**
	 * If Tutor course edit then show tutor menu as active
	 *
	 * @since v2.0.0
	 */
	let lists = document.querySelectorAll('#adminmenu li > a');
	if (window._tutorobject.is_tutor_course_edit && lists) {
		lists.forEach((item) => {
			if (item.tagName === 'A' && item.hasAttribute('href') && item.getAttribute('href') == 'admin.php?page=tutor') {
				item.classList.add('current');
				item.closest('li').classList.add('current');
				let mainMenu = item.closest('li#toplevel_page_tutor');
				let currentA = item.closest('#toplevel_page_tutor  li.wp-not-current-submenu.menu-top.toplevel_page_tutor > a');
				if (mainMenu) {
					mainMenu.className =
					'wp-has-submenu wp-has-current-submenu wp-menu-open menu-top toplevel_page_tutor current';
				}
				if (currentA) {
					currentA.className =
					'wp-has-submenu wp-has-current-submenu wp-menu-open menu-top toplevel_page_tutor current';
				}
			}
		});
	}
	
	/**
	 * Fix - Table last row context menu hidden.
	 * 
	 * @since 2.2.4
	 */
	let tableDropdown = jQuery('.tutor-table-responsive .tutor-table .tutor-dropdown')
	if (tableDropdown.length) {
		let tableHeight = jQuery('.tutor-table-responsive .tutor-table').height()
		jQuery('.tutor-table-responsive').css('min-height', tableHeight + 110)
	}

	/**
	 * Set get pro link
	 * @since 2.2.5
	 */
	const getProMenu = document.querySelector('span.tutor-get-pro-text')
	if (getProMenu?.parentElement?.nodeName === 'A') {
		const el = getProMenu.parentElement;
		const link = 'https://www.themeum.com/product/tutor-lms/pricing?utm_source=tutor_plugin_get_pro_page&utm_medium=wordpress_dashboard&utm_campaign=go_premium';

		el.setAttribute('href', link)
		el.setAttribute('target', '_blank')
	}

});

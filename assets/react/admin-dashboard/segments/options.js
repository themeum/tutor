import ajaxHandler from '../../../../../tutor-pro/assets/react/lib/ajax-handler';
import { get_response_message } from '../../helper/response';
import tutorFormData from '../../helper/tutor-formdata';

// SVG Icons Totor V2
const tutorIconsV2 = {
	warning:
		'<svg class="tutor-icon-v2 warning" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18.0388 14.2395C18.2457 14.5683 18.3477 14.9488 18.3321 15.3333C18.3235 15.6951 18.2227 16.0493 18.0388 16.3647C17.851 16.6762 17.5885 16.9395 17.2733 17.1326C16.9301 17.3257 16.5383 17.4237 16.1412 17.4159H5.87591C5.47974 17.4234 5.08907 17.3253 4.74673 17.1326C4.42502 16.9409 4.15549 16.6776 3.96071 16.3647C3.77376 16.0506 3.67282 15.6956 3.66741 15.3333C3.6596 14.9496 3.76106 14.5713 3.96071 14.2395L9.11094 5.64829C9.29701 5.31063 9.58016 5.03215 9.9263 4.84641C10.2558 4.67355 10.6248 4.58301 10.9998 4.58301C11.3747 4.58301 11.7437 4.67355 12.0732 4.84641C12.4259 5.02952 12.7154 5.30825 12.9062 5.64829L18.0388 14.2395ZM11.7447 10.4086C11.7447 10.2131 11.7653 10.0176 11.7799 9.81924C11.7946 9.62089 11.8063 9.41971 11.818 9.21853C11.8178 9.1484 11.8129 9.07836 11.8034 9.00885C11.7916 8.94265 11.7719 8.87799 11.7447 8.81617C11.6644 8.64655 11.5255 8.50928 11.3517 8.42798C11.1805 8.3467 10.9848 8.32759 10.8003 8.37414C10.6088 8.42217 10.4413 8.53471 10.3281 8.69149C10.213 8.84985 10.1525 9.03921 10.1551 9.2327C10.1551 9.3602 10.1756 9.48771 10.1844 9.61239C10.1932 9.73706 10.202 9.86457 10.2137 9.99208C10.2401 10.4709 10.2695 10.947 10.2988 11.4088C10.3281 11.8707 10.3545 12.3552 10.3838 12.8256C10.3857 12.9019 10.4032 12.9771 10.4352 13.0468C10.4672 13.1166 10.5131 13.1796 10.5703 13.2322C10.6275 13.2849 10.6948 13.3261 10.7685 13.3536C10.8422 13.381 10.9208 13.3942 10.9998 13.3923C11.0794 13.3946 11.1587 13.3813 11.2328 13.353C11.307 13.3248 11.3744 13.2822 11.4309 13.228C11.5454 13.1171 11.6115 12.968 11.6157 12.8114V12.5281C11.6157 12.4317 11.6157 12.3382 11.6157 12.2447C11.6362 11.9415 11.6538 11.6327 11.6743 11.3238C11.6949 11.015 11.7271 10.7118 11.7447 10.4086ZM10.9998 15.5118C11.1049 15.5119 11.2091 15.4919 11.3062 15.453C11.4034 15.4141 11.4916 15.3571 11.5658 15.2851C11.6441 15.2191 11.7061 15.137 11.7472 15.0448C11.7883 14.9526 11.8075 14.8527 11.8034 14.7524C11.8053 14.6497 11.7863 14.5476 11.7474 14.452C11.7085 14.3564 11.6505 14.2692 11.5767 14.1953C11.5029 14.1213 11.4147 14.0621 11.3172 14.0211C11.2197 13.9801 11.1149 13.958 11.0086 13.9562C10.9023 13.9543 10.7966 13.9727 10.6977 14.0103C10.5987 14.0479 10.5084 14.1039 10.4319 14.1752C10.3553 14.2465 10.2941 14.3317 10.2516 14.4259C10.2092 14.52 10.1863 14.6214 10.1844 14.7241C10.1844 14.933 10.2703 15.1333 10.4232 15.2811C10.5761 15.4288 10.7835 15.5118 10.9998 15.5118Z" fill="#9CA0AC"/></svg>',

	magnifyingGlass:
		'<svg class="tutor-icon-v2 magnifying-glass" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M10.3056 5.375C7.58249 5.375 5.375 7.58249 5.375 10.3056C5.375 13.0286 7.58249 15.2361 10.3056 15.2361C13.0286 15.2361 15.2361 13.0286 15.2361 10.3056C15.2361 7.58249 13.0286 5.375 10.3056 5.375ZM4.125 10.3056C4.125 6.89214 6.89214 4.125 10.3056 4.125C13.719 4.125 16.4861 6.89214 16.4861 10.3056C16.4861 13.719 13.719 16.4861 10.3056 16.4861C6.89214 16.4861 4.125 13.719 4.125 10.3056Z" fill="#9CA0AC"/><path fill-rule="evenodd" clip-rule="evenodd" d="M13.7874 13.7872C14.0314 13.5431 14.4272 13.5431 14.6712 13.7872L17.6921 16.8081C17.9362 17.0521 17.9362 17.4479 17.6921 17.6919C17.448 17.936 17.0523 17.936 16.8082 17.6919L13.7874 14.6711C13.5433 14.427 13.5433 14.0313 13.7874 13.7872Z" fill="#9CA0AC"/></svg>',

	angleRight:
		'<svg class="tutor-icon-v2 angle-right" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.842 12.633C7.80402 12.6702 7.7592 12.6998 7.71 12.72C7.65839 12.7401 7.60341 12.7503 7.548 12.75C7.49655 12.7496 7.44563 12.7395 7.398 12.72C7.34843 12.7005 7.30347 12.6709 7.266 12.633L6.88201 12.252C6.84384 12.2138 6.81284 12.1691 6.79051 12.12C6.76739 12.0694 6.75367 12.015 6.75001 11.9595C6.74971 11.9045 6.75832 11.8498 6.77551 11.7975C6.79308 11.7477 6.82181 11.7025 6.85951 11.6655L9.53249 9.00001L6.86701 6.33453C6.82576 6.29904 6.79427 6.2536 6.77551 6.20253C6.75832 6.15026 6.74971 6.09555 6.75001 6.04053C6.75367 5.98502 6.76739 5.93064 6.79051 5.88003C6.81284 5.8309 6.84384 5.78619 6.88201 5.74803L7.263 5.36704C7.30047 5.32916 7.34543 5.29953 7.395 5.28004C7.44263 5.26056 7.49355 5.25038 7.545 5.25004C7.60142 5.24931 7.65745 5.2595 7.71 5.28004C7.7592 5.30025 7.80402 5.3298 7.842 5.36704L11.181 8.70752C11.2233 8.74442 11.2579 8.78926 11.283 8.83951C11.3077 8.88941 11.3206 8.94433 11.3206 9.00001C11.3206 9.05569 11.3077 9.11062 11.283 9.16051C11.2579 9.21076 11.2233 9.25561 11.181 9.29251L7.842 12.633Z" fill="#B4B7C0"/></svg>',
};

// Tutor v2 icons
const { angleRight, magnifyingGlass, warning } = tutorIconsV2;

document.addEventListener('DOMContentLoaded', function () {
	var $ = window.jQuery;
	const { __ } = wp.i18n;

	let image_uploader = document.querySelectorAll('.image_upload_button');
	// let image_input = document.getElementById("image_url_field");

	for (let i = 0; i < image_uploader.length; ++i) {
		let image_upload_wrap = image_uploader[i].closest('.image-previewer');
		let input_file = image_upload_wrap.querySelector('.input_file');
		let upload_preview = image_upload_wrap.querySelector('.upload_preview');
		let email_title_logo = document.querySelector('[data-source="email-title-logo"]');
		// document.querySelector(
		//   "[data-source='email-title-logo']"
		// );
		let image_delete = image_upload_wrap.querySelector('.delete-btn');

		image_uploader[i].onclick = function (e) {
			e.preventDefault();

			var image_frame = wp.media({
				title: 'Upload Image',
				library: {
					type: 'image',
				},
				multiple: false,
				frame: 'post',
				state: 'insert',
			});

			image_frame.open();

			/* image_frame.on("select", function (e) {
				console.log("image size");
				console.log(image.state().get("selection").first().toJSON());

				var image_url = image_frame.state().get("selection").first().toJSON().url;

				upload_previewer.src = image_input.value = image_url;
			}); */

			image_frame.on('insert', function (selection) {
				var state = image_frame.state();
				selection = selection || state.get('selection');
				if (!selection) return;
				// We set multiple to false so only get one image from the uploader
				var attachment = selection.first();
				var display = state.display(attachment).toJSON(); // <-- additional properties
				attachment = attachment.toJSON();
				// Do something with attachment.id and/or attachment.url here
				var image_url = attachment.sizes[display.size].url;

				if (null !== upload_preview) {
					upload_preview.src = input_file.value = image_url;
				}
				if (null !== email_title_logo) {
					email_title_logo.src = input_file.value = image_url;
				}
			});
		};

		image_delete.onclick = function () {
			input_file.value = '';
			email_title_logo.src = '';
		};
	}

	const validateEmail = (email) => {
		const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(String(email).toLowerCase());
	};

	$(window).on('click', function (e) {
		$('.tutor-notification, .search_result').removeClass('show');
	});

	$('.tutor-notification-close').click(function (e) {
		$('.tutor-notification').removeClass('show');
	});

	var formSubmit = false;
	const checkEmailFields = (inputFields) => {
		inputFields.forEach((emailField) => {
			emailField.onchange = (e) => {
				if (false === validateEmail(emailField.value)) {
					emailField.style.borderColor = 'red';
					emailField.focus();
					formSubmit = false;
				} else {
					emailField.style.borderColor = '#ddd';
					formSubmit = true;
				}
			};
		});
	};

	const checkNumberFields = (inputFields) => {
		inputFields.forEach((numberField) => {
			numberField.oninput = (e) => {
				const { target } = e;
				const min = Number(target.getAttribute('min') || -Infinity);
				const max = Number(target.getAttribute('max') || Infinity);
				const numberType = target.getAttribute('data-number-type') || 'decimal';
				const value = Number(target.value);

				if (min !== -Infinity && value <= min) e.target.value = min;
				if (max !== Infinity && value >= max) e.target.value = max;
				if (['integer', 'int'].includes(numberType)) e.target.value = parseInt(e.target.value)
			};
		});
	};

	const checkEmailFieldsOnSubmit = (inputFields) => {
		inputFields.forEach((emailField) => {
			let pageNeedsValidation = emailField.closest('.tutor-option-nav-page');
			let invalidLabel = emailField && emailField.parentNode.parentNode.querySelector('[tutor-option-name]').innerText;
			let pageTitle = pageNeedsValidation && pageNeedsValidation.querySelector('[tutor-option-title]').innerText;

			let invalidMessage = '"' + pageTitle + ' > ' + invalidLabel + '" email is invalid!';
			if (false === validateEmail(emailField.value)) {
				emailField.style.borderColor = 'red';
				emailField.focus();
				tutor_toast('Warning', invalidMessage, 'error');
			} else {
				formSubmit = true;
			}
		});
	};

	const checkNumberFieldsOnSubmit = (inputFields) => {
		inputFields.forEach((numberField) => {
			// console.log(numberField);
		});
	};

	const inputEmailFields = document.querySelectorAll('.tutor-form-control[type="email"]');
	// const inputEmailFields = document.querySelectorAll('[type="email"]');
	const inputNumberFields = document.querySelectorAll('.tutor-form-control[type="number"]');
	// const inputNumberFields = document.querySelectorAll('[type="number"]');

	if (inputNumberFields.length) checkNumberFields(inputNumberFields);

	if (0 !== inputEmailFields.length) {
		checkEmailFields(inputEmailFields);
	} else {
		formSubmit = true;
	}

	$('#save_tutor_option').click(function (e) {
		e.preventDefault();
		$('#tutor-option-form').submit();
	});

	$('#tutor-option-form').submit(function (e) {
		e.preventDefault();
		if (tinyMCE) {
			tinyMCE.triggerSave();
		}
		var button = $('#save_tutor_option');
		var $form = $(this);
		var data = $form.serializeObject();

		// if (typeof inputNumberFields !== 'undefined') {
		if (0 !== inputNumberFields.length) {
			checkNumberFieldsOnSubmit(inputNumberFields);
		}
		// if (typeof inputEmailFields !== 'undefined') {
		if (0 !== inputEmailFields.length) {
			checkEmailFieldsOnSubmit(inputEmailFields);
		}

		if (true === formSubmit) {
			if (!e.detail || e.detail == 1) {
				$.ajax({
					url: window._tutorobject.ajaxurl,
					type: 'POST',
					data: data,
					beforeSend: function () {
						button.addClass('is-loading');
						button.attr('disabled', true);
					},
					success: function (resp) {
						const { data = {}, success, message = __('Settings Saved', 'tutor') } = resp || {};

						if (success) {
							// Disableing save btn after saved successfully
							if (document.getElementById('save_tutor_option')) {
								document.getElementById('save_tutor_option').disabled = true;
							}
							tutor_toast(__('Success!', 'tutor'), message, 'success');
							window.dispatchEvent(new CustomEvent('tutor_option_saved', { detail: data }));
						} else {
							tutor_toast(__('Warning!', 'tutor'), message, 'warning');
						}
					},
					complete: function () {
						button.removeClass('is-loading');
						button.attr('disabled', 'disabled');
					},
				});
			}
		}
	});

	/**
	 * Manual payment management
	 *
	 * @since 3.0.0
	 */
	const manualPaymentForm = document.getElementById('tutor-manual-payment-form');
	const manualPaymentUpdateForm = document.getElementById('tutor-update-manual-payment-form');
	const editManualPaymentBtns = document.querySelectorAll('.tutor-manual-payment-method-edit');
	const deleteManualPaymentBtns = document.querySelectorAll('.tutor-manual-payment-method-delete');
	const defaultErrorMsg = __('Something went wrong, please try again!', 'tutor');

	if (manualPaymentForm) {
		manualPaymentForm.addEventListener('submit', async (e) => {
			const button = manualPaymentForm.querySelector('#tutor-manual-payment-button');
			e.preventDefault();
			const formData = new FormData(manualPaymentForm);
			
			button.classList.add('is-loading');
			button.setAttribute('disabled', true);

			try {
				const post = await ajaxHandler(formData);
				if (post.ok) {
					const {success, data} = await post.json();
					if (success) {
						tutor_toast(__('Success!', 'tutor'), data, 'success');
						this.location.reload();
					} else {
						tutor_toast(__('Error!', 'tutor'), data, 'error');
					}
				} else {
					tutor_toast(__('Error!', 'tutor'), defaultErrorMsg, 'error');
				}
			} catch (error) {
				tutor_toast(__('Error!', 'tutor'), error, 'error');
			} finally {
				button.classList.remove('is-loading');
				button.removeAttribute('disabled');
			}
			
		});
	}

	deleteManualPaymentBtns.forEach(btn => {
		btn.addEventListener('click', async(e) => {
			// Set target elem
			let t = e.target;
			if (t.tagName === 'I' || t.tagName === 'SPAN') {
				t = t.closest('a');
			}

			const paymentMethodId = t.dataset.paymentMethodId;
			const formData = tutorFormData([{payment_method_id: paymentMethodId, action: 'tutor_delete_manual_payment_method'}]);

			t.classList.add('is-loading');
			t.setAttribute('disabled', true);

			try {
				const post = await ajaxHandler(formData);
				if (post.ok) {
					const {success, data} = await post.json();
					if (success) {						
						e.target.closest('.tutor-option-single-item').remove(); 
						tutor_toast(__('Success!', 'tutor'), data, 'success');
					} else {
						tutor_toast(__('Failed!', 'tutor'), data, 'error');
					}
				}
			} catch (error) {
				tutor_toast(__('Error!', 'tutor'), error, 'error');
			} finally {
				t.classList.remove('is-loading');
				t.removeAttribute('disabled');
			}
		})
	})
	editManualPaymentBtns.forEach(btn => {
		btn.addEventListener('click', async(e) => {
			// Set target elem
			let t = e.target;
			if (t.tagName === 'I' || t.tagName === 'SPAN') {
				t = t.closest('a');
			}

			// Set update form data.
			manualPaymentUpdateForm.querySelector('input[name=is_enable]').value = t.dataset.isEnable;
			manualPaymentUpdateForm.querySelector('input[name=payment_method_id]').value = t.dataset.paymentMethodId;
			manualPaymentUpdateForm.querySelector('input[name=payment_method_name]').value = t.dataset.paymentMethodName;
			manualPaymentUpdateForm.querySelector('textarea[name=additional_details]').value = t.dataset.additionalDetails;
			manualPaymentUpdateForm.querySelector('textarea[name=payment_instructions]').value = t.dataset.paymentInstructions;
		})
	});

	if (manualPaymentUpdateForm) {
		manualPaymentUpdateForm.addEventListener('submit', async (e) => {
			const button = manualPaymentUpdateForm.querySelector('button[type=submit]');
			e.preventDefault();
			const formData = new FormData(manualPaymentUpdateForm);
			
			button.classList.add('is-loading');
			button.setAttribute('disabled', true);

			try {
				const post = await ajaxHandler(formData);
				if (post.ok) {
					const {success, data} = await post.json();
					if (success) {
						tutor_toast(__('Success!', 'tutor'), data, 'success');
						this.location.reload();
					} else {
						tutor_toast(__('Error!', 'tutor'), data, 'error');
					}
				} else {
					tutor_toast(__('Error!', 'tutor'), defaultErrorMsg, 'error');
				}
			} catch (error) {
				tutor_toast(__('Error!', 'tutor'), error, 'error');
			} finally {
				button.classList.remove('is-loading');
				button.removeAttribute('disabled');
			}
			
		});
	}

	// Handle enable/disable
	const manualPaymentToggleBtn = document.querySelectorAll('.tutor-manual-payment-switch');
	manualPaymentToggleBtn.forEach(btn => {
		btn.addEventListener('click', async (e) => {
			const data = [
				{
					is_enable: e.target.checked ? 'on' : 'off',
					payment_method_id: e.target.dataset.paymentMethodId,
					action: 'tutor_add_manual_payment_method',
				}
			];

			const formData = tutorFormData(data);

			try {
				const post = await ajaxHandler(formData);
				if (post.ok) {
					const {success, data} = await post.json();
					if (success) {
						tutor_toast(__('Success!', 'tutor'), data, 'success');
					} else {
						tutor_toast(__('Error!', 'tutor'), data, 'error');
					}
				} else {
					tutor_toast(__('Error!', 'tutor'), defaultErrorMsg, 'error');
				}
			} catch (error) {
				tutor_toast(__('Error!', 'tutor'), error, 'error');
			}
		});
	});


	function view_item(text, section_slug, section, block, field_key) {
		var navTrack = block ? `${angleRight} ${block}` : '';

		var output = `
		<a data-tab="${section_slug}" data-key="field_${field_key}">
			<div class="search_result_title">
			${magnifyingGlass}
			<span class="tutor-fs-7">${text}</span>
			</div>
			<div class="search_navigation">
			<div class="nav-track tutor-fs-7">
				<span>${section}</span>
				<span>${navTrack}</span>
			</div>
			</div>
		</a>`;

		return output;
	}

	let wait_for_input;
	$('#search_settings').on('input', function (e) {
		e.preventDefault();
		let $this = $(this);

		if (wait_for_input) {
			window.clearTimeout(wait_for_input);
		}

		wait_for_input = window.setTimeout(() => {
			if (e.target.value) {
				var searchKey = this.value;
				$.ajax({
					url: window._tutorobject.ajaxurl,
					type: 'POST',
					data: {
						action: 'tutor_option_search',
						keyword: searchKey,
					},

					beforeSend: function () {
						$this.parent().find('.tutor-form-icon').removeClass('tutor-icon-search').addClass('tutor-icon-circle-notch tutor-animation-spin');
					},

					success: function (data) {

						if (!data.success) {
							tutor_toast(__('Error', 'tutor'), get_response_message(data), 'error');
							return;
						}

						var output = '',
							wrapped_item = '',
							notfound = true,
							item_text = '',
							section_slug = '',
							section_label = '',
							block_label = '',
							matchedText = '',
							searchKeyRegex = '',
							field_key = '',
							result = data.data.fields;

						Object.values(result).forEach(function (item, index, arr) {
							item_text = item.label;
							section_slug = item.section_slug;
							section_label = item.section_label;
							block_label = item.block_label;
							field_key = item.event ? item.key + '_' + item.event : item.key;
							searchKeyRegex = new RegExp(searchKey, 'ig');
							matchedText = item_text.match(searchKeyRegex)?.[0];

							if (matchedText) {
								wrapped_item = item_text.replace(
									searchKeyRegex,
									`<span style='color: #212327; font-weight:500'>${matchedText}</span>`,
								);

								output += view_item(wrapped_item, section_slug, section_label, block_label, field_key);
								notfound = false;
							}
						});
						if (notfound) {
							output += `<div class="no_item">${warning} No Results Found</div>`;
						}

						$('.search_result').html(output).addClass('show');

						$this.parent().find('.tutor-form-icon').removeClass('tutor-icon-circle-notch tutor-animation-spin').addClass('tutor-icon-search');

						output = '';
					},
					complete: function () {
						navigationTrigger();
					},
				});
			} else {
				document.querySelector('.search-popup-opener').classList.remove('show');
			}

			wait_for_input = undefined;
		}, 500);
	});

	/**
	 * Search suggestion, navigation trigger
	 */
	function navigationTrigger() {
		const suggestionLinks = document.querySelectorAll('.tutor-options-search .search-popup-opener a');
		const navTabItems = document.querySelectorAll('[tutor-option-tabs] li > a');
		const navPages = document.querySelectorAll('.tutor-option-nav-page');

		suggestionLinks.forEach((link) => {
			link.addEventListener('click', (e) => {
				const dataTab = e.target.closest('[data-tab]').dataset.tab;
				const dataKey = e.target.closest('[data-key]').dataset.key;
				if (dataTab) {
					document.title = e.target.innerText + ' < ' + _tutorobject.site_title;
					navTabItems.forEach((item) => {
						item.classList.remove('is-active');
					});

					// add active to the current nav item
					document.querySelector(`.tutor-option-tabs [data-tab=${dataTab}]`).classList.add('is-active');

					// hide other tab contents
					navPages.forEach((content) => {
						content.classList.remove('is-active');
					});
					// add active to the current content
					document.querySelector(`.tutor-option-tab-pages #${dataTab}`).classList.add('is-active');

					// History push
					const url = new URL(window.location);
					url.searchParams.set('tab_page', dataTab);
					window.history.pushState({}, '', url);
				}

				// Reset + Hide Suggestion box
				document.querySelector('.search-popup-opener').classList.remove('visible');
				document.querySelector('.tutor-options-search input[type="search"]').value = '';
				// Highlight selected element
				highlightSearchedItem(dataKey);
			});
		});
	}

	/**
	 * Highlight items form search suggestion
	 */
	function highlightSearchedItem(dataKey) {
		const target = document.querySelector(`#${dataKey}`);
		const targetEl = target && target.querySelector(`[tutor-option-name]`);
		const scrollTargetEl = target && target.parentNode.querySelector('.tutor-option-field-row');

		if (scrollTargetEl) {
			targetEl.classList.add('isHighlighted');
			setTimeout(() => {
				targetEl.classList.remove('isHighlighted');
			}, 6000);

			scrollTargetEl.scrollIntoView({
				behavior: 'smooth',
				block: 'center',
				inline: 'nearest',
			});
		} else {
			console.warn(`scrollTargetEl Not found!`);
		}
	}

	/**
	 * Highlight items form query params
	 */
	const urlParams = new URLSearchParams(window.location.search);
	if (urlParams.get('highlight')) {
		highlightSearchedItem(urlParams.get('highlight'));
	}

	/**
	 * Show/Hide setting option
	 * @param object element			Dom object
	 * @param string value 				change value
	 * @param string required_value		Required value for match the conditon for show, else it will hide
	 * @return void
	 * 
	 * @since 2.0.7
	 */
	function showHideOption(element, value, required_value) {
		if (element.style === undefined) return;

		value === (required_value !== undefined ? required_value : 'on')
			? element.style.display = 'grid'
			: element.style.display = 'none'
	}

	/**
	 * Input value change detector (Normal/Hidden input)
	 * 
	 * @param object	element 
	 * @param function	callback 
	 * @return void
	 * 
	 * @since 2.0.7
	 */
	function changeListener(element, callback) {
		MutationObserver = window.MutationObserver || window.WebKitMutationObserver;
		let observer = new MutationObserver(function (mutations, observer) {
			if (mutations[0].attributeName == "value") {
				if (typeof callback === 'function') {
					callback(element.value)
				}
			}
		});
		observer.observe(element, {
			attributes: true
		});
	}

	/**
	 * Woocommerce order auto complete
	 *
	 * @since 2.0.5
	 * 
	 * Invoice generate options added
	 *
	 * @since 2.1.4
	 */
	const monetization_field = document.querySelector("[name='tutor_option[monetize_by]']");
	const order_autocomplete_wrapper = document.getElementById('field_tutor_woocommerce_order_auto_complete');

	const invoice_field = document.querySelector("[name='tutor_option[tutor_woocommerce_invoice]']");
	const invoice_field_wrapper = document.getElementById('field_tutor_woocommerce_invoice');

	if (invoice_field) {
		showHideOption(invoice_field_wrapper, monetization_field.value, 'wc')
	}

	if (monetization_field) {
		showHideOption(order_autocomplete_wrapper, monetization_field.value, 'wc');
		monetization_field.onchange = (e) => {
			showHideOption(order_autocomplete_wrapper, e.target.value, 'wc');
			showHideOption(invoice_field_wrapper, e.target.value, 'wc');
		}
	}

	/**
	 * On toggle switch change - show, hide setting's elements
	 * @since 2.1.9
	 */
	function showHideToggleChildren(el) {
		let isChecked = el.is(':checked')
		let fields = el.data('toggle-fields').split(',')
		if (Array.isArray(fields) === false || fields.length === 0) return

		fields = fields.map(s => s.trim());
		isChecked
			? fields.forEach((f) => $('#field_' + f).removeClass('tutor-hide-option'))
			: fields.forEach((f) => $('#field_' + f).addClass('tutor-hide-option'))

		let toggleWrapper = el.parent().parent().parent()
		let sectionWrapper = el.parent().parent().parent().parent()
		let visibleElements = sectionWrapper.find('.tutor-option-field-row').not('div.tutor-hide-option').length

		visibleElements === 1
			? toggleWrapper.addClass('tutor-option-no-bottom-border')
			: toggleWrapper.removeClass('tutor-option-no-bottom-border')

	}

	const btnToggles = $('input[type="checkbox"][data-toggle-fields]')
	btnToggles.each(function () {
		showHideToggleChildren($(this))
	})

	btnToggles.change(function () {
		showHideToggleChildren($(this))
	})

	/**
	 * Maxlength counter for Textarea and Text field.
	 * @since 2.2.3
	 */
	let maxLengthTargets = $('.tutor-option-field-input textarea[maxlength], .tutor-option-field-input input[maxlength]')
	maxLengthTargets.each(function () {
		let el = $(this),
			max = $(this).attr('maxlength'),
			len = $(this).val().length,
			text = `${len}/${max}`;

		el.css('margin-right', 0)
		$(this).parent().append(`<div class="tutor-field-maxlength-info tutor-mr-4 tutor-fs-8 tutor-color-muted">${text}</div>`)
	});

	maxLengthTargets.keyup(function () {
		let el = $(this),
			max = $(this).attr('maxlength'),
			len = $(this).val().length,
			text = `${len}/${max}`;

		el.parent().find('.tutor-field-maxlength-info').text(text)
	})

});

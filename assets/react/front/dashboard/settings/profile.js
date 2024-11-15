const { get_response_message } = require('../../../helper/response');

const validURL = (str) => {
	var pattern = new RegExp(
		'^(https?:\\/\\/)?' + // protocol
		'((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' + // domain name
		'((\\d{1,3}\\.){3}\\d{1,3}))' + // OR ip (v4) address
		'(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // port and path
		'(\\?[;&a-z\\d%_.~+=-]*)?' + // query string
		'(\\#[-a-z\\d_]*)?$',
		'i',
	); // fragment locator
	return !!pattern.test(str);
};

const getResizedFromUploaded = (file, dimension, callback) => {
	// Read image as data url
	let reader = new FileReader();
	reader.addEventListener('load', () => {

		// Read as image to retrieve dimension
		let image = new Image();
		image.addEventListener('load', () => {
			let { width, height } = image;

			let left = 0;
			let top = 0;
			let w = width;
			let h = height;

			if (dimension.width == dimension.height) {
				left = width > height ? (width - height) / 2 : 0;
				top = height > width ? (height - width) / 2 : 0;

				w = width > height ? height : width;
				h = height > width ? width : height;
			}

			dimension.height = dimension.height || (height / width) * dimension.width;

			let dm_width = dimension.width > width ? width : dimension.width;
			let dm_height = dimension.width > width ? height : dimension.height;

			// Create the destination canvas
			let canvas = document.createElement('canvas');
			canvas.width = dm_width;
			canvas.height = dm_height;
			let context = canvas.getContext('2d');

			context.drawImage(image, left, top, w, h, 0, 0, canvas.width, canvas.height);

			canvas.toBlob(blob => {
				blob.name = file.name;
				blob.lastModified = file.lastModified;

				let reader2 = new FileReader();
				reader2.addEventListener('load', () => {
					callback(blob, reader2.result);
				})
				reader2.readAsDataURL(blob);

			}, 'image/jpeg');
		});

		image.src = reader.result;
	});

	reader.readAsDataURL(file);
}

window.jQuery(document).ready(($) => {
	const { __ } = wp.i18n;
	/**
	 * Profile Photo and Cover Photo editor
	 *
	 * @since  v.1.7.5
	 */
	var PhotoEditor = function (photo_editor) {
		this.dialogue_box = photo_editor.find('#tutor_photo_dialogue_box');

		this.open_dialogue_box = function (name) {
			this.dialogue_box.attr('name', name);
			this.dialogue_box.trigger('click');
		};

		this.upload_selected_image = function (name, file) {

			var nonce = tutor_get_nonce_data(true);

			var context = this;
			context.toggle_loader(name, true);

			// Prepare payload to upload
			var form_data = new FormData();
			form_data.append('action', 'tutor_user_photo_upload');
			form_data.append('photo_type', name);
			form_data.append('photo_file', file, file.name);
			form_data.append(nonce.key, nonce.value);

			// Upload the image to server
			const _this = this;
			$.ajax({
				url: window._tutorobject.ajaxurl,
				data: form_data,
				type: 'POST',
				processData: false,
				contentType: false,
				error: context.error_alert,
				success: function () {
					let photoType = _this.title_capitalize(name.replace('_', ' '));
					let title = __('Success', 'tutor');
					let msg = photoType + ' Changed Successfully!';

					if ('Profile Photo' === photoType) {
						msg = __('Profile Photo Changed Successfully!', 'tutor');
					}
					if ('Cover Photo' === photoType) {
						msg = __('Cover Photo Changed Successfully!', 'tutor');
					}

					tutor_toast(title, msg, 'success');
				},
				complete: function () {
					context.toggle_loader(name, false);
				},
			});
		};

		this.title_capitalize = function (string) {
			const arr = string.split(' ');
			for (var i = 0; i < arr.length; i++) {
				arr[i] = arr[i].charAt(0).toUpperCase() + arr[i].slice(1);
			}
			return arr.join(' ');
		};
		this.accept_upload_image = function (context, e) {
			var file = e.currentTarget.files[0] || null;
			context.update_preview(e.currentTarget.name, file);

			// Resize 
			getResizedFromUploaded(file, { width: 1200 }, blob => {
				context.upload_selected_image(e.currentTarget.name, blob);
			});

			$(e.currentTarget).val('');
		};

		this.delete_image = function (name) {
			var context = this;
			context.toggle_loader(name, true);

			$.ajax({
				url: window._tutorobject.ajaxurl,
				data: { action: 'tutor_user_photo_remove', photo_type: name },
				type: 'POST',
				error: context.error_alert,
				complete: function () {
					context.toggle_loader(name, false);
				},
			});
		};

		this.update_preview = function (name, file) {
			var renderer = photo_editor.find(name == 'cover_photo' ? '#tutor_cover_area' : '#tutor_profile_area');

			if (!file) {
				renderer.css('background-image', 'url(' + renderer.data('fallback') + ')');
				this.delete_image(name);
				return;
			}

			var reader = new FileReader();

			reader.onload = function (e) {
				renderer.css('background-image', 'url(' + e.target.result + ')');
			};

			reader.readAsDataURL(file);
		};

		this.toggle_profile_pic_action = function (show) {
			var method = show === undefined ? 'toggleClass' : show ? 'addClass' : 'removeClass';
			photo_editor[method]('pop-up-opened');
		};

		this.error_alert = function () {
			tutor_toast('Error', 'Maximum file size exceeded!', 'error');
			// alert('Something Went Wrong.');
		};

		this.toggle_loader = function (name, show) {
			photo_editor.find('#tutor_photo_meta_area .loader-area').css('display', show ? 'block' : 'none');
		};

		this.initialize = function () {
			var context = this;

			this.dialogue_box.change(function (e) {
				context.accept_upload_image(context, e);
			});

			photo_editor.find('#tutor_profile_area .tutor_overlay, #tutor_pp_option>div:last-child').click(function () {
				context.toggle_profile_pic_action();
			});

			// Upload new
			photo_editor.find('.tutor_cover_uploader').click(function () {
				context.open_dialogue_box('cover_photo');
			});
			photo_editor.find('.tutor_pp_uploader').click(function () {
				context.open_dialogue_box('profile_photo');
			});

			// Delete existing
			photo_editor.find('.tutor_cover_deleter').click(function () {
				context.update_preview('cover_photo', null);
			});
			photo_editor.find('.tutor_pp_deleter').click(function () {
				context.update_preview('profile_photo', null);
			});
		};
	};

	var photo_editor = $('#tutor_profile_cover_photo_editor');
	photo_editor.length > 0 ? new PhotoEditor(photo_editor).initialize() : 0;

	// Save profile settings with ajax
	$('.tutor-profile-settings-save').click(function (e) {
		e.preventDefault();

		var btn = $(this);
		var form = btn.closest('form');
		var data = form.serializeObject();
		let phone = document.querySelector('[name=phone_number]');

		/**
		 * Basic markup for profile bio
		 * @since 2.2.4
		 */
		if (window.tinyMCE !== undefined) {
			let editor = tinyMCE.get('tutor_profile_bio');
			data.tutor_profile_bio = editor.getContent({ format: 'html' });
		}

		if (data.phone_number && !data.phone_number.match(/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/im)) {
			phone.classList.add('invalid');
			tutor_toast('Invalid', 'Invalid phone number', 'error');
			phone.focus();
			return false;
		} else {
			phone.classList.remove('invalid');
		}

		data.action = 'tutor_update_profile';

		$.ajax({
			url: _tutorobject.ajaxurl,
			type: 'POST',
			data,
			beforeSend: () => {
				btn.addClass('is-loading');
			},
			success: (resp) => {
				let { success } = resp;

				if (success) {
					window.tutor_toast('Success', get_response_message(resp), 'success');
				} else {
					window.tutor_toast('Error', get_response_message(resp), 'error');
				}
			},
			complete: () => {
				btn.removeClass('is-loading');
			},
		});
	});

	$('#user_social_form').submit(function (e) {
		e.preventDefault();

		const btnSubmit = $(this).find('button[type=submit]');
		const url = _tutorobject.ajaxurl;
		const data = $(this).serializeObject();

		$.ajax({
			url: url,
			type: 'POST',
			data,
			beforeSend: () => {
				btnSubmit.addClass('is-loading');
			},
			success: (resp) => {
				let { success } = resp;
				let message = get_response_message(resp);
				if (success) {
					window.tutor_toast(__('Success', 'tutor'), message, 'success');
				} else {
					window.tutor_toast(__('Error', 'tutor'), message, 'error');
				}
			},
			complete: () => {
				btnSubmit.removeClass('is-loading');
			},
		});

	})
});

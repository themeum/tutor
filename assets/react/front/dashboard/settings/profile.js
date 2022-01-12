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

window.jQuery(document).ready(($) => {
	/**
	 * Profile Photo and Cover Photo editor
	 *
	 * @since  v.1.7.5
	 */
	var PhotoEditor = function(photo_editor) {
		this.dialogue_box = photo_editor.find('#tutor_photo_dialogue_box');

		this.open_dialogue_box = function(name) {
			this.dialogue_box.attr('name', name);
			this.dialogue_box.trigger('click');
		};

		this.validate_image = function(file) {
			return true;
		};

		this.upload_selected_image = function(name, file) {
			if (!file || !this.validate_image(file)) {
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
			// let server_max_size = photo_editor.find('.upload_max_filesize').val();
			// this.verify_filesize(file);
			if (this.verify_filesize(file)) {
				$.ajax({
					url: window._tutorobject.ajaxurl,
					data: form_data,
					type: 'POST',
					processData: false,
					contentType: false,
					error: context.error_alert,
					complete: function() {
						context.toggle_loader(name, false);
					},
				});
				let photoType = this.title_capitalize(name.replace('_', ' '));
				tutor_toast('Success', photoType + ' Changed successfully!', 'success');
			} else {
				tutor_toast('Error', 'Maximum file size exceeded!', 'error');
				return false;
			}

			// console.log(this.max_filesize,name,file);
		};

		this.title_capitalize = function(string) {
			const arr = string.split(' ');
			for (var i = 0; i < arr.length; i++) {
				arr[i] = arr[i].charAt(0).toUpperCase() + arr[i].slice(1);
			}
			return arr.join(' ');
		};
		this.accept_upload_image = function(context, e) {
			var file = e.currentTarget.files[0] || null;
			if (this.verify_filesize(file)) {
				context.update_preview(e.currentTarget.name, file);
			}
			context.upload_selected_image(e.currentTarget.name, file);
			$(e.currentTarget).val('');
		};

		this.delete_image = function(name) {
			var context = this;
			context.toggle_loader(name, true);

			$.ajax({
				url: window._tutorobject.ajaxurl,
				data: { action: 'tutor_user_photo_remove', photo_type: name },
				type: 'POST',
				error: context.error_alert,
				complete: function() {
					context.toggle_loader(name, false);
				},
			});
		};

		this.update_preview = function(name, file) {
			var renderer = photo_editor.find(name == 'cover_photo' ? '#tutor_cover_area' : '#tutor_profile_area');

			if (!file) {
				renderer.css('background-image', 'url(' + renderer.data('fallback') + ')');
				this.delete_image(name);
				return;
			}

			var reader = new FileReader();
			this.verify_filesize(file);

			reader.onload = function(e) {
				renderer.css('background-image', 'url(' + e.target.result + ')');
			};

			reader.readAsDataURL(file);
		};

		this.verify_filesize = function(file) {
			let server_max_size = photo_editor.find('.upload_max_filesize').val();

			if (server_max_size < file.size) {
				return false;
			}
			return true;
		};

		this.toggle_profile_pic_action = function(show) {
			var method = show === undefined ? 'toggleClass' : show ? 'addClass' : 'removeClass';
			photo_editor[method]('pop-up-opened');
		};

		this.error_alert = function() {
			tutor_toast('Error', 'Maximum file size exceeded!', 'error');
			// alert('Something Went Wrong.');
		};

		this.toggle_loader = function(name, show) {
			photo_editor.find('#tutor_photo_meta_area .loader-area').css('display', show ? 'block' : 'none');
		};

		this.initialize = function() {
			var context = this;

			this.dialogue_box.change(function(e) {
				context.accept_upload_image(context, e);
			});

			photo_editor.find('#tutor_profile_area .tutor_overlay, #tutor_pp_option>div:last-child').click(function() {
				context.toggle_profile_pic_action();
			});

			// Upload new
			photo_editor.find('.tutor_cover_uploader').click(function() {
				context.open_dialogue_box('cover_photo');
			});
			photo_editor.find('.tutor_pp_uploader').click(function() {
				context.open_dialogue_box('profile_photo');
			});

			// Delete existing
			photo_editor.find('.tutor_cover_deleter').click(function() {
				context.update_preview('cover_photo', null);
			});
			photo_editor.find('.tutor_pp_deleter').click(function() {
				context.update_preview('profile_photo', null);
			});
		};
	};

	var photo_editor = $('#tutor_profile_cover_photo_editor');
	photo_editor.length > 0 ? new PhotoEditor(photo_editor).initialize() : 0;

	// Save profile settings with ajax
	$('.tutor-profile-settings-save').click(function(e) {
		e.preventDefault();

		var btn = $(this);
		var form = btn.closest('form');
		var data = form.serializeObject();
		let phone = document.querySelector('[name=phone_number]');
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
				btn.addClass('tutor-updating-message');
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
				btn.removeClass('tutor-updating-message');
			},
		});
	});
});

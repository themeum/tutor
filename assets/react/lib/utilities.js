window.jQuery(document).ready(function($) {
	const { __ } = wp.i18n;

	// Copy text
	$(document).on('click', '.tutor-copy-text', function(e) {
		// Prevent default action
		e.stopImmediatePropagation();
		e.preventDefault();

		// Get the text
		let text = $(this).data('text');

		// Create input to place texts in
		var $temp = $('<input>');
		$('body').append($temp);

		$temp.val(text).select();

		document.execCommand('copy');
		$temp.remove();

		tutor_toast(__('Copied!', 'tutor'), text, 'success');
	});

	// Ajax action
	$(document).on('click', '.tutor-list-ajax-action', function(e) {
		if (!e.detail || e.detail == 1) {
			e.preventDefault();

			let $that = $(this);
			let modal = $that.closest('.tutor-modal');
			let buttonContent = $that.html();
			let prompt = $(this).data('prompt');
			let del = $(this).data('delete_element_id');
			let redirect = $(this).data('redirect_to');
			var data = $(this).data('request_data') || {};
			typeof data == 'string' ? (data = JSON.parse(data)) : 0;

			if (prompt && !window.confirm(prompt)) {
				return;
			}

			$.ajax({
				url: _tutorobject.ajaxurl,
				type: 'POST',
				data: data,
				beforeSend: function() {
					$that
						.text(__('Deleting...', 'tutor'))
						.attr('disabled', 'disabled')
						.addClass('is-loading');
				},
				success: function(data) {
					if (data.success) {
						if (del) {
							$('#' + del).fadeOut(function() {
								$(this).remove();
							});
						}

						if (redirect !== undefined) {
							window.location.assign(redirect);
						}
						return;
					}

					let { message = __('Something Went Wrong!', 'tutor') } = data.data || {};
					tutor_toast(__('Error!', 'tutor'), message, 'error');
				},
				error: function() {
					tutor_toast(__('Error!', 'tutor'), __('Something Went Wrong!', 'tutor'), 'error');
				},
				complete: function() {
					$that
						.html(buttonContent)
						.removeAttr('disabled')
						.removeClass('is-loading');

					if (modal.length !== 0) {
						$('body').removeClass('tutor-modal-open');
						modal.removeClass('tutor-is-active');
					}
				},
			});
		}
	});

	// Textarea auto height
	$(document).on('input', '.tutor-form-control-auto-height', function() {
		this.style.height = 'auto';
		this.style.height = this.scrollHeight + 'px';
	});
	$('.tutor-form-control-auto-height').trigger('input');

	// Prevent number input out of range
	$(document).on(
		'input',
		'input.tutor-form-control[type="number"], input.tutor-form-number-verify[type="number"]',
		function() {
			var value = $(this).val(); 
			if (value == '') {
				$(this).val('');
				return;
			}
			// Allow only 2 decimal places.
			if (value.includes('.')) {
				var decimal = String(value).split('.')[1].length;
				console.log( decimal);
				if (decimal > 2) {
					$(this).val(parseFloat(value).toFixed(2));
				}
			}
		},
	);

	// Open location on dropdoqn change
	$(document).on('change', '.tutor-select-redirector', function() {
		let url = $(this).val();
		window.location.assign(url);
	});

	/**
	 * Toggle switch button handler.
	 *
	 * @since 1.0.0
	 */
	const toggleChange = document.querySelectorAll('.tutor-form-toggle-input');
	toggleChange.forEach((element) => {
		element.addEventListener('change', (e) => {
			let check_value = element.previousElementSibling;
			if (check_value) {
				check_value.value == 'on' ? (check_value.value = 'off') : (check_value.value = 'on');
			}
		});
	});
});

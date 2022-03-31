import { get_response_message } from '../../helper/response';

window.jQuery(document).ready(($) => {
	const { __ } = wp.i18n;

	/**
	 * Tutor ajax login
	 *
	 * @since v.1.6.3
	 */
	$(document).on('submit', '#tutor-login-form', function(e) {
		e.preventDefault();

		var form = $(this);
		var button = form.find('button');
		var error_container = form.find('.tutor-login-error');

		var form_data = $(this).serializeObject();
		form_data.action = 'tutor_user_login';

		$.ajax({
			url: _tutorobject.ajaxurl,
			type: 'POST',
			data: form_data,
			beforeSend: () => {
				button.addClass('is-loading');
				error_container.empty();
			},
			success: function(response) {
				if (response.success) {
					location.assign(response.data.redirect_to);
					return;
				}

				var error_message = (response.data || {}).message || __('Invalid username or password!', 'tutor');
				error_container.html(`
                    <div class="tutor-alert tutor-warning tutor-mt-28">
                        <div class="tutor-alert-text">
                            <span class="tutor-alert-icon tutor-icon-34 tutor-icon-circle-info tutor-mr-12"></span>
                            <span>
                                ${error_message}
                            </span>
                        </div>
                    </div>
                `);
			},
			error: () => {
				tutor_toast(__('Error!', 'tutor'), get_response_message(), 'error');
			},
			complete: () => {
				button.removeClass('is-loading');
			},
		});
	});
});

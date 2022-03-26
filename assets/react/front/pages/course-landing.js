window.jQuery(document).ready(($) => {
	const { __ } = window.wp.i18n;
    /**
     * Retake course
     *
     * @since v1.9.5
     */
    $('.tutor-course-retake-button').prop('disabled', false).click(function (e) {
        e.preventDefault();
		var url = $(this).attr('href');
		var course_id = $(this).data('course_id');
		
        var data = {
            title: __('Override Previous Progress', 'tutor'),
            description: __('Before continue, please decide whether to keep progress or reset.', 'tutor'),
            buttons: {
                reset: {
                    title: __('Reset Data', 'tutor'),
                    class: 'tutor-btn tutor-is-outline tutor-is-default',

                    callback: function (button) {

                        $.ajax({
                            url: window._tutorobject.ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'tutor_reset_course_progress',
                                course_id: course_id,
                            },
                            beforeSend: () => {
                                button.prop('disabled', true).addClass('tutor-updating-message');
                            },
                            success: function (response) {
                                if (response.success) {
                                    window.location.assign(response.data.redirect_to);
                                } else {
                                    alert((response.data || {}).message || __('Something went wrong', 'tutor'));
                                }
                            },
                            complete: function () {
                                button.prop('disabled', false).removeClass('tutor-updating-message');
                            }
                        });
                    }
                },
                keep: {
                    title: __('Keep Data', 'tutor'),
                    class: 'tutor-btn',
                    callback: function () {
                        window.location.assign(url);
                    }
                }
            }
        };

        new window.tutor_popup($, 'icon-gear', 40).popup(data);
    });
});

readyState_complete(() => {
    let loadingSpinner = document.querySelector('.tutor-video-player .loading-spinner');
    if (null !== loadingSpinner) {
        loadingSpinner.remove();
    }
});

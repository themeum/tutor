window.jQuery(document).ready($ => {

    const {__} = window.wp.i18n;

    /**
     * Retake course
     * 
     * @since v1.9.5
     */
     $('.tutor-course-retake-button').click(function(e) {
        e.preventDefault();

        var button = $(this);
        var url = button.attr('href');
        var course_id = button.data('course_id');

        var popup;

        var data = {
            title: __('Override Previous Progress', 'tutor'),
            description : __('Before continue, please decide whether to keep progress or reset.', 'tutor'),
            buttons : {
                reset: {
                    title: __('Reset Data', 'tutor'),
                    class: 'tutor-btn tutor-is-outline tutor-is-default',

                    callback: function() {

                        var button = popup.find('.tutor-button-secondary');
                        button.prop('disabled', true).append('<img style="margin-left: 7px" src="'+ window._tutorobject.loading_icon_url +'"/>');

                        $.ajax({
                            url: window._tutorobject.ajaxurl,
                            type: 'POST',
                            data: {action: 'tutor_reset_course_progress', course_id: course_id},
                            success: function(response) {
                                if(response.success) {
                                    window.location.assign(response.data.redirect_to);
                                } else {
                                    alert((response.data || {}).message || __('Something went wrong', 'tutor'));
                                }
                            },
                            complete: function() {
                                button.prop('disabled', false).find('img').remove();
                            }
                        });
                    }
                },
                keep: {
                    title: __('Keep Data', 'tutor'),
                    class: 'tutor-btn',
                    callback: function() {
                        window.location.assign(url);
                    }
                }
            } 
        };

        popup = new window.tutor_popup($, 'icon-gear', 40).popup(data);
    });
});
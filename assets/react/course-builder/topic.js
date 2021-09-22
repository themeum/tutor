window.jQuery(document).ready(function($){

    $(document).on('click', '#tutor-add-topic-btn', function (e) {
        e.preventDefault();
        var $that = $(this);
        var container = $that.closest('.tutor-metabox-add-topics');
        var form_data = container.find('input, textarea').serializeObject();
        form_data.action = 'tutor_add_course_topic';

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : form_data,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    $('#tutor-course-content-wrap').html(data.data.course_contents);
                    container.find('input[type!="hidden"], textarea').each(function () {
                        $(this).val('');
                    });
                    container.removeClass('tutor-is-active');
                    enable_sorting_topic_lesson();
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Confirmation for deleting Topic
     */
     $(document).on('click', '.topic-delete-btn a', function(e){
        var topic_id = $(this).attr('data-topic-id');

        if ( ! confirm( __( 'Are you sure to delete?', 'tutor' ) )){
            e.preventDefault();
        }
    });

    $(document).on('click', '.tutor-expand-all-topic', function (e) {
        e.preventDefault();
        $('.tutor-topics-body').slideDown();
        $('.expand-collapse-wrap i').removeClass('tutor-icon-light-down').addClass('tutor-icon-light-up');
    });

    $(document).on('click', '.tutor-collapse-all-topic', function (e) {
        e.preventDefault();
        $('.tutor-topics-body').slideUp();
        $('.expand-collapse-wrap i').removeClass('tutor-icon-light-up').addClass('tutor-icon-light-down');
    });

    $(document).on('click', '.topic-inner-title, .expand-collapse-wrap', function (e) {
        e.preventDefault();
        var $that = $(this);
        $that.closest('.tutor-topics-wrap').find('.tutor-topics-body').slideToggle();
        $that.closest('.tutor-topics-wrap').find('.expand-collapse-wrap i').toggleClass('tutor-icon-light-down tutor-icon-light-up');
    });
});
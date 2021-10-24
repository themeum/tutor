window.jQuery(document).ready(function($){

    const {__} = wp.i18n;

    $(document).on('click', '#tutor-add-topic-btn', function (e) {
        e.preventDefault();
        var $that = $(this);
        var container = $that.closest('.tutor-modal');
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
     $(document).on('click', '.tutor-topics-wrap .topic-delete-btn', function(e){
         var $that = $(this);
        var container = $(this).closest('.tutor-topics-wrap');
        var topic_id = container.attr('data-topic-id');

        if ( ! confirm( __( 'Are you sure to delete the topic?', 'tutor' ) )){
            return;
        }

        $.ajax({
            url: window._tutorobject.ajaxurl,
            type: 'POST',
            data: {
                action: 'tutor_delete_topic',
                topic_id
            },
            beforeSend: function() {
                $that.addClass('tutor-updating-message');
            },
            success: function(data) {
                // To Do: Load updated topic list here
                if(data.success) {
                    container.remove();
                    return;
                }
                
                tutor_toast('Error!', (data.data || {}).message || __('Something Went Wrong', 'tutor'), 'error');
            }, 
            complete: function() {
                $that.removeClass('tutor-updating-message');
            }
        })
    });

    $(document).on('click', '.topic-inner-title, .expand-collapse-wrap', function (e) {
        e.preventDefault();
        
        var wrapper = $(this).closest('.tutor-topics-wrap');
        wrapper.find('.tutor-topics-body').slideToggle();
        wrapper.find('.expand-collapse-wrap').toggleClass('is-expanded').find('i').toggleClass('tutor-icon-light-down tutor-icon-light-up');
    });
});
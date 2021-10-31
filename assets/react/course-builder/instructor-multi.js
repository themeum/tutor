window.jQuery(document).ready($=>{
    const {__} = wp.i18n;
    
    var search_container = $('#tutor_course_instructor_modal .tutor-search-result');
    var course_id = $('#tutor_course_instructor_modal').data('course_id');
    let search_timeout;

    // Search on input
    $(document).on('input', '#tutor_course_instructor_modal input[type="text"]', function() {

        var ajax_call=()=>{
            search_timeout = undefined;

            var search_terms = ($(this).val() || '').trim();
            
            // Clear result if no keyword
            if(!search_terms) {
                search_container.empty();
                return;
            }

            // Ajax request
            $.ajax({
                url: _tutorobject.ajaxurl,
                type: 'POST',
                data: {
                    course_id, 
                    search_terms, 
                    action: 'tutor_course_instructor_search'
                },
                success: function(resp) {
                    search_container.html((resp.data || {}).output);
                }
            });
        }

        if(search_timeout) {
            clearTimeout(search_timeout);
        }
        search_timeout = setTimeout(ajax_call, 600);
    });

    $(document).on('click', '.add_instructor_to_course_btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var $modal = $('.tutor-modal-wrap');
        var course_id = $('#post_ID').val();
        
        var data = $modal.find('input').serializeObject();
        data.course_id = course_id;
        data.action = 'tutor_add_instructors_to_course';

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : data,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    $('.tutor-course-available-instructors').html(data.data.output);
                    $('.tutor-modal-wrap').removeClass('show');
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    $(document).on('click', '.tutor-instructor-delete-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var course_id = $('#post_ID').val();
        var instructor_id = $that.closest('.added-instructor-item').attr('data-instructor-id');

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {
                course_id,
                instructor_id,
                action : 'detach_instructor_from_course'
            },
            beforeSend: ()=>$that.addClass('tutor-updating-message'),
            complete: ()=>$that.removeClass('tutor-updating-message'),
            success: function (data) {
                if (data.success){
                    $that.closest('.added-instructor-item').remove();
                    return;
                }

                tutor_toast('Error!', get_response_message(data), 'error');
            }
        });
    });
})
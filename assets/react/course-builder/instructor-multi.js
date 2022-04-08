window.jQuery(document).ready($=>{
    const {__} = wp.i18n;
    
    var search_container = $('#tutor_course_instructor_modal .tutor-search-result');
    var shortlist_container = $('#tutor_course_instructor_modal .tutor-selected-result');
    var course_id = $('#tutor_course_instructor_modal').data('course_id');
    let search_timeout;

    var search_method=(user_id, callback)=>{
        var ajax_call=()=>{
            search_timeout = undefined;

            var input = $('#tutor_course_instructor_modal input[type="text"]');
            var search_terms = (input.val() || '').trim();
            
            // Clear result if no keyword
            if(!search_terms) {
                search_container.empty();
                return;
            }

            var shortlisted = []; 
            shortlist_container.find('[data-instructor-id]').each(function(){
                shortlisted.push($(this).data('instructor-id'));
            });
            (user_id && !isNaN(user_id)) ? shortlisted.push(user_id) : 0;
            
            // Ajax request
            $.ajax({
                url: _tutorobject.ajaxurl,
                type: 'POST',
                data: {
                    course_id, 
                    search_terms, 
                    shortlisted: shortlisted,
                    action: 'tutor_course_instructor_search'
                },
                beforeSend:()=>{
                    if(!callback){
                        // Don't show if click on add. Then add loading icon should appear
                        search_container.html('');
                        search_container.addClass('is-loading');
                        // search_container.html('<div class="tutor-text-center"><span class="is-loading"></span></div>');
                    }
                },
                success: function(resp) {
                    const {search_result, shortlisted, shortlisted_count} = resp.data || {};

                    search_container.removeClass('is-loading');
                    
                    search_container.html(search_result);
                    shortlist_container.html(shortlisted);

                    let btn_disabled = shortlisted_count ? false : true;
                    $('.add_instructor_to_course_btn').prop('disabled', btn_disabled);

                    callback ? callback() : 0;
                }
            });
        }

        if(search_timeout) {
            clearTimeout(search_timeout);
        }
        search_timeout = setTimeout(ajax_call, 350);
    }

    // Search/Click input
    $(document).on('input', '#tutor_course_instructor_modal input[type="text"]', search_method);
    $(document).on('focus', '#tutor_course_instructor_modal input[type="text"]', function(){
        search_container.show();
    });

    // Shortlist on plus click
    $(document).on('click', '#tutor_course_instructor_modal .tutor-shortlist-instructor', function() {
        $(this).addClass('is-loading');
        search_method($(this).closest('[data-user_id]').data('user_id'), ()=>{
            search_container.hide();
        });
    }); 

    // Remove from shortlist
    $(document).on('click', '#tutor_course_instructor_modal .tutor-selected-result .instructor-control a', function(){
        $(this).closest('.added-instructor-item').fadeOut(function(){
            $(this).remove();
        });
    });

    // Add instructor to course from shortlist
    $(document).on('click', '.add_instructor_to_course_btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var course_id = $('#tutor_course_instructor_modal').data('course_id');
        
        var shortlisted = []; 
        shortlist_container.find('[data-instructor-id]').each(function(){
            shortlisted.push($(this).data('instructor-id'));
        });

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {
                course_id,
                tutor_instructor_ids: shortlisted,
                action: 'tutor_add_instructors_to_course'
            },
            beforeSend: function () {
                $that.addClass('is-loading');
            },
            success: function (data) {
                if (data.success){

                    // remove search content 
                    search_container.empty();
                    shortlist_container.empty();

                    // Hide the modal
                    $('#tutor_course_instructor_modal').removeClass('tutor-is-active');
                    $('body').removeClass('tutor-modal-open');

                    // Show the result in course editor
                    $('.tutor-course-instructors-metabox-wrap').parent().html(data.data.output);
                    $('.tutor-modal-wrap').removeClass('show');
                    return;
                }

                tutor_toast('Error!', get_response_message(data), 'error');
            },
            complete: function () {
                $that.removeClass('is-loading');
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
            beforeSend: ()=>$that.addClass('is-loading'),
            complete: ()=>$that.removeClass('is-loading'),
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
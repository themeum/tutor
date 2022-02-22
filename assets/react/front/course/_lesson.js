window.jQuery(document).ready($=>{
    $(document).on('click', '.tutor-single-course-lesson-comments button[type="submit"]', function(e){
        e.preventDefault();

        let btn = $(this);
        let form = btn.closest('form');
        let data = form.serialize();

        console.log(data);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: data,
            beforeSend: function(){
                btn.addClass('tutor-updating-message').prop('disabled', true);
            },
            success: function(){
                // Replicate pagination click
                let wrapper = btn.closest('.tutor-pagination-wrapper-replacable').find('[data-tutor_pagination_ajax]');
                let {course_id, current_page_num} = wrapper.data('tutor_pagination_ajax');
                let page_num = btn.hasClass('tutor-lesson-comment-reply') ? current_page_num : 1; // New comment opens first page
                
                wrapper
                    .attr('data-tutor_pagination_ajax', '{"action":"tutor_single_course_lesson_load_more","course_id":'+course_id+'}')
                    .append('<a class="page-numbers" style="display:none" id="tutor_lesson_load_temp" href="'+_tutorobject.home_url+'?current_page='+page_num+'"></a>')
                    .find('a#tutor_lesson_load_temp').trigger('click');
            },
            error: function(e){
                alert('Something went wrong!');
                btn.removeClass('tutor-updating-message').prop('disabled', false);
            }
        });
    });
});
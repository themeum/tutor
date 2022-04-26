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
                btn.addClass('is-loading').prop('disabled', true);
            },
            success: function(){
                // Replicate pagination click
                let replacable = btn.closest('.tutor-pagination-wrapper-replacable');
                let wrapper = replacable.find('[data-tutor_pagination_ajax]');
                let lesson_id = replacable.data('lesson_id');
                let {current_page_num=1} = wrapper.length ? wrapper.data('tutor_pagination_ajax') : {};
                let page_num = btn.hasClass('tutor-lesson-comment-reply') ? current_page_num : 1; // New comment opens first page
                
                // Create wrapper if not available
                if(!wrapper.length) {
                    let rand_string = 'tutor_id_' + new Date().getTime();
                    replacable.append('<div data-tutor_pagination_ajax="" id="'+rand_string+'"></div>');
                    wrapper = $('#'+rand_string);
                }
                
                wrapper
                    .attr('data-tutor_pagination_ajax', '{"action":"tutor_single_course_lesson_load_more","lesson_id":'+lesson_id+'}')
                    .append('<a class="page-numbers" style="display:none" id="tutor_lesson_load_temp" href="'+_tutorobject.home_url+'?current_page='+page_num+'"></a>')
                    .find('a#tutor_lesson_load_temp')
                    .trigger('click');
            },
            error: function(e){
                alert('Something went wrong!');
                btn.removeClass('is-loading').prop('disabled', false);
            }
        });
    });
});
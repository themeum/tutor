import { get_response_message } from "../helper/response";

window.jQuery(document).ready($=>{
    const {__} = wp.i18n;

    // Change badge
    $(document).on('click', '.tutor-qna-badges [data-action]', function(){
        let qna_action = $(this).data('action');
        let question_id = $(this).closest('[data-question_id]').data('question_id');
        let button = $(this);

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: {
                question_id, 
                qna_action, 
                action: 'tutor_qna_single_action'
            },
            beforeSend:()=>{
                button.addClass('tutor-updating-message');
            },
            success: resp=>{
                if(!resp.success) {
                    tutor_toast('Error!', get_response_message(resp), 'error');
                    return;
                }

                button.attr('data-value', resp.data.new_value).data('value', new_value);
            },
            complete:()=>{
                button.removeClass('tutor-updating-message');
            }
        });
    });

    // Save/update question/reply
    $(document).on('click', '.tutor-qa-reply button', function(){
        let button      = $(this);
        let form        = button.closest('.tutor-qa-reply');

        let question_id = button.closest('[data-question_id]').data('question_id');
        let course_id   = button.closest('[data-course_id]').data('course_id');
        let context     = button.closest('[data-context]').data('context');
        let answer      = form.find('textarea').val();

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: {
                course_id,
                question_id,
                context,
                answer,
                action: 'tutor_qna_create_update'
            },
            beforeSend: () =>{
                button.addClass('tutor-updating-message');
            },
            success: resp => {
                if(!resp.success) {
                    tutor_toast('Error!', get_response_message(resp), 'error');
                    return;
                }

                // Append content
                if(question_id) {
                    $('.tutor-qna-single-question').filter('[data-question_id="'+question_id+'"]').replaceWith(resp.data.html);
                } else {
                    $('.tutor-qna-single-question').eq(0).before(resp.data.html);
                }
            },
            complete: () =>{
                button.removeClass('tutor-updating-message');
            }
        })
    });

    $(document).on('click', '.tutor-toggle-reply span', function(){
        $(this).closest('.tutor-qna-chat').nextAll().toggle();
        $(this).closest('.tutor-qna-single-wrapper').find('.tutor-qa-reply').toggle();
    });
});
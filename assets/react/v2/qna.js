import { get_response_message } from "../helper/response";

window.jQuery(document).ready($=>{
    const {__} = wp.i18n;

    $(document).on('click', '.tutor-qna-single-wrapper .tutor-qa-sticky-bar [data-action]', function(){
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

    $(document).on('click', '.tutor-qa-reply button', function(){
        let question_id = $(this).closest('[data-question_id]').data('question_id');
        let answer = $(this).parent().find('textarea').val();

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: {
                question_id,
                answer,
                action: 'tutor_place_answer'
            }
        })
    });
});
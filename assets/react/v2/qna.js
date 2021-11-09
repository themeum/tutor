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
                console.log(resp);
            },
            complete:()=>{
                button.removeClass('tutor-updating-message');
            }
        });
    });
});
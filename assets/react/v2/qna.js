import { get_response_message } from "../helper/response";

window.jQuery(document).ready($=>{
    const {__} = wp.i18n;

    // Change view as mode at frontend dashboard
    $('.tutor-dashboard-qna-vew-as input[type="checkbox"]').prop('disabled', false);
    $(document).on('change', '.tutor-dashboard-qna-vew-as input[type="checkbox"]', function() {
        var is_instructor = $(this).prop('checked');

        $(this).prop('disabled', true);
        window.location.replace($(this).data(is_instructor ? 'as_instructor_url' : 'as_student_url'));
    });

    // Change badge
    $(document).on('click', '.tutor-qna-badges-wrapper [data-action]', function(e){
        e.preventDefault();
        var $that = $(this);
        
        if($that.hasClass('is-loading')) {
            return;
        }

        let row = $(this).closest('tr');
        let qna_action = $(this).data('action');
        let question_id = $(this).closest('[data-question_id]').data('question_id');
        let button = $(this);
        let context = button.closest('[data-qna_context]').data('qna_context');

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: {
                question_id,
                qna_action,
                context,
                action: 'tutor_qna_single_action'
            },
            beforeSend:() => {
                $that.addClass('is-loading');
            },
            success: resp=>{
                if(!resp.success) {
                    tutor_toast('Error!', get_response_message(resp), 'error');
                    return;
                }

                const {new_value} = resp.data;
                if(button.data('state-class-0')) {

                    // Get toggle class
                    var remove_class = button.data( new_value==1 ? 'state-class-0' : 'state-class-1' );
                    var add_class = button.data( new_value==1 ? 'state-class-1' : 'state-class-0' );

                    var class_element = button.data('state-class-selector') ? button.find(button.data('state-class-selector')) : button;
                    class_element.removeClass(remove_class).addClass(add_class);
                    // Toggle active class
                    class_element[new_value==1 ? 'addClass' : 'removeClass']('active');
                }

                if(button.data('state-text-0')) {

                    // Get toggle text
                    var new_text = button.data( new_value==1 ? 'state-text-1' : 'state-text-0' );

                    var text_element = button.data('state-text-selector') ? button.find(button.data('state-text-selector')) : button;
                    text_element.text(new_text);
                }

                // Update read unread
                if (qna_action == 'archived') {
                    location.reload();
                }
                if(qna_action=='read') {
                    let method = new_value==0 ? 'removeClass' : 'addClass';
                    row.find('.tutor-qna-question-col')[method]('is-read');
                }
            },
            complete:()=>{
                $that.removeClass('is-loading');
            }
        });
    });

    $(document).on('click', '#sidebar-qna-tab-content .tutor-qa-new a.sidebar-ask-new-qna-btn', function(e) {
        $('.tutor-quesanswer-askquestion').addClass('tutor-quesanswer-askquestion-expand');
        $('#sidebar-qna-tab-content').css({
            'height' : 'calc(100% - 140px)'
        });
    })

    $(document).on('click', '#sidebar-qna-tab-content .tutor-qa-new .sidebar-ask-new-qna-cancel-btn', function(e) {
        $('.tutor-quesanswer-askquestion').removeClass('tutor-quesanswer-askquestion-expand');
        $('#sidebar-qna-tab-content').css({
            'height' : 'calc(100% - 60px)'
        });
    })

    // Save/update question/reply
    $(document).on('click', '.tutor-qa-reply button.tutor-btn, .tutor-qa-new button.sidebar-ask-new-qna-submit-btn', function(e){
        let button      = $(this);
        let currentEditor = '';
        const closestWrapper = e.target.closest('.tutor-qna-reply-editor');
        if (_tutorobject.tutor_pro_url && tinymce) {
            // Current editor id
            currentEditor = closestWrapper.querySelector('.tmce-active').getAttribute('id').split('-')[1];
        }
        let form        = button.closest('[data-question_id]');

        let question_id = button.closest('[data-question_id]').data('question_id');
        let course_id   = button.closest('[data-course_id]').data('course_id');
        let context     = button.closest('[data-context]').data('context');
        let answer      = '' !== currentEditor ? tinymce.get(currentEditor).getContent() : form.find('textarea').val();

        let back_url    = $(this).data('back_url');

        const btnInnerHtml = button.html().trim();

        /**
         * Warning alert
         * 
         * @since v2.1.0
         */
        if (_tutorobject.tutor_pro_url && currentEditor !== '') {
            let tinyMCEContent = tinymce.get(currentEditor).getContent();
            if (tinyMCEContent === '') {
                tutor_toast('Warning!', __( 'Empty Content not Allowed', 'tutor'), 'error');
                return;
            }
        } else {
            if (answer === '') {
                tutor_toast('Warning!', __( 'Empty Content not Allowed', 'tutor'), 'error');
                return;
            }
        }
        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data: {
                course_id,
                question_id,
                context,
                answer,
                back_url,
                action: 'tutor_qna_create_update'
            },
            beforeSend: () =>{
                button.addClass('is-loading');
            },
            success: resp => {
                const {editor_id} = resp.data;
                if(!resp.success) {
                    tutor_toast('Error!', get_response_message(resp), 'error');
                    return;
                }

                // Append content
                if(question_id) {
                    $('.tutor-qna-single-question').filter('[data-question_id="'+question_id+'"]').replaceWith(resp.data.html);
                } else {
                    $('.tutor-empty-state-wrapper').remove();
                    $('.tutor-qna-single-question').eq(0).before(resp.data.html);
                }
                //on successful reply make the textarea empty
                if ($("#sidebar-qna-tab-content .tutor-quesanswer-askquestion textarea")) {
                    $("#sidebar-qna-tab-content .tutor-quesanswer-askquestion textarea").val('');
                }
                
                if (_tutorobject.tutor_pro_url && tinymce && undefined !== editor_id) {
                    // Clear editor content.
                    tinymce.get(currentEditor).setContent('');
                    
                    // Reinitialize new added question/reply editor.
                    tinymce.execCommand('mceRemoveEditor', false, editor_id);
                    tinymce.execCommand('mceAddEditor', false, editor_id);

                    // Highlight code snippets
                    $('.tutor-qna-single-question pre').each(function () {
                        let el = $(this),
                            fallback = 'javascript',
                            lang = el.attr('class').trim().replace('language-', '') || fallback,
                            highlighted = null;
                
                        if (Prism) {
                            try {
                                highlighted = Prism.highlight(el.text(), Prism.languages[lang], lang);
                            } catch (error) {
                                highlighted = Prism.highlight(el.text(), Prism.languages[fallback], fallback);
                            }
                
                            highlighted ? el.html(highlighted) : null
                        }
                    });

                } else {
                    // Clear question & reply textarea.
                    if ($(".tutor-quesanswer-askquestion textarea")) {
                        $(".tutor-quesanswer-askquestion textarea").val('');
                    }
                    if (closestWrapper.find('textarea').length) {
                        closestWrapper.find('textarea').val();
                    }
                }
            },
            complete: () =>{
                button.removeClass('is-loading');
            }
        })
    });

    $(document).on('click', '.tutor-toggle-reply span', function(){
        $(this).closest('.tutor-qna-chat').nextAll().toggle();
        $(this).closest('.tutor-qna-single-wrapper').find('.tutor-qa-reply').toggle();
    });
});
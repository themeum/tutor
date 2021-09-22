window.jQuery(document).ready(function($){

    const { __, _x, _n, _nx } = wp.i18n;
    
    // Create/edit assignment opener
    $(document).on('click', '.open-tutor-assignment-modal, .tutor-create-assignments-btn', function (e) {
        e.preventDefault();

        var $that = $(this);
        var assignment_id = $that.hasClass('tutor-create-assignments-btn') ? 0 : $that.attr('data-assignment-id');
        var topic_id = $that.closest('.tutor-topics-wrap').data('topic-id');
        var course_id = $('#post_ID').val();

        $.ajax({
            url: window._tutorobject.ajaxurl,
            type: 'POST',
            data: { 
                assignment_id: assignment_id, 
                topic_id: topic_id, 
                course_id: course_id, 
                action: 'tutor_load_assignments_builder_modal' 
            },
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('.tutor-assignment-modal-wrap .modal-container').html(data.data.output);
                $('.tutor-assignment-modal-wrap').addClass('tutor-is-active');
                $(document).trigger('assignment_modal_loaded');

                tinymce.init(tinyMCEPreInit.mceInit.course_description);
                tinymce.execCommand('mceRemoveEditor', false, 'tutor_assignments_modal_editor');
                tinyMCE.execCommand('mceAddEditor', false, "tutor_assignments_modal_editor");
            },
            complete: function () {
                quicktags({ id: "tutor_assignments_modal_editor" });
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Update Assignment Data
     */
     $(document).on( 'click', '.update_assignment_modal_btn',  function( event ){
        event.preventDefault();

        var $that = $(this);
        var content;
        var inputid = 'tutor_assignments_modal_editor';
        var editor = tinyMCE.get(inputid);
        if (editor) {
            content = editor.getContent();
        } else {
            content = $('#'+inputid).val();
        }
        
        var form_data = $(this).closest('.tutor-modal').find('form.tutor_assignment_modal_form').serializeObject();
        form_data.assignment_content = content;
        
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
                    enable_sorting_topic_lesson();

                    //Close the modal
                    $('.tutor-assignment-modal-wrap').removeClass('tutor-is-active');
                    
                    tutor_toast(__('Success', 'tutor'), __('Assignment Updated', 'tutor'), 'success');
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });
});
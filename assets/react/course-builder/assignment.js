window.jQuery(document).ready(function($){

    const { __, _x, _n, _nx } = wp.i18n;
    
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
        
        var form_data = $(this).closest('form').serializeObject();
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
                    $('.tutor-lesson-modal-wrap').removeClass('show');
                    
                    tutor_toast(__('Assignment Updated', 'tutor'), $that.data('toast_success_message'), 'success');
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });
})
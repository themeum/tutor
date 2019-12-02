jQuery(document).ready(function($){
    'use strict';

    /**
     *
     * @type {jQuery}
     *
     * Course builder auto draft save
     *
     * @since v.1.3.4
     */
    var tutor_course_builder = $('input[name="tutor_action"]').val();
    if (tutor_course_builder === 'tutor_add_course_builder'){
        setInterval(auto_draft_save_course_builder, 30000);
    }

    function auto_draft_save_course_builder(){
        var form_data = $('form#tutor-frontend-course-builder').serialize();
        $.ajax({
            //url : _tutorobject.ajaxurl,
            type : 'POST',
            data : form_data+'&tutor_ajax_action=tutor_course_builder_draft_save',
            beforeSend: function () {
                $('.tutor-dashboard-builder-draft-btn span').text('Saving...');
            },
            success: function (data) {

            },
            complete: function () {
                $('.tutor-dashboard-builder-draft-btn span').text('Save');
            }
        });
    }

});
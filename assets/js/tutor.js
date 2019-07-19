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
        //setInterval(auto_draft_save_course_builder, 5000);
    }

    function auto_draft_save_course_builder(){

        var form_data = $('form#tutor-frontend-course-builder').serialize();

        //frontend_course_builder_auto_draft_save
        //form_data = JSON.parse(JSON.stringify(form_data));

        $.ajax({
            //url : _tutorobject.ajaxurl,
            url : 'http://10.0.1.28/lms/dev/dashboard/create-course/?course_ID=1341',
            type : 'POST',
            data : form_data+'&tutor_ajax_action=course_builder_save',
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
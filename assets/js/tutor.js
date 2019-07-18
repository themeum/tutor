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

        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {topic_id : topic_id, course_id : course_id, action: 'tutor_auto_draft_save'},
            beforeSend: function () {

            },
            success: function (data) {

            },
            complete: function () {

            }
        });


    }






});
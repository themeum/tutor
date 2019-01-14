jQuery(document).ready(function($){
    'use strict';

    $(document).on('click', '.tutor-rating-delete-link', function (e) {
        e.preventDefault();

        var $that= $(this);
        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {review_id : $that.attr('data-rating-id'), action : 'tutor_review_delete' },
            beforeSend: function () {
                $that.addClass('updating-message');
            },
            success: function (data) {
                if (data.success){
                    $that.closest('tr').remove();
                }
            },
            complete: function () {
                $that.removeClass('updating-message');
            }
        });
    });
    
    $(document).on('click', '.tutor-quiz-attempt-delete-btn', function (e) {
        e.preventDefault();

        var $that= $(this);

        console.log( $that.attr('data-attempt-id'));


        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {attempt_id : $that.attr('data-attempt-id'), action : 'treport_quiz_atttempt_delete' },
            beforeSend: function () {
                $that.addClass('updating-message');
            },
            success: function (data) {
                if (data.success){
                    $that.closest('tr').remove();
                }
            },
            complete: function () {
                $that.removeClass('updating-message');
            }
        });
    });

    /**
     * Datepicker initiate
     */
    if (jQuery.datepicker){
        $( ".tutor_report_datepicker" ).datepicker({"dateFormat" : 'yy-mm-dd'});
    }


});

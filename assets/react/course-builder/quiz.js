window.jQuery(document).ready(function($){

    /**
     * Create new quiz
     */

     $(document).on('click', '.quiz-modal-btn-first-step', function(e){
        e.preventDefault();

        var $that = $(this);
        var $quizTitle = $('[name="quiz_title"]');
        var quiz_title = $quizTitle.val();
        var quiz_description = $('[name="quiz_description"]').val();

        if ( ! quiz_title){
            $quizTitle.closest('.tutor-quiz-builder-group').find('.quiz_form_msg').html('Please enter quiz title');
            return;
        }else{
            $quizTitle.closest('.tutor-quiz-builder-group').find('.quiz_form_msg').html('');
        }

        var course_id = $('#post_ID').val();
        var topic_id = $that.closest('.tutor-modal-wrap').attr('quiz-for-post-id');

        if ($('#tutor_quiz_builder_quiz_id').length) {
            /**
             *
             * @type {jQuery}
             *
             * if quiz id exists, we are sending it to update quiz
             */

            var quiz_id = $('#tutor_quiz_builder_quiz_id').val();
            $.ajax({
                url : window._tutorobject.ajaxurl,
                type : 'POST',
                data : {quiz_title:quiz_title, quiz_description: quiz_description, quiz_id : quiz_id, topic_id : topic_id, action: 'tutor_quiz_builder_quiz_update'},
                beforeSend: function () {
                    $that.addClass('tutor-updating-message');
                },
                success: function (data) {
                    $('#tutor-quiz-'+quiz_id).html(data.data.output_quiz_row);
                    $('#tutor-quiz-modal-tab-items-wrap a[href="#quiz-builder-tab-questions"]').trigger('click');

                    tutor_slider_init();
                },
                complete: function () {
                    $that.removeClass('tutor-updating-message');
                }
            });

            return;
        }

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {quiz_title:quiz_title, quiz_description: quiz_description, course_id : course_id, topic_id : topic_id, action: 'tutor_create_quiz_and_load_modal'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('.tutor-quiz-builder-modal-wrap .modal-container').html(data.data.output);
                $('#tutor-topics-'+topic_id+' .tutor-lessons').append(data.data.output_quiz_row);
                $('#tutor-quiz-modal-tab-items-wrap a[href="#quiz-builder-tab-questions"]').trigger('click');

                tutor_slider_init();

                $(document).trigger('quiz_modal_loaded', {topic_id : topic_id, course_id : course_id});
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });

    });

});
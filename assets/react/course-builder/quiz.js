window.jQuery(document).ready(function($){

    // Quiz modal next click
    $(document).on('click', '.tutor-quiz-builder-modal-wrap [data-action="next"]', function(e){

        var container = $(this).closest('.tutor-modal');
        var quiz_title = container.find('[name="quiz_title"]').val();
        var topic_id = container.find('[name="current_topic_id_for_quiz"]').val();
        var course_id;

        switch($(this).closest('.tutor-modal').attr('data-target')) {

            // Save quiz title and description
            case 'quiz-builder-tab-quiz-info' : 
                // 
        }
        var $that = $(this);
        var quiz_description = $('[name="quiz_description"]').val();

        var course_id = $('#post_ID').val();

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
                data : {
                    quiz_title:quiz_title, 
                    quiz_description: quiz_description, 
                    quiz_id : quiz_id, 
                    topic_id : topic_id, 
                    action: 'tutor_quiz_builder_quiz_update'
                },
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
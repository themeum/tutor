
/**
 * Add option disable when don't need to add an option
 * 
 * @since 1.9.7
 */
 var disableAddoption=function() {
    const selected_question_type      = document.querySelector(".tutor_select_value_holder").value;
    const question_answers            = document.getElementById("tutor_quiz_question_answers");
    const question_answer_form        = document.getElementById("tutor_quiz_question_answer_form");
    const add_question_answer_option  = document.querySelector(".add_question_answers_option");

    const addDisabledClass = (elem) => {
        if ( !elem.classList.contains("disabled") ) {
            elem.classList.add('disabled');
        }
    }

    const removeDisabledClass = (elem) => {
        if ( elem.classList.contains("disabled") ) {
            elem.classList.remove('disabled');
        }
    }

    //dont need add option for open_ended & short_answer
    if ( selected_question_type === 'open_ended' || selected_question_type === 'short_answer' ) {
        addDisabledClass(add_question_answer_option);
    } else if ( selected_question_type === 'true_false' || selected_question_type === 'fill_in_the_blank' ) {
        //if already have options then dont need to show add option
        if ( question_answer_form.hasChildNodes() || question_answers.hasChildNodes() ) {
            addDisabledClass(add_question_answer_option);
        } else {
            removeDisabledClass(add_question_answer_option);
        }
    } else {
        //if other question type then remove disabled
        removeDisabledClass(add_question_answer_option);
    }
}

window.jQuery(document).ready(function($){

    const {__} = wp.i18n;

    // TAB switching
    var step_switch=function(modal, go_next, clear_next) {
        var element = modal.find('.tutor-modal-steps');
        var current = element.find('li[data-tab="'+modal.attr('data-target')+'"]');
        var next = current.next();
        var prev = current.prev();

        if(!go_next) {
            var new_tab = prev.data('tab');
            prev.length ? modal.attr('data-target', new_tab) : 0;
            clear_next ? element.find('li[data-tab="'+new_tab+'"]').nextAll().removeClass('tutor-is-completed') : 0;
            return;
        }

        if(next.length) {
            next.addClass('tutor-is-completed');
            modal.attr('data-target', next.data('tab'));
            return true;
        }

        tutor_toast(__('Success', 'tutor'), __('Quiz Updated'), 'success');
        modal.removeClass('tutor-is-active');
        return null;
    }

    // Slider initiator
    var tutor_slider_init=function(){
        $('.tutor-field-slider').each(function(){
            var $slider = $(this);
            var $input = $slider.closest('.tutor-field-type-slider').find('input[type="hidden"]');
            var $showVal = $slider.closest('.tutor-field-type-slider').find('.tutor-field-type-slider-value');
            var min = parseFloat($slider.closest('.tutor-field-type-slider').attr('data-min'));
            var max = parseFloat($slider.closest('.tutor-field-type-slider').attr('data-max'));

            $slider.slider({
                range: "max",
                min: min,
                max: max,
                value: $input.val(),
                slide: function( event, ui ) {
                    $showVal.text(ui.value);
                    $input.val(ui.value);
                }
            });
        });
    }

    function tutor_save_sorting_quiz_questions_order(){
        var questions = {};
        $('.quiz-builder-question-wrap').each(function(index, item){
            var $question = $(this);
            var question_id = parseInt($question.attr('data-question-id'), 10);
            questions[index] = question_id;
        });

        $.ajax({
            url : window._tutorobject.ajaxurl, 
            type : 'POST',
            data : {
                sorted_question_ids : questions, 
                action: 'tutor_quiz_question_sorting'
            },
        });
    }

    // Sort quiz question
    function enable_quiz_questions_sorting(){
        if (jQuery().sortable) {
            $(".quiz-builder-questions-wrap").sortable({
                handle: ".question-sorting",
                start: function (e, ui) {
                    ui.placeholder.css('visibility', 'visible');
                },
                stop: function (e, ui) {
                    tutor_save_sorting_quiz_questions_order();
                },
            });
        }
    }

    /**
     * Save answer sorting placement
     *
     * @since v.1.0.0
     */
     function enable_quiz_answer_sorting(){
        if (jQuery().sortable) {
            $("#tutor_quiz_question_answers").sortable({
                handle: ".tutor-quiz-answer-sort-icon",
                start: function (e, ui) {
                    ui.placeholder.css('visibility', 'visible');
                },
                stop: function (e, ui) {
                    tutor_save_sorting_quiz_answer_order();
                },
            });
        }
    }

    function tutor_save_sorting_quiz_answer_order(){
        var answers = {};
        $('.tutor-quiz-answer-wrap').each(function(index, item){
            var $answer = $(this);
            var answer_id = parseInt($answer.attr('data-answer-id'), 10);
            answers[index] = answer_id;
        });

        $.ajax({url : window._tutorobject.ajaxurl, type : 'POST',
            data : {sorted_answer_ids : answers, action: 'tutor_quiz_answer_sorting'},
        });
    }

    function tutor_select(){
        var obj = {
            init : function(){
                $(document).on('click', '.question-type-select .tutor-select-option', function(e){
                    e.preventDefault();

                    var $that = $(this);
                    if ($that.attr('data-is-pro') !== 'true') {
                        var $html = $that.html().trim();
                        $that.closest('.question-type-select').find('.select-header .lead-option').html($html);
                        $that.closest('.question-type-select').find('.select-header input.tutor_select_value_holder').val($that.attr('data-value')).trigger('change');
                        $that.closest('.tutor-select-options').hide();

                        disableAddoption();
                    }else{
                        alert('Tutor Pro version required');
                    }
                });
                $(document).on('click', '.question-type-select .select-header', function(e){
                    e.preventDefault();
                   
                    var $that = $(this);
                    $that.closest('.question-type-select').find('.tutor-select-options').slideToggle();
                });

                this.setValue();
                this.hideOnOutSideClick();
            },
            setValue : function(){
                $('.question-type-select').each(function(){
                    var $that = $(this);
                    var $option = $that.find('.tutor-select-option');

                    if ($option.length){
                        $option.each(function(){
                            var $thisOption = $(this);

                            if ($thisOption.attr('data-selected') === 'selected'){
                                var $html = $thisOption.html().trim();
                                $thisOption.closest('.question-type-select').find('.select-header .lead-option').html($html);
                                $thisOption.closest('.question-type-select').find('.select-header input.tutor_select_value_holder').val($thisOption.attr('data-value'));
                            }
                        });
                    }
                });
            },
            hideOnOutSideClick : function(){
                $(document).mouseup(function(e) {
                    var $option_wrap = $(".tutor-select-options");
                    if ( ! $(e.target).closest('.select-header').length && !$option_wrap.is(e.target) && $option_wrap.has(e.target).length === 0) {
                        $option_wrap.hide();
                    }
                });
            },
            reInit : function(){
                this.setValue();
            }
        };

        return obj;
    }

    tutor_select().init();
    tutor_slider_init();

    // Create/Edit quiz opener
    $(document).on('click', '.tutor-add-quiz-btn, .open-tutor-quiz-modal, .back-to-quiz-questions-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var step_1 = $(this).hasClass('open-tutor-quiz-modal');
        var modal = $('.tutor-modal.tutor-quiz-builder-modal-wrap');
        var quiz_id = $that.hasClass('tutor-add-quiz-btn') ? 0 : $that.attr('data-quiz-id');
        var topic_id = $that.closest('.tutor-topics-wrap').data('topic-id');
        var course_id = $('#post_ID').val();

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {
                quiz_id : quiz_id, 
                topic_id : topic_id, 
                course_id : course_id, 
                action: 'tutor_load_quiz_builder_modal'
            },
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('.tutor-quiz-builder-modal-wrap').addClass('tutor-is-active');
                $('.tutor-quiz-builder-modal-wrap .modal-container').html(data.data.output);
                $('.tutor-quiz-builder-modal-wrap').attr('data-quiz-id', quiz_id).attr('quiz-for-post-id', topic_id).addClass('show');
                
                modal.removeClass('tutor-has-question-from');

                if(step_1) {
                    step_switch(modal, false, true);
                    step_switch(modal, false, true);
                }
                
                $(document).trigger('quiz_modal_loaded', {
                    quiz_id : quiz_id, 
                    topic_id : topic_id, 
                    course_id : course_id
                });

                tutor_slider_init();
                enable_quiz_questions_sorting();
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    // Quiz modal next click
    $(document).on('click', '.tutor-quiz-builder-modal-wrap button', function(e){

        // DOM findar
        var btn = $(this);
        var modal = btn.closest('.tutor-modal');
        var current_tab = modal.attr('data-target');
        var action = $(this).data('action');
        
        if(action=='back') {
            step_switch(modal, false);
            return;
        } else if(action!='next') {
            return;
        }

        // Quiz meta data
        var course_id = $('#post_ID').val();
        var topic_id = modal.find('[name="topic_id"]').val();
        var quiz_id = modal.find('[name="quiz_id"]').val();

        if(current_tab=='quiz-builder-tab-quiz-info' || current_tab=='quiz-builder-tab-settings') {

            // Save quiz info. Title and description
            var quiz_title = modal.find('[name="quiz_title"]').val();
            var quiz_description = modal.find('[name="quiz_description"]').val();

            var settings = modal.find('#quiz-builder-tab-settings :input, #quiz-builder-tab-advanced-options :input').serializeObject();
    
            $.ajax({
                url : window._tutorobject.ajaxurl,
                type : 'POST',
                data : {
                    ...settings,
                    quiz_title      : quiz_title, 
                    quiz_description: quiz_description, 
                    course_id       : course_id,
                    quiz_id         : quiz_id, 
                    topic_id        : topic_id, 
                    action          : 'tutor_quiz_save',
                },
                beforeSend: function () {
                    btn.addClass('tutor-updating-message');
                },
                success: function (data) {
                    console.log(quiz_id, quiz_id!=0);

                    if(quiz_id && quiz_id!=0) {
                        // Update if exists already
                        $('#tutor-quiz-'+quiz_id).replaceWith(data.data.output_quiz_row);
                        console.log($('#tutor-quiz-'+quiz_id))
                    } else {
                        // Otherwise create new row
                        $('#tutor-topics-'+topic_id+' .tutor-lessons').append(data.data.output_quiz_row);
                        console.log($('#tutor-topics-'+topic_id+' .tutor-lessons'))
                    }

                    // Update modal content
                    $('.tutor-quiz-builder-modal-wrap .modal-container').html(data.data.output);
                    $(document).trigger('quiz_modal_loaded', {topic_id : topic_id, course_id : course_id});

                    tutor_slider_init();
                    step_switch(modal, true);

                enable_quiz_questions_sorting();
                },
                complete: function () {
                    btn.removeClass('tutor-updating-message');
                }
            });
        } else if(current_tab=='quiz-builder-tab-questions') {
            step_switch(modal, true);
        }
    });

    // Add question
    $(document).on('click', '.tutor-quiz-open-question-form', function(e){
        e.preventDefault();

        var $that = $(this);
        var modal = $that.closest('.tutor-modal');
        var quiz_id = modal.find('[name="quiz_id"]').val();
        var topic_id = modal.find('[name="topic_id"]').val();
        var course_id = $('#post_ID').val();
        var question_id = $that.attr('data-question-id');

        var params = {
            quiz_id     : quiz_id, 
            topic_id    : topic_id,
            course_id   : course_id, 
            action      : 'tutor_quiz_builder_get_question_form'
        };

        if (question_id) {
            params.question_id = question_id;
        }

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : params,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                modal.find('.modal-container').html(data.data.output);
                modal.addClass('tutor-has-question-from');

                //Initializing Tutor Select
                // tutor_select().reInit();
                enable_quiz_answer_sorting();
                // disableAddoption();
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    // Trash question
    $(document).on('click', '.tutor-quiz-question-trash', function(e){
        e.preventDefault();

        var $that = $(this);
        var question_id = $that.attr('data-question-id');

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {question_id : question_id, action: 'tutor_quiz_builder_question_delete'},
            beforeSend: function () {
                $that.closest('.quiz-builder-question-wrap').remove();
            },
        });
    });

    /**
     * Get question answers option form to save multiple/single/true-false options
     *
     * @since v.1.0.0
     */
     $(document).on('click', '.add_question_answers_option:not(.disabled)', function(e){
        e.preventDefault();

        var $that = $(this);
        var question_id = $that.attr('data-question-id');
        
        var $formInput = $('#tutor-quiz-question-wrapper :input').serializeObject();
        
        $formInput.question_id = question_id;
        $formInput.action = 'tutor_quiz_add_question_answers';
        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : $formInput,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('#tutor_quiz_question_answer_form').html(data.data.output);
                disableAddoption();
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });


    /**
     * Quiz Question edit save and continue
     */
     $(document).on('click', '.quiz-modal-question-save-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var modal = $that.closest('.tutor-modal');
        var $formInput = $('#tutor-quiz-question-wrapper :input').serializeObject();
        $formInput.action = 'tutor_quiz_modal_update_question';
   
        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : $formInput,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    //ReOpen questions
                    modal.find('.back-to-quiz-questions-btn').trigger('click');
                } else {
                    if (typeof data.data !== 'undefined') {
                        $('#quiz_validation_msg_wrap').html(data.data.validation_msg);
                    }
                }
            },
            complete: function () {
                setTimeout(()=>$that.removeClass('tutor-updating-message'), 2000);
            }
        });
    });


    // Quiz question answer refresh
    $(document).on('refresh', '#tutor_quiz_question_answers', function(e){
        e.preventDefault();

        var $that = $(this);
        var question_id = $that.attr('data-question-id');
        var question_type = $('.tutor_select_value_holder').val();

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {question_id : question_id, question_type : question_type, action: 'tutor_quiz_builder_get_answers_by_question'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
                $('#tutor_quiz_question_answer_form').html('');
            },
            success: function (data) {
                if (data.success){
                    $that.html(data.data.output);
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * If change question type from quiz builder question
     *
     * @since v.1.0.0
     */
     $(document).on('change', 'input.tutor_select_value_holder', function(e) {
        $('.add_question_answers_option').trigger('click');
        $('#tutor_quiz_question_answers').trigger('refresh');
    });
});
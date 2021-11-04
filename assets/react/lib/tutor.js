import '../../../v2-library/_src/js/main';
import { get_response_message } from '../helper/response';

window.tutor_get_nonce_data=function(send_key_value) {

    var nonce_data = window._tutorobject || {};
    var nonce_key = nonce_data.nonce_key || '';
    var nonce_value = nonce_data[nonce_key] || '';

    if(send_key_value) {
        return {key:nonce_key, value:nonce_value};
    }

    return {[nonce_key]:nonce_value};
}

window.tutor_popup = function($, icon, padding) {

    var $this = this;
    var element; 

    this.popup_wrapper = function(wrapper_tag) {
        var img_tag = icon === '' ? '' : '<img class="tutor-pop-icon" src="'+window._tutorobject.tutor_url+'assets/images/'+icon+'.svg"/>';
        
        return '<'+wrapper_tag+' class="tutor-component-popup-container">\
            <div class="tutor-component-popup-'+padding+'">\
                <div class="tutor-component-content-container">'+img_tag+'</div>\
                <div class="tutor-component-button-container"></div>\
            </div>\
        </'+wrapper_tag+'>';
    }

    this.popup = function(data) {
        
        var title = data.title ? '<h3>'+data.title+'</h3>' : '';
        var description = data.description ? '<p>'+data.description+'</p>' : '';

        var buttons = Object.keys(data.buttons || {}).map(function(key) {
            var button = data.buttons[key];
            var button_id = button.id ? 'tutor-popup-'+button.id : ''; 
            return $('<button id="'+button_id+'" class="'+button.class+'">'+button.title+'</button>').click(button.callback);
        });

        element = $($this.popup_wrapper(data.wrapper_tag || 'div'));
        var content_wrapper = element.find('.tutor-component-content-container');

        content_wrapper.append(title);
        data.after_title ? content_wrapper.append(data.after_title) : 0;

        content_wrapper.append(description);
        data.after_description ? content_wrapper.append(data.after_description) : 0;

        // Assign close event on click black overlay
        element.click(function() {
            $(this).remove();
        }).children().click(function(e) {
            e.stopPropagation();
        });

        // Append action button
        for(var i=0; i<buttons.length; i++) {
            element.find('.tutor-component-button-container').append(buttons[i]);
        }
        
        $('body').append(element);

        return element;
    }

    return {popup: this.popup};
}

window.tutorDotLoader = (loaderType) => {
    return `    
    <div class="tutor-dot-loader ${loaderType ? loaderType: ''}">
        <span class="dot dot-1"></span>
        <span class="dot dot-2"></span>
        <span class="dot dot-3"></span>
        <span class="dot dot-4"></span>
    </div>`;
}

window.tutor_date_picker = () => {
    if (jQuery.datepicker){
        var format = _tutorobject.wp_date_format;
        if ( !format ) {
            format = "yy-mm-dd";
        }
        $( ".tutor_date_picker" ).datepicker({"dateFormat" : format});
    }
}

jQuery(document).ready(function($){
    'use strict';
    const { __, _x, _n, _nx } = wp.i18n;
    /**
     * Global date_picker selector 
     * 
     * @since 1.9.7
     */
    function load_date_picker() {
        if (jQuery.datepicker){
            var format = _tutorobject.wp_date_format;
            if ( !format ) {
                format = "yy-mm-dd";
            }
            $( ".tutor_date_picker" ).datepicker({"dateFormat" : format});
        }
        
        /** Disable typing on datePicker field */
        $(document).on('keydown', '.hasDatepicker, .tutor_date_picker', function( e ) {
            if ( e.keyCode !== 8 ) {
                e.preventDefault();
            }
        });
    };
    load_date_picker();
     

    /**
     * Video source tabs
     */

    if (jQuery().select2){
        $('.videosource_select2').select2({
            width: "100%",
            templateSelection: iformat,
            templateResult: iformat,
            allowHtml: true
        });
    }
    //videosource_select2

    function iformat(icon) {
        var originalOption = icon.element;
        return $('<span><i class="tutor-icon-' + $(originalOption).data('icon') + '"></i> ' + icon.text + '</span>');
    }

    /**
     * Course Builder
     *
     * @since v.1.3.4
     */

    $(document).on( 'click', '.tutor-course-thumbnail-upload-btn',  function( event ){
        event.preventDefault();
        var $that = $(this);
        var frame;
        if ( frame ) {
            frame.open();
            return;
        }
        frame = wp.media({
            title: __( 'Select or Upload Media Of Your Chosen Persuasion', 'tutor' ),
            button: {
                text: __( 'Use this media', 'tutor' )
            },
            multiple: false
        });
        frame.on( 'select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $that.closest('.tutor-thumbnail-wrap').find('.thumbnail-img').attr('src', attachment.url);
            $that.closest('.tutor-thumbnail-wrap').find('input').val(attachment.id);
            $('.tutor-course-thumbnail-delete-btn').show();
        });
        frame.open();
    });

    //Delete Thumbnail
    $(document).on( 'click', '.tutor-course-thumbnail-delete-btn',  function( event ){
        event.preventDefault();
        var $that = $(this);

        var placeholder_src = $that.closest('.tutor-thumbnail-wrap').find('.thumbnail-img').attr('data-placeholder-src');
        $that.closest('.tutor-thumbnail-wrap').find('.thumbnail-img').attr('src', placeholder_src);
        $that.closest('.tutor-thumbnail-wrap').find('input').val('');
        $('.tutor-course-thumbnail-delete-btn').hide();
    });

    $(document).on('change keyup', '.course-edit-topic-title-input', function (e) {
        e.preventDefault();
        $(this).closest('.tutor-topics-top').find('.topic-inner-title').html($(this).val());
    });

    /**
     * Delete Lesson from course builder
     */
    $(document).on('click', '.tutor-delete-lesson-btn', function(e){
        e.preventDefault();

        if( ! confirm( __( 'Are you sure to delete?', 'tutor' ) )){
            return;
        }

        var $that = $(this);
        var lesson_id = $that.attr('data-lesson-id');

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {lesson_id : lesson_id, action: 'tutor_delete_lesson_by_id'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    $that.closest('.course-content-item').remove();
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Quiz Builder Modal Tabs
     */
    $(document).on('click', '.tutor-quiz-modal-tab-item', function(e){
        e.preventDefault();

        var $that = $(this);

        var $quizTitle = $('[name="quiz_title"]');
        var quiz_title = $quizTitle.val();
        if ( ! quiz_title){
            $quizTitle.closest('.tutor-quiz-builder-form-row').find('.quiz_form_msg').html('<p class="quiz-form-warning">Please save the quiz' +
                ' first</p>');
            return;
        }else{
            $quizTitle.closest('.tutor-quiz-builder-form-row').find('.quiz_form_msg').html('');
        }

        var tabSelector = $that.attr('href');
        $('.quiz-builder-tab-container').hide();
        $(tabSelector).show();

        $('a.tutor-quiz-modal-tab-item').removeClass('active');
        $that.addClass('active');
    });

    /**
     * Get question answers option edit form
     *
     * @since v.1.0.0
     */
    $(document).on('click', '.tutor-quiz-answer-edit a', function(e){
        e.preventDefault();

        var $that = $(this);
        var answer_id = $that.closest('.tutor-quiz-answer-wrap').attr('data-answer-id');

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {answer_id : answer_id, action : 'tutor_quiz_edit_question_answer'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('#tutor_quiz_question_answer_form').html(data.data.output);
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Saving question answers options
     * Student should select the right answer at quiz attempts
     *
     * @since v.1.0.0
     */

    $(document).on('click', '#quiz-answer-save-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var $formInput = $('#tutor-quiz-question-wrapper :input').serializeObject();
        $formInput.action = 'tutor_save_quiz_answer_options';

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : $formInput,
            beforeSend: function () {
                $('#quiz_validation_msg_wrap').html("");
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('#tutor_quiz_question_answers').trigger('refresh');
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Updating Answer
     *
     * @since v.1.0.0
     */
    $(document).on('click', '#quiz-answer-edit-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var $formInput = $('#tutor-quiz-question-wrapper :input').serializeObject();
        $formInput.action = 'tutor_update_quiz_answer_options';

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : $formInput,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('#tutor_quiz_question_answers').trigger('refresh');
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    $(document).on('change', '.tutor-quiz-answers-mark-correct-wrap input', function(e){
        e.preventDefault();

        var $that = $(this);

        var answer_id = $that.val();
        var inputValue = 1;
        if ( ! $that.prop('checked')) {
            inputValue = 0;
        }

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {answer_id:answer_id, inputValue : inputValue, action : 'tutor_mark_answer_as_correct'},
        });
    });



    /**
     * Delete answer for a question in quiz builder
     *
     * @since v.1.0.0
     */

    $(document).on('click', '.tutor-quiz-answer-trash-wrap a.answer-trash-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var answer_id = $that.attr('data-answer-id');

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {answer_id : answer_id, action: 'tutor_quiz_builder_delete_answer'},
            beforeSend: function () {
                $that.closest('.tutor-quiz-answer-wrap').remove();
            },
        });
    });


    /**
     * Delete Quiz
     * @since v.1.0.0
     */

    $(document).on('click', '.tutor-delete-quiz-btn', function(e){
        e.preventDefault();

        if( ! confirm( __( 'Are you sure to delete?', 'tutor' ) )){
            return;
        }

        var $that = $(this);
        var quiz_id = $that.attr('data-quiz-id');

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {
                quiz_id : quiz_id, 
                action: 'tutor_delete_quiz_by_id'
            },
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function(resp) {
                const {data={}, success} = resp || {};
                const {message=__('Something Went Wrong!')} = data;

                if(success) {
                    $that.closest('.course-content-item').remove();
                    return;
                }

                tutor_toast('Error!', message, 'error');
            },
            complete:function() {
                $that.removeClass('tutor-updating-message')
            }
        });
    });

    $(document).on('click', '.tutor-media-upload-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var frame;
        if ( frame ) {
            frame.open();
            return;
        }
        frame = wp.media({
            title: __( 'Select or Upload Media Of Your Chosen Persuasion', 'tutor' ),
            button: {
                text: __( 'Use this media', 'tutor' )
            },
            multiple: false
        });
        frame.on( 'select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $that.html('<img src="'+attachment.url+'" alt="" />');
            $that.closest('.tutor-media-upload-wrap').find('input').val(attachment.id);
        });
        frame.open();
    });

    $(document).on('click', '.tutor-media-upload-trash', function(e){
        e.preventDefault();

        var $that = $(this);
        $that.closest('.tutor-media-upload-wrap').find('.tutor-media-upload-btn').html('<i class="tutor-icon-image1"></i>');
        $that.closest('.tutor-media-upload-wrap').find('input').val('');
    });

    $(document).on('click', '.settings-tabs-navs li', function(e){
        e.preventDefault();

        var $that = $(this);
        var data_target = $that.find('a').attr('data-target');
        var url = $that.find('a').attr('href');

        $that.addClass('active').siblings('li.active').removeClass('active');
        $('.settings-tab-wrap').removeClass('active').hide();
        $(data_target).addClass('active').show();

        window.history.pushState({}, '', url);
    });

    /**
     * Re init required
     * Modal Loaded...
     */

    $(document).on('lesson_modal_loaded quiz_modal_loaded assignment_modal_loaded', function(e, obj){
        if (jQuery().select2){
            $('.select2_multiselect').select2({
                dropdownCssClass:'increasezindex'
            });
        }
        load_date_picker();
    });
    
    /**
     * Tutor number validation
     *
     * @since v.1.6.3
     */
    $(document).on('keyup change', '.tutor-number-validation', function(e) {
        var input = $(this);
        var val = parseInt(input.val());
        var min = parseInt(input.attr('data-min'));
        var max = parseInt(input.attr('data-max'));
        if ( val < min )  { 
            input.val(min);
        } else if ( val > max ) {
            input.val(max);
        }
    });

    /*
    * @since v.1.6.4
    * Quiz Attempts Instructor Feedback 
    */
   $(document).on('click', '.tutor-instructor-feedback', function(e) {

    e.preventDefault();
    var $that = $(this);
    
    $.ajax({
        url : (window.ajaxurl || _tutorobject.ajaxurl),
        type : 'POST',
        data : {
            attempts_id: $that.data('attemptid'), 
            feedback: $('.tutor-instructor-feedback-content').val() , 
            action: 'tutor_instructor_feedback'
        },
        beforeSend: function () {
            $that.addClass('tutor-updating-message'); 
        },
        success: function (data) {
            if (data.success){
                $that.closest('.course-content-item').remove();
                tutor_toast(__('Success', 'tutor'), $that.data('toast_success_message'), 'success');
            }
        },
        complete: function () {
            $that.removeClass('tutor-updating-message');
        }
        });
    });

    //dropdown toggle
    $(document).click(function(){
        $(".tutor-dropdown").removeClass('show');
      });

      $(".tutor-dropdown").click(function(e){
        e.stopPropagation();
        if ( $('.tutor-dropdown').hasClass('show') ) {
            $('.tutor-dropdown').removeClass('show')
        }
        $(this).addClass('show');
      });

   /**
    * @since v.1.8.6
    * SUbmit form through ajax
    */
    $('.tutor-form-submit-through-ajax').submit(function(e) {
        e.preventDefault();

        var $that = $(this);
        var url = $(this).attr('action') || window.location.href;
        var type = $(this).attr('method') || 'GET';
        var data = $(this).serializeObject();

        $that.find('button').addClass('tutor-updating-message');

        $.ajax({
            url: url,
            type: type,
            data: data,
            success: function() {
                tutor_toast(__('Success', 'tutor'), $that.data('toast_success_message'), 'success');
            },
            complete: function () {
                $that.find('button').removeClass('tutor-updating-message');
            }
        });
    });

    /*
    * @since v.1.7.9
    * Send wp nonce to every ajax request
    */
    $.ajaxSetup({data : tutor_get_nonce_data()});
});

jQuery.fn.serializeObject = function()
{
   var values = {};
   var array = this.serializeArray();

   jQuery.each(array, function() {
       if (values[this.name]) {
           if (!values[this.name].push) {
               values[this.name] = [values[this.name]];
           }
           values[this.name].push(this.value || '');
       } else {
           values[this.name] = this.value || '';
       }
   });

   return values;
};

window.tutor_toast=function(title, description, type, is_left) {
    var tutor_ob = window._tutorobject || {};
    var asset = (tutor_ob.tutor_url || '') + 'assets/images/';

    if(!jQuery('.tutor-toast-parent').length) {
        jQuery('body').append('<div class="tutor-toast-parent '+(is_left ? 'tutor-toast-left' : '')+'"></div>');
    }

    var icons = {
        success : asset+'icon-check.svg',
        error: asset+'icon-cross.svg'
    }
    
    var content = jQuery('\
        <div>\
            <div>\
                <img src="'+icons[type]+'"/>\
            </div>\
            <div>\
                <div>\
                    <b>'+title+'</b>\
                    <span>'+description+'</span>\
                </div>\
            </div>\
            <div>\
                <i class="tutor-toast-close tutor-icon-line-cross"></i>\
            </div>\
        </div>');

    content.find('.tutor-toast-close').click(function() {
        content.remove();
    });

    jQuery('.tutor-toast-parent').append(content);

    setTimeout(function() {
        if(content) {
            content.fadeOut('fast', function() {
                jQuery(this).remove();
            });
        }
    }, 5000);
}
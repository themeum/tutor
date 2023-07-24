import codeSampleLang from "../lib/codesample-lang";

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
                $that.addClass('is-loading');
            },
            success: function (data) {
                $('.tutor-assignment-modal-wrap .tutor-modal-container').html(data.data.output);
                $('.tutor-assignment-modal-wrap').addClass('tutor-is-active');

                let editor_id = 'tutor_assignments_modal_editor',
                    editor_wrap_selector = '#wp-tutor_assignments_modal_editor-wrap',
                    tinymceConfig = tinyMCEPreInit.mceInit.tutor_assignment_editor_config;

                if ($(editor_wrap_selector).hasClass('html-active')) {
                    $(editor_wrap_selector).removeClass('html-active');
                }

				$(editor_wrap_selector).addClass('tmce-active');
                
                /**
				 * Add codesample plugin to support code snippet
				 *
				 * @since 2.0.9
				 */
				if (tinymceConfig && _tutorobject.tutor_pro_url) {
                    if (!tinymceConfig.plugins.includes('codesample')) {
                        tinymceConfig.plugins = `${tinymceConfig.plugins}, codesample`;
                        tinymceConfig.codesample_languages = codeSampleLang;
                        tinymceConfig.toolbar1 = `${tinymceConfig.toolbar1}, codesample`;
                    }
				}

                tinymceConfig.wpautop = false;
                tinymce.init(tinymceConfig);
                tinymce.execCommand('mceRemoveEditor', false, editor_id );
                tinyMCE.execCommand('mceAddEditor', false, editor_id );
                quicktags({ id: editor_id });

                window.dispatchEvent(new Event(_tutorobject.content_change_event));
                window.dispatchEvent(new CustomEvent('tutor_modal_shown', {detail: e.target}));
            },
            complete: function () {
                $that.removeClass('is-loading');
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
            content = editor.getContent({format: 'raw'});
        } else {
            content = $('#'+inputid).val();
        }
         
        // removing <br data-mce-bogus="1">
        if(content === '<p><br data-mce-bogus="1"></p>') {
            content = '';
        }
        
        var form_data = $(this).closest('.tutor-modal').find('form.tutor_assignment_modal_form').serializeObject();
        form_data.assignment_content = content;
        
        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : form_data,
            beforeSend: function () {
                $that.addClass('is-loading');
            },
            success: function (data) {
                if (data.success){
                    $('#tutor-course-content-wrap').html(data.data.course_contents);
                    enable_sorting_topic_lesson();

                    //Close the modal
                    $('.tutor-assignment-modal-wrap').removeClass('tutor-is-active');

                    // Trigger content change event
                    window.dispatchEvent(new Event(window._tutorobject.content_change_event));
                    
                    tutor_toast(__('Success', 'tutor'), __('Assignment Updated', 'tutor'), 'success');
                }
            },
            complete: function () {
                $that.removeClass('is-loading');
            }
        });
    });
});
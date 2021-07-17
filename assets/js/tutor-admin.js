jQuery(document).ready(function($){
    'use strict';

    const { __, _x, _n, _nx } = wp.i18n;
    /**
     * Color Picker
     * @since v.1.2.21
     */
    if (jQuery().wpColorPicker) {
        $('.tutor_colorpicker').wpColorPicker();
    }

    if (jQuery().select2){
        $('.tutor_select2').select2();
    }

    /**
     * Option Settings Nav Tab
     */
    $('.tutor-option-nav-tabs li a').click(function(e){
        e.preventDefault();
        var tab_page_id = $(this).attr('data-tab');
        $('.option-nav-item').removeClass('current');
        $(this).closest('li').addClass('current');
        $('.tutor-option-nav-page').hide();
        $(tab_page_id).addClass('current-page').show();
        window.history.pushState('obj', '', $(this).attr('href'));
    });

    $('#save_tutor_option').click(function (e) {
        e.preventDefault();
        $(this).closest('form').submit();
    });
    $('#tutor-option-form').submit(function(e){
        e.preventDefault();

        var $form = $(this);
        var data = $form.serializeObject();

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : data,
            beforeSend: function () {
                $form.find('.button').addClass('tutor-updating-message');
            },
            success: function (data) {
                data.success ? 
                    tutor_toast(__('Saved', 'tutor'), $form.data('toast_success_message'), 'success') : 
                    tutor_toast(__('Request Error', 'tutor'), __('Could not save', 'tutor'), 'error');
            },
            complete: function () {
                $form.find('.button').removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Withdraw nav tabs
     * @since v.1.1.2
     */
    $(document).on('click', '.withdraw-method-nav li a', function(e){
        e.preventDefault();
        var tab_page_id = $(this).attr('data-target-id');
        $('.withdraw-method-form-wrap').hide();
        $('#'+tab_page_id).show();
    });

    /**
     * End Withdraw nav tabs
     */

    /**
     * Don't move it to anywhere?
     */
    function enable_sorting_topic_lesson(){
        if (jQuery().sortable) {
            $(".course-contents").sortable({
                handle: ".course-move-handle",
                start: function (e, ui) {
                    ui.placeholder.css('visibility', 'visible');
                },
                stop: function (e, ui) {
                    tutor_sorting_topics_and_lesson();
                },
            });
            $(".tutor-lessons:not(.drop-lessons)").sortable({
                connectWith: ".tutor-lessons",
                items: "div.course-content-item",
                start: function (e, ui) {
                    ui.placeholder.css('visibility', 'visible');
                },
                stop: function (e, ui) {
                    tutor_sorting_topics_and_lesson();
                },
            });
        }
    }
    enable_sorting_topic_lesson();
    function tutor_sorting_topics_and_lesson(){
        var topics = {};
        $('.tutor-topics-wrap').each(function(index, item){
            var $topic = $(this);
            var topics_id = parseInt($topic.attr('id').match(/\d+/)[0], 10);
            var lessons = {};

            $topic.find('.course-content-item').each(function(lessonIndex, lessonItem){
                var $lesson = $(this);
                var lesson_id = parseInt($lesson.attr('id').match(/\d+/)[0], 10);

                lessons[lessonIndex] = lesson_id;
            });
            topics[index] = { 'topic_id' : topics_id, 'lesson_ids' : lessons };
        });
        $('#tutor_topics_lessons_sorting').val(JSON.stringify(topics));
    }

    /**
     * Lesson Update or Create Modal
     */
    $(document).on( 'click', '.update_lesson_modal_btn',  function( event ){
        event.preventDefault();

        var $that = $(this);
        var content;
        var inputid = 'tutor_lesson_modal_editor';
        var editor = tinyMCE.get(inputid);
        if (editor) {
            content = editor.getContent();
        } else {
            content = $('#'+inputid).val();
        }

        var form_data = $(this).closest('form').serializeObject();
        form_data.lesson_content = content;

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

                    tutor_toast(__('Lesson Updated', 'tutor'), $that.data('toast_success_message'), 'success');
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Lesson Video
     */
    $(document).on('change', '.tutor_lesson_video_source', function(e){
        var selector = $(this).val();
        $('[class^="video_source_wrap"]').hide();
        $('.video_source_wrap_'+selector).show();

        if (selector === 'html5'){
            $('.tutor-video-poster-field').show();
        } else{
            $('.tutor-video-poster-field').hide();
        }
    });

    $(document).on( 'click', '.video_source_wrap_html5 .video_upload_btn',  function( event ){
        event.preventDefault();

        var $that = $(this);
        var frame;
        // If the media frame already exists, reopen it.
        if ( frame ) {
            frame.open();
            return;
        }

        // Create a new media frame
        frame = wp.media({
            title: 'Select or Upload Media Of Your Chosen Persuasion',
            button: {
                text: 'Use this media'
            },
            library: { type: 'video' },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected in the media frame...
        frame.on( 'select', function() {
            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();
            $that.closest('.video_source_wrap_html5').find('span.video_media_id').data('video_url', attachment.url).text(attachment.id).trigger('paste').closest('p').show();
            $that.closest('.video_source_wrap_html5').find('input.input_source_video_id').val(attachment.id);
        });
        // Finally, open the modal on click
        frame.open();
    });

    $(document).on('click', 'a.tutor-delete-attachment', function(e){
        e.preventDefault();
        $(this).closest('.tutor-added-attachment').remove();
    });

    $(document).on('click', '.tutorUploadAttachmentBtn', function(e){
        e.preventDefault();

        var $that = $(this);
        var frame;
        // If the media frame already exists, reopen it.
        if ( frame ) {
            frame.open();
            return;
        }
        // Create a new media frame
        frame = wp.media({
            title: 'Select or Upload Media Of Your Chosen Persuasion',
            button: {
                text: 'Use this media'
            },
            multiple: true  // Set to true to allow multiple files to be selected
        });
        // When an image is selected in the media frame...
        frame.on( 'select', function() {
            // Get media attachment details from the frame state
            var attachments = frame.state().get('selection').toJSON();
            if (attachments.length){
                for (var i=0; i < attachments.length; i++){
                    var attachment = attachments[i];

                    var inputHtml = '<div class="tutor-added-attachment"><i class="tutor-icon-archive"></i> <a href="javascript:;" class="tutor-delete-attachment tutor-icon-line-cross"></a> <span> <a href="'+attachment.url+'">'+attachment.filename+'</a> </span><input type="hidden" name="tutor_attachments[]" value="'+attachment.id+'"></div>';
                    $that.closest('.tutor-lesson-attachments-metabox').find('.tutor-added-attachments-wrap').append(inputHtml);
                }
            }
        });
        // Finally, open the modal on click
        frame.open();
    });

    /**
     * Open Sidebar Menu
     */
    if (_tutorobject.open_tutor_admin_menu){
        var $adminMenu = $('#adminmenu');
        $adminMenu.find('[href="admin.php?page=tutor"]').closest('li.wp-has-submenu').addClass('wp-has-current-submenu');
        $adminMenu.find('[href="admin.php?page=tutor"]').closest('li.wp-has-submenu').find('a.wp-has-submenu').removeClass('wp-has-current-submenu').addClass('wp-has-current-submenu');
    }

    $(document).on('click', '.tutor-option-media-upload-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var frame;
        if ( frame ) {
            frame.open();
            return;
        }
        frame = wp.media({
            title: 'Select or Upload Media Of Your Chosen Persuasion',
            button: {
                text: 'Use this media'
            },
            multiple: false
        });
        frame.on( 'select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $that.closest('.option-media-wrap').find('.option-media-preview').html('<img src="'+attachment.url+'" alt="" />');
            $that.closest('.option-media-wrap').find('input').val(attachment.id);
            $that.closest('.option-media-wrap').find('.tutor-media-option-trash-btn').show();
        });
        frame.open();
    });

    /**
     * Remove option media
     * @since v.1.4.3
     */
    $(document).on('click', '.tutor-media-option-trash-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        $that.closest('.option-media-wrap').find('img').remove();
        $that.closest('.option-media-wrap').find('input').val('');
        $that.closest('.option-media-wrap').find('.tutor-media-option-trash-btn').hide();
    });


    $(document).on('change', '.tutor_addons_list_item', function(e) {
        var $that = $(this);

        var isEnable = $that.prop('checked') ? 1 : 0;
        var addonFieldName = $that.attr('name');

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {isEnable:isEnable, addonFieldName:addonFieldName, action : 'addon_enable_disable'},
            success: function (data) {
                if (data.success){
                    //Success
                }
            }
        });
    });

    /**
     * Add instructor
     * @since v.1.0.3
     */
    $(document).on('submit', '#new-instructor-form', function(e){
        e.preventDefault();

        var $that = $(this);
        var formData = $that.serializeObject();
        formData.action = 'tutor_add_instructor';

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : formData,
            success: function (data) {
                if (data.success){
                    $that.trigger("reset");
                    $('#form-response').html('<p class="tutor-status-approved-context">'+data.data.msg+'</p>');
                }else{
                    var errorMsg = '';

                    var errors = data.data.errors;
                    if (errors && Object.keys(errors).length){
                        $.each(data.data.errors, function( index, value ) {
                            if (isObject(value)){
                                $.each(value, function( key, value1 ) {
                                    errorMsg += '<p class="tutor-required-fields">'+value1[0]+'</p>';
                                });
                            } else{
                                errorMsg += '<p class="tutor-required-fields">'+value+'</p>';
                            }
                        });
                        $('#form-response').html(errorMsg);
                    }

                }
            }
        });
    });


    /**
     * Instructor block unblock action
     * @since v.1.5.3
     */

    $(document).on('click', 'a.instructor-action', function(e){
        e.preventDefault();

        var $that = $(this);
        var action = $that.attr('data-action');
        var instructor_id = $that.attr('data-instructor-id');
        
        var prompt_message = $that.attr('data-prompt-message');
        if(prompt_message && !confirm(prompt_message)){
            // Avoid Accidental CLick
            return;
        }

        var nonce_key = _tutorobject.nonce_key;
        var json_data = { instructor_id : instructor_id, action_name : action, action: 'instructor_approval_action'};
        json_data[nonce_key] = _tutorobject[nonce_key];

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : json_data,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                location.reload(true);
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    function isObject (value) {
        return value && typeof value === 'object' && value.constructor === Object;
    }

    /**
     * Tutor Assignments JS
     * @since v.1.3.3
     */
    $(document).on('click', '.tutor-create-assignments-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var topic_id = $(this).attr('data-topic-id');
        var course_id = $('#post_ID').val();

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {topic_id : topic_id, course_id : course_id, action: 'tutor_load_assignments_builder_modal'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('.tutor-lesson-modal-wrap .modal-container').html(data.data.output);
                $('.tutor-lesson-modal-wrap').attr('data-topic-id', topic_id).addClass('show');

                $(document).trigger('assignment_modal_loaded', {topic_id : topic_id, course_id : course_id});

                tinymce.init(tinyMCEPreInit.mceInit.tutor_editor_config);
                tinymce.execCommand( 'mceRemoveEditor', false, 'tutor_assignments_modal_editor' );
                tinyMCE.execCommand('mceAddEditor', false, "tutor_assignments_modal_editor");
            },
            complete: function () {
                quicktags({id : "tutor_assignments_modal_editor"});
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    $(document).on('click', '.open-tutor-assignment-modal', function(e){
        e.preventDefault();

        var $that = $(this);
        var assignment_id = $that.attr('data-assignment-id');
        var topic_id = $that.attr('data-topic-id');
        var course_id = $('#post_ID').val();

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {assignment_id : assignment_id, topic_id : topic_id, course_id : course_id, action: 'tutor_load_assignments_builder_modal'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('.tutor-lesson-modal-wrap .modal-container').html(data.data.output);
                $('.tutor-lesson-modal-wrap').attr({'data-assignment-id' : assignment_id, 'data-topic-id':topic_id}).addClass('show');

                $(document).trigger('assignment_modal_loaded', {assignment_id : assignment_id, topic_id : topic_id, course_id : course_id});

                tinymce.init(tinyMCEPreInit.mceInit.tutor_editor_config);
                tinymce.execCommand( 'mceRemoveEditor', false, 'tutor_assignments_modal_editor' );
                tinyMCE.execCommand('mceAddEditor', false, "tutor_assignments_modal_editor");
            },
            complete: function () {
                quicktags({id : "tutor_assignments_modal_editor"});
                $that.removeClass('tutor-updating-message');
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

    /**
     * Add Assignment
     */
    $(document).on( 'click', '.add-assignment-attachments',  function( event ){
        event.preventDefault();

        var $that = $(this);
        var frame;
        // If the media frame already exists, reopen it.
        if ( frame ) {
            frame.open();
            return;
        }

        // Create a new media frame
        frame = wp.media({
            title: 'Select or Upload Media Of Your Chosen Persuasion',
            button: {
                text: 'Use this media'
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected in the media frame...
        frame.on( 'select', function() {
            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();

            var  field_markup = '<div class="tutor-individual-attachment-file"><p class="attachment-file-name">'+attachment.filename+'</p><input type="hidden" name="tutor_assignment_attachments[]" value="'+attachment.id+'"><a href="javascript:;" class="remove-assignment-attachment-a text-muted"> &times; Remove</a></div>';

            $('#assignment-attached-file').append(field_markup);
            $that.closest('.video_source_wrap_html5').find('input').val(attachment.id);
        });
        // Finally, open the modal on click
        frame.open();
    });

    $(document).on( 'click', '.remove-assignment-attachment-a',  function( event ){
        event.preventDefault();
        $(this).closest('.tutor-individual-attachment-file').remove();
    });

    /**
     * Used for backend profile photo upload.
     */

    //tutor_video_poster_upload_btn
    $(document).on( 'click', '.tutor_video_poster_upload_btn',  function( event ){
        event.preventDefault();

        var $that = $(this);
        var frame;
        // If the media frame already exists, reopen it.
        if ( frame ) {
            frame.open();
            return;
        }

        // Create a new media frame
        frame = wp.media({
            title: 'Select or Upload Media Of Your Chosen Persuasion',
            button: {
                text: 'Use this media'
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected in the media frame...
        frame.on( 'select', function() {
            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();
            $that.closest('.tutor-video-poster-wrap').find('.video-poster-img').html('<img src="'+attachment.sizes.thumbnail.url+'" alt="" />');
            $that.closest('.tutor-video-poster-wrap').find('input').val(attachment.id);
        });
        // Finally, open the modal on click
        frame.open();
    });


    /**
     * Tutor Memberships toggle in Paid Membership Pro panel
     * @since v.1.3.6
     */

    $(document).on( 'change', '#tutor_pmpro_membership_model_select',  function( e ){
        e.preventDefault();

        var $that = $(this);

        if ($that.val() === 'category_wise_membership'){
            $('.membership_course_categories').show();
        } else{
            $('.membership_course_categories').hide();
        }
    });

    $(document).on( 'change', '#tutor_pmpro_membership_model_select',  function( e ){
        e.preventDefault();

        var $that = $(this);

        if ($that.val() === 'category_wise_membership'){
            $('.membership_course_categories').show();
        } else{
            $('.membership_course_categories').hide();
        }
    });

    // Require category selection
    $(document).on('submit', '.pmpro_admin form', function(e) {
        var form = $(this);

        if(!form.find('input[name="tutor_action"]').length) {
            // Level editor or tutor action not necessary
            return;
        }

        if(
            form.find('[name="tutor_pmpro_membership_model"]').val()=='category_wise_membership' && 
            !form.find('.membership_course_categories input:checked').length) {

            if(!confirm(__('Do you want to save without any category?', 'tutor'))) {
                e.preventDefault();
            }
        }
    });

    /**
     * Find user/student from select2
     * @since v.1.4.0
     */

    $('#select2_search_user_ajax').select2({
        allowClear: true,
        placeholder: "Search students",
        minimumInputLength: '1',
        escapeMarkup: function( m ) {
            return m;
        },
        ajax: {
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            dataType: 'json',
            delay:       1000,
            data: function( params ) {
                return {
                    term:     params.term,
                    action:   'tutor_json_search_students'
                };
            },
            processResults: function( data ) {
                var terms = [];
                if ( data ) {
                    $.each( data, function( id, text ) {
                        terms.push({
                            id: id,
                            text: text
                        });
                    });
                }
                return {
                    results: terms
                };

            },
            cache: true
        }
    });

    /**
     * Confirm Alert for deleting enrollments data
     *
     * @since v.1.4.0
     */
    $(document).on( 'click', 'table.enrolments .delete a',  function( e ){
        if (! confirm(__('Are you sure? it can not be undone.', 'tutor'))) {
            e.preventDefault();
        }
    });
    

    /**
     * Show hide is course public checkbox (backend dashboard editor)
     * 
     * @since  v.1.7.2
    */
    var price_type = $('#tutor-attach-product [name="tutor_course_price_type"]');
    if(price_type.length==0){
        $('#_tutor_is_course_public_meta_checkbox').show();
    }
    else{
        price_type.change(function(){
            if($(this).prop('checked')){
                var method = $(this).val()=='paid' ? 'hide' : 'show';
                $('#_tutor_is_course_public_meta_checkbox')[method]();
            }
        }).trigger('change');
    }    
    
    
    /**
     * Focus selected instructor layout in setting page
     * 
     * @since  v.1.7.5
    */
    $(document).on('click', '.instructor-layout-template', function(){
        $('.instructor-layout-template').removeClass('selected-template');
        $(this).addClass('selected-template');
    });


    
    /**
     * Programmatically open preview link. For some reason it's not working normally.
     * 
     * @since  v.1.7.9
    */
   $('#preview-action a.preview').click(function(e) {
        var href = $(this).attr('href');

        if(href) {
            e.preventDefault();
            window.open(href, '_blank');
        }
   });


   
});


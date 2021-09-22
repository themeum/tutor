(function($){
    window.enable_sorting_topic_lesson=function(){
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


    window.tutor_sorting_topics_and_lesson=function(){
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

})(window.jQuery);


window.jQuery(document).ready(function($){
    
    const { __, _x, _n, _nx } = wp.i18n;
    
    enable_sorting_topic_lesson();
    
    /**
     * Open Lesson Modal
     */
     $(document).on('click', '.open-tutor-lesson-modal', function(e){
        e.preventDefault();

        var $that = $(this);
        var lesson_id = $that.attr('data-lesson-id');
        var topic_id = $that.attr('data-topic-id');
        var course_id = $('#post_ID').val();

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {
                lesson_id : lesson_id, 
                topic_id : topic_id, 
                course_id : course_id, 
                action: 'tutor_load_edit_lesson_modal'
            },
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('.tutor-lesson-modal-wrap .modal-container').html(data.data.output);
                $('.tutor-lesson-modal-wrap').attr({'data-lesson-id' : lesson_id, 'data-topic-id':topic_id}).addClass('show');
                $('.tutor-lesson-modal-wrap').addClass('tutor-is-active');

                var tinymceConfig = tinyMCEPreInit.mceInit.tutor_editor_config;
                if ( ! tinymceConfig){
                    tinymceConfig = tinyMCEPreInit.mceInit.course_description;
                }
                tinymce.init(tinymceConfig);
                tinymce.execCommand( 'mceRemoveEditor', false, 'tutor_lesson_modal_editor' );
                tinyMCE.execCommand('mceAddEditor', false, "tutor_lesson_modal_editor");

                $(document).trigger('lesson_modal_loaded', {lesson_id : lesson_id, topic_id : topic_id, course_id : course_id});
            },
            complete: function () {
                quicktags({id : "tutor_lesson_modal_editor"});
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    // Video source 
    $(document).on('change', '.tutor_lesson_video_source', function(e){
        $(this).nextAll().hide().filter('.video_source_wrap_'+$(this).val()).show();
    });

    // Update lesson
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

        var form_data = $(this).closest('.tutor-modal').find('form').serializeObject();
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
                    $that.closest('.tutor-modal').removeClass('tutor-is-active');
                    
                    tutor_toast(__('Success', 'tutor'), __('Lesson Updated', 'tutor'), 'success');
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });


   /**
    * @since v.1.9.0
    * Parse and show video duration on link paste in lesson video 
    */
    var video_url_input = '.video_source_wrap_external_url input, .video_source_wrap_vimeo input, .video_source_wrap_youtube input, .video_source_wrap_html5, .video_source_upload_wrap_html5';
    var autofill_url_timeout;
    $('body').on('paste', video_url_input, function(e) {
        e.stopImmediatePropagation();

        var root = $(this).closest('.tutor-lesson-modal-wrap').find('.tutor-option-field-video-duration');
        var duration_label = root.find('label');
        var is_wp_media = $(this).hasClass('video_source_wrap_html5') || $(this).hasClass('video_source_upload_wrap_html5');
        var autofill_url = $(this).data('autofill_url');
        $(this).data('autofill_url', null);

        var video_url = is_wp_media ? $(this).find('span').data('video_url') : (autofill_url || e.originalEvent.clipboardData.getData('text')); 
        
        var toggle_loading = function(show) {

            if(!show) {
                duration_label.find('img').remove();
                return;
            }

            // Show loading icon
            if(duration_label.find('img').length==0) {
                duration_label.append(' <img src="'+window._tutorobject.loading_icon_url+'" style="display:inline-block"/>');
            }
        }

        var set_duration = function(sec_num) {
            var hours   = Math.floor(sec_num / 3600);
            var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
            var seconds = Math.round( sec_num - (hours * 3600) - (minutes * 60) );

            if (hours   < 10) {hours   = "0"+hours;}
            if (minutes < 10) {minutes = "0"+minutes;}
            if (seconds < 10) {seconds = "0"+seconds;}
    
            var fragments = [hours, minutes, seconds];
            var time_fields = root.find('input');
            for(var i=0; i<3; i++) {
                time_fields.eq(i).val(fragments[i]);
            }
        }
        
        var yt_to_seconds = function (duration) {
            var match = duration.match(/PT(\d+H)?(\d+M)?(\d+S)?/);
          
            match = match.slice(1).map(function(x) {
                if (x != null) {
                    return x.replace(/\D/, '');
                }
            });
          
            var hours = (parseInt(match[0]) || 0);
            var minutes = (parseInt(match[1]) || 0);
            var seconds = (parseInt(match[2]) || 0);
          
            return hours * 3600 + minutes * 60 + seconds;
        }

        if(is_wp_media || $(this).parent().hasClass('video_source_wrap_external_url')) {
            var player = document.createElement('video');
            player.addEventListener('loadedmetadata', function() {
                set_duration( player.duration );
                toggle_loading(false);
            });

            toggle_loading(true);
            player.src = video_url;

        } else if($(this).parent().hasClass('video_source_wrap_vimeo')) {

            var regExp = /^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/;
            var match = video_url.match(regExp);
            var video_id = match ? match[5] : null;

            if(video_id) {
                toggle_loading(true);

                $.getJSON('http://vimeo.com/api/v2/video/'+video_id+'/json', function(data) {
                    if(Array.isArray(data) && data[0] && data[0].duration!==undefined) {
                        set_duration(data[0].duration);
                    }
                    
                    toggle_loading(false);
                });
            }            
        } else if($(this).parent().hasClass('video_source_wrap_youtube')) {
            var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/;
            var match = video_url.match(regExp);
            var video_id = (match && match[7].length==11) ? match[7] : false;
            var api_key = $(this).data('youtube_api_key');

            if(video_id && api_key) {

                var result_url = 'https://www.googleapis.com/youtube/v3/videos?id='+video_id+'&key='+api_key+'&part=contentDetails';
                toggle_loading(true);

                $.getJSON(result_url, function(data) {
                    if(typeof data=='object' && data.items && data.items[0] && data.items[0].contentDetails && data.items[0].contentDetails.duration) {
                        set_duration( yt_to_seconds(data.items[0].contentDetails.duration) );
                    }

                    toggle_loading(false);
                });
            }
        }
    }).on('input', video_url_input, function() {

        if(autofill_url_timeout) {
            clearTimeout(autofill_url_timeout);
        }

        var $this = $(this);
        autofill_url_timeout = setTimeout(function() {
            var val = $this.val();
            val = val ? val.trim() : '';
            console.log('Trigger', val);
            val ? $this.data('autofill_url', val).trigger('paste') : 0;
        }, 700);
    });
});
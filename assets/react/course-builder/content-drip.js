window.jQuery(document).ready(function($){

    console.log('Content drip sc');

    // Update content drip data instantly on change.
    // So lesson, quiz, assignment modal can get data without pressing course update/publish
    $('#course_setting_content_drip, [name="_tutor_course_settings[content_drip_type]"]').change(function(){

        if($(this).attr('type')=='radio' && !$(this).prop('checked')) {
            return;
        }

        var val = $(this).attr('type')=='checkbox' ? ($(this).prop('checked') ? 1 : 0) : $(this).val();

        $.ajax({
            url: window._tutorobject.ajaxurl,
            type: 'POST',
            data: {
                [$(this).attr('name')]: val,
                course_id: $('#post_ID').val(),
                action: 'tutor_content_drip_state_update'
            }
        })
    });
});
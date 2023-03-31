window.jQuery(document).ready(function($){

    let dripCheckbox = $('#course_setting_content_drip')
    let dripOptions  = $('div.content-drip-options-wrapper')
    if ( dripCheckbox.is(':checked') === false ) {
        dripOptions.hide()
    }

    // Update content drip data instantly on change.
    // So lesson, quiz, assignment modal can get data without pressing course update/publish
    $('#course_setting_content_drip, [name="_tutor_course_settings[content_drip_type]"]').change(function(){

        if($(this).attr('type')=='radio' && !$(this).prop('checked')) {
            return;
        }

        var val = $(this).attr('type')=='checkbox' ? ($(this).prop('checked') ? 1 : 0) : $(this).val();

        let isChecked = $(this).is(':checked')
        isChecked ? dripOptions.show() : dripOptions.hide()

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
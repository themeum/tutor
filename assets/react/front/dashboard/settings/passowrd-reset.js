const { get_response_message } = require("../../../helper/response");

window.jQuery(document).ready($=>{
    $('.tutor-settings-pass-field [name="confirm_new_password"]').on('input', function(){
        let original = $('[name="new_password"]');
        let val = (original.val() || '').trim();
        let matched = val && $(this).val()===val;
        
        $(this).parent().find('.tutor-validation-icon')[matched ? 'show' : 'hide']();
    });

    $('.tutor-profile-password-reset').click(function(e){
        e.preventDefault();

        var btn = $(this);
        var form = btn.closest('form');
        var data = form.serializeObject();
        data.action = 'tutor_profile_password_reset';

        $.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            data,
            beforeSend:()=>{
                btn.addClass('is-loading');
            },
            success:resp=>{
                let {success} = resp;
                
                if(success) {
                    window.tutor_toast('Success', get_response_message(resp), 'success');
                    window.location.reload();
                } else {
                    window.tutor_toast('Error', get_response_message(resp), 'error');
                }
            },
            complete:()=>{
                btn.removeClass('is-loading');
            }
        })
    });
})
window.jQuery(document).ready($=>{
    $('.tutor-settings-pass-field [name="confirm_new_password"]').on('input', function(){
        let original = $('[name="new_password"]');
        let val = (original.val() || '').trim();
        let matched = val && $(this).val()===val;
        
        $(this).next()[matched ? 'show' : 'hide']();
    });
})
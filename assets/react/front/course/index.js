window.jQuery(document).ready($=>{

    // Login require on enrol purchase click
    $(document).on('click', '.tutor-enrol-require-auth', function(e){
        e.preventDefault();
        $('.tutor-login-modal').addClass('tutor-is-active');
    });
});
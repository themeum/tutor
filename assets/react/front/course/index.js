import './_login-reg';
import './_spotlight';
import './_wishlist';
import './_social-share';
import './_spotlight-quiz';

window.jQuery(document).ready($=>{
    // Login require on enrol purchase click
    $(document).on('click', '.tutor-course-entry-box-login button, .tutor-course-entry-box-login a, .tutor-open-login-modal', function(e){
        e.preventDefault();
        var login_url = $(this).data('login_url') || $(this).closest('.tutor-course-entry-box-login').data('login_url');

        if(login_url) {
            window.location.assign(login_url);
        } else {
            $('.tutor-login-modal').addClass('tutor-is-active');
        }
    });
});
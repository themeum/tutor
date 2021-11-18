import './_login-reg';
import './_spotlight';
import './_wishlist';

window.jQuery(document).ready($=>{

    // Login require on enrol purchase click
    $(document).on('click', '.tutor-course-entry-box-login button, .tutor-course-entry-box-login a, .tutor-open-login-modal', function(e){
        e.preventDefault();
        $('.tutor-login-modal').addClass('tutor-is-active');
    });
});
import './_spotlight';
import './_wishlist';
import './_social-share';
import './_spotlight-quiz';
import './_spotlight-quiz-timing';
import './_lesson';
import './_woocommerce';
import './_archive';

window.jQuery(document).ready($ => {
    // Login require on enrol purchase click
    $(document).on('click', '.tutor-course-entry-box-login button, .tutor-course-entry-box-login a, .tutor-open-login-modal', function (e) {
        e.preventDefault();
        var login_url = $(this).data('login_url') || $(this).closest('.tutor-course-entry-box-login').data('login_url');

        if (login_url) {
            window.location.assign(login_url);
        } else {
            $('.tutor-login-modal').addClass('tutor-is-active');
        }
    });
});

/**
 * Tutor password protected course.
 * 
 * @since v.3.0.0
 */
const passwordProtectedCourse = document.querySelector('.tutor-password-protected-course');
if (passwordProtectedCourse) {
    // Disable page scrolling when password protected course modal is active.
    const body = document.querySelector('body');
    body.style.overflow = 'hidden';

    // Hide and show password on checkbox click.
    const passwordInput = passwordProtectedCourse.querySelector('input[type="password"]');
    const checkbox = passwordProtectedCourse.querySelector('input[type="checkbox"]');

    checkbox.addEventListener('change', function () {
        if (checkbox.checked) {
            passwordInput.type = 'text';
        } else {
            passwordInput.type = 'password';
        }
    });
}

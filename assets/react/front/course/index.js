import './_archive';
import './_lesson';
import './_social-share';
import './_spotlight';
import './_spotlight-quiz';
import './_spotlight-quiz-timing';
import './_wishlist';
import './_woocommerce';

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

    /**
     * Replace UTC date time to local date time
     * 
     * @since v.3.3.0
     */
    function convertUTCTime() {
      const utcDateTimes = document.querySelectorAll('.tutor-utc-date-time');
      if (utcDateTimes.length > 0 && wp.date) {
        const settings = wp.date.getSettings();
        const dateFormat = settings.formats.date;
        const timeFormat = settings.formats.time;
        const format = `${dateFormat}, ${timeFormat}`;

        utcDateTimes.forEach((utcDateTime) => {
          try {
            const textContent = utcDateTime.textContent.trim();
            const localDateTime = new Date(`${textContent} UTC`);

            if (!isNaN(localDateTime)) {
              utcDateTime.textContent = wp.date.dateI18n(format, localDateTime, Intl.DateTimeFormat().resolvedOptions().timeZone);
            } else {
              console.warn(`Invalid UTC date: "${textContent}"`);
            }
          } catch (error) {
            console.log(error);
          }
        });
      }
    }

    convertUTCTime();

    window.addEventListener( 'tutor_content_changed_event', () => {
        convertUTCTime();
    })
});

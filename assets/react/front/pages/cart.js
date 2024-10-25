import ajaxHandler from "../../admin-dashboard/segments/filter";
import tutorFormData from "../../helper/tutor-formdata";
const { __ } = wp.i18n;

document.addEventListener('DOMContentLoaded', function () {
    const defaultErrorMessage = __('Something went wrong, please try again', 'tutor');

    // Add to cart functionalities
    document.addEventListener('click', async (e) => {
        const button = e.target.closest('.tutor-native-add-to-cart');
        if (button) {
            const formData = tutorFormData([{ action: 'tutor_add_course_to_cart', course_id: button.dataset.courseId }]);
            const isSinglePage = document.body.classList.contains('single-courses');

            try {
                button.setAttribute('disabled', 'disabled');
                button.classList.add('is-loading');

                const post = await ajaxHandler(formData);
				const { status_code, data, message = defaultErrorMessage } = await post.json();

                if (status_code === 201) {
                    tutor_toast(__('Success', 'tutor'), message, 'success');
                    const viewCartButton = `<a href="${data?.cart_page_url}" class="tutor-btn tutor-btn-outline-primary ${isSinglePage ? 'tutor-btn-lg tutor-btn-block' : 'tutor-btn-md'}">${__('View Cart', 'tutor')}</a>`
					button.parentElement.innerHTML = viewCartButton;

					// Create a custom event with cart count
					const cartEvent = new CustomEvent('tutorCartCount', {
						detail: { cart_count: data?.cart_count }
					});

					// Dispatch the custom cart event
					document.dispatchEvent(cartEvent);

                } else {
                    tutor_toast(__('Failed', 'tutor'), message, 'error');
                }
            } catch (error) {
                tutor_toast(__('Failed', 'tutor'), defaultErrorMessage, 'error');
            } finally {
                button.removeAttribute('disabled');
                button.classList.remove('is-loading');
            }
        }
    });

    // Remove course from card
    const tutorCartPage = document.querySelector('.tutor-cart-page');
    if (tutorCartPage) {
        document.addEventListener('click', async (e) => {
            const button = e.target.closest('.tutor-cart-remove-button');
            if (button) {
                const formData = tutorFormData([{ action: 'tutor_delete_course_from_cart', course_id: button.dataset.courseId }]);

                try {
                    button.setAttribute('disabled', 'disabled');
                    button.classList.add('is-loading');

                    const post = await ajaxHandler(formData);
                    const { status_code, data, message = defaultErrorMessage } = await post.json();
                    if (status_code === 200) {
                        document.querySelector('.tutor-cart-page-wrapper').parentElement.innerHTML = data;
                        tutor_toast(__('Success', 'tutor'), message, 'success');
                    } else {
                        tutor_toast(__('Failed', 'tutor'), message, 'error');
                    }
                } catch (error) {
                    tutor_toast(__('Failed', 'tutor'), defaultErrorMessage, 'error');
                } finally {
                    button.removeAttribute('disabled');
                    button.classList.remove('is-loading');
                }
            }
        });
    }
});

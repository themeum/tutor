import ajaxHandler from '../../helper/ajax-handler';
import tutorFormData from '../../helper/tutor-formdata';

document.addEventListener('DOMContentLoaded', function () {
	const { __ } = wp.i18n;
	const defaultErrorMessage = __('Something went wrong, please try again', 'tutor');

	// Add to cart functionalities
	document.addEventListener('click', async (e) => {
		const button = e.target.closest('.tutor-native-add-to-cart');
		if (button) {
			const formData = tutorFormData([
				{ action: 'tutor_add_course_to_cart', course_id: button.dataset.courseId },
			]);
			const isSinglePage = document.body.classList.contains('single-courses') || document.body.classList.contains('single-course-bundle');

			try {
				button.setAttribute('disabled', 'disabled');
				button.classList.add('is-loading');

				const post = await ajaxHandler(formData);
				const { status_code, data, message = defaultErrorMessage } = await post.json();

				if (status_code === 201) {
					tutor_toast(__('Success', 'tutor'), message, 'success');
					const viewCartButton = `<a data-cy="tutor-native-view-cart" href="${data?.cart_page_url ?? "#"}" class="tutor-btn tutor-btn-outline-primary ${isSinglePage ? 'tutor-btn-lg tutor-btn-block' : 'tutor-btn-md'} ${!data?.cart_page_url ? 'tutor-cart-page-not-configured' : ''}">${__('View Cart', 'tutor')}</a>`
					button.parentElement.innerHTML = viewCartButton;

					// Create a custom event with cart count
					const addToCartEvent = new CustomEvent('tutorAddToCartEvent', {
						detail: { cart_count: data?.cart_count }
					});

					// Dispatch the custom cart event
					document.dispatchEvent(addToCartEvent);

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
				const formData = tutorFormData([
					{ action: 'tutor_delete_course_from_cart', course_id: button.dataset.courseId },
				]);

				try {
					button.setAttribute('disabled', 'disabled');
					button.classList.add('is-loading');

					const post = await ajaxHandler(formData);
					const { status_code, data, message = defaultErrorMessage } = await post.json();

					if (status_code === 200) {
						document.querySelector('.tutor-cart-page-wrapper').parentElement.innerHTML = data?.cart_template;
						tutor_toast(__('Success', 'tutor'), message, 'success');

						// Trigger a custom event with cart count
						const removeCartEvent = new CustomEvent('tutorRemoveCartEvent', {
							detail: { cart_count: data?.cart_count },
						});

						// Dispatch the custom cart event
						document.dispatchEvent(removeCartEvent);
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


	// Display toast if cart page is not configured.
	document.addEventListener('click', (e) => {
		if (e.target.classList.contains('tutor-cart-page-not-configured')) {
			e.preventDefault();
			tutor_toast(__('Error!', 'tutor'), __('Cart page is not configured.', 'tutor'), 'error');
		}
	});

	// Display toast if checkout page is not configured.
	document.addEventListener('click', (e) => {
		if (e.target.classList.contains('tutor-checkout-page-not-configured')) {
			e.preventDefault();
			tutor_toast(__('Error!', 'tutor'), __('Checkout page is not configured.', 'tutor'), 'error');
		}
	});
});

/**
 * Tutor Cart Button - Global JavaScript for cart count updates
 * 
 * This file handles dynamic cart count updates for the Tutor LMS cart button.
 * It listens for custom events dispatched by the cart operations and updates
 * the cart count display across all cart button instances on the page.
 * 
 * @package Tutor
 * @since 4.1.0
 */

document.addEventListener('DOMContentLoaded', function () {
	// Listen for add to cart event
	document.addEventListener('tutorAddToCartEvent', function (event) {
		const cartCounters = document.querySelectorAll('.tutor-cart-count');
		cartCounters.forEach(function (counter) {
			if (event.detail.cart_count > 0) {
				counter.textContent = event.detail.cart_count;
				counter.style.display = 'inline-block';
			}
		});
	});

	// Listen for remove from cart event
	document.addEventListener('tutorRemoveCartEvent', function (event) {
		const cartCounters = document.querySelectorAll('.tutor-cart-count');
		cartCounters.forEach(function (counter) {
			const mode = counter.getAttribute('data-show-count') || 'if_has_items';
			
			if (event.detail.cart_count > 0) {
				counter.textContent = event.detail.cart_count;
				counter.style.display = 'inline-block';
			} else {
				if (mode === 'always') {
					counter.textContent = '0';
					counter.style.display = 'inline-block';
				} else {
					counter.textContent = '';
					counter.style.display = 'none';
				}
			}
		});
	});
});

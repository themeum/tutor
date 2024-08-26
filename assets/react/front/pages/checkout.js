import ajaxHandler from "../../admin-dashboard/segments/filter";
const { __ } = wp.i18n;

document.addEventListener('DOMContentLoaded', function () {
    const defaultErrorMessage = __('Something went wrong, please try again', 'tutor');
    const checkoutPageWrapper = document.querySelector(".tutor-checkout-page");

    if (checkoutPageWrapper) {
        const checkoutForm = document.querySelector("#tutor-checkout-form");
        // const payNowButton = document.querySelector("#tutor-checkout-pay-now-button");

        const paymentOptionsWrapper = document.querySelector(".tutor-checkout-payment-options");
        const paymentOptionInput = paymentOptionsWrapper.querySelector("input[name=payment_method]");
        const paymentTypeInput = document.querySelector("input[name=payment_type]");
        const paymentOptionButtons = paymentOptionsWrapper.querySelectorAll("button");

        const toggleCouponFormButton = document.querySelector("#tutor-toggle-coupon-form");
        const checkoutCouponForm = document.querySelector(".tutor-checkout-coupon-form");
        const checkoutCouponInput = checkoutCouponForm.querySelector("input");
        const checkoutCouponButton = checkoutCouponForm.querySelector("button");

        const checkoutCouponWrapper = document.querySelector(".tutor-checkout-coupon-wrapper");
        const checkoutCouponRemove = document.querySelector("#tutor-checkout-remove-coupon");

        // Handle payment method click 
        paymentOptionButtons.forEach((button) => {
            button.addEventListener('click', (e) => {
                paymentOptionButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                const paymentMethod = button.dataset.paymentMethod;
                paymentOptionInput.value = paymentMethod;
                paymentTypeInput.value = button.dataset.paymentType;
            })
        })

        // Handle toggle coupon form button click
        toggleCouponFormButton.addEventListener('click', () => {
            if (checkoutCouponForm.classList.contains('tutor-d-none')) {
                checkoutCouponForm.classList.remove('tutor-d-none');
                checkoutCouponInput.focus();
            } else {
                checkoutCouponForm.classList.add('tutor-d-none');
            }
        });

        // Handle apply coupon button click
        checkoutCouponButton.addEventListener('click', async (e) => {
            const couponCode = checkoutCouponInput.value;

            if (couponCode.length === 0) {
                tutor_toast(__('Failed', 'tutor'), __('Please add coupon code.'), 'error');
                return;
            }

            const formData = new FormData();
            formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);
            formData.set('action', 'tutor_apply_coupon');
            formData.set('coupon_code', couponCode);
            formData.set('object_ids', checkoutCouponButton.dataset.objectIds);

            try {
                checkoutCouponButton.setAttribute('disabled', 'disabled');
                checkoutCouponButton.classList.add('is-loading');

                const post = await ajaxHandler(formData);
                const { status_code, data, message = defaultErrorMessage } = await post.json();

                if (status_code === 200 ) {
                    tutor_toast(__('Success', 'tutor'), message, 'success');
                    checkoutCouponWrapper.classList.remove('tutor-d-none');
                    checkoutCouponForm.classList.add('tutor-d-none');
                    // @TODO: Display coupon code and price reduction.
                } else {
                    tutor_toast(__('Failed', 'tutor'), message, 'error');
                }
            } catch (error) {
                tutor_toast(__('Failed', 'tutor'), defaultErrorMessage, 'error');
            } finally {
                checkoutCouponButton.removeAttribute('disabled');
                checkoutCouponButton.classList.remove('is-loading');
            }
        });

        // Handle coupon remove button click
        checkoutCouponRemove.addEventListener('click', (e) => {
            checkoutCouponWrapper.classList.add('tutor-d-none');
            console.log('Remove coupon!');
            // @TODO: Handle coupon remove functions.
        });

        // Handle checkout form submit
        // checkoutForm.addEventListener('submit', async (e) => {
        //     e.preventDefault();

        //     const formData = new FormData(checkoutForm);

        //     try {
        //         payNowButton.setAttribute('disabled', 'disabled');
        //         payNowButton.classList.add('is-loading');

        //         const post = await ajaxHandler(formData);
        //         const { success, data = defaultErrorMessage } = await post.json();

        //         if (success) {
        //             tutor_toast(__('Success', 'tutor'), data, 'success');
        //         } else {
        //             tutor_toast(__('Failed', 'tutor'), data, 'error');
        //         }
        //     } catch (error) {
        //         tutor_toast(__('Failed', 'tutor'), defaultErrorMessage, 'error');
        //     } finally {
        //         payNowButton.removeAttribute('disabled');
        //         payNowButton.classList.remove('is-loading');
        //     }
        // });
    }
});

import ajaxHandler from "../../admin-dashboard/segments/filter";
const { __ } = wp.i18n;

document.addEventListener('DOMContentLoaded', function () {
    const defaultErrorMessage = __('Something went wrong, please try again', 'tutor');
    const checkoutPageWrapper = document.querySelector(".tutor-checkout-page");

    if (checkoutPageWrapper) {
        const checkoutCourses = document.querySelector('.tutor-checkout-courses');
        const checkoutGrandTotal = document.querySelector('.tutor-checkout-grand-total');
        const checkoutPrevCourses = checkoutCourses.innerHTML;
        const checkoutPrevGrandTotal = checkoutGrandTotal.innerHTML;

        const paymentOptionsWrapper = document.querySelector(".tutor-checkout-payment-options");
        const paymentOptionInput = paymentOptionsWrapper.querySelector("input[name=payment_method]");
        const paymentTypeInput = document.querySelector("input[name=payment_type]");
        const paymentOptionButtons = paymentOptionsWrapper.querySelectorAll("button");

        const toggleCouponFormButton = document.querySelector("#tutor-toggle-coupon-form");
        const applyCouponForm = document.querySelector(".tutor-apply-coupon-form");
        const checkoutHaveACoupon = document.querySelector(".tutor-have-a-coupon");
        const applyCouponInput = applyCouponForm?.querySelector("input");
        const applyCouponButton = applyCouponForm?.querySelector("button");

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

                const paymentInstructions = button.dataset.paymentInstruction;
                if (paymentInstructions) {
                    document.querySelector('.tutor-payment-instructions').classList.remove('tutor-d-none');
                    document.querySelector('.tutor-payment-instructions').textContent = paymentInstructions;
                } else {
                    document.querySelector('.tutor-payment-instructions').classList.add('tutor-d-none');
                }
            });
        });

        // Handle toggle coupon form button click
        toggleCouponFormButton?.addEventListener('click', () => {
            if (applyCouponForm.classList.contains('tutor-d-none')) {
                applyCouponForm.classList.remove('tutor-d-none');
                applyCouponInput.focus();
            } else {
                applyCouponForm.classList.add('tutor-d-none');
            }
        });

        // Handle apply coupon button click
        applyCouponButton?.addEventListener('click', async (e) => {
            const url = new URL(window.location.href);
            const plan = url.searchParams.get('plan');
            const couponCode = applyCouponInput.value;

            if (couponCode.length === 0) {
                tutor_toast(__('Failed', 'tutor'), __('Please add a coupon code.'), 'error');
                return;
            }

            const formData = new FormData();
            formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);
            formData.set('action', 'tutor_apply_coupon');
            formData.set('coupon_code', couponCode);
            formData.set('object_ids', applyCouponButton.dataset.objectIds);
            if (plan) {
                formData.set('plan', plan);
            }

            try {
                applyCouponButton.setAttribute('disabled', 'disabled');
                applyCouponButton.classList.add('is-loading');

                const post = await ajaxHandler(formData);
                const { status_code, data, message = defaultErrorMessage } = await post.json();

                if (status_code === 200) {
                    tutor_toast(__('Success', 'tutor'), message, 'success');
                    checkoutCouponWrapper.classList.remove('tutor-d-none');
                    checkoutCouponWrapper.querySelector('.tutor-checkout-coupon-badge span').innerHTML = couponCode;
                    checkoutCouponWrapper.querySelector('.tutor-discount-amount').innerHTML = `-${data.deducted_price}`;
                    applyCouponForm.classList.add('tutor-d-none');
                    checkoutHaveACoupon.classList.add('tutor-d-none');
                    checkoutGrandTotal.innerHTML = data.total_price;

                    // Update course items
                    data.items?.forEach((item) => {
                        const courseItem = document.querySelector(`[data-course-id="${item.item_id}"]`);
                        if (item.is_applied && courseItem) {
                            courseItem.querySelector('.tutor-text-right').innerHTML = `
                                <div class="tutor-fw-bold">${item.discount_price}</div>
                                <div class="tutor-checkout-discount-price">${item.regular_price}</div>
                            `
                            courseItem.querySelector('.tutor-checkout-coupon-badge').classList.remove('tutor-d-none');
                            courseItem.querySelector('.tutor-checkout-coupon-badge > span').innerHTML = couponCode;
                        }
                    });
                } else {
                    tutor_toast(__('Failed', 'tutor'), message, 'error');
                }
            } catch (error) {
                tutor_toast(__('Failed', 'tutor'), defaultErrorMessage, 'error');
            } finally {
                applyCouponButton.removeAttribute('disabled');
                applyCouponButton.classList.remove('is-loading');
            }
        });

        // Handle coupon remove button click
        checkoutCouponRemove?.addEventListener('click', (e) => {
            applyCouponInput.value = '';
            checkoutCourses.innerHTML = checkoutPrevCourses;
            checkoutGrandTotal.innerHTML = checkoutPrevGrandTotal;
            checkoutCouponWrapper.classList.add('tutor-d-none');
            checkoutHaveACoupon.classList.remove('tutor-d-none');
        });


        // Validate checkout form.
        const tutorCheckoutForm = document.getElementById('tutor-checkout-form');
        tutorCheckoutForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(e.target);
            const paymentMethod = formData.get('payment_method');
            if (!paymentMethod) {
                tutor_toast(__('Error', 'tutor'), __('Please select a payment method.', 'tutor'), 'error');
                return;
            }

            const submitButton = document.getElementById('tutor-checkout-pay-now-button');
            submitButton.classList.add('is-loading');
            submitButton.textContent = __('Processing', 'tutor');
            submitButton.setAttribute('disabled', true);
            
            this.submit();
        });
    }
});

import ajaxHandler from "../../helper/ajax-handler";

document.addEventListener('DOMContentLoaded', () => {
    const { __ } = wp.i18n;
    const defaultErrorMessage = __('Something went wrong, please try again', 'tutor');
    const checkoutPageWrapper = document.querySelector(".tutor-checkout-page");

    if (checkoutPageWrapper) {
        const paymentMethodWrapper = document.querySelector('.tutor-payment-method-wrapper');
        const payNowBtn = document.querySelector('#tutor-checkout-pay-now-button');
        const payments = document.querySelectorAll('.tutor-checkout-payment-item');
        const paymentTypeInput = document.querySelector("input[name=payment_type]");

        /**
         * Get checkout JSON object.
         *
         * @since 3.6.0
         *
         * @returns {object} JSON object.
         */
        function getCheckoutData() {
            let jsonStr = document.querySelector('#checkout_data')?.value
            return JSON.parse(jsonStr)
        }

        /**
         * Determines if tax is managed by Tutor (not the payment gateway) 
         *
         * @since 3.6.0
         *
         * @param {string} paymentMethodName - The name of the payment method (paypal, stripe, paddle, etc).
         *
         * @returns {boolean}
         */
        function isTaxManagedByTutor(paymentMethodName) {
            const externallyManagedTaxMethods = ['paddle'];
            return !externallyManagedTaxMethods.includes(paymentMethodName.toLowerCase());
        }

        /**
         * Determine tax info will be shown or hide.
         * 
         * @since 3.6.0
         *
         * @param {string} paymentMethodName payment method name.
         *
         * @returns {void}
         */
        function shouldShowTaxInfo(paymentMethodName) {
            const taxAmount = document.querySelector('.tutor-checkout-tax-amount');
            const inclTaxLabels = document.querySelectorAll('.tutor-checkout-incl-tax-label');
            const grandTotal = document.querySelector('.tutor-checkout-grand-total');
            const checkoutData = getCheckoutData();

            if (isTaxManagedByTutor(paymentMethodName)) {
                grandTotal.innerHTML = checkoutData.formatted_total_price;
                inclTaxLabels.forEach(label => label.classList.remove('tutor-d-none'));
                taxAmount?.classList.remove('tutor-d-none');
            } else {
                grandTotal.innerHTML = checkoutData.formatted_total_price_without_tax;
                inclTaxLabels.forEach(label => label.classList.add('tutor-d-none'));
                taxAmount?.classList.add('tutor-d-none');
            }
        }

        // Handle payment method selection.
        document.addEventListener('click', async (e) => {
            if (e.target.closest(".tutor-checkout-payment-options")) {
                const paymentOptionsWrapper = document.querySelector(".tutor-checkout-payment-options");
                const paymentOptions = paymentOptionsWrapper.querySelectorAll("label");

                // Remove active class.
                paymentOptions.forEach(item => item.classList.remove('active'));
                // Add active class to the selected option.
                const clickedOption = e.target.closest("label");
                clickedOption.classList.add('active');

                paymentTypeInput.value = clickedOption.dataset.paymentType;
                const paymentMethodName = clickedOption.firstElementChild.value;

                shouldShowTaxInfo(paymentMethodName);

                const instructionsContainer = document.querySelector('.tutor-payment-instructions');
                if (instructionsContainer) {
                    const paymentInstructions = clickedOption.querySelector('.tutor-payment-item-instructions');
                    if (paymentInstructions) {
                        instructionsContainer.classList.remove('tutor-d-none');
                        instructionsContainer.innerHTML = paymentInstructions.innerHTML;
                    } else {
                        instructionsContainer.classList.add('tutor-d-none');
                    }
                }
            }

            // Handle toggle coupon form button click.
            if (e.target.closest("#tutor-toggle-coupon-button")) {
                const applyCouponForm = document.querySelector(".tutor-apply-coupon-form");
                const applyCouponInput = applyCouponForm?.querySelector("input");
                if (applyCouponForm.classList.contains('tutor-d-none')) {
                    applyCouponForm.classList.remove('tutor-d-none');
                    applyCouponInput.focus();
                } else {
                    applyCouponForm.classList.add('tutor-d-none');
                }
            }

            // Handle apply coupon button.
            if (e.target.closest("#tutor-apply-coupon-button")) {
                const url = new URL(window.location.href);
                const plan = url.searchParams.get('plan');
                const couponCode = document.querySelector(".tutor-apply-coupon-form input")?.value;
                const applyCouponButton = document.querySelector(".tutor-apply-coupon-form button");

                if (couponCode.length === 0) {
                    tutor_toast(__('Failed', 'tutor'), __('Please add a coupon code.', 'tutor'), 'error');
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
                        await updateCheckoutData(couponCode, null, null);

                        if (!data.total_price && data.order_type === 'single_order') {
                            paymentMethodWrapper.classList.add('tutor-d-none');
                            payNowBtn.innerHTML = getCheckoutData()?.pay_now_btn_text;
                            paymentMethodWrapper.insertAdjacentHTML('beforeend', `<input type='hidden' name='payment_method' value='free' id="tutor-temp-payment-method"/>`);
                        }
                    } else {
                        tutor_toast(__('Failed', 'tutor'), message, 'error');
                    }
                } catch (error) {
                    tutor_toast(__('Failed', 'tutor'), defaultErrorMessage, 'error');
                } finally {
                    applyCouponButton.removeAttribute('disabled');
                    applyCouponButton.classList.remove('is-loading');
                }
            }

            // Handle coupon remove button click.
            if (e.target.closest("#tutor-checkout-remove-coupon")) {
                document.querySelector('input[name=coupon_code]').value = '-1';
                document.querySelector('#tutor-checkout-remove-coupon').classList.add('is-loading');

                await updateCheckoutData('-1', null, null);

                const selectedPaymentMethod = document.querySelector('input[name=payment_method]:checked')?.value;
                if (selectedPaymentMethod) {
                    shouldShowTaxInfo(selectedPaymentMethod);
                }

                paymentMethodWrapper.classList.remove('tutor-d-none');
                payNowBtn.innerHTML = getCheckoutData()?.pay_now_btn_text;
                document.getElementById('tutor-temp-payment-method')?.remove();
                handleSinglePaymentOptionSelection();
            }
        });

        // If only one payment available, keep selected.
        handleSinglePaymentOptionSelection();

        // Apply coupon on enter coupon field.
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && e.target.closest("input[name=coupon_code]")) {
                e.preventDefault();
                const btnApply = e.target.parentNode.querySelector("#tutor-apply-coupon-button");
                btnApply?.click();
            }
        });

        // Validate checkout form.
        const tutorCheckoutForm = document.getElementById('tutor-checkout-form');
        tutorCheckoutForm?.addEventListener('submit', function (e) {
            e.preventDefault();

            const checkoutData = getCheckoutData();
            const formData = new FormData(e.target);

            if (checkoutData.payment_method_required && !formData.get('payment_method')) {
                tutor_toast(__('Error', 'tutor'), __('Please select a payment method.', 'tutor'), 'error');
                return;
            }

            const submitButton = document.getElementById('tutor-checkout-pay-now-button');
            submitButton.classList.add('is-loading');
            submitButton.textContent = __('Processing', 'tutor');
            submitButton.setAttribute('disabled', true);

            this.submit();
        });

        /**
         * Update checkout data.
         * 
         * @since 3.3.0
         * 
         * @param {string} couponCode coupon code.
         * @param {string} billingCountry Billing country.
         * @param {string} billingState Billing state.
         */
        async function updateCheckoutData(couponCode, billingCountry = null, billingState = null) {
            const url = new URL(window.location.href);
            const plan = url.searchParams.get('plan');
            const course_id = url.searchParams.get('course_id');
            const order_id = url.searchParams.get('order_id');

            const formData = new FormData();
            formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);
            formData.set('action', 'tutor_get_checkout_html');
            formData.set('coupon_code', couponCode);

            if (billingCountry) {
                formData.set('billing_country', billingCountry);
            }

            if (billingState) {
                formData.set('billing_state', billingState);
            }

            if (plan) {
                formData.set('plan', plan);
            }

            if (course_id) {
                formData.set('course_id', course_id);
            }

            if (order_id) {
                formData.set('order_id', order_id);
            }

            const response = await ajaxHandler(formData);
            const res = await response.json();

            const checkoutDetails = document.querySelector('[tutor-checkout-details]');
            if (checkoutDetails) {
                checkoutDetails.innerHTML = res.data;
            }
        }

        /**
         * Handle tax calculation on country and state change.
         * 
         * @since 3.0.0
         */
        const dropdown_billing_country = document.querySelector('[name=billing_country]');
        const dropdown_billing_state = document.querySelector('[name=billing_state]');
        const spinner = '<span class="tutor-btn is-loading tutor-checkout-spinner"></span>';

        async function saveBilling(formData) {
            formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);
            formData.set('action', 'tutor_save_billing_info');

            const response = await ajaxHandler(formData);
            const res = await response.json();
            return res;
        }

        const toggleSpinner = (target, visibility) => {
            if ('show' === visibility) {
                target?.setAttribute('disabled', 'disabled');
                target?.closest('.tutor-position-relative')?.insertAdjacentHTML('beforeend', spinner);
            } else {
                target?.removeAttribute('disabled')
                target?.closest('.tutor-position-relative')?.querySelector('.tutor-checkout-spinner')?.remove();
            }
        }

        dropdown_billing_country?.addEventListener('change', async (e) => {
            const input_coupon_code = document.querySelector('[name=coupon_code]');
            const country = e.target.value;
            const coupon_code = input_coupon_code?.value ? input_coupon_code.value : '';

            if (country) {
                toggleSpinner(e.target, 'show');

                const formData = new FormData();
                formData.set('billing_country', country);

                await saveBilling(formData);
                await updateCheckoutData(coupon_code, dropdown_billing_country.value, dropdown_billing_state.value);

                toggleSpinner(e.target, 'hide');
            }
        })

        dropdown_billing_state?.addEventListener('change', async (e) => {
            const input_coupon_code = document.querySelector('[name=coupon_code]');
            const country = dropdown_billing_country.value;
            const state = e.target.value;
            const coupon_code = input_coupon_code?.value ? input_coupon_code.value : '';

            if (state) {
                toggleSpinner(e.target, 'show');

                const formData = new FormData();
                formData.set('billing_country', country);
                formData.set('billing_state', state);

                await saveBilling(formData);
                await updateCheckoutData(coupon_code, dropdown_billing_country.value, dropdown_billing_state.value);

                toggleSpinner(e.target, 'hide');
            }
        })

        /**
         * Handles the selection of a single payment option.
         * 
         * @returns {void}
         * @since 3.5.0
         */
        function handleSinglePaymentOptionSelection() {
            const checkoutData = getCheckoutData();
            if (!checkoutData.payment_method_required) {
                return;
            }

            // If only one payment available, keep selected.
            if (payments.length === 1) {
                payments[0].classList.add('active');
                payments[0].querySelector('input[name=payment_method]').checked = true;
                paymentTypeInput.value = payments[0].dataset.paymentType;

                shouldShowTaxInfo(payments[0].firstElementChild.value);

                const paymentInstructions = payments[0].querySelector('.tutor-payment-item-instructions');
                if (paymentInstructions) {
                    document.querySelector('.tutor-payment-instructions').classList.remove('tutor-d-none');
                    document.querySelector('.tutor-payment-instructions').innerHTML = paymentInstructions.innerHTML;
                }
            }
        }
    }
});

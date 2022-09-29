/**
 * Course price & product management
 *
 * @since v2.0.5
 */
document.addEventListener('DOMContentLoaded', function () {
    const { __ } = wp.i18n;

    /**
     * Backend: Fetch WC product info on product dropdown change.
     * @since 2.0.7
     */
    // jQuery('select[name="_tutor_course_product_id"]').on('change', function (e) {
    //     console.log(e.target.value);
    //     console.log(e.target.tagName);
    //     console.log(e.target.id);
    //     console.log(e.target.dataset);

    //     const selectElem = document.getElementById('tutor-wc-product-select');
    //     let id = jQuery(this).val();
    //     const linkedProductId = jQuery(this).data('product-id');
    //     if (!id) return;
    //     /**
    //      * If user select already linked product then return
    //      * it will prevent unnecessary ajax request.
    //      * 
    //      * @since v2.1.0
    //      */
    //     if (id == linkedProductId) {
    //         return;
    //     }
    //     let data = {
    //         action: 'tutor_get_wc_product',
    //         product_id: id,
    //         course_id: jQuery(this).data('course-id'),
    //     }

    //     jQuery.ajax({
    //         url: _tutorobject.ajaxurl,
    //         type: 'POST',
    //         dataType: 'json',
    //         data: data,
    //         success: function (res) {
    //             const {success, data} = res;
    //             // console.log(res);
    //             if (success) {
    //                 jQuery('input[name="course_price"]').val(res.data.regular_price)
    //                 jQuery('input[name="course_sale_price"]').val(res.data.sale_price)
    //                 selectElem.dataset.productId = id;
    //             }
    //             if (!success) {
    //                 tutor_toast(
    //                     __( 'Failed', 'tutor' ),
    //                     __( data, 'tutor' ),
    //                     'error'
    //                 );
    //                 selectElem.value = "-1";
    //             }
    //         }
    //     });

    // })

    const attachMetaBox = document.getElementById('tutor-attach-product');
    if (attachMetaBox) {
        attachMetaBox.onchange = (e) => {
            const target = e.target;
            if (target.tagName === 'SELECT' && target.id === 'tutor-wc-product-select') {
                let id = target.value;
                const linkedProductId = target.dataset.productId;
                const jsSelect = target.nextElementSibling;
                if (!id) return;
                /**
                 * If user select already linked product then return
                 * it will prevent unnecessary ajax request.
                 * 
                 * @since v2.1.0
                 */
                if (id == linkedProductId) {
                    return;
                }
                let data = {
                    action: 'tutor_get_wc_product',
                    product_id: id,
                    course_id: target.dataset.courseId,
                }
        
                jQuery.ajax({
                    url: _tutorobject.ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    success: function (res) {
                        const {success, data} = res;
                        // console.log(res);
                        if (success) {
                            jQuery('input[name="course_price"]').val(res.data.regular_price)
                            jQuery('input[name="course_sale_price"]').val(res.data.sale_price)
                            target.dataset.productId = id;
                        }
                        if (!success) {
                            tutor_toast(
                                __( 'Failed', 'tutor' ),
                                __( data, 'tutor' ),
                                'error'
                            );
                            target.value = linkedProductId == 0 ? "-1" : linkedProductId;
                            target.dataset.productId = linkedProductId == 0 ? 0 : linkedProductId;;
                            if (jsSelect) {
                                let label = jsSelect.querySelector('span.tutor-form-select-label');
                                if (label) {
                                    label.innerHTML = linkedProductId == 0 ? __( 'Select a product', 'tutor') : target.options[target.selectedIndex].text;
                                    label.dataset.value = linkedProductId == 0 ? "-1" : linkedProductId;
                                    
                                    // attachMetaBox.querySelectorAll('[type=number]').forEach((elem) => {
                                    //     elem.setAttribute('value', '');
                                    // })
                                }
                            }
                        }
                    }
                });
            }
        }
    }

    /**
     * Backend: Course price free/paid toggle
     *
     * @since v2.0.7
     */
    let priceTypeEl = jQuery('input[name="tutor_course_price_type"]');
    let togglePriceWrapper = function (priceType) {
        let priceWrapper = jQuery('.tutor-course-product-fields');
        'free' === priceType
            ? priceWrapper.hide()
            : priceWrapper.show();
    }

    let priceType = priceTypeEl.filter(":checked").val();
    togglePriceWrapper(priceType)

    setTimeout(() => {
        priceTypeEl.change(function (e) { togglePriceWrapper(jQuery(this).val()) })
    })


    /**
     * Price validation
     * @since 2.0.7
     */
    let old_sale_price = jQuery('input[name="course_sale_price"]').val();
    jQuery('input[name="course_sale_price"]').on('blur', function () {
        let regular_price = jQuery('input[name="course_price"]').val()
        let sale_price = jQuery(this).val();
        if (Number(sale_price) >= Number(regular_price)) {
            tutor_toast(__('Invalid Sale Price', 'tutor'), __('Sale price must be smaller than regular price', 'tutor'), 'error');
            jQuery('input[name="course_sale_price"]').val(old_sale_price)
        }
    })


    /**
     * Frontend: Course price free/paid toggle 
     * 
     * @since v2.0.7
     */
    const priceToggleRadios = document.querySelectorAll(".tutor-course-price-toggle input[type='radio']");
    const priceRow = document.querySelector(".tutor-course-price-row");
    priceToggleRadios.forEach((radio) => {
        radio.addEventListener('change', (e) => {
            if (e.target.value === 'paid' && !priceRow.classList.contains('is-paid')) {
                priceRow.classList.add('is-paid');
            } else {
                priceRow.classList.remove('is-paid');
            }
        })
    })

});
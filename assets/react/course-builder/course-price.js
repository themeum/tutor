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
    jQuery('select[name="_tutor_course_product_id"]').on('change', function () {
        let id = jQuery(this).val()
        if (!id) return;

        let data = {
            action: 'tutor_get_wc_product',
            product_id: id
        }

        jQuery.ajax({
            url: _tutorobject.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function (res) {
                // console.log(res);
                if (res.success) {
                    jQuery('input[name="course_price"]').val(res.data.regular_price)
                    jQuery('input[name="course_sale_price"]').val(res.data.sale_price)
                }
            }
        });

    })

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
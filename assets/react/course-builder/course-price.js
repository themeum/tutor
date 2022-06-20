/**
 * Course price & product management
 *
 * @since v2.0.5
 */
document.addEventListener('DOMContentLoaded', function() {
    /**
     * Show product dropdown if paid option checked
     *
     * @since v2.0.5
     */
     const productFieldsWrapper = document.querySelector('.tutor-course-product-fields');
     const coursePriceWrapper = document.querySelector('.tutor-course-product-fields.tutor-course-is-free');
     const productField = document.querySelector('select[name=_tutor_course_product_id]');
     if (coursePriceWrapper) {
         coursePriceWrapper.style.display = 'none';
         productField.value = '-1';
     }
     const priceFields = document.querySelectorAll('.tutor-course-price-fields [name=tutor_course_price_type]');
     priceFields.forEach((field) => {
         if (field) {
             field.onclick = (e) => {
                const status = e.target.value;
                if (status === 'paid') {
                    productFieldsWrapper.style.display = 'flex';
                } else {
                    productFieldsWrapper.style.display = 'none';
                    productField.value = '-1';
                }
             }
         }
     });


    /**
     * Course price free/paid toggle 
     * 
     * @since v2.0.7
     */
    const priceToggleRadios = document.querySelectorAll(".tutor-course-price-toggle input[type='radio']");
    const priceRow = document.querySelector(".tutor-course-price-row");
    priceToggleRadios.forEach((radio)=>{
        radio.addEventListener('change', (e)=>{
            if(e.target.value === 'paid' && !priceRow.classList.contains('is-paid')){
                priceRow.classList.add('is-paid');
            }else{
                priceRow.classList.remove('is-paid');
            }
        })
    })

});
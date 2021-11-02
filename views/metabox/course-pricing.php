<?php
    $monetize_by = tutor_utils()->get_option('monetize_by');
    $course_price = tutor_utils()->get_raw_course_price(get_the_ID());
    $currency_symbol = tutor_utils()->currency_symbol();
    $_tutor_course_price_type = tutor_utils()->price_type();
?>

<div class="tutor-bs-row tutor-bs-align-items-center">
    <div class="tutor-bs-col-6 tutor-bs-col-sm-5 tutor-bs-col-lg-4">
        <div class="tutor-form-check tutor-mb-15">
            <input type="radio" id="tutor_price_paid" class="tutor-form-check-input" name="tutor_course_price_type"  value="paid" <?php checked($_tutor_course_price_type, 'paid'); ?>/>
            <label for="tutor_price_paid" class="tutor-amount-field">
                <span class="tutor-input-prepand">
                    <?php echo $currency_symbol; ?>
                </span>
                <input type="text" name="course_price" value="<?php echo $course_price->regular_price; ?>" placeholder="<?php _e('Set course price', 'tutor'); ?>">
            </label>
        </div>
    </div>
    <div class="tutor-bs-col-6 tutor-bs-col-sm-5 tutor-bs-col-lg-4">
        <div class="tutor-form-check tutor-mb-15">
            <input type="radio" id="tutor_price_free" class="tutor-form-check-input" name="tutor_course_price_type" value="free" <?php $_tutor_course_price_type ? checked($_tutor_course_price_type, 'free') : checked('true', 'true'); ?>/>
            <label for="tutor_price_free">
                <?php _e('Free', "tutor") ?>
            </label>
        </div>
    </div>
</div>
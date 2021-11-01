<?php
    $monetize_by = tutor_utils()->get_option('monetize_by');
    $course_price = tutor_utils()->get_raw_course_price(get_the_ID());
    $currency_symbol = tutor_utils()->currency_symbol();
    $_tutor_course_price_type = tutor_utils()->price_type();
?>

<div class="tutor-bs-row">
    <div class="tutor-bs-col-4">
        <label>
            <input type="radio" name="tutor_course_price_type" value="paid" <?php checked($_tutor_course_price_type, 'paid'); ?>>
            <span></span>
            <div class="tutor-form-group">
                <span class="tutor-input-prepand"><?php echo $currency_symbol; ?></span>
                <input type="text" name="course_price" value="<?php echo $course_price->regular_price; ?>" placeholder="<?php _e('Set course price', 'tutor'); ?>">
            </div>
        </label>
    </div>
    <div class="tutor-bs-col-4">
        <label>
            <input type="radio" name="tutor_course_price_type" value="free" <?php $_tutor_course_price_type ? checked($_tutor_course_price_type, 'free') : checked('true', 'true'); ?>>
            <span><?php _e('Free', "tutor") ?></span>
        </label>
    </div>
</div>
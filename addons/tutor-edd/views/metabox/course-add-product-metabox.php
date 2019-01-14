<?php
/**
 * Add product metabox
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @since v.1.0.0
 */

?>

<div class="tutor-option-field-row">
    <div class="tutor-option-field-label">
        <label for="">
			<?php _e('Select product', 'tutor-edd'); ?> <br />
            <p class="text-muted">(<?php _e('Only for if you sell course', 'tutor-edd'); ?>)</p>
        </label>
    </div>
    <div class="tutor-option-field">
		<?php

		$products = tutor_edd_utils()->get_edd_products();
        $product_id = tutor_utils()->get_course_product_id();
		?>

        <select name="_tutor_course_product_id" class="tutor_select2" style="min-width: 300px;">
            <option value=""><?php _e('Select a Product'); ?></option>
			<?php
			foreach ($products as $product){
				echo "<option value='{$product->ID}' ".selected($product->ID, $product_id)." >{$product->post_title}</option>";
			}
			?>

        </select>

        <p class="desc">
			<?php _e('Sell your product, process by EDD', 'tutor-edd'); ?>
        </p>
    </div>
</div>

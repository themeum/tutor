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
			<?php _e('Select product', 'tutor'); ?> <br />
            <p class="text-muted">(<?php _e('Only for if you sell course', 'tutor'); ?>)</p>
        </label>
    </div>
    <div class="tutor-option-field">
		<?php
		$products = tutor_utils()->get_wc_products_db();
		$product_id = tutor_utils()->get_course_product_id();
		?>

        <select name="_tutor_course_product_id" class="tutor_select2" style="min-width: 300px;">
            <option value=""><?php _e('Select a Product'); ?></option>
			<?php
			foreach ($products as $product){
			    if ($product->ID == $product_id){
				    echo "<option value='{$product->ID}' ".selected($product->ID, $product_id)." >{$product->post_title}</option>";
			    }

			    $usedProduct = tutor_utils()->product_belongs_with_course($product->ID);
			    if ( ! $usedProduct){
				    echo "<option value='{$product->ID}' ".selected($product->ID, $product_id)." >{$product->post_title}</option>";
			    }
			}
			?>
        </select>

        <p class="desc">
			<?php _e('If you like to sell your course, then select a product, the purchase will be process by WooCommerce', 'tutor'); ?>
        </p>
    </div>
</div>

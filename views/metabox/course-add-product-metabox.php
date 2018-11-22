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

<div class="dozent-option-field-row">
    <div class="dozent-option-field-label">
        <label for="">
			<?php _e('Select product', 'dozent'); ?> <br />
            <p class="text-muted">(<?php _e('Only for if you sell course', 'dozent'); ?>)</p>
        </label>
    </div>
    <div class="dozent-option-field">
		<?php
		$products = dozent_utils()->get_wc_products_db();
		$product_id = dozent_utils()->get_course_product_id();
		?>

        <select name="_dozent_course_product_id" class="dozent_select2" style="min-width: 300px;">
            <option value=""><?php _e('Select a Product'); ?></option>
			<?php
			foreach ($products as $product){
				echo "<option value='{$product->ID}' ".selected($product->ID, $product_id)." >{$product->post_title}</option>";
			}
			?>

        </select>

        <p class="desc">
			<?php _e('If you like to sell your course, then select a product, the purchase will be process by WooCommerce', 'dozent'); ?>
        </p>
    </div>
</div>

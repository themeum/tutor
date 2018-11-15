
<?php
/**
 * Template for displaying price
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */



$is_purchasable = tutor_utils()->is_course_purchasable();

if ($is_purchasable){
	$product_id = tutor_utils()->get_course_product_id();
	$product = wc_get_product( $product_id );

	if ($product) {
		?>

		<p class="price">
			<?php echo $product->get_price_html(); ?>
		</p>

		<?php
	}
}else{
	?>
	<p class="price">
		<?php _e('Free', 'tutor'); ?>
	</p>
	<?php
}

?>
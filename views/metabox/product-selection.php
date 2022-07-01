<?php
	$tutor_course_price_type	= tutils()->price_type();
	$course_price    			= tutor_utils()->get_raw_course_price( get_the_ID() );
?>
<div class="tutor-row tutor-mt-16 tutor-mb-16 tutor-course-price-fields">
	<div class="tutor-col-12 tutor-col-sm-5 tutor-col-lg-4">
		<label for="tutor_course_price_type" class="tutor-fs-6 tutor-fw-medium">
			<?php _e( 'Course Type', 'tutor' ); ?> <br />
		</label>
	</div>
	<div class="tutor-col-12 tutor-col-sm-7 tutor-col-lg-8 tutor-d-flex">
		<div class="tutor-form-check tutor-mr-16">
			<input type="radio" id="tutor-course-price-paid" class="tutor-form-check-input" name="tutor_course_price_type" value="paid" <?php checked( $tutor_course_price_type, 'paid' ); ?>/>
			<label for="tutor-course-price-paid"><?php _e( 'Paid', 'tutor' ); ?></label>
		</div>
		<div class="tutor-form-check tutor-mr-16">
			<input type="radio" id="tutor-course-price-free" class="tutor-form-check-input" name="tutor_course_price_type" value="free" <?php $tutor_course_price_type ? checked( $tutor_course_price_type, 'free' ) : checked( 'true', 'true' ); ?>/>
			<label for="tutor-course-price-free"><?php _e( 'Free', 'tutor' ); ?></label>
		</div>
	</div>
</div>
<div class="tutor-row tutor-mt-16 tutor-mb-16 tutor-course-product-fields tutor-course-is-<?php echo esc_attr( $tutor_course_price_type ); ?>">
	<div class="tutor-col-12 tutor-col-md-5 tutor-col-lg-4">
		<label class="tutor-fs-6 tutor-fw-medium">
			<?php _e( 'Select product', 'tutor' ); ?> <br />
			<p class="tutor-color-muted">(<?php echo $label_info ?>)</p>
		</label>
	</div>
	<div class="tutor-col-12 tutor-col-md-7 tutor-col-lg-8">
		<select name="_tutor_course_product_id" class="tutor-form-select" required>
			<option value="-1"><?php _e( 'Select a Product' ); ?></option>
			<?php
				foreach ( $products as $product ) {
					echo "<option value='{$product->ID}' " . selected( $product->ID, $product_id ) . " >{$product->post_title}</option>";
				}
			?>
		</select>
		<div class="tutor-form-feedback">
			<i class="tutor-icon-circle-info-o tutor-form-feedback-icon" area-hidden="true"></i>
			<div><?php echo $info_text; ?></div>
		</div>
	</div>
<?php if( tutor()->has_pro ): ?>
	<div class="tutor-col-12 tutor-col-md-5 tutor-col-lg-4 tutor-mt-12">
		<label class="tutor-fs-6 tutor-fw-medium">
			<?php _e( 'Regular Price', 'tutor' ); ?>
		</label>
	</div>

	<div class="tutor-col-12 tutor-col-md-7 tutor-col-lg-8 tutor-mt-12">
		<input type="number" class="tutor-form-control" style="width:170px" name="course_price" value="<?php echo $course_price->regular_price === 0 ? null : $course_price->regular_price; ?>"  step="any" min="0" pattern="^\d*(\.\d{0,2})?$">
	</div>

	<div class="tutor-col-12 tutor-col-md-5 tutor-col-lg-4 tutor-mt-12">
		<label class="tutor-fs-6 tutor-fw-medium">
			<?php _e( 'Sale Price (Discounted Price)', 'tutor' ); ?>
		</label>
	</div>

	<div class="tutor-col-12 tutor-col-md-7 tutor-col-lg-8 tutor-mt-12">
		<input type="number" class="tutor-form-control" style="width:170px" name="course_sale_price" value="<?php echo $course_price->sale_price === 0 ? null: $course_price->sale_price; ?>" step="any" min="0" pattern="^\d*(\.\d{0,2})?$">
	</div>
<?php endif; ?>
</div>


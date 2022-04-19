<?php
	$_tutor_course_price_type = tutils()->price_type();
?>

<div class="tutor-row tutor-mt-16 tutor-mb-16">
	<div class="tutor-col-12 tutor-col-md-5 tutor-col-lg-4">
		<label class="tutor-fs-6 tutor-fw-medium">
			<?php _e( 'Select product', 'tutor' ); ?> <br />
			<p class="tutor-color-muted">(<?php _e( 'When selling the course', 'tutor' ); ?>)</p>
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
</div>

<div class="tutor-row tutor-mt-16 tutor-mb-16">
	<div class="tutor-col-12 tutor-col-sm-5 tutor-col-lg-4">
		<label for="">
			<?php _e( 'Course Type', 'tutor' ); ?> <br />
		</label>
	</div>
	<div class="tutor-col-12 tutor-col-sm-7 tutor-col-lg-8 tutor-d-flex">
		<div class="tutor-form-check tutor-mr-16">
			<input type="radio" id="tutor_coursePrice_paid" class="tutor-form-check-input" name="tutor_course_price_type" value="paid" <?php checked( $_tutor_course_price_type, 'paid' ); ?>/>
			<label for="tutor_coursePrice_paid"><?php _e( 'Paid', 'tutor' ); ?></label>
		</div>
		<div class="tutor-form-check tutor-mr-16">
			<input type="radio" id="tutor_coursePrice_free" class="tutor-form-check-input" name="tutor_course_price_type" value="free" <?php $_tutor_course_price_type ? checked( $_tutor_course_price_type, 'free' ) : checked( 'true', 'true' ); ?>/>
			<label for="tutor_coursePrice_free"><?php _e( 'Free', 'tutor' ); ?></label>
		</div>
	</div>
</div>

<?php
	$filter_object = new \TUTOR\Course_Filter();

	$filter_prices = array(
		'free' => __( 'Free', 'tutor' ),
		'paid' => __( 'Paid', 'tutor' ),
	);

	$course_levels     = tutor_utils()->course_levels();
	$supported_filters = tutor_utils()->get_option( 'supported_course_filters', array() );
	$supported_filters = array_keys( $supported_filters );
	?>
<form>
	<?php do_action( 'tutor_course_filter/before' ); ?>
	<?php
	if ( in_array( 'search', $supported_filters ) ) {
		?>
			<div class="filter-widget-search">
				<div class="tutor-input-group tutor-form-control-has-icon tutor-from-control">
					<span class="tutor-icon-search-filled tutor-input-group-icon tutor-color-black-50"></span>
					<input type="Search" class="tutor-form-control" name="keyword" placeholder="<?php _e( 'Search...' ); ?>"/>
				</div>
			</div>
			<?php
	}
	?>
	<div class="tutor-filter-widget-items-wrap">
		<div class="filter-widget-input-wrapper tutor-mt-24">
			<div class="filter-widget-input">
				<?php
				if ( in_array( 'category', $supported_filters ) ) {
					?>
				<div class="filter-widget-title tutor-fs-6 tutor-color-black tutor-mb-24">
					<?php esc_html_e( 'Category', 'tutor' ); ?>
				</div>
				<div class="filter-widget-checkboxes">
					<?php $filter_object->render_terms( 'category' ); ?>
				</div>
				<?php } ?>
			</div>
		</div>
		<div class="filter-widget-input-wrapper tutor-mt-24">
			<div class="filter-widget-input">
				<?php
				if ( in_array( 'tag', $supported_filters ) ) {
					?>
				<div class="filter-widget-title tutor-fs-6 tutor-color-black tutor-mb-24">
					<?php esc_html_e( 'Tag', 'tutor' ); ?>
				</div>
				<div class="filter-widget-checkboxes">
					<?php $filter_object->render_terms( 'tag' ); ?>
				</div>
				<?php } ?>
			</div>
		</div>
		<?php
		if ( in_array( 'difficulty_level', $supported_filters ) ) {
			?>
		<div class="filter-widget-input-wrapper tutor-mt-24">
			<div class="filter-widget-input">
				<div class="filter-widget-title tutor-fs-6 tutor-color-black tutor-mb-24">
				<?php esc_html_e( 'Level', 'tutor' ); ?>
				</div>
				<div class="filter-widget-checkboxes">
				<?php
					$key = '';
				foreach ( $course_levels as  $value => $title ) {
					if ( $key == 'all_levels' ) {
						continue;
					}
					?>
						<div class="tutor-form-check tutor-mb-20">
							<input type="checkbox" class="tutor-form-check-input" id="<?php echo esc_html( $value ); ?>" name="tutor-course-filter-level" value="<?php echo esc_html( $value ); ?>"/>&nbsp;
							<label for="<?php echo esc_html( $value ); ?>">
							<?php esc_html_e( $title ); ?>
							</label>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php } ?>
		<?php
			$is_membership = get_tutor_option( 'monetize_by' ) == 'pmpro' && tutor_utils()->has_pmpro();
		if ( ! $is_membership && in_array( 'price_type', $supported_filters ) ) {
			?>
		<div class="filter-widget-input-wrapper tutor-mt-24">
			<div class="filter-widget-input">
				<div class="filter-widget-title tutor-fs-6 tutor-color-black tutor-mb-24">
				<?php _e( 'Price', 'tutor' ); ?>
				</div>
				<div class="filter-widget-checkboxes">
				<?php
				foreach ( $filter_prices as $value => $title ) {
					?>
							<div class="tutor-form-check tutor-mb-20">
								<input type="checkbox" class="tutor-form-check-input" id="<?php echo esc_html( $value ); ?>" name="tutor-course-filter-price" value="<?php echo esc_html( $value ); ?>"/>&nbsp;
								<label for="<?php echo esc_html( $value ); ?>">
							<?php esc_html_e( $title ); ?>
								</label>
							</div>
						<?php
				}
				?>
				</div>
			</div>
		</div>
		<?php } ?>
		<div class="filter-widget-input-wrapper tutor-mt-24">
			<div class="filter-widget-input">
				<div class="tutor-clear-all-filter">
					<a href="#" onclick="window.location.reload()">
						<i class="tutor-icon-cross-filled"></i> <?php esc_html_e( 'Clear All Filters', 'tutor' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
	<?php do_action( 'tutor_course_filter/after' ); ?>
</form>

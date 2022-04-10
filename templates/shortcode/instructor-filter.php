<?php
/**
 * Prepare categories & short by items
 *
 * @since v2.0.0
 * @package Instructor list
 */

$category_id      = '';
$total_categories = isset( $all_cats ) ? $all_cats : 0;
$categories       = isset( $categories ) ? $categories : array();
$limit            = 8;
$show_more        = true;
$short_by         = array(
	'relevant' => __( 'Relevant', 'tutor' ),
	'new'      => __( 'New', 'tutor' ),
	'popular'  => __( 'Popular', 'tutor' ),
);

if ( $total_categories && $total_categories > $limit ) {
	$show_more = true;
}
?>

<div class="tutor-instructors" tutor-instructors <?php
	foreach ( $attributes as $key => $value ) {
		echo 'data-' . $key . '="' . $value . '" ';
	}
?>>
	<div class="tutor-row">
		<aside class="tutor-col-lg-3">
			
			<!-- <div class="tutor-instructor-customize-wrapper">
				<div class="tutor-instructor-filters">
					<i class="tutor-icon-slider-vertical tutor-color-text-brand"></i>
					<span class="tutor-fs-5 tutor-fw-medium tutor-color-black">
						<?php esc_html_e( 'Filters', 'tutor' ); ?>
					</span>
				</div>
				<div class="tutor-instructor-customize-clear clear-instructor-filter">
					<i class="tutor-icon-times design-dark"></i>
					<span className="tutor-color-muted tutor-fs-6">
						<?php esc_html_e( 'Clear', 'tutor' ); ?>
					</span>
				</div>
			</div> -->

			<div class="tutor-widget tutor-widget-course-categories tutor-mt-48">
				<h3 class="tutor-widget-title">
					<?php esc_html_e( 'Category', 'tutor' ); ?>
				</h3>

				<div class="tutor-widget-content">
					<div class="<?php echo $show_more ? 'tutor-toggle-more-content tutor-toggle-more-collapsed' : '' ?>"<?php echo $show_more ? ' data-tutor-toggle-more-content data-toggle-height="200" style="height: 200px;"' : '' ?>>
						<div class="tutor-list" tutor-instructors-category-filter>
							<?php foreach ( $categories as $category ) : ?>
								<div class="tutor-list-item">
									<label>
										<input id="tutor-instructor-checkbox-<?php esc_attr_e( $category->term_id ); ?>" type="checkbox" class="tutor-form-check-input" name="category" value="<?php esc_attr_e( $category->term_id ); ?>" />
										<?php esc_html_e( $category->name ); ?>
									</label>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<?php if ( $show_more ) : ?>
						<a href="#" class="tutor-btn-show-more tutor-btn tutor-btn-ghost tutor-mt-32" data-tutor-toggle-more=".tutor-toggle-more-content">
							<span class="tutor-toggle-btn-icon tutor-icon tutor-icon-plus tutor-mr-8" area-hidden="true"></span>
							<span class="tutor-toggle-btn-text"><?php esc_html_e( 'Show More', 'tutor' ); ?></span>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<div class="tutor-widget tutor-widget-course-ratings tutor-mt-48">
				<h3 class="tutor-widget-title">
					<?php esc_html_e( 'Ratings', 'tutor' ); ?>
				</h3>

				<div class="tutor-widget-content">
					<div class="tutor-ratings tutor-ratings-lg">
						<div class="tutor-ratings-stars">
							<?php for ( $i = 1; $i < 6; $i++ ) : ?>
								<i class="tutor-icon-star-line" tutor-instructors-ratings-value data-value="<?php echo $i; ?>" area-hidden="true"></i>
							<?php endfor; ?> 
						</div>
						<span class="tutor-ratings-count tutor-instructor-rating-filter"></span>  
					</div>
				</div>
			</div>
		</aside>

		<main class="tutor-col-lg-9">
			<div class="tutor-form-wrap tutor-mb-24">
				<span class="tutor-icon-search tutor-form-icon tutor-form-icon-reverse" area-hidden="true"></span>
				<input type="text" class="tutor-form-control" name="keyword" placeholder="<?php esc_html_e( 'Search any instructor...', 'tutor' ); ?>" />
			</div>
			<div class="tutor-instructor-form-group">
				<label for="tutor-instructor-relevant-sort" class="tutor-fs-6 tutor-color-muted">
					<?php _e( 'Short by', 'tutor' ); ?>
				</label>
				<select class="tutor-form-control" id="tutor-instructor-relevant-sort">
					<?php foreach ( $short_by as $k => $v ) : ?>
						<option value="<?php esc_attr_e( $k ); ?>">
							<?php esc_html_e( $v ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<!-- Filter -->
			<div tutor-instructors-content>
				<?php echo $content; ?>
			</div>
		</main>
	</div>
</div>

<?php
/**
 * Instructor filter
 *
 * @package Tutor\Templates
 * @subpackage Shortcode
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$category_id      = '';
$total_categories = isset( $all_cats ) ? $all_cats : 0;
$categories       = isset( $categories ) ? $categories : array();
$limit            = 8;
$show_more        = false;
$short_by         = array(
	'relevant' => __( 'Relevant', 'tutor' ),
	'new'      => __( 'New', 'tutor' ),
	'popular'  => __( 'Popular', 'tutor' ),
);

if ( $total_categories && $total_categories > $limit ) {
	$show_more = true;
}

$columns = tutor_utils()->get_option( 'courses_col_per_row', 3 );
?>

<div class="tutor-wrap tutor-wrap-parent tutor-instructors" tutor-instructors 
<?php
foreach ( $attributes as $key => $value ) {
	if ( is_array( $value ) ) {
		continue;
	}
	echo esc_attr( 'data-' . $key . '="' . $value . '" ' );
}
?>
>
	<div class="tutor-row">
		<aside class="tutor-col-lg-3 tutor-mb-32 tutor-mb-lg-0" tutor-instructors-filters>
			<div class="tutor-d-flex tutor-align-center">
				<div>
					<span class="tutor-icon-slider-vertical tutor-color-primary tutor-mr-8" area-hidden="true"></span>
					<span class="tutor-fs-5 tutor-fw-medium tutor-color-black"><?php esc_html_e( 'Filters', 'tutor' ); ?></span>
				</div>

				<div class="tutor-ml-32">
					<a href="#" class="tutor-btn tutor-btn-ghost" tutor-instructors-filter-clear>
						<span class="tutor-icon-times tutor-mr-8" area-hidden="true"></span>
						<span class="tutor-fw-medium"><?php esc_html_e( 'Clear', 'tutor' ); ?></span>
					</a>
				</div>
			</div>

			<div class="tutor-widget tutor-widget-course-categories tutor-mt-48">
				<h3 class="tutor-widget-title">
					<?php esc_html_e( 'Category', 'tutor' ); ?>
				</h3>

				<div class="tutor-widget-content">
					<div class="<?php echo $show_more ? 'tutor-toggle-more-content tutor-toggle-more-collapsed' : ''; ?>"<?php echo $show_more ? ' data-tutor-toggle-more-content data-toggle-height="200" style="height: 200px;"' : ''; ?>>
						<div class="tutor-list" tutor-instructors-filter-category>
							<?php foreach ( $categories as $category ) : ?>
								<div class="tutor-list-item">
									<label>
										<input id="tutor-instructor-checkbox-<?php echo esc_attr( $category->term_id ); ?>" type="checkbox" class="tutor-form-check-input" name="category" value="<?php echo esc_attr( $category->term_id ); ?>" />
										<?php echo esc_html( $category->name ); ?>
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
				<div class="tutor-ratings tutor-ratings-lg tutor-ratings-selectable">
						<div class="tutor-ratings-stars">
							<?php for ( $i = 1; $i < 6; $i++ ) : ?>
								<i class="tutor-icon-star-line" tutor-instructors-filter-rating data-value="<?php echo esc_attr( $i ); ?>" area-hidden="true"></i>
							<?php endfor; ?> 
						</div>
						<span class="tutor-ratings-count tutor-instructor-rating-filter" tutor-instructors-filter-rating-count></span>  
					</div>
				</div>
			</div>
		</aside>

		<?php if ( $columns < 3 ) : ?>
		<div class="tutor-col-1 tutor-d-none tutor-d-xl-block" area-hidden="true"></div>
		<?php endif; ?>

		<main class="tutor-col-lg-9 tutor-col-xl-<?php echo $columns < 3 ? 8 : 9; ?>">
			<div class="tutor-form-wrap tutor-mb-24">
				<span class="tutor-icon-search tutor-form-icon" area-hidden="true"></span>
				<input type="text" class="tutor-form-control" name="keyword" placeholder="<?php esc_html_e( 'Search any instructor...', 'tutor' ); ?>" tutor-instructors-filter-search />
			</div>
			<div class="tutor-d-flex tutor-align-center tutor-mb-24">
				<div class="tutor-mr-16">
					<label for="tutor-instructor-relevant-sort" class="tutor-fs-6 tutor-color-muted">
						<?php esc_html_e( 'Sort by', 'tutor' ); ?>
					</label>
				</div>
				<div>
					<select class="tutor-form-control" id="tutor-instructor-relevant-sort" tutor-instructors-filter-sort>
						<?php foreach ( $short_by as $k => $v ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>">
								<?php echo esc_html( $v ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<div tutor-instructors-content>
				<?php echo $content;//phpcs:ignore ?>
			</div>
		</main>
	</div>
</div>

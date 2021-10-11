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
$show_more        = false;
$short_by         = array(
	'relevant' => __( 'Relevant', 'tutor' ),
	'new'      => __( 'New', 'tutor' ),
	'popular'  => __( 'Popular', 'tutor' ),
);
if ( $total_categories && $total_categories > $limit ) {
	$show_more = true;
}
?>

<div class="tutor-instructor-filter" 
	<?php
	foreach ( $attributes as $key => $value ) {
		echo 'data-' . $key . '="' . $value . '" ';
	}
	?>
	>
	<div class="tutor-instructor-filter-sidebar">
		<div class="tutor-instructor-customize-wrapper">
			<div class="tutor-instructor-filters">
				<i class="ttr ttr-customize-filled color-text-brand"></i>
				<span class="text-medium-h5 color-text-primary">
					<?php esc_html_e( 'Filters', 'tutor' ); ?>
				</span>
			</div>
			<div class="tutor-instructor-customize-clear clear-instructor-filter">
				<i class="tutor-icon-line-cross design-dark"></i>
				<span className="color-text-hints text-regular-body">
					<?php esc_html_e( 'Clear', 'tutor' ); ?>
				</span>
			</div>
		</div>
		<div class="tutor-instructor-categories-wrapper">
			<div>
				<div class="tutor-category-text">
					<span class="color-text-title">
						<?php esc_html_e( 'Category', 'tutor' ); ?>
					</span>
				</div>
				<br/>
			</div>
			<div class="course-category-filter <?php esc_attr_e( $show_more ? 'tutor-instructor-plus tutor-show-more-blur' : '' ); ?>">
				<?php
				foreach ( $categories as $category ) {
					$category_id = $category->term_id;
					?>
						<div class="tutor-form-check tutor-mb-25">
							<input
								id="tutor-instructor-checkbox-<?php esc_attr_e( $category_id ); ?>"
								type="checkbox"
								class="tutor-form-check-input tutor-form-check-square"
								name="category"
								value="<?php esc_attr_e( $category_id ); ?>"/>
							<label for="tutor-instructor-checkbox-<?php esc_attr_e( $category_id ); ?>" class="color-text-title text-medium-caption">
							 <?php esc_html_e( $category->name ); ?>
							</label>
						</div>
						<?php
				}
				?>
			</div>
			<?php if ( $show_more ) : ?>
				<div class="tutor-instructor-category-show-more">
					<div class="text-medium-caption" data-id="<?php esc_attr_e( $category_id ); ?>">
						<i class="ttr ttr-plus-bold-filled color-text-brand"></i>
						<span class="text-subsued text-medium-caption">
							<?php esc_html_e( 'Show More', 'tutor' ); ?>
							<span class="tutor-show-more-loading"></span>
						</span>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<div class="tutor-instructor-ratings-wrapper">
			<div class="tutor-instructor-rating-title">
				<span class="text-title">
					<?php esc_html_e( 'Ratings', 'tutor' ); ?>
				</span>
			</div>
			<div class="tutor-instructor-rating-range-wrapper">
				<div class="tutor-instructor-ratings">
					<?php for ( $i = 1; $i < 6; $i++ ) : ?>
						<i class="ttr ttr-star-line-filled color-black-fill-20" data-value="<?php echo $i; ?>"></i>
					<?php endfor; ?> 
				</div>
				<span class="text-subsued text-medium-body tutor-instructor-rating-filter"></span>   
			</div>
		</div>
	</div>
	<div class="tutor-instructor-filter-result">
		<div class="filter-pc">
			<div class="keyword-field">
				<i class="tutor-icon-magnifying-glass-1"></i>
				<input type="text" name="keyword" placeholder="<?php _e( 'Search any instructor...', 'tutor' ); ?>"/>
			</div>
		</div>
		<div class="tutor-instructor-relevant-short-wrapper tutor-mb-30">
			<div class="tutor-instructor-form-group">
				<label for="tutor-instructor-relevant-sort" class="text-hints text-regular-body">
					<?php _e( 'Short by', 'tutor-pro' ); ?>
				</label>
				<select class="text-title  text-regular-body" id="tutor-instructor-relevant-sort">
					<?php foreach ( $short_by as $k => $v ) : ?>
						<option value="<?php esc_attr_e( $k ); ?>">
							<?php esc_html_e( $v ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="filter-mobile">
			<div class="mobile-filter-container">
				<div class="keyword-field mobile-screen">
					<i class="tutor-icon-magnifying-glass-1"></i>
					<input type="text" name="keyword" placeholder="<?php _e( 'Search any instructor...', 'tutor' ); ?>"/>
				</div>
				<i class="tutor-icon-filter-tool-black-shape"></i>
			</div>
			<div class="mobile-filter-popup">
				<div>
					<div class="tutor-category-text">
						<div class="expand-instructor-filter"></div>
						<span>Category</span>
						<span class="clear-instructor-filter">
							<i class="tutor-icon-line-cross"></i> <span><?php esc_html_e( 'Clear All', 'tutor' ); ?></span>
						</span>
					</div>

					<div>
					<?php
					foreach ( $categories as $category ) {
						$category_id = $category->term_id;
						?>
						<div class="tutor-form-check tutor-mb-25">
							<input
								id="tutor-instructor-checkbox-id-<?php esc_attr_e( $category_id ); ?>"
								type="checkbox"
								class="tutor-form-check-input tutor-form-check-square"
								name="category"
								value="<?php esc_attr_e( $category->term_id ); ?>"/>
							<label for="tutor-instructor-checkbox-id-<?php esc_attr_e( $category_id ); ?>">
								 <?php esc_html_e( $category->name ); ?>
							</label>
						</div>
						<?php
					}
					?>
					<?php if ( $show_more ) : ?>
						<div class="tutor-instructor-category-show-more tutor-mb-25">
							<div class="text-medium-caption" data-id="<?php esc_attr_e( $category_id ); ?>">
								<i class="ttr ttr-plus-bold-filled color-text-brand"></i>
								<span class="text-subsued text-medium-caption">
									<?php esc_html_e( 'Show More', 'tutor' ); ?>
									<span class="tutor-show-more-loading"></span>
								</span>
							</div>
						</div>
					<?php endif; ?>
					</div>

					<div>
						<button class="tutor-btn btn-sm">
							<?php esc_html_e( 'Apply Filter', 'tutor' ); ?>
						</button>
					</div>
				</div>
			</div>
			<div class="selected-cate-list">

			</div>
		</div>
		<div class="filter-result-container">
			<?php echo $content; ?>
		</div>
	</div>
</div>

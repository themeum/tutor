<?php

use TUTOR\Input;

if ( isset( $data ) ) : ?>
	<div class="tutor-px-20">
		<?php 
			$is_bulk_action = isset( $data['bulk_action'] ) && true === $data['bulk_action']; 
			$admin_class = $is_bulk_action ? 'tutor-col-xxxl-2 tutor-col-md-3 tutor-col-sm-6 tutor-mt-xxxl-0 tutor-mt-12': 'tutor-col';
			$frontend_class = 'tutor-col-lg-3 tutor-col-md-6 tutor-mt-lg-0 tutor-mt-12';
		?>
		<div class="tutor-wp-dashboard-filter tutor-align-center <?php echo $is_bulk_action ? "with-bulk-action tutor-row" : (is_admin() ? "without-bulk-action tutor-row": "frontend tutor-row")?>">
			<?php if ( isset( $data['bulk_action'] ) && true === $data['bulk_action'] ) : ?>
				<div class="tutor-wp-dashboard-filter-items <?php echo is_admin() ? "tutor-col-xxxl-2 tutor-col-6" : ""?>">
					<form id="tutor-admin-bulk-action-form" action method="post">
						<input type="hidden" name="action" value="<?php echo esc_html( $data['ajax_action'] ); ?>" />
						<div class="tutor-d-flex">
							<div class="tutor-mr-12">
								<select name="bulk-action" title="Please select an action" class="tutor-form-select">
									<?php foreach ( $data['bulk_actions'] as $k => $v ) : ?>
										<option value="<?php echo esc_attr( $v['value'] ); ?>">
											<?php echo esc_html( $v['option'] ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
							<button class="tutor-btn tutor-btn-outline-primary" id="tutor-admin-bulk-action-btn" data-tutor-modal-target="tutor-bulk-confirm-popup">
								<?php esc_html_e( 'Apply', 'tutor' ); ?>
							</button>
						</div>
					</form>
				</div>
			<?php endif; ?>
			<?php if ( isset( $data['filters'] ) && true === $data['filters'] ) : ?>
				<?php
				$courses    = ( current_user_can( 'administrator' ) ) ? tutor_utils()->get_courses() : tutor_utils()->get_courses_by_instructor();
				$terms_arg  = array(
					'taxonomy' => 'course-category',
					'orderby'  => 'term_id',
					'order'    => 'DESC',
				);
				$categories = get_terms( $terms_arg );
				?>

					<div class="tutor-wp-dashboard-filter-item item-reset <?php echo $is_bulk_action ? "item-reset-admin tutor-col-xxxl-2 tutor-col-sm-6" : (is_admin() ? "tutor-col" : "tutor-col-lg-3 tutor-col-md-6")?> ">
						<label class="tutor-form-label">
						</label>
						<?php
						$page        = Input::get( 'page', '' );
						$sub_page    = Input::get( 'sub_page', '' );
						$current_tab = Input::get( 'tab', '' );
						if ( '' === $sub_page && '' !== $current_tab ) {
							$sub_page = $current_tab;
						}
						$url = '';
						/**
						 * Tab query param support added for reset link
						 *
						 * @since v2.1.0
						 */
						if ( '' === $sub_page && '' === $current_tab ) {
							$url = "?page=$page";
						} elseif ( '' === $current_tab ) {
							$url = "?page=$page&sub_page=$sub_page";
						} else {
							$url = "?page=$page&tab=$current_tab";
						}

						?>
						<a class="tutor-btn tutor-btn-ghost" href="<?php echo esc_url( $url ); ?>">
							<i class="tutor-icon-refresh tutor-mr-8" area-hidden="true"></i> <?php esc_html_e( 'Reset', 'tutor' ); ?>
						</a>
					</div>
					<?php
					$course_id     = Input::get( 'course-id', 0, Input::TYPE_INT );
					$order         = Input::get( 'order', 'DESC' );
					$date          = Input::get( 'date', '' );
					$search        = Input::get( 'search', '' );
					$category_slug = Input::get( 'category', '' );
					?>
					<?php if ( isset( $data['course_filter'] ) && true === $data['course_filter'] ) : ?>
						<div class="tutor-wp-dashboard-filter-item <?php echo is_admin() ? "{$admin_class}" : "{$frontend_class}"?>">
							<label class="tutor-form-label">
								<?php esc_html_e( 'Course', 'tutor' ); ?>
							</label>
							<select class="tutor-form-select" id="tutor-backend-filter-course">
								<?php if ( count( $courses ) ) : ?>
									<option value="">
										<?php esc_html_e( 'All Courses', 'tutor' ); ?>
									</option>
									<?php foreach ( $courses as $course ) : ?>
										<option value="<?php echo esc_attr( $course->ID ); ?>" <?php selected( $course_id, $course->ID, 'selected' ); ?>>
											<?php echo esc_html( $course->post_title ); ?>
										</option>
									<?php endforeach; ?>
								<?php else : ?>
									<option value=""><?php esc_html_e( 'No course found', 'tutor' ); ?></option>
								<?php endif; ?>
							</select>
						</div>
					<?php endif; ?>
					<?php if ( isset( $data['category_filter'] ) && true === $data['category_filter'] ) : ?>
						<div class="tutor-wp-dashboard-filter-item <?php echo is_admin() ? "{$admin_class}" : "{$frontend_class}"?>">
							<label class="tutor-form-label">
								<?php esc_html_e( 'Category', 'tutor' ); ?>
							</label>
							<select class="tutor-form-select" id="tutor-backend-filter-category">
								<?php if ( count( $categories ) ) : ?>
									<option value="">
										<?php esc_html_e( 'All Category', 'tutor' ); ?>
									</option>
									<?php foreach ( $categories as $category ) : ?>
										<option value="<?php echo esc_attr( $category->slug ); ?>" <?php selected( $category_slug, $category->slug, 'selected' ); ?>>
											<?php echo esc_html( $category->name ); ?>
										</option>
									<?php endforeach; ?>
								<?php else : ?>
									<option value=""><?php esc_html_e( 'No record found', 'tutor' ); ?></option>
								<?php endif; ?>
							</select>
						</div>
					<?php endif; ?>

					<?php if ( ! isset( $data['sort_by'] ) || $data['sort_by'] == true ) : ?>
						<div class="tutor-wp-dashboard-filter-item <?php echo is_admin() ? "{$admin_class}" : "{$frontend_class}"?>">
							<label class="tutor-form-label">
								<?php esc_html_e( 'Sort By', 'tutor' ); ?>
							</label>
							<select class="tutor-form-select" id="tutor-backend-filter-order" data-search="no">
								<option value="DESC" <?php selected( $order, 'DESC', 'selected' ); ?>>
									<?php esc_html_e( 'DESC', 'tutor' ); ?>
								</option>
								<option value="ASC" <?php selected( $order, 'ASC', 'selected' ); ?>>
									<?php esc_html_e( 'ASC', 'tutor' ); ?>
								</option>
							</select>
						</div>
					<?php endif; ?>
					<div class="tutor-wp-dashboard-filter-item <?php echo is_admin() ? "{$admin_class}" : "{$frontend_class}"?>">
						<label class="tutor-form-label">
							<?php esc_html_e( 'Date', 'tutor' ); ?>
						</label>
						<div class="tutor-v2-date-picker"></div>
					</div>
					<div class="tutor-wp-dashboard-filter-item <?php echo is_admin() ? "{$admin_class}" : "{$frontend_class}"?>">
						<form action="" method="get" id="tutor-admin-search-filter-form">
							<label class="tutor-form-label">
								<?php esc_html_e( 'Search', 'tutor' ); ?>
							</label>
							<div class="tutor-form-wrap">
								<span class="tutor-form-icon"><span class="tutor-icon-search" area-hidden="true"></span></span>
								<input type="search" class="tutor-form-control" id="tutor-backend-filter-search" name="search" placeholder="<?php esc_html_e( 'Search...' ); ?>" value="<?php echo esc_html( wp_unslash( $search ) ); ?>" />
							</div>
						</form>
					</div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>

<?php
tutor_load_template_from_custom_path( tutor()->path . 'views/elements/bulk-confirm-popup.php' );
?>

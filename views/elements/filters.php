<?php if ( isset( $data ) ) : ?>
	<div class="tutor-admin-page-filters" style="display: flex; justify-content: space-between;">
		<?php if ( $data['bulk_action'] ) : ?>
			<div class="tutor-admin-bulk-action-wrapper">
				<form action="" method="post" id="tutor-admin-bulk-action-form">
					<input type="hidden" name="action" value="<?php esc_html_e( $data['ajax_action'] ); ?>">
					<div class="tutor-bulk-action-group">
						<select name="bulk-action" id="tutor-admin-bulk-action" required>
							<?php foreach ( $data['bulk_actions'] as $k => $v ) : ?>
								<option value="<?php esc_attr_e( $v['value'] ); ?>">
									<?php esc_html_e( $v['option'] ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<button type="button" id="tutor-admin-bulk-action-btn" class="tutor-btn" data-tutor-modal-target="tutor-bulk-confirm-popup">
							<?php esc_html_e( 'Apply', 'tutor' ); ?>
						</button>
					</div>
				</form>
			</div>
		<?php endif; ?>
		<?php if ( isset( $data['filters'] ) && true === $data['filters'] ) : ?>
			<?php
				$courses = ( current_user_can( 'administrator' ) ) ? tutils()->get_courses() : tutils()->get_courses_by_instructor();
			?>
			<div class="tutor-admin-page-filter-wrapper" style="display: flex;">
				<?php 
					$course_id = isset( $_GET['course-id'] ) ? esc_html__( $_GET['course-id'] ) : '';
					$order     = isset( $_GET['order'] ) ?  esc_html__( $_GET['order'] ) : '';
					$date      = isset( $_GET['date'] ) ?  esc_html__( $_GET['date'] ) : '';
					$search    = isset( $_GET['search'] ) ?  esc_html__( $_GET['search'] ) : '';
				?>
				<div class="tutor-form-group">
					<label for="tutor-backend-filter-course">
						<?php esc_html_e( 'Course', 'tutor' ); ?>
					</label>
					<select type="text" id="tutor-backend-filter-course" name="tutor-backend-filter-course">
					<?php if ( $courses ) : ?>
						<option value="">
							<?php esc_html_e( 'All Courses', 'tutor' ); ?>
						</option>
						<?php foreach ( $courses as $course ) : ?>
						<option value="<?php echo esc_attr( $course->ID ); ?>" <?php selected( $course_id, $course->ID, 'selected' ); ?>>
							<?php echo $course->post_title; ?>
						</option>
					<?php endforeach; ?>
				<?php else : ?>
					<option value=""><?php esc_html_e( 'No course found', 'tutor' ); ?></option>
				<?php endif; ?>
					</select>
				</div>
				<div class="tutor-form-group">
					<label for="tutor-backend-filter-order">
						<?php esc_html_e( 'Sort By', 'tutor' ); ?>
					</label>
					<select type="text" id="tutor-backend-filter-order" name="tutor-backend-filter-course">
						<option value="DESC" <?php selected( $order, 'DESC', 'selected' ); ?>>
							<?php esc_html_e( 'DESC', 'tutor' ); ?>
						</option>
						<option value="ASC" <?php selected( $order, 'ASC', 'selected' ); ?>>
							<?php esc_html_e( 'ASC', 'tutor' ); ?>
						</option>
					</select>
				</div>
				<div class="tutor-form-group">
					<label for="tutor-backend-filter-date">
						<?php esc_html_e( 'Date', 'tutor' ); ?>
					</label>
					<input type="date" name="tutor-backend-filter-date" id="tutor-backend-filter-date" value="<?php esc_html_e( tutor_get_formated_date( get_option( 'date_format' ), $date )); ?>" value="<?php esc_attr_e( $date ); ?>">
				</div>
				<form action="" method="get" id="tutor-admin-search-filter-form">
					<div class="tutor-form-group">
						<label for="tutor-backend-filter-search">
							<?php esc_html_e( 'Search', 'tutor' ); ?>
						</label>
						<input type="search" id="tutor-backend-filter-search" name="tutor-backend-filter-search" placeholder="<?php esc_html_e( 'Search...' ); ?>" value="<?php esc_html_e( $search ) ; ?>">
					</div>
				</form>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>
<?php 
	tutor_load_template_from_custom_path( esc_url( tutor()->path . 'views/elements/bulk-confirm-popup.php' ) );
?>

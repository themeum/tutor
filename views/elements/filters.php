<?php if (isset($data)) : ?>
	<div class="tutor-px-20">
		<div class="tutor-wp-dashboard-filter tutor-d-flex tutor-align-end tutor-justify-<?php echo esc_attr(isset($data['bulk_action']) && true === $data['bulk_action'] ? 'between' : 'end'); ?>">
			<?php if (isset($data['bulk_action']) && true === $data['bulk_action']) : ?>
				<div class="tutor-wp-dashboard-filter-items tutor-d-flex tutor-flex-xl-nowrap tutor-flex-wrap">
					<form id="tutor-admin-bulk-action-form" action method="post">
						<input type="hidden" name="action" value="<?php esc_html_e($data['ajax_action']); ?>" />
						<div class="tutor-d-flex">
							<div class="tutor-mr-12">
								<select name="bulk-action" title="Please select an action" class="tutor-form-select">
									<?php foreach ($data['bulk_actions'] as $k => $v) : ?>
										<option value="<?php echo esc_attr($v['value']); ?>">
											<?php echo esc_html($v['option']); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
							<button class="tutor-btn tutor-btn-outline-primary" id="tutor-admin-bulk-action-btn" data-tutor-modal-target="tutor-bulk-confirm-popup">
								<?php esc_html_e('Apply', 'tutor'); ?>
							</button>
						</div>
					</form>
				</div>
			<?php endif; ?>
			<?php if (isset($data['filters']) && true === $data['filters']) : ?>
				<?php
				$courses    = (current_user_can('administrator')) ? tutor_utils()->get_courses() : tutor_utils()->get_courses_by_instructor();
				$terms_arg  = array(
					'taxonomy' => 'course-category',
					'orderby'  => 'term_id',
					'order'    => 'DESC',
				);
				$categories = get_terms($terms_arg);
				?>

				<div class="tutor-wp-dashboard-filter-items tutor-d-flex tutor-flex-xl-nowrap tutor-flex-wrap">
					<div class="tutor-wp-dashboard-filter-item item-reset">
						<label class="tutor-form-label">
						</label>
						<?php
						$page     = isset($_GET['page']) ? $_GET['page'] : '';
						$sub_page = isset($_GET['sub_page']) ? $_GET['sub_page'] : '';
						$url      = '' === $sub_page ? "?page=$page" : "?page=$page&sub_page=$sub_page";

						?>
						<a class="tutor-btn tutor-btn-ghost tutor-mt-28" href="<?php echo esc_url($url); ?>">
							<i class="tutor-icon-refresh tutor-mr-8" area-hidden="true"></i> <?php esc_html_e('Reset', 'tutor'); ?>
						</a>
					</div>
					<?php
					$course_id     = isset($_GET['course-id']) ? esc_html__($_GET['course-id']) : '';
					$order         = isset($_GET['order']) ? esc_html__($_GET['order']) : '';
					$date          = isset($_GET['date']) ? esc_html__($_GET['date']) : '';
					$search        = isset($_GET['search']) ? esc_html__($_GET['search']) : '';
					$category_slug = isset($_GET['category']) ? esc_html__($_GET['category']) : '';
					?>
					<?php if (isset($data['course_filter']) && true === $data['course_filter']) : ?>
						<div class="tutor-wp-dashboard-filter-item">
							<label class="tutor-form-label">
								<?php esc_html_e('Course', 'tutor'); ?>
							</label>
							<select class="tutor-form-select" id="tutor-backend-filter-course">
								<?php if (count($courses)) : ?>
									<option value="">
										<?php esc_html_e('All Courses', 'tutor'); ?>
									</option>
									<?php foreach ($courses as $course) : ?>
										<option value="<?php echo esc_attr($course->ID); ?>" <?php selected($course_id, $course->ID, 'selected'); ?>>
											<?php echo $course->post_title; ?>
										</option>
									<?php endforeach; ?>
								<?php else : ?>
									<option value=""><?php esc_html_e('No course found', 'tutor'); ?></option>
								<?php endif; ?>
							</select>
						</div>
					<?php endif; ?>
					<?php if (isset($data['category_filter']) && true === $data['category_filter']) : ?>
						<div class="tutor-wp-dashboard-filter-item">
							<label class="tutor-form-label">
								<?php esc_html_e('Category', 'tutor'); ?>
							</label>
							<select class="tutor-form-select" id="tutor-backend-filter-category">
								<?php if (count($categories)) : ?>
									<option value="">
										<?php esc_html_e('All Category', 'tutor'); ?>
									</option>
									<?php foreach ($categories as $category) : ?>
										<option value="<?php echo esc_attr($category->slug); ?>" <?php selected($category_slug, $category->slug, 'selected'); ?>>
											<?php echo esc_html($category->name); ?>
										</option>
									<?php endforeach; ?>
								<?php else : ?>
									<option value=""><?php esc_html_e('No record found', 'tutor'); ?></option>
								<?php endif; ?>
							</select>
						</div>
					<?php endif; ?>

					<?php if (!isset($data['sort_by']) || $data['sort_by'] == true) : ?>
						<div class="tutor-wp-dashboard-filter-item">
							<label class="tutor-form-label">
								<?php esc_html_e('Sort By', 'tutor'); ?>
							</label>
							<select class="tutor-form-select" id="tutor-backend-filter-order" data-search="no">
								<option value="DESC" <?php selected($order, 'DESC', 'selected'); ?>>
									<?php esc_html_e('DESC', 'tutor'); ?>
								</option>
								<option value="ASC" <?php selected($order, 'ASC', 'selected'); ?>>
									<?php esc_html_e('ASC', 'tutor'); ?>
								</option>
							</select>
						</div>
					<?php endif; ?>
					<div class="tutor-wp-dashboard-filter-item">
						<label class="tutor-form-label">
							<?php esc_html_e('Date', 'tutor'); ?>
						</label>
						<div class="tutor-v2-date-picker"></div>
					</div>
					<div class="tutor-wp-dashboard-filter-item">
						<form action="" method="get" id="tutor-admin-search-filter-form">
							<label class="tutor-form-label">
								<?php esc_html_e('Search', 'tutor'); ?>
							</label>
							<div class="tutor-form-wrap">
								<span class="tutor-form-icon"><span class="tutor-icon-search" area-hidden="true"></span></span>
								<input type="search" class="tutor-form-control" id="tutor-backend-filter-search" name="search" placeholder="<?php esc_html_e('Search...'); ?>" value="<?php esc_html_e(wp_unslash($search)); ?>" />
							</div>
						</form>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>

<?php
tutor_load_template_from_custom_path(tutor()->path . 'views/elements/bulk-confirm-popup.php');
include tutor()->path . 'views/elements/elements_style.php';
?>
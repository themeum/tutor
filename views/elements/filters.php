<?php if (isset($data)): ?>
<!-- Filter Wrap -->
<div class="tutor-wp-dashboard-filter d-flex align-items-end justify-content-between tutor-mr-20" style="display: flex; justify-content: space-between;">
	
	<!-- Bulk Action -->
	<?php if ($data['bulk_action']): ?>
	<div class="tutor-wp-dashboard-filter-items">
		<form action="" method="post" id="tutor-admin-bulk-action-form">
			<input type="hidden" name="action" value="<?php esc_html_e($data['ajax_action']); ?>">
			<div class="tutor-form-select-with-btn">
				<select title="Please select a color" class="tutor-form-select tutor-form-control-sm"  name="bulk-action" id="tutor-admin-bulk-action">
					<?php foreach ($data['bulk_actions'] as $k => $v): ?>
					<option value="<?php esc_attr_e($v['value']); ?>">
						<?php esc_html_e($v['option']); ?>
					</option>
					<?php endforeach; ?>
				</select>
				<button class="tutor-btn tutor-btn-wordpress-outline tutor-no-hover tutor-btn-md" id="tutor-admin-bulk-action-btn" data-tutor-modal-target="tutor-bulk-confirm-popup">
					<?php esc_html_e('Apply', 'tutor'); ?>
				</button>
			</div>
		</form>
	</div>
	<?php endif; ?>
	<!-- Bulk Action /-->

	<!-- Filter Section -->
	<?php if (isset($data['filters']) && true === $data['filters']): ?>
	<?php
		$courses = (current_user_can('administrator')) ? tutils()->get_courses() : tutils() ->get_courses_by_instructor();
			$terms_arg = array(
				'taxonomy' => 'course-category',
				'orderby' => 'term_id',
				'order' => 'DESC'
			);
		$categories = get_terms($terms_arg);
	?>
	<div class="tutor-wp-dashboard-filter-items">

		<!-- Course -->
		<?php
			$course_id = isset($_GET['course-id']) ? esc_html__($_GET['course-id']) : '';
			$order = isset($_GET['order']) ? esc_html__($_GET['order']) : '';
			$date = isset($_GET['date']) ? esc_html__($_GET['date']) : '';
			$search = isset($_GET['search']) ? esc_html__($_GET['search']) : '';
			$category_slug = isset($_GET['category']) ? esc_html__($_GET['category']) : '';
		?>
		<?php if (isset($data['course_filter']) && true === $data['course_filter']): ?>
		<div class="">
			<label class="tutor-form-label">
				<?php esc_html_e('Course', 'tutor'); ?>
			</label>
			<select class="tutor-form-select tutor-form-control-sm" id="tutor-backend-filter-course" name="tutor-backend-filter-course">
				<?php if (count($courses)): ?>
					<option value="">
						<?php esc_html_e('All Courses', 'tutor'); ?>
					</option>
				<?php foreach ($courses as $course): ?>
					<option value="<?php echo esc_attr($course->ID); ?>" <?php selected($course_id, $course->ID, 'selected'); ?>>
						<?php echo $course->post_title; ?>
					</option>
				<?php endforeach; ?>
				<?php else: ?>
					<option value=""><?php esc_html_e('No course found', 'tutor'); ?></option>
				<?php endif; ?>
			</select>
		</div>
		<?php endif; ?>
		<!-- Course /-->
		
		<!-- Category -->
		<?php if (isset($data['category_filter']) && true === $data['category_filter']): ?>
		<div class="">
			<label class="tutor-form-label">
				<?php esc_html_e('Category', 'tutor'); ?>
			</label>
			<select class="tutor-form-select tutor-form-control-sm" id="tutor-backend-filter-category" name="tutor-backend-filter-category">
				<?php if (count($categories)): ?>
					<option value="">
						<?php esc_html_e('All Category', 'tutor'); ?>
					</option>
				<?php foreach ($categories as $category): ?>
					<option value="<?php echo esc_attr($category->slug); ?>" <?php selected($category_slug, $category->slug, 'selected'); ?>>
						<?php echo esc_html($category->name); ?>
					</option>
				<?php endforeach; ?>
				<?php else: ?>
					<option value=""><?php esc_html_e('No category found', 'tutor'); ?></option>
				<?php endif; ?>
			</select>
		</div>
		<?php endif; ?>
		<!-- Category /-->
		
		<!-- Order By -->
		<div class="">
			<label class="tutor-form-label">
				<?php esc_html_e('Sort By', 'tutor'); ?>
			</label>
			<select class="tutor-form-select tutor-form-control-sm" id="tutor-backend-filter-order" name="tutor-backend-filter-order">
				<option value="DESC" <?php selected($order, 'DESC', 'selected'); ?>>
					<?php esc_html_e('DESC', 'tutor'); ?>
				</option>
				<option value="ASC" <?php selected($order, 'ASC', 'selected'); ?>>
					<?php esc_html_e('ASC', 'tutor'); ?>
				</option>
			</select>
		</div>
		<!-- Order By /-->

		<!-- Date -->
		<div class="">
			<label class="tutor-form-label">
				<?php esc_html_e('Date', 'tutor'); ?>
			</label>
			<div class="tutor-input-group tutor-form-control-sm">
				<input type="date" class="tutor-form-control" placeholder="dd/mm/yyyy" id="tutor-backend-filter-date" name="tutor-backend-filter-date"/>
			</div>
		</div>
		<!-- Date /-->

		<!-- Search Form -->
		<form action="" method="get" id="tutor-admin-search-filter-form">
			<div class="">
				<label class="tutor-form-label">
					<?php esc_html_e('Search', 'tutor'); ?>
				</label>
				<div class="tutor-input-group tutor-form-control-has-icon tutor-form-control-sm">
					<span class="ttr-search-filled tutor-input-group-icon color-black-50"></span>
					<input type="search" class="tutor-form-control" id="tutor-backend-filter-search" name="tutor-backend-filter-search" placeholder="<?php esc_html_e('Search...'); ?>" value="<?php esc_html_e($search); ?>"/>
				</div>
			</div>
		</form>
		<!-- Search Form /-->
	</div>
	<!-- Filter Section /-->
	<?php endif; ?>
</div>
<!-- Filter Wrap /-->
<?php endif; ?>
<?php tutor_load_template_from_custom_path(esc_url(tutor()->path . 'views/elements/bulk-confirm-popup.php')); ?>

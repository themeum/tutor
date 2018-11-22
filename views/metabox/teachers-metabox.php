
<div class="dozent-course-teachers-metabox-wrap">
	<?php
	$teachers = dozent_utils()->get_teachers_by_course();
	?>

	<div class="dozent-course-available-teachers">
		<?php
		if ($teachers){
			foreach ($teachers as $teacher){
				?>
				<div id="added-teacher-id-<?php echo $teacher->ID; ?>" class="added-teacher-item added-teacher-item-<?php echo $teacher->ID; ?>" data-teacher-id="<?php echo $teacher->ID; ?>">
					<span class="teacher-icon"><i class="dashicons dashicons-admin-users"></i></span>
					<span class="teacher-name"> <?php echo $teacher->display_name; ?> </span>
					<span class="teacher-control">
						<a href="javascript:;" class="dozent-teacher-delete-btn"><i class="dashicons dashicons-trash"></i></a>
					</span>
				</div>
				<?php
			}
		}
		?>
	</div>

	<div class="dozent-add-teacher-button-wrap">
		<button type="button" class="button button-default dozent-add-teacher-btn"> <?php _e('Add Teacher', 'dozent'); ?> </button>
	</div>

</div>


<div class="dozent-modal-wrap dozent-teachers-modal-wrap">
	<div class="dozent-modal-content">
		<div class="modal-header">
			<div class="search-bar">
				<input type="text" class="dozent-modal-search-input" placeholder="<?php _e('Search teacher...'); ?>">
			</div>
			<div class="modal-close-wrap">
				<a href="javascript:;" class="modal-close-btn">&times;</a>
			</div>
		</div>
		<div class="modal-container"></div>
		<div class="modal-footer">
			<button type="button" class="button button-primary add_teacher_to_course_btn"><?php _e('Add Teachers', 'dozent'); ?></button>
		</div>
	</div>
</div>
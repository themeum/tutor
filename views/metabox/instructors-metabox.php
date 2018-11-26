
<div class="tutor-course-instructors-metabox-wrap">
	<?php
	$instructors = tutor_utils()->get_instructors_by_course();
	?>

	<div class="tutor-course-available-instructors">
		<?php
		if ($instructors){
			foreach ($instructors as $instructor){
				?>
				<div id="added-instructor-id-<?php echo $instructor->ID; ?>" class="added-instructor-item added-instructor-item-<?php echo $instructor->ID; ?>" data-instructor-id="<?php echo $instructor->ID; ?>">
					<span class="instructor-icon"><i class="dashicons dashicons-admin-users"></i></span>
					<span class="instructor-name"> <?php echo $instructor->display_name; ?> </span>
					<span class="instructor-control">
						<a href="javascript:;" class="tutor-instructor-delete-btn"><i class="dashicons dashicons-trash"></i></a>
					</span>
				</div>
				<?php
			}
		}
		?>
	</div>

	<div class="tutor-add-instructor-button-wrap">
		<p>
            <button type="button" class="button button-default tutor-add-instructor-btn"> <?php _e('Add Instructor', 'tutor'); ?> </button>
        </p>
	</div>

    <?php
    if ( ! defined('TUTOR_MT_VERSION')){
        echo '<p>'. sprintf( __('To add unlimited multiple instructors at your course, install %sTutor Multi Instructors%s addon ', 'tutor'), '<a href="https://www.themeum.com/product/tutor-multi-instructors" target="_blank">', "</a>" ) .'</p>';
    }
    ?>

</div>


<div class="tutor-modal-wrap tutor-instructors-modal-wrap">
	<div class="tutor-modal-content">
		<div class="modal-header">
			<div class="search-bar">
				<input type="text" class="tutor-modal-search-input" placeholder="<?php _e('Search instructor...'); ?>">
			</div>
			<div class="modal-close-wrap">
				<a href="javascript:;" class="modal-close-btn">&times;</a>
			</div>
		</div>
		<div class="modal-container"></div>
		<div class="modal-footer">
			<button type="button" class="button button-primary add_instructor_to_course_btn"><?php _e('Add Instructors', 'tutor'); ?></button>
		</div>
	</div>
</div>
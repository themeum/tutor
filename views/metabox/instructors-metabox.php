
<div class="tutor-course-instructors-metabox-wrap">
	<?php
	$instructors = tutor_utils()->get_instructors_by_course();
	?>

	<div class="tutor-course-available-instructors">
		<?php
		$t = wp_get_current_user();

		$currentInstructorHtml = '<div id="added-instructor-id-'.$t->ID.'" class="added-instructor-item added-instructor-item-'.$t->ID.'" data-instructor-id="'.$t->ID
              .'">
                    <span class="instructor-icon">'.get_avatar($t->ID, 45).'</span>
                    <span class="instructor-name"> '.$t->display_name.' </span>
                </div>';
        echo $currentInstructorHtml;

		if (is_array($instructors) && count($instructors)){
			foreach ($instructors as $instructor){
				if ($t->ID == $instructor->ID){
				    continue;
                }
				?>
				<div id="added-instructor-id-<?php echo $instructor->ID; ?>" class="added-instructor-item added-instructor-item-<?php echo $instructor->ID; ?>" data-instructor-id="<?php echo $instructor->ID; ?>">
					<span class="instructor-icon">
                        <?php echo get_avatar($instructor->ID, 30); ?>
                    </span>
					<span class="instructor-name"> <?php echo $instructor->display_name; ?> </span>
					<span class="instructor-control">
						<a href="javascript:;" class="tutor-instructor-delete-btn"><i class="tutor-icon-line-cross"></i></a>
					</span>
				</div>
				<?php
			}
		}
		?>
	</div>

	<div class="tutor-add-instructor-button-wrap">
        <button type="button" class="tutor-btn tutor-add-instructor-btn"> <i class="tutor-icon-add-friend"></i> <?php _e('Add More Instructor', 'tutor'); ?> </button>
	</div>

    <?php
   /* if ( ! defined('TUTOR_MT_VERSION')){
        echo '<p>'. sprintf( __('To add unlimited multiple instructors in your course, get %sTutor LMS Pro%s addon ', 'tutor'), '<a href="https://www.themeum.com/product/tutor-lms" target="_blank">', "</a>" ) .'</p>';
    }*/
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
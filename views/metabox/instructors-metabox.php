<div class="tutor-course-instructors-metabox-wrap">
	<?php
	$instructors = tutor_utils()->get_instructors_by_course();
	?>

    <div class="tutor-course-available-instructors">
		<?php
        global $post;

        $instructor_crown_src = tutor()->url.'assets/images/crown.svg';
		if (is_array($instructors) && count($instructors)){
			foreach ($instructors as $instructor){
                $authorTag = '';
				if ($post->post_author == $instructor->ID){
					$authorTag = '<img src="'.$instructor_crown_src.'"><i class="instructor-name-tooltip" title="'. __("Author", "tutor") .'">'. __("Author", "tutor") .'</i>';
				}
				?>
                <div id="added-instructor-id-<?php echo $instructor->ID; ?>" class="added-instructor-item added-instructor-item-<?php echo $instructor->ID; ?>" data-instructor-id="<?php echo $instructor->ID; ?>">
					<span class="instructor-icon">
                        <?php echo get_avatar($instructor->ID, 30); ?>
                    </span>
                    <span class="instructor-name"> 
                        <?php echo $instructor->display_name.' '.$authorTag; ?> 
                        <span class="tutor-bs-d-block text-regular-small">
                            <?php echo $instructor->user_email; ?>
                        </span>
                    </span>
                    <span class="instructor-control">
						<a href="javascript:;" class="tutor-instructor-delete-btn tutor-action-icon">
                            <i class="tutor-icon-line-cross"></i>
                        </a>
					</span>
                </div>
				<?php
			}
		}
		?>
    </div>

    <button data-tutor-modal-target="tutor_course_instructor_modal" type="button" class="tutor-btn tutor-btn-tertiary tutor-is-outline tutor-btn-md tutor-add-instructor-btn"> 
        <i class="tutor-icon-add-friend tutor-mr-10"></i> 
        <?php _e('Add Instructor', 'tutor'); ?> 
    </button>
</div>

<div class="tutor-modal modal-sticky-header-footer" id="tutor_course_instructor_modal" data-course_id="<?php echo get_the_ID(); ?>">
    <span class="tutor-modal-overlay"></span>
    <div class="tutor-modal-root">
        <div class="tutor-modal-inner">
            <div class="tutor-modal-header">
                <h3 class="tutor-modal-title">
                    <?php _e('Add Instructor', 'tutor'); ?>
                </h3>
                <button data-tutor-modal-close class="tutor-modal-close">
                    <span class="las la-times"></span>
                </button>
            </div>
            
            <div class="tutor-modal-body-alt modal-container">
                <input type="text" class="tutor-form-control" placeholder="<?php _e( 'Search instructors...', 'tutor' ); ?>">
                <div class="tutor-search-result"></div>
                <div class="tutor-selected-result"></div>
            </div>

            <div class="tutor-modal-footer">
                <div class="tutor-bs-row">
                    <div class="tutor-bs-col">
                        <div class="tutor-btn-group">
                            <button type="submit" data-action="next" class="tutor-btn tutor-is-primary add_instructor_to_course_btn">
                                <?php _e('Save Changes', 'tutor'); ?>
                            </button>
                        </div>
                    </div>
                    <div class="tutor-bs-col-auto">
                        <button data-tutor-modal-close type="button" data-action="back" class="tutor-btn tutor-is-default">
                            <?php _e('Cancel', 'tutor'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
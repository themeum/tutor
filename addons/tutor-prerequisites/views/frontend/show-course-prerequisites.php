
<h4><?php _e('Course Prerequisite(s)'); ?></h4>

<div class="course-prerequisites-warning">
	<?php _e('Please note that this course has the follwoing prerequisites which must be completed before it can be accessed'); ?>
</div>


<div class="course-prerequisites-lists-wrap">
	<ul class="prerequisites-course-lists">
		<?php
		$savedPrerequisitesIDS = maybe_unserialize(get_post_meta(get_the_ID(), '_tutor_course_prerequisites_ids', true));
		if (is_array($savedPrerequisitesIDS) && count($savedPrerequisitesIDS)){
			foreach ($savedPrerequisitesIDS as $courseID){
				?>
                <li>
                    <a href="<?php echo get_the_permalink($courseID); ?>" class="prerequisites-course-item">
					<span class="prerequisites-course-feature-image">
						<?php echo get_the_post_thumbnail($courseID); ?>
					</span>

                        <span class="prerequisites-course-title">
						<?php echo get_the_title($courseID); ?>
					</span>

						<?php if (tutor_utils()->is_completed_course($courseID)){
							?>
                            <div class="is-complete-prerequisites-course"><i class="tutor-icon-mark"></i></div>
							<?php
						} ?>
                    </a>
                </li>
				<?php
			}
        }

		?>
	</ul>
</div>

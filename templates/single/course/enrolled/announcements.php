<?php
/**
 * Announcements
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

$announcements = tutor_utils()->get_announcements( get_the_ID() );
?>

<?php do_action('tutor_course/announcements/before'); ?>

<?php if(is_array($announcements) && count($announcements)): ?>
	<?php foreach($announcements as $announcement): ?>
		<div class="tutor-announcement bg-white tutor-mb-30">
			<div class="tutor-announcement-head tutor-color-text-primary bg-black-03 tutor-px-28 tutor-py-20">
				<div class="text-medium-h6">
					<?php echo $announcement->post_title; ?>
				</div>
				<div class="tutor-bs-d-flex tutor-mt-14">
					<div class="tutor-bs-d-flex tutor-mr-md-32 tutor-mr-10">
						<div class="tutor-avatar-circle tutor-26 tutor-mr-6">
							<img src="<?php echo get_avatar_url( $announcement->post_author ); ?>" alt="instructor avatar" />
						</div>
						<div class="text-medium-caption">
							<span class="text-regular-caption tutor-color-text-subsued">by</span> <?php echo get_userdata( $announcement->post_author )->display_name; ?>
						</div>
					</div>
					<div class="text-regular-caption">
						<?php _e( sprintf("%s ago", human_time_diff(strtotime($announcement->post_date)) ) , 'tutor' ); ?>
					</div>
				</div>
			</div>
			<div class="tutor-announcement-body tutor-px-28 tutor-py-24">
				<div class="text-regular-body tutor-color-text-subsued">
					<?php 
						echo tutor_utils()->announcement_content(wpautop(stripslashes($announcement->post_content))); 
					?>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
<?php else: ?>
	<div class="tutor-no-announcements">
		<div class="text-medium-h6 tutor-color-text-primary"><?php _e('No announcements posted yet.', 'tutor'); ?></div>
		<div class="text-regular-body tutor-color-text-subsued tutor-mt-7">
			<?php _e('The instructor hasn’t added any announcements to this course yet. Announcements are used to inform you of updates or additions to the course.', 'tutor'); ?>
		</div>
	</div>
<?php endif; ?>

<?php do_action( 'tutor_course/announcements/after' ); ?>

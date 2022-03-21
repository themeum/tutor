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

<?php do_action( 'tutor_course/announcements/before' ); ?>

<?php if ( is_array( $announcements ) && count( $announcements ) ) : ?>
	<?php foreach ( $announcements as $announcement ) : ?>
		<div class="tutor-announcement bg-white tutor-mb-32">
			<div class="tutor-announcement-head tutor-color-black bg-black-03 tutor-px-28 tutor-py-20">
				<div class="tutor-fs-6 tutor-fw-medium">
					<?php echo esc_html( $announcement->post_title ); ?>
				</div>
				<div class="tutor-d-flex tutor-mt-16">
					<div class="tutor-d-flex tutor-mr-md-32 tutor-mr-12">
						<div class="tutor-avatar-circle tutor-26 tutor-mr-8">
							<img src="<?php echo esc_url( get_avatar_url( $announcement->post_author ) ); ?>" alt="instructor avatar" />
						</div>
						<div class="tutor-fs-7 tutor-fw-medium">
							<span class="tutor-fs-7 tutor-color-black-60">by</span> <?php echo esc_html( get_userdata( $announcement->post_author )->display_name ); ?>
						</div>
					</div>
					<div class="tutor-fs-7">
						<?php echo esc_html( human_time_diff( time(), strtotime( $announcement->post_date_gmt ) ) . ' ago', 'tutor' ); ?>
					</div>
				</div>
			</div>
			<div class="tutor-announcement-body tutor-px-28 tutor-py-24">
				<div class="tutor-fs-6 tutor-color-black-60">
					<?php
						echo tutor_utils()->announcement_content( wpautop( stripslashes( $announcement->post_content ) ) );
					?>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
<?php else : ?>
	<div class="tutor-no-announcements">
		<div class="tutor-fs-6 tutor-fw-medium tutor-color-black"><?php _e( 'No announcements posted yet.', 'tutor' ); ?></div>
		<div class="tutor-fs-6 tutor-color-black-60 tutor-mt-16">
			<?php _e( 'The instructor hasn\'t added any announcements to this course yet. Announcements are used to inform you of updates or additions to the course.', 'tutor' ); ?>
		</div>
	</div>
<?php endif; ?>

<?php do_action( 'tutor_course/announcements/after' ); ?>

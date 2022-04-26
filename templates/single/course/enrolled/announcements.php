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
						<?php echo tutor_utils()->get_tutor_avatar( $announcement->post_author ); ?>
						<div class="tutor-fs-7 tutor-fw-medium">
							<span class="tutor-fs-7 tutor-color-secondary">by</span> <?php echo esc_html( get_userdata( $announcement->post_author )->display_name ); ?>
						</div>
					</div>
					<div class="tutor-fs-7">
						<?php echo esc_html( human_time_diff( time(), strtotime( $announcement->post_date_gmt ) ) . ' ago', 'tutor' ); ?>
					</div>
				</div>
			</div>
			<div class="tutor-announcement-body tutor-px-28 tutor-py-24">
				<div class="tutor-fs-6 tutor-color-secondary">
					<?php
						echo tutor_utils()->announcement_content( wpautop( stripslashes( $announcement->post_content ) ) );
					?>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
<?php else : ?>
	<div>
		<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
	</div>
<?php endif; ?>

<?php do_action( 'tutor_course/announcements/after' ); ?>

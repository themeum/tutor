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
		<div class="tutor-card tutor-announcement-card tutor-mb-32">
			<div class="tutor-card-header tutor-d-block tutor-bg-gray-10">
				<h3 class="tutor-card-title"><?php echo esc_html( $announcement->post_title ); ?></h3>

				<div class="tutor-meta tutor-mt-16">
					<div>
						<?php echo tutor_utils()->get_tutor_avatar( $announcement->post_author, 'sm' ); ?>
					</div>

					<div>
						<span class="tutor-meta-key"><?php esc_html_e('By', 'tutor') ?></span>
						<span class="tutor-meta-value"><?php echo esc_html( get_userdata( $announcement->post_author )->display_name ); ?></span>
					</div>

					<div>
						<?php echo esc_html( human_time_diff( time(), strtotime( $announcement->post_date_gmt ) ) . ' ago', 'tutor' ); ?>
					</div>
				</div>
			</div>

			<div class="tutor-card-body">
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

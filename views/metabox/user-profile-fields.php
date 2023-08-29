<?php
/**
 * Settings meta box template
 *
 * @package Tutor\Views
 * @subpackage Tutor\MetaBox
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

?>
<h2>
	<?php esc_html_e( 'Tutor Fields', 'tutor' ); ?>
</h2>

<?php
/**
 * Enqueue Media Scripts
 */
wp_enqueue_media();
?>

<table class="form-table">
	<?php do_action( 'tutor_backend_profile_fields_before' ); ?>
	<tr class="user-description-wrap">
		<th><label for="description"><?php esc_html_e( 'Job Title', 'tutor' ); ?></label></th>
		<td>
			<input type="text" name="_tutor_profile_job_title" id="_tutor_profile_job_title" value="<?php echo esc_attr( get_user_meta( $user->ID, '_tutor_profile_job_title', true ) ); ?>" class="regular-text" />
		</td>
	</tr>

	<tr class="user-description-wrap">
		<th><label for="description"><?php esc_html_e( 'Profile Bio', 'tutor' ); ?></label></th>
		<td>
			<?php
			$profile_bio = get_user_meta( $user->ID, '_tutor_profile_bio', true );
			wp_editor( $profile_bio, '_tutor_profile_bio', tutor_utils()->get_profile_bio_editor_config( '_tutor_profile_bio' ) );
			?>

			<p class="description"><?php esc_html_e( 'Write a little bit more about you, it will show publicly.', 'tutor' ); ?></p>
		</td>
	</tr>

	<tr class="user-description-wrap">
		<th><label for="description"><?php esc_html_e( 'Profile Photo', 'tutor' ); ?></label></th>
		<td>
			<div class="tutor-video-poster-wrap">
				<p class="video-poster-img">
					<?php
					$user_profile_photo = get_user_meta( $user->ID, '_tutor_profile_photo', true );
					if ( $user_profile_photo ) {
						echo '<img src="' . esc_url( wp_get_attachment_image_url( $user_profile_photo ) ) . '"/>';
					}
					?>
				</p>
				<input type="hidden" name="_tutor_profile_photo" value="<?php echo esc_attr( $user_profile_photo ); ?>">
				<button type="button" class="tutor_video_poster_upload_btn button button-primary"><?php esc_html_e( 'Upload', 'tutor' ); ?></button>
			</div>

			<input type="hidden" name="tutor_action" value="tutor_profile_update_by_wp">
		</td>
	</tr>
	<?php do_action( 'tutor_backend_profile_fields_after' ); ?>
</table>


<?php
/**
 * Lesson attachment meta box
 *
 * @package Tutor\Views
 * @subpackage Tutor\MetaBox
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

/**
 * Don't change it, it's supporting modal in other place
 * if get_the_ID() empty, then it's means we are passing $post variable from another place
 */
if ( get_the_ID() ) {
	global $post;
}

?>

<div class="tutor-mb-32">
	<label class="tutor-form-label"><?php esc_html_e( 'Upload exercise files to the Lesson', 'tutor' ); ?></label>
	<div class="tutor-mb-16 tutor-attachments-metabox">
		<?php
			$attachments = tutor_utils()->get_attachments( $post->ID );
			tutor_load_template_from_custom_path(
				tutor()->path . '/views/fragments/attachments.php',
				array(
					'name'        => 'tutor_attachments[]',
					'attachments' => $attachments,
					'add_button'  => true,
				),
				false
			);
			?>
	</div>
</div>

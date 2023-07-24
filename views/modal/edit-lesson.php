<?php
/**
 * Lesson Modal Form
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

use TUTOR\Input;

?>
<form class="tutor_lesson_modal_form">
	<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
	<input type="hidden" name="action" value="tutor_modal_create_or_update_lesson">
	<input type="hidden" name="lesson_id" value="<?php echo esc_attr( $post->ID ); ?>">
	<input type="hidden" name="current_topic_id" value="<?php echo esc_attr( $topic_id ); ?>">

	<?php do_action( 'tutor_lesson_edit_modal_form_before', $post ); ?>

	<div class="tutor-mb-32">
		<label class="tutor-form-label"><?php esc_html_e( 'Lesson Name', 'tutor' ); ?></label>
		<input type="text" name="lesson_title" class="tutor-form-control" value="<?php echo esc_attr( stripslashes( $post->post_title ) ); ?>"/>
		<div class="tutor-form-feedback">
			<i class="tutor-icon-circle-info-o tutor-form-feedback-icon"></i>
			<div><?php esc_html_e( 'Lesson titles are displayed publicly wherever required.', 'tutor' ); ?></div>
		</div>
	</div>

	<div class="tutor-mb-32">
		<label class="tutor-form-label">
			<?php
			esc_html_e( 'Lesson Content', 'tutor' );

			if ( get_tutor_option( 'enable_lesson_classic_editor' ) ) {
				?>
				<a class="tutor-btn tutor-btn-link tutor-ml-12" 
					target="_blank" 
					href="<?php echo esc_url( get_admin_url() . 'post.php?post=' . esc_attr( $post->ID ) . '&action=edit' ); ?>" 
					data-lesson-id="<?php echo esc_attr( $post->ID ); ?>" 
					onclick="tutorLessonWPEditor(event)">
					<i class="tutor-icon-edit tutor-mr-8"></i> <?php echo esc_html_e( 'WP Editor', 'tutor' ); ?>
				</a>
				<?php
			}
			?>
		</label>

		<?php
			/**
			 * Allow iframe inside lesson modal
			 *
			 * @since 2.1.6
			 */
			add_filter( 'wp_kses_allowed_html', Input::class . '::allow_iframe', 10, 2 );
			$sanitized_content = wp_kses_post( wp_unslash( str_replace( 'data-mce-style', 'style', $post->post_content ) ) );
			wp_editor( $sanitized_content, 'tutor_lesson_modal_editor', array( 'editor_height' => 150 ) );
		?>

		<div class="tutor-form-feedback">
			<i class="tutor-icon-circle-info-o tutor-form-feedback-icon"></i>
			<div><?php esc_html_e( 'The idea of a summary is a short text to prepare students for the activities within the topic or week. The text is shown on the course page under the topic name.', 'tutor' ); ?></div>
		</div>
	</div>

	<div class="tutor-mb-32">
		<label class="tutor-form-label"><?php esc_html_e( 'Feature Image', 'tutor' ); ?></label>
		<?php
		$lesson_thumbnail_id = '';
		if ( has_post_thumbnail( $post->ID ) ) {
			$lesson_thumbnail_id = get_post_meta( $post->ID, '_thumbnail_id', true );
		}

		tutor_load_template_from_custom_path(
			tutor()->path . '/views/fragments/thumbnail-uploader.php',
			array(
				'media_id'   => $lesson_thumbnail_id,
				'input_name' => '_lesson_thumbnail_id',
			),
			false
		);
		?>
	</div>

	<?php
		require tutor()->path . 'views/metabox/video-metabox.php';
		do_action( 'tutor_lesson_edit_modal_after_video' );

		require tutor()->path . 'views/metabox/lesson-attachments-metabox.php';
		do_action( 'tutor_lesson_edit_modal_after_attachment' );

		do_action( 'tutor_lesson_edit_modal_form_after', $post );
	?>
</form>
<script>
	/**
	 * Without lesson ID don't redirect user to the edit
	 * 
	 * @since v2.1.1
	 */
	function tutorLessonWPEditor(e) {
		e.preventDefault();
		const currentTarget = e.currentTarget;
		lessonId = currentTarget.dataset.lessonId;
		if (lessonId == 0) {
			tutor_toast('Warning', 'You can access and edit this Lesson with WP Editor only when you update this Lesson at first.', 'warning');
			return;
		} else {
			window.open(currentTarget.href, '_blank');
		}
	}
</script>

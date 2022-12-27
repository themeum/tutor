<?php
/**
 * Lesson meta box
 *
 * @package Tutor\Views
 * @subpackage Tutor\MetaBox
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

?>
<div class="tutor-option-field-row">
	<div class="tutor-option-field-label">
		<label for=""><?php esc_html_e( 'Select Course', 'tutor' ); ?></label>
	</div>
	<div class="tutor-option-field">
		<?php
		$courses = tutor_utils()->get_courses_for_instructors();
		?>

		<select name="selected_course" class="tutor_select2 no-tutor-dropdown">
			<option value=""><?php esc_html_e( 'Select a course', 'tutor' ); ?></option>

			<?php
			$course_id = tutor_utils()->get_course_id_by( 'lesson', get_the_ID() );
			foreach ( $courses as $course ) :
				?>
			<option value="<?php echo esc_attr( $course->ID ); ?>" <?php selected( $course->ID, $course_id ); ?>>
				<?php echo esc_html( $course->post_title ); ?>
			</option>
				<?php
			endforeach;
			?>
		</select>

		<p class="desc">
			<?php esc_html_e( 'Choose the course for this lesson', 'tutor' ); ?>
		</p>
	</div>
</div>

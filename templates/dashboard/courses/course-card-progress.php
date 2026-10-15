<?php
/**
 * Course Card Progress Template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Enrolled_Courses
 * @author Themeum
 *
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Progress;

if ( $course_progress['completed_count'] <= 0 && $course_progress['total_count'] <= 0 ) {
	return;
}

$progress_message_label = isset( $progress_message_label ) ? $progress_message_label : _n( 'lesson', 'lessons', (int) $course_progress['total_count'], 'tutor' );
?>
<div class="tutor-progress-card-progress">
	<?php if ( $course_progress['total_count'] > 0 ) : ?>
		<div class="tutor-progress-card-details">
			<?php
			printf(
				esc_html(
					// translators: %1$s is the completed count, %2$s is the total count, %3$s is the label (lesson/lessons or course/courses).
					_n(
						'%1$s of %2$s %3$s',
						'%1$s of %2$s %3$s',
						(int) $course_progress['total_count'],
						'tutor'
					)
				),
				esc_html( $course_progress['completed_count'] ),
				esc_html( $course_progress['total_count'] ),
				esc_html( $progress_message_label )
			);
			?>
			<span class="tutor-progress-card-separator">•</span>
			<?php
				printf(
					// translators: %1$s is the completed percent.
					esc_html__( '%1$s%% Complete', 'tutor' ),
					esc_html( $course_progress['completed_percent'] )
				);
			?>
		</div>
	<?php endif; ?>
	<?php if ( $course_progress['completed_percent'] >= 0 ) : ?>
		<div class="tutor-progress-card-bar">
			<?php
				Progress::make()
					->value( $course_progress['completed_percent'] )
					->type( 'bar' )
					->render();
			?>
		</div>
	<?php endif; ?>
</div>

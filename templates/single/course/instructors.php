<?php
/**
 * Template for displaying course instructors/ instructor
 *
 * @package Tutor\Templates
 * @subpackage Single\Course
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

use TUTOR\Instructors_List;

do_action( 'tutor_course/single/enrolled/before/instructors' );

$instructors = tutor_utils()->get_instructors_by_course();

if ( $instructors && count( $instructors ) ) : ?>
<div class="tutor-course-details-instructors">
	<h3 class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mb-16">
		<?php esc_html_e( 'A course by', 'tutor' ); ?>
	</h3>

	<?php foreach ( $instructors as $key => $instructor ) : ?>
		<div class="tutor-d-flex tutor-align-center<?php echo ( count( $instructors ) - 1 ) != $key ? ' tutor-mb-24' : ''; ?>">
			<div class="tutor-d-flex tutor-mr-16">
				<?php
				echo wp_kses(
					tutor_utils()->get_tutor_avatar( $instructor, 'md' ),
					tutor_utils()->allowed_avatar_tags()
				);
				?>
			</div>

			<div>
				<a class="tutor-fs-6 tutor-fw-bold tutor-color-black" href="<?php echo esc_url( tutor_utils()->profile_url( $instructor->ID, true ) ); ?>">
					<?php echo esc_html( $instructor->display_name ); ?>
				</a>

				<?php if ( ! empty( $instructor->tutor_profile_job_title ) ) : ?>
					<div class="tutor-instructor-designation tutor-fs-7 tutor-color-muted">
						<?php echo esc_html( $instructor->tutor_profile_job_title ); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
	<?php
endif;

do_action( 'tutor_course/single/enrolled/after/instructors' );

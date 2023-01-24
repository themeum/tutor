<?php
/**
 * Tutor instructor
 *
 * @package Tutor\Templates
 * @subpackage Shortcode
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

use Tutor\Models\CourseModel;

?>
<div class="tutor-pagination-wrapper-replaceable tutor-instructor-list-wrapper">
	<?php if ( count( $instructors ) ) : ?>
		<div class="tutor-instructor-list">
			<div class="tutor-grid tutor-grid-<?php echo esc_attr( $column_count ); ?>">
				<?php foreach ( $instructors as $instructor ) : ?>
					<?php
						$instructor->course_count = CourseModel::get_course_count_by_instructor( $instructor->ID );
						$instructor->ratings      = tutor_utils()->get_instructor_ratings( $instructor->ID );
						tutor_load_template(
							'instructor.' . $layout,
							array(
								'instructor' => $instructor,
							)
						);
					?>
				<?php endforeach; ?>
			</div>
		</div>
	<?php else : ?>
		<?php tutor_utils()->tutor_empty_state( 'No Instructor Found', 'tutor' ); ?>
	<?php endif; ?>

	<?php
		// @todo: convert to pagination
	if ( $current_page > 1 || $instructors_count > $limit ) {
		$pagination_data = array(
			'total_items' => $instructors_count,
			'per_page'    => $limit,
			'paged'       => $current_page,
			'ajax'        => array_merge(
				$filter,
				array(
					'loading_container' => '.tutor-instructor-list-wrapper',
					'action'            => 'load_filtered_instructor',
				)
			),
		);

		tutor_load_template_from_custom_path(
			tutor()->path . 'templates/dashboard/elements/pagination.php',
			$pagination_data
		);
	}
	?>
</div>

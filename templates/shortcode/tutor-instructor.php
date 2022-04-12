<div class="tutor-pagination-wrapper-replacable tutor-instructor-list-wrapper">
	<?php if ( count( $instructors ) ) : ?>
		<div class="tutor-instructor-list">
			<div class="tutor-row tutor-gx-xl-4">
				<?php foreach ( $instructors as $instructor ) : ?>
					<div class="tutor-col-lg-<?php echo round(12 / $column_count); ?> tutor-mb-32">
						<?php
							$instructor->course_count = tutor_utils()->get_course_count_by_instructor( $instructor->ID );
							$instructor->ratings = tutor_utils()->get_instructor_ratings( $instructor->ID );
							tutor_load_template( 'instructor.' . $layout, array(
								'instructor' => $instructor
							));
						?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php else : ?>
		<?php tutor_utils()->tutor_empty_state( 'No Instructor Found', 'tutor' ); ?>
	<?php endif; ?>

	<?php
		// @todo: convert to pagination
		if ($current_page>1 || $instructors_count>$limit) {

			$pagination_data = array(
				'total_page'  => $instructors_count,
				'per_page'    => $limit,
				'paged'       => $current_page,
				'ajax'		  => array_merge($filter, array(
					'loading_container' => '.tutor-instructor-list-wrapper',
					'action' => 'load_filtered_instructor',
				))
			);

			tutor_load_template_from_custom_path(
				tutor()->path . 'templates/dashboard/elements/pagination.php',
				$pagination_data
			);
		}
	?>
</div>
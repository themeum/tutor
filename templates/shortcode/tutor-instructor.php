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
	if ( $previous_page || $next_page ) {
		$prev_url = ! $show_filter ? '?instructor-page=' . $previous_page : '#';
		$next_url = ! $show_filter ? '?instructor-page=' . $next_page : '#';
		?>
			<div class="tutor-pagination-wrap">
				<?php
					echo $previous_page ? '<a class="page-numbers" href="' . $prev_url . '" data-page_number="' . $previous_page . '">« ' . __( 'Previous', 'tutor' ) . '</a>' : '';
					echo $next_page ? '&nbsp; <a class="next page-numbers" href="' . $next_url . '" data-page_number="' . $next_page . '">' . __( 'Next', 'tutor' ) . ' »</a>' : '';
				?>
			</div>
		<?php
	}
?>
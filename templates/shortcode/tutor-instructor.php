<div class="tutor-courses tutor-courses-loop-wrap tutor-courses-layout-<?php esc_attr_e( $column_count . ' ' . $layout ); ?>">
	<?php if ( count( $instructors ) ) : ?>
		<?php
		foreach ( $instructors as $instructor ) :
			$course_count      = tutor_utils()->get_course_count_by_instructor( $instructor->ID );
			$instructor_rating = tutor_utils()->get_instructor_ratings( $instructor->ID );
			if ( 'pp-left-middle' !== $layout ) :
				?>
			<div class="tutor-course-col-<?php echo $column_count; ?>">
				<a href="<?php echo esc_url( tutor_utils()->profile_url( $instructor->ID, true ) ); ?>" class="tutor-course tutor-course-loop tutor-instructor-list tutor-instructor-list-<?php echo $layout; ?> tutor-instructor-list-<?php esc_attr_e( $instructor->ID ); ?>">
					<div class="tutor-instructor-cover-photo" style="background-image:url(<?php echo esc_url( tutor_utils()->get_cover_photo_url( $instructor->ID ) ); ?>)"></div>
					<div class="tutor-instructor-profile-photo" style="background-image:url(<?php echo esc_url( get_avatar_url( $instructor->ID, array( 'size' => 500 ) ) ); ?>)"></div>                    
					<div class="tutor-instructor-rating">
						<div class="ratings">
							<span class="rating-generated">
								<?php tutor_utils()->star_rating_generator( $instructor_rating->rating_avg ); ?>
							</span>

				
							<span class='rating-digits'><?php esc_html_e( $instructor_rating->rating_avg ); ?></span>
							<span class='rating-total-meta'>(<?php esc_html_e( $instructor_rating->rating_count ); ?>)</span>
						
						</div>
					</div>
					<h4 class="tutor-instructor-name"><?php esc_html_e( $instructor->display_name ); ?></h4>
					<div class="tutor-instructor-course-count">
						<span class="tutor-ins-course-count"><?php esc_html_e( $course_count ); ?></span>
						<span class="tutor-ins-course-text"><?php $course_count > 1 ? esc_html_e( 'Courses', 'tutor' ) : esc_html_e( 'Course', 'tutor' ); ?></span>
					</div>
				</a>
			</div>
			<?php else : ?>
				<div  class="tutor-course-col-<?php esc_html_e( $column_count ); ?>">
					<a href="<?php echo esc_url( tutor_utils()->profile_url( $instructor->ID, true ) ); ?>" style="text-decoration: none;">
						<div class="tutor-instructor-left-middle">
							<div class="tutor-instructor-profile-photo">
								<img src="<?php echo esc_url( get_avatar_url( $instructor->ID ) ); ?>" alt="instructor-image">
							</div>
							<div class="tutor-instructor-content">
								<h4 class="tutor-instructor-name"><?php esc_html_e( $instructor->display_name ); ?></h4>
								<span class="tutor-ins-course-count" style="color:#161616;"><?php esc_html_e( $course_count ); ?></span>
								<span class="tutor-ins-course-text" style="color:#7A7A7A;"><?php $course_count > 1 ? esc_html_e( 'Courses', 'tutor' ) : esc_html_e( 'Course', 'tutor' ); ?></span>
							</div>
						</div>
					</a>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php else : ?>
		<div>
			<?php esc_html_e( 'No instructor found', 'tutor' ); ?>
		</div>
	<?php endif; ?>        
</div>

<?php
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

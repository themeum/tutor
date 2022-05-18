<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

$course     = tutor_utils()->get_course_by_quiz( get_the_ID() );
$course_id  = tutor_utils()->get_course_id_by('lesson', get_the_ID());
?>

<div class="tutor-course-topic-single-header tutor-d-flex tutor-single-page-top-bar">
	<a href="#" class="tutor-iconic-btn tutor-iconic-btn-secondary tutor-d-none tutor-d-xl-inline-flex" tutor-course-topics-sidebar-toggler>
		<span class="tutor-icon-left" area-hidden="true"></span>
	</a>

	<a href="<?php echo get_the_permalink( $course_id ); ?>" class="tutor-iconic-btn tutor-d-flex tutor-d-xl-none">
		<span class="tutor-icon-previous" area-hidden="true"></span>
	</a>

	<div class="tutor-course-topic-single-header-title tutor-d-flex tutor-align-center tutor-ml-12 tutor-ml-xl-24">
        <?php
            switch ($post->post_type) {
                case 'tutor_quiz':
                    $title = 'Quiz';
                    $icon = 'tutor-icon-quiz-o';
                    break;
                case 'tutor_assignments':
                    $title = 'Assignment';
                    $icon = 'tutor-icon-assignment-filled';
                    break;
                case 'tutor_zoom_meeting':
                    $title = 'Zoom Meeting';
                    $icon = 'tutor-icon-brand-zoom';
                    break;
                default:
                    $title = 'Lesson';
                    $icon = 'tutor-icon-brand-youtube-bold';
            }
        ?>
		<span class="tutor-course-topic-single-header-icon <?php echo $icon; ?> tutor-mr-12 tutor-d-none tutor-d-xl-block" area-hidden="true"></span>
		<span class="tutor-fs-6">
			<?php esc_html_e( $title . ': ', 'tutor' ); ?>
		</span>
	</div>

	<div class="tutor-ml-auto tutor-align-center tutor-d-none tutor-d-xl-flex">
        <a class="tutor-iconic-btn" href="<?php echo get_the_permalink( $course_id ); ?>">
			<span class="tutor-icon-times" area-hidden="true"></span>
		</a>
	</div>

	<div class="tutor-ml-auto tutor-align-center tutor-d-block tutor-d-xl-none">
		<a class="tutor-iconic-btn" href="#">
			<span class="tutor-icon-hamburger-menu" area-hidden="true"></span>
		</a>
	</div>
</div>


<?php ob_start(); ?>
<div class="tutor-quiz-single-wrap ">
	<input type="hidden" name="tutor_quiz_id" id="tutor_quiz_id" value="<?php the_ID(); ?>">
	<?php
        if ( $course ) {
            tutor_single_quiz_top();
            tutor_single_quiz_body();
        } else {
            tutor_single_quiz_no_course_belongs();
        }
	?>
</div>
<?php echo apply_filters( 'tutor_quiz/single/wrapper', ob_get_clean() ); ?>
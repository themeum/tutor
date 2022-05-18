<?php
/**
 * Template for displaying single quiz
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

get_tutor_header();

global $previous_id;

// Get the ID of this content and the corresponding course
$course_content_id = get_the_ID();
$course_id         = tutor_utils()->get_course_id_by_subcontent( $course_content_id );

$content_id  = tutor_utils()->get_post_id( $course_content_id );
$contents    = tutor_utils()->get_course_prev_next_contents_by_id( $content_id );
$previous_id = $contents->previous_id;
$course      = tutor_utils()->get_course_by_quiz( get_the_ID() );

// Get total content count
$course_stats          = tutor_utils()->get_course_completed_percent( $course_id, 0, true );
$enable_spotlight_mode = tutor_utils()->get_option( 'enable_spotlight_mode' );
$course_id = tutor_utils()->get_course_id_by('lesson', get_the_ID());

ob_start();
?>
	<input type="hidden" name="tutor_quiz_id" id="tutor_quiz_id" value="<?php the_ID(); ?>">

    <div class="tutor-course-topic-single-header tutor-d-flex tutor-single-page-top-bar">
        <a href="#" class="tutor-iconic-btn tutor-iconic-btn-secondary tutor-d-none tutor-d-xl-inline-flex" tutor-course-topics-sidebar-toggler>
            <span class="tutor-icon-left" area-hidden="true"></span>
        </a>

        <a href="<?php echo get_the_permalink( $course_id ); ?>" class="tutor-iconic-btn tutor-d-flex tutor-d-xl-none">
            <span class="tutor-icon-previous" area-hidden="true"></span>
        </a>

        <div class="tutor-course-topic-single-header-title tutor-d-flex tutor-align-center tutor-ml-12 tutor-ml-xl-24">
            <span class="tutor-course-topic-single-header-icon tutor-icon-quiz-o tutor-mr-12 tutor-d-none tutor-d-xl-block" area-hidden="true"></span>
            <span class="tutor-fs-6">
                <?php
                    esc_html_e( 'Quiz: ', 'tutor' );
                    the_title();
                ?>
            </span>
        </div>

        <div class="tutor-ml-auto tutor-align-center tutor-d-none tutor-d-xl-flex">
            <?php do_action( 'tutor_course/single/enrolled/before/lead_info/progress_bar' ); ?>
            <div class="tutor-fs-7 tutor-mr-20">
                <?php if ( true == get_tutor_option( 'enable_course_progress_bar' ) ) : ?>
                    <span class="tutor-progress-content tutor-color-primary-60">
                        <?php _e( 'Your Progress:', 'tutor' ); ?>
                    </span>
                    <span class="tutor-fs-7 tutor-fw-bold">
                        <?php echo $course_stats['completed_count']; ?>
                    </span>
                    <?php _e( 'of ', 'tutor' ); ?>
                    <span class="tutor-fs-7 tutor-fw-bold">
                        <?php echo $course_stats['total_count']; ?>
                    </span>
                    (<?php echo $course_stats['completed_percent'] . '%'; ?>)
                <?php endif; ?>
            </div>
            <?php do_action( 'tutor_course/single/enrolled/after/lead_info/progress_bar' ); ?>

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

    <!-- @todo: remove tutor-d-grid -->
    <div class="tutor-mobile-top-navigation tutor-d-block tutor-d-sm-none tutor-my-20 tutor-mx-12">
        <div class="tutor-mobile-top-nav tutor-d-grid">
            <a href="<?php echo get_the_permalink($previous_id); ?>">
                <span class="tutor-top-nav-icon tutor-icon-previous design-lightgrey"></span>
            </a>
            <div class="tutor-top-nav-title tutor-fs-6 tutor-fw-normal tutor-color-black">
                <?php 
                    the_title();
                ?>
            </div>
        </div>
    </div>

	<?php ob_start(); ?>
		<div class="tutor-quiz-wrapper tutor-quiz-wrapper tutor-d-flex tutor-justify-center tutor-mt-80 tutor-pb-80">
			<input type="hidden" name="tutor_quiz_id" id="tutor_quiz_id" value="<?php the_ID(); ?>">

			<?php
			if ( $course ) {
				tutor_single_quiz_top();
				// tutor_single_quiz_content();
				tutor_single_quiz_body();
			} else {
				tutor_single_quiz_no_course_belongs();
			}
			?>
		</div>
	<?php

	echo apply_filters( 'tutor_quiz/single/wrapper', ob_get_clean() );
	tutor_load_template_from_custom_path(
		__DIR__ . '/single-content-loader.php',
		array(
			'context'      => 'quiz',
			'html_content' => ob_get_clean(),
		),
		false
	);
	?>

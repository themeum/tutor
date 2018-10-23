<?php
/**
 * Template for displaying single quiz
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

get_header();

global $post;
$currentPost = $post;
?>


<?php do_action('tutor_quiz/single/before/wrap'); ?>

    <div <?php tutor_post_class(); ?>>
        <div class="tutor-quiz-single-wrap">

            <div class="tutor-quiz-top">
                <div class="tutor-quiz-top-left">
                    <p><?php _e('Quiz', 'tutor'); ?> : <?php echo get_the_title(); ?></p>
                    <p>
						<?php _e('Course', 'tutor'); ?> :
						<?php
						$course = tutor_utils()->get_course_by_quiz(get_the_ID());
						echo get_the_title($course->ID);
						?>
                    </p>

                    <p>
						<?php
						$attempts_allowed = tutor_utils()->get_quiz_option(get_the_ID(), 'attempts_allowed', 0);
						?>
						<?php _e('Attempts Allowed', 'tutor'); ?> : <?php echo $attempts_allowed; ?>
                    </p>
                </div>

                <div class="tutor-quiz-top-right">
					<?php
					$total_questions = tutor_utils()->total_questions_for_student_by_quiz(get_the_ID());
					?>
                    <p> <?php _e('Questions', 'tutor'); ?>: <?php echo $total_questions; ?></p>
					<?php
					$time_limit = tutor_utils()->get_quiz_option(get_the_ID(), 'time_limit.time_value');
					if ($time_limit){
						$time_type = tutor_utils()->get_quiz_option(get_the_ID(), 'time_limit.time_type');
						echo "<p> ".__('Time', 'tutor').": {$time_limit} {$time_type}</p>";
					}
					?>
					<?php _e('Attempts Remaining', 'tutor'); ?> : <?php echo $attempts_allowed; ?>
                </div>
            </div>



            <div id="tutor-quiz-time-update" data-quiz-time-started="" data-quiz-time="" data-quiz-status="running">

            </div>


            <div id="tutor-quiz-body" class="tutor-quiz-body tutor-quiz-body-<?php the_ID(); ?>">



                <div class="start-quiz-wrap">


                    <form id="tutor-start-quiz" method="post">
	                    <?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>

                        <input type="hidden" value="<?php echo get_the_ID(); ?>" name="quiz_id"/>
                        <input type="hidden" value="tutor_start_quiz" name="tutor_action"/>

                        <button type="submit" class="start-quiz-button" name="start_quiz_btn" value="start_quiz"><i class="icon-hourglass-1"></i> <?php _e( 'Start Quiz', 'tutor' ); ?></button>

                    </form>

                </div>


            </div>



        </div>
    </div><!-- .wrap -->

<?php do_action('tutor_quiz/single/after/wrap');

get_footer();

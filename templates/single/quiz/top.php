<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

global $post;
global $next_id;
$course_content_id 	= get_the_ID();
$course_id 			= tutor_utils()->get_course_id_by_subcontent($course_content_id);

$content_id 		= tutor_utils()->get_post_id($course_content_id);
$contents 			= tutor_utils()->get_course_prev_next_contents_by_id($content_id);
$previous_id 		= $contents->previous_id;
$next_id 			= $contents->next_id;
$currentPost 		= $post;
$quiz_id 			= get_the_ID();
$is_started_quiz 	= tutor_utils()->is_started_quiz();
$course 			= tutor_utils()->get_course_by_quiz(get_the_ID());
$previous_attempts 	= tutor_utils()->quiz_attempts();
$attempted_count 	= is_array($previous_attempts) ? count($previous_attempts) : 0;

$feedback_mode     	= tutor_utils()->get_quiz_option( $quiz_id, 'feedback_mode', 0 );
$attempts_allowed 	= $feedback_mode!='retry' ? 1 : tutor_utils()->get_quiz_option(get_the_ID(), 'attempts_allowed', 0);
$passing_grade 		= tutor_utils()->get_quiz_option(get_the_ID(), 'passing_grade', 0);
$attempt_remaining 	= (int) $attempts_allowed - (int) $attempted_count;

do_action('tutor_quiz/single/before/top');
?>
<?php if (!$is_started_quiz && $attempted_count == 0): ?>
	<div class="tutor-start-quiz-wrapper tutor-p-48">
		<div class="tutor-start-quiz-title tutor-pb-28">
			<div class="tutor-fs-6 tutor-color-black tutor-pb-8">
				<?php _e('Quiz', 'tutor'); ?>
			</div>
			<div class="tutor-fs-4 tutor-fw-medium tutor-color-black">
				<?php echo get_the_title(); ?>
			</div>
			<div>
				<?php echo get_the_content(); ?>
			</div>
		</div>

		<div class="tutor-quiz-info-area tutor-mb-60 tutor-mt-24">
			<?php
				// Show total question count
				$total_questions = tutor_utils()->total_questions_for_student_by_quiz( get_the_ID() );
				if ( $total_questions ) :
			?>
				<div class="tutor-quiz-info">
					<span class="tutor-fs-6 tutor-color-muted"><?php _e('Questions', 'tutor'); ?>:</span>
					<span class="tutor-fs-6 tutor-color-black">
						<?php echo $total_questions; ?>
					</span>
				</div>
			<?php endif; ?>

			<?php
				// Show time limit
				$time_limit = tutor_utils()->get_quiz_option( get_the_ID(), 'time_limit.time_value' );
				if ( $time_limit ) :
					$time_type 	= tutor_utils()->get_quiz_option( get_the_ID(), 'time_limit.time_type' );
					$available_time_type = array(
						'seconds'	=> $time_limit>1 ? __( 'Seconds', 'tutor' ) : __( 'Second', 'tutor' ),
						'minutes'	=> $time_limit>1 ? __( 'Minutes', 'tutor' ) : __( 'Minute', 'tutor' ),
						'hours'		=> $time_limit>1 ? __( 'Hours', 'tutor' ) : __( 'Hour', 'tutor' ),
						'days'		=> $time_limit>1 ? __( 'Days', 'tutor' ) : __( 'Day', 'tutor' ),
						'weeks'		=> $time_limit>1 ? __( 'Weeks', 'tutor' ) : __( 'Week', 'tutor' ),
					);
			?>
				<div class="tutor-quiz-info">
					<span class="tutor-fs-6 tutor-color-muted"><?php _e('Quiz Time', 'tutor'); ?>:</span>
					<span class="tutor-fs-6 tutor-color-black">
						<?php echo $time_limit.' '.sprintf( __( '%s', 'tutor' ), isset( $available_time_type[$time_type] ) ? $available_time_type[$time_type] : $time_type ); ?>
					</span>
				</div>
			<?php endif; ?>

			<!-- Show Total attempt count -->
			<div class="tutor-quiz-info">
				<span class="tutor-fs-6 tutor-color-muted">
					<?php _e('Total Attempted', 'tutor'); ?>:
				</span>
				<span class="tutor-fs-6 tutor-color-black">
					<?php echo $attempted_count . '/' . ($attempts_allowed == 0 ? '&#8734;' : $attempts_allowed); ?>
				</span>
			</div>

			<!-- Show Passing grade -->
			<?php if( $passing_grade ) : ?>
				<div class="tutor-quiz-info">
					<span class="tutor-fs-6 tutor-color-muted"><?php _e( 'Passing Grade', 'tutor' ); ?></span>
					<span class="tutor-fs-6 tutor-color-black">(<?php echo $passing_grade . '%'; ?>)</span>
				</div>
			<?php endif; ?>
		</div>
		
		<?php
			if ($attempt_remaining > 0 || $attempts_allowed == 0) :
			do_action('tutor_quiz/start_form/before', $quiz_id);
			$skip_url = get_the_permalink($next_id ? $next_id : $course_id);
		?>
			<div class="tutor-quiz-btn-group">
				<form id="tutor-start-quiz" method="post">
					<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>

					<input type="hidden" value="<?php echo $quiz_id; ?>" name="quiz_id"/>
					<input type="hidden" value="tutor_start_quiz" name="tutor_action"/>

					<button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-md start-quiz-btn" name="start_quiz_btn" value="start_quiz">
						<?php _e( 'Start Quiz', 'tutor' ); ?>
					</button>
				</form>

				<button class="tutor-btn tutor-btn-ghost tutor-btn-md skip-quiz-btn tutor-ml-24" data-tutor-modal-target="tutor-quiz-skip-to-next">
					<?php _e( 'Skip Quiz', 'tutor' ); ?>
				</button>
			</div>

			<?php
				tutor_load_template( 'modal.confirm', array(
					'id' => 'tutor-quiz-skip-to-next',
					'title' => __('Do You Want to Skip This Quiz?', 'tutor'),
					'content' => __('Are you sure you want to skip this quiz? Please confirm your choice.', 'tutor'),
					'yes' => array(
						'text' => __('Yes, Skip This', 'tutor'),
						'attr' => array( 'onclick="window.location=\''. $skip_url .'\';"' )
					),
				));
			?>
		<?php endif; ?>
	</div>
<?php endif; ?>
<?php do_action('tutor_quiz/single/after/top'); ?>
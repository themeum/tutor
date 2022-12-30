<?php
/**
 * Question Answer
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Question_Answer
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

use TUTOR\Input;

$question_id = Input::get( 'question_id', 0, Input::TYPE_INT );
$question    = tutils()->get_qa_question( $question_id );
$answers     = tutils()->get_qa_answer_by_question( $question_id );
$profile_url = tutils()->profile_url( $question->user_id );

?>
<h2><?php esc_html_e( 'Answer', 'tutor' ); ?></h2>
<div class="tutor-question-and-answer-wrap">
	<div class="tutor_question_answer_wrap">
		<div class="tutor_original_question">
			<div class="tutor-question-wrap">
				<div class="question-top-meta">
					<div class="tutor-question-avater">
						<a href="<?php echo esc_url( $profile_url ); ?>"> 
											<?php
											echo wp_kses(
												tutils()->get_tutor_avatar( $question->user_id ),
												tutor_utils()->allowed_avatar_tags()
											);
											?>
						</a>
					</div>
					<p class="review-meta">
						<a href="<?php echo esc_url( $profile_url ); ?>"><?php echo esc_attr( $question->display_name ); ?></a>
						<span class="tutor-text-mute">
						<?php echo esc_html( wp_sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $question->comment_date_gmt ) ) ) ); ?>
					</span>
					</p>
				</div>

				<div class="tutor_question_area">
					<p><strong><?php echo esc_attr( $question->question_title ); ?> </strong></p>
					<?php echo wp_kses_post( wpautop( stripslashes( $question->comment_content ) ) ); ?>
				</div>
			</div>
		</div>

		<div class="tutor_admin_answers_list_wrap">
			<?php
			if ( is_array( $answers ) && count( $answers ) ) {
				foreach ( $answers as $answer ) {
					$answer_profile = tutils()->profile_url( $answer->user_id );
					?>
						<div class="tutor_individual_answer <?php echo esc_attr( ( $question->user_id == $answer->user_id ) ? 'tutor-bg-white' : 'tutor-bg-light' ); ?>">
							<div class="tutor-question-wrap">
								<div class="question-top-meta">
									<div class="tutor-question-avater">
										<a href="<?php echo esc_url( $answer_profile ); ?>"> 
															<?php
															echo wp_kses(
																tutils()->get_tutor_avatar( $answer->user_id ),
																tutor_utils()->allowed_avatar_tags()
															);
															?>
										</a>
									</div>
									<p class="review-meta">
										<a href="<?php echo esc_url( $answer_profile ); ?>"><?php echo esc_attr( $answer->display_name ); ?></a>
										<span class="tutor-text-mute">
										<?php echo esc_html( wp_sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $answer->comment_date_gmt ) ) ) ); ?>
										</span>
									</p>
								</div>

								<div class="tutor_question_area">
								<?php echo wp_kses_post( wpautop( stripslashes( $answer->comment_content ) ) ); ?>
								</div>
							</div>
						</div>
						<?php
				}
			}
			?>
			<div class="tutor-add-question-wrap">
				<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="tutor_admin_answer_form" method="post">
					<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
					<input type="hidden" value="tutor_place_answer" name="action"/>
					<input type="hidden" value="<?php echo esc_attr( $question_id ); ?>" name="question_id"/>
					<div class="tutor-form-group">
						<?php
						$editor_settings = array(
							'teeny'         => true,
							'media_buttons' => false,
							'quicktags'     => false,
							'editor_height' => 100,
						);
						wp_editor( null, 'answer', $editor_settings );
						?>
					</div>
					<div class="tutor-form-group">
						<button type="submit" class="tutor-button tutor-button-primary" name="tutor_question_search_btn"><?php esc_html_e( 'Reply', 'tutor' ); ?> </button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

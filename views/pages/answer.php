<?php
/**
 * Answer page
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

use TUTOR\Input;

$question_id = Input::get( 'question_id', 0, Input::TYPE_INT );
$question    = tutor_utils()->get_qa_question( $question_id );
?>

<div class="wrap">
	<h2><?php esc_html_e( 'Answer', 'tutor' ); ?></h2>

	<div class="tutor-qanda-wrap">
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="tutor_admin_answer_form" method="post">
			<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
			<input type="hidden" value="tutor_place_answer" name="action"/>
			<input type="hidden" value="<?php echo esc_attr( $question_id ); ?>" name="question_id"/>

			<div class="tutor-option-field-row">
				<div class="tutor-option-field">
					<?php
					$settings = array(
						'teeny'         => true,
						'media_buttons' => false,
						'quicktags'     => false,
						'editor_height' => 200,
					);
					wp_editor( null, 'answer', $settings );
					?>

					<p class="desc"><?php esc_html_e( 'Write an answer here', 'tutor' ); ?></p>
				</div>

				<div class="tutor-option-field">
					<button type="submit" name="tutor_answer_submit_btn" class="button button-primary"><?php esc_html_e( 'Place answer', 'tutor' ); ?></button>
				</div>
			</div>
		</form>
	</div>

	<div class="tutor-admin-individual-question">
		<div class="tutor_original_question tutor-bg-white ">
			<div class="question-left">
				<?php
					echo wp_kses( tutor_utils()->get_tutor_avatar( $question->user_id ), tutor_utils()->allowed_avatar_tags() );
				?>
			</div>

			<div class="question-right">

				<div class="question-top-meta">
					<p class="review-meta">
						<?php echo esc_attr( $question->display_name ); ?> -
						<span class="tutor-color-muted">
							<?php echo wp_sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $question->comment_date ) ) ); //phpcs:ignore ?>
						</span>
					</p>
				</div>

				<div class="tutor_question_area">
					<p>
						<strong>
							<?php echo esc_html( stripslashes( $question->question_title ) ); ?>
						</strong>

						<span class="tutor-color-muted">
							<?php esc_html_e( 'on', 'tutor' ); ?> <?php echo esc_attr( $question->post_title ); ?>
						</span>
					</p>
					<?php echo wp_kses_post( wpautop( stripslashes( $question->comment_content ) ) ); ?>
				</div>

			</div>
		</div>

		<?php
		$answers = tutor_utils()->get_qa_answer_by_question( $question_id );
		?>

		<div class="tutor_admin_answers_list_wrap">
			<?php
			if ( is_array( $answers ) && count( $answers ) ) {
				foreach ( $answers as $answer ) {
					?>
					<div class="tutor_original_question <?php echo $question->user_id == $answer->user_id ? 'tutor-bg-white' : 'tutor-bg-light'; ?>">
						<div class="question-left">
							<?php
								echo wp_kses(
									tutor_utils()->get_tutor_avatar( $answer->user_id ),
									tutor_utils()->allowed_avatar_tags()
								);
							?>
						</div>

						<div class="question-right">
							<div class="question-top-meta">
								<p class="review-meta">
									<?php echo esc_attr( $answer->display_name ); ?> -
									<span class="tutor-color-muted">
										<?php echo wp_sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $answer->comment_date ) ) ); //phpcs:ignore ?>
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
		</div>
	</div>
</div>

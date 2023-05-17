<?php
/**
 * Attempt table
 *
 * @package Tutor\Views
 * @subpackage Tutor\Quiz
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

// Data variable contains $attempt_list, $context.
extract( $data ); //phpcs:ignore WordPress.PHP.DontExtract.extract_extract

$page_key                  = 'attempt-table';
$table_columns             = include __DIR__ . '/contexts.php';
$enabled_hide_quiz_details = tutor_utils()->get_option( 'hide_quiz_details' );

if ( 'course-single-previous-attempts' == $context && is_array( $attempt_list ) && count( $attempt_list ) ) {
	// Provide the attempt data from the first attempt.
	// For now now attempt specific data is shown, that's why no problem if we take meta data from any attempt.
	$attempt_data = $attempt_list[0];
	include __DIR__ . '/header.php';
}
?>

<?php if ( is_array( $attempt_list ) && count( $attempt_list ) ) : ?>
	<div class="tutor-table-responsive tutor-my-24">
		<table class="tutor-table tutor-table-quiz-attempts">
			<thead>
				<tr>
					<?php foreach ( $table_columns as $key => $column ) : ?>
						<?php
						/**
						 * Pro feature: Only for frontend
						 *
						 * @since 2.07
						 */
						if ( 'details' === $key && ! is_admin() && ! current_user_can( 'tutor_instructor' ) && true === $enabled_hide_quiz_details ) {
							continue;
						}
						?>

						<th><?php echo $column; //phpcs:ignore -- contain safe data ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>

			<?php
				$attempt_ids   = array_column( $attempt_list, 'attempt_id' );
				$answers_array = \Tutor\Models\QuizModel::get_quiz_answers_by_attempt_id( $attempt_ids, true );
			?>

			<tbody>
				<?php foreach ( $attempt_list as $attempt ) : ?>
					<?php
						$course_id         = is_object( $attempt ) && property_exists( $attempt, 'course_id' ) ? $attempt->course_id : 0;
						$earned_percentage = ( $attempt->earned_marks > 0 && $attempt->total_marks > 0 ) ? ( number_format( ( $attempt->earned_marks * 100 ) / $attempt->total_marks ) ) : 0;
						$answers           = isset( $answers_array[ $attempt->attempt_id ] ) ? $answers_array[ $attempt->attempt_id ] : array();
						$attempt_info      = @unserialize( $attempt->attempt_info );
						$attempt_info      = ! is_array( $attempt_info ) ? array() : $attempt_info;
						$passing_grade     = isset( $attempt_info['passing_grade'] ) ? (int) $attempt_info['passing_grade'] : 0;
						$ans_array         = is_array( $answers ) ? $answers : array();

						$has_pending = count(
							array_filter(
								$ans_array,
								function( $ans ) {
									return null === $ans->is_correct;
								}
							)
						);

						$correct    = 0;
						$incorrect  = 0;
						$attempt_id = $attempt->attempt_id;

					if ( is_array( $answers ) && count( $answers ) > 0 ) {
						foreach ( $answers as $answer ) {
							if ( (bool) $answer->is_correct ) {
								$correct++;
							} elseif ( ! ( null === $answer->is_correct ) ) {
								$incorrect++;
							}
						}
					}
					?>
					<tr>
						<?php foreach ( $table_columns as $key => $column ) : ?>
							<?php
							/**
							 * Pro feature: Only for frontend
							 *
							 * @since 2.07
							 */
							if ( 'details' === $key && ! is_admin() && ! current_user_can( 'tutor_instructor' ) && true === $enabled_hide_quiz_details ) {
								continue;
							}
							?>
							<td>
								<?php if ( 'checkbox' == $key ) : ?>
									<div class="tutor-d-flex">
										<input id="tutor-admin-list-<?php echo esc_attr( $attempt->attempt_id ); ?>" type="checkbox" class="tutor-form-check-input tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php echo esc_attr( $attempt->attempt_id ); ?>" />
									</div>
								<?php elseif ( 'date' == $key ) : ?>
									<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $attempt->attempt_ended_at ) ) ); ?>
								<?php elseif ( 'quiz_info' == $key ) : ?>
									<div>
										<div class="tutor-fs-7 tutor-fw-normal">
											<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $attempt->attempt_ended_at ) ) ); ?>
										</div>
										<div class="tutor-mt-4">
											<?php
												// For admin panel.
											if ( is_admin() ) {
												echo esc_html( get_the_title( $attempt->quiz_id ) );
											} else {
												// For frontend.
												echo esc_html( get_the_title( $attempt->quiz_id ) );
												?>
													<div class="tooltip-wrap tooltip-icon-custom" >
														<i class="tutor-icon-circle-info-o tutor-color-muted tutor-ml-4 tutor-fs-7"></i>
														<span class="tooltip-txt tooltip-right">
														<?php echo esc_html( get_the_title( $attempt->course_id ) ); ?>
														</span>
													</div>
													<?php
											}
											?>
										</div>
										<div class="tutor-fs-7 tutor-mt-8">
											<?php
												$attempt_user = get_userdata( $attempt->user_id );
												$user_name    = $attempt_user ? $attempt_user->display_name : '';
											?>
											<span class="tutor-color-secondary"><?php esc_html_e( 'Student:', 'tutor' ); ?></span>
											<span class="tutor-fw-normal tutor-color-muted"><?php echo 'backend-dashboard-students-attempts' == $context ? esc_html( $user_name ) : esc_attr( isset( $attempt->display_name ) ? $attempt->display_name : $user_name ); ?></span>
										</div>
										<?php do_action( 'tutor_quiz/table/after/course_title', $attempt, $context ); ?>
									</div>
								<?php elseif ( 'course' == $key ) : ?>
									<?php echo esc_html( get_the_title( $attempt->course_id ) ); ?>
								<?php elseif ( 'question' == $key ) : ?>
									<?php echo esc_html( count( $answers ) ); ?>
								<?php elseif ( 'total_marks' == $key ) : ?>
									<?php echo esc_html( round( $attempt->total_marks ) ); ?>
								<?php elseif ( 'correct_answer' == $key ) : ?>
									<?php echo esc_html( $correct ); ?>
								<?php elseif ( 'incorrect_answer' == $key ) : ?>
									<?php echo esc_html( $incorrect ); ?>
								<?php elseif ( 'earned_marks' == $key ) : ?>
									<?php echo esc_html( round( $attempt->earned_marks ) . ' (' . $earned_percentage . '%)' ); ?>
								<?php elseif ( 'result' == $key ) : ?>
									<?php
									if ( $has_pending ) {
										echo '<span class="tutor-badge-label label-warning">' . esc_html__( 'Pending', 'tutor' ) . '</span>';
									} else {
										echo $earned_percentage >= $passing_grade ?
											'<span class="tutor-badge-label label-success">' . esc_html__( 'Pass', 'tutor' ) . '</span>' :
											'<span class="tutor-badge-label label-danger">' . esc_html__( 'Fail', 'tutor' ) . '</span>';
									}
									?>
								<?php elseif ( 'details' == $key ) : ?>
									<?php
										$url   = add_query_arg( array( 'view_quiz_attempt_id' => $attempt->attempt_id ), tutor()->current_url );
										$style = '';
									?>
									<div class="tutor-d-inline-flex tutor-align-center" style="<?php echo esc_attr( ! is_admin() ? $style : '' ); ?>">
										<a href="<?php echo esc_url( $url ); ?>" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm">
											<?php
											if ( $has_pending && ( 'frontend-dashboard-students-attempts' == $context || 'backend-dashboard-students-attempts' == $context ) ) {
												esc_html_e( 'Review', 'tutor' );
											} else {
												esc_html_e( 'Details', 'tutor-pro' );
											}
											?>
										</a>
										<?php
										$current_page = tutor_utils()->get_current_page_slug();
										if ( ! is_admin() && $course_id && ( tutor_utils()->is_instructor_of_this_course( get_current_user_id(), $course_id ) || current_user_can( 'administrator' ) ) ) :
											?>
											<!-- Don't show delete option on the spotlight section since JS not support -->
											<?php if ( 'quiz-attempts' === $current_page || 'tutor_quiz_attempts' === $current_page ) : ?>
											<a href="#" class="tutor-quiz-attempt-delete tutor-iconic-btn tutor-flex-shrink-0 tutor-ml-4" data-quiz-id="<?php echo esc_attr( $attempt_id ); ?>" data-tutor-modal-target="tutor-common-confirmation-modal">
												<i class="tutor-icon-trash-can-line" data-quiz-id="<?php echo esc_attr( $attempt_id ); ?>"></i>
											</a>
											<?php endif; ?>
										<?php endif; ?>
									</div>
								<?php endif; ?>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php else : ?>
	<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
<?php endif; ?>
<?php
// Load delete modal.
tutor_load_template_from_custom_path(
	tutor()->path . 'views/elements/common-confirm-popup.php',
	array(
		'message' => __( 'Would you like to delete Quiz Attempt permanently? We suggest you proceed with caution.', 'tutor' ),
	)
);

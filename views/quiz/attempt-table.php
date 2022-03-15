<?php
	extract( $data ); // $attempt_list, $context;

	$page_key      = 'attempt-table';
	$table_columns = include __DIR__ . '/contexts.php';

if ( $context == 'course-single-previous-attempts' && is_array( $attempt_list ) && count( $attempt_list ) ) {
	// Provide the attempt data from the first attempt
	// For now now attempt specific data is shown, that's why no problem if we take meta data from any atttempt.
	$attempt_data = $attempt_list[0];
	include __DIR__ . '/header.php';
}
?>

<div class="tutor-ui-table-wrapper tutor-my-24">
	<table class="tutor-ui-table tutor-ui-table-responsive my-quiz-attempts">
		<?php if ( is_array( $attempt_list ) && count( $attempt_list ) ) { ?>
		<thead>
			<tr>
				<?php
					foreach ( $table_columns as $key => $column ) {
						echo '<th>
								<div class="text-regular-small tutor-color-black-60">
									' . $column . '
								</div>
							</th>';
					}
				?>
			</tr>
		</thead>
		<?php } ?>
		<tbody>
			<?php
			if ( is_array( $attempt_list ) && count( $attempt_list ) ) {
				foreach ( $attempt_list as $attempt ) {
					$attempt_action    = tutor_utils()->get_tutor_dashboard_page_permalink( 'my-quiz-attempts/attempts-details/?attempt_id=' . $attempt->attempt_id );
					$earned_percentage = $attempt->earned_marks > 0 ? ( number_format( ( $attempt->earned_marks * 100 ) / $attempt->total_marks ) ) : 0;
					$answers           = tutor_utils()->get_quiz_answers_by_attempt_id( $attempt->attempt_id );

					$attempt_info 	   = @unserialize($attempt->attempt_info);
					$attempt_info	   = !is_array($attempt_info) ? array() : $attempt_info;
					$passing_grade	   = isset($attempt_info['passing_grade']) ? (int)$attempt_info['passing_grade'] : 0;

					$ans_array = is_array($answers) ? $answers : array();
					$has_pending = count(array_filter($ans_array, function($ans){
						return $ans->is_correct===null;
					}));

					$correct   = 0;
					$incorrect = 0;
					if ( is_array( $answers ) && count( $answers ) > 0 ) {
						foreach ( $answers as $answer ) {
							if ( (bool) $answer->is_correct ) {
								$correct++;
							} else if(!($answer->is_correct===null)) {
								$incorrect++;
							}
						}
					}
					?>
						<tr>
						<?php
						foreach ( $table_columns as $key => $column ) {
							switch ( $key ) {
								case 'checkbox':
									?>
											<td data-th="<?php _e( 'Mark', 'tutor' ); ?>" class="v-align-top">
												<div class="td-checkbox tutor-d-flex ">
													<input id="tutor-admin-list-<?php echo $attempt->attempt_id; ?>" type="checkbox" class="tutor-form-check-input tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php echo $attempt->attempt_id; ?>" />
												</div>
											</td>
										<?php
									break;

								case 'date':
									?>
											<td data-th="<?php echo $column; ?>">
												<div class="td-statement-info">
													<span class="text-regular-small tutor-color-black">
														<?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $attempt->attempt_ended_at ) ); ?>
													</span>
												</div>
											</td>
										<?php
									break;

								case 'quiz_info':
									?>
											<td data-th="<?php echo $column; ?>">
												<div class="td-statement-info">
													<span class="tutor-fs-7 tutor-fw-normal tutor-color-black">
														<?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $attempt->attempt_ended_at ) ); ?>
													</span>
													<div class="tutor-fs-6 tutor-fw-medium  tutor-color-black tutor-margin-0">
														<div class="tutor-fs-6 tutor-fw-medium tutor-color-black" data-href="<?php echo get_the_permalink( $attempt->course_id ); ?>">
															<?php echo get_the_title( $attempt->course_id ); ?>
														</div>
														<?php
														$attempt_user = get_userdata( $attempt->user_id );
														$user_name    = $attempt_user ? $attempt_user->display_name : '';
														if ( $context == 'backend-dashboard-students-attempts' ) {
															$attempt_user = get_userdata( $attempt->user_id );
															$user_name    = $attempt_user ? $attempt_user->display_name : '';

														?>
																<div>
																	<span class="tutor-fs-7 tutor-fw-normal tutor-color-black-70">
																		<?php _e( 'Student', 'tutor' ); ?>
																	</span>: <span class="text-medium-small"> <?php echo $user_name; ?> </span>
																</div>
														<?php
														} else {
														?>
															<?php if(!empty($user_name) && isset( $attempt->user_email ) ) : ?>
																<span class="tutor-fs-7 tutor-fw-normal tutor-color-black-70"><?php esc_html_e( 'Student', 'tutor' ); ?>: </span>
																<span class="tutor-color-black-70 tutor-fs-8 tutor-fw-medium" title="<?php echo esc_attr( $attempt->user_email ); ?>">
																	<?php echo esc_attr( isset($attempt->display_name) ? $attempt->display_name : $user_name ); ?>
																</span>

															<?php endif;
														}
														?>
													</div>
													<?php do_action( 'tutor_quiz/table/after/course_title', $attempt, $context ); ?>
												</div>
											</div>
										</td>
									<?php
									break;

								case 'course':
									?>
											<td data-th="<?php echo $column; ?>">
												<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
													<?php echo get_the_title( $attempt->course_id ); ?>
												</span>
											</td>
										<?php
									break;

								case 'question':
									?>
											<td data-th="<?php echo $column; ?>">
												<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
													<?php echo count( $answers ); ?>
												</span>
											</td>
										<?php
									break;

								case 'total_marks':
									?>
											<td data-th="<?php echo $column; ?>">
												<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
													<?php echo round($attempt->total_marks); ?>
												</span>
											</td>
										<?php
									break;

								case 'correct_answer':
									?>
											<td data-th="<?php echo $column; ?>">
												<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
													<?php echo $correct; ?>
												</span>
											</td>
										<?php
									break;

								case 'incorrect_answer':
									?>
											<td data-th="<?php echo $column; ?>">
												<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
													<?php echo $incorrect; ?>
												</span>
											</td>
										<?php
									break;

								case 'earned_marks':
									?>
											<td data-th="<?php echo $column; ?>">
												<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
													<?php echo round($attempt->earned_marks) . ' (' . $earned_percentage . '% )'; ?>
												</span>
											</td>
										<?php
									break;

								case 'result':
									?>
										<td data-th="<?php echo $column; ?>">
											<?php
												if($has_pending){
													echo '<span class="tutor-badge-label label-warning">'.__('Pending', 'tutor').'</span>';
												} else {
													echo $earned_percentage >= $passing_grade ?
														'<span class="tutor-badge-label label-success">' . __( 'Pass', 'tutor' ) . '</span>' :
														'<span class="tutor-badge-label label-danger">' . __( 'Fail', 'tutor' ) . '</span>';
												}
											?>
										</td>
									<?php
									break;

								case 'details':
									$url = add_query_arg( array( 'view_quiz_attempt_id' => $attempt->attempt_id ), tutor()->current_url );
									?>
										<td data-th="See Details">
											<div class="inline-flex-center td-action-btns">
												<a href="<?php echo $url; ?>" class="tutor-btn tutor-btn-disable-outline tutor-btn-outline-fd tutor-btn-sm">
													<?php
														if ( $has_pending && ( $context == 'frontend-dashboard-students-attempts' || $context == 'backend-dashboard-students-attempts' ) ) {
															esc_html_e( 'Review', 'tutor' );
														} else {
															esc_html_e( 'Details', 'tutor-pro' );
														}
													?>
												</a>
											</div>
										</td>
									<?php
									break;
							}
						}
						?>
						</tr>
						<?php
				}
			} else {
				?>
					<tr>
						<td colspan="100%" class="column-empty-state">
							<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
						</td>
					</tr>
					<?php
			}
			?>
		</tbody>
	</table>
</div>

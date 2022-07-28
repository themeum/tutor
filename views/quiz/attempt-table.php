<?php
	extract( $data ); // $attempt_list, $context;
	
	$page_key      = 'attempt-table';
	$table_columns = include __DIR__ . '/contexts.php';
	$enabled_hide_quiz_details = tutor_utils()->get_option( 'hide_quiz_details' );

	if ( $context == 'course-single-previous-attempts' && is_array( $attempt_list ) && count( $attempt_list ) ) {
		// Provide the attempt data from the first attempt
		// For now now attempt specific data is shown, that's why no problem if we take meta data from any attempt.
		$attempt_data = $attempt_list[0];
		include __DIR__ . '/header.php';
	}
?>

<?php if ( is_array( $attempt_list ) && count( $attempt_list ) ): ?>
	<div class="tutor-table-responsive tutor-my-24">
		<table class="tutor-table tutor-table-quiz-attempts">
			<thead>
				<tr>
					<?php foreach ( $table_columns as $key => $column ) : ?>
						<?php
						/**
						 * Pro feature: Only for frontend
						 * @since 2.07
						 */
						if ( $key === 'details' && ! is_admin() && ! current_user_can( 'tutor_instructor') && true === $enabled_hide_quiz_details ) {
							continue;
						}
						?>
							
						<th style="<?php echo $key == 'quiz_info' ? 'width: 30%;' : ''; ?>"><?php echo $column; ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>

			<?php
				$attempt_ids = array_column($attempt_list, 'attempt_id');
				$answers_array = tutor_utils()->get_quiz_answers_by_attempt_id($attempt_ids, true);
			?>

			<tbody>
				<?php foreach ( $attempt_list as $attempt ) : ?>
					<?php
						$earned_percentage 	= $attempt->earned_marks > 0 ? ( number_format( ( $attempt->earned_marks * 100 ) / $attempt->total_marks ) ) : 0;
						$answers           	= isset($answers_array[$attempt->attempt_id]) ? $answers_array[$attempt->attempt_id] : array();
						$attempt_info 	   	= @unserialize($attempt->attempt_info);
						$attempt_info	   	= !is_array($attempt_info) ? array() : $attempt_info;
						$passing_grade	   	= isset($attempt_info['passing_grade']) ? (int)$attempt_info['passing_grade'] : 0;
						$ans_array 			= is_array($answers) ? $answers : array();

						$has_pending = count( array_filter( $ans_array, function( $ans ) {
							return $ans->is_correct === null;
						}));

						$correct   			= 0;
						$incorrect 			= 0;

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
						<?php foreach ( $table_columns as $key => $column ) : ?>
							<?php
							/**
							 * Pro feature: Only for frontend
							 * @since 2.07
							 */
							if (  $key === 'details' && ! is_admin() && ! current_user_can( 'tutor_instructor') && true === $enabled_hide_quiz_details ) {
								continue;
							}
							?>
							<td>
								<?php if ( $key == "checkbox" ) : ?>
									<div class="tutor-d-flex">
										<input id="tutor-admin-list-<?php echo $attempt->attempt_id; ?>" type="checkbox" class="tutor-form-check-input tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php echo $attempt->attempt_id; ?>" />
									</div>
								<?php elseif ( $key == "date" ) : ?>
									<?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $attempt->attempt_ended_at ) ); ?>
								<?php elseif ( $key == "quiz_info" ) : ?>
									<div>
										<div class="tutor-fs-7 tutor-fw-normal tutor-color-muted">
											<?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $attempt->attempt_ended_at ) ); ?>
										</div>
										<div class="tutor-mt-8">
											<?php
												// For admin panel
												if ( is_admin() ) {
													esc_html_e( get_the_title( $attempt->quiz_id ) );
												} else {
													// For frontend
													esc_html_e( get_the_title( $attempt->quiz_id ) );
													?>
													<div class="tooltip-wrap tooltip-icon-custom" >
														<i class="tutor-icon-circle-info-o tutor-color-muted tutor-ml-4 tutor-fs-7"></i>
														<span class="tooltip-txt tooltip-right">
															<?php esc_html_e( get_the_title( $attempt->course_id ) ) ?>
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
											<span class="tutor-color-secondary"><?php _e( 'Student:', 'tutor' ); ?></span>
											<span class="tutor-fw-normal tutor-color-muted"><?php echo $context == 'backend-dashboard-students-attempts' ? $user_name : esc_attr( isset($attempt->display_name) ? $attempt->display_name : $user_name ); ?></span>
										</div>
										<?php do_action( 'tutor_quiz/table/after/course_title', $attempt, $context ); ?>
									</div>
								<?php elseif ( $key == "course" ) : ?>
									<?php esc_html_e( get_the_title( $attempt->course_id ) ); ?>
								<?php elseif ( $key == "question" ) : ?>
									<?php echo count( $answers ); ?>
								<?php elseif ( $key == "total_marks" ) : ?>
									<?php echo round($attempt->total_marks); ?>
								<?php elseif ( $key == "correct_answer" ) : ?>
									<?php echo $correct; ?>
								<?php elseif ( $key == "incorrect_answer" ) : ?>
									<?php echo $incorrect; ?>
								<?php elseif ( $key == "earned_marks" ) : ?>
									<?php echo round($attempt->earned_marks) . ' ( ' . $earned_percentage . '% )'; ?>
								<?php elseif ( $key == "result" ) : ?>
									<?php
										if ( $has_pending ) {
											echo '<span class="tutor-badge-label label-warning">'.__('Pending', 'tutor').'</span>';
										} else {
											echo $earned_percentage >= $passing_grade ?
												'<span class="tutor-badge-label label-success">' . __( 'Pass', 'tutor' ) . '</span>' :
												'<span class="tutor-badge-label label-danger">' . __( 'Fail', 'tutor' ) . '</span>';
										}
									?>
								<?php elseif ( $key == "details" ) : ?>
									<?php $url = add_query_arg( array( 'view_quiz_attempt_id' => $attempt->attempt_id ), tutor()->current_url ); ?>
									<div class="tutor-d-inline-flex tutor-align-center td-action-btns">
										<a href="<?php echo $url; ?>" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm">
											<?php
												if ( $has_pending && ( $context == 'frontend-dashboard-students-attempts' || $context == 'backend-dashboard-students-attempts' ) ) {
													esc_html_e( 'Review', 'tutor' );
												} else {
													esc_html_e( 'Details', 'tutor-pro' );
												}
											?>
										</a>
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
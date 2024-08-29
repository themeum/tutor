window.jQuery(document).ready($=>{

    const {__} = window.wp.i18n;

	/**
	 * Remove course-topic footer from quiz pages
	 */
	if ($('.tutor-quiz-wrap').length) {
		if (!$('.tutor-table-quiz-attempts').length && !$('.tutor-quiz-attempt-details').length) {
			$('.tutor-course-topic-single-footer').remove();
		}
	}

	/**
	 * Quiz attempt
	 */
	var $tutor_quiz_time_update = $('#tutor-quiz-time-update');
    
    // Assign countdown if quiz time element available
	if ($tutor_quiz_time_update.length) {

        // Get timing info from data
		var attempt_settings = JSON.parse($tutor_quiz_time_update.attr('data-attempt-settings'));
		var attempt_meta = JSON.parse($tutor_quiz_time_update.attr('data-attempt-meta'));

        // Restrict quiz timing if time limit is set
		if (attempt_meta.time_limit.time_limit_seconds > 0) {

			// Get the timeout timestamp
			var countDownDate = new Date(attempt_settings.attempt_started_at?.replaceAll('-', '/')).getTime() + attempt_meta.time_limit.time_limit_seconds * 1000;
			var time_now = new Date(attempt_meta.date_time_now?.replaceAll('-', '/')).getTime();

            // Set the time interval to show countdown
			var tutor_quiz_interval = setInterval(function() {
				// Distance between current time and the quiz timeout timestamp
				var distance = countDownDate - time_now;
                
                // Distance in human readable fragments
				var days = Math.floor(distance / (1000 * 60 * 60 * 24));
				var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
				var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
				var seconds = Math.floor((distance % (1000 * 60)) / 1000);

				// Concat fragments to human redable time
                var countdown_human = '';
                days ? countdown_human += days + 'd ' : 0;
                countdown_human += (hours || 0) + 'h ';
                countdown_human += (minutes || 0) + 'm ';
                countdown_human += (seconds || 0) + 's ';

                // If distance is smaller than 0, then clear the interval and show reattempt alert box
				if (distance < 0) {
					clearInterval(tutor_quiz_interval);
					$tutor_quiz_time_update.toggleClass('tutor-quiz-time-expired');

                    // Replace the time with expired text
					countdown_human = 'EXPIRED';

					if (_tutorobject.quiz_options.quiz_when_time_expires === 'auto_submit') {
						// Automatically submit the quiz with the progress so far
						$('form#tutor-answering-quiz').submit();
                    } else {
						// Else if 'auto_abandon' or anything else for now
						// Add Disable state button class and disable then
						$('.tutor-quiz-answer-next-btn, .tutor-quiz-submit-btn, .tutor-quiz-answer-previous-btn').prop('disabled', true);
						$("button[name='quiz_answer_submit_btn']").prop('disabled',true);

						// add alert text
						$('.time-remaining span').css('color', '#F44337');
                        
						// Abandon the quiz. The attempt status in the database will be 'attempt_timeout'
						$.ajax({
							url: _tutorobject.ajaxurl,
							type: 'POST',
							data: { 
                                quiz_id: $('#tutor_quiz_id').val(), 
                                action: 'tutor_quiz_timeout' 
                            },
							success: function(data) {
								var attemptAllowed = $('#tutor-quiz-time-expire-wrapper').data('attempt-allowed');
								var attemptRemaining = $('#tutor-quiz-time-expire-wrapper').data('attempt-remaining');

								var alertDiv = '#tutor-quiz-time-expire-wrapper';
								$(alertDiv).addClass('tutor-alert-show');

								// if attempt remaining
								if (attemptRemaining > 0) {
									$(`${alertDiv} .tutor-quiz-alert-text`).html(
										__('Your time limit for this quiz has expired, please reattempt the quiz. Attempts remaining:', 'tutor') + ' ' + attemptRemaining + '/' + attemptAllowed // Don't break line
									);
								} else {
									// if attempt not remaining
									if ($(alertDiv).hasClass('time-remaining-warning')) {
										$(alertDiv).removeClass('time-remaining-warning');
										$(alertDiv).addClass('time-over');
									}
									if (
										$(`${alertDiv} .flash-info span:first-child`).hasClass('tutor-icon-circle-info')
									) {
										$(`${alertDiv} .flash-info span:first-child`).removeClass(
											'tutor-icon-circle-info'
										);
										$(`${alertDiv} .flash-info span:first-child`).addClass('tutor-icon-circle-times-line');
									}
									$tutor_quiz_time_update.toggleClass('tutor-quiz-time-expired');
									$('#tutor-start-quiz').hide();
									$(`${alertDiv} .tutor-quiz-alert-text`).html(
										`${__('Unfortunately, you are out of time and quiz attempts. ', 'tutor')}`
									);
								}
							},
							complete: function() {},
						});
					}
				}

                // Update the time_now variable
				time_now = time_now + 1000;

                // Update the alert content based on timing
				$tutor_quiz_time_update.html(countdown_human);
				// clearTimeout(tutor_quiz_interval);
				// return;
				if(countdown_human == 'EXPIRED') {
					$tutor_quiz_time_update.addClass('color-text-error');
				}
				
				/**
				 * dynamically update progress indicator
				 *
				 * @since v2.0.0
				 */
				if ( distance ) {
					// convert distance in sec
					let newDistance = distance / 1000;
					// get total time duration in sec
					let totalTime = attempt_meta.time_limit.time_limit_seconds;
					//calculate progress
					let progress = Math.ceil((newDistance * 100 ) / totalTime);
					let svgWrapper = document.querySelector('.quiz-time-remaining-progress-circle');
					let svg = document.querySelector('.quiz-time-remaining-progress-circle svg');

					if(svg && svgWrapper) {
						let StrokeDashOffset = 44 - (44 * (progress / 100));
						if ( progress <= 0 ) {
							progress = 0;
							// if time out red the progress circle
							svgWrapper.innerHTML = `<svg viewBox="0 0 50 50" width="50" height="50">
														<circle cx="0" cy="0" r="11"></circle>
													</svg>`;
							svgWrapper.setAttribute('class', 'quiz-time-remaining-expired-circle');
						}
						svg.setAttribute('style', `stroke-dashoffset: ${StrokeDashOffset};`);
					}
				}
			}, 1000);

		} else {
            // If no time limit is set, show 'No Limit' message
			$tutor_quiz_time_update.html(__('No Limit', 'tutor'));
		}
	}

	var $quiz_start_form = $('form#tutor-start-quiz');
	if ($quiz_start_form.length) {
		if (_tutorobject.quiz_options.quiz_auto_start == 1) {
			$quiz_start_form.submit();
		}
	}
});
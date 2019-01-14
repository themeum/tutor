<?php
$search_term = '';

$per_page = 20;
$pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;
$current_page = max( 1, $pagenum );

$total_items = tutor_utils()->get_total_quiz_attempts($search_term);
$quizAttempts = tutor_utils()->get_quiz_attempts(($current_page-1)*$per_page, $per_page, $search_term);
?>

<div class="tutor-bg-white box-padding">

    <table class="widefat tutor-report-table ">
        <tr>
            <th><?php _e('By', 'tutor-report'); ?></th>
            <th><?php _e('Quiz', 'tutor-report'); ?></th>
            <th><?php _e('Course', 'tutor-report'); ?></th>
            <th><?php _e('Status', 'tutor-report'); ?></th>
            <th>#</th>
        </tr>

		<?php
		if (is_array($quizAttempts) && count($quizAttempts)){
			foreach ($quizAttempts as $quiz_attempt){
				?>
                <tr>
                    <td>
						<?php
						$quiz_title = '<strong>'.$quiz_attempt->display_name.'</strong> <br />'.$quiz_attempt->user_email.'<br /><br />'. human_time_diff(strtotime($quiz_attempt->comment_date)).__(' ago', 'tutor');

						echo $quiz_title;
						?>
                    </td>
                    <td><?php echo $quiz_attempt->post_title; ?></td>
                    <td>
						<?php
						$course = tutor_utils()->get_course_by_quiz($quiz_attempt->comment_post_ID);

						if ($course) {
							$title = get_the_title( $course->ID );
							echo "<a href='".get_the_permalink($course->ID)."' target='_blank'>{$title}</a>";
						}
						?>
                    </td>
                    <td>
						<?php
						$status = ucwords(str_replace('quiz_', '', $quiz_attempt->attempt_status));
						echo  "<span class='tutor-status-context {$quiz_attempt->attempt_status}'>{$status}</span>";
						?>
                    </td>
                    <td>
                        <button type="button" class="button tutor-delete-link tutor-quiz-attempt-delete-btn" data-attempt-id="<?php echo $quiz_attempt->comment_ID; ?>">
                            <i class="tutor-icon-trash"></i> <?php _e('Delete'); ?>
                        </button>
                    </td>
                </tr>
				<?php
			}
		}
		?>

    </table>


    <div class="tutor-pagination" >
		<?php
		echo paginate_links( array(
			'base' => str_replace( $current_page, '%#%', "admin.php?page=tutor_report&sub_page=quiz&paged=%#%" ),
			'current' => max( 1, $current_page ),
			'total' => ceil($total_items/$per_page)
		) );
		?>
    </div>

</div>
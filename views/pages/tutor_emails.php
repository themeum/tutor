
<div class="wrap tutor-emails-lists-wrap">
	<h2><?php _e('E-Mails', 'tutor'); ?></h2>




	<table class="wp-list-table widefat striped">

		<tr>
			<th><?php _e('Email', 'tutor'); ?></th>
			<th><?php _e('Content type', 'tutor'); ?></th>
			<th><?php _e('Variable', 'tutor'); ?></th>
		</tr>

		<tr>
			<td><?php _e('Quiz Finished'); ?></td>
			<td>text/html</td>
			<td>
				<code>
					{username}, {quiz_name}, {course_name}, {submission_time}, {quiz_url}
				</code>

			</td>
		</tr>


		<tr>
			<td><?php _e('Course Completed (to students)'); ?></td>
			<td>text/html</td>
			<td>
				<code>
					{student_username},{course_name},{completion_time},{course_url}
				</code>

			</td>
		</tr>


		<tr>
			<td><?php _e('Course Completed (to teacher)'); ?></td>
			<td>text/html</td>
			<td>
				<code>
					{teacher_username},{student_username},{course_name},{completion_time},{course_url}
				</code>

			</td>
		</tr>

	</table>





</div>
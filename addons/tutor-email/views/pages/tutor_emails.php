<div class="wrap tutor-emails-lists-wrap">
    <h2><?php _e('E-Mails', 'tutor'); ?></h2>

    <table class="wp-list-table widefat striped">
        <thead>

        <tr>
            <th><?php _e('Event', 'tutor'); ?></th>
            <th><?php _e('Content type', 'tutor'); ?></th>
            <th>#</th>
            <th><?php _e('Variable, that can be use at template', 'tutor'); ?></th>
        </tr>
        </thead>

        <tbody>
        <tr>
            <td><?php _e('Quiz Finished'); ?></td>
            <td>text/html</td>
            <td>
				<?php
				$is_on = tutor_utils()->get_option('email_to_students.quiz_completed');
				if($is_on){
					echo '<span class="result-pass">On</span>';
				}
				?>
            </td>

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
				<?php
				$is_on = tutor_utils()->get_option('email_to_students.completed_course');
				if($is_on){
					echo '<span class="result-pass">On</span>';
				}
				?>
            </td>
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
				<?php
				$is_on = tutor_utils()->get_option('email_to_teachers.a_student_completed_course');
				if($is_on){
					echo '<span class="result-pass">On</span>';
				}
				?>
            </td>
            <td>
                <code>
                    {teacher_username},{student_username},{course_name},{completion_time},{course_url}
                </code>

            </td>
        </tr>
        <tr>
            <td><?php _e('Course Enrolled (to teacher)'); ?></td>
            <td>text/html</td>
            <td>
				<?php
				$is_on = tutor_utils()->get_option('email_to_teachers.a_student_enrolled_in_course');
				if($is_on){
					echo '<span class="result-pass">On</span>';
				}
				?>
            </td>
            <td>
                <code>
                    {teacher_username},{student_username},{course_name},{enroll_time},{course_url}
                </code>

            </td>
        </tr>
        <tr>
            <td><?php _e('Asked Question (to teacher)'); ?></td>
            <td>text/html</td>
            <td>
				<?php
				$is_on = tutor_utils()->get_option('email_to_teachers.a_student_placed_question');
				if($is_on){
					echo '<span class="result-pass">On</span>';
				}
				?>
            </td>
            <td>
                <code>
                    {teacher_username},{student_username},{course_name},{course_url},{question_title},{question}
                </code>
            </td>
        </tr>
        <tr>
            <td><?php _e('Student completed a lesson (to teacher)'); ?></td>
            <td>text/html</td>
            <td>
				<?php
				$is_on = tutor_utils()->get_option('email_to_teachers.a_student_completed_lesson');
				if($is_on){
					echo '<span class="result-pass">On</span>';
				}
				?>
            </td>
            <td>
                <code>
                    {teacher_username},{student_username},{course_name},{lesson_name},{completion_time},{lesson_url}
                </code>
            </td>
        </tr>

        </tbody>
    </table>

</div>
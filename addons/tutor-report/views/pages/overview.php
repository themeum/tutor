<?php
global $wpdb;

$course_post_type = tutor()->course_post_type;
$lesson_type = tutor()->lesson_post_type;

$totalCourse = $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type ='{$course_post_type}' AND post_status = 'publish' ");
$totalCourseEnrolled = $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type ='tutor_enrolled' AND post_status = 'completed' ");
$totalLesson = $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type ='{$lesson_type}' AND post_status = 'publish' ");
$totalQuiz = $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type ='tutor_quiz' AND post_status = 'publish' ");
$totalQuestion = $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type ='tutor_question' AND post_status = 'publish' ");
$totalInstructor = $wpdb->get_var("SELECT COUNT(umeta_id) FROM {$wpdb->usermeta} WHERE meta_key ='_is_tutor_instructor' ");
$totalStudents = $wpdb->get_var("SELECT COUNT(umeta_id) FROM {$wpdb->usermeta} WHERE meta_key ='_is_tutor_student' ");
$totalReviews = $wpdb->get_var("SELECT COUNT(comment_ID) FROM {$wpdb->comments} WHERE comment_type ='tutor_course_rating' AND comment_approved = 'approved' ");

$mostPopularCourses = $wpdb->get_results("
              SELECT COUNT(enrolled.ID) as total_enrolled,
              enrolled.post_parent as course_id,
              course.*
              from {$wpdb->posts} enrolled
              INNER JOIN {$wpdb->posts} course ON enrolled.post_parent = course.ID
              WHERE enrolled.post_type = 'tutor_enrolled' AND enrolled.post_status = 'completed'
              GROUP BY course_id
              ORDER BY total_enrolled DESC LIMIT 0,10 ;");

$lastEnrolledCourses = $wpdb->get_results("
              SELECT MAX(enrolled.post_date) as enrolled_time,
              enrolled.post_parent,
              course.ID,
              course.post_title
              
              from {$wpdb->posts} enrolled
              LEFT JOIN {$wpdb->posts} course ON enrolled.post_parent = course.ID
              WHERE enrolled.post_type = 'tutor_enrolled' AND enrolled.post_status = 'completed'

			  GROUP BY enrolled.post_parent
              ORDER BY enrolled_time DESC LIMIT 0,10 ;");

$reviews = $wpdb->get_results("select {$wpdb->comments}.comment_ID, 
			{$wpdb->comments}.comment_post_ID, 
			{$wpdb->comments}.comment_author, 
			{$wpdb->comments}.comment_author_email, 
			{$wpdb->comments}.comment_date, 
			{$wpdb->comments}.comment_content, 
			{$wpdb->comments}.user_id, 
			{$wpdb->commentmeta}.meta_value as rating,
			{$wpdb->users}.display_name 
			
			from {$wpdb->comments}
			INNER JOIN {$wpdb->commentmeta} 
			ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id 
			INNER  JOIN {$wpdb->users}
			ON {$wpdb->comments}.user_id = {$wpdb->users}.ID
			AND meta_key = 'tutor_rating' ORDER BY comment_ID DESC LIMIT 0,10 ;");


$students = $wpdb->get_results("SELECT SQL_CALC_FOUND_ROWS {$wpdb->users}.* ,
            {$wpdb->usermeta}.meta_value as registered_timestamp
            FROM {$wpdb->users} 
			INNER JOIN {$wpdb->usermeta} 
			ON ( {$wpdb->users}.ID = {$wpdb->usermeta}.user_id ) 
			WHERE 1=1 AND ( {$wpdb->usermeta}.meta_key = '_is_tutor_student' )
			ORDER BY {$wpdb->usermeta}.meta_value DESC 
			LIMIT 0,10 ");

$teachers = $wpdb->get_results("SELECT SQL_CALC_FOUND_ROWS {$wpdb->users}.* ,
            {$wpdb->usermeta}.meta_value as registered_timestamp
            FROM {$wpdb->users} 
			INNER JOIN {$wpdb->usermeta} 
			ON ( {$wpdb->users}.ID = {$wpdb->usermeta}.user_id ) 
			WHERE 1=1 AND ( {$wpdb->usermeta}.meta_key = '_is_tutor_instructor' )
			ORDER BY {$wpdb->usermeta}.meta_value DESC 
			LIMIT 0,10 ");

?>

<div class="tutor-report-overview-wrap">
    <div class="report-stats">
        <div class="report-stat-box">
            <div class="report-stat-box-body">
                <div class="box-icon">
                    <i class="tutor-icon-mortarboard"></i>
                </div>
                <div class="box-stats-text">
                    <h3><?php echo $totalCourse; ?></h3>
                    <p><?php _e('Courses', 'tutor-report'); ?></p>
                </div>
            </div>
        </div>

        <div class="report-stat-box">
            <div class="report-stat-box-body">

                <div class="box-icon">
                    <i class="tutor-icon-graduate"></i>
                </div>
                <div class="box-stats-text">
                    <h3><?php echo $totalCourseEnrolled; ?></h3>
                    <p><?php _e('Course Enrolled', 'tutor-report'); ?></p>
                </div>
            </div>
        </div>

        <div class="report-stat-box">
            <div class="report-stat-box-body">

                <div class="box-icon">
                    <i class="tutor-icon-open-book-1"></i>
                </div>
                <div class="box-stats-text">
                    <h3><?php echo $totalLesson; ?></h3>
                    <p><?php _e('Lessons', 'tutor-report'); ?></p>
                </div>
            </div>
        </div>

        <div class="report-stat-box">
            <div class="report-stat-box-body">

                <div class="box-icon">
                    <i class="tutor-icon-clipboard"></i>
                </div>
                <div class="box-stats-text">
                    <h3><?php echo $totalQuiz; ?></h3>
                    <p><?php _e('Quiz', 'tutor-report'); ?></p>
                </div>
            </div>
        </div>

        <div class="report-stat-box">
            <div class="report-stat-box-body">

                <div class="box-icon">
                    <i class="tutor-icon-conversation-1"></i>
                </div>
                <div class="box-stats-text">
                    <h3><?php echo $totalQuestion; ?></h3>
                    <p><?php _e('Question', 'tutor-report'); ?></p>
                </div>
            </div>
        </div>

        <div class="report-stat-box">
            <div class="report-stat-box-body">

                <div class="box-icon">
                    <i class="tutor-icon-professor"></i>
                </div>
                <div class="box-stats-text">
                    <h3><?php echo $totalInstructor; ?></h3>
                    <p><?php _e('Instructors', 'tutor-report'); ?></p>
                </div>
            </div>
        </div>

        <div class="report-stat-box">
            <div class="report-stat-box-body">

                <div class="box-icon">
                    <i class="tutor-icon-student"></i>
                </div>
                <div class="box-stats-text">
                    <h3><?php echo $totalStudents; ?></h3>
                    <p><?php _e('Students', 'tutor-report'); ?></p>
                </div>
            </div>
        </div>

        <div class="report-stat-box">
            <div class="report-stat-box-body">

                <div class="box-icon">
                    <i class="tutor-icon-review"></i>
                </div>
                <div class="box-stats-text">
                    <h3><?php echo $totalReviews; ?></h3>
                    <p><?php _e('Reviews Placed', 'tutor-report'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="tutor-report-overview-section tutor-bg-white box-padding">
        <div class="tutor-overview-month-graph" style="width: 100%;">

            <h3><?php echo sprintf(__('Student enrolment graph for %s', 'tutor-report'), date('F')); ?></h3>
			<?php
			/**
			 * Getting the last week
			 */
			$start_week = date("Y-m-01");
			$end_week = date("Y-m-t");
			/**
			 * Format Date Name
			 */
			$begin = new DateTime($start_week);
			$end = new DateTime($end_week.' + 1 day');
			$interval = DateInterval::createFromDateString('1 day');
			$period = new DatePeriod($begin, $interval, $end);

			$datesPeriod = array();
			foreach ($period as $dt) {
				$datesPeriod[$dt->format("Y-m-d")] = 0;
			}
			/**
			 * Query last week
			 */
			$enrolledQuery = $wpdb->get_results( "
              SELECT COUNT(ID) as total_enrolled, 
              DATE(post_date)  as date_format 
              from {$wpdb->posts} 
              WHERE post_type = 'tutor_enrolled' 
              AND (post_date BETWEEN '{$start_week}' AND '{$end_week}')
              GROUP BY date_format
              ORDER BY post_date ASC ;");

			$total_enrolled = wp_list_pluck($enrolledQuery, 'total_enrolled');
			$queried_date = wp_list_pluck($enrolledQuery, 'date_format');
			$dateWiseEnrolled = array_combine($queried_date, $total_enrolled);

			$chartData = array_merge($datesPeriod, $dateWiseEnrolled);
			foreach ($chartData as $key => $enrolledCount){
				unset($chartData[$key]);
				$formatDate = date('d', strtotime($key));
				$chartData[$formatDate] = $enrolledCount;
			}

			?>

            <p><?php _e('Total Enrolled Course'); ?> <?php echo array_sum($chartData); ?> </p>

            <canvas id="myChart" style="width: 100%; height: 400px;"></canvas>
            <script>
                var ctx = document.getElementById("myChart").getContext('2d');
                var myChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode(array_keys($chartData)); ?>,
                        datasets: [{
                            label: 'Enrolled',
                            backgroundColor: '#3057D5',
                            borderColor: '#3057D5',
                            data: <?php echo json_encode(array_values($chartData)); ?>,
                            borderWidth: 2,
                            fill: false,
                            lineTension: 0,
                        }]
                    },
                    options: {
                        scales: {
                            yAxes: [{
                                ticks: {
                                    min: 0, // it is for ignoring negative step.
                                    beginAtZero: true,
                                    callback: function(value, index, values) {
                                        if (Math.floor(value) === value) {
                                            return value;
                                        }
                                    }
                                }
                            }]
                        },

                        legend: {
                            display: false
                        }
                    }
                });
            </script>


        </div>


    </div>


    <div class="tutor-report-overview-section">

        <div class="overview-section-col6 tutor-bg-white box-padding">
            <h3><?php _e('Most popular courses'); ?></h3>

            <table class="widefat tutor-report-table ">
                <tr>
                    <th><?php _e('Course Name', 'tutor-report'); ?> </th>
                    <th><?php _e('Enrolled', 'tutor-report'); ?> </th>
                </tr>
				<?php
				if (is_array($mostPopularCourses) && count($mostPopularCourses)){
					foreach ($mostPopularCourses as $course){
						?>
                        <tr>
                            <td><a href="<?php echo get_the_permalink($course->ID); ?>" target="_blank"><?php echo $course->post_title; ?></a> </td>
                            <td><?php echo $course->total_enrolled; ?></td>
                        </tr>
						<?php
					}
				}
				?>
            </table>
        </div>

        <div class="overview-section-col6 tutor-bg-white box-padding">

            <h3><?php _e('Last enrolled courses'); ?></h3>

            <table class="widefat tutor-report-table ">
                <tr>
                    <td><?php _e('Course Name', 'tutor-report'); ?> </td>
                    <td><?php _e('Enrolled', 'tutor-report'); ?> </td>
                </tr>
				<?php
				if (is_array($lastEnrolledCourses) && count($lastEnrolledCourses)){
					foreach ($lastEnrolledCourses as $course){
						?>
                        <tr>
                            <td><a href="<?php echo get_the_permalink($course->ID); ?>"><?php echo $course->post_title; ?></a> </td>
                            <td><?php echo human_time_diff(strtotime($course->enrolled_time)).' '.__('ago', 'tutor-report'); ?></td>
                        </tr>
						<?php
					}
				}
				?>
            </table>

        </div>


    </div>


    <div class="tutor-report-overview-section tutor-bg-white box-padding">

        <div class="last-revews-wrap">

            <h3><?php _e('Last reviews'); ?></h3>

            <table class="widefat tutor-report-table ">
                <tr>
                    <th><?php _e('User', 'tutor-report'); ?> </th>
                    <th><?php _e('Course', 'tutor-report'); ?> </th>
                    <th><?php _e('Rating', 'tutor-report'); ?> </th>
                    <th><?php _e('Reviews', 'tutor-report'); ?> </th>
                    <th><?php _e('Time', 'tutor-report'); ?> </th>
                </tr>
				<?php
				if (is_array($reviews) && count($reviews)){
					foreach ($reviews as $review){
						?>
                        <tr>
                            <td><a href="<?php echo tutor_utils()->profile_url($review->user_id); ?>" target="_blank"><?php echo $review->display_name; ?></a> </td>
                            <td><a href="<?php echo get_the_permalink($review->comment_post_ID); ?>" target="_blank"><?php echo get_the_title
									($review->comment_post_ID);
									?></a> </td>
                            <td><?php tutor_utils()->star_rating_generator($review->rating, true); ?></td>
                            <td><?php echo wpautop($review->comment_content); ?></td>
                            <td><?php echo human_time_diff(strtotime($review->comment_date)).' '.__('ago', 'tutor-report'); ?></td>
                        </tr>
						<?php
					}
				}
				?>
            </table>

        </div>

    </div>



    <div class="tutor-report-overview-section">

        <div class="last-revews-wrap tutor-bg-white box-padding">

            <h3><?php _e('Last questions for student'); ?></h3>

            <table class="widefat tutor-report-table ">
                <th><?php _e('User', 'tutor-report'); ?> </th>
                <th><?php _e('Question', 'tutor-report'); ?> </th>
                <th><?php _e('Course', 'tutor-report'); ?> </th>

				<?php

				$questions = tutor_utils()->get_qa_questions();

				if (is_array($questions) && count($questions)){
					foreach ($questions as $question){
						?>
                        <tr>
                            <td><?php echo $question->display_name; ?></td>
                            <td>
                                <a href="<?php echo add_query_arg(array('page'=> 'question_answer', 'sub_page' => 'answer', 'question_id' => $question->comment_ID), admin_url('admin.php')) ?>" target="_blank">
									<?php echo $question->comment_content; ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?php echo get_the_permalink($question->comment_post_ID); ?>" target="_blank">
									<?php echo $question->post_title; ?>
                                </a>
                            </td>
                        </tr>
						<?php
					}
				}
				?>
            </table>

        </div>

    </div>


</div>






<div class="tutor-report-overview-section">

    <div class="overview-section-col6 tutor-bg-white box-padding">
        <h3><?php _e('Last registered students'); ?></h3>

        <table class="widefat tutor-report-table ">
            <tr>
                <td><?php _e('Name', 'tutor-report'); ?> </td>
                <td><?php _e('E-Mail', 'tutor-report'); ?> </td>
                <td><?php _e('Registered at', 'tutor-report'); ?> </td>
            </tr>
			<?php
			if (is_array($students) && count($students)){
				foreach ($students as $student){
					?>
                    <tr>
                        <td>
                            <a href="<?php echo tutor_utils()->profile_url($student->ID); ?>">
                                <?php echo get_avatar($student->user_email, 25); ?>
                                <?php echo $student->display_name; ?>
                            </a>
                        </td>
                        <td><?php echo $student->user_email; ?> </td>
                        <td><?php echo human_time_diff($student->registered_timestamp).' '.__('ago', 'tutor-report'); ?></td>
                    </tr>
					<?php
				}
			}
			?>
        </table>
    </div>

    <div class="overview-section-col6 tutor-bg-white box-padding">
        <h3><?php _e('Last registered Teachers'); ?></h3>

        <table class="widefat tutor-report-table ">
            <tr>
                <td><?php _e('Name', 'tutor-report'); ?> </td>
                <td><?php _e('E-Mail', 'tutor-report'); ?> </td>
                <td><?php _e('Registered at', 'tutor-report'); ?> </td>
            </tr>
			<?php
			if (is_array($teachers) && count($teachers)){
				foreach ($teachers as $teacher){
					?>
                    <tr>
                        <td>
                            <a href="<?php echo tutor_utils()->profile_url($teacher->ID); ?>">
		                        <?php echo get_avatar($teacher->user_email, 25); ?>
		                        <?php echo $teacher->display_name; ?>
                            </a>
                        </td>
                        <td><?php echo $teacher->user_email; ?> </td>
                        <td><?php echo human_time_diff($teacher->registered_timestamp).' '.__('ago', 'tutor-report'); ?></td>
                    </tr>
					<?php
				}
			}
			?>
        </table>

    </div>


</div>

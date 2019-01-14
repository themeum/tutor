<div class="tutor-report-chart tutor-bg-white box-padding">

	<?php
    echo '<h3>';
    switch ($sub_page){
        case 'this_year';
	        echo sprintf(__("Showing Result for the year %s", 'tutor-report'), $currentYear);
	        break;
	    case 'last_year';
		    echo sprintf(__("Showing Result for the year %s", 'tutor-report'), $lastYear);
		    break;
	    case 'last_month';
		    echo sprintf(__("Showing Result for the month of %s", 'tutor-report'), date("F, Y", strtotime($start_date)));
		    break;
	    case 'this_month';
		    echo sprintf(__("Showing Result for the month of %s", 'tutor-report'), date("F, Y"));
		    break;
	    case 'last_week';
		    echo sprintf(__("Showing Result from %s to %s", 'tutor-report'), $begin->format('d F, Y'), $end->format('d F, Y'));
		    break;
	    case 'this_week';
		    echo sprintf(__("Showing Result from %s to %s", 'tutor-report'), $begin->format('d F, Y'), $end->format('d F, Y'));
		    break;
	    case 'date_range';
		    echo sprintf(__("Showing Result from %s to %s", 'tutor-report'), $begin->format('d F, Y'), $end->format('d F, Y'));
		    break;
    }
    echo '</h3>';

    if ($course_id){
		echo '<h4>'.__('Results for course : ', 'tutor-report').get_the_title($course_id).'</h4>';
	}
	?>

    <p class="text-muted">
        <?php _e('Total Enrolled Course'); ?> <?php echo array_sum($chartData); ?>
        <span class="report-download-csv-icon">
            <a href="<?php echo add_query_arg(array('tutor_report_action' => 'download_course_enrol_csv')); ?>"><i class="tutor-icon-file"></i> <?php _e('Download as CSV');
            ?></a>
        </span>
    </p>


	<?php
	include TUTOR_REPORT()->path.'views/pages/courses/top_menu.php';
	?>

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


<?php
if (! $course_id){
	?>
	<div class="top-course-enrolled tutor-bg-white box-padding">
		<h3><?php _e('Top enrolled courses', 'tutor-report'); ?></h3>

		<table class="widefat tutor-report-table ">
			<tr>
				<th><?php _e('Course', 'tutor-report'); ?></th>
				<th><?php _e('Total Enrolled', 'tutor-report'); ?></th>
				<th>#</th>
			</tr>

			<?php
			foreach ($enrolledProduct as $course){
				?>
				<tr>
					<td><a href="<?php echo add_query_arg(array('course_id' => $course->ID)) ?>"><?php echo $course->post_title; ?></a> </td>
					<td><?php echo $course->total_enrolled; ?></td>
					<td><a href="<?php echo get_the_permalink($course->ID) ?>" target="_blank">View </a> </td>
				</tr>
				<?php
			}
			?>
		</table>
	</div>
<?php } ?>
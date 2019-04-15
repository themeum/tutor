
<canvas id="tutorChart" style="width: 100%; height: 400px;"></canvas>
<script>
    var ctx = document.getElementById("tutorChart").getContext('2d');
    var tutorChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_keys($chartData)); ?>,
            datasets: [{
                label: 'Earning',
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


<h3>Sales statements for this period</h3>


<?php

if ($statements->count) {
	?>

    <table class="tutor-dashboard-statement-table">
        <tr>
            <th><?php _e('Course', 'tutor'); ?></th>
            <th><?php _e('Earning', 'tutor'); ?></th>
            <th><?php _e('Deduct', 'tutor'); ?></th>
        </tr>

		<?php
		foreach ($statements->results as $statement){
			?>
            <tr>
                <td>
                    <p>
                        <a href="<?php echo get_the_permalink($statement->course_id); ?>" target="_blank">
							<?php echo $statement->course_title; ?>
                        </a>
                    </p>

                    <p>
						<?php _e('Price', 'tutor'); ?>
						<?php echo tutor_utils()->tutor_price($statement->course_price_total); ?>
                    </p>

                    <p class="small-text">
                        <span class="statement-order-<?php echo $statement->order_status; ?>"><?php echo $statement->order_status; ?></span> <?php
						_e('Order ID'); ?> #<?php echo $statement->order_id; ?>,

                        <strong><?php _e('Date:', 'tutor') ?></strong>
                        <i><?php echo date(get_option('date_format', strtotime($statement->created_at))).' '.date(get_option('time_format', strtotime($statement->created_at))) ?></i>
                    </p>

					<?php
					$order = new WC_Order($statement->order_id);
					echo '<div class="statement-address"> <strong>Purchaser</strong> <address>'.$order->get_formatted_billing_address().'</address></div>';
					?>
                </td>
                <td>
                    <p><?php echo tutor_utils()->tutor_price($statement->instructor_amount); ?></p>
                    <p class="small-text"> <?php _e('As per');  ?> <?php echo $statement->instructor_rate ?> (<?php echo $statement->commission_type ?>) </p>
                </td>

                <td>
                    <p><?php _e('Commission', 'tutor'); ?> : <?php echo tutor_utils()->tutor_price($statement->admin_amount); ?> </p>
                    <p class="small-text"><?php _e('Rate', 'tutor'); ?> : <?php echo $statement->admin_rate; ?> </p>
                    <p class="small-text"><?php _e('Type', 'tutor'); ?> : <?php echo $statement->commission_type; ?> </p>

                    <p><?php _e('Deducted', 'tutor'); ?> : <?php echo $statement->deduct_fees_name; ?>  <?php echo tutor_utils()->tutor_price
						($statement->deduct_fees_amount); ?>
                    </p>
                    <p class="small-text"><?php _e('Type', 'tutor'); ?> : <?php echo $statement->deduct_fees_type; ?> </p>
                </td>
            </tr>
			<?php
		}
		?>
    </table>

	<?php
}

//echo '<pre>';
//die(print_r($statements));


?>
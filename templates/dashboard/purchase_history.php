<h2>Purchase History</h2>

<?php
$orders = tutor_utils()->get_orders_by_user_id();

if (tutor_utils()->count($orders)){
	?>
	<table>
		<tr>
			<th>ID</th>
			<th>Courses</th>
			<th>Amount</th>
			<th>Date</th>
		</tr>
		<?php
		foreach ($orders as $order){
			$wc_order = wc_get_order($order->ID);
			?>
			<tr>
				<td>#<?php echo $order->ID; ?></td>
				<td>
					<?php
					$courses = tutor_utils()->get_course_enrolled_ids_by_order_id($order->ID);
					if (tutor_utils()->count($courses)){
						foreach ($courses as $course){
							echo '<p>'.get_the_title($course['course_id']).'</p>';
						}
					}
					?>
				</td>
				<td><?php echo tutor_utils()->tutor_price($wc_order->get_total()); ?></td>
				<td>
					<?php echo date_i18n(get_option('date_format'), strtotime($order->post_date)) ?>
				</td>
			</tr>
			<?php
		}
		?>
	</table>
	<?php
}else{
	echo _e('No purchase history available', 'tutor');
}

?>

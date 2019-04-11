<?php
/**
 * Template for displaying instructors earnings
 *
 * @since v.1.1.2
 *
 * @author Themeum
 * @url https://themeum.com
 */

global $wpdb;

$user_id = get_current_user_id();

/**
 * Getting the This Week
 */

$start_date = date("Y-m-d", strtotime("last sunday midnight"));
$end_date = date("Y-m-d", strtotime("next saturday"));


$earning_sum = tutor_utils()->get_earning_sum($user_id, compact('start_date', 'end_date'));
if ( ! $earning_sum){
	echo '<p>'.__('No Earning info available', 'tutor' ).'</p>';
	return;
}

$complete_status = tutor_utils()->get_earnings_completed_statuses();
$statuses = $complete_status;
$complete_status = "'".implode("','", $complete_status)."'";

/**
 * Format Date Name
 */
$begin = new DateTime($start_date);
$end = new DateTime($end_date.' + 1 day');
$interval = DateInterval::createFromDateString('1 day');
$period = new DatePeriod($begin, $interval, $end);

$datesPeriod = array();
foreach ($period as $dt) {
	$datesPeriod[$dt->format("Y-m-d")] = 0;
}

/**
 * Query This Month
 */

$salesQuery = $wpdb->get_results( "
              SELECT SUM(instructor_amount) as total_earning, 
              DATE(created_at)  as date_format 
              from {$wpdb->prefix}tutor_earnings 
              WHERE user_id = {$user_id} AND order_status IN({$complete_status}) 
              AND (created_at BETWEEN '{$start_date}' AND '{$end_date}')
              GROUP BY date_format
              ORDER BY created_at ASC ;");

$total_earning = wp_list_pluck($salesQuery, 'total_earning');
$queried_date = wp_list_pluck($salesQuery, 'date_format');
$dateWiseSales = array_combine($queried_date, $total_earning);

$chartData = array_merge($datesPeriod, $dateWiseSales);
foreach ($chartData as $key => $salesCount){
	unset($chartData[$key]);
	$formatDate = date('d M', strtotime($key));
	$chartData[$formatDate] = $salesCount;
}

$statements = tutor_utils()->get_earning_statements(null, compact('start_date', 'end_date', 'statuses'));
?>

    <div class="tutor-dashboard-earning-info-row">

        <div class="tutor-dashboard-earning-sum">
            <h3><?php echo tutor_utils()->tutor_price($earning_sum->instructor_amount); ?></h3>
            <p><?php _e('My Earning', 'tutor'); ?></p>
            <p class="text-small"><?php _e('All time', 'tutor'); ?></p>
        </div>

        <div class="tutor-dashboard-earning-sum">
            <h3><?php echo tutor_utils()->tutor_price($earning_sum->course_price_total); ?></h3>
            <p><?php _e('All time sales.', 'tutor'); ?></p>
            <p class="text-small"><?php _e('Based on course price.', 'tutor'); ?></p>
        </div>

        <div class="tutor-dashboard-earning-sum">
            <h3><?php echo tutor_utils()->tutor_price($earning_sum->admin_amount); ?></h3>
            <p><?php _e('Deducted Commissions', 'tutor'); ?></p>
        </div>

		<?php
		if ($earning_sum->deduct_fees_amount > 0){
			?>
            <div class="tutor-dashboard-earning-sum">
                <h3><?php echo tutor_utils()->tutor_price($earning_sum->deduct_fees_amount); ?></h3>
                <p><?php _e('Deducted Fees', 'tutor'); ?></p>
            </div>
		<?php } ?>
    </div>



<h4><?php echo sprintf(__("Showing Result from %s to %s", 'tutor-report'), $begin->format('d F, Y'), $end->format('d F, Y')); ?></h4>

<?php tutor_load_template('dashboard.earning.chart-body', compact('chartData', 'statements')); ?>
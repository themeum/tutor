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
 * Getting the Last Month
 */
$year = date('Y');
$dataFor = 'yearly';

$earning_sum = tutor_utils()->get_earning_sum($user_id, compact('year', 'dataFor'));


if ( ! $earning_sum){
	echo '<p>'.__('No Earning info available', 'tutor' ).'</p>';
	return;
}

$complete_status = tutor_utils()->get_earnings_completed_statuses();
$statuses = $complete_status;
$complete_status = "'".implode("','", $complete_status)."'";


/**
 * Query This Month
 */

$salesQuery = $wpdb->get_results( "
              SELECT SUM(instructor_amount) as total_earning, 
              MONTHNAME(created_at)  as month_name 
              from {$wpdb->prefix}tutor_earnings 
              WHERE user_id = {$user_id} AND order_status IN({$complete_status}) 
              AND YEAR(created_at) = {$year} 
              GROUP BY MONTH (created_at) 
              ORDER BY MONTH(created_at) ASC ;");

$total_earning = wp_list_pluck($salesQuery, 'total_earning');
$months = wp_list_pluck($salesQuery, 'month_name');
$monthWiseSales = array_combine($months, $total_earning);

/**
 * Format yearly
 */
$emptyMonths = array();
for ($m=1; $m<=12; $m++) {
	$emptyMonths[date('F', mktime(0,0,0,$m, 1, date('Y')))] = 0;
}
$chartData = array_merge($emptyMonths, $monthWiseSales);

$statements = tutor_utils()->get_earning_statements($user_id, compact('year', 'dataFor', 'statuses'));


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

    <h4><?php echo sprintf(__("Earning Data for the year of %s", 'tutor-report'), $year);?></h4>

<?php tutor_load_template('dashboard.earning.chart-body', compact('chartData', 'statements')); ?>
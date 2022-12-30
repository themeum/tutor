<?php
/**
 * Purchase history
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Input;

// Global variables.
$user_id     = get_current_user_id();
$active      = Input::get( 'period', '' );
$time_period = $active;
$start_date  = Input::get( 'start_date', '' );
$end_date    = Input::get( 'end_date', '' );

$paged    = Input::get( 'current_page', 1, Input::TYPE_INT );
$per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
$offset   = ( $per_page * $paged ) - $per_page;
if ( '' !== $start_date ) {
	$start_date = tutor_get_formated_date( 'Y-m-d', $start_date );
}

if ( '' !== $end_date ) {
	$end_date = tutor_get_formated_date( 'Y-m-d', $end_date );
}

/**
 * Prepare filter period buttons
 *
 * Array structure is required as below
 *
 * @since 2.0.0
 */
$filter_period = array(
	array(
		'url'   => esc_url( tutor_utils()->tutor_dashboard_url() . 'purchase_history?period=today' ),
		'title' => __( 'Today', 'tutor' ),
		'type'  => 'today',
	),
	array(
		'url'   => esc_url( tutor_utils()->tutor_dashboard_url() . 'purchase_history?period=monthly' ),
		'title' => __( 'Monthly', 'tutor' ),
		'type'  => 'monthly',
	),
	array(
		'url'   => esc_url( tutor_utils()->tutor_dashboard_url() . 'purchase_history?period=yearly' ),
		'title' => __( 'Yearly', 'tutor' ),
		'type'  => 'yearly',
	),
);

/**
 * Calendar date buttons
 *
 * Array structure is required as below
 *
 * @since 2.0.0
 */

$filter_period_calendar = array(
	'filter_period'   => $filter_period,
	'filter_calendar' => true,
);

$filter_period_calendar_template = tutor()->path . 'views/elements/purchase-history-filter.php';
tutor_load_template_from_custom_path( $filter_period_calendar_template, $filter_period_calendar );

$orders       = tutor_utils()->get_orders_by_user_id( $user_id, $time_period, $start_date, $end_date, $offset, $per_page );
$total_orders = tutor_utils()->get_total_orders_by_user_id( $user_id, $time_period, $start_date, $end_date );
$monetize_by  = tutor_utils()->get_option( 'monetize_by' );

?>

<div class="tutor-purchase-history"> 
	<?php if ( tutor_utils()->count( $orders ) ) : ?>
		<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24"><?php esc_html_e( 'Order History', 'tutor' ); ?></div>
		<div class="tutor-table-responsive">
			<table class="tutor-table">
				<thead>
					<th width="10%">
						<?php esc_html_e( 'Order ID', 'tutor' ); ?>
					</th>
					<th width="40%">
						<?php esc_html_e( 'Course Name', 'tutor' ); ?>
					</th>
					<th>
						<?php esc_html_e( 'Date', 'tutor' ); ?>
					</th>
					<th>
						<?php esc_html_e( 'Price', 'tutor' ); ?>
					</th>
					<th>
						<?php esc_html_e( 'Status', 'tutor' ); ?>
					</th>
					<th></th>
				</thead>

				<tbody>
					<?php foreach ( $orders as $order ) : ?>
						<?php
						if ( 'wc' === $monetize_by ) {
							$wc_order          = wc_get_order( $order->ID );
							$price             = tutor_utils()->tutor_price( $wc_order->get_total() );
							$raw_price         = $wc_order->get_total();
							$status            = $order->post_status;
							$badge_class       = 'primary';
							$order_status_text = '';

							switch ( $status ) {
								case 'wc-completed' === $status:
									$badge_class       = 'success';
									$order_status_text = __( 'Completed', 'tutor' );
									break;
								case 'wc-processing' === $status:
									$badge_class       = 'warning';
									$order_status_text = __( 'Processing', 'tutor' );
									break;
								case 'wc-on-hold' === $status:
									$badge_class       = 'warning';
									$order_status_text = __( 'On Hold', 'tutor' );
									break;
								case 'wc-refunded' === $status:
									$badge_class       = 'danger';
									$order_status_text = __( 'Processing', 'tutor' );
									break;
								case 'wc-cancelled' === $status:
									$badge_class       = 'danger';
									$order_status_text = __( 'Cancelled', 'tutor' );
									break;
								case 'wc-pending' === $status:
									$badge_class       = 'warning';
									$order_status_text = __( 'Pending', 'tutor' );
									break;
							}
						} elseif ( 'edd' === $monetize_by ) {
							$edd_order         = edd_get_payment( $order->ID );
							$price             = edd_currency_filter( edd_format_amount( $edd_order->total ), edd_get_payment_currency_code( $order->ID ) );
							$raw_price         = $edd_order->total;
							$status            = $edd_order->status_nicename;
							$badge_class       = 'primary';
							$order_status_text = $status;
						}
						?>
						<tr>
							<td>
								#<?php echo esc_html( $order->ID ); ?>
							</td>

							<td>
								<?php
									$courses = tutor_utils()->get_course_enrolled_ids_by_order_id( $order->ID );
								if ( tutor_utils()->count( $courses ) ) {
									foreach ( $courses as $course ) {
										echo '<div>' . esc_html( get_the_title( $course['course_id'] ) ) . '</div>';
									}
								}
								?>
							</td>

							<td>
								<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $order->post_date ) ) ); ?>
							</td>

							<td>
								<?php echo wp_kses_post( $price ); ?>
							</td>

							<td>
								<span class="tutor-badge-label label-<?php echo esc_attr( $badge_class ); ?> tutor-m-4"><?php echo esc_html( $order_status_text ); ?></span>
							</td>
							
							<td>
								<?php
									$pay_now_url = '';
								if ( 'wc' === $monetize_by ) {
									$wc_order    = wc_get_order( $order->ID );
									$pay_now_url = esc_url( $wc_order->get_checkout_payment_url() );
								}
								?>
								<div class="tutor-order-history-actions">
									<?php if ( 'wc-pending' === $status ) : ?>
									<a href="<?php echo esc_url( $pay_now_url ); ?>" 
										class="tutor-btn tutor-btn-outline-primary tutor-btn-sm tutor-mr-8">
										<?php esc_html_e( 'Pay', 'tutor-pro' ); ?>	
									</a>
									<?php endif; ?>
									<a href="javascript:;" class="tutor-export-purchase-history tutor-iconic-btn tutor-iconic-btn-secondary" data-order="<?php echo esc_attr( $order->ID ); ?>" data-course-name="<?php echo esc_attr( get_the_title( $course['course_id'] ) ); ?>" data-price="<?php echo esc_attr( $raw_price ); ?>" data-date="<?php echo esc_attr( date_i18n( get_option( 'date_format' ), strtotime( $order->post_date ) ) ); ?>" data-status="<?php echo esc_attr( $order_status_text ); ?>">
										<span class="tutor-icon-receipt-line" area-hidden="true"></span>
									</a>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<?php
			$pagination_data = array(
				'total_items' => ! empty( $total_orders ) ? count( $total_orders ) : 0,
				'per_page'    => $per_page,
				'paged'       => $paged,
			);

			$total_page = ceil( $pagination_data['total_items'] / $pagination_data['per_page'] );

			if ( $total_page > 1 ) {
				$pagination_template = tutor()->path . 'templates/dashboard/elements/pagination.php';
				tutor_load_template_from_custom_path( $pagination_template, $pagination_data );
			}
			?>
	<?php else : ?>
		<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
	<?php endif; ?>
</div>

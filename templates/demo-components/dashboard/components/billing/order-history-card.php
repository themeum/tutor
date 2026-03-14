<?php
/**
 * Dashboard Order History Card
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
use Tutor\Ecommerce\Ecommerce;

$render_status_badge = function ( $status ) {
	$badge_class = 'secondary';

	if ( 'completed' === $status ) {
		return '';
	}

	// @TODO: Need to recheck the status.
	switch ( $status ) {
		case 'processing':
		case 'pending':
		case 'on-hold':
			$badge_class = 'warning';
			break;
		case 'refunded':
		case 'cancelled':
			$badge_class = 'error';
			break;
		case 'incomplete':
			$badge_class = '';
			break;
	}

	return '<span class="tutor-capitalize tutor-badge tutor-badge-rounded tutor-badge-' . $badge_class . '">' . esc_html( $status ) . '</span>';
}

?>

<div class="tutor-billing-card">
	<div class="tutor-billing-card-left">
		<div class="tutor-billing-card-title">
			<div class="tutor-hidden tutor-sm-block">
				<?php echo wp_kses_post( $render_status_badge( $order_status ) ); ?>
			</div>
			<ul>
				<?php foreach ( $items as $item ) : ?>
					<li>
						<span><?php echo esc_html( $item['title'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
			<div class="tutor-sm-hidden">
				<?php echo wp_kses_post( $render_status_badge( $order_status ) ); ?>
			</div>
		</div>
		<div class="tutor-billing-card-details">
			<div class="tutor-billing-card-id">
				#<?php echo esc_html( $id ); ?>
			</div>

			<span>
				<?php echo esc_html( date_i18n( 'D, ' . get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), strtotime( $created_at_gmt ?? '' ) ) ); ?>
			</span>

			<span class="tutor-section-separator-vertical tutor-sm-hidden"></span>

			<div class="tutor-billing-card-payment-method">
				<!-- @TODO: Need to map svg icon or image to payment method -->
				<?php tutor_utils()->render_svg_icon( Icon::LESSON, 12, 12 ); ?>
				<div class="tutor-sm-hidden">
					<?php echo esc_html( Ecommerce::get_payment_method_label( $payment_method ?? '' ) ); ?>
				</div>
			</div>
		</div>
	</div>

	<div class="tutor-billing-card-right">
		<div class="tutor-billing-card-amount">
			<?php echo esc_html( tutor_get_formatted_price( $total_price ) ); ?>
		</div>

		<a class="tutor-btn tutor-btn-link tutor-text-brand tutor-p-none tutor-min-h-fit" href="<?php echo esc_url( add_query_arg( 'invoice', $id, get_permalink( get_page_by_path( 'invoice' ) ) ) ); ?>">
			<!-- @TODO: Need to render pay button here when payment has not been processed yet. -->
			<?php esc_html_e( 'Receipt', 'tutor' ); ?>
		</a>
	</div>
</div>
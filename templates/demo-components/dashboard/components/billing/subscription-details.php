<?php
/**
 * Dashboard Subscription Details
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
use Tutor\Ecommerce\Ecommerce;

$render_status_badge = function ( $status, $price ) {
	$badge_class = 'secondary';
	$receipt_url = add_query_arg( 'invoice', '223', get_permalink( get_page_by_path( 'invoice' ) ) );

	if ( 'completed' === $status ) {
		$formatted_price = esc_html( tutor_get_formatted_price( $price ) );
		return '<div class="tutor-text-secondary tutor-text-small tutor-font-semibold">' . $formatted_price . '</div><a class="tutor-btn tutor-btn-link tutor-text-brand tutor-p-none tutor-min-h-fit" href="' . esc_url( $receipt_url ) . '">' . esc_html__( 'Receipt', 'tutor' ) . '</a>';

	}

	// @TODO: Need to recheck the status.
	switch ( $status ) {
		case 'processing':
		case 'pending':
		case 'on-hold':
			$badge_class = 'warning';
			break;
		case 'failed':
		case 'expired':
			$badge_class = 'error';
			break;
		case 'incomplete':
			$badge_class = '';
			break;
	}

	return '<span class="tutor-capitalize tutor-badge tutor-badge-rounded tutor-badge-' . $badge_class . '">' . esc_html( $status ) . '</span>';
};

$payments = array(
	array(
		'id'                    => '43516',
		'user_id'               => '43516',
		'plan_id'               => '43516',
		'order_id'              => '43516',
		'status'                => 'completed',
		'auto_renew'            => '1',
		'is_trial_enabled'      => '1',
		'is_trial_used'         => '1',
		'regular_price'         => '79.00',
		'sale_price'            => '79.00',
		'enrollment_fee'        => '0.00',
		'coupon_code'           => '',
		'order_price'           => '79.00',
		'payment_method'        => 'paypal',
		'payment_payload'       => null,
		'created_at_gmt'        => '2025-11-17 09:34:14',
		'updated_at_gmt'        => '2025-11-17 09:34:14',
		'first_order_id'        => '43516',
		'active_order_id'       => '43516',
		'trial_end_date_gmt'    => '2025-11-17 09:34:14',
		'start_date_gmt'        => '2025-11-17 09:34:14',
		'end_date_gmt'          => '2025-11-17 09:34:14',
		'next_payment_date_gmt' => '2026-11-17 09:34:14',
		'note'                  => 'Auto renew failed',
		'plan_name'             => 'Auto Renew',
		'plan_type'             => 'category',
		'user_login'            => 'blind',
	),
	array(
		'id'                    => '8',
		'user_id'               => '1',
		'plan_id'               => '110',
		'order_id'              => '0',
		'trx_id'                => '',
		'status'                => 'expired',
		'auto_renew'            => '1',
		'is_trial_enabled'      => '0',
		'is_trial_used'         => '0',
		'regular_price'         => '0.00',
		'sale_price'            => '',
		'enrollment_fee'        => '0.00',
		'coupon_code'           => '',
		'order_price'           => '',
		'payment_method'        => 'stripe',
		'payment_payload'       => '',
		'created_at_gmt'        => '2025-09-30 07:41:04',
		'updated_at_gmt'        => '2025-09-30 07:41:04',
		'first_order_id'        => '3144',
		'active_order_id'       => '43514',
		'trial_end_date_gmt'    => '',
		'start_date_gmt'        => '2025-09-30 07:41:47',
		'end_date_gmt'          => '2025-10-30 07:41:47',
		'next_payment_date_gmt' => '2026-11-30 07:41:47',
		'note'                  => 'Subscription expired',
		'plan_name'             => 'Neuro Explorer',
		'plan_type'             => 'course',
		'user_login'            => 'blind',
	),
	array(
		'id'                    => '9',
		'user_id'               => '1',
		'plan_id'               => '110',
		'order_id'              => '0',
		'trx_id'                => '',
		'status'                => 'pending',
		'auto_renew'            => '1',
		'is_trial_enabled'      => '0',
		'is_trial_used'         => '0',
		'regular_price'         => '0.00',
		'sale_price'            => '',
		'enrollment_fee'        => '0.00',
		'coupon_code'           => '',
		'order_price'           => '',
		'payment_method'        => 'paypal',
		'payment_payload'       => '',
		'created_at_gmt'        => '2025-09-30 07:41:04',
		'updated_at_gmt'        => '2025-09-30 07:41:04',
		'first_order_id'        => '3144',
		'active_order_id'       => '43514',
		'trial_end_date_gmt'    => '',
		'start_date_gmt'        => '2025-09-30 07:41:47',
		'end_date_gmt'          => '2025-10-30 07:41:47',
		'next_payment_date_gmt' => '2026-11-30 07:41:47',
		'note'                  => 'Subscription expired',
		'plan_name'             => 'Neuro Explorer',
		'plan_type'             => 'course',
		'user_login'            => 'blind',
	),
);

?>

<div class="tutor-subscription-details">
	<div class="tutor-subscription-overview">
		<!-- Overview -->
		<div class="tutor-flex tutor-justify-between tutor-w-full">
			<div class="tutor-flex tutor-flex-column">
				<div class="tutor-subscription-overview-subtitle">
					<?php
					printf(
						// translators: Subscription ID.
						esc_html__( 'Subscription ID: #%s', 'tutor' ),
						'9'
					);
					?>
				</div>
				<div class="tutor-subscription-overview-title">
					<!-- @TODO: This will be dynamic once we have the subscription name. -->
					Subscription Name
				</div>
				<div class="tutor-subscription-overview-badge">
					<?php tutor_utils()->render_svg_icon( Icon::CHECK_2 ); ?>
					<!-- @TODO: This will be dynamic once we have the subscription type. -->
					Full Site Access
				</div>
			</div>
			<div class="tutor-flex tutor-items-start tutor-gap-4">
				<!-- @TODO: This will be dynamic once we have the subscription status. -->
				<div class="tutor-subscription-overview-status">
					<?php tutor_utils()->render_svg_icon( Icon::ACTIVE ); ?>
					Active
				</div>
				<button class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
					<?php tutor_utils()->render_svg_icon( Icon::ELLIPSES ); ?>
				</button>
			</div>
		</div>

		<!-- Overview Cards -->
		<div class="tutor-subscription-overview-cards">
			<!-- Amount -->
			<div class="tutor-subscription-overview-card">
				<div class="tutor-text-tiny tutor-text-secondary">
					<?php esc_html_e( 'Amount', 'tutor' ); ?>
				</div>
				<div class="tutor-subscription-overview-card-title">
					<!-- @TODO: Need to render plan price/period here -->
					$10.00
					<span>/month</span>
				</div>
			</div>

			<!-- Payment type -->
			<div class="tutor-subscription-overview-card">
				<div class="tutor-text-tiny tutor-text-secondary">
					<?php esc_html_e( 'Payment', 'tutor' ); ?>
				</div>
				<div class="tutor-subscription-overview-card-title">
					<!-- @TODO: Need to map svg icon or image to payment method -->
					<?php tutor_utils()->render_svg_icon( Icon::RELOAD_2 ); ?>
					Recurring
				</div>
			</div>

			<!-- Active payment method -->
			<div class="tutor-subscription-overview-card">
				<div class="tutor-text-tiny tutor-text-secondary">
					<?php esc_html_e( 'Active Payment Method', 'tutor' ); ?>
				</div>
				<div class="tutor-subscription-overview-card-title">
					<!-- @TODO: Need to map svg icon or image to payment method -->
					<?php tutor_utils()->render_svg_icon( Icon::LESSON ); ?>
					<?php echo esc_html( Ecommerce::get_payment_method_label( 'paypal' ) ); ?>
				</div>
			</div>
		</div>

		<!-- Info -->
		<div class="tutor-subscription-overview-info">
			<!-- Active Since -->
			<div class="tutor-text-tiny">
				<span class="tutor-text-subdued">
					<?php esc_html_e( 'Active Since', 'tutor' ); ?>
				</span>
				<span class="tutor-text-secondary">
					<?php echo esc_html( date_i18n( 'D, ' . get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), strtotime( $created_at_gmt ?? '' ) ) ); ?>
				</span>
			</div>

			<!-- Next Payment -->
			<div class="tutor-text-tiny">
				<span class="tutor-text-subdued">
					<?php esc_html_e( 'Next Payment', 'tutor' ); ?>
				</span>
				<span class="tutor-text-success">
					<?php echo esc_html( date_i18n( 'D, ' . get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), strtotime( $next_payment_date_gmt ?? '' ) ) ); ?>
				</span>
			</div>
		</div>
	</div>

	<!-- Payment History -->
	<div class="tutor-subscription-payments">
		<div class="tutor-subscription-payments-header">
			<div class="tutor-subscription-payments-title">
				<?php tutor_utils()->render_svg_icon( Icon::BILLING, 20, 20 ); ?>
				<?php esc_html_e( 'Payment History', 'tutor' ); ?>
			</div>
			<div class="tutor-badge tutor-badge-rounded">
				<?php
				printf(
					// translators: %s is either 'On' or 'Off'.
					esc_html__( 'Auto-Renewal %s', 'tutor' ),
					'On'
				);
				?>
			</div>
		</div>
		
		<!-- Payment History Cards -->
		<div class="tutor-subscription-payments-list">
			<?php foreach ( $payments as $payment ) : ?>
				<div class="tutor-subscription-payments-item">
					<div class="tutor-billing-card-details">
						<div class="tutor-billing-card-id tutor-sm-hidden">
							#<?php echo esc_html( $payment['id'] ); ?>
						</div>

						<div class="tutor-flex tutor-gap-3">
							<span>
								<?php echo esc_html( date_i18n( 'D, ' . get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), strtotime( $payment['created_at_gmt'] ?? '' ) ) ); ?>
							</span>

							<span class="tutor-section-separator-vertical tutor-sm-hidden"></span>

							<div>
								<div class="tutor-billing-card-payment-method">
								<!-- @TODO: Need to map svg icon or image to payment method -->
									<?php tutor_utils()->render_svg_icon( Icon::LESSON, 12, 12 ); ?>
									<div class="tutor-sm-hidden">
										<?php echo esc_html( Ecommerce::get_payment_method_label( $payment['payment_method'] ?? '' ) ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="tutor-flex tutor-gap-3 tutor-justify-end tutor-items-center tutor-flex-wrap">
						<!-- Amount -->
						<?php echo wp_kses_post( $render_status_badge( $payment['status'], $payment['regular_price'] ) ); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
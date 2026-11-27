<?php
/**
 * WooCommerce: Printable / Save-as-PDF Order Receipt template.
 *
 * @since 4.3.0
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Dashboard;
use TUTOR\Icon;
use Tutor\Components\SvgIcon;

$wc_order = get_query_var( 'tutor_wc_receipt_order' );
$receipt  = get_query_var( 'tutor_wc_receipt_data' );

if ( ! ( $wc_order instanceof \WC_Order ) || ! is_array( $receipt ) ) {
	return;
}

$company     = $receipt['company'] ?? array();
$customer    = $receipt['customer'] ?? array();
$order_info  = $receipt['order'] ?? array();
$billing_url = Dashboard::get_account_page_url( Dashboard::BILLING_PAGE_SLUG );
$site_name   = (string) ( $company['name'] ?? get_bloginfo( 'name' ) );
$order_num   = (string) ( $order_info['number'] ?? $wc_order->get_order_number() );
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'tutor-wc-receipt-body' ); ?>>
	<?php wp_body_open(); ?>
<div class="tutor-wc-receipt-page-wrapper" data-tutor-theme="light">
<div class="tutor-wc-receipt-container">
	<div class="tutor-wc-receipt-card tutor-p-4 tutor-wc-receipt-no-print">
		<div class="tutor-flex tutor-items-center tutor-justify-between">
			<a href="<?php echo esc_url( $billing_url ); ?>" class="tutor-btn tutor-btn-secondary tutor-btn-x-small tutor-btn-icon">
				<?php SvgIcon::make()->name( Icon::LEFT )->flip_rtl()->render(); ?>
			</a>

			<button
				type="button"
				id="tutor-wc-receipt-print"
				data-order-id="<?php echo esc_attr( $order_num ); ?>"
				class="tutor-btn tutor-gap-1 tutor-btn-primary tutor-btn-x-small"
			>
				<?php SvgIcon::make()->name( Icon::PRINTER )->render(); ?>
				<?php esc_html_e( 'Print / Save as PDF', 'tutor' ); ?>
			</button>
		</div>
	</div>

	<div class="tutor-wc-receipt-card tutor-mt-8 tutor-p-10">
		<div class="tutor-wc-receipt-wrapper">
			<div id="tutor-wc-receipt-content" class="tutor-wc-receipt">
				<div class="receipt-header tutor-border-b tutor-pb-8">
					<div class="receipt-header-left">
						<div class="tutor-text-tiny tutor-text-subdued tutor-mb-4"><?php esc_html_e( 'BILL FROM', 'tutor' ); ?></div>
						<div class="tutor-font-bold tutor-text-tiny tutor-text-primary"><?php echo esc_html( $site_name ); ?></div>
						<div class="tutor-text-tiny-2 tutor-text-subdued">
							<?php if ( ! empty( $company['tax_id'] ) ) : ?>
								<div>
									<strong><?php esc_html_e( 'Tax ID:', 'tutor' ); ?></strong>
									<?php echo esc_html( $company['tax_id'] ); ?>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $company['formatted_address'] ) ) : ?>
								<?php
								echo wp_kses(
									$company['formatted_address'],
									array(
										'br' => array(),
									)
								);
								?>
								<br>
							<?php endif; ?>

							<?php if ( ! empty( $company['email'] ) ) : ?>
								<?php echo esc_html( $company['email'] ); ?><br>
							<?php endif; ?>

							<?php if ( ! empty( $company['phone'] ) ) : ?>
								<?php echo esc_html( $company['phone'] ); ?><br>
							<?php endif; ?>

							<?php if ( ! empty( $company['website'] ) ) : ?>
								<?php echo esc_html( $company['website'] ); ?>
							<?php endif; ?>
						</div>
					</div>
					<?php if ( ! empty( $company['logo_url'] ) ) : ?>
						<div class="site-logo">
							<img src="<?php echo esc_url( $company['logo_url'] ); ?>" alt="<?php echo esc_attr( $site_name ); ?>">
						</div>
					<?php endif; ?>
				</div>

				<div class="receipt-info tutor-flex tutor-justify-between tutor-flex-wrap tutor-gap-8 tutor-py-8">
					<div>
						<div class="tutor-text-tiny tutor-text-subdued tutor-mb-4"><?php esc_html_e( 'BILL TO', 'tutor' ); ?></div>
						<?php if ( ! empty( $customer['name'] ) ) : ?>
							<div class="tutor-font-bold tutor-text-tiny tutor-text-primary"><?php echo esc_html( $customer['name'] ); ?></div>
						<?php endif; ?>
						<div class="tutor-text-tiny-2 tutor-text-primary tutor-font-regular">
							<?php if ( ! empty( $customer['company'] ) ) : ?>
								<?php echo esc_html( $customer['company'] ); ?><br>
							<?php endif; ?>

							<?php if ( ! empty( $customer['tax_id'] ) ) : ?>
								<strong><?php esc_html_e( 'Tax ID:', 'tutor' ); ?></strong>
								<?php echo esc_html( $customer['tax_id'] ); ?><br>
							<?php endif; ?>

							<?php if ( ! empty( $customer['formatted_address'] ) ) : ?>
								<?php
								echo wp_kses(
									$customer['formatted_address'],
									array(
										'br' => array(),
									)
								);
								?>
								<br>
							<?php endif; ?>

							<?php if ( ! empty( $customer['email'] ) ) : ?>
								<?php echo esc_html( $customer['email'] ); ?><br>
							<?php endif; ?>

							<?php if ( ! empty( $customer['phone'] ) ) : ?>
								<?php echo esc_html( $customer['phone'] ); ?>
							<?php endif; ?>
						</div>

						<?php if ( ! empty( $customer['note'] ) ) : ?>
							<div class="receipt-note tutor-text-tiny-2 tutor-text-secondary">
								<strong><?php esc_html_e( 'Customer Note:', 'tutor' ); ?></strong>
								<?php echo nl2br( esc_html( $customer['note'] ) ); ?>
							</div>
						<?php endif; ?>
					</div>

					<div class="receipt-box">
						<div class="receipt-row">
							<span class="label tutor-text-tiny-2 tutor-text-secondary tutor-font-regular"><?php esc_html_e( 'Receipt No.', 'tutor' ); ?></span>
							<span class="value tutor-text-tiny-2 tutor-text-primary tutor-font-regular">#<?php echo esc_html( $order_num ); ?></span>
						</div>

						<?php if ( ! empty( $order_info['date_created'] ) ) : ?>
							<div class="receipt-row">
								<span class="label tutor-text-tiny-2 tutor-text-secondary tutor-font-regular"><?php esc_html_e( 'Order Date', 'tutor' ); ?></span>
								<span class="value tutor-text-tiny-2 tutor-text-primary tutor-font-regular"><?php echo esc_html( $order_info['date_created'] ); ?></span>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $order_info['date_paid'] ) ) : ?>
							<div class="receipt-row">
								<span class="label tutor-text-tiny-2 tutor-text-secondary tutor-font-regular"><?php esc_html_e( 'Paid Date', 'tutor' ); ?></span>
								<span class="value tutor-text-tiny-2 tutor-text-primary tutor-font-regular"><?php echo esc_html( $order_info['date_paid'] ); ?></span>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $order_info['date_completed'] ) ) : ?>
							<div class="receipt-row">
								<span class="label tutor-text-tiny-2 tutor-text-secondary tutor-font-regular"><?php esc_html_e( 'Completed Date', 'tutor' ); ?></span>
								<span class="value tutor-text-tiny-2 tutor-text-primary tutor-font-regular"><?php echo esc_html( $order_info['date_completed'] ); ?></span>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $order_info['payment_method_title'] ) ) : ?>
							<div class="receipt-row">
								<span class="label tutor-text-tiny-2 tutor-text-secondary tutor-font-regular"><?php esc_html_e( 'Payment Method', 'tutor' ); ?></span>
								<span class="value tutor-text-tiny-2 tutor-text-primary tutor-font-regular"><?php echo esc_html( $order_info['payment_method_title'] ); ?></span>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $order_info['transaction_id'] ) ) : ?>
							<div class="receipt-row">
								<span class="label tutor-text-tiny-2 tutor-text-secondary tutor-font-regular"><?php esc_html_e( 'Transaction ID', 'tutor' ); ?></span>
								<span class="value tutor-text-tiny-2 tutor-text-primary tutor-font-regular"><?php echo esc_html( $order_info['transaction_id'] ); ?></span>
							</div>
						<?php endif; ?>

						<div class="receipt-row">
							<span class="label tutor-text-tiny-2 tutor-text-secondary"><?php esc_html_e( 'Status', 'tutor' ); ?></span>
							<span class="status paid tutor-text-tiny">
								<?php echo esc_html( (string) ( $order_info['status_label'] ?? '' ) ); ?>
							</span>
						</div>
					</div>
				</div>

				<table class="receipt-table">
					<thead>
						<tr class="tutor-border-b">
							<th class="tutor-text-tiny tutor-text-secondary tutor-font-regular"><?php esc_html_e( 'Item', 'tutor' ); ?></th>
							<th class="qty tutor-text-tiny tutor-text-secondary tutor-font-regular"><?php esc_html_e( 'Qty', 'tutor' ); ?></th>
							<th class="price tutor-text-tiny tutor-text-secondary tutor-font-regular"><?php esc_html_e( 'Price', 'tutor' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( ( $order_info['items'] ?? array() ) as $item ) : ?>
							<tr class="tutor-border-b">
								<td>
									<div class="tutor-flex tutor-gap-4 tutor-pr-4">
										<?php if ( ! empty( $item['image'] ) ) : ?>
											<div class="item-image">
												<img src="<?php echo esc_url( (string) $item['image'] ); ?>" alt="<?php echo esc_attr( (string) ( $item['name'] ?? '' ) ); ?>">
											</div>
										<?php endif; ?>
										<div class="tutor-font-bold tutor-text-tiny tutor-text-primary">
											<?php echo esc_html( (string) ( $item['name'] ?? '' ) ); ?>
											<?php if ( ! empty( $item['is_course'] ) || ! empty( $item['sku'] ) ) : ?>
												<div class="item-meta tutor-font-regular tutor-text-subdued">
													<?php
													$meta_parts = array();
													if ( ! empty( $item['is_course'] ) ) {
														$meta_parts[] = __( 'Course', 'tutor' );
													}
													if ( ! empty( $item['sku'] ) ) {
														$meta_parts[] = sprintf(
															/* translators: %s: product SKU. */
															__( 'SKU: %s', 'tutor' ),
															(string) $item['sku']
														);
													}
													echo esc_html( implode( ' · ', $meta_parts ) );
													?>
												</div>
											<?php endif; ?>
										</div>
									</div>
								</td>
								<td class="qty tutor-text-tiny tutor-text-primary">
									<?php echo esc_html( (string) ( $item['quantity'] ?? 1 ) ); ?>
								</td>
								<td class="price tutor-text-tiny tutor-text-primary">
									<div class="tutor-text-primary tutor-mb-1 tutor-font-regular">
										<?php echo wp_kses( (string) ( $item['formatted_total'] ?? '' ), tutor_price_allowed_html() ); ?>
									</div>
									<?php if ( ! empty( $item['has_item_discount'] ) ) : ?>
										<del class="tutor-text-subdued">
											<?php echo wp_kses( (string) ( $item['formatted_subtotal'] ?? '' ), tutor_price_allowed_html() ); ?>
										</del>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<div class="receipt-summary">
					<?php
					$totals_rows  = is_array( $order_info['totals_rows'] ?? null ) ? $order_info['totals_rows'] : array();
					$coupon_codes = is_array( $order_info['coupon_codes'] ?? null ) ? $order_info['coupon_codes'] : array();
					unset( $totals_rows['payment_method'] );

					if ( ! empty( $totals_rows ) ) :
						foreach ( $totals_rows as $row_key => $total_row ) :
							$is_total_row = 'order_total' === $row_key;
							$row_label    = wp_strip_all_tags( (string) ( $total_row['label'] ?? '' ) );
							if ( 'discount' === $row_key && ! empty( $coupon_codes ) ) {
								$row_label .= ' (' . implode( ', ', $coupon_codes ) . ')';
							}
							?>
							<div class="<?php echo $is_total_row ? 'tutor-border-t tutor-mt-5' : ''; ?>">
								<div class="tutor-text-tiny tutor-text-secondary tutor-font-regular">
									<?php echo esc_html( $row_label ); ?>
								</div>
								<div class="tutor-text-tiny tutor-text-primary <?php echo $is_total_row ? 'tutor-font-bold' : ''; ?>">
									<?php echo wp_kses( (string) ( $total_row['value'] ?? '' ), tutor_price_allowed_html() ); ?>
								</div>
							</div>
							<?php
						endforeach;
					else :
						?>
						<div>
							<div class="tutor-text-tiny tutor-text-secondary tutor-font-regular"><?php esc_html_e( 'Subtotal', 'tutor' ); ?></div>
							<div class="tutor-text-tiny tutor-text-primary">
								<?php echo wp_kses( (string) ( $order_info['formatted_subtotal'] ?? '' ), tutor_price_allowed_html() ); ?>
							</div>
						</div>
						<?php if ( ! empty( $coupon_codes ) ) : ?>
							<div>
								<div class="tutor-text-tiny tutor-text-secondary tutor-font-regular"><?php esc_html_e( 'Coupons', 'tutor' ); ?></div>
								<div class="tutor-text-tiny tutor-text-primary"><?php echo esc_html( implode( ', ', $coupon_codes ) ); ?></div>
							</div>
						<?php endif; ?>
						<div class="tutor-border-t tutor-mt-5">
							<div class="tutor-text-tiny tutor-text-secondary tutor-font-regular"><?php esc_html_e( 'Total', 'tutor' ); ?></div>
							<strong class="tutor-text-tiny tutor-text-primary tutor-font-bold">
								<?php echo wp_kses( (string) ( $order_info['formatted_total'] ?? '' ), tutor_price_allowed_html() ); ?>
							</strong>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $order_info['total_refunded'] ) ) : ?>
						<div>
							<div class="tutor-text-tiny tutor-text-secondary tutor-font-regular"><?php esc_html_e( 'Net Paid', 'tutor' ); ?></div>
							<strong class="tutor-text-tiny tutor-text-primary tutor-font-bold">
								<?php echo wp_kses( (string) ( $order_info['formatted_net_total'] ?? '' ), tutor_price_allowed_html() ); ?>
							</strong>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $company['footer_note'] ) || has_action( 'tutor_wc_receipt_footer' ) ) : ?>
					<div class="receipt-footer tutor-text-tiny-2 tutor-text-subdued">
						<?php if ( ! empty( $company['footer_note'] ) ) : ?>
							<p><?php echo wp_kses_post( nl2br( (string) $company['footer_note'] ) ); ?></p>
						<?php endif; ?>

						<?php do_action( 'tutor_wc_receipt_footer', $receipt, $wc_order ); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
</div>
<?php wp_footer(); ?>
</body>
</html>

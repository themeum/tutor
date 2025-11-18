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

?>

<div class="tutor-billing-card">
	<div class="tutor-billing-card-left">
		<div class="tutor-billing-card-title">
			<ul>
				<?php foreach ( $items as $item ) : ?>
					<li>
						<span><?php echo esc_html( $item['title'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="tutor-billing-card-details">
			<div class="tutor-billing-card-id">
				#<?php echo esc_html( $id ); ?>
			</div>

			<span>
				<?php echo esc_html( date_i18n( 'D, ' . get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), strtotime( $created_at_gmt ?? '' ) ) ); ?>
			</span>

			<span class="tutor-section-separator-vertical"></span>

			<div class="tutor-inline-flex tutor-items-center tutor-gap-4">
				<?php esc_html_e( 'Paid with', 'tutor' ); ?>
				<span class="tutor-billing-card-payment-method">
					<!-- @TODO: Need to map svg icon to payment method -->
					<?php tutor_utils()->render_svg_icon( Icon::LESSON ); ?>
					<?php echo esc_html( Ecommerce::get_payment_method_label( $payment_method ?? '' ) ); ?>
				</span>
			</div>
		</div>
	</div>

	<div class="tutor-billing-card-right">
		<div class="tutor-billing-card-amount">
			<?php echo esc_html( tutor_get_formatted_price( $total_price ) ); ?>
		</div>

		<a class="tutor-billing-card-action-btn" href="#">
			<?php esc_html_e( 'Receipt', 'tutor' ); ?>
			<?php tutor_utils()->render_svg_icon( Icon::DOWNLOAD_2 ); ?>
		</a>
	</div>
</div>
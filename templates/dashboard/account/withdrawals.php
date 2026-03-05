<?php
/**
 * Withdrawals Template for Account
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Badge;
use TUTOR\Input;
use Tutor\Components\Button;
use Tutor\Components\EmptyState;
use Tutor\Components\Pagination;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\Modal;
use Tutor\Components\Tooltip;
use TUTOR\Dashboard;
use Tutor\Helpers\ComponentHelper;
use Tutor\Helpers\QueryHelper;
use TUTOR\Icon;
use Tutor\Models\WithdrawModel;

$item_per_page = tutor_utils()->get_option( 'pagination_per_page', 20 );
$current_page  = max( 1, Input::get( 'current_page', 0, Input::TYPE_INT ) );
$offset        = ( $current_page - 1 ) * $item_per_page;

$selected_filter = Input::get( 'data', '' );
$order_filter    = Input::get( 'order', 'DESC' );
$start_date      = Input::get( 'start_date' );
$end_date        = Input::get( 'end_date' );
$user_id         = get_current_user_id();

$filters = array(
	'status' => array_keys( WithdrawModel::get_withdrawal_status_list() ),
	'order'  => QueryHelper::get_valid_sort_order( $order_filter ),
);

if ( ! empty( $selected_filter ) ) {
	$filters['status'] = $selected_filter;
}

if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
	$filters['start_date'] = $start_date;
	$filters['end_date']   = $end_date;
}

$withdral_history              = WithdrawModel::get_withdrawals_history( $user_id, $filters, $offset, $item_per_page );
$min_withdraw                  = (float) tutor_utils()->get_option( 'min_withdraw_amount' );
$formatted_min_withdraw_amount = tutor_utils()->tutor_price( $min_withdraw );

$saved_account        = WithdrawModel::get_user_withdraw_method();
$withdraw_method_name = tutor_utils()->avalue_dot( 'withdraw_method_name', $saved_account );


$history_count  = $withdral_history->count;
$method_icons   = WithdrawModel::get_method_icons();
$status_message = array(
	WithdrawModel::STATUS_REJECTED => __( 'Please contact the site administrator for more information.', 'tutor' ),
	WithdrawModel::STATUS_PENDING  => __( 'Withdrawal request is pending for approval, please hold tight.', 'tutor' ),
);

$currency_symbol = '';
if ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
	$currency_symbol = get_woocommerce_currency_symbol();
} elseif ( function_exists( 'edd_currency_symbol' ) ) {
	$currency_symbol = edd_currency_symbol();
}

$summary_data                     = WithdrawModel::get_withdraw_summary( $user_id );
$available_for_withdraw           = $summary_data->available_for_withdraw - $summary_data->total_pending;
$is_balance_sufficient            = $available_for_withdraw >= $min_withdraw;
$available_for_withdraw_formatted = tutor_utils()->tutor_price( $available_for_withdraw );
$current_balance_formated         = tutor_utils()->tutor_price( $summary_data->current_balance );
?>

<?php require_once tutor_get_template( 'account-header' ); ?>

<div class="tutor-user-withdrawals tutor-py-9 tutor-sm-py-6" x-data="tutorWithdrawals()">
	<div class="tutor-profile-container">
		<div class="tutor-card tutor-card-rounded-2xl">
			<div class="tutor-flex tutor-items-center tutor-justify-between">
				<div class="tutor-flex tutor-flex-column tutor-gap-2">
					<h3 class="tutor-h3"><?php echo wp_kses_post( $available_for_withdraw_formatted ); ?></h3>
					<div class="tutor-small tutor-text-secondary"><?php esc_html_e( 'Available for Withdrawal', 'tutor' ); ?></div>
				</div>
				<div>
					<?php
					if ( $is_balance_sufficient && $withdraw_method_name ) {
						$form_id             = 'withdrawal-request-form';
						$modal_id            = 'withdrawal-request-modal';
						$modal_template_path = tutor_get_template( 'dashboard.account.withdrawal.withdrawal-request-modal' );

						Button::make()
							->label( __( 'Request Withdrawal', 'tutor' ) )
							->attr( '@click', "TutorCore.form.reset('{$form_id}'); TutorCore.modal.showModal('{$modal_id}')" )
							->variant( Variant::PRIMARY )
							->size( Size::X_SMALL )
							->render();

						Modal::make()
							->id( $modal_id )
							->title( __( 'Withdrawal Request', 'tutor' ) )
							->template(
								$modal_template_path,
								array(
									'form_id'              => $form_id,
									'modal_id'             => $modal_id,
									'available_balance'    => $available_for_withdraw,
									'min_withdrawal'       => $min_withdraw,
									'withdraw_method_name' => $withdraw_method_name,
								)
							)
							->render();
					}
					?>
				</div>
			</div>

			<div class="tutor-withdrawal-status">
				<div class="tutor-withdrawal-status-item">
					<div><?php esc_html_e( 'Net Income', 'tutor' ); ?></div>
					<div><?php echo esc_html( tutor_utils()->tutor_price( $summary_data->total_income ) ); ?></div>
				</div>
				<div class="tutor-withdrawal-status-item">
					<div><?php esc_html_e( 'Pending Withdrawals', 'tutor' ); ?></div>
					<div><?php echo esc_html( tutor_utils()->tutor_price( $summary_data->total_pending ) ); ?></div>
				</div>
				<div class="tutor-withdrawal-status-item">
					<div><?php esc_html_e( 'Withdrawal Total', 'tutor' ); ?></div>
					<div><?php echo esc_html( tutor_utils()->tutor_price( $summary_data->total_withdraw ) ); ?></div>
				</div>
			</div>
		</div>

		<div class="tutor-mt-4 tutor-text-tiny">
			<?php
			$withdrawal_pref_link = Dashboard::get_account_page_url( 'settings?tab=withdraw' );
			echo wp_kses_post(
				sprintf(
					/* translators: %s: Withdraw Preference */
					__( 'The preferred payment method is selected as PayPal. You can change your %s', 'tutor' ),
					'<a href="' . esc_url( $withdrawal_pref_link ) . '">' . __( 'Withdraw Preference', 'tutor' ) . '</a>'
				)
			);
			?>
		</div>

		<div class="tutor-mt-10 tutor-mb-6 tutor-text-medium">
			<?php esc_html_e( 'Withdrawal History', 'tutor' ); ?>
		</div>

		<div class="tutor-card tutor-p-none">
			<div class="tutor-flex tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-b">
				<?php require_once tutor_get_template( 'dashboard.account.withdrawal.withdrawal-history-filters' ); ?>  
			</div>
			<div class="tutor-flex tutor-flex-column">
				<?php if ( tutor_utils()->count( $withdral_history->results ) > 0 ) : ?>
					<?php
					foreach ( $withdral_history->results as $withdrawal ) :
						$method_data  = maybe_unserialize( $withdrawal->method_data );
						$method_key   = $method_data['withdraw_method_key'] ?? '';
						$method_icon  = $method_icons[ $method_key ] ?? '';
						$method_title = '';
						?>
					<div class="tutor-billing-card">
						<div class="tutor-billing-card-left">
							<div class="tutor-flex tutor-gap-6">
								<div>
								<?php
								Badge::make()
									->icon( $method_icon )
									->label( $method_data['withdraw_method_name'] ?? '' )
									->render();
								?>
								</div>
								<div class="tutor-text-tiny">
								<?php
								switch ( $method_key ) {
									case WithdrawModel::METHOD_BANK_TRANSFER_WITHDRAW:
										$method_title = tutor_utils()->asterisks_center_text( $method_data['account_number']['value'] ?? '', 4 );
										break;
									case WithdrawModel::METHOD_PAYPAL_WITHDRAW:
										$method_title = tutor_utils()->asterisks_email( $method_data['paypal_email']['value'] ?? '' );
										break;
								}
								echo esc_html( $method_title );
								?>
								</div>
							</div>
							<div class="tutor-text-tiny">
								<?php echo esc_html( tutor_i18n_get_formated_date( $withdrawal->created_at ) ); ?>
							</div>
						</div>

						<div class="tutor-billing-card-right">
							<div class="tutor-billing-card-amount">
								<?php echo esc_html( tutor_utils()->tutor_price( $withdrawal->amount ) ); ?>

								<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-mt-4">
									<?php
									ComponentHelper::render_status_badge( $withdrawal->status );
									if ( in_array( $withdrawal->status, array( WithdrawModel::STATUS_PENDING, WithdrawModel::STATUS_REJECTED ), true ) ) {
										$tooltip_content = $status_message[ $withdrawal->status ];
										if ( WithdrawModel::STATUS_REJECTED === $withdrawal->status && is_array( $method_data ) && isset( $method_data['rejects'] ) ) {
											$tooltip_content = $method_data['rejects']['reject_comment'] ? $method_data['rejects']['reject_comment'] : $method_data['rejects']['reject_type'];
										}
										Tooltip::make()
											->content( $tooltip_content )
											->placement( 'top' )
											->trigger_element( tutor_utils()->get_svg_icon( Icon::INFO_OCTAGON ) )
											->render();
									}
									?>
								</div>
							</div>
						</div>
					</div>
					<?php endforeach; ?>
				<?php else : ?>
					<?php
					EmptyState::make()
					->title( __( 'No withdrawal records found', 'tutor' ) )
					->render();
					?>
				<?php endif; ?>
			</div>
		</div>
		<?php
		Pagination::make()
			->current( $current_page )
			->total( $history_count )
			->limit( $item_per_page )
			->attr( 'class', 'tutor-mt-6' )
			->render();
		?>
	</div>
</div>

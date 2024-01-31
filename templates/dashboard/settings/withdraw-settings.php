<?php
/**
 * Withdraw settings
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

use Tutor\Models\WithdrawModel;

$col_classes = array(
	1 => 'tutor-col-12',
	2 => 'tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6',
	3 => 'tutor-col-12 tutor-col-lg-4',
);
?>

<div class="tutor-fs-5 tutor-fw-medium tutor-mb-24"><?php esc_html_e( 'Settings', 'tutor' ); ?></div>

<div class="tutor-dashboard-setting-withdraw tutor-dashboard-content-inner">
	<div class="tutor-mb-32">
		<?php
			tutor_load_template( 'dashboard.settings.nav-bar', array( 'active_setting_nav' => 'withdrawal' ) );
		?>
		<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mt-32"><?php esc_html_e( 'Select a withdraw method', 'tutor' ); ?></div>
	</div>

	<form id="tutor-withdraw-account-set-form" action="" method="post">

		<?php
		$tutor_withdrawal_methods = apply_filters( 'tutor_withdrawal_methods_available', array() );

		if ( tutor_utils()->count( $tutor_withdrawal_methods ) ) {
			$saved_account       = WithdrawModel::get_user_withdraw_method();
			$old_method_key      = tutor_utils()->avalue_dot( 'withdraw_method_key', $saved_account );
			$min_withdraw_amount = tutor_utils()->get_option( 'min_withdraw_amount' );
			?>
			<div class="tutor-row tutor-mb-32">
				<?php
				$method_count = count( $tutor_withdrawal_methods );
				foreach ( $tutor_withdrawal_methods as $method_id => $method ) {
					?>
					<div class="<?php echo esc_attr( $col_classes[ $method_count ] ); ?>" data-withdraw-method="<?php echo esc_attr( $method_id ); ?>">
						<label class="tutor-radio-select tutor-align-center tutor-mb-12">
							<input class="tutor-form-check-input" type="radio" name="tutor_selected_withdraw_method" value="<?php echo esc_attr( $method_id ); ?>" <?php checked( $method_id, $old_method_key ); ?>/>
							<div class="tutor-radio-select-content">
								<span class="tutor-radio-select-title">
									<?php echo esc_html( tutor_utils()->avalue_dot( 'method_name', $method ) ); ?>
								</span>
								<?php esc_html_e( 'Min withdraw', 'tutor' ); ?> <?php echo wp_kses_post( tutor_utils()->tutor_price( $min_withdraw_amount ) ); ?>
							</div>
						</label>
					</div>
					<?php
				}
				?>
			</div>

			<input type="hidden" value="tutor_save_withdraw_account" name="action"/>
			<?php
				wp_nonce_field( tutor()->nonce_action, tutor()->nonce );
				do_action( 'tutor_withdraw_set_account_form_before' );

			foreach ( $tutor_withdrawal_methods as $method_id => $method ) {
				$form_fields = tutor_utils()->avalue_dot( 'form_fields', $method );

				$method_values                                = get_user_meta( get_current_user_id(), '_tutor_withdraw_method_data_' . $method_id, true );
				$method_values                                = maybe_unserialize( $method_values );
				! is_array( $method_values ) ? $method_values = array() : 0;
				?>

					<div data-withdraw-form="<?php echo esc_attr( $method_id ); ?>" class="tutor-row withdraw-method-form" style="<?php echo esc_attr( $old_method_key != $method_id ? 'display: none;' : '' ); ?>">
					<?php
					do_action( "tutor_withdraw_set_account_{$method_id}_before" );

					$field_count = tutor_utils()->count( $form_fields );
					if ( $field_count ) {
						foreach ( $form_fields as $field_name => $field ) {
							?>
								<div class="<?php echo esc_attr( $field_count ) > 1 ? 'tutor-col-12 tutor-col-sm-6' : 'tutor-col-12'; ?> tutor-mb-32">
								<?php
								if ( ! empty( $field['label'] ) ) {
									$markup = "<label class='tutor-form-label tutor-color-muted' for='field_{$method_id}_$field_name'>
                                                " . htmlspecialchars( $field['label'] ) . '
                                            </label>';

									echo wp_kses(
										$markup,
										array(
											'label' => array(
												'class' => true,
												'for'   => true,
											),
										)
									);
								}

								$passing_data = apply_filters(
									'tutor_withdraw_account_field_type_data',
									array(
										'method_id'  => $method_id,
										'method'     => $method,
										'field_name' => $field_name,
										'field'      => $field,
										'old_value'  => null,
									)
								);

								$old_value = tutor_utils()->avalue_dot( $field_name . '.value', $saved_account );
								if ( $old_value ) {
									$passing_data['old_value'] = $old_value;
								} elseif ( isset( $method_values[ $field_name ], $method_values[ $field_name ]['value'] ) ) {
									$passing_data['old_value'] = $method_values[ $field_name ]['value'];
									$old_value                 = $passing_data['old_value'];
								}

								if ( in_array( $field['type'], array( 'text', 'number', 'email' ) ) ) {
									?>
											<input class="tutor-form-control tutor-mt-4" type="<?php echo esc_attr( $field['type'] ); ?>" name="withdraw_method_field[<?php echo esc_attr( $method_id ); ?>][<?php echo esc_attr( $field_name ); ?>]" value="<?php echo esc_attr( $old_value ); ?>" >
										<?php
								} elseif ( 'textarea' == $field['type'] ) {
									?>
											<textarea class="tutor-form-control tutor-mt-4" name="withdraw_method_field[<?php echo esc_attr( $method_id ); ?>][<?php echo esc_attr( $field_name ); ?>]">
											<?php echo esc_textarea( $old_value ); ?>
											</textarea>
										<?php
								}

								if ( ! empty( $field['desc'] ) ) {
									echo wp_kses_post( "<div class='tutor-fs-7 tutor-color-secondary withdraw-field-desc tutor-mt-4'>{$field['desc']}</div>" );
								}
								?>
								</div>
								<?php
						}
					}
					?>

					<?php do_action( "tutor_withdraw_set_account_{$method_id}_after" ); ?>

						<div class="withdraw-account-save-btn-wrap tutor-mt-32">
							<button type="submit" class="tutor_set_withdraw_account_btn tutor-btn tutor-btn-primary" name="withdraw_btn_submit">
							<?php esc_html_e( 'Save Withdrawal Account', 'tutor' ); ?>
							</button>
						</div>
					</div>
					<?php
			}

				do_action( 'tutor_withdraw_set_account_form_after' );
		} else {
			?>
			<div class="tutor-row tutor-mb-32">
				<p>
					<?php echo __( 'There\'s no Withdrawal method selected yet! To select a Withdraw method, please contact the Site Admin.', 'tutor' ); ?>
				</p>
			</div>
			<?php
		}
		?>
	</form>
</div>

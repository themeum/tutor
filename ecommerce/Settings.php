<?php
/**
 * Settings class for configuring ecommerce settings
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce;

use TUTOR\Input;

/**
 * Configure ecommerce settings
 */
class Settings {

	/**
	 * Register hooks
	 */
	public function __construct() {
		add_filter( 'tutor/options/extend/attr', __CLASS__ . '::add_ecommerce_settings' );
		add_filter( 'tutor_after_ecommerce_settings', __CLASS__ . '::get_payment_gateway_settings' );

		add_action( 'add_manual_payment_btn', __CLASS__ . '::add_manual_payment_btn' );
		add_action( 'wp_ajax_tutor_add_manual_payment_method', __CLASS__ . '::ajax_add_manual_payment_method' );
		add_action( 'wp_ajax_tutor_delete_manual_payment_method', __CLASS__ . '::ajax_delete_manual_payment_method' );

	}

	/**
	 * Add ecommerce settings
	 *
	 * @param array $fields Tutor setting fields.
	 *
	 * @return array
	 */
	public static function add_ecommerce_settings( $fields ) {
		$pages = tutor_utils()->get_pages();

		$arr = array(
			'ecommerce_basic'    => array(
				'label'    => __( 'Basic', 'tutor' ),
				'slug'     => 'ecommerce_basic',
				'desc'     => __( 'Advanced Settings', 'tutor' ),
				'template' => 'basic',
				'icon'     => 'tutor-icon-filter',
				'blocks'   => array(
					array(
						'label'      => false,
						'block_type' => 'uniform',
						'slug'       => 'cart_page',
						'fields'     => array(
							array(
								'key'     => CartController::PAGE_ID_OPTION_NAME,
								'type'    => 'select',
								'label'   => __( 'Cart Page', 'tutor' ),
								'default' => '0',
								'options' => $pages,
								'desc'    => __( 'Select the page to be used as the cart page.', 'tutor' ),
							),
						),
					),
					array(
						'label'      => false,
						'block_type' => 'uniform',
						'slug'       => 'checkout_page',
						'fields'     => array(
							array(
								'key'     => CheckoutController::PAGE_ID_OPTION_NAME,
								'type'    => 'select',
								'label'   => __( 'Checkout Page', 'tutor' ),
								'default' => '0',
								'options' => $pages,
								'desc'    => __( 'Select the page to be used as the checkout page.', 'tutor' ),
							),
						),
					),
					array(
						'label'      => __( 'Currency', 'tutor' ),
						'slug'       => 'currency',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'            => OptionKeys::CURRENCY_TYPE,
								'type'           => 'select',
								'label'          => __( 'Currency', 'tutor' ),
								'select_options' => true,
								'options'        => array(
									'USD' => 'USD Dollar',
									'ER'  => 'Euro',
								),
								'default'        => 'USD',
								'desc'           => __( 'Choose the currency for transactions.', 'tutor' ),
							),
							array(
								'key'            => OptionKeys::CURRENCY_POSITION,
								'type'           => 'select',
								'label'          => __( 'Currency Position', 'tutor' ),
								'select_options' => true,
								'options'        => array(
									'left'  => 'Left',
									'right' => 'Right',
								),
								'default'        => 'left',
								'desc'           => __( 'Set the position of the currency symbol.', 'tutor' ),
							),
							array(
								'key'     => OptionKeys::THOUSAND_SEPARATOR,
								'type'    => 'text',
								'label'   => __( 'Thousand Separator', 'tutor' ),
								'default' => ',',
								'desc'    => __( 'Specify the thousand separator.', 'tutor' ),
							),
							array(
								'key'     => OptionKeys::DECIMAL_SEPARATOR,
								'type'    => 'text',
								'label'   => __( 'Decimal Separator', 'tutor' ),
								'default' => '.',
								'desc'    => __( 'Specify the decimal separator.', 'tutor' ),
							),
							array(
								'key'     => OptionKeys::NUMBER_OF_DECIMALS,
								'type'    => 'number',
								'label'   => __( 'Number of Decimals', 'tutor' ),
								'default' => '2',
								'desc'    => __( 'Set the number of decimal places.', 'tutor' ),
							),
						),
					),

				),
			),
			'ecommerce_payment'  => array(
				'label'    => __( 'Payment', 'tutor' ),
				'slug'     => 'automate_payment_gateway',
				'desc'     => __( 'Advanced Settings', 'tutor' ),
				'template' => 'basic',
				'icon'     => 'tutor-icon-filter',
				'blocks'   => array(),
			),
			'ecommerce_tax'      => array(
				'label'    => __( 'Tax', 'tutor' ),
				'slug'     => 'ecommerce_tax',
				'desc'     => __( 'Advanced Settings', 'tutor' ),
				'template' => 'basic',
				'icon'     => 'tutor-icon-filter',
				'blocks'   => array(
					array(
						'label'      => __( 'Tax Configuration', 'tutor' ),
						'slug'       => 'options',
						'block_type' => 'uniform',
						'fields'     => array(),
					),
				),
			),
			'ecommerce_checkout' => array(
				'label'    => __( 'Checkout', 'tutor' ),
				'slug'     => 'ecommerce_checkout',
				'template' => 'basic',
				'icon'     => 'tutor-icon-filter',
				'blocks'   => array(
					array(
						'label'      => __( 'Checkout Configuration', 'tutor' ),
						'desc'       => __( 'Customize your checkout process to suit your preferences.', 'tutor' ),
						'slug'       => 'checkout_configuration',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'     => OptionKeys::BILLING_ADDRESS,
								'type'    => 'select',
								'label'   => __( 'Billing Address', 'tutor' ),
								'default' => OptionKeys::get_billing_field_options()['optional'],
								'options' => OptionKeys::get_billing_field_options(),
							),
							array(
								'key'     => OptionKeys::BILLING_PHONE_NUMBER,
								'type'    => 'select',
								'label'   => __( 'Phone Number', 'tutor' ),
								'default' => OptionKeys::get_billing_field_options()['optional'],
								'options' => OptionKeys::get_billing_field_options(),
								'desc'    => __( 'Choose the page for instructor registration.', 'tutor' ),
							),
							array(
								'key'     => OptionKeys::BILLING_EMAIL,
								'type'    => 'select',
								'label'   => __( 'Email Address', 'tutor' ),
								'default' => OptionKeys::get_billing_field_options()['optional'],
								'options' => OptionKeys::get_billing_field_options(),
								'desc'    => __( 'Choose the page for student registration.', 'tutor' ),
							),
							array(
								'key'     => OptionKeys::IS_TAX_APPLICABLE,
								'type'    => 'toggle_switch',
								'label'   => __( 'Apply Tax Rate', 'tutor' ),
								'default' => 'off',
								'desc'    => __( 'Enable this to accept payments via PayPal.', 'tutor' ),
							),
							array(
								'key'     => OptionKeys::IS_COUPON_APPLICABLE,
								'type'    => 'toggle_switch',
								'label'   => __( 'Apply Coupon Code', 'tutor' ),
								'default' => 'off',
								'desc'    => __( 'Enable this to accept payments via Stripe.', 'tutor' ),
							),
						),
					),
					array(
						'label'      => __( 'Legal Information', 'tutor' ),
						'slug'       => 'legal_information',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'     => OptionKeys::REFUND_POLICY,
								'type'    => 'select',
								'label'   => __( 'Refund Policy', 'tutor' ),
								'default' => 0,
								'options' => $pages,
								'desc'    => __( 'Choose the page for instructor registration.', 'tutor' ),
							),
							array(
								'key'     => OptionKeys::PRIVACY_POLICY,
								'type'    => 'select',
								'label'   => __( 'Privacy Policy', 'tutor' ),
								'default' => 0,
								'options' => $pages,
								'desc'    => __( 'Choose the page for student registration.', 'tutor' ),
							),
						),
					),
				),
			),
		);

		return apply_filters( 'tutor_after_ecommerce_settings', $fields + $arr );
	}

	/**
	 * Show add manual payment btn
	 *
	 * @since 3.0.0
	 *
	 * @param string $slug Block slug.
	 *
	 * @return void
	 */
	public static function add_manual_payment_btn( $slug ) {
		?>
		<div class="tutor-add-payment-method-container">
			<button type="button" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm" target="_blank" data-tutor-modal-target="tutor-add-manual-payment-modal">
				<?php esc_html_e( '+ Add manual payment', 'tutor' ); ?>
			</button>
		</div>
		<?php
	}

	/**
	 * Save tutor manual payments methods.
	 *
	 * @since 3.0.0
	 *
	 * @return void send wp_json response
	 */
	public static function ajax_add_manual_payment_method() {
		tutor_utils()->checking_nonce();

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$request = Input::sanitize_array(
			$_POST,
			array(
				'additional_details'   => 'sanitize_textarea_field',
				'payment_instructions' => 'sanitize_textarea_field',
			)
		);

		$method_id = $request['payment_method_id'] ?? null;

		$success = false;
		if ( $method_id ) {
			$success = self::update_manual_method( $method_id, $request );
		} else {
			$method_id   = uniqid();
			$config_keys = array_keys( self::get_manual_payment_config_keys() );

			$data = array();
			foreach ( $config_keys as $key ) {
				if ( 'payment_method_id' === $key ) {
					$data['payment_method_id'] = $method_id;
				} else {
					$data[ "{$method_id}_$key" ] = $request[ $key ];
				}
			}

			$success = self::add_new_manual_method( $data );
		}

		if ( $success ) {
			wp_send_json_success( __( 'Manual payment added successfully!', 'tutor' ) );
		} else {
			wp_send_json_error( __( 'Failed to add manual payment', 'tutor' ) );
		}
	}

	/**
	 * Ajax handler for deleting a manual payment
	 *
	 * @since 3.0.0
	 *
	 * @return void send wp_json response
	 */
	public static function ajax_delete_manual_payment_method() {
		tutor_utils()->checking_nonce();

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$payment_method_id = Input::post( 'payment_method_id' );
		if ( ! $payment_method_id ) {
			wp_send_json_error( __( 'Payment method id required', 'tutor' ) );
		}

		$success = self::delete_manual_method( $payment_method_id );

		if ( $success ) {
			wp_send_json_success( __( 'Payment method deleted successfully', 'tutor' ) );
		} else {
			wp_send_json_error( __( 'Payment method delete failed', 'tutor' ) );
		}
	}

	/**
	 * Register new custom manual payment
	 *
	 * Store it inside tutor_options
	 *
	 * @since 3.0.0
	 *
	 * @param array $data Payment config data.
	 *
	 * @see OptionKeys::get_manual_payment_config_keys for data array.
	 *
	 * @return bool
	 */
	public static function add_new_manual_method( array $data ) {
		// Extract fillable fields.
		$new_payment_method = $data;

		$payment_methods = tutor_utils()->get_option( OptionKeys::MANUAL_PAYMENT_KEY, array() );
		array_push( $payment_methods, $new_payment_method );

		try {
			tutor_utils()->update_option( OptionKeys::MANUAL_PAYMENT_KEY, $payment_methods );
			return true;
		} catch ( \Throwable $th ) {
			error_log( $th->getMessage() . ' File: ' . $th->getFile(), ' Line: ' . $th->getLine() );
			return false;
		}
	}

	/**
	 * Register new custom manual payment
	 *
	 * Store it inside tutor_options
	 *
	 * @since 3.0.0
	 *
	 * @param array $data Payment config data.
	 *
	 * @see OptionKeys::get_manual_payment_config_keys for data array.
	 *
	 * @return bool
	 */
	public static function update_manual_method( array $data ) {
		// Extract fillable fields.
		$new_payment_method = $data;

		$payment_methods = tutor_utils()->get_option( OptionKeys::MANUAL_PAYMENT_KEY, array() );
		array_push( $payment_methods, $new_payment_method );

		try {
			tutor_utils()->update_option( OptionKeys::MANUAL_PAYMENT_KEY, $payment_methods );
			return true;
		} catch ( \Throwable $th ) {
			error_log( $th->getMessage() . ' File: ' . $th->getFile(), ' Line: ' . $th->getLine() );
			return false;
		}
	}

	/**
	 * Delete a manual method
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $method_id Payment method id.
	 *
	 * @return bool
	 */
	public static function delete_manual_method( $method_id ) {
		$payment_methods = tutor_utils()->get_option( OptionKeys::MANUAL_PAYMENT_KEY, array() );

		if ( is_array( $payment_methods ) && count( $payment_methods ) ) {
			$payment_methods = array_filter(
				$payment_methods,
				function( $method ) use ( $method_id ) {
					return $method['payment_method_id'] !== $method_id;
				}
			);
		}

		try {
			tutor_utils()->update_option( OptionKeys::MANUAL_PAYMENT_KEY, $payment_methods );
			return true;
		} catch ( \Throwable $th ) {
			error_log( $th->getMessage() . ' File: ' . $th->getFile(), ' Line: ' . $th->getLine() );
			return false;
		}
	}

	/**
	 * Get default automate payment gateways
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Tutor settings.
	 *
	 * @return array
	 */
	public static function get_payment_gateway_settings( $settings ): array {
		$paypal = array(
			'label'      => __( 'Supported payment methods ', 'tutor' ),
			'slug'       => 'paypal_payment_gateway',
			'block_type' => 'uniform',
			'fields'     => array(
				array(
					'key'           => OptionKeys::IS_ENABLE_PAYPAL_PAYMENT,
					'type'          => 'toggle_switch',
					'label'         => __( 'Paypal', 'tutor-pro' ),
					'label_title'   => '',
					'default'       => 'off',
					'desc'          => __( 'Enable Paypal payment', 'tutor-pro' ),
					'toggle_fields' => implode( ',', self::get_paypal_config_keys() ),
				),
				array(
					'key'         => 'paypal_environment',
					'type'        => 'select',
					'label'       => __( 'PayPal Environment', 'tutor-pro' ),
					'desc'        => '',
					'default'     => array_keys( self::get_payment_environments() )[0],
					'options'     => self::get_payment_environments(),
					'placeholder' => __( 'Enter your PayPal Environment here', 'tutor-pro' ),
				),
				array(
					'key'         => 'paypal_merchant_email',
					'type'        => 'text',
					'label'       => __( 'Merchant Email', 'tutor-pro' ),
					'desc'        => '',
					'placeholder' => __( 'Enter your Merchant Email here', 'tutor-pro' ),
				),
				array(
					'key'         => 'paypal_client_id',
					'type'        => 'text',
					'label'       => __( 'Client ID', 'tutor-pro' ),
					'desc'        => '',
					'placeholder' => __( 'Enter your Client ID here', 'tutor-pro' ),
				),
				array(
					'key'         => 'paypal_client_secret',
					'type'        => 'text',
					'label'       => __( 'Client Secret', 'tutor-pro' ),
					'desc'        => '',
					'placeholder' => __( 'Enter your Client Secret here', 'tutor-pro' ),
				),
				array(
					'key'         => 'paypal_webhook_id',
					'type'        => 'text',
					'label'       => __( 'Webhook ID', 'tutor-pro' ),
					'desc'        => '',
					'placeholder' => __( 'Enter your Webhook ID here', 'tutor-pro' ),
				),
			),
		);

		$stripe = array(
			'slug'       => 'stripe_payment_gateway',
			'block_type' => 'uniform',
			'fields'     => array(
				array(
					'key'           => OptionKeys::IS_ENABLE_STRIPE_PAYMENT,
					'type'          => 'toggle_switch',
					'label'         => __( 'Stripe', 'tutor-pro' ),
					'label_title'   => '',
					'default'       => 'off',
					'desc'          => __( 'Enable stripe payment', 'tutor-pro' ),
					'toggle_fields' => implode( ',', self::get_stripe_config_keys() ),
				),
				array(
					'key'         => 'stripe_environment',
					'type'        => 'select',
					'label'       => __( 'Stripe Environment', 'tutor-pro' ),
					'desc'        => '',
					'default'     => array_keys( self::get_payment_environments() )[0],
					'options'     => self::get_payment_environments(),
					'placeholder' => __( 'Enter your Stripe Environment here', 'tutor-pro' ),
				),
				array(
					'key'         => 'stripe_secret_key',
					'type'        => 'text',
					'label'       => __( 'Stripe Secret Key', 'tutor-pro' ),
					'desc'        => '',
					'placeholder' => __( 'Enter your Stripe Secret Key here', 'tutor-pro' ),
				),
				array(
					'key'         => 'stripe_webhook_signature_key',
					'type'        => 'text',
					'label'       => __( 'Stripe Webhook Signature Key', 'tutor-pro' ),
					'desc'        => '',
					'placeholder' => __( 'Enter your Stripe Webhook Signature Key here', 'tutor-pro' ),
				),
			),
		);

		array_push( $settings['ecommerce_payment']['blocks'], $paypal );
		array_push( $settings['ecommerce_payment']['blocks'], $stripe );

		$settings = apply_filters( 'tutor_ecommerce_payment_settings', $settings );

		// Manual Payments.
		$manual_gateways = array(
			'label'      => __( 'Manual payment methods ', 'tutor' ),
			'slug'       => 'manual_payment_gateway',
			'block_type' => 'uniform',
			'fields'     => array(),
		);

		array_push( $settings['ecommerce_payment']['blocks'], $manual_gateways );

		$manual_gateways = self::get_manual_payment_setting_fields();

		foreach ( $manual_gateways as $gateway ) {
			array_push( $settings['ecommerce_payment']['blocks'], $gateway );
		}

		$add_btn = array(
			'block_type' => 'action_placeholder',
			'action'     => 'add_manual_payment_btn',
		);

		array_push( $settings['ecommerce_payment']['blocks'], $add_btn );

		return $settings;
	}

	/**
	 * Get manual payment setting fields
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_manual_payment_setting_fields() {
		$blocks = array();

		$manual_payment_methods = tutor_utils()->get_option( OptionKeys::MANUAL_PAYMENT_KEY );
		if ( is_array( $manual_payment_methods ) && count( $manual_payment_methods ) ) {
			foreach ( $manual_payment_methods as $method ) {

				$method_id = $method['payment_method_id'] ?? '';
				if ( empty( $method_id ) ) {
					continue;
				}

				$is_enable            = $method[ "{$method_id}_is_enable" ] ?? 'off';
				$method_name          = $method[ "{$method_id}_payment_method_name" ] ?? '';
				$additional_details   = $method[ "{$method_id}_additional_details" ] ?? '';
				$payment_instructions = $method[ "{$method_id}_payment_instructions" ] ?? '';

				$block = array(
					'label'             => $method_name,
					'slug'              => "manual_payment_gateway_{$method_id}",
					'block_type'        => 'manual_payment',
					'fields'            => array(),
					'payment_method_id' => $method_id,
					'default'           => $is_enable,
					'desc'              => sprintf( __( 'Enable %s payment method', 'tutor' ), $method_name ),
					'data-attrs'        => array(
						'method-name'          => $method_name,
						'additional-details'   => $additional_details,
						'payment-instructions' => $payment_instructions,
					),
				);

				array_push( $blocks, $block );
			}
		}

		return $blocks;
	}

	/**
	 * Get paypal config keys
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_paypal_config_keys() {
		return array(
			'paypal_environment',
			'paypal_merchant_email',
			'paypal_client_id',
			'paypal_client_secret',
			'paypal_webhook_id',
		);
	}

	/**
	 * Get stripe config keys
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_stripe_config_keys() {
		return array(
			'stripe_environment',
			'stripe_secret_key',
			'stripe_webhook_signature_key',
		);
	}

	/**
	 * Get payment environments
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_payment_environments() {
		return array(
			'test' => __( 'Test', 'tutor' ),
			'live' => __( 'Live', 'tutor' ),
		);
	}

	/**
	 * Get manual payment config keys
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_manual_payment_config_keys() {
		return array(
			'payment_method_id'    => 'hidden',
			'payment_method_name'  => 'text',
			'additional_details'   => 'textarea',
			'payment_instructions' => 'textarea',
		);
	}

}

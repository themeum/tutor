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
use Tutor\Traits\JsonResponse;

/**
 * Configure ecommerce settings
 */
class Settings {

	use JsonResponse;

	/**
	 * Register hooks
	 */
	public function __construct() {
		add_filter( 'tutor/options/extend/attr', __CLASS__ . '::add_ecommerce_settings' );
		add_filter( 'tutor_after_ecommerce_settings', __CLASS__ . '::add_payment_gateway_settings' );

		add_action( 'add_manual_payment_btn', __CLASS__ . '::add_manual_payment_btn' );
		add_action( 'wp_ajax_tutor_add_manual_payment_method', __CLASS__ . '::ajax_add_manual_payment_method' );
		add_action( 'wp_ajax_tutor_delete_manual_payment_method', __CLASS__ . '::ajax_delete_manual_payment_method' );
		add_action( 'wp_ajax_tutor_get_tax_settings', array( $this, 'ajax_get_tax_settings' ) );

		add_filter( 'tutor_option_input', array( $this, 'format_ecommerce_tax_data' ) );
		add_filter( 'tutor_option_input', array( $this, 'format_payment_settings_data' ) );
		add_filter( 'wp_ajax_tutor_payment_settings', array( $this, 'ajax_get_tutor_payment_settings' ) );
	}


	/**
	 * Format ecommerce tax setting data.
	 *
	 * @param array $option option.
	 *
	 * @return array
	 */
	public function format_ecommerce_tax_data( $option ) {
		if ( ! empty( $option['ecommerce_tax'] ) ) {
			$option['ecommerce_tax'] = wp_unslash( $option['ecommerce_tax'] );
		}

		return $option;
	}

	/**
	 * Format payment settings data.
	 *
	 * @param array $option option.
	 *
	 * @return array
	 */
	public function format_payment_settings_data( $option ) {
		if ( ! empty( $option['payment_settings'] ) ) {
			$option['payment_settings'] = wp_unslash( $option['payment_settings'] );
		}

		return $option;
	}

	/**
	 * Get tax settings.
	 *
	 * @since 3.0.0
	 *
	 * @return object
	 */
	public static function get_tax_settings() {
		$tax_settings = tutor_utils()->get_option( 'ecommerce_tax' );

		if ( ! empty( $tax_settings ) && is_string( $tax_settings ) ) {
			$tax_settings = json_decode( $tax_settings );
		}

		return $tax_settings;
	}

	/**
	 * Get tax settings key data.
	 *
	 * @since 3.0.0
	 *
	 * @param string $key key.
	 * @param mixed  $default default value.
	 *
	 * @return mixed
	 */
	public static function get_tax_setting( $key, $default = false ) {
		$tax_settings = self::get_tax_settings();

		if ( ! empty( $tax_settings->$key ) ) {
			return $tax_settings->$key;
		}

		return $default;
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

		$pages_fields = array(
			array(
				'key'        => CartController::PAGE_ID_OPTION_NAME,
				'type'       => 'select',
				'label'      => __( 'Cart Page', 'tutor' ),
				'default'    => '0',
				'options'    => $pages,
				'desc'       => __( 'Select the page you wish to set as the cart page.', 'tutor' ),
				'searchable' => true,
			),
			array(
				'key'        => CheckoutController::PAGE_ID_OPTION_NAME,
				'type'       => 'select',
				'label'      => __( 'Checkout Page', 'tutor' ),
				'default'    => '0',
				'options'    => $pages,
				'desc'       => __( 'Select the page to be used as the checkout page.', 'tutor' ),
				'searchable' => true,
			),
		);

		$basic_settings_blocks = array(
			'ecommerce_block_currency' => array(
				'label'      => __( 'Currency', 'tutor' ),
				'slug'       => 'ecommerce_currency',
				'block_type' => 'uniform',
				'fields'     => array(
					array(
						'key'            => OptionKeys::CURRENCY_CODE,
						'type'           => 'select',
						'label'          => __( 'Currency Symbol', 'tutor' ),
						'select_options' => false,
						'options'        => self::get_currency_options(),
						'default'        => 'USD',
						'desc'           => __( 'Choose the currency for transactions.', 'tutor' ),
						'searchable'     => true,
					),
					array(
						'key'            => OptionKeys::CURRENCY_POSITION,
						'type'           => 'select',
						'label'          => __( 'Currency Position', 'tutor' ),
						'select_options' => false,
						'options'        => array(
							'left'  => 'Left',
							'right' => 'Right',
						),
						'default'        => 'left',
						'desc'           => __( 'Set the position of the currency symbol.', 'tutor' ),
					),
					array(
						'key'           => OptionKeys::THOUSAND_SEPARATOR,
						'type'          => 'text',
						'label'         => __( 'Thousand Separator', 'tutor' ),
						'field_classes' => 'tutor-w-90',
						'default'       => ',',
						'desc'          => __( 'Specify the thousand separator.', 'tutor' ),
					),
					array(
						'key'           => OptionKeys::DECIMAL_SEPARATOR,
						'type'          => 'text',
						'label'         => __( 'Decimal Separator', 'tutor' ),
						'field_classes' => 'tutor-w-90',
						'default'       => '.',
						'desc'          => __( 'Specify the decimal separator.', 'tutor' ),
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
		);

		foreach ( $pages_fields as $page_field ) {
			$fields['monetization']['blocks']['block_options']['fields'][] = $page_field;
		}

		$prepared_blocks = array();
		foreach ( $fields['monetization']['blocks'] as $key => $block ) {
			$prepared_blocks[ $key ] = $block;
			if ( 'block_options' === $key ) {
				foreach ( $basic_settings_blocks as $key => $block ) {
					$prepared_blocks[ $key ] = $block;
				}
			}
		}

		$fields['monetization']['blocks'] = $prepared_blocks;

		$arr = apply_filters( 'tutor_before_ecommerce_payment_settings', array() );

		$arr['ecommerce_payment'] = array(
			'label'    => __( 'Payment Methods', 'tutor' ),
			'slug'     => 'automate_payment_gateway',
			'desc'     => __( 'Advanced Settings', 'tutor' ),
			'template' => 'basic',
			'icon'     => 'tutor-icon-credit-card',
			'blocks'   => array(),
		);

		// @TODO.
		$arr['ecommerce_tax'] = array(
			'label'    => __( 'Tax', 'tutor' ),
			'slug'     => 'ecommerce_tax',
			'desc'     => __( 'Advanced Settings', 'tutor' ),
			'template' => 'basic',
			'icon'     => 'tutor-icon-receipt-percent',
			'blocks'   => array(
				array(
					'label'      => __( 'Tax Configuration', 'tutor' ),
					'slug'       => 'options',
					'block_type' => 'uniform',
					'fields'     => array(),
				),
			),
		);

		$arr['ecommerce_checkout'] = array(
			'label'    => __( 'Checkout', 'tutor' ),
			'slug'     => 'ecommerce_checkout',
			'template' => 'basic',
			'icon'     => 'tutor-icon-change',
			'blocks'   => array(
				array(
					'label'      => __( 'Checkout Configuration', 'tutor' ),
					'desc'       => __( 'Customize your checkout process to suit your preferences.', 'tutor' ),
					'slug'       => 'checkout_configuration',
					'block_type' => 'uniform',
					'fields'     => array(
						// @TODO.
						// array(
						// 'key'     => OptionKeys::IS_TAX_APPLICABLE,
						// 'type'    => 'toggle_switch',
						// 'label'   => __( 'Apply Tax Rate', 'tutor' ),
						// 'default' => 'off',
						// 'desc'    => __( 'Enable this to accept payments via PayPal.', 'tutor' ),
						// ),
						array(
							'key'     => OptionKeys::IS_COUPON_APPLICABLE,
							'type'    => 'toggle_switch',
							'label'   => __( 'Enable Coupon Code', 'tutor' ),
							'default' => 'off',
							'desc'    => __( 'Enable this option to allow users to apply a coupon code while checkout.', 'tutor' ),
						),
					),
				),
			),
		);

		$arr                               = apply_filters( 'tutor_after_ecommerce_settings', $arr );
		$fields['monetization']['submenu'] = $arr;

		return $fields;
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
		<div class="tutor-add-payment-method-container tutor-mt-16">
			<button type="button" class="tutor-btn tutor-btn-outline-primary tutor-btn-lg tutor-gap-4px" target="_blank" data-tutor-modal-target="tutor-add-manual-payment-modal">
				<i class="tutor-icon-plus"></i>
				<?php esc_html_e( 'Add manual payment', 'tutor' ); ?>
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
		if ( ! tutor_utils()->is_nonce_verified() ) {
			wp_send_json_error( tutor_utils()->error_message( 'nonce' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$request = Input::sanitize_array(
			$_POST, //phpcs:ignore -- already sanitized.
			array(
				'additional_details'   => 'sanitize_textarea_field',
				'payment_instructions' => 'sanitize_textarea_field',
			)
		);

		$is_update = false;
		$method_id = $request['payment_method_id'] ?? null;

		$success = false;
		if ( $method_id ) {
			$is_update = true;
			$success   = self::update_manual_method( $method_id, $request );
		} else {
			$method_id                    = uniqid();
			$request['payment_method_id'] = $method_id;

			$config_keys = self::get_manual_payment_config_keys();
			$data        = array_intersect_key( $request, $config_keys );

			$success = self::add_new_manual_method( $data );
		}

		if ( $success ) {
			wp_send_json_success( $is_update ? __( 'Data updated successfully!', 'tutor' ) : __( 'Manual payment added successfully!', 'tutor' ) );
		} else {
			wp_send_json_error( $is_update ? __( 'Failed to update!', 'tutor' ) : __( 'Failed to add manual payment', 'tutor' ) );
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
		if ( ! tutor_utils()->is_nonce_verified() ) {
			wp_send_json_error( tutor_utils()->error_message( 'nonce' ) );
		}

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
	 * @return bool
	 */
	public static function add_new_manual_method( array $data ) {
		// Extract fillable fields.
		$new_payment_method = $data;

		$payment_methods = get_option( OptionKeys::MANUAL_PAYMENT_KEY, array() );
		array_push( $payment_methods, $new_payment_method );

		try {
			update_option( OptionKeys::MANUAL_PAYMENT_KEY, $payment_methods );
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
	 * @param mixed $method_id Payment method id.
	 * @param array $data Payment config data.
	 *
	 * @return bool
	 */
	public static function update_manual_method( $method_id, array $data ) {
		// Extract fillable fields.
		$data = array_intersect_key( $data, self::get_manual_payment_config_keys() );

		$payment_methods = get_option( OptionKeys::MANUAL_PAYMENT_KEY, array() );

		if ( is_array( $payment_methods ) && count( $payment_methods ) ) {
			foreach ( $payment_methods as $key => $method ) {
				// Update payment method.
				if ( $method['payment_method_id'] === $method_id ) {
					foreach ( $data as $k => $v ) {
						$method[ $k ] = $v;
					}
					$payment_methods[ $key ] = $method;
					break;
				}
			}
		}

		try {
			update_option( OptionKeys::MANUAL_PAYMENT_KEY, $payment_methods );
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
		$payment_methods = get_option( OptionKeys::MANUAL_PAYMENT_KEY, array() );

		if ( is_array( $payment_methods ) && count( $payment_methods ) ) {
			$payment_methods = array_filter(
				$payment_methods,
				function ( $method ) use ( $method_id ) {
					return $method['payment_method_id'] !== $method_id;
				}
			);
		}

		try {
			update_option( OptionKeys::MANUAL_PAYMENT_KEY, $payment_methods );
			return true;
		} catch ( \Throwable $th ) {
			error_log( $th->getMessage() . ' File: ' . $th->getFile(), ' Line: ' . $th->getLine() );
			return false;
		}
	}

	/**
	 * Add payment gateway settings
	 *
	 * Using filter hook this method will add payment gateway settings
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Ecommerce payment settings.
	 *
	 * @return array
	 */
	public static function add_payment_gateway_settings( $settings ): array {
		$payment_gateway = array(
			'label'      => __( 'Supported payment methods', 'tutor' ),
			'slug'       => 'supported_payment_gateway',
			'block_type' => 'uniform',
			'fields'     => array(),
		);

		array_push( $settings['ecommerce_payment']['blocks'], $payment_gateway );

		foreach ( self::get_default_automate_payment_gateways() as $key => $gateway ) {

			$new_gateway = array(
				'slug'       => "{$key}_payment_gateway",
				'block_type' => 'uniform',
			);

			$config_keys_method = 'get_' . $key . '_config_keys';

			// Set enable field.
			$fields = array(
				array(
					'key'                => "is_enable_{$key}_payment",
					'type'               => 'toggle_switch',
					'label'              => $gateway['label'],
					'label_title'        => '',
					'label_icon'         => tutor()->url . 'assets/images/paypal.svg',
					'label_tag'          => __( 'Supports Subscription', 'tutor' ),
					'default'            => 'off',
					/* translators: %s: gateway name */
					'toggle_fields'      => implode( ',', array_keys( self::$config_keys_method() ) ),
					'has_control_button' => true,
				),
			);

			// Set config fields.
			$fields = array_merge( $fields, self::get_config_fields( $key ) );

			$new_gateway['fields'] = $fields;

			// Append new gateways inside ecommerce payment.
			$settings['ecommerce_payment']['blocks'][] = $new_gateway;
		}

		$settings = apply_filters( 'tutor_ecommerce_payment_settings', $settings );

		$add_more_gateway = array(
			'block_type' => 'action_placeholder',
			'action'     => 'add_more_automate_payment_gateway',
		);

		array_push( $settings['ecommerce_payment']['blocks'], $add_more_gateway );

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
	 * Get default automate payment gateways
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_default_automate_payment_gateways() {
		$gateways = array(
			'paypal' => array(
				'label'                => 'PayPal',
				'is_active'            => self::is_active( 'paypal' ),
				'icon'                 => esc_url_raw( tutor()->url . 'assets/images/paypal.svg' ),
				'support_subscription' => true,
			),
		);

		return apply_filters( 'tutor_default_automate_payment_gateways', $gateways );
	}

	/**
	 * Check if a payment gateways is active
	 *
	 * @since 3.0.0
	 *
	 * @param string $gateway Gateway key.
	 *
	 * @return boolean
	 */
	public static function is_active( string $gateway ) : bool {
		$payments = tutor_utils()->get_option( OptionKeys::PAYMENT_SETTINGS );
		$payments = json_decode( stripslashes( $payments ) );

		if ( $payments ) {
			foreach ( $payments->payment_methods as $method ) {
				if ( $method->name === $gateway ) {
					return (bool) $method->is_active;
				}
			}
		}

		return false;
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

		$manual_payment_methods = get_option( OptionKeys::MANUAL_PAYMENT_KEY );
		if ( is_array( $manual_payment_methods ) && count( $manual_payment_methods ) ) {
			foreach ( $manual_payment_methods as $method ) {

				$method_id = $method['payment_method_id'] ?? '';
				if ( empty( $method_id ) ) {
					continue;
				}

				$is_enable            = $method['is_enable'] ?? 'off';
				$method_name          = $method['payment_method_name'] ?? '';
				$additional_details   = $method['additional_details'] ?? '';
				$payment_instructions = $method['payment_instructions'] ?? '';

				$block = array(
					'label'             => $method_name,
					'slug'              => "manual_payment_gateway_{$method_id}",
					'class'             => 'manual_payment_gateway',
					'block_type'        => 'manual_payment',
					'fields'            => array(),
					'payment_method_id' => $method_id,
					'default'           => $is_enable,
					/* translators: %s: payment method name */
					'desc'              => sprintf( __( 'Enable %s payment method', 'tutor' ), $method_name ),
					'data-attrs'        => array(
						'is-enable'            => $is_enable,
						'payment-method-id'    => $method_id,
						'payment-method-name'  => $method_name,
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
			'paypal_environment'    => 'environment',
			'paypal_merchant_email' => 'email',
			'paypal_client_id'      => 'text',
			'paypal_client_secret'  => 'password',
			'paypal_webhook_id'     => 'password',
			'paypal_webhook_url'    => 'webhook_url',
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
			'is_enable'            => 'toggle',
			'payment_method_id'    => 'hidden',
			'payment_method_name'  => 'text',
			'additional_details'   => 'textarea',
			'payment_instructions' => 'textarea',
		);
	}

	/**
	 * Construct config fields based on gateway
	 *
	 * This method will return an array of config fields based on the provided gateway key.
	 *
	 * @since 3.0.0
	 *
	 * @param string $gateway Gateway name.
	 *
	 * @return array
	 */
	public static function get_config_fields( string $gateway ): array {
		$config_keys_method = 'get_' . $gateway . '_config_keys';

		if ( ! method_exists( __CLASS__, $config_keys_method ) ) {
			return array();
		}

		$config_keys   = self::$config_keys_method();
		$config_fields = array();

		foreach ( $config_keys as $key => $type ) {
			$key_label = ucfirst( str_replace( '_', ' ', $key ) );
			/* translators: %s: key label */
			$placeholder = sprintf( __( 'Enter your %s here', 'tutor' ), $key_label );

			if ( 'environment' === $type ) {
				$config_fields[] = array(
					'key'            => $key,
					'type'           => 'select',
					'default'        => array_keys( self::get_payment_environments() )[0],
					'options'        => self::get_payment_environments(),
					'label'          => $key_label,
					'select_options' => false,
					'desc'           => '',
					'placeholder'    => $placeholder,
				);
			} else {
				$config_fields[] = array(
					'key'         => $key,
					'type'        => $type,
					'label'       => $key_label,
					'desc'        => '',
					'placeholder' => $placeholder,
				);
			}
		}

		return $config_fields;
	}

	/**
	 * Get currency options where key is symbol
	 * and code is value
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_currency_options() {
		$currencies = get_tutor_currencies();

		$options = array();

		foreach ( $currencies as $currency ) {
			$options[ $currency['code'] ] = $currency['code'] . ' (' . $currency['symbol'] . ')';
		}
		return $options;
	}

	/**
	 * Get currency options where key is symbol
	 * and code is value
	 *
	 * It will return $ as default
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $code Currency code.
	 *
	 * @return string
	 */
	public static function get_currency_symbol_by_code( $code ) {
		$currencies = get_tutor_currencies();
		$search     = array_search( $code, array_column( $currencies, 'code' ) );

		if ( false !== $search ) {
			return $currencies[ $search ]['symbol'];
		} else {
			return '$';
		}
	}

	/**
	 * Get webhook fields
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_webhook_fields(): array {
		$arr = array(
			'stripe_webhook_signature_key',
			'paypal_webhook_id',
		);

		return apply_filters( 'tutor_ecommerce_webhook_fields', $arr );
	}


	/**
	 * Get the tax settings from the tutor options.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function ajax_get_tax_settings() {
		$tax_settings = self::get_tax_settings();

		if ( ! empty( $tax_settings->active_country ) ) {
			$tax_settings->active_country = null;
		}

		$this->json_response( __( 'Success', 'tutor' ), $tax_settings );
	}

	/**
	 * Ajax handler to get payment settings
	 *
	 * @since 3.0.0
	 *
	 * @return void send wp_json
	 */
	public function ajax_get_tutor_payment_settings() {
		tutor_utils()->checking_nonce();
		tutor_utils()->check_current_user_capability();

		$settings = tutor_utils()->get_option( OptionKeys::PAYMENT_SETTINGS );
		$this->json_response( __( 'Success', 'tutor' ), json_decode( $settings ) );
	}
}

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

use Tutor\Helpers\HttpHelper;
use TUTOR\Input;
use Tutor\PaymentGateways\Configs\PaypalConfig;
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
		add_action( 'add_manual_payment_btn', __CLASS__ . '::add_manual_payment_btn' );
		add_action( 'wp_ajax_tutor_add_manual_payment_method', __CLASS__ . '::ajax_add_manual_payment_method' );
		add_action( 'wp_ajax_tutor_delete_manual_payment_method', __CLASS__ . '::ajax_delete_manual_payment_method' );

		add_filter( 'tutor_option_input', array( $this, 'format_payment_settings_data' ) );
		add_action( 'wp_ajax_tutor_payment_settings', array( $this, 'ajax_get_tutor_payment_settings' ) );
		add_action( 'wp_ajax_tutor_payment_gateways', array( $this, 'ajax_tutor_payment_gateways' ) );

	}

	/**
	 * Check coupon usage enabled in site checkout.
	 *
	 * @since 3.0.0
	 *
	 * @return boolean
	 */
	public static function is_coupon_usage_enabled() {
		return (bool) tutor_utils()->get_option( OptionKeys::IS_COUPON_APPLICABLE, false );
	}

	/**
	 * Format payment settings data.
	 *
	 * @since 3.0.0
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
						'options'        => self::get_currency_position_options(),
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

		/**
		 * Ecommerce payment settings will be generated from react app.
		 */
		$arr['ecommerce_payment'] = array(
			'label'    => __( 'Payment Methods', 'tutor' ),
			'slug'     => 'ecommerce_payment',
			'desc'     => __( 'Advanced Settings', 'tutor' ),
			'template' => 'basic',
			'icon'     => 'tutor-icon-credit-card',
			'blocks'   => array(
				array(
					'label'      => '',
					'slug'       => 'options',
					'block_type' => 'uniform',
					'class'      => 'tutor-d-none',
					'fields'     => array(
						array(
							'key'   => 'payment_settings',
							'type'  => 'text',
							'label' => __( 'Payment Settings', 'tutor' ),
							'desc'  => '',
						),
					),
				),
			),
		);

		/**
		 * Tax settings will be generated from react app.
		 */
		$arr['ecommerce_tax'] = array(
			'label'    => __( 'Taxes', 'tutor' ),
			'slug'     => 'ecommerce_tax',
			'desc'     => __( 'Advanced Settings', 'tutor' ),
			'template' => 'basic',
			'icon'     => 'tutor-icon-receipt-percent',
			'blocks'   => array(
				array(
					'label'      => '',
					'slug'       => 'options',
					'block_type' => 'uniform',
					'class'      => 'tutor-d-none',
					'fields'     => array(
						array(
							'key'   => 'ecommerce_tax',
							'type'  => 'text',
							'label' => __( 'Tax Settings', 'tutor' ),
							'desc'  => '',
						),
					),
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
						array(
							'key'     => OptionKeys::IS_COUPON_APPLICABLE,
							'type'    => 'toggle_switch',
							'label'   => __( 'Enable Coupon Code', 'tutor' ),
							'default' => 'on',
							'desc'    => __( 'Allow users to apply the coupon code during checkout.', 'tutor' ),
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
	 * Currency position options
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_currency_position_options() {
		return array(
			'left'  => __( 'Left', 'tutor' ),
			'right' => __( 'Right', 'tutor' ),
		);
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
	 * Get payment settings
	 *
	 * @since 3.0.0
	 *
	 * @return object
	 */
	public static function get_payment_settings() {
		$settings = tutor_utils()->get_option( OptionKeys::PAYMENT_SETTINGS );
		$settings = json_decode( stripslashes( $settings ), true );

		return $settings;
	}

	/**
	 * Get specific payment gateway settings.
	 *
	 * @since 3.0.0
	 *
	 * @param string $gateway_name gateway name.
	 *
	 * @return array
	 */
	public static function get_payment_gateway_settings( $gateway_name ) {
		$settings = self::get_payment_settings();

		if ( empty( $gateway_name ) || ! isset( $settings['payment_methods'] ) || ! is_array( $settings['payment_methods'] ) ) {
			return array();
		}

		$data = array_values(
			array_filter(
				$settings['payment_methods'],
				function ( $method ) use ( $gateway_name ) {
					return $method['name'] === $gateway_name;
				}
			)
		);

		return isset( $data[0] ) ? $data[0] : array();
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

		$settings = self::get_payment_settings();
		$this->json_response( __( 'Success', 'tutor' ), $settings );
	}

	/**
	 * Get tutor pro payment gateways
	 *
	 * @since 3.0.0
	 *
	 * @return void send wp_json response
	 */
	public function ajax_tutor_payment_gateways() {
		tutor_utils()->checking_nonce();
		tutor_utils()->check_current_user_capability();

		try {
			$payment_gateways = array();

			$default_gateway = array(
				'name'                 => 'paypal',
				'label'                => 'PayPal',
				'is_installed'         => true,
				'is_active'            => false,
				'icon'                 => tutor()->url . 'assets/images/paypal.svg',
				'support_subscription' => true,
				'fields'               => self::get_paypal_config_fields(),
			);

			$payment_gateways[] = $default_gateway;

			$this->json_response( __( 'Success', 'tutor' ), apply_filters( 'tutor_payment_gateways', $payment_gateways ) );
		} catch ( \Throwable $th ) {
			$this->json_response( $th->getMessage(), null, HttpHelper::STATUS_BAD_REQUEST );
		}
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
			'environment'    => 'select',
			'merchant_email' => 'text',
			'client_id'      => 'secret_key',
			'secret_id'      => 'secret_key',
			'webhook_id'     => 'secret_key',
			'webhook_url'    => 'webhook_url',
		);
	}

	/**
	 * Get config fields
	 *
	 * @since 3.0.0.0
	 *
	 * @return array
	 */
	public static function get_paypal_config_fields() {
		$config_keys   = self::get_paypal_config_keys();
		$config_fields = array();

		foreach ( $config_keys as $key => $type ) {
			if ( 'environment' === $key ) {
				$config_fields[] = array(
					'name'    => $key,
					'label'   => __( ucfirst( str_replace( '_', ' ', $key ) ), 'tutor' ),//phpcs:ignore
					'type'    => $type,
					'options' => array(
						'test' => __( 'Test', 'tutor' ),
						'live' => __( 'Live', 'tutor' ),
					),
					'value'   => 'test',
				);
			} else {
				$config_fields[] = array(
					'name'  => $key,
					'type'  => $type,
					'label' => __( ucfirst( str_replace( '_', ' ', $key ) ), 'tutor-' ),//phpcs:ignore
					'value' => '',
				);
			}
		}

		return $config_fields;
	}
}

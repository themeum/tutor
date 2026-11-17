<?php
/**
 * Shared utilities for payment gateways.
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 4.2.0
 */

namespace Tutor\PaymentGateways;

defined( 'ABSPATH' ) || exit;

use stdClass;
use Brick\Money\Money;
use Brick\Math\RoundingMode;
use Ollyo\PaymentHub\Core\Support\Uri;
use Tutor\PaymentGateways\Exceptions\InvalidDataException;

/**
 * Order data, money, address and metadata helpers shared by payment gateways.
 *
 * Most methods were moved from Ollyo\PaymentHub\Core\Support\System. The money
 * methods need brick/money, which GatewayBase loads from the PayPal vendor folder.
 *
 * @since 4.2.0
 */
class Utils {
	/**
	 * Create an object for the default order data.
	 *
	 * @since 1.0.0
	 * @since 4.2.0 Moved from System.
	 *
	 * @param string $type Order data type: 'payment' or 'refund'. Default 'payment'.
	 *
	 * @return object Default payment or refund data; an empty object for any other type.
	 */
	public static function defaultOrderData( $type = 'payment' ): object {
		$returnData = new stdClass();

		if ( $type === 'payment' ) {
			$returnData = (object) array(
				'type'                 => 'payment',
				'id'                   => null,
				'payment_status'       => 'unpaid',
				'payment_error_reason' => '',
				'transaction_id'       => '',
				'payment_method'       => '',
				'payment_payload'      => '',
				'tax_amount'           => '',
				'fees'                 => '',
				'earnings'             => '',
			);

		} elseif ( $type === 'refund' ) {
			$returnData = (object) array(
				'type'                => 'refund',
				'id'                  => null,
				'refund_status'       => '',
				'refund_id'           => '',
				'payment_method'      => '',
				'refund_amount'       => '',
				'refund_error_reason' => '',
				'refund_payload'      => '',
			);
		}

		return $returnData;
	}


	/**
	 * Validates and sanitizes an email address.
	 *
	 * @param  string $email        The email address to be validated and sanitized.
	 * @return string               The sanitized email address if valid.
	 * @throws InvalidDataException If the email address is invalid according to `FILTER_SANITIZE_EMAIL`
	 *                              or `FILTER_VALIDATE_EMAIL` filters.
	 * @since  1.0.0
	 * @since  4.2.0 Moved from System.
	 */
	public static function validateAndSanitizeEmailAddress( string $email ) {
		$sanitizeEmail = filter_var( $email, FILTER_SANITIZE_EMAIL );
		$validateEmail = filter_var( $email, FILTER_VALIDATE_EMAIL );

		if ( ! $sanitizeEmail || ! $validateEmail ) {
			throw new InvalidDataException( 'Invalid Email Address' );
		}

		return $email;
	}

	/**
	 * Extracts the first and last name parts from a full name string.
	 *
	 * @param  string|null $name    The full name string to be processed.
	 * @return array                An array containing the first and last name, or null if the name is empty.
	 * @since  1.0.0
	 * @since  4.2.0 Moved from System.
	 */
	public static function extractNameParts( $name ): array {
		if ( empty( $name ) ) {
			return array();
		}

		// Trim leading and trailing spaces, split the full name into an array, and remove empty elements
		$nameParts = array_filter( explode( ' ', trim( $name ) ) );
		// Extract the last name.
		$lastName = array_pop( $nameParts );
		// Extract the first name
		$firstName = implode( ' ', $nameParts );

		return array( $firstName, $lastName );
	}

	/**
	 * Splits the address into two parts if it exceeds a certain length.
	 *
	 * @param  object $data  Address data.
	 * @param  int    $maxLength     The maximum length for the first part of the street address.
	 * @return array                The formatted part of the street address.
	 * @since  1.0.0
	 * @since  4.2.0 Moved from System.
	 */
	public static function splitAddress( $data, $maxLength ) {
		if ( empty( $data->address1 ) ) {
			return array();
		}

		$address_1 = mb_strimwidth( $data->address1, 0, $maxLength );
		$address_2 = ( strlen( $data->address1 ) > $maxLength ) ? mb_strimwidth( $data->address1, $maxLength, $maxLength ) : $data->address2;

		return array( $address_1, $address_2 );
	}

	/**
	 * Converts a major currency amount to its minor unit.
	 *
	 * @param  float|string $amount     The major currency amount to convert.
	 * @param  string       $currency   The currency code to use for the conversion.
	 *
	 * @return int|float               The minor currency amount as an integer, or 0.0 when the amount is empty.
	 * @since  1.0.0
	 * @since  4.2.0 Moved from System. Returns 0.0 instead of null when the amount is empty.
	 */
	public static function getMinorAmountBasedOnCurrency( $amount, $currency ) {
		if ( ! empty( $amount ) ) {
			return Money::of( (float) $amount, $currency, null, RoundingMode::HALF_UP )->getMinorAmount()->toInt();
		}

		return 0.0;
	}

	/**
	 * Converts a minor currency amount to its major unit equivalent.
	 *
	 * @param  int|string $amount   The minor currency amount to convert.
	 * @param  string     $currency The currency code to use for the conversion.
	 *
	 * @return float           The major currency amount, or 0.0 when the amount is empty.
	 * @since  1.0.0
	 * @since  4.2.0 Moved from System. Returns 0.0 instead of null when the amount is empty.
	 */
	public static function convertMinorAmountToMajor( $amount, $currency ) {
		if ( ! empty( $amount ) ) {
			return Money::ofMinor( $amount, $currency, null, RoundingMode::HALF_UP )->getAmount()->toFloat();
		}

		return 0.0;
	}

	/**
	 * Determines if the total amount is zero or not.
	 *
	 * @param  object $data Contains the order's necessary charges.
	 * @return bool         Returns true if the total amount equals zero, false otherwise.
	 * @since  1.0.0
	 * @since  4.2.0 Moved from System.
	 */
	public static function isTotalAmountZero( &$data ): bool {
		$data->subtotal        ??= 0;
		$data->tax             ??= 0;
		$data->shipping_charge ??= 0;
		$data->coupon_discount ??= 0;

		$totalAmount = ( $data->subtotal + $data->tax + $data->shipping_charge ) - $data->coupon_discount;

		return empty( filter_var( $totalAmount, FILTER_VALIDATE_FLOAT ) ) ? true : false;
	}

	/**
	 * Updates and returns a webhook URL by encoding success and cancel URLs as parameters.
	 *
	 * @param   object $config  Configuration object that provides the URLs and payment method.
	 * @return  string          The updated webhook URL with encoded data.
	 * @since   1.0.0
	 * @since   4.2.0 Moved from System.
	 */
	public static function updateWebhookUrl( $config ): string {
		$encodedUrlData = base64_encode(
			wp_json_encode(
				array(
					'success_url' => $config->get( 'success_url' ),
					'cancel_url'  => $config->get( 'cancel_url' ),
				)
			)
		);

		$webhookUrl = Uri::getInstance( $config->get( 'webhook_url' ) );
		$webhookUrl->setVar( 'encodedData', $encodedUrlData );
		$webhookUrl->setVar( 'payment_method', $config->get( 'name' ) );

		return $webhookUrl->__toString();
	}

	/**
	 * Builds the Tutor metadata attached to payment gateway requests.
	 *
	 * @since 4.2.0
	 *
	 * @param int    $order_user_id The ID of the user who placed the order.
	 * @param string $gateway_environment Payment environment mode
	 *
	 * @return array The metadata as string key/value pairs.
	 */
	public static function prepareMerchantMetadata( $order_user_id, $gateway_environment ): array {
		$user = $order_user_id ? get_userdata( $order_user_id ) : false;

		return array(
			'tutor_version' => defined( 'TUTOR_VERSION' ) ? TUTOR_VERSION : '',
			'wp_user'       => $user ? "{$user->ID} | {$user->user_email}" : '',
			'site_url'      => get_site_url(),
			'env'           => $gateway_environment,
		);
	}
}

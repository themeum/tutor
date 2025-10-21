<?php

namespace Ollyo\PaymentHub\Payments\Paypal;

use Ollyo\PaymentHub\Core\Support\Path;
use Ollyo\PaymentHub\Core\Support\System;
use GuzzleHttp\Exception\RequestException;
use Ollyo\PaymentHub\Contracts\Config\RepositoryContract;


/**
 * Paypal Helper Class
 */
final class Helper {

	/**
	 * Retrieves and formats items from the provided data.
	 *
	 * @param  object $data The data object containing item details.
	 * @return array        An array of formatted items.
	 * @since  3.0.0
	 */
	public static function getItems( &$data ): array {
		$currency       = $data->currency->code;
		$data->subtotal = 0;

		$items = array_map(
			function ( $item ) use ( $currency, $data ) {

				$price = is_null( $item['discounted_price'] ) ? $item['regular_price'] : $item['discounted_price'];
				$price = number_format( $price, 2, '.', '' );

				$data->subtotal += $price * (int) $item['quantity'];

				return array(
					'name'        => $item['item_name'],
					'quantity'    => (string) $item['quantity'],
					'image_url'   => isset( $item['image'] ) && $item['image'] ? Path::clean( $item['image'] ) : null,
					'unit_amount' => array(
						'currency_code' => $currency,
						'value'         => $price,
					),
				);
			},
			(array) $data->items
		);

		$minChargeApplicable = System::isTotalAmountZero( $data );

		if ( $minChargeApplicable ) {
			$items[] = array(
				'name'        => 'Minimum Charge',
				'description' => 'Minimum charge to process the payment',
				'quantity'    => '1',
				'unit_amount' => array(
					'currency_code' => $currency,
					'value'         => '0.01',
				),
			);

			$data->total_price += 0.01;
			$data->subtotal    += 0.01;
		}

		return $items;
	}

	/**
	 * Creates the amount data array for a payment request.
	 *
	 * This method constructs and returns an array containing the currency code, total price, and item breakdown
	 * for a payment request. It also includes optional fields for shipping, tax, and discount if they are present in the input data.
	 *
	 * @param  object $data   The input data containing the currency, total price, subtotal, and optional fields.
	 * @return array            The formatted amount data including currency code, total price, and breakdown.
	 * @since  3.0.0
	 */
	public static function createAmountData( $data ): array {
		$returnData = array(
			'currency_code' => $data->currency->code,
			'value'         => number_format( $data->total_price, 2, '.', '' ),
		);

		$extraCharges = array(
			'shipping_charge' => 'shipping',
			'tax'             => 'tax_total',
			'coupon_discount' => 'discount',
			'subtotal'        => 'item_total',
		);

		array_walk(
			$extraCharges,
			function ( $value, $key ) use ( $data, &$returnData ) {

				if ( isset( $data->$key ) && ! empty( $data->$key ) ) {
					$returnData['breakdown'][ $value ] = array(
						'currency_code' => $data->currency->code,
						'value'         => number_format( $data->$key, 2, '.', '' ),
					);
				}
			}
		);

		return $returnData;
	}

	/**
	 * Creates an amount array for recurring payments.
	 *
	 * @param  object $data    The data array containing currency and amount information.
	 * @return array            Returns an array with currency code and amount value.
	 * @since  3.0.0
	 */
	public static function createAmountForRecurring( $data ): array {
		return array(
			'currency_code' => $data->currency->code,
			'value'         => number_format( $data->total_amount, 2, '.', '' ),
		);
	}

	/**
	 * Configures the payment source for one-time payments.
	 *
	 * @param  array              $returnData Reference to the data array to be modified with payment source details.
	 * @param RepositoryContract $config Configuration repository instance providing URLs and payment settings.
	 * @return void
	 * @since  3.0.0
	 */
	public static function getPaymentSourceForOneTime( &$returnData, RepositoryContract $config ): void {
		$returnData['payment_source']['paypal'] = array(

			'experience_context' => array(
				'user_action'               => 'PAY_NOW',
				'payment_method_preference' => 'UNRESTRICTED',
				'return_url'                => $config->get( 'success_url' ),
				'cancel_url'                => $config->get( 'cancel_url' ),
			),
		);

		if ( $config->get( 'save_payment_method' ) ) {
			$returnData['payment_source']['paypal']['attributes']['vault'] = array(

				'store_in_vault' => 'ON_SUCCESS',
				'usage_type'     => 'MERCHANT',
			);
		}
	}


	/**
	 * Build and assign the PayPal payment source for a recurring payment.
	 *
	 * @since 3.9.0
	 *
	 * @param array  $returnData       Reference to the data array that will be updated
	 *                                 with the constructed payment source information.
	 * @param object $previous_payload The previous PayPal payload.
	 *
	 * @return void
	 */
	public static function getPaymentSourceForRecurring( &$returnData, $previous_payload ): void {

		$payment_source = isset( $previous_payload->payment_source ) ? self::built_payment_source_from_vault( $previous_payload ) : self::built_payment_source_from_order_details( $previous_payload );

		if ( $payment_source ) {
			$returnData['payment_source'] = $payment_source;
		}
	}

	/**
	 * Handles the error response from an HTTP request and formats a user-friendly error message.
	 *
	 * This method extracts error details from the response body and constructs a comprehensive
	 * error message. It processes different parts of the error response, including issues,
	 * details, and general error messages, and combines them into a single message string.
	 *
	 * @param  RequestException $errorResponse The error response from the HTTP request.
	 * @return string|null                          The formatted error message.
	 * @since  3.0.0
	 */
	public static function handleErrorResponse( $errorResponse ): ?string {
		$message = '';

		if ( ! is_null( $errorResponse->getResponse() ) ) {

			$errorBody = json_decode( $errorResponse->getResponse()->getBody() );

			if ( ! empty( $errorBody->issues ) ) {
				$message .= self::processIssues( $errorBody->issues );
			}

			if ( ! empty( $errorBody->details ) ) {
				$message .= self::processIssues( $errorBody->details );
			}

			if ( ! empty( $errorBody->error ) && ! empty( $errorBody->error_description ) ) {
				$message .= "{$errorBody->error} : {$errorBody->error_description}. ";
			}

			if ( ! empty( $errorBody->message ) ) {
				$message .= "{$errorBody->name} : {$errorBody->message}. ";
			}

			return $message;
		}

		return null;
	}

	/**
	 * Processes a list of issues from an error response and formats them into a single message string.
	 *
	 * This method iterates over the provided issues, extracting relevant information such as
	 * the issue type, description, and associated fields. It constructs a detailed message string
	 * containing all the extracted information.
	 *
	 * @param  array $issues The list of issues from the error response.
	 * @return string         The formatted message string containing details of all issues.
	 * @since  3.0.0
	 */
	private static function processIssues( $issues ): string {

		$final_message = array_reduce(
			$issues,
			function ( $message, $issue ) {

				if ( ! empty( $issue->issue ) ) {
					$message .= "Issue: {$issue->issue}. ";
				}

				if ( ! empty( $issue->description ) ) {
					$message .= "Description: {$issue->description}. ";
				}

				if ( ! empty( $issue->fields ) ) {
					foreach ( $issue->fields as $field ) {
						if ( ! empty( $field->field ) ) {
							$message .= "Field: {$field->field}. ";
						}
					}
				}

				return $message;
			},
			''
		);

		return $final_message;
	}

	/**
	 * Creates the shipping information array for a payment request.
	 *
	 * This method constructs and returns an array containing the shipping type, receiver's full name,
	 * and address for a payment request.
	 *
	 * @param  object $shipping The shipping data containing the receiver's name and address.
	 * @return array        The formatted shipping information including type, name, and address.
	 * @since  3.0.0
	 */
	public static function getShippingInfo( $shipping ): array {
		[$address1, $address2] = System::splitAddress( $shipping, 300 );

		return array(
			'type'    => 'SHIPPING',
			'name'    => array( 'full_name' => $shipping->name ),
			'address' => array(
				'address_line_1' => $address1,
				'address_line_2' => $address2,
				'admin_area_2'   => $shipping->city,
				'admin_area_1'   => $shipping->region,
				'postal_code'    => $shipping->postal_code,
				'country_code'   => $shipping->country->alpha_2,
			),
		);
	}

	/**
	 * Retrieves the URL from an array of links that matches a specified condition.
	 *
	 * This method filters the provided links array to find the link that matches the given
	 * condition. If a matching link is found, it returns the href property of that link.
	 *
	 * @param  array  $links     The array of links to search through.
	 * @param  string $condition The condition to match against the rel property of the links.
	 * @return string|null             The URL if a matching link is found, otherwise null.
	 * @since  3.0.0
	 */
	public static function getUrl( $links, $condition ): ?string {
		$url = array_filter(
			$links,
			function ( $link ) use ( $condition ) {
				return $link->rel === $condition;
			}
		);

		return $url ? reset( $url )->href : null;
	}

	/**
	 * Checks if the required PayPal webhook variables are present in the server request.
	 *
	 * @param array $server_variables The server variables containing PayPal headers.
	 *
	 * @return bool Returns true if all required variables are present, false otherwise.
	 * @since  3.0.0
	 */
	public static function checkWebhookVariables( $server_variables ): bool {
		return isset( $server_variables['HTTP_PAYPAL_AUTH_ALGO'] )
		&& isset( $server_variables['HTTP_PAYPAL_CERT_URL'] )
		&& isset( $server_variables['HTTP_PAYPAL_TRANSMISSION_ID'] )
		&& isset( $server_variables['HTTP_PAYPAL_TRANSMISSION_SIG'] )
		&& isset( $server_variables['HTTP_PAYPAL_TRANSMISSION_TIME'] );
	}

	/**
	 * Build a PayPal payment source array using stored vault details.
	 *
	 * @since 3.9.0.
	 *
	 * @param object $previous_payload The previous PayPal payload containing
	 *                                 a reference to the vault details link.
	 *
	 * @return array|null The formatted PayPal payment source array, or null if not found.
	 */
	private static function built_payment_source_from_vault( $previous_payload ): ?array {

		$vault_url     = self::getUrl( $previous_payload->payment_source->paypal->attributes->vault->links, 'self' );
		$vault_details = Api::get_vault_details( $vault_url );

		if ( ! $vault_details ) {
			return null;
		}

		$paypal_source    = $vault_details->payment_source->paypal;
		$shipping_address = $paypal_source->shipping->address;

		return array(
			'paypal' => array(
				'attributes'         => array(
					'customer' => array(
						'id' => $vault_details->customer->id,
					),
				),
				'vault_id'           => $vault_details->id,
				'email_address'      => $paypal_source->email_address,
				'name'               => array(
					'given_name' => $paypal_source->name->given_name,
					'surname'    => $paypal_source->name->surname,
				),
				'experience_context' => array(
					'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',
				),
				'address'            => self::format_address( $shipping_address ),
			),
		);
	}

	/**
	 * Format a shipping address object into an associative array.
	 *
	 * @since 3.9.0
	 *
	 * @param object $shipping_address The PayPal shipping address object.
	 *
	 * @return array The formatted address as an associative array.
	 */
	private static function format_address( $shipping_address ): array {

		return array(
			'address_line_1' => $shipping_address->address_line_1,
			'address_line_2' => $shipping_address->address_line_2,
			'admin_area_1'   => $shipping_address->admin_area_1 ?? '',
			'admin_area_2'   => $shipping_address->admin_area_2 ?? '',
			'postal_code'    => $shipping_address->postal_code,
			'country_code'   => $shipping_address->country_code,
		);
	}

	/**
	 * Build a PayPal payment source array using order details.
	 *
	 * @since 3.9.0
	 *
	 * @param object $previous_payload The previous PayPal payload containing
	 *                                 a reference to the order details link.
	 *
	 * @return array|null The formatted PayPal payment source array, or null if not found.
	 */
	private static function built_payment_source_from_order_details( $previous_payload ): ?array {

		$order_details_url = self::getUrl( $previous_payload->resource->links, 'up' );
		$order_details     = Api::get_order_details( $order_details_url );

		if ( ! $order_details ) {
			return null;
		}

		$paypal_source    = $order_details->payment_source->paypal;
		$shipping_address = $order_details->purchase_units[0]->shipping->address;

		return array(
			'paypal' => array(
				'attributes'         => array(
					'customer' => array(
						'id' => $paypal_source->attributes->vault->customer->id,
					),
				),
				'vault_id'           => $paypal_source->attributes->vault->id,
				'email_address'      => $paypal_source->email_address,
				'name'               => array(
					'given_name' => $paypal_source->name->given_name,
					'surname'    => $paypal_source->name->surname,
				),
				'experience_context' => array(
					'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',
				),
				'address'            => self::format_address( $shipping_address ),
			),
		);
	}
}

<?php
namespace Ollyo\PaymentHub\Core\Support;

use stdClass;
use Brick\Money\Money;
use Brick\Math\RoundingMode;
use GuzzleHttp\Client;
use Ollyo\PaymentHub\Exceptions\NotFoundException;
use Ollyo\PaymentHub\Exceptions\InvalidDataException;

class System
{
	public static function createClassInstance($class)
	{
		if (!class_exists($class)) {
			throw new NotFoundException(sprintf('The class %s does not found!', $class));
		}

		return new $class();
	}

	public static function parseUrl($url, $component = -1)
	{
		if (extension_loaded('mbstring') && mb_convert_encoding($url, 'ISO-8859-1', 'UTF-8') === $url) {
			return parse_url($url, $component);
		}

		// Build the reserved uri encoded characters map.
		$reservedUriCharactersMap = [
			'%21' => '!',
			'%2A' => '*',
			'%27' => "'",
			'%28' => '(',
			'%29' => ')',
			'%3B' => ';',
			'%3A' => ':',
			'%40' => '@',
			'%26' => '&',
			'%3D' => '=',
			'%24' => '$',
			'%2C' => ',',
			'%2F' => '/',
			'%3F' => '?',
			'%23' => '#',
			'%5B' => '[',
			'%5D' => ']',
		];

		$parts = parse_url(strtr(urlencode($url), $reservedUriCharactersMap), $component);

		return $parts ? array_map('urldecode', $parts): $parts;
	}

	/**
	 * Create an object for the the default order data.
	 *
	 * @return object
	 */
	public static function defaultOrderData($type = 'payment'): object
	{
		$returnData = new stdClass();
		
		if ($type === 'payment') {
			$returnData = (object) [
				'type' 							=> 'payment',
				'id' 							=> null,
				'payment_status' 				=> 'unpaid',
				'payment_error_reason' 			=> '',
				'transaction_id' 				=> '',
				'payment_method' 				=> '',
				'payment_payload' 				=> '',
				'tax_amount' 					=> '',
				'fees' 							=> '',
				'earnings' 						=> ''
			];
			
		} elseif ($type === 'refund') {
			$returnData = (object) [
				'type' 								=> 'refund',
				'id' 								=> null,
				'refund_status' 					=> '',
				'refund_id' 						=> '',
				'payment_method' 					=> '',
				'refund_amount' 					=> '',
				'refund_error_reason' 				=> '',
				'refund_payload' 					=> ''
			];
		}
		
		return $returnData;
	}


	/**
	 * Validates and sanitizes an email address.
	 *
	 * This method takes an email address as input, sanitizes it to remove any illegal
	 * characters or sequences, and then validates it against the standard email format.
	 * If the email address fails either sanitization or validation, an InvalidDataException
	 * is thrown with an appropriate error message.
	 *
	 * @param  string $email 		The email address to be validated and sanitized.
	 * @return string 				The sanitized email address if valid.
	 * @throws InvalidDataException If the email address is invalid according to `FILTER_SANITIZE_EMAIL`
	 *                              or `FILTER_VALIDATE_EMAIL` filters.
	 * @since  1.0.0
	 */
	public static function validateAndSanitizeEmailAddress(string $email)
	{
		$sanitizeEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
		$validateEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
		
		if (!$sanitizeEmail || !$validateEmail) {
			throw new InvalidDataException("Invalid Email Address");		
		}
		
		return $email;
	}

	/**
     * Extracts the first and last name parts from a full name string.
     *
     * @param  string|null $name    The full name string to be processed.
     * @return array           		An array containing the first and last name, or null if the name is empty.
     * @since  1.0.0
     */
    public static function extractNameParts($name) : array
    {
        if (empty($name)) {
            return [];
        }
        
        // Trim leading and trailing spaces, split the full name into an array, and remove empty elements
        $nameParts = array_filter(explode(' ', trim($name)));
        // Extract the last name.
        $lastName  = array_pop($nameParts);
        // Extract the first name       
        $firstName = implode(' ', $nameParts);

        return [$firstName, $lastName];
    }

	/**
	 * Splits the address into two parts if it exceeds a certain length.
	 *
	 * @param  string 	$address1 	The primary address.
	 * @param  string 	$address2 	The secondary address (optional).
	 * @param  int 		$length 	The maximum length for the first part of the street address.
	 * @return array 				The formatted part of the street address.
	 * @since  1.0.0
	 */
	public static function splitAddress($data, $maxLength)
	{
		if (empty($data->address1)) {
			return [];
		}
		
		$address_1 = mb_strimwidth($data->address1, 0, $maxLength);
		$address_2 = (strlen($data->address1) > $maxLength) ? mb_strimwidth($data->address1, $maxLength, $maxLength) : $data->address2;

		return [$address_1, $address_2];
	}

	/**
	 * Converts a major currency amount to its minor unit.
	 *
	 * @param  float|string $amount   	The major currency amount to convert.
	 * @param  string       $currency 	The currency code to use for the conversion.
	 *
	 * @return int|null 				Returns the minor currency amount as an integer, or null if the amount is invalid.
	 * @since  1.0.0
	 */

	public static function getMinorAmountBasedOnCurrency($amount, $currency)
    {
        if (!is_null($amount) || !empty($amount)) {
            return Money::of((float)$amount, $currency, null, RoundingMode::HALF_UP)->getMinorAmount()->toInt();
        }

        return null;
    }

	/**
	 * Converts a minor currency amount to its major unit equivalent.
	 *
	 * @param  int|string $amount   The minor currency amount to convert.
	 * @param  string     $currency The currency code to use for the conversion.
	 *
	 * @return float|null 			Returns the major currency amount as a float, or null if the amount is invalid.
	 * @since  1.0.0
	 */
	public static function convertMinorAmountToMajor($amount, $currency)
	{
		if (!is_null($amount) || !empty($amount)) {
			return Money::ofMinor($amount, $currency, null, RoundingMode::HALF_UP)->getAmount()->toFloat();
		}

		return null;
	}

	/**
	 * Determines if the total amount is zero or not.
	 *
	 * @param  object $data Contains the order's necessary charges.
	 * @return bool         Returns true if the total amount equals zero, false otherwise.
	 * @since  1.0.0
	 */
	public static function isTotalAmountZero(&$data): bool
    {
        $data->subtotal ??= 0;
        $data->tax ??= 0;
        $data->shipping_charge ??= 0;
        $data->coupon_discount ??= 0;
        
        $totalAmount = ($data->subtotal + $data->tax + $data->shipping_charge) - $data->coupon_discount;

        return empty(filter_var($totalAmount, FILTER_VALIDATE_FLOAT)) ? true : false;
    }

	/**
	 * Updates and returns a webhook URL by encoding success and cancel URLs as parameters.
	 *
	 * @param 	object $config 	Configuration object that provides the URLs and payment method.
	 * @return 	string 			The updated webhook URL with encoded data.
	 * @since 	1.0.0
	 */
	public static function updateWebhookUrl($config): string
    {
        $encodedUrlData = base64_encode(json_encode([
            'success_url'   => $config->get('success_url'),
            'cancel_url'    => $config->get('cancel_url')]
        ));

        $webhookUrl = Uri::getInstance($config->get('webhook_url'));
        $webhookUrl->setVar('encodedData', $encodedUrlData);
        $webhookUrl->setVar('payment_method', $config->get('name'));

        return $webhookUrl->__toString();
    }

	/**
     * Sends an HTTP request using the specified method and options.
     *
     * @param   object      $requestData    An object containing the request method, URL, and options (e.g., headers, body).
     * @return  object|null                 The decoded JSON response body if $return is true, otherwise null.
     * @since   1.0.0
     */
    public static function sendHttpRequest($requestData)
    {
        $http       = new Client();
        $method     = $requestData->method;
        $requestUrl = $requestData->url;
        $response   = $http->$method($requestUrl, $requestData->options);

        return json_decode($response->getBody());
    }
}
<?php
namespace Ollyo\PaymentHub\Core\Support;

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
	public static function defaultOrderData(): object
	{
		return (object) [
			'id' 				   => null,
			'payment_status' 	   => 'unpaid',
			'payment_error_reason' => '',
			'transaction_id' 	   => '',
			'payment_method' 	   => '',
			'payment_payload' 	   => ''
		];
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
}
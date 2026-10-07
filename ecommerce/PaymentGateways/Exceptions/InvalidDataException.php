<?php
/**
 * Exception for invalid payment data.
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 4.2.0
 */

namespace Tutor\PaymentGateways\Exceptions;

defined( 'ABSPATH' ) || exit;

use InvalidArgumentException;
use Throwable;

/**
 * Thrown when payment data, such as an email address or webhook payload, is invalid.
 *
 * @since 1.0.0
 * @since 4.2.0 Moved from Ollyo\PaymentHub\Exceptions.
 */
final class InvalidDataException extends InvalidArgumentException implements Throwable {

}

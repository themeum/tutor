<?php
/**
 * Exception for invalid webhook signatures.
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 4.2.0
 */

namespace Tutor\PaymentGateways\Exceptions;

defined( 'ABSPATH' ) || exit;

use RuntimeException;
use Throwable;

/**
 * Thrown when a payment gateway webhook signature cannot be verified.
 *
 * @since 1.0.0
 * @since 4.2.0 Moved from Ollyo\PaymentHub\Exceptions.
 */
final class InvalidSignatureException extends RuntimeException implements Throwable {

}

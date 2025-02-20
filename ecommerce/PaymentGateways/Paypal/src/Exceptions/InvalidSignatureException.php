<?php
namespace Ollyo\PaymentHub\Exceptions;

use RuntimeException;
use Throwable;

final class InvalidSignatureException extends RuntimeException implements Throwable
{
}
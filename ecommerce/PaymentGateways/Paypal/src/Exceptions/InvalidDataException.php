<?php
namespace Ollyo\PaymentHub\Exceptions;

use InvalidArgumentException;
use Throwable;

final class InvalidDataException extends InvalidArgumentException implements Throwable
{
}
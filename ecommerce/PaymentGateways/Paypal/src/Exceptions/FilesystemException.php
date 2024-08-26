<?php

namespace Ollyo\PaymentHub\Exceptions;

use RuntimeException;
use Throwable;

class FilesystemException extends RuntimeException implements Throwable
{
	public function __construct($message = "", $code = 0, Throwable $previous = null)
	{
		parent::__construct(
			$message,
			$code,
			$previous
		);
	}
}

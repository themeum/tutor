<?php
namespace Ollyo\PaymentHub\Core\Support;

use Ollyo\PaymentHub\Exceptions\FilesystemException;

class Path
{

    /**
     * Function to strip additional / or \ in a path name.
     *
     * @param   string  $path  The path to clean.
     * @param   string  $ds    Directory separator (optional).
     *
     * @return  string  The cleaned path.
     *
     * @since   1.0
     * @throws  UnexpectedValueException If $path is not a string.
	 * @see 	https://github.com/joomla-framework/filesystem/blob/ae40545bfe50d5b1cc94fbfbef4dd04a31460d1f/src/Path.php#L191
     */
	public static function clean(string $path, $ds = \DIRECTORY_SEPARATOR)
	{
		$stream = explode('://', $path, 2);
		$scheme = '';
		$path   = $stream[0];

		if (count($stream) >= 2)
		{
			$scheme = $stream[0] . '://';
			$path   = $stream[1];
		}

		$path = trim($path);

		// Remove double slashes and backslashes and convert all slashes and backslashes to DIRECTORY_SEPARATOR
		// If dealing with a UNC path don't forget to prepend the path with a backslash.
		if (($ds === '\\') && ($path[0] === '\\') && ($path[1] === '\\'))
		{
			$path = '\\' . preg_replace('#[/\\\\]+#', $ds, $path);
		}
		else
		{
			$path = preg_replace('#[/\\\\]+#', $ds, $path);
		}

		return $scheme . $path;
	}

	/**
	 * Checks for snooping outside of the file system root.
	 *
	 * @param   string  $path      A file system path to check.
	 * @param   string  $basePath  The base path of the system
	 *
	 * @return  string  A cleaned version of the path or exit on error.
	 *
	 * @since   1.0
	 * @throws  FilesystemException
	 * @see 	https://github.com/joomla-framework/filesystem/blob/ae40545bfe50d5b1cc94fbfbef4dd04a31460d1f/src/Path.php#L151
	 */
	public static function check($path, $basePath = '')
	{
		if (strpos($path, '..') !== false)
		{
			throw new FilesystemException(
				sprintf(
					'%s() - Use of relative paths not permitted',
					__METHOD__
				),
				20
			);
		}

		$path = static::clean($path);

		// If a base path is defined then check the cleaned path is not outside of root
		if (($basePath != '') && strpos($path, static::clean($basePath)) !== 0)
		{
			throw new FilesystemException(
				sprintf(
					'%1$s() - Snooping out of bounds @ %2$s',
					__METHOD__,
					$path
				),
				20
			);
		}

		return $path;
	}
}
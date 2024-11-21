<?php

namespace Ollyo\PaymentHub\Core\Support;

use Ollyo\PaymentHub\Exceptions\NotFoundException;
use Throwable;

final class View
{
	/**
	 * The base url for the view file
	 *
	 * @var string
	 */
	protected $base;

	/**
	 * The data will be shared with the view file.
	 *
	 * @var array
	 */
	protected $data;


	/**
	 * dot separated path for finding the view file.
	 *
	 * @var string
	 */
	protected $path;
	

	public function __construct($path, $data, $base)
	{
		$this->path = $path;
		$this->data = $data;
		$this->base = $base;
	}

	/**
	 * Make a view instance by the help of the path, data and base url.
	 *
	 * @param string $path 		The dot separated path.
	 * @param array $data 		The data array.
	 * @param string|null $base The base url
	 *
	 * @return static
	 */
	public static function make(string $path, $data, $base = null)
	{
		return (new static($path, $data, $base));
	}

	/**
	 * Extract the dot separated path
	 *
	 * @param string $path
	 * @return array
	 */
	protected function extractPath(string $path)
	{
		$path = preg_replace("/\s+/", '', $path);
		return explode('.', $path);
	}

	/**
	 * Generate the view file full path.
	 *
	 * @param string $path
	 * @return string
	 * @throws NotFoundException
	 */
	protected function generateViewPath(string $path)
	{
		$parts = $this->extractPath($path);

		if (is_null($this->base)) {
			$this->base = $this->getLocalBase();
		}

		$defaults = [$this->base];
		$parts = array_merge($defaults, $parts);

		$viewPath = implode('/', $parts) . '.php';
		$viewPath = Path::check($viewPath);

		if (!file_exists($viewPath)) {
			throw new NotFoundException(sprintf('View path "%s" not found!', $viewPath));
		}

		return $viewPath;
	}

	public function getLocalBase()
	{
		return Path::check(dirname(dirname(__DIR__))) . '/layouts';
	}

	/**
	 * Generate the HTML string from the view php file with the data.
	 *
	 * @return string
	 */
	public function render()
	{
		$path = $this->generateViewPath($this->path);
		extract($this->data);

		ob_start();
		require $path;
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
}


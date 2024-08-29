<?php
namespace Ollyo\PaymentHub\Core\Support;

use Ollyo\PaymentHub\Contracts\Support\UriContract;
use RuntimeException;

class Uri implements UriContract
{
	/**
	 * The Uri instance
	 *
	 * @var static|null
	 */
	protected static $instance = null;

	/**
	 * Original URI
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $uri;

	/**
	 * Protocol
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $scheme;

	/**
	 * Host
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $host;

	/**
	 * Port
	 *
	 * @var    integer
	 * @since  1.0
	 */
	protected $port;

	/**
	 * Username
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $user;

	/**
	 * Password
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $pass;

	/**
	 * Path
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $path;

	/**
	 * Query
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $query;

	/**
	 * Anchor
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $fragment;

	/**
	 * Query variable hash
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $vars = [];

	protected function __construct($uri = null)
	{
		if (!is_null($uri)) {
			$this->parse($uri);
		}
	}

	public static function getInstance($uri = 'SERVER')
	{
		if (is_null(static::$instance)) {
			if ($uri === 'SERVER') {
				// Check if the request is over SSL (HTTPS)
				if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtoupper($_SERVER['HTTPS']) !== 'OFF')) {
					$scheme = 'https://';
				} elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtoupper($_SERVER['HTTP_X_FORWARDED_PROTO']) !== 'HTTP') {
					$scheme = 'https://';
				} else {
					$scheme = 'http://';
				}

				if (!empty($_SERVER['PHP_SELF']) && !empty($_SERVER['REQUEST_URI'])) {
					$generatedUri  = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				} else {
					$generatedUri = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];

					if (!empty($_SERVER['QUERY_STRING'])) {
						$generatedUri .= '?' . $_SERVER['QUERY_STRING'];
					}
				}

				$generatedUri = str_replace(["'", '"', '<', '>'], ['%27', '%22', '%3C', '%3E'], $generatedUri);
			} else {
				$generatedUri = $uri;
			}

			static::$instance = new static($generatedUri);
		}

		return static::$instance;
	}

	protected function parse($uri)
	{
		$this->uri = $uri;
		$parts = System::parseUrl($uri);

		if ($parts === false) {
			throw new RuntimeException(sprintf('Could not parse the requested URI %s', $uri));
		}

		if (isset($parts['query']) && strpos($parts['query'], '&amp;') !== false) {
			$parts['query'] = str_replace('&amp;', '&', $parts['query']);
		}

		foreach ($parts as $key => $value) {
			$this->$key = $value;
		}

		if (!empty($parts['query'])) {
			parse_str($parts['query'], $this->vars);
		}

		return !empty($parts) ? true : false;
	}

	protected static function buildQuery(array $params)
	{
		return http_build_query($params, '', '&');
	}

	public function getQuery($toArray = false)
	{
		if ($toArray) {
			return $this->vars;
		}

		return static::buildQuery($this->vars);
	}

	public function getQueryArray()
	{
		return $this->getQuery(true);
	}

	public function getScheme()
	{
		return $this->scheme;
	}

	public function getHost()
	{
		return $this->host;
	}

	public function getPort()
	{
		return $this->port;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function getFragment()
	{
		return $this->fragment;
	}

	public function isSsl()
	{
		return strtoupper($this->getScheme()) === 'HTTPS';
	}

	public function render($parts = self::ALL)
	{
		$query = $this->getQuery();

		$uri = '';
		$uri .= $parts & static::SCHEME ? (!empty($this->scheme) ? $this->scheme . '://' : '') : '';
		$uri .= $parts & static::HOST ? $this->host : '';
		$uri .= $parts & static::PORT ? (!empty($this->port) ? ':' : '') . $this->port : '';
		$uri .= $parts & static::PATH ? $this->path : '';
		$uri .= $parts & static::QUERY ? (!empty($query) ? '?' . $query : '') : '';
		$uri .= $parts & static::FRAGMENT ? (!empty($this->fragment) ? '#' . $this->fragment : '') : '';

		return $uri;
	}

	public function hasVar($name)
	{
		return array_key_exists($name, $this->vars);
	}

	public function getVar($name, $default = null)
	{
		if (!$this->hasVar($name)) {
			return $default;
		}

		return $this->vars[$name];
	}

	public function setVar($name, $value)
	{
		$this->vars[$name] = $value;
	}

	public function toString($parts = ['scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'])
	{
		$bitmask = 0;

		foreach ($parts as $part) {
			$const = 'static::' . strtoupper($part);

			if (\defined($const)) {
				$bitmask |= \constant($const);
			}
		}

		return $this->render($bitmask);
	}

	public function __toString()
	{
		return $this->toString();
	}
}
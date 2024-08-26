<?php
/**
 * Helper class for handling magic ai functionalities
 *
 * @package Tutor\MagicAI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\OpenAI\Support;

/**
 * The support class for creating multipart/form-data
 *
 * @since 3.0.0
 */
final class MultipartFormData {

	/**
	 * The boundary prefix for the multipart form data.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	const BOUNDARY_PREFIX = '--TutorLMSBoundary';

	/**
	 * The unique boundary value for the multipart/form-data.
	 *
	 * @var string|null
	 * @since 3.0.0
	 */
	private $boundary = null;

	/**
	 * The request parameters
	 *
	 * @var array<string, mixed>
	 * @since 3.0.0
	 */
	private array $parameters = array();

	/**
	 * The resources array for keeping the multipart/form-data resource.
	 *
	 * @var array
	 * @since 3.0.0
	 */
	private array $resources = array();

	/**
	 * The constructor method for creating a formData
	 *
	 * @param array $parameters The request parameters.
	 * @since   3.0.0
	 */
	private function __construct( array $parameters ) {
		$this->boundary   = self::BOUNDARY_PREFIX . uniqid();
		$this->parameters = $parameters;
	}

	/**
	 * Create a new FormData instance.
	 *
	 * @param array $parameters The request parameters.
	 * @return self
	 */
	public static function create( array $parameters ) {
		return new self( $parameters );
	}

	/**
	 * Getter method for getting the boundary value.
	 *
	 * @return string
	 * @since 3.0.0
	 */
	public function get_boundary() {
		return $this->boundary;
	}

	/**
	 * Add a resource to the FormData.
	 *
	 * @param string $boundary_resource The multipart boundary resource.
	 * @return self
	 */
	private function add_resource( string $boundary_resource ) {
		$this->resources[] = $boundary_resource;

		return $this;
	}

	/**
	 * Get the created form-data resources.
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public function get_resources() {
		return $this->resources;
	}

	/**
	 * Check if the value is a File value or not.
	 *
	 * @param mixed $value The payload value.
	 * @return boolean
	 * @since 3.0.0
	 */
	private function is_file_value( $value ) {
		if ( is_array( $value ) && isset( $value['tmp_name'] ) && is_file( $value['tmp_name'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a content is a valid JSON string or not.
	 *
	 * @param string $content The string content to check.
	 * @return boolean
	 */
	private function is_valid_base64( $content ) {
		return base64_encode( base64_decode( $content, true ) ) === $content;
	}

	/**
	 * Check if provided resource is a base64 image or not.
	 *
	 * @param string $value The resource value.
	 * @return boolean
	 * @since 3.0.0
	 */
	private function is_base64_file_value( $value ) {
		if ( false === strpos( $value, ',' ) ) {
			return false;
		}

		$file_content = explode( ',', $value )[1];

		if ( empty( $file_content ) ) {
			return false;
		}

		if ( ! $this->is_valid_base64( $file_content ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Create a resource for base64 file.
	 *
	 * @param string $name The resource name.
	 * @param mixed  $value The resource content.
	 * @return string
	 * @since 3.0.0
	 */
	private function create_base64_file_resource( string $name, $value ) {
		$parts        = explode( ',', $value, 2 );
		$file_content = $parts[1];
		$file_content = base64_decode( $file_content );
		$boundary     = $this->get_boundary();
		$filename     = 'image.png';
		$filetype     = 'image/png';

		$form_data = array(
			"--{$boundary}\r\n",
			"Content-Disposition: form-data; name=\"{$name}\"; filename=\"{$filename}\"\r\n",
			"Content-Type: {$filetype}\r\n\r\n",
			"{$file_content}\r\n",
		);

		return implode( '', $form_data );
	}

	/**
	 * Create the form-data resource.
	 *
	 * @param string $name The resource name.
	 * @param string $value The resource value.
	 * @return string
	 * @since 3.0.0
	 */
	private function create_resource( string $name, string $value ) {
		$boundary  = $this->get_boundary();
		$form_data = array(
			"--{$boundary}\r\n",
			"Content-Disposition: form-data; name=\"{$name}\"\r\n\r\n",
			"{$value}\r\n",
		);

		return implode( '', $form_data );
	}

	/**
	 * Create the resource for the file input.
	 *
	 * @param string $name The resource name.
	 * @param array  $value The resource value.
	 * @return string
	 * @since 3.0.0
	 */
	private function create_file_resource( string $name, array $value ) {
		$file_content = file_get_contents( $value['tmp_name'] );
		$filename     = $value['name'];
		$filetype     = $value['type'];
		$boundary     = $this->get_boundary();

		$form_data = array(
			"--{$boundary}\r\n",
			"Content-Disposition: form-data; name=\"{$name}\"; filename=\"{$filename}\"\r\n",
			"Content-Type: {$filetype}\r\n\r\n",
			"{$file_content}\r\n",
		);

		return implode( '', $form_data );
	}

	/**
	 * Prepare the resources recursively.
	 *
	 * @param string $name The resource name.
	 * @param mixed  $value The resource value.
	 * @return void
	 * @since 3.0.0
	 */
	private function prepare( string $name, $value ) {
		if ( $this->is_file_value( $value ) ) {
			$this->add_resource(
				$this->create_file_resource( $name, $value )
			);
		} elseif ( $this->is_base64_file_value( $value ) ) {
			$this->add_resource(
				$this->create_base64_file_resource( $name, $value )
			);
		} elseif ( is_array( $value ) ) {
			foreach ( $value as $key => $nested_value ) {
				$nested_name = $name . "[{$key}]";
				$this->prepare( $nested_name, $nested_value );
			}
		} else {
			$this->add_resource(
				$this->create_resource( $name, $value )
			);
		}
	}

	/**
	 * Build the form-data from the provided parameters.
	 *
	 * @return string
	 */
	public function build() {
		$parameters = $this->parameters;

		foreach ( $parameters as $name => $value ) {
			$this->prepare( $name, $value );
		}

		$resources = $this->get_resources();
		$boundary  = $this->get_boundary();

		$form_data  = implode( '', $resources );
		$form_data .= "--{$boundary}--\r\n";

		return $form_data;
	}

	/**
	 * Create the content type header with the boundary suffix.
	 *
	 * @return string
	 * @since 3.0.0
	 */
	public function content_type_with_boundary() {
		$boundary = $this->get_boundary();

		return "multipart/form-data; boundary={$boundary}";
	}
}

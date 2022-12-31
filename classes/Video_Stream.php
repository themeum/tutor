<?php
/**
 * Video Stream
 *
 * @package Tutor\VideoStream
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class Video_Stream
 *
 * @since 1.0.0
 */
class Video_Stream {

	/**
	 * Path
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $path = '';

	/**
	 * Stream
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $stream = '';

	/**
	 * Buffer time
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $buffer = 102400;

	/**
	 * Start
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	private $start = -1;

	/**
	 * End
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	private $end = -1;

	/**
	 * Size
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	private $size = 0;

	/**
	 * Video format
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $video_format;

	/**
	 * Resolve dependencies
	 *
	 * @since 1.0.0
	 *
	 * @param string $file_path file path.
	 */
	public function __construct( $file_path ) {
		$this->video_format = apply_filters(
			'tutor_video_types',
			array(
				'mp4'  => 'video/mp4',
				'webm' => 'video/webm',
				'ogg'  => 'video/ogg',
			)
		);
		$this->path         = $file_path;
	}

	/**
	 * Open stream
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function open() {
		$this->stream = fopen( $this->path, 'rb' );
		if ( ! ( $this->stream ) ) {
			die( 'Could not open stream for reading' );
		}
	}

	/**
	 * Set proper header to serve the video content
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function set_header() {
		ob_get_clean();

		header( 'Content-Type: ' . $this->video_format[ strtolower( pathinfo( $this->path, PATHINFO_EXTENSION ) ) ] );
		header( 'Cache-Control: max-age=2592000, public' );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', tutor_time() + 2592000 ) . ' GMT' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', @filemtime( $this->path ) ) . ' GMT' );
		$this->start = 0;
		$this->size  = filesize( $this->path );
		$this->end   = $this->size - 1;
		header( 'Accept-Ranges: 0-' . $this->end );

		if ( isset( $_SERVER['HTTP_RANGE'] ) ) {
			$c_end         = $this->end;
			list(, $range) = explode( '=', sanitize_text_field( wp_unslash( $_SERVER['HTTP_RANGE'] ) ), 2 );

			if ( '-' == $range ) {
				$c_start = $this->size - substr( $range, 1 );
			} else {
				$range   = explode( '-', $range );
				$c_start = $range[0];

				$c_end = ( isset( $range[1] ) && is_numeric( $range[1] ) ) ? $range[1] : $c_end;
			}
			$c_end = ( $c_end > $this->end ) ? $this->end : $c_end;
			if ( $c_start > $c_end || $c_start > $this->size - 1 || $c_end >= $this->size ) {
				header( 'HTTP/1.1 416 Requested Range Not Satisfiable' );
				header( "Content-Range: bytes $this->start-$this->end/$this->size" );
				exit;
			}
			$this->start = $c_start;
			$this->end   = $c_end;
			$length      = $this->end - $this->start + 1;
			header( 'HTTP/1.1 206 Partial Content' );
			header( 'Content-Length: ' . $length );
			header( "Content-Range: bytes $this->start-$this->end/" . $this->size );
			header( 'Accept-Ranges: bytes' );
		} else {
			header( 'Content-Length: ' . $this->size );
		}

	}

	/**
	 * Close currently opened stream
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function end() {
		fclose( $this->stream );
		exit;
	}

	/**
	 * Perform the streaming of calculated range
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function stream() {
		$i = $this->start;
		set_time_limit( 0 );
		while ( ! feof( $this->stream ) && $i <= $this->end ) {
			$bytes_to_read = $this->buffer;
			if ( ( $i + $bytes_to_read ) > $this->end ) {
				$bytes_to_read = $this->end - $i + 1;
			}
			$data = @stream_get_contents( $this->stream, $bytes_to_read, $i );
			echo wp_kses_post( $data );
			flush();
			$i += $bytes_to_read;
		}
	}

	/**
	 * Start streaming tutor video content
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function start() {
		$this->open();
		$this->set_header();
		$this->stream();
		$this->end();
	}
}

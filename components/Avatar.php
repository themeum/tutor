<?php
/**
 * Avatar Component Class.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

use Tutor\Components\Constants\Size;

defined( 'ABSPATH' ) || exit;

/**
 * Class Avatar
 *
 * Responsible for rendering user avatars using either an image or initials.
 * Supports various sizes, border styles, and radius options.
 *
 * Example usage:
 *
 * ```php
 * Avatar with image
 * Avatar::make()
 *     ->src('https://example.com/avatar.jpg')
 *     ->size(Size::SIZE_20)
 *     ->bordered()
 *     ->render();
 *
 * Avatar with initials
 * Avatar::make()
 *     ->initials('SK')
 *     ->size(Size::SIZE_32)
 *     ->rounded(false)
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class Avatar extends BaseComponent {

	/**
	 * Avatar size (20, 24, etc).
	 *
	 * @since 4.0.0
	 *
	 * @see Size constants
	 *
	 * @var string
	 */
	protected $size = Size::SIZE_56;

	/**
	 * Avatar type (image or initials).
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $type = 'image';

	/**
	 * Avatar image URL.
	 *
	 * @since 4.0.0
	 * @var string|null
	 */
	protected $src = null;

	/**
	 * User initials.
	 *
	 * @since 4.0.0
	 *
	 * @var string|null
	 */
	protected $initials = null;

	/**
	 * Border enabled flag.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $bordered = false;

	/**
	 * Avatar Shape
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $shape = '';

	/**
	 * Set the avatar size.
	 *
	 * @since 4.0.0
	 *
	 * @param string $size Avatar size, see size constants.
	 *
	 * @return $this
	 */
	public function size( string $size ): self {
		$this->size = sanitize_html_class( $size );
		return $this;
	}

	/**
	 * Set the avatar type (image or initials).
	 *
	 * @since 4.0.0
	 *
	 * @param string $type Avatar type.
	 *
	 * @return $this
	 */
	public function type( string $type ): self {
		$this->type = in_array( $type, array( 'image', 'initials' ), true ) ? $type : 'image';
		return $this;
	}

	/**
	 * Set the avatar shape ('' or square).
	 *
	 * @since 4.0.0
	 *
	 * @param string $shape Avatar shape.
	 *
	 * @return $this
	 */
	public function shape( string $shape = '' ): self {
		$this->shape = $shape;
		return $this;
	}

	/**
	 * Set the image URL for avatar.
	 *
	 * @since 4.0.0
	 *
	 * @param string $src Image URL.
	 * @return $this
	 */
	public function src( string $src ): self {
		$this->src = esc_url_raw( $src );
		return $this;
	}

	/**
	 * Set initials for the avatar.
	 *
	 * @since 4.0.0
	 *
	 * @param string $initials User initials.
	 * @return $this
	 */
	public function initials( string $initials ): self {
		$this->initials = strtoupper( sanitize_text_field( $initials ) );
		return $this;
	}

	/**
	 * Enable or disable border.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $bordered Whether avatar has border.
	 * @return $this
	 */
	public function bordered( bool $bordered = true ): self {
		$this->bordered = $bordered;
		return $this;
	}

	/**
	 * Get the avatar HTML.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML output.
	 */
	public function get(): string {
		$classes = array(
			'tutor-avatar',
			'tutor-avatar-' . esc_attr( $this->size ),
		);

		if ( $this->bordered ) {
			$classes[] = 'tutor-avatar-border';
		}

		if ( ! empty( $this->shape ) ) {
			$classes[] = 'tutor-avatar-' . esc_attr( $this->shape );
		}

		$this->attributes['class'] = trim( implode( ' ', $classes ) . ' ' . ( $this->attributes['class'] ?? '' ) );

		$attributes = $this->render_attributes();

		if ( 'image' === $this->type && $this->src ) {
			$content = sprintf(
				'<img src="%1$s" alt="%2$s" class="tutor-avatar-image" />',
				esc_url( $this->src ),
				esc_attr( $this->initials ?? _x( 'User Avatar', 'image alter text', 'tutor' ) )
			);
		} else {
			$content = sprintf(
				'<span class="tutor-avatar-initials">%s</span>',
				esc_html( $this->initials ?? '' )
			);
		}

		$this->component_string = sprintf( '<div %1$s>%2$s</div>', $attributes, $content );

		return $this->component_string;
	}
}

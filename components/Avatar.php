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
use Tutor\Cache\TutorCache;

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
 * Avatar with user object/ID
 * Avatar::make()
 *     ->user($user_id)
 *     ->size(Size::SIZE_56)
 *     ->render();
 *
 * Avatar with image source
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
	 * User object or ID.
	 *
	 * @since 4.0.0
	 * @var int|object|null
	 */
	protected $user = null;

	/**
	 * Avatar alt text.
	 *
	 * @since 4.0.0
	 * @var string|null
	 */
	protected $alt = null;

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
	 * Set alt text for the avatar.
	 *
	 * @since 4.0.0
	 *
	 * @param string $alt Alt text.
	 * @return $this
	 */
	public function alt( string $alt ): self {
		$this->alt = $alt;
		return $this;
	}

	/**
	 * Set user for the avatar.
	 *
	 * @since 4.0.0
	 *
	 * @param int|object $user User ID or object.
	 * @return $this
	 */
	public function user( $user ): self {
		if ( ! $user ) {
			return $this;
		}

		$user_id    = is_object( $user ) ? $user->ID : (int) $user;
		$cache_key  = 'tutor_avatar_component_user_data_' . $user_id;
		$cache_data = TutorCache::get( $cache_key );

		if ( false !== $cache_data ) {
			if ( $cache_data['src'] ) {
				$this->src( $cache_data['src'] );
			}
			$this->type( $cache_data['type'] );
			$this->initials( $cache_data['initials'] );
			$this->alt( $cache_data['alt'] );
			return $this;
		}

		if ( ! is_object( $user ) ) {
			$user = get_userdata( $user_id );
		}

		if ( is_a( $user, 'WP_User' ) ) {
			$profile_photo = get_user_meta( $user->ID, '_tutor_profile_photo', true );
			$avatar_src    = '';

			if ( $profile_photo ) {
				$url = wp_get_attachment_image_url( $profile_photo, 'thumbnail' );
				if ( $url ) {
					$avatar_src = $url;
				}
			}

			// Generate initials.
			$name        = $user->display_name;
			$arr         = explode( ' ', trim( $name ) );
			$first_char  = ! empty( $arr[0] ) ? mb_substr( $arr[0], 0, 1, 'UTF-8' ) : '';
			$second_char = ! empty( $arr[1] ) ? mb_substr( $arr[1], 0, 1, 'UTF-8' ) : '';
			$initials    = $first_char . $second_char;

			$data = array(
				'src'      => $avatar_src,
				'type'     => $avatar_src ? 'image' : 'initials',
				'initials' => $initials,
				'alt'      => $name,
			);

			// Store in cache.
			TutorCache::set( $cache_key, $data );

			// Apply to current instance.
			if ( $data['src'] ) {
				$this->src( $data['src'] );
			}
			$this->type( $data['type'] );
			$this->initials( $data['initials'] );
			$this->alt( $data['alt'] );
		}

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
				esc_attr( $this->alt ?? $this->initials ?? _x( 'User Avatar', 'image alter text', 'tutor' ) )
			);
		} else {
			$content = sprintf(
				'<span class="tutor-avatar-text">%s</span>',
				esc_html( $this->initials ?? '' )
			);
		}

		$this->component_string = sprintf(
			'<div %1$s><div class="tutor-ratio tutor-ratio-1x1">%2$s</div></div>',
			$attributes,
			$content
		);

		return $this->component_string;
	}
}

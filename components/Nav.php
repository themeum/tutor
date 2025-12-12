<?php
/**
 * Nav Component Class.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;

/**
 * Class Nav
 *
 * Responsible for rendering the nav component.
 *
 *
 * //Example Usage :
 *
 * ```
 * $dropdown = array(
 *        'type'    => 'dropdown',
 *        'icon'    => Icon::ENROLLED,
 *        'active'  => true,
 *        'options' => array(
 *            array(
 *                'label'  => 'Active',
 *                'icon'   => Icon::PLAY_LINE,
 *                'url'    => '#',
 *                'active' => false,
 *            ),
 *            array(
 *                'label'  => 'Enrolled',
 *                'icon'   => Icon::ENROLLED,
 *                'url'    => '#',
 *                'active' => true,
 *            ),
 *        ),
 *    );
 *
 *   echo Nav::make()
 *       ->items( array( $dropdown ) )
 *       ->size( Size::SM )
 *       ->variant( Variant::SECONDARY )
 *       ->render();
 * ```
 *
 * @since 4.0.0
 */
class Nav extends BaseComponent {

	/**
	 * The nav variant.
	 *
	 * @var string
	 */
	protected $nav_variant = Variant::PRIMARY;

	/**
	 * The nav size.
	 *
	 * @var string
	 */
	protected $nav_size = Size::MD;

	/**
	 * The nav items.
	 *
	 * @var array
	 */
	protected $nav_items = array();

	/**
	 * Set the nav variant.
	 *
	 * @since 4.0.0
	 *
	 * @param string $variant the nav variant to set.
	 *
	 * @return self
	 */
	public function variant( $variant = Variant::PRIMARY ): self {
		$this->nav_variant = $variant;
		return $this;
	}

	/**
	 * Set the nav size.
	 *
	 * @since 4.0.0
	 *
	 * @param string $size the nav size.
	 *
	 * @return self
	 */
	public function size( $size = Size::MD ): self {
		$this->nav_size = $size;
		return $this;
	}

	/**
	 * Set the nav items.
	 *
	 * @since 4.0.0
	 *
	 * @param array $items the nav items.
	 *
	 * Expected $items structure:
	 *
	 * $items = array(
	 *            array(
	 *                'type'     => 'link',        // 'link' or 'dropdown'
	 *                'label'    => 'Wishlist',
	 *                'icon'     => Icon::WISHLIST,
	 *                'url'      => '#',
	 *                'active'   => false,
	 *            ),
	 *            array(
	 *                'type'    => 'dropdown',
	 *                'icon'    => Icon::ENROLLED,
	 *                'active'  => true,
	 *                'options' => array(
	 *                    array(
	 *                        'label'  => 'Active',
	 *                        'icon'   => Icon::PLAY_LINE,
	 *                        'url'    => '#',
	 *                        'active' => false,
	 *                    ),
	 *                    array(
	 *                        'label'  => 'Enrolled',
	 *                        'icon'   => Icon::ENROLLED,
	 *                        'url'    => '#',
	 *                        'active' => true,
	 *                    ),
	 *                ),
	 *            ),
	 *         );
	 *
	 * @return self
	 */
	public function items( $items = array() ): self {
		$this->nav_items = $items;
		return $this;
	}

	/**
	 * Get the HTML nav component.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public function get(): string {
		if ( ! count( $this->nav_items ) ) {
			return '';
		}

		ob_start();
		tutor_load_template(
			'core-components.nav',
			array(
				'items'   => $this->nav_items,
				'size'    => $this->nav_size,
				'variant' => $this->nav_variant,
			)
		);
		$output = ob_get_clean();

		return $output;
	}
}

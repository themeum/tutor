<?php
/**
 * Sorting Component Class.
 *
 * Responsible for rendering a sorting dropdown
 * with ascending and descending options.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

/**
 * Class Sorting
 *
 * Example Usage:
 * ```
 * Sorting::make()
 *     ->order( 'DESC' )
 *     ->label_asc( 'Oldest' )
 *     ->label_desc( 'Newest' )
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class Sorting extends BaseComponent {

	/**
	 * Order param
	 *
	 * @var string
	 */
	protected $order = 'DESC';

	/**
	 * Label for ascending order
	 *
	 * @var string
	 */
	protected $label_asc;

	/**
	 * Label for descending order
	 *
	 * @var string
	 */
	protected $label_desc;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->label_asc  = __( 'Oldest First', 'tutor' );
		$this->label_desc = __( 'Newest First', 'tutor' );
	}

	/**
	 * Set order
	 *
	 * @param string $order order.
	 *
	 * @return self
	 */
	public function order( string $order ): self {
		$this->order = $order;
		return $this;
	}

	/**
	 * Set label for ascending order
	 *
	 * @param string $label label.
	 *
	 * @return self
	 */
	public function label_asc( string $label ): self {
		$this->label_asc = $label;
		return $this;
	}

	/**
	 * Set label for descending order
	 *
	 * @param string $label label.
	 *
	 * @return self
	 */
	public function label_desc( string $label ): self {
		$this->label_desc = $label;
		return $this;
	}

	/**
	 * Get component content
	 *
	 * @return string
	 */
	public function get(): string {
		$order = $this->order;

		ob_start();
		?>
		<div
			x-data="tutorPopover({
				placement: 'bottom-end',
				offset: 4,
			})"
		>
			<button type="button" x-ref="trigger" @click="toggle()" class="tutor-btn tutor-btn-outline tutor-btn-x-small tutor-btn-icon">
				<?php tutor_utils()->render_svg_icon( Icon::STEPPER ); ?>
			</button>
			<div 
				x-ref="content"
				x-show="open"
				x-cloak
				@click.outside="handleClickOutside()"
				class="tutor-popover"
			>
				<div class="tutor-popover-menu" style="min-width: 108px;">
					<a 
						href="<?php echo esc_attr( add_query_arg( 'order', 'DESC' ) ); ?>" 
						class="tutor-popover-menu-item<?php echo esc_attr( 'DESC' === $order ? ' tutor-active' : '' ); ?>"
					>
						<?php echo esc_html( $this->label_desc ); ?>
					</a>
					<a 
						href="<?php echo esc_attr( add_query_arg( 'order', 'ASC' ) ); ?>" 
						class="tutor-popover-menu-item<?php echo esc_attr( 'ASC' === $order ? ' tutor-active' : '' ); ?>"
					>
						<?php echo esc_html( $this->label_asc ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}

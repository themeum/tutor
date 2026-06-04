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

use Tutor\Helpers\QueryHelper;
use Tutor\Components\Constants\Positions;
use Tutor\Components\Constants\Color;
use Tutor\Components\Constants\Size;
use Tutor\Components\Popover;
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
 *     ->on_change( 'changeOrder' )
 *     ->bind_active_order( 'currentOrder' )
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
	 * On change callback function name
	 *
	 * @var string
	 */
	protected $on_change = '';

	/**
	 * Alpine.js variable name for active state
	 *
	 * @var string
	 */
	protected $active_order = '';

	/**
	 * Base URL for building sort links (preserves other query params when set).
	 *
	 * @var string
	 */
	protected $base_url = '';

	/**
	 * Component size
	 *
	 * @var string
	 */
	protected $size = Size::X_SMALL;

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
		$this->order = QueryHelper::get_valid_sort_order( $order );
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
	 * Set JS callback on change
	 *
	 * @param string $method_name JS method name.
	 *
	 * @return self
	 */
	public function on_change( string $method_name ): self {
		$this->on_change = $method_name;
		return $this;
	}

	/**
	 * Set Alpine.js active state binding
	 *
	 * @param string $active_order Alpine variable name.
	 * @return self
	 */
	public function bind_active_order( string $active_order ): self {
		$this->active_order = $active_order;
		return $this;
	}

	/**
	 * Set base URL for sort links so other query params are preserved (cumulative filtering).
	 *
	 * @param string $url Full current page URL including query string.
	 *
	 * @return self
	 */
	public function base_url( string $url ): self {
		$this->base_url = $url;
		return $this;
	}

	/**
	 * Set component size
	 *
	 * @param string $size size.
	 *
	 * @return self
	 */
	public function size( string $size ): self {
		$this->size = $size;
		return $this;
	}

	/**
	 * Get component content
	 *
	 * @return string
	 */
	public function get(): string {
		$orders = array(
			'DESC' => $this->label_desc,
			'ASC'  => $this->label_asc,
		);

		$origin = Popover::TRANSFORM_ORIGIN_MAP[ Positions::BOTTOM_END ] ?? 'right.top';

		ob_start();
		?>
		<div
			x-data="tutorPopover({
				placement: 'bottom-end',
				offset: 4,
			})"
		>
			<button
				type="button"
				x-ref="trigger"
				@click="toggle()"
				class="tutor-btn tutor-btn-outline tutor-btn-<?php echo esc_attr( $this->size ); ?> tutor-btn-icon"
				aria-label="<?php esc_attr_e( 'Open sorting options', 'tutor' ); ?>"
			>
				<?php
					$sorting_icon = 'DESC' === $this->order ? Icon::ASCENDING : Icon::DESCENDING;
					SvgIcon::make()->name( $sorting_icon )->size( 16 )->color( Color::SECONDARY )->render();
				?>
			</button>

			<div
				x-ref="content"
				x-show="open"
				x-cloak
				x-transition.<?php echo esc_attr( $origin ); ?>
				@click.outside="handleClickOutside()"
				class="tutor-popover"
			>
				<div class="tutor-popover-menu" style="min-width: 108px;">
					<?php foreach ( $orders as $order => $label ) : ?>
						<?php
						$order_url = ! empty( $this->base_url ) ? add_query_arg( 'order', $order, $this->base_url ) : add_query_arg( 'order', $order );
						?>
						<?php if ( $this->on_change ) : ?>
							<button
								type="button"
								@click="<?php echo esc_js( $this->on_change ); ?>('<?php echo esc_attr( $order ); ?>'); hide()"
								class="tutor-popover-menu-item <?php echo esc_attr( ! $this->active_order && $this->order === $order ? ' tutor-active' : '' ); ?>"
								<?php if ( $this->active_order ) : ?>
									:class="{ 'tutor-active': (<?php echo esc_attr( $this->active_order ); ?> || '') === '<?php echo esc_attr( $order ); ?>' }"
								<?php endif; ?>
							>
								<?php echo esc_html( $label ); ?>
							</button>
						<?php else : ?>
							<a
								href="<?php echo esc_attr( $order_url ); ?>"
								class="tutor-popover-menu-item <?php echo esc_attr( $this->order === $order ? ' tutor-active' : '' ); ?>"
							>
								<?php echo esc_html( $label ); ?>
							</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}

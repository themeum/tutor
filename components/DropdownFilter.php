<?php
/**
 * DropdownFilter Component Class.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\Constants\Positions;
use Tutor\Components\Constants\Color;
use Tutor\Components\Popover;
use TUTOR\Icon;
use TUTOR\Input;

defined( 'ABSPATH' ) || exit;

/**
 * Class DropdownFilter
 *
 * Example Usage:
 * ```
 * // Basic dropdown with custom options
 * DropdownFilter::make()
 *     ->options( $options )
 *     ->query_param( 'status' )
 *     ->variant( Variant::PRIMARY )
 *     ->size( Size::SMALL )
 *     ->search( true )
 *     ->render();
 *
 * // Course filter (convenience method)
 * DropdownFilter::make()
 *     ->options( $courses )
 *     ->count( $total_items )
 *     ->query_param( 'course_id' )
 *     ->variant( Variant::PRIMARY_SOFT )
 *     ->size( Size::X_SMALL )
 *     ->popover_size( Size::MEDIUM )
 *     ->position( Positions::BOTTOM_END )
 *     ->placeholder( __( 'Search Course', 'tutor' ) )
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class DropdownFilter extends BaseComponent {

	/**
	 * Options for the dropdown
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * Whether to show search box
	 *
	 * @var bool
	 */
	protected $show_search = false;

	/**
	 * Trigger button variant
	 *
	 * @var string
	 */
	protected $variant = Variant::LINK;

	/**
	 * Trigger button size
	 *
	 * @var string
	 */
	protected $size = Size::MEDIUM;

	/**
	 * Custom button CSS classes
	 *
	 * @var string
	 */
	protected $button_class = '';

	/**
	 * Search placeholder
	 *
	 * @var string
	 */
	protected $placeholder = '';

	/**
	 * Query parameter name to manage URL and active state
	 *
	 * @var string
	 */
	protected $query_param = '';

	/**
	 * Count to display
	 *
	 * @var int|null
	 */
	protected $count = null;

	/**
	 * Popover size
	 *
	 * @var string
	 */
	protected $popover_size = Size::SMALL;

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
	protected $active_value = '';

	/**
	 * Base URL for building filter links (preserves other query params when set).
	 *
	 * @var string
	 */
	protected $base_url = '';


	/**
	 * Icon size
	 *
	 * @var int|null
	 */
	protected $icon_size = null;

	/**
	 * Popover placement position
	 *
	 * @var string
	 */
	protected $position = Positions::BOTTOM_START;

	/**
	 * Set options list
	 *
	 * @param array $options list of options. Each item: array( 'label' => string, 'url' => string, 'count' => int|null, 'active' => bool, 'value' => mixed ).
	 *
	 * @return self
	 */
	public function options( array $options ): self {
		$this->options = $options;
		return $this;
	}

	/**
	 * Set query parameter name
	 *
	 * @param string $key query parameter name.
	 *
	 * @return self
	 */
	public function query_param( string $key ): self {
		$this->query_param = $key;
		return $this;
	}

	/**
	 * Set search visibility
	 *
	 * @param bool $show whether to show search.
	 *
	 * @return self
	 */
	public function search( bool $show ): self {
		$this->show_search = $show;
		return $this;
	}

	/**
	 * Set button variant
	 *
	 * @param string $variant button variant.
	 *
	 * @return self
	 */
	public function variant( string $variant ): self {
		$this->variant = $variant;
		return $this;
	}

	/**
	 * Set button size
	 *
	 * @param string $size button size.
	 *
	 * @return self
	 */
	public function size( string $size ): self {
		$this->size = $size;
		return $this;
	}

	/**
	 * Set button CSS classes
	 *
	 * @param string $classes CSS classes for the button.
	 *
	 * @return self
	 */
	public function button_class( string $classes ): self {
		$this->button_class = $classes;
		return $this;
	}

	/**
	 * Set search placeholder
	 *
	 * @param string $placeholder search placeholder.
	 *
	 * @return self
	 */
	public function placeholder( string $placeholder ): self {
		$this->placeholder = $placeholder;
		return $this;
	}

	/**
	 * Set popover width
	 *
	 * @param string $size popover width size (Size::SMALL, Size::MEDIUM, Size::LARGE).
	 *
	 * @return self
	 */
	public function popover_size( string $size ): self {
		$this->popover_size = $size;
		return $this;
	}

	/**
	 * Set icon size
	 *
	 * @param int $size icon size.
	 *
	 * @return self
	 */
	public function icon_size( int $size ): self {
		$this->icon_size = $size;
		return $this;
	}

	/**
	 * Set popover placement position.
	 *
	 * @param string $position placement position (Positions::TOP, Positions::BOTTOM, etc).
	 *
	 * @return self
	 */
	public function position( string $position ): self {
		$this->position = $position;
		return $this;
	}

	/**
	 * Get popover width in pixels based on size constant
	 *
	 * @param string $size Size constant.
	 *
	 * @return string Width in pixels.
	 */
	protected function get_popover_width( string $size ): string {
		switch ( $size ) {
			case Size::MEDIUM:
				return '275px';
			case Size::LARGE:
				return '350px';
			case Size::SMALL:
			default:
				return '172px';
		}
	}

	/**
	 * Set count
	 *
	 * @param int $count count to display.
	 *
	 * @return self
	 */
	public function count( int $count ): self {
		$this->count = $count;
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
	 * @param string $active_value Alpine variable name.
	 *
	 * @return self
	 */
	public function bind_active_value( string $active_value ): self {
		$this->active_value = $active_value;
		return $this;
	}

	/**
	 * Set base URL for filter links so other query params are preserved (cumulative filtering).
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
	 * Get the base URL used for auto-generated filter links.
	 *
	 * Removes transient pagination state so changing filters resets pagination.
	 *
	 * @return string
	 */
	protected function get_filter_base_url(): string {
		if ( ! empty( $this->base_url ) ) {
			return remove_query_arg( 'current_page', $this->base_url );
		}

		return remove_query_arg( 'current_page' );
	}


	/**
	 * Get component content
	 *
	 * @return string
	 */
	public function get(): string {
		$options = $this->options;
		if ( empty( $options ) ) {
			return '';
		}

		// Automatically manage active state and URL if query_param is set.
		if ( ! empty( $this->query_param ) ) {
			$current_val = Input::get( $this->query_param, '' );
			$base        = $this->get_filter_base_url();
			foreach ( $options as &$option ) {
				$val = isset( $option['value'] ) ? $option['value'] : '';
				if ( ! isset( $option['active'] ) ) {
					$option['active'] = (string) $val === (string) $current_val;
				}
				if ( ! isset( $option['url'] ) ) {
					if ( ! empty( $base ) ) {
						$option['url'] = ! empty( $val ) ? add_query_arg( $this->query_param, $val, $base ) : remove_query_arg( $this->query_param, $base );
					} else {
						$option['url'] = ! empty( $val ) ? add_query_arg( $this->query_param, $val ) : remove_query_arg( $this->query_param );
					}
				}
			}
			unset( $option );
		}

		$active_option = null;

		foreach ( $options as $option ) {
			if ( ! empty( $option['active'] ) ) {
				$active_option = $option;
				break;
			}
		}

		// Fallback to first option if none is active.
		if ( ! $active_option && ! empty( $options ) ) {
			$active_option = $options[0];
		}

		$label = $active_option['label'] ?? '';
		$count = $active_option['count'] ?? null;

		// If count is set via count() method, use it for the active option.
		if ( null !== $this->count ) {
			$count = $this->count;
		}

		$btn_class = $this->button_class;
		if ( empty( $btn_class ) ) {
			$size_class = 'tutor-btn-medium';
			if ( Size::SMALL === $this->size ) {
				$size_class = 'tutor-btn-small';
			} elseif ( Size::X_SMALL === $this->size ) {
				$size_class = 'tutor-btn-x-small';
			} elseif ( Size::LARGE === $this->size ) {
				$size_class = 'tutor-btn-large';
			}

			if ( Variant::PRIMARY === $this->variant ) {
				$btn_class = 'tutor-btn tutor-btn-primary ' . $size_class;
			} elseif ( Variant::PRIMARY_SOFT === $this->variant ) {
				$btn_class = 'tutor-btn tutor-btn-primary-soft ' . $size_class;
			} elseif ( Variant::OUTLINE === $this->variant ) {
				$btn_class = 'tutor-btn tutor-btn-outline ' . $size_class;
			} else {
				$btn_class = 'tutor-btn tutor-btn-link';
			}
		}

		$origin = Popover::TRANSFORM_ORIGIN_MAP[ $this->position ] ?? 'left.top';

		ob_start();
		?>
		<div
			class="tutor-dropdown-filter"
			x-data="{
				...tutorPopover({
					placement: '<?php echo esc_js( $this->position ); ?>',
					offset: 4,
				}),
				<?php if ( $this->active_value ) : ?>
					labels: <?php echo esc_attr( wp_json_encode( array_column( $options, 'label', 'value' ) ) ); ?>,
					counts: <?php echo esc_attr( wp_json_encode( array_column( $options, 'count', 'value' ) ) ); ?>
				<?php endif; ?>
			}"
		>
			<button 
				type="button" 
				x-ref="trigger" 
				@click="toggle()" 
				class="<?php echo esc_attr( $btn_class ); ?>"
			>
				<span class="tutor-truncate <?php echo Variant::LINK === $this->variant ? esc_attr( 'tutor-text-secondary' ) : ''; ?>" style="max-width: 150px;"
					<?php if ( $this->active_value ) : ?>
						x-text="labels[<?php echo esc_attr( $this->active_value ); ?>] || '<?php echo esc_js( $label ); ?>'"
					<?php endif; ?>
				>
					<?php echo esc_html( $label ); ?>
				</span>
				<?php if ( null !== $count || $this->active_value ) : ?>
					<span class="tutor-font-medium <?php echo Variant::LINK === $this->variant ? esc_attr( 'tutor-text-primary' ) : ''; ?>"
						<?php if ( $this->active_value ) : ?>
							x-text="'(' + (counts[<?php echo esc_attr( $this->active_value ); ?>] ?? '0') + ')'"
							x-show="counts[<?php echo esc_attr( $this->active_value ); ?>] !== null"
						<?php endif; ?>
					>
						<?php echo null !== $count ? '(' . esc_html( $count ) . ')' : ''; ?>
					</span>
				<?php endif; ?>
				<?php
				$icon_size = $this->icon_size;
				if ( null === $icon_size ) {
					$icon_size = in_array( $this->variant, array( Variant::PRIMARY, Variant::PRIMARY_SOFT ), true ) ? 16 : 20;
				}
				SvgIcon::make()->name( Icon::CHEVRON_DOWN_2 )->size( $icon_size )->color( Variant::PRIMARY_SOFT === $this->variant ? Color::BRAND : Color::SECONDARY )->render();
				?>
			</button>

			<div 
				x-ref="content"
				x-show="open"
				x-cloak
				x-transition.<?php echo esc_attr( $origin ); ?>
				@click.outside="handleClickOutside()"
				class="tutor-popover"
				style="width: <?php echo esc_attr( $this->get_popover_width( $this->popover_size ) ); ?>; max-width: unset;"
			>
				<div class="tutor-popover-menu" x-data="{ search: '', get hasResults() { return this.search === '' || [...$el.querySelectorAll('.tutor-popover-menu-item')].some(el => el.style.display !== 'none' && !el.hasAttribute('hidden')); } }">
					<?php if ( $this->show_search ) : ?>
						<div class="tutor-input-field tutor-px-5 tutor-pt-2 tutor-pb-4">
							<div class="tutor-input-wrapper">
								<div class="tutor-input-content tutor-input-content-left">
									<?php SvgIcon::make()->name( Icon::SEARCH_2 )->size( 16 )->color( Color::IDLE )->render(); ?>
								</div>
								<input 
									type="text" 
									class="tutor-input tutor-input-sm tutor-input-content-left" 
									placeholder="<?php echo esc_attr( $this->placeholder ? $this->placeholder : __( 'Search...', 'tutor' ) ); ?>"
									x-model="search"
									@click.stop
								>
							</div>
						</div>
					<?php endif; ?>

					<div class="tutor-overflow-y-auto" style="max-height: 200px;">
						<?php foreach ( $options as $option ) : ?>
							<?php
							$opt_label = $option['label'] ?? '';
							$opt_url   = $option['url'] ?? '#';
							$opt_count = $option['count'] ?? null;
							$is_active = ! empty( $option['active'] );
							$val       = isset( $option['value'] ) ? $option['value'] : '';
							?>
							<?php if ( $this->on_change ) : ?>
								<button
									type="button"
									@click="<?php echo esc_js( $this->on_change ); ?>('<?php echo esc_attr( $val ); ?>'); hide()"
									class="tutor-popover-menu-item <?php echo esc_attr( ! $this->active_value && $is_active ? ' tutor-active' : '' ); ?>"
									<?php if ( $this->active_value ) : ?>
										:class="{ 'tutor-active': (<?php echo esc_attr( $this->active_value ); ?> || '') === '<?php echo esc_attr( $val ); ?>' }"
									<?php endif; ?>
									<?php if ( $this->show_search ) : ?>
										x-show="search === '' || '<?php echo esc_js( $opt_label ); ?>'.toLowerCase().includes(search.toLowerCase())"
									<?php endif; ?>
								>
									<span class="tutor-flex tutor-gap-2 tutor-items-center tutor-w-full">
										<span class="tutor-truncate">
											<?php echo esc_html( $opt_label ); ?>
										</span>
										<?php if ( null !== $opt_count ) : ?>
											<span>
												(<?php echo esc_html( $opt_count ); ?>)
											</span>
										<?php endif; ?>
									</span>
								</button>
							<?php else : ?>
								<a 
									href="<?php echo esc_url( $opt_url ); ?>" 
									class="tutor-popover-menu-item <?php echo $is_active ? 'tutor-active' : ''; ?>"
									<?php if ( $this->show_search ) : ?>
										x-show="search === '' || '<?php echo esc_js( $opt_label ); ?>'.toLowerCase().includes(search.toLowerCase())"
									<?php endif; ?>
								>
									<span class="tutor-flex tutor-gap-2 tutor-items-center tutor-w-full">
										<span class="tutor-truncate">
											<?php echo esc_html( $opt_label ); ?>
										</span>
										<?php if ( null !== $opt_count ) : ?>
											<span>
												(<?php echo esc_html( $opt_count ); ?>)
											</span>
										<?php endif; ?>
									</span>
								</a>
							<?php endif; ?>
						<?php endforeach; ?>

						<?php if ( $this->show_search ) : ?>
							<div 
								x-show="search !== '' && !hasResults" 
								class="tutor-p-5 tutor-text-center tutor-text-secondary tutor-p2"
							>
								<?php esc_html_e( 'No results found', 'tutor' ); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<?php
		$this->component_string = ob_get_clean();

		return $this->component_string;
	}
}

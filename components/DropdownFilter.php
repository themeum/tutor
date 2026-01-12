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
	 * Popover width
	 *
	 * @var string
	 */
	protected $popover_width = '172px';

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
	 * @param string $width popover width (e.g., '275px', '300px').
	 *
	 * @return self
	 */
	public function popover_width( string $width ): self {
		$this->popover_width = $width;
		return $this;
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
	 * Get component content
	 *
	 * @return string
	 */
	public function get(): string {
		$options = $this->options;

		// Automatically manage active state and URL if query_param is set.
		if ( ! empty( $this->query_param ) ) {
			$current_val = Input::get( $this->query_param, '' );
			foreach ( $options as &$option ) {
				$val = isset( $option['value'] ) ? $option['value'] : '';
				if ( ! isset( $option['active'] ) ) {
					$option['active'] = (string) $val === (string) $current_val;
				}
				if ( ! isset( $option['url'] ) ) {
					$option['url'] = ! empty( $val ) ? add_query_arg( $this->query_param, $val ) : remove_query_arg( $this->query_param );
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
				$btn_class = 'tutor-btn tutor-btn-primary tutor-gap-2 ' . $size_class;
			} elseif ( Variant::PRIMARY_SOFT === $this->variant ) {
				$btn_class = 'tutor-btn tutor-btn-primary-soft tutor-gap-2 ' . $size_class;
			} else {
				$btn_class = 'tutor-btn tutor-btn-link tutor-btn-small tutor-font-regular tutor-gap-2 tutor-p-none tutor-min-h-0';
			}
		}

		ob_start();
		?>
		<div
			class="tutor-dropdown-filter"
			x-data="tutorPopover({
				placement: 'bottom-start',
				offset: 4,
			})"
		>
			<button 
				type="button" 
				x-ref="trigger" 
				@click="toggle()" 
				class="<?php echo esc_attr( $btn_class ); ?>"
			>
				<span class="tutor-truncate" style="max-width: 150px;">
					<?php echo esc_html( $label ); ?>
				</span>
				<?php if ( null !== $count ) : ?>
					<span class="tutor-font-medium">
						(<?php echo esc_html( $count ); ?>)
					</span>
				<?php endif; ?>
				<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_DOWN_2, 20, 20, array( 'class' => 'tutor-icon-secondary' ) ); ?>
			</button>

			<div 
				x-ref="content"
				x-show="open"
				x-cloak
				@click.outside="handleClickOutside()"
				class="tutor-popover"
				style="width: <?php echo esc_attr( $this->popover_width ); ?>;"
			>
				<div class="tutor-popover-menu" x-data="{ search: '', get hasResults() { return this.search === '' || [...$el.querySelectorAll('.tutor-popover-menu-item')].some(el => el.style.display !== 'none' && !el.hasAttribute('hidden')); } }">
					<?php if ( $this->show_search ) : ?>
						<div class="tutor-input-field tutor-px-5 tutor-pt-2 tutor-pb-4">
							<div class="tutor-input-wrapper">
								<div class="tutor-input-content tutor-input-content-left">
									<?php tutor_utils()->render_svg_icon( Icon::SEARCH_2, 16, 16, array( 'class' => 'tutor-icon-idle' ) ); ?>
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
							?>
							<a 
								href="<?php echo esc_url( $opt_url ); ?>" 
								class="tutor-popover-menu-item <?php echo $is_active ? 'tutor-active' : ''; ?>"
								<?php if ( $this->show_search ) : ?>
									x-show="search === '' || '<?php echo esc_js( $opt_label ); ?>'.toLowerCase().includes(search.toLowerCase())"
								<?php endif; ?>
							>
								<span class="tutor-truncate">
									<?php echo esc_html( $opt_label ); ?>
								</span>
								<?php if ( null !== $opt_count ) : ?>
									<span>
										(<?php echo esc_html( $opt_count ); ?>)
									</span>
								<?php endif; ?>
							</a>
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

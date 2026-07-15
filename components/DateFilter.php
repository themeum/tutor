<?php
/**
 * Date Filter Component.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

use TUTOR\Icon;
use TUTOR\Input;
use Tutor\Components\Constants\Size;
use Tutor\Components\Popover;

defined( 'ABSPATH' ) || exit;

/**
 * DateFilter Component Class.
 *
 * Example usage:
 *
 * ```php
 * // Single-date picker (bottom-start)
 * DateFilter::make()
 *     ->type( DateFilter::TYPE_SINGLE )
 *     ->render();
 *
 * // Date-range picker (bottom-start, default)
 * DateFilter::make()
 *     ->type( DateFilter::TYPE_RANGE )
 *     ->render();
 *
 * // Date-range picker positioned at bottom-end
 * DateFilter::make()
 *     ->type( DateFilter::TYPE_RANGE )
 *     ->placement( DateFilter::PLACEMENT_BOTTOM_END )
 *     ->render();
 *
 * // With a custom button label
 * DateFilter::make()
 *     ->type( DateFilter::TYPE_RANGE )
 *     ->label( 'Filter by date' )
 *     ->render();
 *
 * // Hide the initial label (show only icon until a range is selected)
 * DateFilter::make()
 *     ->type( DateFilter::TYPE_RANGE )
 *     ->hide_initial_label()
 *     ->render();
 *
 * // Suppress label text entirely (icon-only button)
 * DateFilter::make()
 *     ->type( DateFilter::TYPE_RANGE )
 *     ->show_label( false )
 *     ->render();
 *
 * // Custom trigger button size and icon size
 * DateFilter::make()
 *     ->type( DateFilter::TYPE_RANGE )
 *     ->trigger_size( Size::X_SMALL )
 *     ->icon_size( 14 )
 *     ->render();
 *
 * // Clear related query params when the filter changes (e.g. reset pagination)
 * DateFilter::make()
 *     ->type( DateFilter::TYPE_RANGE )
 *     ->clear_params( array( 'current_page' ) )
 *     ->render();
 *
 * // Extra attributes on the wrapper
 * DateFilter::make()
 *     ->type( DateFilter::TYPE_SINGLE )
 *     ->attr( 'id', 'my-date-filter' )
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class DateFilter extends BaseComponent {

	/**
	 * Date Filter Type Single
	 */
	const TYPE_SINGLE = 'single';

	/**
	 * Date Filter Type Range
	 */
	const TYPE_RANGE = 'range';

	/**
	 * Placement Bottom Start
	 */
	const PLACEMENT_BOTTOM_START = 'bottom-start';

	/**
	 * Placement Bottom End
	 */
	const PLACEMENT_BOTTOM_END = 'bottom-end';

	/**
	 * Component Type
	 *
	 * @var string
	 */
	protected $type = self::TYPE_SINGLE;

	/**
	 * Button Label
	 *
	 * @var string
	 */
	protected $label = '';

	/**
	 * Popover placement
	 *
	 * @var string
	 */
	protected $placement = self::PLACEMENT_BOTTOM_START;

	/**
	 * Button size.
	 *
	 * @var string
	 */
	protected $size = Size::SMALL;

	/**
	 * Icon size.
	 *
	 * @var integer
	 */
	protected $icon_size = Size::SIZE_16;

	/**
	 * Whether to display the label text.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $show_label = true;

	/**
	 * Whether to hide the initial label when no date range is selected.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $hide_initial_label = false;

	/**
	 * Query params to clear when a date filter is applied or cleared (e.g. 'current_page').
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $clear_params = array();

	/**
	 * Set filter type.
	 *
	 * @param string $type Filter type (single|range).
	 *
	 * @return self
	 */
	public function type( string $type ): self {
		$this->type = $type;
		return $this;
	}

	/**
	 * Set Button Size.
	 *
	 * @since 4.0.0
	 *
	 * @param string $size the size type (x-small|small|medium|large).
	 *
	 * @return self
	 */
	public function trigger_size( string $size ): self {
		$allowed_sizes = array( Size::X_SMALL, Size::SMALL, Size::MEDIUM, Size::LARGE );
		if ( in_array( $size, $allowed_sizes, true ) ) {
			$this->size = $size;
		}
		return $this;
	}

	/**
	 * Set the button icon size.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $size the icon size.
	 *
	 * @return self
	 */
	public function icon_size( int $size ): self {
		$this->icon_size = $size;
		return $this;
	}

	/**
	 * Set button label.
	 *
	 * @param string $label Button label.
	 *
	 * @return self
	 */
	public function label( string $label ): self {
		$this->label = $label;
		return $this;
	}

	/**
	 * Set popover placement.
	 *
	 * @param string $placement Popover placement.
	 *
	 * @return self
	 */
	public function placement( string $placement ): self {
		$this->placement = $placement;
		return $this;
	}

	/**
	 * Enable or disable the display of the label text.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $show_label True to show the label, false to hide it.
	 *
	 * @return $this
	 */
	public function show_label( bool $show_label ) {
		$this->show_label = $show_label;
		return $this;
	}

	/**
	 * Hide or show the initial label text before a date range is selected.
	 *
	 * @since 4.0.0
	 *
	 * @return self
	 */
	public function hide_initial_label(): self {
		$this->hide_initial_label = true;
		return $this;
	}

	/**
	 * Set query params to clear when a date filter is applied or cleared.
	 *
	 * Useful for resetting pagination (e.g. 'current_page') or other
	 * context-specific params whenever the date selection changes.
	 *
	 * @since 4.0.0
	 *
	 * @param array $params List of query param keys to remove on apply/clear.
	 *
	 * @return self
	 */
	public function clear_params( array $params ): self {
		$this->clear_params = $params;
		return $this;
	}

	/**
	 * Render the component.
	 *
	 * @return string
	 */
	public function get(): string {
		$is_range = self::TYPE_RANGE === $this->type;

		if ( empty( $this->label ) ) {
			$this->label = $this->calculate_label();
		}

		// Default settings based on type.
		$calendar_options = array(
			'type'        => 'default',
			'clearParams' => $this->clear_params,
		);

		$button_classes = 'tutor-btn tutor-btn-outline';

		if ( $this->size ) {
			$button_classes .= ' tutor-btn-' . $this->size;
		}

		$popover_classes = 'tutor-popover';
		$icon            = Icon::CALENDAR_2;

		if ( $is_range ) {
			$calendar_options = array(
				'type'               => 'multiple',
				'selectionDatesMode' => 'multiple-ranged',
				'clearParams'        => $this->clear_params,
			);
			$popover_classes .= ' tutor-range-calendar-popover';
		}

		if ( empty( $this->label ) ) {
			$button_classes .= ' tutor-btn-icon';
		} else {
			$button_classes .= ' tutor-gap-2';
		}

		$options_json = wp_json_encode( $calendar_options );

		$origin = Popover::TRANSFORM_ORIGIN_MAP[ $this->placement ] ?? 'center.top';

		ob_start();
		?>
		<div x-data="tutorPopover({ placement: '<?php echo esc_attr( $this->placement ); ?>' })" <?php echo $this->get_attributes_string(); //phpcs:ignore -- Sanitization is performed inside get_attributes_string. ?>>

			<button 
				type="button"
				x-ref="trigger"
				@click="toggle()"
				class="<?php echo esc_attr( $button_classes ); ?>"
				<?php if ( empty( $this->label ) ) : ?>
					aria-label="<?php echo $is_range ? esc_attr__( 'Filter by date range', 'tutor' ) : esc_attr__( 'Filter by date', 'tutor' ); ?>"
				<?php endif; ?>
			>
				<?php SvgIcon::make()->name( $icon )->size( $this->icon_size )->render(); ?>
				<?php if ( ! empty( $this->label ) ) : ?>
					<span><?php echo esc_html( $this->label ); ?></span>
				<?php endif; ?>

				<?php if ( $this->has_selection() ) : ?>
					<span @click.stop="$dispatch('tutor-calendar:clear')" class="tutor-cursor-pointer tutor-icon-secondary tutor-flex tutor-align-center">
						<?php SvgIcon::make()->name( Icon::CROSS_2 )->render(); ?>
					</span>
				<?php endif; ?>
			</button>

			<div 
				x-ref="content"
				x-show="open"
				x-cloak
				x-transition.origin.<?php echo esc_attr( $origin ); ?>
				@click.outside="handleClickOutside()"
				class="<?php echo esc_attr( $popover_classes ); ?>"
			>
				<div x-data="tutorCalendar({ options: <?php echo esc_attr( $options_json ); ?>, hidePopover: () => hide() })"></div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Check if filter has active selection.
	 *
	 * @return bool
	 */
	protected function has_selection(): bool {
		if ( self::TYPE_SINGLE === $this->type ) {
			return ! empty( Input::get( 'date' ) );
		}
		return ! empty( Input::get( 'start_date' ) ) && ! empty( Input::get( 'end_date' ) );
	}

	/**
	 * Calculate dynamic label based on URL parameters.
	 *
	 * @return string
	 */
	protected function calculate_label(): string {
		if ( self::TYPE_SINGLE === $this->type ) {
			return Input::get( 'date', '' );
		}

		if ( ! $this->show_label ) {
			return '';
		}

		$start_date = Input::get( 'start_date' );
		$end_date   = Input::get( ( 'end_date' ) );

		if ( ! $start_date || ! $end_date ) {
			return $this->hide_initial_label ? '' : __( 'All Time', 'tutor' );
		}

		$presets = array(
			'today'      => __( 'Today', 'tutor' ),
			'yesterday'  => __( 'Yesterday', 'tutor' ),
			'last-7'     => __( 'Last 7 Days', 'tutor' ),
			'last-14'    => __( 'Last 14 Days', 'tutor' ),
			'last-30'    => __( 'Last 30 Days', 'tutor' ),
			'this-month' => __( 'This Month', 'tutor' ),
			'last-month' => __( 'Last Month', 'tutor' ),
			'last-year'  => __( 'Last Year', 'tutor' ),
		);

		$current_time = time();

		foreach ( $presets as $key => $label ) {
			$range = $this->get_preset_dates( $key, $current_time );
			if ( $range && $range[0] === $start_date && $range[1] === $end_date ) {
				return $label;
			}
		}

		$start_format = date_i18n( 'M j', strtotime( $start_date ) );
		$end_format   = date_i18n( 'M j', strtotime( $end_date ) );

		return sprintf( '%s - %s', $start_format, $end_format );
	}

	/**
	 * Get preset dates.
	 *
	 * @param string $preset Preset key.
	 * @param int    $current_time Current timestamp.
	 *
	 * @return array|null
	 */
	protected function get_preset_dates( $preset, $current_time ) {
		$today = gmdate( 'Y-m-d', $current_time );

		switch ( $preset ) {
			case 'today':
				return array( $today, $today );
			case 'yesterday':
				$d = gmdate( 'Y-m-d', strtotime( '-1 day', $current_time ) );
				return array( $d, $d );
			case 'last-7':
				$start = gmdate( 'Y-m-d', strtotime( '-6 days', $current_time ) );
				return array( $start, $today );
			case 'last-14':
				$start = gmdate( 'Y-m-d', strtotime( '-13 days', $current_time ) );
				return array( $start, $today );
			case 'last-30':
				$start = gmdate( 'Y-m-d', strtotime( '-29 days', $current_time ) );
				return array( $start, $today );
			case 'this-month':
				$start = gmdate( 'Y-m-01', $current_time );
				$end   = gmdate( 'Y-m-t', $current_time );
				return array( $start, $end );
			case 'last-month':
				$first_day_last_month = strtotime( 'first day of previous month', $current_time );
				$start                = gmdate( 'Y-m-d', $first_day_last_month );
				$end                  = gmdate( 'Y-m-t', $first_day_last_month );
				return array( $start, $end );
			case 'last-year':
				$start = gmdate( 'Y-01-01', strtotime( '-1 year', $current_time ) );
				$end   = gmdate( 'Y-12-31', strtotime( '-1 year', $current_time ) );
				return array( $start, $end );
		}
		return null;
	}
}

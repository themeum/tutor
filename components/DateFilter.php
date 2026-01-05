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

use Tutor\Components\Constants\Size;
use TUTOR\Icon;
use TUTOR\Input;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DateFilter Component Class.
 *
 * Example usage:
 *
 * ```php
 * DateFilter::make()
 *     ->type( DateFilter::TYPE_RANGE )
 *     ->placement( 'bottom-start' )
 *     ->render();
 * ```
 *
 * ```php
 * DateFilter::make()
 *     ->type( DateFilter::TYPE_SINGLE )
 *     ->placement( 'bottom-end' )
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
	protected $placement = 'bottom-start';

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
	protected $icon_size = 20;

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
	public function size( string $size ): self {
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
	 * Render the component.
	 *
	 * @return string
	 */
	public function get(): string {
		$is_range = self::TYPE_RANGE === $this->type;

		// Default settings based on type.
		$calendar_options = array(
			'type' => 'default',
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
			);
			$button_classes  .= ' tutor-gap-2';
			$popover_classes .= ' tutor-range-calendar-popover';

			// Default label for range if not set.
			if ( empty( $this->label ) ) {
				$this->label = $this->calculate_label();
			}
		} else {
			$button_classes .= ' tutor-btn-icon';
			if ( 'bottom-start' === $this->placement ) {
				$this->placement = 'bottom-end';
			}
		}

		$options_json = wp_json_encode( $calendar_options );

		ob_start();
		?>
		<div x-data="tutorPopover({ placement: '<?php echo esc_attr( $this->placement ); ?>' })">
			<button 
				type="button"
				x-ref="trigger"
				@click="toggle()"
				class="<?php echo esc_attr( $button_classes ); ?>"
			>
				<?php
				if ( function_exists( 'tutor_utils' ) ) {
					tutor_utils()->render_svg_icon( $icon, $this->icon_size, $this->icon_size );
				}
				?>
				<?php if ( ! empty( $this->label ) ) : ?>
					<span><?php echo esc_html( $this->label ); ?></span>
				<?php endif; ?>
			</button>

			<div 
				x-ref="content"
				x-show="open"
				x-cloak
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
	 * Calculate dynamic label based on URL parameters.
	 *
	 * @return string
	 */
	protected function calculate_label(): string {
		$start_date = Input::get( 'start_date' );
		$end_date   = Input::get( ( 'end_date' ) );

		if ( ! $start_date || ! $end_date ) {
			return __( 'All Time', 'tutor' );
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

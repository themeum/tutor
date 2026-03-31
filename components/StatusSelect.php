<?php
/**
 * Tutor Component: StatusSelect
 *
 * Provides a fluent builder for rendering status selection dropdowns.
 * Direct replacement for the legacy select-with-icon.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;

/**
 * StatusSelect Component Class.
 *
 * Example usage:
 * ```
 * StatusSelect::make()
 *     ->options([
 *         'publish' => ['label' => 'Published', 'variant' => 'success', 'icon' => Icon::CHECK_MARK],
 *         'pending' => ['label' => 'Pending', 'variant' => 'warning', 'icon' => Icon::CLOCK],
 *     ])
 *     ->selected('publish')
 *     ->action('tutor_change_course_status')
 *     ->data(['id' => 123])
 *     ->render();
 * ```
 */
class StatusSelect extends BaseComponent {

	/**
	 * List of options.
	 *
	 * [ 'value' => [ 'label' => 'Text', 'variant' => 'success', 'icon' => 'slug' ] ]
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * Selected value.
	 *
	 * @var string
	 */
	protected $selected = '';

	/**
	 * AJAX action.
	 *
	 * @var string
	 */
	protected $action = '';

	/**
	 * Additional data attributes.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Set options.
	 *
	 * @param array $options List of options.
	 * @return self
	 */
	public function options( array $options ): self {
		$this->options = $options;
		return $this;
	}

	/**
	 * Set selected value.
	 *
	 * @param string $selected Selected value.
	 * @return self
	 */
	public function selected( string $selected ): self {
		$this->selected = $selected;
		return $this;
	}

	/**
	 * Set AJAX action.
	 *
	 * @param string $action AJAX action name.
	 * @return self
	 */
	public function action( string $action ): self {
		$this->action = $action;
		return $this;
	}

	/**
	 * Set additional data attributes.
	 *
	 * @param array $data Key-value pairs for data attributes.
	 * @return self
	 */
	public function data( array $data ): self {
		$this->data = array_merge( $this->data, $data );
		return $this;
	}

	/**
	 * Get the component HTML.
	 *
	 * @return string
	 */
	public function get(): string {
		$selected_option = $this->options[ $this->selected ] ?? null;
		$current_variant = $selected_option['variant'] ?? 'default';

		// Prepare Alpine.js props.
		$alpine_props = wp_json_encode(
			array(
				'selected' => $this->selected,
				'action'   => $this->action,
				'data'     => $this->data,
				'variants' => array_map( fn( $opt ) => $opt['variant'] ?? 'default', $this->options ),
			)
		);

		ob_start();
		?>
		<div 
			class="tutor-status-select tutor-status-select-<?php echo esc_attr( $current_variant ); ?>"
			x-data="tutorStatusSelect(<?php echo esc_attr( $alpine_props ); ?>)"
			:class="variantClasses"
			<?php $this->render_attributes(); ?>
		>
			<div class="tutor-status-select-icon">
				<div class="tutor-status-select-icon-wrapper" x-show="!isLoading">
					<?php foreach ( $this->options as $value => $option ) : ?>
						<?php if ( ! empty( $option['icon'] ) ) : ?>
							<span x-show="selectedValue === '<?php echo esc_attr( $value ); ?>'" <?php echo $this->selected !== $value ? 'style="display: none;"' : ''; ?>>
								<?php SvgIcon::make()->name( $option['icon'] )->render(); ?>
							</span>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
				<span x-show="isLoading" style="display: none;">
					<?php SvgIcon::make()->name( Icon::SPINNER )->size( 14 )->attr( 'class', 'tutor-animate-spin' )->render(); ?>
				</span>
			</div>

			<select 
				x-model="selectedValue"
				@change="updateStatus"
				:disabled="isLoading"
			>
				<?php foreach ( $this->options as $value => $option ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $this->selected, $value ); ?>>
						<?php echo esc_html( $option['label'] ?? '' ); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<div class="tutor-status-select-arrow">
				<?php SvgIcon::make()->name( Icon::CHEVRON_DOWN_2 )->render(); ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}

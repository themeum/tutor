<?php
/**
 * SearchFilter Component Class.
 *
 * Responsible for rendering a search filter component
 * that handles server-side filtering via search param.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

use TUTOR\Icon;
use TUTOR\Input;

defined( 'ABSPATH' ) || exit;

/**
 * Class SearchFilter
 *
 * Example Usage:
 * ```
 * SearchFilter::make()
 *     ->form_id( 'my-search-form' )
 *     ->placeholder( 'Search items...' )
 *     ->hidden_inputs( array( 'type' => 'course' ) )
 *     ->action( 'https://example.com/search' )
 *     ->input_name( 'search' )
 *     ->size( 'small' )
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class SearchFilter extends BaseComponent {

	/**
	 * Form ID
	 *
	 * @var string
	 */
	protected $form_id = 'tutor-search-filter-form';

	/**
	 * Search Placeholder
	 *
	 * @var string
	 */
	protected $placeholder;

	/**
	 * Form Action URL
	 *
	 * @var string
	 */
	protected $action_url;

	/**
	 * Search Input Name
	 *
	 * @var string
	 */
	protected $input_name = 'search';

	/**
	 * Additional hidden inputs
	 *
	 * @var array
	 */
	protected $hidden_inputs = array();

	/**
	 * Set form ID
	 *
	 * @param string $form_id form ID.
	 *
	 * @return self
	 */
	public function form_id( string $form_id ): self {
		$this->form_id = $form_id;
		return $this;
	}

	/**
	 * Set placeholder
	 *
	 * @param string $placeholder placeholder.
	 *
	 * @return self
	 */
	public function placeholder( string $placeholder ): self {
		$this->placeholder = $placeholder;
		return $this;
	}

	/**
	 * Set action URL
	 *
	 * @param string $url action URL.
	 *
	 * @return self
	 */
	public function action( string $url ): self {
		$this->action_url = $url;
		return $this;
	}

	/**
	 * Set input name
	 *
	 * @param string $name input name.
	 *
	 * @return self
	 */
	public function input_name( string $name ): self {
		$this->input_name = $name;
		return $this;
	}

	/**
	 * Add hidden inputs
	 *
	 * @param array $inputs Input array.
	 *
	 * @return self
	 */
	public function hidden_inputs( array $inputs ): self {
		$this->hidden_inputs = array_merge( $this->hidden_inputs, $inputs );
		return $this;
	}

	/**
	 * Input Size
	 *
	 * @var string
	 */
	protected $size = 'medium';

	/**
	 * Set size of the input
	 *
	 * @param string $size size of the input.
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
		$form_id      = $this->form_id;
		$placeholder  = $this->placeholder ?? __( 'Search...', 'tutor' );
		$current_url  = $this->action_url ?? '';
		$input_name   = $this->input_name;
		$search_value = Input::get( $input_name, '' );
		$size         = 'small' === $this->size ? 'tutor-input-sm' : ( 'large' === $this->size ? 'tutor-input-lg' : '' );

		if ( empty( $current_url ) ) {
			// Fallback to current URL with preserved query args if not provided.
			global $wp;
			$current_url = add_query_arg( $_GET, home_url( $wp->request ) );
		}

		ob_start();
		?>
		<form 
			action="<?php echo esc_url( $current_url ); ?>" 
			method="GET" 
			id="<?php echo esc_attr( $form_id ); ?>"
			x-data="tutorForm({ id: '<?php echo esc_attr( $form_id ); ?>', mode: 'onSubmit' })"
			x-bind="getFormBindings()"
			@submit="handleSubmit(
				(data) => { 
					$el.submit();
				}
			)($event)"
		>
			<input type="hidden" name="paged" value="1">

			<?php foreach ( $this->hidden_inputs as $name => $value ) : ?>
				<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
			<?php endforeach; ?>

			<div class="tutor-input-field">
				<div class="tutor-input-wrapper">
					<div class="tutor-input-content tutor-input-content-left">
						<?php
						tutor_utils()->render_svg_icon(
							Icon::SEARCH_2,
							20,
							20,
							array( 'class' => 'tutor-icon-idle' )
						)
						?>
					</div>
					<input 
						type="search"
						name="<?php echo esc_attr( $input_name ); ?>"
						placeholder="<?php echo esc_attr( $placeholder ); ?>"
						class="tutor-input <?php echo esc_attr( $size ); ?> tutor-input-content-left tutor-input-content-clear"
						x-bind="register('<?php echo esc_attr( $input_name ); ?>')"
						x-init="$nextTick(() => setValue('<?php echo esc_attr( $input_name ); ?>', '<?php echo esc_attr( $search_value ); ?>'))"
					/>

					<button 
						type="button"
						class="tutor-input-clear-button"
						x-show="values.<?php echo esc_attr( $input_name ); ?> && String(values.<?php echo esc_attr( $input_name ); ?>).length > 0"
						x-cloak
						aria-label="<?php esc_attr_e( 'Clear search', 'tutor' ); ?>"
						@click="setValue('<?php echo esc_attr( $input_name ); ?>', ''); $el.closest('form').submit();"
					>
						<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ) ); ?>
					</button>
				</div>
			</div>
		</form>
		<?php
		return ob_get_clean();
	}
}

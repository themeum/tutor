<?php
/**
 * Tutor Component: WPEditor
 *
 * Provides a fluent builder for rendering WordPress editor (TinyMCE/QuickTags)
 * with tutorForm integration for validation and state management.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

use Tutor\Helpers\UrlHelper;

defined( 'ABSPATH' ) || exit;

/**
 * WPEditor Component Class.
 *
 * Example usage:
 * ```
 * // Basic editor
 * WPEditor::make()
 *     ->name( 'description' )
 *     ->label( 'Description' )
 *     ->content( 'Default content' )
 *     ->render();
 *
 * // Editor with validation
 * WPEditor::make()
 *     ->name( 'answer' )
 *     ->label( 'Your Answer' )
 *     ->placeholder( 'Write your answer here' )
 *     ->attr( 'x-bind', "register('answer', { required: 'Answer is required' })" )
 *     ->render();
 *
 * // Editor with custom configuration
 * WPEditor::make()
 *     ->name( 'bio' )
 *     ->content( $user_bio )
 *     ->editor_config(
 *         array(
 *             'teeny'         => false,
 *             'media_buttons' => false,
 *             'quicktags'     => false,
 *             'editor_height' => 150,
 *             'tinymce'       => array(
 *                 'toolbar1' => 'bold,italic,underline,link,unlink,removeformat,image,bullist,codesample',
 *                 'toolbar2' => '',
 *                 'toolbar3' => '',
 *                 'plugins'  => 'link,image,lists,codesample',
 *             ),
 *         )
 *     );
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class WPEditor extends BaseComponent {

	/**
	 * Editor name attribute (also used as editor ID if ID is not set).
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Editor ID attribute.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Editor content.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $content = '';

	/**
	 * Editor label text.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $label = '';

	/**
	 * Editor placeholder text.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $placeholder = '';

	/**
	 * Help text below editor.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $help_text = '';

	/**
	 * Error message text.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $error = '';

	/**
	 * Whether editor is required.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $required = false;

	/**
	 * WordPress editor configuration.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $editor_config = array();

	/**
	 * Set editor name.
	 *
	 * @since 4.0.0
	 *
	 * @param string $name Editor name.
	 *
	 * @return $this
	 */
	public function name( $name ) {
		$this->name = sanitize_key( $name );
		return $this;
	}

	/**
	 * Set editor ID.
	 *
	 * @since 4.0.0
	 *
	 * @param string $id Editor ID.
	 *
	 * @return $this
	 */
	public function id( $id ) {
		$this->id = sanitize_key( $id );
		return $this;
	}

	/**
	 * Set editor content.
	 *
	 * @since 4.0.0
	 *
	 * @param string $content Editor content.
	 *
	 * @return $this
	 */
	public function content( $content ) {
		$this->content = $content;
		return $this;
	}

	/**
	 * Set editor label.
	 *
	 * @since 4.0.0
	 *
	 * @param string $label Label text.
	 *
	 * @return $this
	 */
	public function label( $label ) {
		$this->label = $label;
		return $this;
	}

	/**
	 * Set editor placeholder.
	 *
	 * @since 4.0.0
	 *
	 * @param string $placeholder Placeholder text.
	 *
	 * @return $this
	 */
	public function placeholder( $placeholder ) {
		$this->placeholder = $placeholder;
		return $this;
	}

	/**
	 * Set help text.
	 *
	 * @since 4.0.0
	 *
	 * @param string $text Help text.
	 *
	 * @return $this
	 */
	public function help_text( $text ) {
		$this->help_text = $text;
		return $this;
	}

	/**
	 * Set error message.
	 *
	 * @since 4.0.0
	 *
	 * @param string $error Error message.
	 *
	 * @return $this
	 */
	public function error( $error ) {
		$this->error = $error;
		return $this;
	}

	/**
	 * Set required state.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $required Whether editor is required.
	 *
	 * @return $this
	 */
	public function required( $required = true ) {
		$this->required = $required;
		return $this;
	}

	/**
	 * Set WordPress editor configuration.
	 *
	 * @since 4.0.0
	 *
	 * @param array $config Editor configuration.
	 *
	 * @return $this
	 */
	public function editor_config( $config ) {
		$this->editor_config = $config;
		return $this;
	}

	/**
	 * Get default editor configuration.
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	protected function get_default_config() {
		return array(
			'media_buttons' => false,
			'teeny'         => true,
			'quicktags'     => true,
			'editor_height' => 150,
			'textarea_name' => $this->name,
			'tinymce'       => array(
				'skin'        => 'light',
				'skin_url'    => UrlHelper::asset( 'lib/tinymce/light' ),
				'content_css' => $this->get_content_css(),
			),
		);
	}

	/**
	 * Get TinyMCE content styles used inside the editor iframe.
	 *
	 * Mirrors the stylesheet list used by the React WPEditor component so both
	 * entry points render editor content consistently.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	protected function get_content_css() {
		return implode(
			',',
			array(
				includes_url( 'css/dashicons.min.css' ),
				includes_url( 'js/tinymce/skins/wordpress/wp-content.css' ),
				UrlHelper::asset( 'lib/tinymce/light/content.min.css' ),
			)
		);
	}

	/**
	 * Attributes for the wrapper element.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $wrapper_attr = array();

	/**
	 * Set wrapper attribute.
	 *
	 * @since 4.0.0
	 *
	 * @param string $key Attribute key.
	 * @param string $value Attribute value.
	 *
	 * @return $this
	 */
	public function wrapper_attr( $key, $value ) {
		$this->wrapper_attr[ $key ] = $value;
		return $this;
	}

	/**
	 * Get the editor component as HTML string.
	 *
	 * @since 4.0.0
	 *
	 * @return string Component HTML.
	 */
	public function get(): string {
		if ( empty( $this->name ) ) {
			return '';
		}

		ob_start();

		$editor_id      = ! empty( $this->id ) ? $this->id : $this->name;
		$default_config = $this->get_default_config();
		$config         = array_merge( $default_config, $this->editor_config );

		if ( isset( $default_config['tinymce'] ) || isset( $this->editor_config['tinymce'] ) ) {
			$config['tinymce'] = array_merge(
				$default_config['tinymce'] ?? array(),
				$this->editor_config['tinymce'] ?? array()
			);
		}
		$wrapper_class = 'tutor-wp-editor-wrapper tutor-input-field';

		if ( isset( $this->attributes['class'] ) ) {
			$wrapper_class .= ' ' . $this->attributes['class'];
		}
		if ( isset( $this->wrapper_attr['class'] ) ) {
			$wrapper_class .= ' ' . $this->wrapper_attr['class'];
		}

		if ( ! empty( $this->error ) ) {
			$wrapper_class .= ' tutor-wp-editor-error tutor-input-field-error';
		}

		$wrapper_attrs = $this->wrapper_attr;

		$wrapper_attrs['class'] = $wrapper_class;

		?>
		<div 
			<?php
			foreach ( $wrapper_attrs as $key => $value ) {
				printf( '%s="%s"', esc_attr( $key ), esc_attr( $value ) );
			}
			?>
			x-data="tutorWPEditor({ 
				name: '<?php echo esc_js( $this->name ); ?>',
				editorId: '<?php echo esc_js( $editor_id ); ?>',
				placeholder: '<?php echo esc_js( $this->placeholder ); ?>'
			})"
		>
			<?php if ( ! empty( $this->label ) ) : ?>
				<label 
					for="<?php echo esc_attr( $editor_id ); ?>" 
					class="tutor-label <?php echo $this->required ? 'tutor-label-required' : ''; ?>"
				>
					<?php echo esc_html( $this->label ); ?>
				</label>
			<?php endif; ?>

			<div class="tutor-wp-editor-container">
				<!-- Hidden input for form value binding -->
				<input 
					type="hidden" 
					name="<?php echo esc_attr( $this->name ); ?>"
					<?php $this->render_attributes(); ?>
				/>

				<?php
				// Render WordPress editor.
				wp_editor( $this->content, $editor_id, $config );
				?>
			</div>

			<!-- Error Text (Alpine + PHP) -->
			<div 
				class="tutor-error-text" 
				x-cloak 
				x-show="errors?.['<?php echo esc_js( $this->name ); ?>']" 
				x-text="errors?.['<?php echo esc_js( $this->name ); ?>']?.message" 
				role="alert" 
				aria-live="polite"
			>
				<?php echo esc_html( $this->error ); ?>
			</div>

			<!-- Help Text -->
			<?php if ( ! empty( $this->help_text ) ) : ?>
				<div 
					class="tutor-help-text"
					x-show="!errors?.['<?php echo esc_js( $this->name ); ?>']?.message"
				>
					<?php echo esc_html( $this->help_text ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php

		return ob_get_clean();
	}
}

<?php
/**
 * Tutor Component: Tabs
 *
 * Provides a fluent builder for rendering tabs
 * with different orientation.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

use TUTOR\Input;

defined( 'ABSPATH' ) || exit;

/**
 * Class Tabs
 *
 * Responsible for rendering the tab navigation and tab content using Alpine.js (`tutorTabs`).
 * Supports both horizontal and vertical orientations.
 *
 * Example usage:
 *
 * ```php
 * $tabs_data = [
 *     [
 *         'id'      => 'lesson',
 *         'label'   => 'Lessons',
 *         'icon'    => 'book',
 *         'content' => '<p>Lesson content goes here.</p>',
 *     ],
 *     [
 *         'id'      => 'assignments',
 *         'label'   => 'Assignments',
 *         'icon'    => 'file',
 *         'content' => '<p>Assignment content goes here.</p>',
 *     ],
 *     [
 *         'id'      => 'quizzes',
 *         'label'   => 'Quizzes',
 *         'icon'    => 'check',
 *         'content' => '<p>Quiz content goes here.</p>',
 *     ],
 * ];
 *
 * Tabs::make()
 *     ->tabs( $tabs_data )
 *     ->default_tab( 'lesson' )
 *     ->orientation( 'horizontal' )
 *     ->url_params( [ 'enabled' => true ] )
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class Tabs extends BaseComponent {

	/**
	 * Tab data array.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected array $tabs = array();

	/**
	 * Default active tab ID.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected string $default_tab = '';


	/**
	 * Tab orientation type (horizontal|vertical).
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	public const TYPE_HORIZONTAL = 'horizontal';
	public const TYPE_VERTICAL   = 'vertical';

	/**
	 * Tab orientation (horizontal|vertical).
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected string $orientation = 'horizontal';

	/**
	 * URL parameters for syncing tab state.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	protected array $url_params = array(
		'enabled'   => true,
		'paramName' => 'tab',
	);

	/**
	 * Set the tab data.
	 *
	 * @since 4.0.0
	 *
	 * @param array $tabs Tabs configuration data.
	 *
	 * @return $this
	 */
	public function tabs( array $tabs ) {
		$this->tabs = $tabs;
		return $this;
	}

	/**
	 * Set the default active tab.
	 *
	 * @since 4.0.0
	 *
	 * @param string $tab_id Tab ID to activate by default.
	 *
	 * @return $this
	 */
	public function default_tab( string $tab_id ) {
		$this->default_tab = Input::get( 'tab', $tab_id );
		return $this;
	}

	/**
	 * Set the tab orientation.
	 *
	 * @since 4.0.0
	 *
	 * @param string $orientation Either 'horizontal' or 'vertical'.
	 *
	 * @return $this
	 */
	public function orientation( string $orientation ) {
		$this->orientation = in_array( $orientation, array( self::TYPE_HORIZONTAL, self::TYPE_VERTICAL ), true )
			? $orientation
			: self::TYPE_HORIZONTAL;
		return $this;
	}

	/**
	 * Set the URL parameter configuration for tab state.
	 *
	 * @since 4.0.0
	 *
	 * @param array $params URL parameter settings.
	 *
	 * @return $this
	 */
	public function url_params( array $params ) {
		$this->url_params = wp_parse_args(
			$params,
			array(
				'enabled'   => true,
				'paramName' => 'tab',
			)
		);
		return $this;
	}

	/**
	 * Get the tabs component.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML markup for the tabs component.
	 */
	public function get(): string {
		$tabs_json   = wp_json_encode( $this->tabs );
		$default     = esc_js( $this->default_tab );
		$orientation = esc_attr( $this->orientation );
		$url_params  = wp_json_encode( $this->url_params );

		ob_start();
		?>
		<div 
			x-data='tutorTabs({
				tabs: <?php echo $tabs_json; // phpcs:ignore ?>,
				orientation: "<?php echo $orientation; // phpcs:ignore ?>",
				defaultTab: "<?php echo $default; // phpcs:ignore ?>",
				urlParams: <?php echo $url_params; // phpcs:ignore ?>
			})'
		>
			<div x-ref="tablist" class="tutor-tabs-nav" role="tablist" aria-orientation="<?php echo $orientation; // phpcs:ignore ?>">
				<template x-for="tab in tabs" :key="tab.id">
					<button
						type="button"
						role="tab"
						:class='getTabClass(tab)'
						x-bind:aria-selected="isActive(tab.id)"
						:disabled="tab.disabled ? true : false"
						@click="selectTab(tab.id)"
					>
						<span x-data="tutorIcon({ name: tab.icon, width: 24, height: 24 })"></span>
						<span x-text="tab.label"></span>
					</button>
				</template>
			</div>

			<div class="tutor-tabs-content">
				<template x-for="tab in tabs" :key="tab.id">
					<div
						class="tutor-tab-panel"
						role="tabpanel"
						x-show="activeTab === tab.id"
						x-cloak
						x-html="tab.content"
					></div>
				</template>
			</div>
		</div>
		<?php
		$this->component_string = ob_get_clean();

		return $this->component_string;
	}
}


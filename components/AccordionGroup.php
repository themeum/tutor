<?php
/**
 * Tutor Component: AccordionGroup
 *
 * Provides a container for managing multiple accordion items with
 * Alpine.js state management.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AccordionGroup Component Class.
 *
 * Example usage:
 * ```
 * echo AccordionGroup::make()
 *     ->add_item(
 *         Accordion::make()
 *             ->id( 'about' )
 *             ->title( 'About this Course' )
 *             ->content( '<p>Course description...</p>' )
 *             ->open()
 *     )
 *     ->add_item(
 *         Accordion::make()
 *             ->id( 'requirements' )
 *             ->title( 'Requirements' )
 *             ->content( '<p>Prerequisites...</p>' )
 *     )
 *     ->allow_multiple()
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class AccordionGroup extends BaseComponent {

	/**
	 * Accordion items.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $items = array();

	/**
	 * Whether multiple items can be open.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $allow_multiple = false;

	/**
	 * Group ID for Alpine.js.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $group_id = '';

	/**
	 * Add accordion item to group.
	 *
	 * @since 4.0.0
	 *
	 * @param Accordion $item Accordion item instance.
	 *
	 * @return $this
	 */
	public function add_item( Accordion $item ) {
		$this->items[] = $item;
		return $this;
	}

	/**
	 * Allow multiple items to be open.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $allow Whether to allow multiple open items.
	 *
	 * @return $this
	 */
	public function allow_multiple( $allow = true ) {
		$this->allow_multiple = (bool) $allow;
		return $this;
	}

	/**
	 * Set group ID.
	 *
	 * @since 4.0.0
	 *
	 * @param string $id Group ID.
	 *
	 * @return $this
	 */
	public function group_id( $id ) {
		$this->group_id = sanitize_key( $id );
		return $this;
	}

	/**
	 * Render the accordion group HTML.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML output.
	 */
	public function render(): string {
		if ( empty( $this->items ) ) {
			return '';
		}

		// Generate group ID if not set.
		if ( empty( $this->group_id ) ) {
			$this->group_id = 'acc-group-' . wp_rand( 1000, 9999 );
		}

		// Build Alpine.js config.
		$alpine_config = array();
		if ( ! $this->allow_multiple ) {
			$alpine_config['allowMultiple'] = false;
		}

		// Get initially open items.
		$open_items = array();
		foreach ( $this->items as $index => $item ) {
			if ( $item instanceof Accordion && method_exists( $item, 'is_open' ) ) {
				// This assumes we add a getter method to check if item is open
				$open_items[] = $index;
			}
		}

		if ( ! empty( $open_items ) ) {
			$alpine_config['openItems'] = $open_items;
		}

		$alpine_json = ! empty( $alpine_config ) 
			? sprintf( 'tutorAccordion(%s)', wp_json_encode( $alpine_config ) )
			: 'tutorAccordion()';

		// Merge custom classes.
		$group_classes = 'tutor-accordion-group';
		if ( ! empty( $this->attributes['class'] ) ) {
			$group_classes .= ' ' . $this->attributes['class'];
			unset( $this->attributes['class'] );
		}

		$this->attributes['x-data'] = $alpine_json;
		$this->attributes['class']  = $group_classes;

		$group_attrs = $this->render_attributes();

		// Render all items with proper index.
		$items_html = '';
		foreach ( $this->items as $index => $item ) {
			if ( $item instanceof Accordion ) {
				$item->index( $index );
				$items_html .= $item->render();
			}
		}

		return sprintf(
			'<div %s>%s</div>',
			$group_attrs,
			$items_html
		);
	}

}
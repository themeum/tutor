<?php
/**
 * Tutor Component: Confirmation Modal
 *
 * Provides a reusable confirmation modal with Alpine.js integration.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

/**
 * Confirmation Modal Component Class.
 *
 * ```
 * // Example usage:
 *
 * ConfirmationModal::make()
 *     ->id( 'delete-course-modal' )
 *     ->title( 'Delete This Course?' )
 *     ->message( 'Are you sure you want to delete this course permanently? Please confirm your choice.' )
 *     ->confirm_handler( 'handleDeleteCourse(payload?.courseId)' )
 *     ->mutation_state( 'deleteMutation' )
 *     ->render();
 *
 * // With custom icon
 * ConfirmationModal::make()
 *     ->id( 'delete-announcement-modal' )
 *     ->title( 'Delete This Announcement?' )
 *     ->message( 'This action cannot be undone.' )
 *     ->icon( Icon::DELETE_2, 80, 80 )
 *     ->confirm_handler( 'handleDeleteAnnouncement(payload?.announcementId)' )
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class ConfirmationModal extends BaseComponent {

	/**
	 * Modal unique ID.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Modal title.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Confirmation message.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * Icon name from Icon class.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $icon = Icon::BIN;

	/**
	 * Icon width.
	 *
	 * @since 4.0.0
	 *
	 * @var int
	 */
	protected $icon_width = 100;

	/**
	 * Icon height.
	 *
	 * @since 4.0.0
	 *
	 * @var int
	 */
	protected $icon_height = 100;

	/**
	 * Confirm handler function name (Alpine.js method).
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $confirm_handler = '';

	/**
	 * Mutation state variable name (e.g., 'deleteMutation').
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $mutation_state = 'confirmMutation';

	/**
	 * Cancel button text.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $cancel_text = '';

	/**
	 * Confirm button text.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $confirm_text = '';

	/**
	 * Custom width for modal content.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $width = '426px';

	/**
	 * Custom confirm button HTML.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $confirm_btn = '';

	/**
	 * Custom cancel button HTML.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $cancel_btn = '';

	/**
	 * Set modal ID.
	 *
	 * @since 4.0.0
	 *
	 * @param string $id Modal unique ID.
	 *
	 * @return $this
	 */
	public function id( $id ) {
		$this->id = sanitize_key( $id );
		return $this;
	}

	/**
	 * Set modal title.
	 *
	 * @since 4.0.0
	 *
	 * @param string $title Modal title.
	 *
	 * @return $this
	 */
	public function title( string $title ) {
		$this->title = $title;
		return $this;
	}

	/**
	 * Set confirmation message.
	 *
	 * @since 4.0.0
	 *
	 * @param string $message Confirmation message.
	 *
	 * @return $this
	 */
	public function message( string $message ) {
		$this->message = $message;
		return $this;
	}

	/**
	 * Set icon.
	 *
	 * @since 4.0.0
	 *
	 * @param string $icon Icon name from Icon class.
	 * @param int    $width Icon width.
	 * @param int    $height Icon height.
	 *
	 * @return $this
	 */
	public function icon( string $icon, int $width = 100, int $height = 100 ) {
		$this->icon        = $icon;
		$this->icon_width  = $width;
		$this->icon_height = $height;
		return $this;
	}

	/**
	 * Set confirm handler function name.
	 *
	 * @since 4.0.0
	 *
	 * @param string $handler Alpine.js method name.
	 *
	 * @return $this
	 */
	public function confirm_handler( string $handler ) {
		$this->confirm_handler = $handler;
		return $this;
	}

	/**
	 * Set mutation state variable name.
	 *
	 * @since 4.0.0
	 *
	 * @param string $state Mutation state variable name.
	 *
	 * @return $this
	 */
	public function mutation_state( string $state ) {
		$this->mutation_state = $state;
		return $this;
	}

	/**
	 * Set cancel button text.
	 *
	 * @since 4.0.0
	 *
	 * @param string $text Cancel button text.
	 *
	 * @return $this
	 */
	public function cancel_text( string $text ) {
		$this->cancel_text = $text;
		return $this;
	}

	/**
	 * Set confirm button text.
	 *
	 * @since 4.0.0
	 *
	 * @param string $text Confirm button text.
	 *
	 * @return $this
	 */
	public function confirm_text( string $text ) {
		$this->confirm_text = $text;
		return $this;
	}

	/**
	 * Set custom width for modal content.
	 *
	 * @since 4.0.0
	 *
	 * @param string $width Width value (e.g., '426px', '50%').
	 *
	 * @return $this
	 */
	public function width( string $width ) {
		$this->width = $width;
		return $this;
	}

	/**
	 * Set custom confirm button HTML.
	 *
	 * @since 4.0.0
	 *
	 * @param string $html Button HTML.
	 *
	 * @return $this
	 */
	public function confirm_button( string $html ) {
		$this->confirm_btn = $html;
		return $this;
	}

	/**
	 * Set custom cancel button HTML.
	 *
	 * @since 4.0.0
	 *
	 * @param string $html Button HTML.
	 *
	 * @return $this
	 */
	public function cancel_button( string $html ) {
		$this->cancel_btn = $html;
		return $this;
	}

	/**
	 * Get the modal HTML.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML output.
	 */
	public function get(): string {
		if ( empty( $this->id ) ) {
			return '';
		}

		// Set default button texts if not provided.
		if ( empty( $this->cancel_text ) ) {
			$this->cancel_text = __( 'Cancel', 'tutor' );
		}
		if ( empty( $this->confirm_text ) ) {
			$this->confirm_text = __( 'Yes', 'tutor' );
		}

		// Build Alpine.js x-data.
		$alpine_data = array( 'id' => $this->id );
		$alpine_json = wp_json_encode( $alpine_data );

		// Build style attribute for custom width.
		$style_attr = $this->width
			? sprintf( ' style="max-width: %s;"', esc_attr( $this->width ) )
			: '';

		// Build icon HTML.
		ob_start();
		tutor_utils()->render_svg_icon( $this->icon, $this->icon_width, $this->icon_height );
		$icon_html = ob_get_clean();

		// Prepare confirm button HTML.
		$confirm_html = $this->confirm_btn;
		if ( empty( $confirm_html ) ) {
			$confirm_html = sprintf(
				'<button 
					class="tutor-btn tutor-btn-secondary tutor-btn-small"
					:class="%s?.isPending ? \'tutor-btn-loading\' : \'\'"
					@click="%s"
					:disabled="%s?.isPending"
				>
					%s
				</button>',
				esc_js( $this->mutation_state ),
				$this->confirm_handler,
				esc_js( $this->mutation_state ),
				esc_html( $this->confirm_text )
			);
		}

		// Prepare cancel button HTML.
		$cancel_html = $this->cancel_btn;
		if ( empty( $cancel_html ) ) {
			$cancel_html = sprintf(
				'<button 
					class="tutor-btn tutor-btn-primary tutor-btn-small" 
					@click="TutorCore.modal.closeModal(\'%s\')"
				>
					%s
				</button>',
				esc_js( $this->id ),
				esc_html( $this->cancel_text )
			);
		}

		$this->component_string = sprintf(
			'<div x-data="tutorModal(%s)" x-cloak>
				<template x-teleport="body">
					<div x-bind="getModalBindings()">
						<div x-bind="getBackdropBindings()"></div>
						<div x-bind="getModalContentBindings()"%s>
							<button x-data="tutorIcon({ name: \'cross\', width: 16, height: 16})" x-bind="getCloseButtonBindings()"></button>

							<div class="tutor-p-7 tutor-pt-10 tutor-flex tutor-flex-column tutor-items-center">
								%s
								<h5 class="tutor-h5 tutor-font-medium tutor-mt-7">
									%s
								</h5>
								<p class="tutor-p3 tutor-text-secondary tutor-mt-2 tutor-text-center">
									%s
								</p>
							</div>

							<div class="tutor-grid tutor-grid-cols-2 tutor-gap-6 tutor-px-7 tutor-pb-6">
								%s
								%s
							</div>
						</div>
					</div>
				</template>
			</div>',
			esc_attr( $alpine_json ),
			$style_attr,
			$icon_html,
			esc_html( $this->title ?? __( 'Are you sure?', 'tutor' ) ),
			esc_html( $this->message ?? __( 'Are you sure you want to perform this action? Please confirm your choice.', 'tutor' ) ),
			$confirm_html,
			$cancel_html
		);

		return $this->component_string;
	}
}

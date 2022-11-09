<?php
/**
 * Backend Page Trait
 * Use this trait in existing classes to reuse frequently used methods
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Backend Page Trait
 *
 * @since 2.0.0
 */
trait Backend_Page_Trait {
	/**
	 * Bulk action abstract property.
	 *
	 * @var $bulk_action
	 */
	public $bulk_action = true;

	/**
	 * Bulk action default option
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function bulk_action_default(): array {
		return array(
			'value'  => '',
			'option' => __( 'Bulk Action', 'tutor' ),
		);
	}

	/**
	 * Bulk action complete
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function bulk_action_complete(): array {
		return array(
			'value'  => 'complete',
			'option' => __( 'Complete', 'tutor' ),
		);
	}

	/**
	 * Bulk action published
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function bulk_action_publish(): array {
		return array(
			'value'  => 'publish',
			'option' => __( 'Publish', 'tutor' ),
		);
	}

	/**
	 * Bulk action draft
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function bulk_action_draft(): array {
		return array(
			'value'  => 'draft',
			'option' => __( 'Draft', 'tutor' ),
		);
	}

	/**
	 * Bulk action on hold
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function bulk_action_on_hold(): array {
		return array(
			'value'  => 'on-hold',
			'option' => __( 'On Hold', 'tutor' ),
		);
	}

	/**
	 * Bulk action pending
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function bulk_action_pending(): array {
		return array(
			'value'  => 'pending',
			'option' => __( 'Pending', 'tutor' ),
		);
	}

	/**
	 * Bulk action processing
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function bulk_action_processing(): array {
		return array(
			'value'  => 'processing',
			'option' => __( 'Processing', 'tutor' ),
		);
	}

	/**
	 * Bulk action delete
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function bulk_action_delete(): array {
		return array(
			'value'  => 'delete',
			'option' => __( 'Delete Permanently', 'tutor' ),
		);
	}

	/**
	 * Bulk action cancel
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function bulk_action_cancel(): array {
		return array(
			'value'  => 'cancel',
			'option' => __( 'Cancel', 'tutor' ),
		);
	}

	/**
	 * Bulk action approved
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function bulk_action_approved(): array {
		return array(
			'value'  => 'approved',
			'option' => __( 'Approve', 'tutor' ),
		);
	}

	/**
	 * Bulk action blocked
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function bulk_action_blocked(): array {
		return array(
			'value'  => 'blocked',
			'option' => __( 'Block', 'tutor' ),
		);
	}
	/**
	 * Bulk action trash
	 *
	 * @return array
	 * @since v2.0.0
	 */
	public function bulk_action_trash(): array {
		return array(
			'value'  => 'trash',
			'option' => __( 'Trash', 'tutor' ),
		);
	}

	/**
	 * Bulk action trash
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function bulk_action_reject(): array {
		return array(
			'value'  => 'reject',
			'option' => __( 'Reject', 'tutor' ),
		);
	}
}

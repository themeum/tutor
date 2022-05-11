<?php
/**
 * Backend Page Trait to use with existing class
 * contains backend page related reuseable code snippet
 *
 * @package Enrolment List
 * @since v2.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Trait for backend pages
 * Reuse able methods implemented can be override from child class
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
	 * @return array
	 * @since v2.0.0
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
	 * @return array
	 * @since v2.0.0
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
	 * @return array
	 * @since v2.0.0
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
	 * @return array
	 * @since v2.0.0
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
	 * @return array
	 * @since v2.0.0
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
	 * @return array
	 * @since v2.0.0
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
	 * @return array
	 * @since v2.0.0
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
	 * @return array
	 * @since v2.0.0
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
	 * @return array
	 * @since v2.0.0
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
	 * @return array
	 * @since v2.0.0
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
	 * @return array
	 * @since v2.0.0
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
	 * @return array
	 * @since v2.0.0
	 */
	public function bulk_action_reject(): array {
		return array(
			'value'  => 'reject',
			'option' => __( 'Reject', 'tutor' ),
		);
	}
}

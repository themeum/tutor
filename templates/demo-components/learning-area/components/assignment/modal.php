<?php
/**
 * Assignment Confirm Submission Modal
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$modal_id = 'assignment-confirm-submission-modal';

?>

<div x-data="tutorModal({ id: 'assignment-confirm-submission-modal' })" x-cloak>
	<template x-teleport="body">
		<div x-bind="getModalBindings()">
			<div x-bind="getBackdropBindings()"></div>
			<div x-bind="getModalContentBindings()">
			<button x-data="tutorIcon({ name: 'cross', width: 16, height: 16})" x-bind="getCloseButtonBindings()"></button>
			<div class="tutor-modal-header">
				<div class="tutor-modal-title">
					<?php esc_html_e( 'Confirm Submission', 'tutor' ); ?>
				</div>
				<div class="tutor-modal-subtitle">
					<?php esc_html_e( "Are you sure you want to submit this assignment? You won't be able to make changes after submission.", 'tutor' ); ?>
				</div>
			</div>
			<div class="tutor-modal-body tutor-flex tutor-flex-column tutor-gap-4 tutor-mt-2">
				<div class="tutor-flex tutor-gap-4 tutor-justify-between tutor-items-center tutor-text-subdued">
					<?php esc_html_e( 'Written Response', 'tutor' ); ?>
					<span class="tutor-small tutor-font-medium tutor-flex tutor-items-center tutor-gap-3">
						<?php tutor_utils()->render_svg_icon( Icon::COMPLETED_CIRCLE, 16, 16 ); ?>
						<?php esc_html_e( 'Included', 'tutor' ); ?>
					</span>
				</div>
				<div class="tutor-flex tutor-gap-4 tutor-justify-between tutor-items-center tutor-text-subdued">
					<?php esc_html_e( 'File Attached', 'tutor' ); ?>
					<span class="tutor-small tutor-font-medium tutor-flex tutor-items-center">
						<?php
						printf(
							// translators: %d - number of files.
							esc_html__( '%d file(s)', 'tutor' ),
							0
						);
						?>
					</span>
				</div>
			</div>
			<div class="tutor-modal-footer">
				<button class="tutor-btn tutor-btn-ghost" @click="TutorCore.modal.closeModal('assignment-confirm-submission-modal')"><?php esc_html_e( 'Cancel', 'tutor' ); ?></button>
				<!-- @TODO: need to add functionality -->
				<button class="tutor-btn tutor-btn-primary" @click="window.location.href = window.location.href.replace('edit', 'attempts')">
					<?php esc_html_e( 'Confirm Submission', 'tutor' ); ?>
				</button>
			</div>
			</div>
		</div>
	</template>
</div>
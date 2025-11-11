<?php
/**
 * Modal
 *
 * @package   Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

?>
<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<h1 class="tutor-text-2xl tutor-font-bold tutor-mb-6">Modal</h1>
	<p class="tutor-text-gray-600 tutor-mb-4">
		Modals are used to display content that temporarily blocks interactions with the rest of the page.
		<br />
		Use <code>TutorCore.modal.showModal(:id)</code> to open a modal and <code>TutorCore.modal.closeModal(:id)</code> to close it.
	</p>
	<div class="tutor-my-8 tutor-flex tutor-gap-4">
		<button class="tutor-btn tutor-btn-primary" onclick="TutorCore.modal.showModal('full-modal')">Open Modal</button>

		<button class="tutor-btn tutor-btn-secondary" onclick="TutorCore.modal.showModal('headless-modal')">Open Modal (only closeable via API)</button>
	</div>

	<div x-data="tutorModal({ id: 'full-modal' })" x-cloak>
		<template x-teleport="body">
			<div x-bind="getModalBindings()">
				<div x-bind="getBackdropBindings()"></div>
				<div x-bind="getModalContentBindings()">
					<button x-data="tutorIcon({ name: 'cross', width: 16, height: 16})", x-bind="getCloseButtonBindings()"></button>
					<div class="tutor-modal-header">
						<div class="tutor-modal-title">Confirm Submission</div>
						<div class="tutor-modal-subtitle">Are you sure you want to submit this assignment? You won't be able to make changes after submission.</div>
					</div>
					<div class="tutor-modal-body">
						<p>
							Try tabbing through the modal to see the focus management in action. It should also manage the overflow scrolling for you.
							<br />
							Try closing with the close button or pressing the escape key or clicking outside the modal.
						</p>
					</div>
					<div class="tutor-modal-footer">
						<button class="tutor-btn" @click="TutorCore.modal.closeModal('full-modal')">Close</button>
						<button class="tutor-btn tutor-btn-primary" @click="TutorCore.modal.closeModal('full-modal')">Submit</button>
					</div>
				</div>
			</div>
		</template>
	</div>

	<div x-data="tutorModal({ id: 'headless-modal', isCloseable: false })" x-cloak>
		<template x-teleport="body">
			<div x-bind="getModalBindings()">
				<div x-bind="getBackdropBindings()"></div>
				<div x-bind="getModalContentBindings()" style="width: 354px;">
					<div class="tutor-modal-body tutor-flex tutor-flex-column tutor-gap-6 tutor-text-center">
						<h3>Fantastic, @blind!</h3>
						<p>You've dedicated over
						<p>That's 1,350,000 minutes, and 81,000,000 seconds! Incredible!</p>

						<button class="tutor-btn tutor-btn-primary tutor-radius-full" @click="TutorCore.modal.closeModal('headless-modal')">See you next time! 👋</button>
					</div>
				</div>
			</div>
		</template>
	</div>
</section>

<?php
/**
 * Tutor learning area certificate.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

?>
<div class="tutor-learning-area-certificate">
	<div class="tutor-certificate-header">
		<div class="tutor-flex tutor-items-center tutor-gap-3 tutor-sm-hidden">
			<button type="button" class="tutor-btn tutor-btn-secondary tutor-btn-x-small tutor-btn-icon tutor-icon-idle">
				<?php tutor_utils()->render_svg_icon( Icon::SHARE ); ?>
			</button>
			<button type="button" class="tutor-btn tutor-btn-secondary tutor-btn-x-small tutor-btn-icon tutor-icon-idle">
				<?php tutor_utils()->render_svg_icon( Icon::PRINTER ); ?>
			</button>
		</div>
		<div class="tutor-flex tutor-items-center tutor-sm-items-stretch tutor-gap-3 tutor-sm-gap-6 tutor-sm-w-full">
			<button 
				type="button" 
				class="tutor-btn tutor-btn-secondary tutor-btn-x-small tutor-sm-hidden"
				onclick="TutorCore.modal.showModal('certificate-modal')">
				<?php esc_html_e( 'View Certificate', 'tutor' ); ?>
			</button>
			<button type="button" class="tutor-btn tutor-btn-primary tutor-btn-x-small tutor-gap-2 tutor-sm-text-small tutor-sm-w-full">
				<?php tutor_utils()->render_svg_icon( Icon::DOWNLOAD_2 ); ?>
				<?php esc_html_e( 'Download Certificate', 'tutor' ); ?>
			</button>
			<div x-data="tutorPopover({ placement: 'bottom-start' })" class="tutor-hidden tutor-sm-block">
				<button 
					type="button"
					x-ref="trigger"
					@click="toggle()"
					class="tutor-btn tutor-btn-secondary tutor-btn-large tutor-btn-icon tutor-icon-idle">
					<?php tutor_utils()->render_svg_icon( Icon::THREE_DOTS_VERTICAL, 20, 20 ); ?>
				</button>

				<div 
					x-ref="content"
					x-show="open"
					x-cloak
					@click.outside="handleClickOutside()"
					class="tutor-popover"
				>
					<div class="tutor-popover-menu">
						<!-- @TODO: Dropdown items design is not ready -->
						<button class="tutor-popover-menu-item">
							<?php tutor_utils()->render_svg_icon( Icon::CERTIFICATE_2 ); ?> View Certificate
						</button>
						<button class="tutor-popover-menu-item">
							<?php tutor_utils()->render_svg_icon( Icon::SHARE ); ?> Share
						</button>
						<button class="tutor-popover-menu-item">
							<?php tutor_utils()->render_svg_icon( Icon::PRINTER ); ?> Print
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="tutor-surface-l1 tutor-border tutor-rounded-2xl tutor-p-4">
		<img src="<?php echo esc_attr( tutor()->url . 'assets/images/certificate-demo.jpg' ); ?>" alt="<?php esc_attr_e( 'Certificate preview', 'tutor' ); ?>" class="tutor-w-full" />
	</div>
	<div class="tutor-certificate-info">
		<div class="tutor-certificate-info-item">
			<div class="tutor-flex tutor-flex-column tutor-gap-1">
				<div class="tutor-tiny tutor-text-secondary">Course</div>
				<div class="tutor-tiny tutor-font-medium">Logo Design From Scratch</div>
			</div>
			<div class="tutor-flex tutor-flex-column tutor-gap-1">
				<div class="tutor-tiny tutor-text-secondary">Graduate</div>
				<div class="tutor-tiny tutor-font-medium">Md. Jilon Ahmed</div>
			</div>
			<div class="tutor-flex tutor-flex-column tutor-gap-1">
				<div class="tutor-tiny tutor-text-secondary">Grade Achieved</div>
				<div class="tutor-tiny tutor-font-medium">85.53%</div>
			</div>
			<div class="tutor-flex tutor-flex-column tutor-gap-1">
				<div class="tutor-tiny tutor-text-secondary">Skills reinforced</div>
				<div class="tutor-flex tutor-gap-2 tutor-flex-wrap tutor-max-w-xs">
					<div class="tutor-badge">Logo basics</div>
					<div class="tutor-badge">Shape psychology</div>
					<div class="tutor-badge">Color theory</div>
					<div class="tutor-badge">Shape design</div>
				</div>
			</div>
		</div>
		<div class="tutor-certificate-info-item">
			<div>
				<div class="tutor-tiny tutor-text-secondary">Certificate ID</div>
				<div class="tutor-tiny tutor-font-medium">007497kDGG</div>
			</div>
			<div>
				<div class="tutor-tiny tutor-text-secondary">Issued on</div>
				<div class="tutor-tiny tutor-font-medium">Aug 27, 2025</div>
			</div>
			<div>
				<div class="tutor-tiny tutor-text-secondary">Certifying organization</div>
				<div class="tutor-tiny tutor-font-medium">Logopogo.Design inc.</div>
			</div>
			<div>
				<div class="tutor-tiny tutor-text-secondary">Instructor</div>
				<div class="tutor-tiny tutor-font-medium">Md. Nahid Hossain Alif</div>
			</div>
		</div>
	</div>

	<div x-data="tutorModal({ id: 'certificate-modal' })" x-cloak>
		<template x-teleport="body">
			<div x-bind="getModalBindings()">
				<div x-bind="getBackdropBindings()"></div>
				<div x-bind="getModalContentBindings()" class="tutor-certificate-locked-modal">
					<div class="tutor-flex tutor-justify-center tutor-mb-6">
						<span class="tutor-certificate-locked-badge">
							<?php tutor_utils()->render_svg_icon( Icon::LOCK_FILL, 20, 20 ); ?>
							<?php esc_html_e( 'Certificate Locked', 'tutor' ); ?>
						</span>
					</div>
					<div class="tutor-certificate-modal-preview">
						<img src="<?php echo esc_attr( tutor()->url . 'assets/images/certificate-preview.png' ); ?>" alt="<?php esc_attr_e( 'Certificate preview', 'tutor' ); ?>" class="tutor-w-full" />
						<div class="tutor-certificate-preview-effect">
							<div class="tutor-certificate-preview-lock">
								<?php tutor_utils()->render_svg_icon( Icon::LOCK_FILL, 32, 32 ); ?>
							</div>
						</div>
					</div>
					<h4 class="tutor-h4 tutor-font-medium tutor-sm-text-medium tutor-mb-6 tutor-sm-mb-4">Complete at-least 85% to unlock the certificate</h4>
					<div class="tutor-flex tutor-flex-column tutor-gap-4 tutor-mb-8 tutor-sm-mb-6">
						<div class="tutor-flex tutor-items-center tutor-justify-between tutor-medium tutor-sm-text-small tutor-text-secondary">
							<div><span class="tutor-font-semibold tutor-text-primary">7%</span> Completed</div>
							<div>Required <span class="tutor-font-semibold tutor-text-primary">100%</span></div>
						</div>
						<div class="tutor-progress-bar" data-tutor-animated>
							<div class="tutor-progress-bar-fill" style="--tutor-progress-width: 75%;"></div>
						</div>
					</div>
					<button type="button" class="tutor-btn tutor-btn-primary tutor-btn-block" onclick="TutorCore.modal.closeModal('certificate-modal')">
						<?php esc_html_e( 'Okay, I Understand', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</template>
	</div>
</div>

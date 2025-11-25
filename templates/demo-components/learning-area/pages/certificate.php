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
<div class="tutor-learning-area-certificate tutor-mt-7">
	<div class="tutor-certificate-header">
		<div class="tutor-flex tutor-items-center tutor-gap-3">
			<button type="button" class="tutor-btn tutor-btn-secondary tutor-btn-x-small tutor-btn-icon tutor-icon-idle">
				<?php tutor_utils()->render_svg_icon( Icon::SHARE ); ?>
			</button>
			<button type="button" class="tutor-btn tutor-btn-secondary tutor-btn-x-small tutor-btn-icon tutor-icon-idle">
				<?php tutor_utils()->render_svg_icon( Icon::PRINTER ); ?>
			</button>
		</div>
		<div class="tutor-flex tutor-items-center tutor-gap-3">
			<button type="button" class="tutor-btn tutor-btn-secondary tutor-btn-x-small">
				<?php esc_html_e( 'View Certificate', 'tutor' ); ?>
			</button>
			<button type="button" class="tutor-btn tutor-btn-primary tutor-btn-x-small tutor-gap-2">
				<?php tutor_utils()->render_svg_icon( Icon::DOWNLOAD_2 ); ?>
				<?php esc_html_e( 'Download Certificate', 'tutor' ); ?>
			</button>
		</div>
	</div>
	<div class="tutor-surface-l1 tutor-border tutor-rounded-2xl tutor-p-4">
		<img src="http://localhost:10058/wp-content/uploads/tutor-certificates/FzweUAvhD3-cbe8db57c0c70757.jpg" alt="<?php esc_attr_e( 'Certificate preview', 'tutor' ); ?>" class="tutor-w-full" />
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
</div>

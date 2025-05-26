<?php
/**
 * Templates importing view
 *
 * @package Tutor
 * @subpackage TemplateImport
 * @author Tutor <support@themeum.com>
 * @link https://tutor.com
 * @since 3.6.0
 */

// todo
// remove demo word from class naming, tutor-theme-import-wrapper tutor-theme-import-* like this
?>
<div class="tutor-templates-demo-import-wrapper">
	<div class="tutor-templates-demo-import">
		<div class="tutor-demo-importer-top tutor-d-flex tutor-flex-wrap tutor-justify-between tutor-gap-4 tutor-pb-20 tutor-my-24">
			<div class="tutor-demo-importer-top-left tutor-d-flex tutor-gap-1">
				<div class="top-left-icon">
					<svg width="32" height="32" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.667 11.666v10.667a4 4 0 0 0 4 4h5.666M3.667 11.667v-2a4 4 0 0 1 4-4h16.666a4 4 0 0 1 4 4v2m-24.666 0h9.666m0 14.666h11a4 4 0 0 0 4-4V11.667m-15 14.666V11.667m15 0h-15" stroke="#4B505C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</div>
				<div>
					<div class="top-left-heading tutor-fs-5 tutor-fw-medium">
						<?php esc_html_e( 'Templates', 'tutor' ); ?>
					</div>
					<div class="tutor-top-left-text tutor-fs-6 tutor-fw-regular"><?php esc_html_e( 'Leverage the collection of magnificent Tutor starter templates to make a jump start.', 'tutor' ); ?></div>
				</div>
			</div>
		</div>
		<?php
			require_once tutor()->path . 'views/template-import/templates-list.php';
		?>
	</div>

	<div class="tutor-template-preview-modal">
		<div class="tutor-template-preview-modal-overlay"></div>
		<div class="tutor-template-preview-frame">
			<div class="tutor-template-preview-frame-header">
				<div class="tutor- tutor-d-flex tutor-gap-1 tutor-align-center tutor-template-preview-modal-back-link">
					<i class="tutor-icon-previous"></i>
					<div class="">Themes</div>
				</div>
				<ul class="tutor-template-preview-device-switcher">
					<li class="active" data-device="desktop" data-width="100%" data-height="100%">
						<i class="tutor-icon-laptop"></i>
					</li>
					<li class="" data-device="tablet" data-width="768px" data-height="1024px">
						<i class="tutor-icon-tablet"></i>
					</li>
					<li class="" data-device="phone" data-width="375px" data-height="667px">
						<i class="tutor-icon-mobile"></i>
					</li>
				</ul>
			</div>
			<div class="tutor-template-preview-iframe-wrapper">
				<iframe id="tutor-template-preview-iframe" src="" frameborder="0"></iframe>
				<div class="tutor-template-loading-indicator" style="display: none;"><?php esc_html_e( 'Loading...', 'tutor' ); ?></div>
				<div class="tutor-template-preview-import-area tutor-flex-column tutor-justify-center tutor-gap-1" style="display: none;">
					<div class="tutor-preview-template-name">BeatLab Academy</div>	
					<p class="tutor-droip-color-presets-heading" style="display: none;">
						<?php esc_html_e( 'Choose your color palette and continue with your design', 'tutor' ); ?>
					</p>
					<div id="droip-color-presets">
						<div id="droip-color-modes">
						</div>
					</div>
					<div class="tutor-template-demo-import-btn-wrapper">
						<?php do_action( 'template_import_btn' ); ?>
					</div>
					<!-- <i class="tutor-icon-times"></i> -->
				</div>
			</div>
		</div>
	</div>
</div>
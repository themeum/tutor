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

?>
<div class="tutor-template-import-wrapper">
	<div class="tutor-template-import-area">
		<div class="tutor-template-import-area-top tutor-d-flex tutor-flex-wrap tutor-justify-between tutor-gap-4 tutor-pb-20 tutor-my-24">
			<div class="tutor-template-import-area-left tutor-d-flex tutor-gap-1">
				<div class="tutor-template-area-top-left-icon">
					<svg width="32" height="32" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.667 11.666v10.667a4 4 0 0 0 4 4h5.666M3.667 11.667v-2a4 4 0 0 1 4-4h16.666a4 4 0 0 1 4 4v2m-24.666 0h9.666m0 14.666h11a4 4 0 0 0 4-4V11.667m-15 14.666V11.667m15 0h-15" stroke="#4B505C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</div>
				<div>
					<div class="tutor-template-top-left-heading tutor-fs-5 tutor-fw-medium">
						<?php esc_html_e( 'Themes', 'tutor' ); ?>
					</div>
					<div class="tutor-template-area-left-text tutor-fs-6 tutor-fw-regular"><?php esc_html_e( 'Leverage the collection of magnificent Tutor starter templates to make a jump start.', 'tutor' ); ?></div>
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
					<div class=""><?php esc_html_e( 'Themes', 'tutor' ); ?></div>
				</div>
				<ul class="tutor-template-preview-device-switcher">
					<li class="active" data-device="desktop" data-width="100%" data-height="100%">
						<svg width="20" height="16" viewBox="0 0 20 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M2.6573 12.9234H17.3392C18.4991 12.9234 19.0902 12.3547 19.0902 11.1724V2.55183C19.0902 1.3695 18.4991 0.800781 17.3392 0.800781H2.6573C1.48993 0.800781 0.90625 1.3695 0.90625 2.55183V11.1724C0.90625 12.3547 1.48993 12.9234 2.6573 12.9234ZM2.67975 11.9955C2.10355 11.9955 1.83416 11.7411 1.83416 11.1499V2.5668C1.83416 1.98312 2.10355 1.72869 2.67975 1.72869H17.3167C17.8929 1.72869 18.1623 1.98312 18.1623 2.5668V11.1499C18.1623 11.7411 17.8929 11.9955 17.3167 11.9955H2.67975ZM6.339 15.1983H13.6575C13.9119 15.1983 14.1215 14.9963 14.1215 14.7418C14.1215 14.4799 13.9119 14.2704 13.6575 14.2704H6.339C6.07709 14.2704 5.86756 14.4799 5.86756 14.7418C5.86756 14.9963 6.07709 15.1983 6.339 15.1983Z" fill="#9197A8"/>
						</svg>
						<svg width="20" height="16" viewBox="0 0 20 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M2.6573 12.9234H17.3392C18.4991 12.9234 19.0902 12.3547 19.0902 11.1724V2.55183C19.0902 1.3695 18.4991 0.800781 17.3392 0.800781H2.6573C1.48993 0.800781 0.90625 1.3695 0.90625 2.55183V11.1724C0.90625 12.3547 1.48993 12.9234 2.6573 12.9234ZM6.339 15.1983H13.6575C13.9119 15.1983 14.1215 14.9963 14.1215 14.7418C14.1215 14.4799 13.9119 14.2704 13.6575 14.2704H6.339C6.07709 14.2704 5.86756 14.4799 5.86756 14.7418C5.86756 14.9963 6.07709 15.1983 6.339 15.1983Z" fill="#446EF5"/>
						</svg>
					</li>
					<li class="" data-device="tablet" data-width="768px" data-height="1024px">
						<svg width="14" height="18" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M0.636719 15.5586C0.636719 16.8242 1.44922 17.6055 2.74609 17.6055H11.2461C12.5508 17.6055 13.3633 16.8242 13.3633 15.5586V2.44922C13.3633 1.18359 12.5508 0.394531 11.2461 0.394531H2.74609C1.44922 0.394531 0.636719 1.18359 0.636719 2.44922V15.5586ZM1.60547 15.4102V2.58984C1.60547 1.80078 2.05078 1.36328 2.86328 1.36328H11.1367C11.9492 1.36328 12.3945 1.80078 12.3945 2.58984V15.4102C12.3945 16.1992 11.9492 16.6445 11.1367 16.6445H2.86328C2.05078 16.6445 1.60547 16.1992 1.60547 15.4102ZM4.82422 15.243H9.17578C9.35547 15.243 9.48047 15.118 9.48047 14.9383C9.48047 14.7508 9.35547 14.6258 9.17578 14.6258H4.82422C4.65234 14.6258 4.51953 14.7508 4.51953 14.9383C4.51953 15.118 4.65234 15.243 4.82422 15.243Z" fill="#9197A8"/>
						</svg>
						<svg width="14" height="18" viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M11.2461 0.394531C12.5508 0.394531 13.3633 1.18359 13.3633 2.44922V15.5586C13.3633 16.8242 12.5508 17.6055 11.2461 17.6055H2.74609C1.44922 17.6055 0.636719 16.8242 0.636719 15.5586V2.44922C0.636719 1.18359 1.44922 0.394531 2.74609 0.394531H11.2461ZM4.82422 15.0498C4.6524 15.0498 4.51962 15.1749 4.51953 15.3623C4.51953 15.542 4.65234 15.667 4.82422 15.667H9.17578C9.35547 15.667 9.48047 15.542 9.48047 15.3623C9.48038 15.1749 9.35541 15.0498 9.17578 15.0498H4.82422Z" fill="#446EF5"/>
						</svg>
					</li>
					<li class="" data-device="phone" data-width="375px" data-height="667px">
						<svg width="10" height="18" viewBox="0 0 10 18" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M3.78438 14.7273H6.22344C6.40313 14.7273 6.52813 14.6023 6.52813 14.4227C6.52813 14.2352 6.40313 14.1102 6.22344 14.1102H3.78438C3.60469 14.1102 3.47969 14.2352 3.47969 14.4227C3.47969 14.6023 3.60469 14.7273 3.78438 14.7273Z" fill="#9197A8"/>
							<path fill-rule="evenodd" clip-rule="evenodd" d="M2.27344 17.043H7.72656C8.98438 17.043 9.75781 16.293 9.75781 15.0742V2.93359C9.75781 1.71484 8.98438 0.957031 7.72656 0.957031H2.27344C1.01563 0.957031 0.242187 1.71484 0.242188 2.93359V15.0742C0.242188 16.293 1.01563 17.043 2.27344 17.043ZM2.38281 16.082C1.60156 16.082 1.21094 15.6992 1.21094 14.9336V3.06641C1.21094 2.30859 1.60156 1.92578 2.38281 1.92578H2.67969C2.99688 1.92578 3.15625 1.92578 3.15625 1.92578H6.85156C7.14063 1.92578 7.24688 1.92578 7.32813 1.92578H7.61719C8.39844 1.92578 8.79688 2.30859 8.79688 3.06641V14.9336C8.79688 15.6992 8.39844 16.082 7.61719 16.082H2.38281Z" fill="#9197A8"/>
						</svg>
						<svg width="10" height="16" viewBox="0 0 10 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M7.60254 0C8.53749 0 9.12012 0.733579 9.12012 1.91016V14.0967C9.12012 15.2733 8.53749 16 7.60254 16H1.51172C0.582363 16 0 15.2733 0 14.0967V1.91016C0 0.733579 0.582363 0 1.51172 0H7.60254ZM3.06738 13C2.9635 13 2.8829 13.1012 2.88281 13.2529C2.88281 13.3985 2.96345 13.5 3.06738 13.5H5.69824C5.8069 13.5 5.88281 13.3985 5.88281 13.2529C5.88273 13.1012 5.80685 13 5.69824 13H3.06738Z" fill="#446EF5"/>
						</svg>
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
					<div class="tutor-template-import-btn-wrapper">
						<?php do_action( 'template_import_btn' ); ?>
					</div>
					<!-- <i class="tutor-icon-times"></i> -->
				</div>
			</div>
		</div>
	</div>
</div>
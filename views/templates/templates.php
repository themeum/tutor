<?php
/**
 * Templates importing view
 *
 * @package Tutor
 * @author Tutor <support@themeum.com>
 * @link https://tutor.com
 * @since 3.3.3
 */

?>
<div class="tutor-templates-demo-import">
	<div class="tutorowl-demo-importer-top tutor-d-flex tutor-flex-wrap tutor-justify-between tutor-gap-4 tutor-pb-20 tutor-my-24">
		<div class="tutorowl-demo-importer-top-left tutor-d-flex tutor-gap-1">
			<div class="tutorowl-top-left-icon">
				<svg width="32" height="32" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.667 11.666v10.667a4 4 0 0 0 4 4h5.666M3.667 11.667v-2a4 4 0 0 1 4-4h16.666a4 4 0 0 1 4 4v2m-24.666 0h9.666m0 14.666h11a4 4 0 0 0 4-4V11.667m-15 14.666V11.667m15 0h-15" stroke="#4B505C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</div>
			<div>
				<div class="tutorowl-top-left-heading tutor-fs-5 tutor-fw-medium">
					<?php esc_html_e( 'Templates', 'tutor' ); ?>
				</div>
				<div class="tutorowl-top-left-text tutor-fs-6 tutor-fw-regular"><?php esc_html_e( 'Leverage the collection of magnificent Tutor starter templates to make a jumpstart.', 'tutor' ); ?></div>
			</div>
		</div>
		<!-- <div class="tutorowl-demo-importer-top-right">
			<div class="tutorowl-template-search-wrapper">
				<input type="text" placeholder="Search...">
				<svg class="tutorowl-template-search-icon" width="16" height="16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6.858 1.524a5.334 5.334 0 1 0 0 10.667 5.334 5.334 0 0 0 0-10.667ZM0 6.858a6.858 6.858 0 1 1 12.216 4.28l3.56 3.561a.762.762 0 1 1-1.077 1.078l-3.561-3.561A6.858 6.858 0 0 1 0 6.858Z" fill="#9197A8"/></svg>
			</div>
		</div> -->
	</div>
	<ul class="tutorowl-demo-importer-list">
		<?php
			require_once tutor()->path . 'views/templates/templates-list.php';
		?>
	</ul>
</div>

<div id="tutorowl-import-modal-wrapper">
	<div class="tutorowl-modal-wrapper-overlay"></div>
	<div class="tutorowl-modal-content">
		<div class="tutorowl-modal-img">
			<img class="" alt="template image">
			<div class="tutorowl-circle">
				<div class="tutorowl-checkmark"></div>
			</div>
		</div>
		<div class="tutorowl-modal-right">
			<div class="tutorowl-modal-head">
				<h5 class="tutor-fs-5 tutor-fw-medium"></h5>
				<div class="tutorowl-modal-head-subtitle tutor-fw-regular">
					<?php esc_html_e( 'The following items will be installed during the Edumax import process:', 'tutor' ); ?>
				</div>
			</div>
			<div class="tutorowl-import-item-wrapper">
				<div class="tutorowl-installation-progress-wrapper tutor-mt-8">
					<div class="tutor-d-flex tutor-justify-between">
						<div class="tutorowl-import-percentage-text"> <?php esc_html_e( 'Progress', 'tutor' ); ?> </div>
						<div class="tutorowl-import-percentage-number">0%</div>
					</div>
					<div class="tutorowl-progress">
						<div class="tutorowl-progress-status"></div>
					</div>
				</div>
				<div class="tutorowl-import-item">
					<svg class="svg-circle" style="width: 15px; height: 15px;">
						<circle class="circle-full" cx="8" cy="8" r="8" fill="#22A848"></circle>
						<path class="check-mark"
							d="M6.138 8.9714L3.9427 6.776 3 7.7187l3.138 3.138L12 4.9427l-.9427-.9426L6.138 8.9714z"
							fill="#fff"></path>
					</svg>
					<svg class="svg-spinner-inner" viewBox="0 0 50 50">
						<circle class="path" cx="25" cy="25" r="20" fill="none"></circle>
					</svg>
					<svg class="svg-spinner" viewBox="0 0 50 50">
						<circle class="path" cx="25" cy="25" r="20" fill="none"></circle>
					</svg>
					<div class="tutorowl-import-item-title"><?php esc_html_e( 'Tutor Owl', 'tutor' ); ?></div>
				</div>
				<div class="tutorowl-import-item">
					<svg class="svg-circle" style="width: 15px; height: 15px;">
						<circle class="circle-full" cx="8" cy="8" r="8" fill="#22A848"></circle>
						<path class="check-mark"
							d="M6.138 8.9714L3.9427 6.776 3 7.7187l3.138 3.138L12 4.9427l-.9427-.9426L6.138 8.9714z"
							fill="#fff"></path>
					</svg>
					<svg class="svg-spinner-inner" viewBox="0 0 50 50">
						<circle class="path" cx="25" cy="25" r="20" fill="none"></circle>
					</svg>
					<svg class="svg-spinner" viewBox="0 0 50 50">
						<circle class="path" cx="25" cy="25" r="20" fill="none"></circle>
					</svg>
					<div class="tutorowl-import-item-title"><?php esc_html_e( 'Droip', 'tutor' ); ?></div>
				</div>
				<div class="tutorowl-import-item">
					<svg class="svg-circle" style="width: 15px; height: 15px;">
						<circle class="circle-full" cx="8" cy="8" r="8" fill="#22A848"></circle>
						<path class="check-mark"
							d="M6.138 8.9714L3.9427 6.776 3 7.7187l3.138 3.138L12 4.9427l-.9427-.9426L6.138 8.9714z"
							fill="#fff"></path>
					</svg>
					<svg class="svg-spinner-inner" viewBox="0 0 50 50">
						<circle class="path" cx="25" cy="25" r="20" fill="none"></circle>
					</svg>
					<svg class="svg-spinner" viewBox="0 0 50 50">
						<circle class="path" cx="25" cy="25" r="20" fill="none"></circle>
					</svg>
					<div class="tutorowl-import-item-title tutorowl-import-item-content-title"><?php esc_html_e( 'Contents', 'tutor' ); ?></div>
				</div>
				<div id="tutorowl-content-details"></div>
			</div>
			<div class="tutorowl-modal-footer tutor-align-center tutor-justify-end tutor-gap-1 tutor-mt-8">
				<button id="tutorowl-import-cancel-btn" class="tutor-btn tutor-btn-sm tutor-fs-6 tutor-color-secondary">
					<?php esc_html_e( 'Cancel', 'tutor' ); ?>
				</button>
				<button data-template="<?php echo esc_attr( $key ); ?>" class="tutor-btn tutor-btn-primary tutor-btn-sm tutor-template-import-now-btn">
					<i class="tutor-icon-import tutor-mr-8"></i>
					<?php esc_html_e( 'Import', 'tutor' ); ?>
				</button>
			</div>

			<div class="tutorowl-success-block-wrapper">
				<div class="tutorowl-success-heading tutor-ml-4">
					<h3 class="tutorowl-imported-template-name tutor-fs-5 tutor-fw-medium"></h3>
					<div class="tutor-fs-7 tutor-color-subdued d-block tutor-mt-8"><?php esc_html_e( 'Bingo! Your site is ready. Explore it now and see how everything looks!', 'tutor' ); ?></div>
				</div>
				<div>
					<button id="tutorowl-visit-later-btn" class="tutor-btn tutor-btn-sm tutor-fs-6 tutor-color-secondary">
						<?php esc_html_e( 'Later', 'tutor' ); ?>
					</button>
					<a href="<?php echo esc_url( home_url() ); ?>" class="tutor-btn tutor-btn-primary tutor-btn-sm view-site-now"
						target="_blank">
						<?php esc_html_e( 'View Site', 'tutor' ); ?>
					</a>
				</div>
			</div>
		</div>
		<div class="tutorowl-import-modal-close">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6.68576 7.5991C6.43775 7.34767 6.43775 6.94001 6.68576 6.68858C6.93377 6.43714 7.33588 6.43714 7.58389 6.68858L11.9998 11.1654L16.4156 6.6886C16.6636 6.43716 17.0657 6.43716 17.3137 6.68859C17.5617 6.94003 17.5617 7.34768 17.3137 7.59912L12.8979 12.0759L17.1639 16.4008C17.4119 16.6523 17.4119 17.0599 17.1639 17.3114C16.9159 17.5628 16.5138 17.5628 16.2657 17.3114L11.9998 12.9865L7.73374 17.3114C7.48573 17.5628 7.08363 17.5628 6.83562 17.3114C6.58761 17.0599 6.58761 16.6523 6.83562 16.4009L11.1016 12.0759L6.68576 7.5991Z" fill="#9197A8"/>
			</svg>
		</div>
	</div>
</div>

<div class="template-live-preview-modal">
	<div class="template-live-preview-frame">
		<div class="template-preview-frame-header">
			<h3 class="preview-modal-template-name"></h3>
			<ul class="template-preview-device-switcher">
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
			<span class="live-preview-close-modal"><i class="tutor-icon-times"></i></span>
		</div>
		<div class="template-preview-iframe-wrapper">
			<iframe id="template-preview-iframe" src="" frameborder="0"></iframe>
			<div class="loading-indicator" style="display: none;">Loading...</div>
			<style>
				.loading-indicator {
					font-size: 40px;
					position: absolute;
					top: 50%;
					left: 50%;
					margin: 0 auto;
					transform: translate(-40%, -50%);
				}
			</style>
		</div>
	</div>
</div>
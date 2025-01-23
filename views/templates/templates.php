<?php
/**
 * Templates view
 *
 * @package Tutor
 * @author Tutor <support@themeum.com>
 * @link https://tutor.com
 * @since 3.0.2
 */

use TUTOR\TemplateImporter;

$template_list = ( new TemplateImporter() )::get_template_list();

?>

<div class="tutor-templates-demo-import">
	<div class="tutorowl-demo-importer-wrapper">
		<div class="tutorowl-demo-importer-top tutor-d-flex tutor-justify-between tutor-pr-24 tutor-my-24">
			<div class="tutorowl-demo-importer-top-left tutor-d-flex tutor-gap-1">
				<div class="tutorowl-top-left-icon">
					<svg width="32" height="32" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.667 11.666v10.667a4 4 0 0 0 4 4h5.666M3.667 11.667v-2a4 4 0 0 1 4-4h16.666a4 4 0 0 1 4 4v2m-24.666 0h9.666m0 14.666h11a4 4 0 0 0 4-4V11.667m-15 14.666V11.667m15 0h-15" stroke="#4B505C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</div>
				<div>
					<div class="tutorowl-top-left-heading">
						<?php esc_html_e( 'Templates', 'tutor' ); ?>
					</div>
					<div class="tutorowl-top-left-text"><?php esc_html_e( 'Leverage the collection of magnificent Tutor starter themes to make a jumpstart.', 'tutor' ); ?></div>
				</div>
			</div>
			<div class="tutorowl-demo-importer-top-right">
				<div class="tutorowl-template-search-wrapper">
					<input type="text" placeholder="Search...">
					<svg class="tutorowl-template-search-icon" width="16" height="16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6.858 1.524a5.334 5.334 0 1 0 0 10.667 5.334 5.334 0 0 0 0-10.667ZM0 6.858a6.858 6.858 0 1 1 12.216 4.28l3.56 3.561a.762.762 0 1 1-1.077 1.078l-3.561-3.561A6.858 6.858 0 0 1 0 6.858Z" fill="#9197A8"/></svg>
				</div>
			</div>
		</div>
		<ul class="tutorowl-demo-importer-list">
			<?php
			$i = 0;
			if ( ! empty( $template_list ) ) {
				foreach ( $template_list as $key => $template ) {
					$template = (object) $template;
					?>
					<li class="tutorowl-single-template">
						<div class="tutorowl-single-template-inner">
							<div class="tutorowl-template-preview-img">
								<img src="<?php echo esc_url( $template->preview_image ); ?>" loading="lazy" alt="icon">
							</div>
						</div>
						<div class="tutorowl-single-template-footer">
							<div class="tutorowl-template-name">
								<span><?php echo esc_html( $template->name ); ?></span>
								<span class="tutorowl-template-badge"> <?php esc_html_e( 'Pro', 'tutor' ); ?> </span>
							</div>
							<div class="tutor-d-flex tutor-align-center">
								<a class="tutor-btn tutor-fs-6 tutor-color-secondary" href="<?php echo esc_url( '#', 'tutor' ); ?>">
									<?php esc_html_e( 'Preview', 'tutor' ); ?>
								</a>
								<button data-template="<?php echo esc_attr( $key ); ?>" class="tutor-btn tutor-btn-primary tutor-btn-sm tutor-template-import-btn">
									<i class="tutor-icon-import tutor-mr-8"></i>	
									<?php esc_html_e( 'Import', 'tutor' ); ?>
								</button>
							</div>
						</div>
					</li>
					<?php
				}
			} else {
				?>
					<h3 style="text-align: center; margin-top: 30px;">
						<?php esc_html_e( 'No template available.', 'tutor' ); ?>
					</h3>
			<?php } ?>
		</ul>
	</div>
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
			<!-- tutorowl-template-importing
			tutorowl-template-imported -->
			<div class="tutorowl-import-item-wrapper">
				<div class="tutorowl-installation-progress-wrapper">
					<div class="tutor-d-flex tutor-justify-between">
						<div class="percentage-text"> <?php esc_html_e( 'Progress', 'tutor' ); ?> </div>
						<div class="percentage-number">0%</div>
					</div>
					<div class="tutorowl-progress">
						<div class="tutorowl-progress-status"></div>
					</div>
				</div>
				<div class="tutorowl-import-item">
					<svg class="svg-circle" style="width: 15px; height: 15px;">
						<circle class="circle-full" cx="8" cy="8" r="8" fill="#5FAC23"></circle>
						<path class="check-mark"
							d="M6.138 8.9714L3.9427 6.776 3 7.7187l3.138 3.138L12 4.9427l-.9427-.9426L6.138 8.9714z"
							fill="#fff"></path>
					</svg>
					<svg class="svg-spinner" viewBox="0 0 50 50">
						<circle class="path" cx="25" cy="25" r="20" fill="none"></circle>
					</svg>
					<div class="title"><?php esc_html_e( 'Tutor Owl', 'tutor' ); ?></div>
				</div>
				<div class="tutorowl-import-item">
					<svg class="svg-circle" style="width: 15px; height: 15px;">
						<circle class="circle-full" cx="8" cy="8" r="8" fill="#5FAC23"></circle>
						<path class="check-mark"
							d="M6.138 8.9714L3.9427 6.776 3 7.7187l3.138 3.138L12 4.9427l-.9427-.9426L6.138 8.9714z"
							fill="#fff"></path>
					</svg>
					<svg class="svg-spinner" viewBox="0 0 50 50">
						<circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
					</svg>
					<div class="title"><?php esc_html_e( 'Droip', 'tutor' ); ?></div>
				</div>
				<div class="tutorowl-import-item">
					<svg class="svg-circle" style="width: 15px; height: 15px;">
						<circle class="circle-full" cx="8" cy="8" r="8" fill="#5FAC23"></circle>
						<path class="check-mark"
							d="M6.138 8.9714L3.9427 6.776 3 7.7187l3.138 3.138L12 4.9427l-.9427-.9426L6.138 8.9714z"
							fill="#fff"></path>
					</svg>
					<svg class="svg-spinner" viewBox="0 0 50 50">
						<circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
					</svg>
					<div class="title"><?php esc_html_e( 'Contents', 'tutor' ); ?></div>
				</div>
				<div id="tutorowl-content-details"></div>
			</div>
			<!-- <div class="tutorowl-danger-block"></div> -->
			<div class="tutorowl-modal-footer">
				<button id="tutorowl-import-cancel-btn" class="tutor-btn tutor-fs-6 tutor-color-secondary">
					<?php esc_html_e( 'Cancel', 'tutor' ); ?>
				</button>
				<button data-template="<?php echo esc_attr( $key ); ?>" class="tutor-btn tutor-btn-primary tutor-btn-sm tutor-template-import-now-btn">
					<i class="tutor-icon-import tutor-mr-8"></i>
					<?php esc_html_e( 'Import', 'tutor' ); ?>
				</button>
			</div>

			<div class="tutorowl-success-block-wrapper">
				<div class="tutorowl-success-heading">
					<h3 class="tutorowl-imported-template-name"></h3>
					<p><?php esc_html_e( 'Bingo! Your site is ready. Explore it now and see how everything looks!', 'tutor' ); ?></p>
				</div>
				<div>
					<a href="<?php echo esc_url( home_url() ); ?>" class="tutor-btn tutor-btn-primary tutor-btn-sm view-site-now"
						target="_blank">
						<?php esc_html_e( 'View Site', 'tutor' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
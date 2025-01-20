<?php
/**
 * Templates view
 *
 * @package Tutor
 * @author Tutor <support@themeum.com>
 * @link https://tutor.com
 * @since 3.0.2
 */
function get_template_list() {
	$template_response = file_get_contents( TEMPLATE_LIST_ENDPOINT );
	$templates         = json_decode( $template_response, true );
	try {
		return $templates;
	} catch ( \Throwable $th ) {
		return array();
	}
}

$template_list = get_template_list();

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
						<?php esc_html_e( 'Themes', 'tutorowl' ); ?>
					</div>
					<div class="tutorowl-top-left-text"><?php esc_html_e( 'Leverage the collection of magnificent Tutor starter themes to make a jumpstart.', 'tutorowl' ); ?></div>
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
								<!-- <img src="<?php echo esc_url( $prev_img[ $i++ ] ); ?>" loading="lazy" alt="icon"> -->
								<img src="<?php echo esc_url( $template->preview_image ); ?>" loading="lazy" alt="icon">
								<button 
									data-template="<?php echo esc_attr( $key ); ?>"
									class="tutor-template-import-btn">
									<?php esc_html_e( 'Get this', 'tutorowl' ); ?>
								</button>
							</div>
							<!-- <div class="tutorowl-template-actions">
								<a class="preview-url btn btn-light" href="https://preview.tutorlms.com/singlecourse/"
									target="_blank"><?php esc_html_e( 'Preview', 'tutorowl' ); ?></a>
								<button 
									data-template="<?php echo esc_attr( $key ); ?>"
									class="tutor-template-import-btn btn btn-primary primary-btn">
									<span><?php esc_html_e( 'Import', 'tutorowl' ); ?></span>
								</button>
							</div> -->
						</div>
						<div class="tutorowl-single-template-footer">
							<div class="tutorowl-template-name">
								<span><?php echo esc_html( $template->name ); ?></span>
								<span class="tutorowl-template-badge"> <?php esc_html_e( 'Pro', 'tutorowl' ); ?> </span>
							</div>
							<a class="tutorowl-template-preview" href="<?php echo esc_url( '#', 'tutorowl' ); ?>">
								<svg width="16" height="16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M2.343 3.879a.889.889 0 0 0-.888.889v8.889a.889.889 0 0 0 .888.889h8.89a.889.889 0 0 0 .888-.89V8.809a.727.727 0 0 1 1.455 0v4.849A2.343 2.343 0 0 1 11.232 16H2.343A2.343 2.343 0 0 1 0 13.657v-8.89a2.343 2.343 0 0 1 2.343-2.343h4.849a.727.727 0 0 1 0 1.455H2.343ZM9.697.727c0-.401.326-.727.727-.727h4.849c.401 0 .727.326.727.727v4.849a.727.727 0 0 1-1.454 0V1.455h-4.122a.727.727 0 0 1-.727-.728Z" fill="#9197A8"/><path fill-rule="evenodd" clip-rule="evenodd" d="M15.787.213a.727.727 0 0 1 0 1.029L6.898 10.13A.727.727 0 0 1 5.87 9.102L14.758.213a.727.727 0 0 1 1.029 0Z" fill="#9197A8"/></svg>
							</a>
						</div>
					</li>
					<?php
				}
			} else {
				?>
					<h3 style="text-align: center; margin-top: 30px;">
					<?php esc_html_e( 'No template available.', 'tutorowl' ); ?></h3>
			<?php } ?>
		</ul>
	</div>
</div>
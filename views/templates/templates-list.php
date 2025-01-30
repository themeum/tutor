<?php
/**
 * Templates importing view
 *
 * @package Tutor
 * @author Tutor <support@themeum.com>
 * @link https://tutor.com
 * @since 3.3.3
 */

/**
 * Get Template list.
 */
function get_template_list() {
	$template_response = file_get_contents( tutor()->path . '/views/templates/droip-layouts.json' );
	$templates         = json_decode( $template_response, true );
	return $templates;
}

$template_list = get_template_list();

$i = 0;
if ( ! empty( $template_list ) ) {
	foreach ( $template_list as $key => $template ) {
		$template = (object) $template;
		?>
		<li class="tutorowl-single-template tutor-d-flex tutor-flex-column tutor-justify-between tutor-gap-1 tutor-p-12">
			<div class="tutorowl-single-template-inner">
				<div class="tutorowl-template-preview-img">
					<img src="<?php echo esc_url( $template->preview_image ); ?>" loading="lazy" alt="icon">
				</div>
			</div>
			<div class="tutorowl-single-template-footer tutor-d-flex tutor-align-center tutor-justify-between">
				<div class="tutorowl-template-name tutor-fs-6 tutor-fw-medium">
					<span><?php echo esc_html( $template->name ); ?></span>
					<!-- <span class="tutorowl-template-badge"> <?php esc_html_e( 'Pro', 'tutor' ); ?> </span> -->
				</div>
				<div class="tutor-d-flex tutor-align-center tutor-gap-1">
					<button class="tutor-btn tutor-btn-sm open-template-live-preview" data-url="<?php echo esc_url( $template->preview_url, 'tutor' ); ?>" style="border: 1px solid #ddd;">
						<?php esc_html_e( 'Preview', 'tutor' ); ?>
					</button>
					<?php do_action( 'template_import_btn', $key ); ?>
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
<?php
/**
 * Templates importing view
 *
 * @package Tutor
 * @author Tutor <support@themeum.com>
 * @link https://tutor.com
 * @since 3.0.2
 */

use TUTOR\Input;
use TUTOR\TemplateImporter;

$search_query = Input::post( 'q' );

$template_list = TemplateImporter::get_template_list();


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
					<a class="tutor-btn tutor-btn-sm tutor-fs-6 tutor-color-secondary" href="<?php echo esc_url( '#', 'tutor' ); ?>">
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

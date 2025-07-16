<?php
/**
 * Templates importing listing
 *
 * @package Tutor
 * @subpackage TemplateImport
 * @author Tutor <support@themeum.com>
 * @link https://tutor.com
 * @since 3.6.0
 */

use Tutor\Helpers\TemplateImportHelper;

$template_list = ( new TemplateImportHelper() )->get_template_list();

$i = 0;
if ( ! empty( $template_list ) ) { ?>
	<ul class="tutor-template-list">
		<?php
		foreach ( $template_list as $key => $template ) {
			$template = (object) $template;
			if ( property_exists( $template, 'is_coming_soon' ) && ( 'off' === $template->is_coming_soon || ! $template->is_coming_soon ) ) {
				?>
				<li class="tutor-template-list-single-template tutor-d-flex tutor-flex-column tutor-justify-between tutor-gap-1 tutor-p-12">
					<div class="tutor-template-list-single-template-inner">
						<div class="tutor-import-template-preview-img">
							<img src="<?php echo esc_url( $template->preview_image ); ?>" loading="lazy" alt="icon">
						</div>
					</div>
					<div class="tutor-template-list-single-template-footer tutor-d-flex tutor-align-center tutor-justify-between">
						<div class="tutor-import-template-name tutor-fs-6 tutor-fw-medium">
							<span><?php echo esc_html( $template->label ); ?></span>
						</div>
						<div class="tutor-d-flex tutor-align-center tutor-gap-1">
							<button class="tutor-btn tutor-btn-primary tutor-btn-sm tutor-template-preview-btn" data-template_name="<?php echo esc_attr( $template->label ); ?>" data-template_id="<?php echo esc_attr( $template->slug ); ?>" data-template_url="<?php echo esc_url( $template->preview_url, 'tutor' ); ?>" data-template_course_data_url="<?php echo esc_url( $template->course_data_url ); ?>" >
								<?php esc_html_e( 'Import', 'tutor' ); ?>
							</button>
						</div>
					</div>
				</li>
				<?php
			} else {
				?>
				<li class="tutor-template-list-single-template tutor-d-flex tutor-flex-column tutor-justify-between tutor-gap-1 tutor-p-12">
					<div class="tutor-template-list-single-template-inner">
						<div class="tutor-import-template-preview-img">
							<img src="<?php echo esc_url( $template->preview_image ); ?>" loading="lazy" alt="icon">
						</div>
					</div>
					<div class="tutor-template-list-single-template-footer tutor-d-flex tutor-align-center tutor-justify-between">
						<div class="tutor-import-template-name tutor-fs-6 tutor-fw-medium">
							<span><?php echo esc_html( $template->label ); ?></span>
						</div>
						<div class="tutor-template-coming-soon">
							<?php esc_html_e( 'Coming soon', 'tutor' ); ?>
						</div>
					</div>
				</li>
				<?php
			}
		}
		?>
	</ul>
	<?php
} else {
	?>
		<div class="tutor-template-empty-state" style="text-align: center; margin-top: 30px;">
			<?php esc_html_e( 'No template available.', 'tutor' ); ?>
		</div>
<?php } ?>

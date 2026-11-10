<?php
/**
 * Tools pages
 *
 * @package Tutor\Views
 * @subpackage Tutor\Tools
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || exit;

$tutor_pages = tutor_utils()->tutor_pages();
?>
<div class="tutor-option-main-title">
	<div class="tutor-fs-4 tutor-fw-medium tutor-color-black"><?php esc_html_e( 'Tutor Pages', 'tutor' ); ?></div>
</div>

<?php tutor_alert( __( 'Note: This tool will install all the missing Tutor pages. Pages already defined and set up will not be replaced.', 'tutor' ), 'primary' ); ?>

<div class="tutor-option-single-item tutor-mb-32 item-variation-table table-col-3 all-pages">
	<div class="tutor-option-group-title tutor-mb-16">
		<div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'All Pages', 'tutor' ); ?></div>
	</div>
	<div class="item-wrapper">
		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<?php esc_html_e( 'ID', 'tutor' ); ?>
			</div>
			<div class="tutor-option-field-label">
				<?php esc_html_e( 'Page Name', 'tutor' ); ?>
			</div>
			<div class="tutor-option-field-label">
				<?php esc_html_e( 'Status', 'tutor' ); ?>
			</div>
		</div>
		<?php
		foreach ( $tutor_pages as $tutor_page ) {
			$page_id = $tutor_page['page_id'];
			?>
		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<div class="tutor-fs-7 tutor-fw-medium"><?php echo esc_html( $page_id ); ?></div>
			</div>
			<div class="tutor-option-field-label">
				<div class="tutor-fs-7 tutor-fw-medium">
					<?php if ( $tutor_page['page_exists'] ) : ?>
						<a class="tutor-btn tutor-btn-ghost" href="<?php echo esc_url( admin_url( "post.php?post={$page_id}&action=edit" ) ); ?>" target='_blank'>
							<?php echo esc_html( $tutor_page['page_name'] ); ?>
						</a>
					<?php else : ?>
						<?php echo esc_html( $tutor_page['page_name'] ); ?>
					<?php endif; ?>
				</div>
			</div>
			<div class="tutor-option-field-label">
				<?php $page_post = $tutor_page['page_exists'] && $tutor_page['page_visible'] ? get_post( $page_id ) : null; ?>
				<?php if ( $page_post ) : ?>
					<div class="tutor-d-flex tutor-align-center">
						<span class='tutor-icon-circle-mark tutor-color-success'></span>
						<span class='tutor-mx-4'>/</span>
						<span><?php echo esc_html( urldecode( $page_post->post_name ) ); ?></span>
						<span class="tutor-ml-8">
							<a href="<?php echo esc_url( get_permalink( $page_post ) ); ?>" class="tutor-iconic-btn" target="_blank">
								<i class="tutor-icon-external-link"></i>
							</a>
						</span>
					</div>
				<?php else : ?>
					<span class='tutor-icon-circle-times-line tutor-color-warning'></span>
				<?php endif; ?>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
<div class="btn-wrap regenerate-pages">
	<form method="post">
		<?php
		tutor_action_field( 'regenerate_tutor_pages' );
		tutor_nonce_field();
		?>
		<button class="tutor-btn tutor-btn-primary" type="submit"><?php esc_html_e( 'Re-Generate Tutor Pages', 'tutor' ); ?></button>
	</form>
</div><br>

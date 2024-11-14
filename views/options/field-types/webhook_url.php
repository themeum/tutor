<?php
/**
 * Input filed type webhook_url for settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

$field_key = $field['key'];
$field_id  = esc_attr( 'field_' . $field_key );
?>
<div class="tutor-mt-16" id="<?php echo esc_attr( $field_id ); ?>">
	<div class="tutor-fs-6 tutor-fw-medium">
		<?php esc_html_e( 'Webhook URL', 'tutor' ); ?>
	</div> 
	<?php
	$method = explode( '_', $field_key )[0];
	$url    = site_url( 'wp-json/tutor/v1/ecommerce-webhook?payment_method=' . $method );
	?>
	<div class="tutor-d-flex tutor-justify-between tutor-align-center tutor-gap-1">
		<span class="tutor-color-success">
			<?php echo esc_url( $url ); ?>
		</span>
		<a class="tutor-btn tutor-btn-outline-primary tutor-btn-sm tutor-copy-text" data-text="<?php echo esc_url( $url ); ?>">
			<span class="tutor-icon-copy-text tutor-mr-8"></span>
			<span><?php esc_html_e( 'Copy', 'tutor' ); ?></span>
		</a>
	</div>
</div>

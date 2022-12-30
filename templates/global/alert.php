<?php
/**
 * Alert template
 *
 * @package Tutor\Templates
 * @subpackage Global
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

/**
 * These variables are the supported args that can be passed while loading template.
 *
 * If don't want to show button or close icon then simple don't pass arg
 * it will not show button and icon then.
 */
$alert_class  = isset( $data['alert_class'] ) ? $data['alert_class'] : '';
$message      = isset( $data['message'] ) ? $data['message'] : '';
$icon         = isset( $data['icon'] ) ? $data['icon'] : '';
$button_text  = isset( $data['button_text'] ) ? $data['button_text'] : '';
$button_class = isset( $data['button_class'] ) ? $data['button_class'] : '';
$button_id    = isset( $data['button_id'] ) ? $data['button_id'] : '';
$close_icon   = isset( $data['close_icon'] ) ? $data['close_icon'] : '';

if ( '' === $alert_class ) {
	die( esc_html_e( 'Please define alert class', 'tutor' ) );
}
?>
<div class="<?php echo esc_attr( $alert_class ); ?>">
	<div class="tutor-alert-text">
		<span class="tutor-alert-icon tutor-fs-4 <?php echo esc_attr( $icon ); ?> tutor-mr-12"></span>
		<span>
		<?php echo esc_html( $message ); ?>
		</span>
	</div>
	
	<?php if ( '' !== $button_text || '' !== $close_icon ) : ?>
	<div class="alert-btn-group">
		<?php if ( '' !== $button_text ) : ?>
			<button class="<?php echo esc_attr( $button_class ); ?>" id="<?php echo esc_attr( $button_id ); ?>">
				<?php echo esc_html( $button_text ); ?>
			</button>
		<?php endif; ?>

		<?php if ( '' !== $close_icon ) : ?>
			<span class="tutor-alert-close tutor-fs-5 tutor-color-secondary <?php echo esc_attr( $close_icon ); ?>"></span>
		<?php endif; ?>
	</div>
	<?php endif; ?>
</div>

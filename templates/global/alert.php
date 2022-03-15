<?php
/**
 * Tutor alert template
 *
 * Display various alert message
 *
 * @package TutorAlertTemplate
 *
 * @since v2.0.0
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
		<span class="tutor-alert-icon tutor-icon-34 <?php echo esc_attr( $icon ); ?> tutor-mr-12"></span>
		<span>
		<?php echo esc_html( $message ); ?>
		</span>
	</div>
	<div class="tutor-alert-btns">
		<?php if ( '' !== $button_text ) : ?>
		<div class="alert-btn-group">
			<button class="<?php echo esc_attr( $button_class ); ?>" id="<?php echo esc_attr( $button_id ); ?>">
				<?php echo esc_html( $button_text ); ?>
			</button>
		</div>
		<?php endif; ?>
		<?php if ( '' !== $close_icon ) : ?>
			<span class="tutor-alert-close tutor-icon-28 tutor-color-black-40 <?php echo esc_attr( $close_icon ); ?>"></span>
		<?php endif; ?>
	</div>
</div>

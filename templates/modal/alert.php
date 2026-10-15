<?php
/**
 * Tutor prompt alert template
 * Display various prompt messages
 *
 * @package Tutor\Templates
 * @subpackage Modal
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.2
 */

$id      = isset( $id ) ? $id : 'tutor-alert-modal-' . uniqid(); // Ensure we have an ID for ARIA.
$class   = isset( $class ) ? ' ' . $class : '';
$title   = isset( $title ) ? $title : 'Do You Want to Delete This?'; // phpcs:ignore
$content = isset( $content ) ? $content : '';
$close   = isset( $close ) ? (bool) $close : true;
?>
<div id="<?php echo esc_attr( $id ); ?>" class="tutor-modal<?php echo esc_attr( $class ); ?>" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $id ); ?>-title" aria-hidden="true">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window tutor-modal-window-sm">
		<div class="tutor-modal-content tutor-modal-content-white">
			<?php if ( $close ) : ?>
			<button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close aria-label="<?php esc_attr_e( 'Close', 'tutor' ); ?>">
				<span class="tutor-icon-times" aria-hidden="true"></span>
			</button>
			<?php endif; ?>
			<div class="tutor-modal-body tutor-text-center">
				<div class="tutor-my-32">
					<?php if ( $title ) : ?>
						<div id="<?php echo esc_attr( $id ); ?>-title" class="tutor-fs-4 tutor-fw-medium tutor-color-black tutor-mb-8"><?php echo esc_html( $title ); ?></div>
					<?php endif; ?>
					<?php if ( $content ) : ?>
						<div class="tutor-fs-6 tutor-color-muted"><?php echo esc_html( $content ); ?></div>
					<?php endif; ?>
					<button class="tutor-btn tutor-btn-primary tutor-btn-fw tutor-mt-32" data-tutor-modal-close>
						<?php esc_html_e( 'Ok', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>

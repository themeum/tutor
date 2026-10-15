<?php
/**
 * Tutor confirm alert template
 * Display confirm window
 *
 * @package Tutor\Templates
 * @subpackage Modal
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.2
 */

$id      = isset( $id ) ? $id : 'tutor-confirm-modal-' . uniqid(); // Ensure we have an ID for ARIA.
$class   = isset( $class ) ? ' ' . $class : '';
$image   = isset( $image ) ? $image : '';
$icon    = isset( $icon ) ? $icon : '';
$title   = isset( $title ) ? $title : ''; //phpcs:ignore
$content = isset( $content ) ? $content : '';
$yes     = isset( $yes ) ? $yes : array( 'text' => __( 'Yes', 'tutor' ) );
$close   = isset( $close ) ? (bool) $close : true;
?>
<div id="<?php echo esc_attr( $id ); ?>" class="tutor-modal<?php echo esc_attr( $class ); ?>" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $id ); ?>-title" aria-hidden="true">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window">
		<div class="tutor-modal-content tutor-modal-content-white">
			<?php if ( $close ) : ?>
			<button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close aria-label="<?php esc_attr_e( 'Close', 'tutor' ); ?>">
				<span class="tutor-icon-times" aria-hidden="true"></span>
			</button>
			<?php endif; ?>
			<div class="tutor-modal-body tutor-text-center">
				<div class="tutor-px-lg-48 tutor-py-lg-24">
					<?php if ( $image ) : ?>
						<div class="tutor-mt-24">
							<img class="tutor-d-inline-block" src="<?php echo esc_url( tutor()->url ); ?>assets/images/<?php echo esc_attr( $image ); ?>" alt="" />
						</div>
					<?php endif; ?>

					<?php if ( $icon ) : ?>
						<div class="tutor-mt-24">
							<span class="tutor-d-inline-block" aria-hidden="true"><?php echo esc_attr( $icon ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( $title ) : ?>
						<div id="<?php echo esc_attr( $id ); ?>-title" class="tutor-fs-3 tutor-fw-medium tutor-color-black tutor-mb-12"><?php echo esc_html( $title ); ?></div>
					<?php endif; ?>
					
					<?php if ( $content ) : ?>
						<div class="tutor-fs-6 tutor-color-muted"><?php echo esc_html( $content ); ?></div>
					<?php endif; ?>

					<div class="tutor-d-flex tutor-justify-center tutor-mt-48 tutor-mb-24">
						<button class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
							<?php esc_html_e( 'Cancel', 'tutor' ); ?>
						</button>
						<button class="tutor-btn tutor-btn-primary<?php echo isset( $yes['class'] ) ? ' ' . esc_html( $yes['class'] ) : ''; ?> tutor-ml-20" <?php echo isset( $yes['attr'] ) && is_array( $yes['attr'] ) ? implode( ' ', $yes['attr'] ) : ''; //phpcs:ignore ?>>
							<?php echo esc_html( $yes['text'] ); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

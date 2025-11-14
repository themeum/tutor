<?php
/**
 * Popup Progress Card Component
 * Reusable popup progress card component for dashboard
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

// Default values.
$card_title  = isset( $card_title ) ? $card_title : esc_html__( 'Amazing!', 'tutor' );
$user_name   = isset( $user_name ) ? $user_name : '';
$subtitle    = isset( $subtitle ) ? $subtitle : '';
$value       = isset( $value ) ? $value : '';
$button_text = isset( $button_text ) ? $button_text : esc_html__( "I'm Happy", 'tutor' );
$breakdown   = isset( $breakdown ) ? $breakdown : '';

?>
<div class="tutor-popup-progress-card">
	<div class="tutor-popup-progress-card-icon">
		<?php tutor_utils()->render_svg_icon( Icon::CONFETTI, 32, 32 ); ?>
	</div>
	
	<div class="tutor-popup-progress-card-header">
		<h2 class="tutor-popup-progress-card-title">
			<span class="tutor-popup-progress-card-title-text"><?php echo esc_html( $card_title ); ?></span>
			<?php if ( ! empty( $user_name ) ) : ?>
				<span class="tutor-popup-progress-card-title-name"><?php echo esc_html( $user_name ); ?></span>
			<?php endif; ?>
		</h2>
		<?php if ( ! empty( $subtitle ) ) : ?>
			<p class="tutor-popup-progress-card-subtitle">
				<?php echo esc_html( $subtitle ); ?>
			</p>
		<?php endif; ?>
	</div>
	
	<div class="tutor-popup-progress-card-content">
		<?php if ( ! empty( $value ) ) : ?>
			<div class="tutor-popup-progress-card-value">
				<span class="tutor-popup-progress-card-value-text"><?php echo esc_html( $value ); ?></span>
			</div>
		<?php endif; ?>
		
		<?php if ( ! empty( $breakdown ) ) : ?>
			<p class="tutor-popup-progress-card-breakdown">
				<?php echo wp_kses_post( $breakdown ); ?>
			</p>
		<?php endif; ?>
	</div>
	
	<div class="tutor-popup-progress-card-footer">
		<button type="button" class="tutor-btn tutor-btn-primary tutor-btn-block tutor-gap-2">
			<?php tutor_utils()->render_svg_icon( Icon::HAPPY, 20, 20 ); ?>
			<?php echo esc_html( $button_text ); ?>
		</button>
	</div>
</div>


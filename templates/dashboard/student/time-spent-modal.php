<?php
/**
 * Student dashboard time spent modal
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use Tutor\Helpers\UrlHelper;

$minutes_value = ( $time_spent['hours'] * MINUTE_IN_SECONDS ) + $time_spent['minutes'];
$seconds_value = ( $time_spent['hours'] * HOUR_IN_SECONDS ) + ( $time_spent['minutes'] * MINUTE_IN_SECONDS ) + $time_spent['seconds'];

$has_minutes = $minutes_value > 0;
$has_seconds = $seconds_value > 0;

$minutes_html = '<span class="tutor-font-medium">' . esc_html( $minutes_value ) . '+</span>';
$seconds_html = '<span class="tutor-font-medium">' . esc_html( $seconds_value ) . '+</span>';

if ( $has_minutes && $has_seconds ) {
	$text = sprintf(
		/* translators: 1: minutes, 2: seconds */
		__( 'That\'s %1$s minutes, and %2$s seconds! Incredible!', 'tutor' ),
		$minutes_html,
		$seconds_html
	);
} elseif ( $has_minutes ) {
	$text = sprintf(
		/* translators: 1: minutes */
		__( 'That\'s %1$s minutes! Incredible!', 'tutor' ),
		$minutes_html
	);
} elseif ( $has_seconds ) {
	$text = sprintf(
		/* translators: 1: seconds */
		__( 'That\'s %1$s seconds! Incredible!', 'tutor' ),
		$seconds_html
	);
} else {
	$text = __( "That's 0 seconds! Incredible!", 'tutor' );
}
?>
<div x-data="tutorModal({ id: 'tutor-time-spent-modal' })" x-cloak>
	<template x-teleport="body">
		<div x-bind="getModalBindings()">
			<div x-bind="getBackdropBindings()"></div>
			<div x-bind="getModalContentBindings()" style="width: 354px;">
				<div class="tutor-modal-body tutor-px-9 tutor-pt-9 tutor-pb-8 tutor-text-center">
					<div class="tutor-flex tutor-justify-center">
						<img
							src="<?php echo esc_url( UrlHelper::asset( 'images/illustrations/confetti.svg' ) ); ?>"
							alt="<?php esc_attr_e( 'Confetti', 'tutor' ); ?>"
						/>
					</div>

					<h3 class="tutor-h3 tutor-mb-2 tutor-mt-6">
						<span class="tutor-font-regular"><?php esc_html_e( 'Fantastic,', 'tutor' ); ?></span>
						<?php echo esc_html( $user_data->display_name ); ?>!
					</h3>

					<div class="tutor-tiny tutor-text-secondary tutor-mb-6">
						<?php echo esc_html__( "You've dedicated over", 'tutor' ); ?>
					</div>

					<h2 class="tutor-h2 tutor-text-exception4 tutor-py-6 tutor-surface-warning tutor-rounded-lg tutor-mb-6">
						<?php echo esc_html( $time_spent_value ); ?>
					</h2>

					<p class="tutor-p2 tutor-mb-7">
						<?php echo wp_kses_post( $text ); ?>
					</p>

					<button
						class="tutor-btn tutor-btn-primary tutor-btn-large tutor-rounded-full tutor-btn-block tutor-gap-2"
						@click="TutorCore.modal.closeModal('tutor-time-spent-modal')"
					>
						<?php SvgIcon::make()->name( Icon::HAPPY )->size( 20 )->render(); ?>
						<?php esc_html_e( "I'm Happy", 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</template>
</div>

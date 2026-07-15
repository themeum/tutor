<?php
/**
 * Profile Completion Template
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Constants\Size;
use Tutor\Components\Progress;
use Tutor\Components\SvgIcon;
use Tutor\Components\Constants\Color;
use TUTOR\Icon;

if ( ! tutor_utils()->get_option( 'enable_profile_completion' ) ) {
	return;
}

$profile_completion = tutor_utils()->user_profile_completion();

if ( empty( $profile_completion ) || ! is_array( $profile_completion ) ) {
	return;
}

$total_count          = count( $profile_completion );
$first_incomplete_key = null;

foreach ( $profile_completion as $key => $data ) {
	if ( empty( $data['is_set'] ) ) {
		$first_incomplete_key = $key;
		break;
	}
}

if ( null === $first_incomplete_key ) {
	return;
}

$complete_count = 0;

foreach ( $profile_completion as $data ) {
	if ( ! empty( $data['is_set'] ) ) {
		$complete_count++;
	}
}

$completion_percentage = $total_count > 0
	? ( $complete_count / $total_count ) * 100
	: 0;

$first_incomplete_item = $profile_completion[ $first_incomplete_key ];
$rest_of_the_items     = $profile_completion;

unset( $rest_of_the_items[ $first_incomplete_key ] );
?>
<div class="tutor-profile-completion" x-data="{ expanded: false }">
	<div class="tutor-profile-completion-header" :class="expanded && 'tutor-expanded'">
		<div class="tutor-flex tutor-items-center tutor-gap-5">
			<?php Progress::make()->type( 'circle' )->size( Size::MEDIUM )->value( $completion_percentage )->render(); ?>
			<div class="tutor-flex tutor-flex-column tutor-gap-2">
				<div class="tutor-tiny tutor-text-secondary">
					<?php esc_html_e( 'Complete Your Profile', 'tutor' ); ?>
				</div>

				<?php if ( ! empty( $first_incomplete_item ) ) { ?>
					<a href="<?php echo esc_url( $first_incomplete_item['url'] ); ?>"
						class="tutor-tiny tutor-font-medium tutor-text-brand tutor-flex tutor-items-center tutor-gap-3">
						<?php echo esc_html( $first_incomplete_item['text'] ); ?>
						<?php SvgIcon::make()->name( Icon::ARROW_RIGHT_2 )->flip_rtl()->render(); ?>
					</a>
				<?php } ?>
			</div>
		</div>

		<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-small tutor-btn-icon" @click="expanded = !expanded" aria-label="<?php esc_attr_e( 'Toggle profile completion', 'tutor' ); ?>">
			<span :class="expanded && 'tutor-rotate-180'" class="tutor-flex tutor-transition-all">
				<?php SvgIcon::make()->name( Icon::CHEVRON_DOWN_2 )->size( 20 )->color( Color::SECONDARY )->render(); ?>
			</span>
		</button>
	</div>

	<div class="tutor-profile-completion-body" x-show="expanded" x-cloak>
		<?php foreach ( $rest_of_the_items as $data ) : ?>
			<div class="tutor-flex tutor-items-center tutor-gap-8">
				<?php if ( $data['is_set'] ) : ?>
					<div class="tutor-flex tutor-p-3 tutor-surface-brand-primary tutor-border tutor-border-brand tutor-rounded-full">
						<?php SvgIcon::make()->name( Icon::CHECK_2 )->size( 16 )->color( Color::IDLE_INVERSE )->render(); ?>
					</div>
					<div class="tutor-tiny tutor-font-medium tutor-text-subdued">
						<?php echo esc_html( $data['text'] ); ?>
					</div>
				<?php else : ?>
					<div class="tutor-flex tutor-p-3 tutor-border tutor-rounded-full">
						<?php SvgIcon::make()->name( Icon::CHECK_2 )->size( 16 )->color( Color::DISABLED )->render(); ?>
					</div>
					<a href="<?php echo esc_url( $data['url'] ); ?>" class="tutor-tiny tutor-font-medium tutor-text-brand tutor-flex tutor-items-center tutor-gap-3">
						<?php echo esc_html( $data['text'] ); ?>
						<?php SvgIcon::make()->name( Icon::ARROW_RIGHT_2 )->flip_rtl()->render(); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>

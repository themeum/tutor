<?php
/**
 * Leaderboard Item Component
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
use Tutor\Components\Avatar;
use Tutor\Components\Constants\Size;

?>

<div class="tutor-dashboard-home-card-item">
	<div class="tutor-flex tutor-align-center tutor-gap-4">
		<!-- Index -->
		<div class="tutor-dashboard-home-card-icon">
			<?php if ( 0 === $item_key ) : ?>
				<?php tutor_utils()->render_svg_icon( Icon::BADGE, 16, 16, array( 'class' => 'tutor-icon-exception4' ) ); ?>
			<?php else : ?>
				<?php echo esc_html( $item_key + 1 ); ?>
			<?php endif; ?>
		</div>

		<!-- Avatar -->
		<div class="tutor-dashboard-home-card-avatar">
			<?php Avatar::make()->src( $item['avatar'] )->initials( $item['name'] )->size( Size::SIZE_32 )->render(); ?>
		</div>

		<!-- Info -->
		<div class="tutor-flex-1 tutor-flex tutor-flex-column">
			<div class="tutor-tiny tutor-font-medium">
				<?php echo esc_html( $item['name'] ); ?>
			</div>
			<div class="tutor-tiny">
				<span class="tutor-text-subdued">
					<?php
					printf(
						// translators: %s: Number of courses.
						esc_html__( '%s courses', 'tutor' ),
						esc_html( $item['no_of_courses'] )
					);
					?>
				</span>
				<span class="tutor-text-subdued tutor-mx-2">•</span>
				<span class="tutor-text-success">
					<?php
					printf(
						// translators: %s: Completion percentage.
						esc_html__( '%s completion', 'tutor' ),
						esc_html( $item['completion_percentage'] . '%' )
					);
					?>
				</span>
			</div>
		</div>
	</div>
</div>

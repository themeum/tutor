<?php
/**
 * Recent Activity Item Component
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Components\Avatar;
use Tutor\Components\Constants\Size;

?>

<div class="tutor-dashboard-home-card-item tutor-dashboard-home-activity">
	<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-w-full">
		<!-- Avatar -->
		<div class="tutor-dashboard-home-card-avatar">
			<?php Avatar::make()->src( $item['user']['avatar'] )->initials( $item['user']['name'] )->size( Size::SIZE_40 )->render(); ?>
		</div>
		<div class="tutor-flex-1 tutor-flex tutor-flex-column">
			<div class="tutor-flex tutor-justify-between tutor-items-center tutor-tiny">
				<div>
					<?php echo esc_html( $item['user']['name'] ); ?>
					<span class="tutor-text-subdued">
						<?php echo esc_html( $item['meta'] ); ?>
					</span>
				</div>
				<div class="tutor-tiny-2 tutor-text-subdued">
					<?php echo esc_html( human_time_diff( strtotime( $item['date'] ) ) ); ?>
				</div>
			</div>
			<a class="tutor-tiny tutor-font-medium tutor-truncate" href="<?php echo esc_url( $item['course_url'] ); ?>">
				<?php echo esc_html( $item['course_name'] ); ?>
			</a>
		</div>
	</div>
</div>

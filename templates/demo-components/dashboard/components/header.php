<?php
/**
 * Tutor dashboard sidebar.
 *
 * @package tutor
 */

use TUTOR\Icon;

?>

<div class="tutor-dashboard-header">
	<div class="tutor-dashboard-header-inner">
		<div class="tutor-dashboard-header-left">
			<div class="tutor-h5 tutor-text-primary tutor-text-medium">
				<span class="tutor-text-subdued">Hi,</span> Johny! 👋
			</div>
			<ul class="tutor-dashboard-header-user-streaks">
				<li><span class="tutor-font-medium tutor-text-primary">24</span>-day learning streak</li>
				<li>Keep it up!</li>
			</ul>
		</div>
		<div class="tutor-dashboard-header-right">
			<button class="tutor-btn tutor-btn-outline tutor-btn-x-small tutor-gap-2">
				24 
				<?php
				tutor_utils()->render_svg_icon(
					Icon::ENERGY,
					16,
					16,
					array(
						'class' => 'tutor-text-brand',
					)
				);
				?>
			</button>
			<button class="tutor-btn tutor-btn-outline tutor-btn-x-small">
				<?php tutor_utils()->render_svg_icon( Icon::NOTIFICATION ); ?>
			</button>
			<div class="tutor-dashboard-header-user">
				<div class="tutor-dashboard-header-user-avatar">
					<?php echo get_avatar( get_current_user_id(), 32 ); ?>
				</div>
			</div>
		</div>
	</div>
</div>

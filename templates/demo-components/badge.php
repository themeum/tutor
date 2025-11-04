<?php
/**
 * Template for displaying dashboard demo components
 *
 * @package Tutor
 * @since 1.0.0
 */

use TUTOR\Icon;
?>
<div class="tutor-bg-white tutor-py-6 tutor-px-8">
	<h2>Badges</h2>
	<div class="tutor-rounded-lg tutor-shadow-sm" style="display: flex; gap:10px;">
		<span class="tutor-badge">
			<?php tutor_utils()->render_svg_icon( Icon::INFO ); ?>
			Primary
		</span>
		<span class="tutor-badge tutor-badge-primary">
			<?php tutor_utils()->render_svg_icon( Icon::INFO ); ?>
			Primary
		</span>
		<span class="tutor-badge tutor-badge-pending">
			Pending
		</span> 
		<span class="tutor-badge tutor-badge-completed">
			Completed
		</span>
		<span class="tutor-badge tutor-badge-cancelled">
			Cancelled
		</span> 
		<span class="tutor-badge tutor-badge-secondary">
			Secondary
		</span> 
		<span class="tutor-badge tutor-badge-secondary tutor-badge-circle tutor-text-secondary">
			<span class="tutor-text-subdued">Points:</span> 20
		</span>
		<span class="tutor-badge tutor-badge-exception tutor-badge-circle">
			Bundle
		</span>
	</div>
</div>
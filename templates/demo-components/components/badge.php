<?php
/**
 * Template for displaying dashboard demo components
 *
 * @package Tutor
 * @since 1.0.0
 */

use TUTOR\Icon;
?>
<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-flex tutor-flex-column tutor-gap-4">
	<h2>Badges</h2>
	<div class="tutor-rounded-lg tutor-flex tutor-gap-4">
		<span class="tutor-badge">
			<?php tutor_utils()->render_svg_icon( Icon::INFO ); ?>
			Primary
		</span>
		<span class="tutor-badge tutor-badge-info">
			Info
		</span>
		<span class="tutor-badge tutor-badge-warning">
			Warning
		</span>
		<span class="tutor-badge tutor-badge-success">
			Success
		</span>
		<span class="tutor-badge tutor-badge-success-solid">
			Success Solid
		</span>
		<span class="tutor-badge tutor-badge-error">
			Error
		</span>
		<span class="tutor-badge tutor-badge-highlight">
			Highlight
		</span>
	</div>

	<div class="tutor-rounded-lg tutor-flex tutor-gap-4">
		<span class="tutor-badge tutor-badge-rounded">
			<?php tutor_utils()->render_svg_icon( Icon::INFO ); ?>
			Rounded
		</span>
		<span class="tutor-badge tutor-badge-rounded tutor-badge-info">
			Rounded Info
		</span>
		<span class="tutor-badge tutor-badge-rounded tutor-badge-warning">
			Rounded Warning
		</span>
		<span class="tutor-badge tutor-badge-rounded tutor-badge-success">
			Rounded Success
		</span>
		<span class="tutor-badge tutor-badge-rounded tutor-badge-success-solid">
			Rounded Success Solid
		</span>
		<span class="tutor-badge tutor-badge-rounded tutor-badge-error">
			Rounded Error
		</span>
		<span class="tutor-badge tutor-badge-rounded tutor-badge-highlight">
			Rounded Highlight
		</span>
	</div>
</section>
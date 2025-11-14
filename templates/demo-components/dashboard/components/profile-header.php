<?php
/**
 * Tutor dashboard profile header
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
?>
<div class="tutor-profile-settings-header">
	<div class="tutor-dashboard-container tutor-flex tutor-items-center tutor-justify-between">
		<div class="tutor-flex tutor-items-center">
			<button type="button" class="tutor-profile-settings-back-btn tutor-btn tutor-btn-ghost tutor-btn-x-small">
				<span x-data="tutorIcon({ name: '<?php echo esc_html( Icon::ARROW_LEFT ); ?>', width: 20, height: 20})"></span>
			</button>
			<h4 class="tutor-text-h4 tutor-font-semibold tutor-ml-4">Settings</h4>
			<span class="tutor-badge tutor-badge-secondary tutor-badge-circle tutor-ml-5">Unsaved changes</span>
		</div>
		<div class="tutor-flex tutor-gap-4">
			<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-x-small">Discard</button>
			<button type="button" class="tutor-btn tutor-btn-primary tutor-btn-x-small">Save</button>
		</div>
	</div>
</div>
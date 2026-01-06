<?php
/**
 * Notifications settings
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
?>
<section class="tutor-profile-notification">
	<h5 class="tutor-mb-4 tutor-mt-4 tutor-md-mt-1 tutor-h5 tutor-sm-hidden">Notifications</h5>
	<div class="tutor-profile-notification-card tutor-card-rounded-2xl tutor-mt-5" x-data="{ expanded: false }">
		<div class="tutor-flex tutor-items-center tutor-justify-between tutor-gap-8 tutor-p-6">
			<div class="tutor-flex tutor-items-center tutor-gap-5">
				<?php tutor_utils()->render_svg_icon( Icon::NOTIFICATION_2, 20, 20 ); ?>
				<div>
					<div class="tutor-text-small tutor-font-medium tutor-text-primary">Email Notifications</div>
					<div class="tutor-text-small tutor-text-secondary">Configure custom notifications settings for Email.</div>
				</div>
			</div>
			<div class="tutor-flex tutor-gap-4">
				<div class="tutor-profile-notification-toggle tutor-text-subdued" :class="{ 'is-expanded': expanded }" @click="expanded = ! expanded">
					<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_UP, 20, 20 ); ?>
				</div>
				<div class="tutor-input-field">
					<input 
						type="checkbox"
						id="switch-sm"
						name="switch"
						class="tutor-switch"
						checked
					>
				</div>
			</div>
		</div>
		<div class="tutor-profile-notification-content tutor-p-6" x-show="expanded" x-collapse.duration.200ms>
			<span class="tutor-text-small tutor-text-subdued">General</span>
			<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-pt-4">
				<div class="tutor-input-field">
					<input type="checkbox" id="name" placeholder="Enter your full name" class="tutor-checkbox">
				</div>
				<span class="tutor-text-small tutor-text-primary">Lorem, ipsum dolor sit amet consectetur adipisicing elit.</span>
			</div>
			<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-pt-4">
				<div class="tutor-input-field">
					<input type="checkbox" id="name" placeholder="Enter your full name" class="tutor-checkbox">
				</div>
				<span class="tutor-text-small tutor-text-primary">Lorem, ipsum dolor sit amet consectetur adipisicing elit.</span>
			</div>
		</div>
	</div>
	<div class="tutor-profile-notification-card tutor-card-rounded-2xl tutor-mt-5" x-data="{ expanded: false }">
		<div class="tutor-flex tutor-items-center tutor-justify-between tutor-gap-8 tutor-p-6">
			<div class="tutor-flex tutor-items-center tutor-gap-5">
				<?php tutor_utils()->render_svg_icon( Icon::NOTIFICATION_2, 20, 20 ); ?>
				<div>
					<div class="tutor-text-small tutor-font-medium tutor-text-primary">Email Notifications</div>
					<div class="tutor-text-small tutor-text-secondary">Configure custom notifications settings for Email.</div>
				</div>
			</div>
			<div class="tutor-flex tutor-gap-4">
				<div class="tutor-profile-notification-toggle tutor-text-subdued" :class="{ 'is-expanded': expanded }" @click="expanded = ! expanded">
					<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_UP, 20, 20 ); ?>
				</div>
				<div class="tutor-input-field">
					<input 
						type="checkbox"
						id="switch-sm"
						name="switch"
						class="tutor-switch"
						checked
					>
				</div>
			</div>
		</div>
		<div class="tutor-profile-notification-content tutor-p-6" x-show="expanded" x-collapse.duration.200ms>
			<span class="tutor-text-small tutor-text-subdued">General</span>
			<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-pt-4">
				<div class="tutor-input-field">
					<input type="checkbox" id="name" placeholder="Enter your full name" class="tutor-checkbox">
				</div>
				<span class="tutor-text-small tutor-text-primary">Lorem, ipsum dolor sit amet consectetur adipisicing elit.</span>
			</div>
			<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-pt-4">
				<div class="tutor-input-field">
					<input type="checkbox" id="name" placeholder="Enter your full name" class="tutor-checkbox">
				</div>
				<span class="tutor-text-small tutor-text-primary">Lorem, ipsum dolor sit amet consectetur adipisicing elit.</span>
			</div>
		</div>
	</div>
</section>
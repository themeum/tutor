<?php
/**
 * Avatar components
 *
 * @package Tutor
 * @since 1.0.0
 */

use TUTOR\Icon;
?>
<div class="tutor-bg-white tutor-py-6 tutor-px-8">
	<h2>Avatars</h2>
	
	<!-- Avatar Sizes -->
	<div class="tutor-mb-6">
		<h3 class="tutor-mb-4">Avatar Sizes</h3>
		<div class="tutor-rounded-lg tutor-shadow-sm tutor-p-6" style="display: flex; align-items: center; gap: 20px;">

			<div class="tutor-avatar">
				<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
			</div>

			<!-- Extra Small -->
			<div class="tutor-avatar tutor-avatar-xs">
				<img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" alt="User Avatar" class="tutor-avatar-image">
			</div>
			
			<!-- Small -->
			<div class="tutor-avatar tutor-avatar-sm">
				<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
			</div>
			
			<!-- Medium -->
			<div class="tutor-avatar tutor-avatar-md">
				<img src="https://i.pravatar.cc/150?u=a042581f4e29026704d" alt="User Avatar" class="tutor-avatar-image">
			</div>
			
			<!-- Large -->
			<div class="tutor-avatar tutor-avatar-lg">
				<img src="https://i.pravatar.cc/150?u=a04258114e29026302d" alt="User Avatar" class="tutor-avatar-image">
			</div>
			
			<!-- Extra Large -->
			<div class="tutor-avatar tutor-avatar-xl">
				<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
			</div>
			
			<!-- Double Extra Large -->
			<div class="tutor-avatar tutor-avatar-xxl">
				<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
			</div>
		</div>
	</div>

	<!-- Avatar with Initials -->
	<div class="tutor-mb-6">
		<h3 class="tutor-mb-4">With Initials</h3>
		<div class="tutor-rounded-lg tutor-shadow-sm tutor-p-6" style="display: flex; align-items: center; gap: 20px;">
			<div class="tutor-avatar tutor-avatar-xs">
				<span class="tutor-avatar-initials">JD</span>
			</div>
			
			<div class="tutor-avatar tutor-avatar-sm">
				<span class="tutor-avatar-initials">JS</span>
			</div>
			
			<div class="tutor-avatar tutor-avatar-md">
				<span class="tutor-avatar-initials">MB</span>
			</div>
			
			<div class="tutor-avatar tutor-avatar-lg">
				<span class="tutor-avatar-initials">AL</span>
			</div>
			
			<div class="tutor-avatar tutor-avatar-xl">
				<span class="tutor-avatar-initials">KW</span>
			</div>

			<div class="tutor-avatar tutor-avatar-xxl">
				<span class="tutor-avatar-initials">KW</span>
			</div>
		</div>
	</div>

	<!-- Avatar with Icons -->
	<div class="tutor-mb-6">
		<h3 class="tutor-mb-4">With Icons</h3>
		<div class="tutor-rounded-lg tutor-shadow-sm tutor-p-6" style="display: flex; align-items: center; gap: 20px;">
			<div class="tutor-avatar tutor-avatar-icon">
				<?php tutor_utils()->render_svg_icon( Icon::USER, 24, 24 ); ?>
			</div>
			
			<div class="tutor-avatar tutor-avatar-icon tutor-avatar-md">
				<?php tutor_utils()->render_svg_icon( Icon::USER, 24, 24 ); ?>
			</div>
			
			<div class="tutor-avatar tutor-avatar-icon tutor-avatar-lg">
				<?php tutor_utils()->render_svg_icon( Icon::USER_CIRCLE, 32, 32 ); ?>
			</div>
			
			<div class="tutor-avatar tutor-avatar-icon tutor-avatar-xl">
				<?php tutor_utils()->render_svg_icon( Icon::USER, 40, 40 ); ?>
			</div>
		</div>
	</div>

	<!-- Square Avatars -->
	<div class="tutor-mb-6">
		<h3 class="tutor-mb-4">Square Variants</h3>
		<div class="tutor-rounded-lg tutor-shadow-sm tutor-p-6" style="display: flex; align-items: center; gap: 20px;">
			<div class="tutor-avatar tutor-avatar-square tutor-avatar-sm">
				<img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" alt="User Avatar" class="tutor-avatar-image">
			</div>
			
			<div class="tutor-avatar tutor-avatar-square tutor-avatar-md">
				<span class="tutor-avatar-initials">JS</span>
			</div>
			
			<div class="tutor-avatar tutor-avatar-square tutor-avatar-lg">
				<img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=80&h=80&fit=crop&crop=face" alt="User Avatar" class="tutor-avatar-image">
			</div>

			<div class="tutor-avatar tutor-avatar-square tutor-avatar-xl">
				<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
			</div>
			
			<div class="tutor-avatar tutor-avatar-square tutor-avatar-xxl">
				<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
			</div>
		</div>
	</div>
</div>
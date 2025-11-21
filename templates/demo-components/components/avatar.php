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

			<!-- 20 -->
			<div class="tutor-avatar tutor-avatar-20">
				<img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" alt="User Avatar" class="tutor-avatar-image">
			</div>

			<!-- 32 -->
			<div class="tutor-avatar tutor-avatar-32">
				<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
			</div>

			<!-- 40 -->
			<div class="tutor-avatar tutor-avatar-40">
				<img src="https://i.pravatar.cc/150?u=a042581f4e29026704d" alt="User Avatar" class="tutor-avatar-image">
			</div>

			<!-- 48 -->
			<div class="tutor-avatar tutor-avatar-48">
				<img src="https://i.pravatar.cc/150?u=a042581f4e29026704d" alt="User Avatar" class="tutor-avatar-image">
			</div>

			<!-- 56 -->
			<div class="tutor-avatar tutor-avatar-56">
				<img src="https://i.pravatar.cc/150?u=a042581f4e29026704d" alt="User Avatar" class="tutor-avatar-image">
			</div>

			<!-- 64 -->
			<div class="tutor-avatar tutor-avatar-64">
				<img src="https://i.pravatar.cc/150?u=a04258114e29026302d" alt="User Avatar" class="tutor-avatar-image">
			</div>

			<!-- 80 -->
			<div class="tutor-avatar tutor-avatar-80">
				<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
			</div>

			<!-- 104 -->
			<div class="tutor-avatar tutor-avatar-104">
				<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
			</div>
		</div>
	</div>

	<!-- Avatar with Initials -->
	<div class="tutor-mb-6">
		<h3 class="tutor-mb-4">With Initials</h3>
		<div class="tutor-rounded-lg tutor-shadow-sm tutor-p-6" style="display: flex; align-items: center; gap: 20px;">
			<div class="tutor-avatar tutor-avatar-20">
				<span class="tutor-avatar-initials">JD</span>
			</div>

			<div class="tutor-avatar tutor-avatar-32">
				<span class="tutor-avatar-initials">JS</span>
			</div>

			<div class="tutor-avatar tutor-avatar-40">
				<span class="tutor-avatar-initials">MB</span>
			</div>

			<div class="tutor-avatar tutor-avatar-48">
				<span class="tutor-avatar-initials">MB</span>
			</div>

			<div class="tutor-avatar tutor-avatar-56">
				<span class="tutor-avatar-initials">MB</span>
			</div>

			<div class="tutor-avatar tutor-avatar-64">
				<span class="tutor-avatar-initials">AL</span>
			</div>

			<div class="tutor-avatar tutor-avatar-80">
				<span class="tutor-avatar-initials">KW</span>
			</div>

			<div class="tutor-avatar tutor-avatar-104">
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

			<div class="tutor-avatar tutor-avatar-icon tutor-avatar-48">
				<?php tutor_utils()->render_svg_icon( Icon::USER, 24, 24 ); ?>
			</div>

			<div class="tutor-avatar tutor-avatar-icon tutor-avatar-64">
				<?php tutor_utils()->render_svg_icon( Icon::USER_CIRCLE, 32, 32 ); ?>
			</div>

			<div class="tutor-avatar tutor-avatar-icon tutor-avatar-80">
				<?php tutor_utils()->render_svg_icon( Icon::USER, 40, 40 ); ?>
			</div>
		</div>
	</div>

	<!-- Square Avatars -->
	<div class="tutor-mb-6">
		<h3 class="tutor-mb-4">Square Variants</h3>
		<div class="tutor-rounded-lg tutor-shadow-sm tutor-p-6" style="display: flex; align-items: center; gap: 20px;">
			<div class="tutor-avatar tutor-avatar-square tutor-avatar-32">
				<img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" alt="User Avatar" class="tutor-avatar-image">
			</div>

			<div class="tutor-avatar tutor-avatar-square tutor-avatar-48">
				<span class="tutor-avatar-initials">JS</span>
			</div>

			<div class="tutor-avatar tutor-avatar-square tutor-avatar-64">
				<img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=80&h=80&fit=crop&crop=face" alt="User Avatar" class="tutor-avatar-image">
			</div>

			<div class="tutor-avatar tutor-avatar-square tutor-avatar-80">
				<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
			</div>

			<div class="tutor-avatar tutor-avatar-square tutor-avatar-104">
				<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
			</div>
		</div>
	</div>

	<!-- border avatar  -->
	<div style="background: #3e64de;padding: 20px;" >
		<div class="tutor-mb-6">
			<h3 class="tutor-mb-4">Border Variants</h3>
			<div class="tutor-rounded-lg tutor-shadow-sm tutor-p-6" style="display: flex; align-items: center; gap: 20px;">
				<div class="tutor-avatar tutor-avatar-border tutor-avatar-20">
					<img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" alt="User Avatar" class="tutor-avatar-image">
				</div>

				<div class="tutor-avatar tutor-avatar-border tutor-avatar-32">
					<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
				</div>

				<div class="tutor-avatar tutor-avatar-border tutor-avatar-48">
					<img src="https://i.pravatar.cc/150?u=a042581f4e29026704d" alt="User Avatar" class="tutor-avatar-image">
				</div>

				<div class="tutor-avatar tutor-avatar-border tutor-avatar-64">
					<img src="https://i.pravatar.cc/150?u=a04258114e29026302d" alt="User Avatar" class="tutor-avatar-image">
				</div>

				<div class="tutor-avatar tutor-avatar-border tutor-avatar-80">
					<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
				</div>

				<div class="tutor-avatar tutor-avatar-border tutor-avatar-104">
					<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
				</div>
			</div>
		</div>

		<!-- Square Border Avatars -->
		<div class="tutor-mb-6">
			<h3 class="tutor-mb-4">Square Border Variants</h3>
			<div class="tutor-rounded-lg tutor-shadow-sm tutor-p-6" style="display: flex; align-items: center; gap: 20px;">
				<div class="tutor-avatar tutor-avatar-square tutor-avatar-border tutor-avatar-32">
					<img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" alt="User Avatar" class="tutor-avatar-image">
				</div>

				<div class="tutor-avatar tutor-avatar-square tutor-avatar-border tutor-avatar-48">
					<span class="tutor-avatar-initials">JS</span>
				</div>

				<div class="tutor-avatar tutor-avatar-square tutor-avatar-border tutor-avatar-64">
					<img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=80&h=80&fit=crop&crop=face" alt="User Avatar" class="tutor-avatar-image">
				</div>

				<div class="tutor-avatar tutor-avatar-square tutor-avatar-border tutor-avatar-80">
					<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
				</div>

				<div class="tutor-avatar tutor-avatar-square tutor-avatar-border tutor-avatar-104">
					<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="User Avatar" class="tutor-avatar-image">
				</div>
			</div>
		</div>
	</div>
</div>

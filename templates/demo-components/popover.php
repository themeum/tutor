<?php
/**
 * Popover component documentation
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

?>
<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<h1 class="tutor-text-2xl tutor-font-bold tutor-mb-6">Popover Component</h1>

	<!-- Basic Popover -->
	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Basic Popover</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Click the button to show a basic popover with default settings.
		</p>

		<div x-data="tutorPopover({ placement: 'bottom-start' })">
			<button 
				x-ref="trigger"
				@click="toggle()"
				class="tutor-btn tutor-btn-primary"
			>
				Show Popover
			</button>

			<div 
				x-ref="content"
				x-show="open"
				x-cloak
				@click.outside="handleClickOutside()"
				class="tutor-popover tutor-popover-top"
			>
				<div class="tutor-popover-header">
					<h3 class="tutor-popover-title">Popover Title</h3>
					<button @click="hide()" class="tutor-popover-close">
						<?php tutor_utils()->render_svg_icon( Icon::CROSS, 14, 14 ); ?>
					</button>
				</div>
				<div class="tutor-popover-body">
					<p>This is a basic popover with some content. It can contain any HTML content you need.</p>
				</div>
			</div>
		</div>
	</div>

	<!-- Placement Variations -->
	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Placement Variations</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Popovers can be positioned in different directions relative to the trigger.
		</p>

		<div class="tutor-flex tutor-gap-4 tutor-flex-wrap">
			<!-- Top -->
			<div x-data="tutorPopover({ placement: 'top' })">
				<button 
					x-ref="trigger"
					@click="toggle()"
					class="tutor-btn tutor-btn-secondary"
				>
					Top
				</button>
				<div 
					x-ref="content"
					x-show="open"
					x-cloak
					@click.outside="handleClickOutside()"
					class="tutor-popover tutor-popover-top"
				>
					<div class="tutor-popover-body">
						<div>Top positioned popover</div>
					</div>
				</div>
			</div>

			<!-- Bottom -->
			<div x-data="tutorPopover({ placement: 'bottom' })">
				<button 
					x-ref="trigger"
					@click="toggle()"
					class="tutor-btn tutor-btn-secondary"
				>
					Bottom
				</button>
				<div 
					x-ref="content"
					x-show="open"
					x-cloak
					@click.outside="handleClickOutside()"
					class="tutor-popover tutor-popover-bottom"
				>
					<div class="tutor-popover-body">
						<div>Bottom positioned popover</div>
					</div>
				</div>
			</div>

			<!-- Left -->
			<div x-data="tutorPopover({ placement: 'left' })">
				<button 
					x-ref="trigger"
					@click="toggle()"
					class="tutor-btn tutor-btn-secondary"
				>
					Left
				</button>
				<div 
					x-ref="content"
					x-show="open"
					x-cloak
					@click.outside="handleClickOutside()"
					class="tutor-popover tutor-popover-left"
				>
					<div class="tutor-popover-body">
						<div>Left positioned popover</div>
					</div>
				</div>
			</div>

			<!-- Right -->
			<div x-data="tutorPopover({ placement: 'right' })">
				<button 
					x-ref="trigger"
					@click="toggle()"
					class="tutor-btn tutor-btn-secondary"
				>
					Right
				</button>
				<div 
					x-ref="content"
					x-show="open"
					x-cloak
					@click.outside="handleClickOutside()"
					class="tutor-popover tutor-popover-right"
				>
					<div class="tutor-popover-body">
						<div>Right positioned popover</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- With Footer Actions -->
	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">With Footer Actions</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Popovers can include footer actions for user interactions.
		</p>

		<div x-data="tutorPopover({ placement: 'bottom-start' })">
			<button 
				x-ref="trigger"
				@click="toggle()"
				class="tutor-btn tutor-btn-primary"
			>
				Show Confirmation
			</button>

			<div 
				x-ref="content"
				x-show="open"
				x-cloak
				@click.outside="handleClickOutside()"
				class="tutor-popover tutor-popover-bottom"
			>
				<div class="tutor-popover-header">
					<h3 class="tutor-popover-title">Confirm Action</h3>
				</div>
				<div class="tutor-popover-body">
					<p>Are you sure you want to delete this item? This action cannot be undone.</p>
				</div>
				<div class="tutor-popover-footer">
					<button @click="hide()" class="tutor-btn tutor-btn-ghost tutor-btn-sm">Cancel</button>
					<button @click="hide()" class="tutor-btn tutor-btn-destructive tutor-btn-sm">Delete</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Three dots Kebab menu -->
	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Three dots Kebab menu</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Three dots Kebab menu example with popover
		</p>

		<div x-data="tutorPopover({ placement: 'bottom-start' })">
			<button 
				x-ref="trigger"
				@click="toggle()"
				class="tutor-btn tutor-btn-text"
			>
				<?php tutor_utils()->render_svg_icon( Icon::THREE_DOTS_VERTICAL, 24, 24 ); ?>
			</button>

			<div 
				x-ref="content"
				x-show="open"
				x-cloak
				@click.outside="handleClickOutside()"
				class="tutor-popover tutor-popover-bottom"
			>
				<div class="tutor-popover-menu">
					<button class="tutor-popover-menu-item">
						<?php tutor_utils()->render_svg_icon( Icon::EDIT_2 ); ?> Edit
					</button>
					<button class="tutor-popover-menu-item">
						<?php tutor_utils()->render_svg_icon( Icon::DELETE_2 ); ?> Delete
					</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Usage Example -->
	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Usage</h2>
		<div class="tutor-bg-gray-50 tutor-p-4 tutor-rounded-lg">
			<pre class="tutor-text-sm tutor-text-gray-700"><code>&lt;div x-data="tutorPopover({
	placement: 'top',
	offset: 8,
	onShow: () => console.log('Popover shown'),
	onHide: () => console.log('Popover hidden')
})"&gt;
	&lt;button x-ref="trigger" @click="toggle()"&gt;
		Trigger Button
	&lt;/button&gt;

	&lt;div 
		x-ref="content"
		x-show="open"
		x-cloak
		@click.outside="handleClickOutside()"
		class="tutor-popover tutor-popover-top"
	&gt;
		&lt;div class="tutor-popover-body"&gt;
			Popover content
		&lt;/div&gt;
	&lt;/div&gt;
&lt;/div&gt;</code></pre>
		</div>
	</div>
</section>

<?php
/**
 * Toast component documentation
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

?>
<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<h1 class="tutor-text-2xl tutor-font-bold tutor-mb-6">Toast Notifications</h1>

	<!-- Introduction -->
	<div class="tutor-mb-8">
		<p class="tutor-text-gray-600 tutor-mb-4">
			Toast notifications provide non-intrusive feedback to users. The toast container is automatically injected into the DOM when you trigger your first toast - no manual setup required!
		</p>
		<div class="tutor-alert tutor-info tutor-mb-4">
			<div class="tutor-alert-text">
				<span class="tutor-alert-icon tutor-fs-4 tutor-icon-circle-info tutor-mr-12"></span>
				<span>
					All toasts include a default title based on their type (Success, Error, Warning, Info). You can customize this title if needed.
				</span>
			</div>
		</div>
	</div>

	<!-- Toast Variants -->
	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Toast Variants</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Four toast variants for different notification types: Default (Info), Success, Warning, and Error.
		</p>
		<div class="tutor-p-6 tutor-bg-gray-50 tutor-rounded-md">
			<div class="tutor-flex tutor-items-center tutor-gap-3 tutor-flex-wrap">
				<button 
					class="tutor-btn tutor-btn-primary"
					onclick="TutorCore.toast.info('This is an informational message')"
				>
					Show Info Toast
				</button>
				<button 
					class="tutor-btn tutor-btn-primary"
					onclick="TutorCore.toast.success('Operation completed successfully!')"
				>
					Show Success Toast
				</button>
				<button 
					class="tutor-btn tutor-btn-primary"
					onclick="TutorCore.toast.warning('Please review your settings')"
				>
					Show Warning Toast
				</button>
				<button 
					class="tutor-btn tutor-btn-primary"
					onclick="TutorCore.toast.error('An error occurred. Please try again.')"
				>
					Show Error Toast
				</button>
			</div>
		</div>
	</div>

	<!-- Toast with Title and Message -->
	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Toast with Custom Title</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Toast notifications automatically show a title based on the variant (Success, Error, Warning, Info). You can override this with a custom title.
		</p>
		<div class="tutor-p-6 tutor-bg-gray-50 tutor-rounded-md">
			<div class="tutor-flex tutor-items-center tutor-gap-3 tutor-flex-wrap">
				<button 
					class="tutor-btn tutor-btn-primary"
					onclick="TutorCore.toast.show('Your profile has been updated with the latest changes.', { type: 'success', title: 'Profile Updated' })"
				>
					Custom Title - Success
				</button>
				<button 
					class="tutor-btn tutor-btn-primary"
					onclick="TutorCore.toast.show('Your session will expire in 5 minutes. Please save your work.', { type: 'warning', title: 'Session Expiring' })"
				>
					Custom Title - Warning
				</button>
			</div>
		</div>
	</div>

	<!-- Custom Duration -->
	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Custom Duration</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Control how long toasts stay visible. Default is 5 seconds.
		</p>
		<div class="tutor-p-6 tutor-bg-gray-50 tutor-rounded-md">
			<div class="tutor-flex tutor-items-center tutor-gap-3 tutor-flex-wrap">
				<button 
					class="tutor-btn tutor-btn-primary"
					onclick="TutorCore.toast.success('Quick toast (2s)', 2000)"
				>
					2 Seconds
				</button>
				<button 
					class="tutor-btn tutor-btn-primary"
					onclick="TutorCore.toast.info('Normal toast (5s)', 5000)"
				>
					5 Seconds (Default)
				</button>
				<button 
					class="tutor-btn tutor-btn-primary"
					onclick="TutorCore.toast.warning('Long toast (10s)', 10000)"
				>
					10 Seconds
				</button>
				<button 
					class="tutor-btn tutor-btn-primary"
					onclick="TutorCore.toast.show('This toast stays until closed', { type: 'info', duration: 0 })"
				>
					Persistent (No Auto-dismiss)
				</button>
			</div>
		</div>
	</div>

	<!-- Multiple Toasts -->
	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Stacking Toasts</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Multiple toasts stack vertically and can be shown simultaneously.
		</p>
		<div class="tutor-p-6 tutor-bg-gray-50 tutor-rounded-md">
			<div class="tutor-flex tutor-items-center tutor-gap-3 tutor-flex-wrap">
				<button 
					class="tutor-btn tutor-btn-primary"
					onclick="
						TutorCore.toast.info('First notification');
						setTimeout(() => TutorCore.toast.success('Second notification'), 300);
						setTimeout(() => TutorCore.toast.warning('Third notification'), 600);
					"
				>
					Show Multiple Toasts
				</button>
				<button 
					class="tutor-btn tutor-btn-destructive"
					onclick="TutorCore.toast.clear()"
				>
					Clear All Toasts
				</button>
			</div>
		</div>
	</div>

	<!-- Usage Code -->
	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Usage</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			How to use toast notifications in your code.
		</p>
		<div class="tutor-p-6 tutor-bg-gray-50 tutor-rounded-md">
			<pre class="tutor-bg-white tutor-p-4 tutor-rounded tutor-overflow-x-auto"><code>// Show a success toast (default title: "Success")
TutorCore.toast.success('Operation completed!');

// Show an error toast (default title: "Error")
TutorCore.toast.error('Something went wrong');

// Show a warning toast (default title: "Warning")
TutorCore.toast.warning('Please be careful');

// Show an info toast (default title: "Info")
TutorCore.toast.info('Here is some information');

// Custom title
TutorCore.toast.show('Your profile has been updated', { 
  type: 'success', 
  title: 'Profile Updated' 
});

// Custom duration (in milliseconds)
TutorCore.toast.success('Quick message', 2000);

// Persistent toast (doesn't auto-dismiss)
TutorCore.toast.show('Important message', { type: 'warning', duration: 0 });

// Clear all toasts
TutorCore.toast.clear();</code></pre>
		</div>
	</div>
</section>

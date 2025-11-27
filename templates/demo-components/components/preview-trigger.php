<?php
/**
 * Preview Trigger Component - Usage Example
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

$preview_data = array(
	'title'          => 'Camera Skills & Photo Theory',
	'url'            => '#',
	'thumbnail'      => 'https://workademy.tutorlms.io/wp-content/uploads/2025/09/Cloud-It-Ops_-Cloud-Fundamentals-for-Enterprise-Teams.webp',
	'instructor'     => 'John Doe',
	'instructor_url' => '#',
);

?>
<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<h1 class="tutor-text-2xl tutor-font-bold tutor-mb-6">Preview Trigger Examples</h1>

	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Course/Lesson Preview</h2>
		<div>
			I recently completed 
			<div
				x-data="tutorPreviewTrigger({ data: <?php echo esc_attr( wp_json_encode( $preview_data ) ); ?> })"
				x-ref="trigger"
				class="tutor-preview-trigger"
			>
				<span class="tutor-preview-trigger-text">Camera Skills & Photo Theory</span>
				<div 
					x-ref="content"
					x-show="open"
					x-cloak
					@click.outside="handleClickOutside()"
					class="tutor-popover tutor-preview-card"
				></div>
			</div>
			and it was amazing!
		</div>
	</div>

	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Usage</h2>
		<div class="tutor-bg-gray-50 tutor-p-4 tutor-rounded-lg">

		<pre class="tutor-text-sm tutor-text-gray-700"><code>&lt;div 
	x-data="tutorPreviewTrigger({ data: &lt;?php echo esc_attr( wp_json_encode( $preview_data ) ); ?&gt; })"
	x-ref="trigger"
	class="tutor-preview-trigger"
&gt;
	&lt;span class="tutor-preview-trigger-text" &gt;Course Title&lt;/span&gt;
	&lt;div 
		x-ref="content"
		x-show="open"
		x-cloak
		@click.outside="handleClickOutside()"
		class="tutor-popover tutor-preview-card"
	&gt;&lt;/div&gt;
&lt;/div&gt;</code></pre>
		</div>
	</div>

	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Preview Data Structure:</h2>
		<div class="tutor-bg-gray-50 tutor-p-4 tutor-rounded-lg">
		<pre class="tutor-text-sm tutor-text-gray-700"><code>{
	"title": "Course Title",
	"url": "https://example.com/course/123"
	"thumbnail": "https://example.com/image.jpg",
	"instructor": "Instructor Name",
	"instructor_url": "#",
}</code></pre>
		</div>
	</div>

	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Optional Props:</h2>

		<ul class="tutor-mb-8">
			<li><code>delay</code> - Hover delay in milliseconds (default: 300)</li>
			<li><code>placement</code> - Popover placement (default: 'bottom-start')</li>
			<li><code>offset</code> - Offset from trigger (default: 8)</li>
		</ul>

		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Behavior:</h2>
		<ul>
			<li><strong>Desktop:</strong> Hover over the text to show preview after delay</li>
			<li><strong>Mobile:</strong> Tap to toggle preview, tap outside to close</li>
			<li><strong>Keyboard:</strong> Press Escape to close preview</li>
		</ul>
	</div>
</section>

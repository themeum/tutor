<?php
/**
 * Preview Trigger Component - Usage Example
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

?>

<div class="tutor-container">
	<h2>Preview Trigger Examples</h2>

	<div style="padding: 40px; line-height: 2;">
		<h3>Course Preview</h3>
		<p>
			I recently completed 
			<span 
				x-data="tutorPreviewTrigger()"
				x-ref="trigger"
				class="tutor-preview-trigger" 
				data-tutor-preview="course" 
				data-tutor-preview-id="123"
				data-tutor-preview-delay="300"
			>
				Camera Skills & Photo Theory
				<div 
					x-ref="content"
					x-show="open"
					x-cloak
					@click.outside="handleClickOutside()"
					class="tutor-popover tutor-preview-card"
				>
					<div class="tutor-preview-card-loading" x-show="isLoading">Loading...</div>
				</div>
			</span>
			and it was amazing!
		</p>

		<h3>Lesson Preview</h3>
		<p>
			The lesson on 
			<span 
				x-data="tutorPreviewTrigger()"
				x-ref="trigger"
				class="tutor-preview-trigger" 
				data-tutor-preview="lesson" 
				data-tutor-preview-id="456"
			>
				Understanding Aperture
				<div 
					x-ref="content"
					x-show="open"
					x-cloak
					@click.outside="handleClickOutside()"
					class="tutor-popover tutor-preview-card"
				>
					<div class="tutor-preview-card-loading" x-show="isLoading">Loading...</div>
				</div>
			</span>
			was very helpful.
		</p>

		<h3>Multiple Previews</h3>
		<p>
			Check out these courses: 
			<span x-data="tutorPreviewTrigger()" x-ref="trigger" class="tutor-preview-trigger" data-tutor-preview="course" data-tutor-preview-id="1">
				Web Development
				<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()" class="tutor-popover tutor-preview-card">
					<div class="tutor-preview-card-loading" x-show="isLoading">Loading...</div>
				</div>
			</span>,
			<span x-data="tutorPreviewTrigger()" x-ref="trigger" class="tutor-preview-trigger" data-tutor-preview="course" data-tutor-preview-id="2">
				Graphic Design
				<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()" class="tutor-popover tutor-preview-card">
					<div class="tutor-preview-card-loading" x-show="isLoading">Loading...</div>
				</div>
			</span>, and
			<span x-data="tutorPreviewTrigger()" x-ref="trigger" class="tutor-preview-trigger" data-tutor-preview="course" data-tutor-preview-id="3">
				Digital Marketing
				<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()" class="tutor-popover tutor-preview-card">
					<div class="tutor-preview-card-loading" x-show="isLoading">Loading...</div>
				</div>
			</span>.
		</p>
	</div>

	<div style="background: #f5f5f5; padding: 20px; margin-top: 40px; border-radius: 8px;">
		<h3>Usage Instructions</h3>
		
		<h4>HTML Structure:</h4>
		<pre><code>&lt;span 
  x-data="tutorPreviewTrigger()"        <!-- Initialize component -->
  x-ref="trigger"                       <!-- Mark as trigger element -->
  class="tutor-preview-trigger" 
  data-tutor-preview="course"           <!-- Type: 'course' or 'lesson' -->
  data-tutor-preview-id="123"           <!-- ID of the course/lesson -->
  data-tutor-preview-delay="300"        <!-- Optional: Hover delay in ms (default: 300) -->
&gt;
  Course Title
  &lt;div 
    x-ref="content"                     <!-- Mark as content element -->
    x-show="open"                       <!-- Show when open -->
    x-cloak                             <!-- Hide until Alpine loads -->
    @click.outside="handleClickOutside()" <!-- Close on outside click -->
    class="tutor-popover tutor-preview-card"
  &gt;
    &lt;div class="tutor-preview-card-loading" x-show="isLoading"&gt;Loading...&lt;/div&gt;
  &lt;/div&gt;
&lt;/span&gt;</code></pre>

		<h4>Required Attributes:</h4>
		<ul>
			<li><code>class="tutor-preview-trigger"</code> - Identifies the element as a preview trigger</li>
			<li><code>data-tutor-preview</code> - Type of content: "course" or "lesson"</li>
			<li><code>data-tutor-preview-id</code> - The ID of the course or lesson</li>
		</ul>

		<h4>Optional Attributes:</h4>
		<ul>
			<li><code>data-tutor-preview-delay</code> - Hover delay in milliseconds (default: 300ms)</li>
		</ul>

		<h4>Behavior:</h4>
		<ul>
			<li><strong>Desktop:</strong> Hover over the text to show preview after delay</li>
			<li><strong>Mobile:</strong> Tap to toggle preview, tap outside to close</li>
			<li><strong>Keyboard:</strong> Press Escape to close preview</li>
		</ul>

		<h4>API Endpoint Required:</h4>
		<p>The component expects a REST API endpoint at:</p>
		<pre><code>/wp-json/tutor/v1/preview/{type}/{id}</code></pre>
		
		<p>Example response format:</p>
		<pre><code>{
  "type": "course",
  "title": "Camera Skills & Photo Theory",
  "excerpt": "Learn the fundamentals of photography...",
  "thumbnail": "https://example.com/image.jpg",
  "instructor": "John Doe",
  "students": 1234,
  "rating": 4.8,
  "url": "https://example.com/course/123"
}</code></pre>
	</div>
</div>

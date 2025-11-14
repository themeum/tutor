<?php
/**
 * Preview Trigger Component - Usage Example
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

// Sample preview data
$course_preview_data = array(
	'type'       => 'course',
	'title'      => 'Camera Skills & Photo Theory',
	'excerpt'    => 'Learn the fundamentals of photography including composition, lighting, and camera settings.',
	'thumbnail'  => 'https://workademy.tutorlms.io/wp-content/uploads/2025/09/Cloud-It-Ops_-Cloud-Fundamentals-for-Enterprise-Teams.webp',
	'instructor' => 'John Doe',
	'students'   => 1234,
	'rating'     => 4.8,
	'url'        => '#',
);

$lesson_preview_data = array(
	'type'       => 'lesson',
	'title'      => 'Understanding Aperture',
	'excerpt'    => 'Master the concept of aperture and how it affects depth of field in your photos.',
	'duration'   => '12 mins',
	'lessonType' => 'Video',
	'url'        => '#',
);

$design_course_data = array(
	'type'       => 'course',
	'title'      => 'Graphic Design Fundamentals',
	'excerpt'    => 'Master the principles of design, typography, and color theory.',
	'thumbnail'  => 'https://workademy.tutorlms.io/wp-content/uploads/2025/09/Cloud-It-Ops_-Cloud-Fundamentals-for-Enterprise-Teams.webp',
	'instructor' => 'Jane Smith',
	'students'   => 856,
	'rating'     => 4.6,
	'url'        => '#',
);

?>

<div class="tutor-container">
	<h2>Preview Trigger Examples (Props-based)</h2>

	<div style="padding: 40px; line-height: 2;">
		<h3>Course Preview</h3>
		<p>
			I recently completed 
			<span 
				x-data="tutorPreviewTrigger({ data: <?php echo esc_attr( wp_json_encode( $course_preview_data ) ); ?> })"
				x-ref="trigger"
				class="tutor-preview-trigger"
			>
				Camera Skills & Photo Theory
				<div 
					x-ref="content"
					x-show="open"
					x-cloak
					@click.outside="handleClickOutside()"
					class="tutor-popover tutor-preview-card"
				></div>
			</span>
			and it was amazing!
		</p>

		<h3>Lesson Preview</h3>
		<p>
			The lesson on 
			<span 
				x-data="tutorPreviewTrigger({ data: <?php echo esc_attr( wp_json_encode( $lesson_preview_data ) ); ?> })"
				x-ref="trigger"
				class="tutor-preview-trigger"
			>
				Understanding Aperture
				<div 
					x-ref="content"
					x-show="open"
					x-cloak
					@click.outside="handleClickOutside()"
					class="tutor-popover tutor-preview-card"
				></div>
			</span>
			was very helpful.
		</p>

		<h3>Data Attribute Method</h3>
		<p>
			You can also use 
			<span 
				x-data="tutorPreviewTrigger()"
				x-ref="trigger"
				class="tutor-preview-trigger"
				data-tutor-preview-data='<?php echo esc_attr( wp_json_encode( $design_course_data ) ); ?>'
			>
				data attributes
				<div 
					x-ref="content"
					x-show="open"
					x-cloak
					@click.outside="handleClickOutside()"
					class="tutor-popover tutor-preview-card"
				></div>
			</span>
			for simpler usage.
		</p>
	</div>

	<div style="background: #f5f5f5; padding: 20px; margin-top: 40px; border-radius: 8px;">
		<h3>Usage Instructions</h3>
		
		<h4>Method 1: Props-based (Recommended)</h4>
		<pre><code>&lt;?php
$preview_data = array(
  'type'       => 'course',
  'title'      => 'Course Title',
  'excerpt'    => 'Course description...',
  'thumbnail'  => 'https://example.com/image.jpg',
  'instructor' => 'John Doe',
  'students'   => 1234,
  'rating'     => 4.8,
  'url'        => '#',
);
?&gt;

&lt;span 
  x-data="tutorPreviewTrigger({ data: &lt;?php echo esc_attr( wp_json_encode( $preview_data ) ); ?&gt; })"
  x-ref="trigger"
  class="tutor-preview-trigger"
&gt;
  Course Title
  &lt;div 
    x-ref="content"
    x-show="open"
    x-cloak
    @click.outside="handleClickOutside()"
    class="tutor-popover tutor-preview-card"
  &gt;&lt;/div&gt;
&lt;/span&gt;</code></pre>

		<h4>Method 2: Data Attribute</h4>
		<pre><code>&lt;span 
  x-data="tutorPreviewTrigger()"
  x-ref="trigger"
  class="tutor-preview-trigger"
  data-tutor-preview-data='&lt;?php echo esc_attr( wp_json_encode( $preview_data ) ); ?&gt;'
&gt;
  Course Title
  &lt;div 
    x-ref="content"
    x-show="open"
    x-cloak
    @click.outside="handleClickOutside()"
    class="tutor-popover tutor-preview-card"
  &gt;&lt;/div&gt;
&lt;/span&gt;</code></pre>

		<h4>Preview Data Structure:</h4>
		<p><strong>For Courses:</strong></p>
		<pre><code>{
  "type": "course",
  "title": "Course Title",
  "excerpt": "Course description",
  "thumbnail": "https://example.com/image.jpg",
  "instructor": "Instructor Name",
  "students": 1234,
  "rating": 4.8,
  "url": "https://example.com/course/123"
}</code></pre>

		<p><strong>For Lessons:</strong></p>
		<pre><code>{
  "type": "lesson",
  "title": "Lesson Title",
  "excerpt": "Lesson description",
  "duration": "12 mins",
  "lessonType": "Video",
  "url": "https://example.com/lesson/456"
}</code></pre>

		<h4>Optional Props:</h4>
		<ul>
			<li><code>delay</code> - Hover delay in milliseconds (default: 300)</li>
			<li><code>placement</code> - Popover placement (default: 'bottom-start')</li>
			<li><code>offset</code> - Offset from trigger (default: 8)</li>
		</ul>

		<h4>Behavior:</h4>
		<ul>
			<li><strong>Desktop:</strong> Hover over the text to show preview after delay</li>
			<li><strong>Mobile:</strong> Tap to toggle preview, tap outside to close</li>
			<li><strong>Keyboard:</strong> Press Escape to close preview</li>
		</ul>
	</div>
</div>

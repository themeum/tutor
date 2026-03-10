<?php
/**
 * WP Editor Demo
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\WPEditor;
?>
<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<h1 class="tutor-text-2xl tutor-font-bold tutor-mb-3">WP Editor</h1>
	<p class="tutor-text-gray-600 tutor-mb-6">
		WordPress editor demo using <code class="tutor-bg-gray-200 tutor-px-2 tutor-py-1 tutor-rounded">tutorWPEditor</code> from <code class="tutor-bg-gray-200 tutor-px-2 tutor-py-1 tutor-rounded">assets/core/ts/components/wp-editor.ts</code>.
	</p>

	<?php if ( function_exists( 'wp_editor' ) ) : ?>
		<form
			x-data="tutorForm({ id: 'wp-editor-demo-form', mode: 'onBlur', shouldFocusError: true })"
			x-bind="getFormBindings()"
			@submit="handleSubmit(
				(data) => { alert('WP Editor form submitted!\n' + JSON.stringify(data, null, 2)); },
				(errors) => { console.log('WP Editor form errors:', errors); }
			)($event)"
		>
			<div class="tutor-mb-8">
				<?php
				WPEditor::make()
					->name( 'course_description' )
					->id( 'tutor_demo_wp_editor_full' )
					->label( 'Course Description' )
					->placeholder( 'Write your course description here...' )
					->required()
					->help_text( 'Try blur validation: this field requires at least 20 characters.' )
					->content( '<p>Write your course announcement here...</p>' )
					->editor_config(
						array(
							'media_buttons' => true,
							'teeny'         => false,
							'editor_height' => 220,
						)
					)
					->attr( 'x-bind', "register('course_description', { required: 'Course description is required', minLength: { value: 20, message: 'Please write at least 20 characters' } })" )
					->render();
				?>
			</div>

			<div class="tutor-mb-8">
				<?php
				WPEditor::make()
					->name( 'quick_note' )
					->id( 'tutor_demo_wp_editor_compact' )
					->label( 'Quick Note' )
					->placeholder( 'Add a short note for learners...' )
					->help_text( 'Compact editor with teeny toolbar enabled.' )
					->content( '<p>Quick note for learners...</p>' )
					->editor_config(
						array(
							'media_buttons' => false,
							'teeny'         => true,
							'editor_height' => 160,
						)
					)
					->attr( 'x-bind', "register('quick_note')" )
					->render();
				?>
			</div>

			<div class="tutor-flex tutor-gap-3">
				<button
					type="submit"
					class="tutor-btn tutor-btn-primary"
					:disabled="isSubmitting"
					:class="{ 'tutor-btn-loading': isSubmitting }"
				>
					<span>Submit</span>
				</button>
				<button
					type="button"
					class="tutor-btn tutor-btn-outline"
					@click="reset()"
				>
					Reset
				</button>
			</div>
		</form>
	<?php else : ?>
		<p class="tutor-text-danger">The <code>wp_editor()</code> function is unavailable in this context.</p>
	<?php endif; ?>
</section>

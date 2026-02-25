<?php
/**
 * Demo: Time Input
 *
 * @package TutorLMS\Templates
 */

use Tutor\Components\InputField;
use Tutor\Components\Constants\InputType;
use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

?>

<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<div class="tutor-p-6 tutor-flex tutor-flex-column tutor-gap-6">
		<h3 class="tutor-text-xl tutor-font-medium">
			<?php echo esc_html__( 'Time Input Demo', 'tutor' ); ?>
		</h3>

		<div class="tutor-flex tutor-flex-column tutor-gap-3 tutor-max-w-sm">
			<h4 class="tutor-text-base tutor-font-medium">
				<?php echo esc_html__( 'Default (30 minute interval)', 'tutor' ); ?>
			</h4>
			<?php
			InputField::make()
				->type( InputType::TIME )
				->name( 'start_time' )
				->value( '02:30 PM' )
				->placeholder( __( 'Select start time', 'tutor' ) )
				->render();
			?>
		</div>

		<div class="tutor-flex tutor-flex-column tutor-gap-3 tutor-max-w-sm">
			<h4 class="tutor-text-base tutor-font-medium">
				<?php echo esc_html__( '15 minute interval', 'tutor' ); ?>
			</h4>
			<?php
			InputField::make()
				->type( InputType::TIME )
				->name( 'meeting_time' )
				->interval( 15 )
				->placeholder( __( 'Select meeting time', 'tutor' ) )
				->render();
			?>
		</div>

		<div class="tutor-flex tutor-flex-column tutor-gap-3 tutor-max-w-sm">
			<h4 class="tutor-text-base tutor-font-medium">
				<?php echo esc_html__( 'InputField Builder Usage', 'tutor' ); ?>
			</h4>
			<p class="tutor-text-gray-600 tutor-mb-2">
				<?php echo esc_html__( 'Using InputField with InputType::TIME.', 'tutor' ); ?>
			</p>
			<?php
			InputField::make()
				->type( InputType::TIME )
				->name( 'builder_class_time' )
				->label( __( 'Class Time (Builder)', 'tutor' ) )
				->left_icon( tutor_utils()->get_svg_icon( Icon::CLOCK, 20, 20 ) )
				->placeholder( __( 'Select class time', 'tutor' ) )
				->required( __( 'Class time is required', 'tutor' ) )
				->clearable()
				->help_text( __( 'This is rendered through InputField::make()', 'tutor' ) )
				->render();
			?>
		</div>

		<div class="tutor-flex tutor-flex-column tutor-gap-3 tutor-max-w-sm">
			<h4 class="tutor-text-base tutor-font-medium">
				<?php echo esc_html__( 'Form Integration', 'tutor' ); ?>
			</h4>
			<p class="tutor-text-gray-600 tutor-mb-2">
				<?php echo esc_html__( 'Time input integrates with tutorForm validation. Try submitting without selecting a time.', 'tutor' ); ?>
			</p>

			<form
				x-data="tutorForm({ id: 'time-input-form', mode: 'onBlur', shouldFocusError: true })"
				x-bind="getFormBindings()"
				@submit="handleSubmit(
					(data) => { 
						alert('Form submitted!\\n' + JSON.stringify(data, null, 2)); 
					},
					(errors) => { 
						console.log('Validation errors:', errors); 
					}
				)($event)"
				class="tutor-max-w-md tutor-space-y-5"
			>
				<?php
				InputField::make()
					->type( InputType::TIME )
					->name( 'demo_class_time' )
					->label( __( 'Class Time', 'tutor' ) )
					->placeholder( __( 'Select class time', 'tutor' ) )
					->clearable()
					->required( __( 'Class time is required', 'tutor' ) )
					->help_text( __( 'Choose when the class starts', 'tutor' ) )
					->render();
				?>

				<div class="tutor-flex tutor-gap-3">
					<button type="submit" class="tutor-btn tutor-btn-primary">
						<?php echo esc_html__( 'Submit', 'tutor' ); ?>
					</button>
					<button type="button" @click="reset()" class="tutor-btn tutor-btn-outline">
						<?php echo esc_html__( 'Reset', 'tutor' ); ?>
					</button>
				</div>

				<div class="tutor-mt-6 tutor-p-4 tutor-bg-gray-100 tutor-rounded-lg tutor-text-sm">
					<h4 class="tutor-font-semibold tutor-mb-2"><?php echo esc_html__( 'Form State', 'tutor' ); ?></h4>
					<div><strong><?php echo esc_html__( 'Values:', 'tutor' ); ?></strong> <span x-text="JSON.stringify(values, null, 2)"></span></div>
					<div><strong><?php echo esc_html__( 'Errors:', 'tutor' ); ?></strong> <span x-text="JSON.stringify(errors, null, 2)"></span></div>
					<div><strong><?php echo esc_html__( 'Valid:', 'tutor' ); ?></strong> <span x-text="isValid"></span></div>
				</div>
			</form>
		</div>
	</div>
</section>

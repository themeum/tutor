<?php
/**
 * Inputs
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

?>
<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<h1 class="tutor-text-2xl tutor-font-bold tutor-mb-6">Inputs</h1>
	<p class="tutor-text-gray-600 tutor-mb-4">
		Alpine.js input field components with react-hook-form compatible API. HTML validation is disabled to use custom validation logic.
	</p>

	<div class="tutor-mb-8 tutor-grid tutor-grid-cols-2 tutor-gap-10">
		<!-- Input Field -->
		<div>
			<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Input Field</h2>
		
			<div class="tutor-input-field">
				<label for="name" class="tutor-label tutor-label-required">Full Name</label>
				<div class="tutor-input-wrapper">
					<input 
						type="text"
						id="name"
						placeholder="Enter your full name"
						class="tutor-input tutor-input-content-clear"
					>
					<button 
						type="button"
						class="tutor-input-clear-button"
						aria-label="Clear input"
					>
						<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ) ); ?>
					</button>
				</div>
				<div class="tutor-help-text">This is a helper text.</div>
			</div>
			<div class="tutor-input-field">
				<label for="name" class="tutor-label tutor-label-required">Full Name (Disabled)</label>
				<div class="tutor-input-wrapper">
					<input 
						type="text"
						id="name"
						placeholder="Enter your full name"
						disabled
						class="tutor-input"
					>
				</div>
				<div class="tutor-help-text">This field is disabled.</div>
			</div>
			<div class="tutor-input-field tutor-input-field-error">
				<label for="name" class="tutor-label tutor-label-required">Full Name (Error)</label>
				<div class="tutor-input-wrapper">
					<input 
						type="text"
						id="name"
						placeholder="Enter your full name"
						class="tutor-input"
					>
				</div>
				<div class="tutor-error-text" role="alert" aria-live="polite">
					This field is required.
				</div>
			</div>
		</div>

		<div>
			<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Input Field With Content</h2>
		
			<div class="tutor-input-field">
				<label for="name" class="tutor-label tutor-label-required">Full Name</label>
				<div class="tutor-input-wrapper">
					<input 
						type="text"
						id="name"
						placeholder="Enter your full name"
						class="tutor-input tutor-input-content-left tutor-input-content-clear"
					>
					<div class="tutor-input-content tutor-input-content-left">
						<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::EMAIL, 16, 16 ) ); ?>
					</div>
					<button 
						type="button"
						class="tutor-input-clear-button"
						aria-label="Clear input"
					>
						<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ) ); ?>
					</button>
				</div>
				<div class="tutor-help-text">This is a helper text.</div>
			</div>
			<div class="tutor-input-field">
				<label for="name" class="tutor-label tutor-label-required">Full Name (Disabled)</label>
				<div class="tutor-input-wrapper">
					<input 
						type="text"
						id="name"
						disabled
						placeholder="Enter your full name"
						class="tutor-input tutor-input-content-left"
					>
					<div class="tutor-input-content tutor-input-content-left">
						<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::EMAIL, 16, 16 ) ); ?>
					</div>
				</div>
				<div class="tutor-help-text">This field is disabled.</div>
			</div>
			<div class="tutor-input-field tutor-input-field-error">
				<label for="name" class="tutor-label tutor-label-required">Full Name (Error)</label>
				<div class="tutor-input-wrapper">
					<input 
						type="text"
						id="name"
						placeholder="Enter your full name"
						class="tutor-input tutor-input-content-left"
					>
					<div class="tutor-input-content tutor-input-content-left">
						<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::EMAIL, 16, 16 ) ); ?>
					</div>
				</div>
				<div class="tutor-error-text" role="alert" aria-live="polite">
					This field is required.
				</div>
			</div>
		</div>
	</div>

	<div class="tutor-mb-8 tutor-grid tutor-grid-cols-2 tutor-gap-10">
		<!-- Input Field -->
		<div>
			<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Textarea</h2>
		
			<div class="tutor-input-field">
				<label for="name" class="tutor-label tutor-label-required">Bio</label>
				<div class="tutor-input-wrapper">
					<textarea 
						type="text"
						id="name"
						placeholder="Enter your full name"
						class="tutor-input tutor-text-area tutor-input-content-clear"
					></textarea>
					<button 
						type="button"
						class="tutor-input-clear-button"
						aria-label="Clear input"
					>
						<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ) ); ?>
					</button>
				</div>
				<div class="tutor-help-text">This is a helper text.</div>
			</div>
			<div class="tutor-input-field">
				<label for="name" class="tutor-label tutor-label-required">Bio (Disabled)</label>
				<div class="tutor-input-wrapper">
					<textarea 
						type="text"
						id="name"
						placeholder="Enter your full name"
						disabled
						class="tutor-input tutor-text-area"
					></textarea>
				</div>
				<div class="tutor-help-text">This field is disabled.</div>
			</div>
			<div class="tutor-input-field tutor-input-field-error">
				<label for="name" class="tutor-label tutor-label-required">Bio (Error)</label>
				<div class="tutor-input-wrapper">
					<textarea 
						type="text"
						id="name"
						placeholder="Enter your full name"
						class="tutor-input tutor-text-area"
					></textarea>
				</div>
				<div class="tutor-error-text" role="alert" aria-live="polite">
					This field is required.
				</div>
			</div>
		</div>

		<div class="tutor-flex tutor-flex-column tutor-gap-3">
			<div>
				<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Checkbox</h2>
				<div class="tutor-input-field">
					<div class="tutor-input-wrapper">
						<input 
							type="checkbox"
							id="name"
							placeholder="Enter your full name"
							class="tutor-checkbox"
						>
						<label for="name" class="tutor-label tutor-label-required">Are you small(sm)?</label>
					</div>
					<div class="tutor-help-text">This is a small checkbox.</div>
				</div>
				<div class="tutor-input-field">
					<div class="tutor-input-wrapper">
						<input 
							type="checkbox"
							id="name"
							checked
							placeholder="Enter your full name"
							class="tutor-checkbox tutor-checkbox-md"
						>
						<label for="name" class="tutor-label tutor-label-required">Are you regular(md)?</label>
					</div>
					<div class="tutor-help-text">This is a regular checkbox.</div>
				</div>
				<div class="tutor-input-field">
					<div class="tutor-input-wrapper">
						<input 
							type="checkbox"
							id="name"
							disabled
							placeholder="Enter your full name"
							class="tutor-checkbox tutor-checkbox-md"
						>
						<label for="name" class="tutor-label tutor-label-required">Are you disabled(md)?</label>
					</div>
					<div class="tutor-help-text">This is a regular checkbox.</div>
				</div>
				<div class="tutor-input-field">
					<div class="tutor-input-wrapper">
						<input 
							type="checkbox"
							id="name"
							checked
							class="tutor-checkbox tutor-checkbox-md tutor-checkbox-intermediate"
						>
						<label for="name" class="tutor-label tutor-label-required">Are you intermediate(md)?</label>
					</div>
					<div class="tutor-help-text">This is a regular checkbox.</div>
				</div>
				<div class="tutor-input-field">
					<div class="tutor-input-wrapper">
						<input 
							type="checkbox"
							id="name"
							disabled
							checked
							class="tutor-checkbox tutor-checkbox-md"
						>
						<label for="name" class="tutor-label tutor-label-required">Are you disabled(md)?</label>
					</div>
					<div class="tutor-help-text">This is a regular checkbox.</div>
				</div>
				<div class="tutor-input-field">
					<div class="tutor-input-wrapper">
						<input 
							type="checkbox"
							id="name"
							disabled
							checked
							class="tutor-checkbox tutor-checkbox-md tutor-checkbox-intermediate"
						>
						<label for="name" class="tutor-label tutor-label-required">Are you disabled intermediate(md)?</label>
					</div>
					<div class="tutor-help-text">This is a regular checkbox.</div>
				</div>
			</div>

			<div>
				<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Radio</h2>

				<div class="tutor-input-field">
					<div class="tutor-input-wrapper">
						<input 
							type="radio"
							id="name"
							name="radio"
							class="tutor-radio"
						>
						<label for="name" class="tutor-label tutor-label-required">Are you small(sm)?</label>
					</div>
					<div class="tutor-help-text">This is a small radio.</div>
				</div>
				<div class="tutor-input-field">
					<div class="tutor-input-wrapper">
						<input 
							type="radio"
							id="name"
							name="radio"
							class="tutor-radio tutor-radio-md"
						>
						<label for="name" class="tutor-label tutor-label-required">Are you regular(md)?</label>
					</div>
					<div class="tutor-help-text">This is a regular radio.</div>
				</div>
				<div class="tutor-input-field">
					<div class="tutor-input-wrapper">
						<input 
							type="radio"
							id="name"
							disabled
							checked
							name="radio-2"
							class="tutor-radio tutor-radio-md"
						>
						<label for="name" class="tutor-label tutor-label-required">Are you disabled(md)?</label>
					</div>
					<div class="tutor-help-text">This is a regular disabled radio.</div>
				</div>
			</div>

			<div>
	<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Switch</h2>

	<!-- Small Switch -->
	<div class="tutor-input-field">
	<div class="tutor-input-wrapper">
		<input 
		type="checkbox"
		id="switch-sm"
		name="switch"
		class="tutor-switch"
		>
		<label for="switch-sm" class="tutor-label tutor-label-required">Enable small switch?</label>
	</div>
	<div class="tutor-help-text">This is a small switch.</div>
	</div>

	<!-- Medium Switch -->
	<div class="tutor-input-field">
	<div class="tutor-input-wrapper">
		<input 
		type="checkbox"
		id="switch-md"
		name="switch"
		class="tutor-switch tutor-switch-md"
		>
		<label for="switch-md" class="tutor-label tutor-label-required">Enable medium switch?</label>
	</div>
	<div class="tutor-help-text">This is a medium switch.</div>
	</div>

	<!-- Intermediate Switch -->
	<div class="tutor-input-field">
	<div class="tutor-input-wrapper">
		<input 
		type="checkbox"
		id="switch-intermediate"
		name="switch"
		class="tutor-switch tutor-switch-md tutor-switch--intermediate"
		>
		<label for="switch-intermediate" class="tutor-label tutor-label-required">Enable intermediate switch?</label>
	</div>
	<div class="tutor-help-text">This switch shows an intermediate state.</div>
	</div>

	<!-- Disabled Switch -->
	<div class="tutor-input-field">
	<div class="tutor-input-wrapper">
		<input 
		type="checkbox"
		id="switch-disabled"
		disabled
		checked
		name="switch-disabled"
		class="tutor-switch tutor-switch-md"
		>
		<label for="switch-disabled" class="tutor-label tutor-label-required">Enable disabled switch?</label>
	</div>
	<div class="tutor-help-text">This is a medium disabled switch.</div>
	</div>
</div>

		</div>
	</div>
</section>
<?php
/**
 * Settings Account
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$timezone_options = array();
foreach ( tutor_global_timezone_lists() as $key => $value ) {
	$timezone_options[] = array(
		'label' => $value,
		'value' => $key,
	);
}

?>

<div class="tutor-account-section">
	<form
		id="account-form"
		x-data="tutorForm({ id: 'account-form', mode: 'onBlur', shouldFocusError: true })"
		x-bind="getFormBindings()"
		@submit="handleSubmit(
			(data) => { 
				// @TODO: Handle form submission here
				console.log('Billing address saved:', data);
				alert('Billing address saved successfully!');
			},
			(errors) => { 
				// @TODO: Handle form validation errors
				console.log('Form validation errors:', errors); 
			}
		)($event)"
		class="tutor-flex tutor-flex-column tutor-gap-6"
	>
		<div class="tutor-flex tutor-flex-column tutor-gap-4">
			<h5 class="tutor-h5 tutor-sm-hidden"><?php echo esc_html__( 'Account', 'tutor' ); ?></h5>
			<div class="tutor-card tutor-flex tutor-flex-column tutor-gap-5">
				<div class="tutor-account-avatar-wrapper">
					<div x-data="tutorPopover({
						placement: 'bottom',
						offset: 8,
					})">
						<div class="tutor-account-avatar" :class="open ? 'active' : ''">
							<img src="https://i.pravatar.cc/150?u=a042581f4e29026704d" alt="User Avatar" class="tutor-avatar-image">
							<button type="button" class="tutor-account-avatar-edit" x-ref="trigger" @click="toggle()">
								<?php tutor_utils()->render_svg_icon( Icon::EDIT_2, 24, 24 ); ?>
							</button>
						</div>

						<div 
							x-ref="content"
							x-show="open"
							x-cloak
							@click.outside="handleClickOutside()"
							class="tutor-popover"
						>
							<div class="tutor-flex tutor-flex-column tutor-gap-3 tutor-p-5">
								<button type="button" class="tutor-btn tutor-btn-primary tutor-btn-x-small">
									<?php esc_html_e( 'Upload Photo', 'tutor' ); ?>
								</button>
								<button type="button" class="tutor-btn tutor-btn-secondary tutor-btn-x-small">
									<?php esc_html_e( 'Remove Photo', 'tutor' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>
				<div class="tutor-grid tutor-md-grid-cols-1 tutor-grid-cols-2 tutor-gap-5">
					<div class="tutor-input-field" :class="{'tutor-input-field-error': errors.first_name}">
						<label for="first_name" class="tutor-label"><?php echo esc_html__( 'First Name', 'tutor' ); ?></label>
						<input 
							type="text"
							id="first_name"
							placeholder="<?php echo esc_html__( 'Enter your first name', 'tutor' ); ?>"
							class="tutor-input"
							x-bind="register('first_name', { 
								required: true,
							})"
						>
						<div class="tutor-error-text" x-cloak x-show="errors.first_name" x-text="errors?.first_name?.message" role="alert" aria-live="polite"></div>
					</div>
					<div class="tutor-input-field" :class="{'tutor-input-field-error': errors.last_name}">
						<label for="last_name" class="tutor-label"><?php echo esc_html__( 'Last Name', 'tutor' ); ?></label>
						<input 
							type="text"
							id="last_name"
							placeholder="<?php echo esc_html__( 'Enter your last name', 'tutor' ); ?>"
							class="tutor-input"
							x-bind="register('last_name', { 
								required: true,
							})"
						>
						<div class="tutor-error-text" x-cloak x-show="errors.last_name" x-text="errors?.last_name?.message" role="alert" aria-live="polite"></div>
					</div>
				</div>
				<div class="tutor-input-field" :class="{'tutor-input-field-error': errors.username}">
					<label for="username" class="tutor-label"><?php echo esc_html__( 'Username', 'tutor' ); ?></label>
					<input 
						type="text"
						id="username"
						placeholder="<?php echo esc_html__( 'Username', 'tutor' ); ?>"
						class="tutor-input"
						x-bind="register('username', { 
							required: true,
						})"
					>
					<div class="tutor-error-text" x-cloak x-show="errors.username" x-text="errors?.username?.message" role="alert" aria-live="polite"></div>
				</div>
				<div class="tutor-input-field" :class="{'tutor-input-field-error': errors.phone_number}">
					<label for="phone_number" class="tutor-label"><?php echo esc_html__( 'Phone Number', 'tutor' ); ?></label>
					<input 
						type="text"
						id="phone_number"
						placeholder="<?php echo esc_html__( 'Phone Number', 'tutor' ); ?>"
						class="tutor-input"
					>
					<div class="tutor-error-text" x-cloak x-show="errors.phone_number" x-text="errors?.phone_number?.message" role="alert" aria-live="polite"></div>
				</div>
				<div class="tutor-input-field" :class="{'tutor-input-field-error': errors.occupation}">
					<label for="occupation" class="tutor-label"><?php echo esc_html__( 'Skill/Occupation', 'tutor' ); ?></label>
					<input 
						type="text"
						id="occupation"
						placeholder="<?php echo esc_html__( 'Skill/Occupation', 'tutor' ); ?>"
						class="tutor-input"
					>
					<div class="tutor-error-text" x-cloak x-show="errors.occupation" x-text="errors?.occupation?.message" role="alert" aria-live="polite"></div>
				</div>

				<?php
				tutor_load_template(
					'core-components.form-select',
					array(
						'name'        => 'timezone',
						'label'       => __( 'Timezone', 'tutor' ),
						'placeholder' => __( 'Select timezone', 'tutor' ),
						'options'     => $timezone_options,
						'searchable'  => true,
						'required'    => true,
						'clearable'   => true,
					)
				);
				?>

				<div class="tutor-input-field" :class="{'tutor-input-field-error': errors.birthdate}">
					<label for="birthdate" class="tutor-label"><?php echo esc_html__( 'Birthdate', 'tutor' ); ?></label>
					<input 
						type="text"
						id="birthdate"
						placeholder="<?php echo esc_html__( 'Birthdate', 'tutor' ); ?>"
						class="tutor-input"
					>
					<div class="tutor-error-text" x-cloak x-show="errors.birthdate" x-text="errors?.birthdate?.message" role="alert" aria-live="polite"></div>
				</div>
			</div>
		</div>

		<div class="tutor-flex tutor-flex-column tutor-gap-4">
			<h5 class="tutor-h5"><?php echo esc_html__( 'Public Profile', 'tutor' ); ?></h5>
			<div class="tutor-card tutor-flex tutor-flex-column tutor-gap-5">
				<div class="tutor-input-field" :class="{'tutor-input-field-error': errors.display_name}">
					<label for="tutor_display_name" class="tutor-label"><?php echo esc_html__( 'Display Name', 'tutor' ); ?></label>
					<input 
						type="text"
						id="tutor_display_name"
						placeholder="<?php echo esc_html__( 'Display Name', 'tutor' ); ?>"
						class="tutor-input"
					>
					<div class="tutor-error-text" x-cloak x-show="errors.display_name" x-text="errors?.display_name?.message" role="alert" aria-live="polite"></div>
				</div>
				<div class="tutor-input-field" :class="{'tutor-input-field-error': errors.tutor_profile_bio}">
					<label for="tutor_profile_bio" class="tutor-label"><?php echo esc_html__( 'Bio', 'tutor' ); ?></label>
					<textarea 
						id="tutor_profile_bio"
						placeholder="<?php echo esc_html__( 'Bio', 'tutor' ); ?>"
						class="tutor-input"
						rows="6"
					></textarea>
					<div class="tutor-error-text" x-cloak x-show="errors.tutor_profile_bio" x-text="errors?.tutor_profile_bio?.message" role="alert" aria-live="polite"></div>
				</div>
			</div>
		</div>

		<!-- Certificate Signature will be served from certificate addon -->
		<div class="tutor-flex tutor-flex-column tutor-gap-4">
			<h5 class="tutor-h5"><?php echo esc_html__( 'Certificate Signature', 'tutor' ); ?></h5>
			<div class="tutor-card">
				<?php
				tutor_load_template(
					'core-components.file-uploader',
					array(
						'multiple'    => false,
						'icon'        => Icon::SIGNATURE_UPLOAD,
						'subtitle'    => __( 'JPG, JPEG, GIF OR PNG Formats (700x430 Pixels)', 'tutor' ),
						'button_text' => __( 'Upload Image', 'tutor' ),
					)
				);
				?>
			</div>
		</div>

		<button 
					type="button" 
					class="tutor-btn tutor-btn-ghost tutor-btn-x-small"
					@click="TutorCore.form.reset(activeTab+'-form')"
				>
					<?php esc_html_e( 'Discard', 'tutor' ); ?>
				</button>
				<button 
					type="submit"
					class="tutor-btn tutor-btn-primary tutor-btn-x-small"
					x-bind:form="activeTab === 'none' ? '' : activeTab+'-form'"
				>
					<?php esc_html_e( 'Save', 'tutor' ); ?>
				</button>
			</div>
	</form>
</div>

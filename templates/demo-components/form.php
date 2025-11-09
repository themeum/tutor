<?php
/**
 * Form Validation
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Icon;

?>
<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<h1 class="tutor-text-2xl tutor-font-bold tutor-mb-6">Forms</h1>
	<p class="tutor-text-gray-600 tutor-mb-8">
		Alpine.js form validation components with react-hook-form compatible API. HTML validation is disabled to use custom validation logic.
	</p>

	<!-- Basic Form Example -->
	<div class="tutor-mb-12">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Basic Form</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Simple form with validation on blur mode and automatic error focusing. Clear buttons sync with form state. Try leaving fields empty and clicking elsewhere to see error messages.
		</p>
		<form 
			x-data="tutorForm({ id: 'basic-form', mode: 'onBlur', shouldFocusError: true })"
			x-bind="getFormBindings()"
			@submit="handleSubmit(
				(data) => { 
					alert('Form submitted successfully!\\n' + JSON.stringify(data, null, 2)); 
				},
				(errors) => { 
					console.log('Form errors:', errors); 
				}
			)($event)"
			class="tutor-max-w-md"
		>
			<!-- Name Field -->
			<div class="tutor-input-field" :class="{
				'tutor-input-field-error': errors.name,
			}">
				<label for="name" class="tutor-label tutor-label-required">Full Name</label>
				<div class="tutor-input-wrapper">
					<input 
						type="text"
						id="name"
						placeholder="Enter your full name"
						class="tutor-input tutor-input-content-clear"
						x-bind="register('name', { required: 'Name is required', minLength: { value: 2, message: 'Name must be at least 2 characters' } })"
					>
					<button 
						type="button"
						class="tutor-input-clear-button"
						x-show="values.name && String(values.name).length > 0"
						x-cloak
						@click="setValue('name', '')"
						aria-label="Clear input"
					>
						<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ) ); ?>
					</button>
				</div>
				<div class="tutor-error-text" x-cloak x-show="errors.name" x-text="errors?.name?.message" role="alert" aria-live="polite"></div>
			</div>

			<!-- Email Field -->
			<div class="tutor-input-field" :class="{
				'tutor-input-field-error': errors.email,
			}">
				<label for="email" class="tutor-label tutor-label-required">
					Email Address
					<span class="tutor-input-help-icon" title="We will never share your email with anyone else.">
						<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::QUESTION, 16, 16 ) ); ?>
					</span>
				</label>
				<div class="tutor-input-wrapper">
					<input 
						type="email"
						id="email"
						placeholder="Enter your email"
						class="tutor-input tutor-input-content-clear"
						x-bind="register('email', { 
							required: 'Email is required', 
							pattern: { 
								value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, 
								message: 'Please enter a valid email address' 
							} 
						})"
					>
					<button 
						type="button"
						class="tutor-input-clear-button"
						x-show="values.email && String(values.email).length > 0"
						x-cloak
						@click="setValue('email', '')"
						aria-label="Clear input"
					>
						<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ) ); ?>
					</button>
				</div>
				<div class="tutor-error-text" x-cloak x-show="errors.email" x-text="errors?.email?.message" role="alert" aria-live="polite"></div>
				<div class="tutor-help-text" x-show="!errors?.email?.message">We will never share your email with anyone else.</div>
			</div>

			<!-- Submit Button -->
			<div class="tutor-flex tutor-gap-3">
				<button 
					type="submit" 
					class="tutor-btn tutor-btn-primary"
					:disabled="isSubmitting"
					:class="{ 'tutor-btn-loading': isSubmitting }"
				>
					<span>Submit Form</span>
				</button>
				<button 
					type="button" 
					@click="reset()"
					class="tutor-btn tutor-btn-outline"
				>
					Reset
				</button>
			</div>

			<!-- Form State Debug -->
			<div class="tutor-mt-6 tutor-p-4 tutor-bg-gray-100 tutor-rounded-lg tutor-text-sm">
				<h4 class="tutor-font-semibold tutor-my-2">Form States</h4>
				<div><strong>Values:</strong> <span x-text="JSON.stringify(values, null, 2)"></span></div>
				<div><strong>Errors:</strong> <span x-text="JSON.stringify(errors, null, 2)"></span></div>
				<div><strong>Touched Fields:</strong> <span x-text="JSON.stringify(touchedFields, null, 2)"></span></div>
				<div><strong>Valid:</strong> <span x-text="isValid"></span></div>
			</div>
		</form>
	</div>

	<!-- Validation Modes -->
	<div class="tutor-mb-12 tutor-max-w-md">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Validation Modes</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Different validation timing modes: onChange, onBlur, and onSubmit.
		</p>
		
		<div class="tutor-grid tutor-grid-cols-1 md:tutor-grid-cols-3 tutor-gap-6">
			<!-- onChange Mode -->
			<div>
				<h3 class="tutor-font-semibold tutor-mb-3">onChange Mode</h3>
				<form 
					x-data="tutorForm({ id: 'onChange-form', mode: 'onChange' })"
					x-bind="getFormBindings()"
					class="tutor-space-y-4"
				>
					<div class="tutor-input-field" :class="{
						'tutor-input-field-error': errors.username,
					}">
						<label for="username" class="tutor-label">Username</label>
						<div class="tutor-input-wrapper">
							<input 
								type="text"
								id="username"
								placeholder="Type to see validation"
								class="tutor-input tutor-input-content-clear"
								x-bind="register('username', { 
									required: 'Username is required', 
									minLength: { value: 3, message: 'Username must be at least 3 characters' } 
								})"
							>
							<button 
								type="button"
								class="tutor-input-clear-button"
								x-cloak
								x-show="values.username && String(values.username).length > 0"
								@click="setValue('username', '')"
								aria-label="Clear input"
							>
								<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ) ); ?>
							</button>
						</div>
						<div class="tutor-error-text" x-show="errors.username" x-text="errors?.username?.message" role="alert" aria-live="polite"></div>
						<div class="tutor-help-text" x-show="!errors?.username?.message">Username must be at least 3 characters.</div>
					</div>
				</form>
			</div>

			<!-- onBlur Mode -->
			<div>
				<h3 class="tutor-font-semibold tutor-mb-3">onBlur Mode</h3>
				<form 
					x-data="tutorForm({ id: 'onBlur-form', mode: 'onBlur' })"
					x-bind="getFormBindings()"
					class="tutor-space-y-4"
				>
					<div class="tutor-input-field" :class="{
						'tutor-input-field-error': errors.phone,
					}">
						<label for="phone" class="tutor-label">Phone Number</label>
						<div class="tutor-input-wrapper">
							<input 
								type="tel"
								id="phone"
								placeholder="Focus out to validate"
								class="tutor-input tutor-input-content-clear"
								x-bind="register('phone', { 
									required: 'Phone is required', 
									pattern: { value: /^\d{11}$/, message: 'Phone must be 11 digits' } 
								})"
							>
							<button 
								type="button"
								class="tutor-input-clear-button"
								x-cloak
								x-show="values.phone && String(values.phone).length > 0"
								@click="setValue('phone', '')"
								aria-label="Clear input"
							>
								<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ) ); ?>
							</button>
						</div>
						<div class="tutor-error-text" x-cloak x-show="errors.phone" x-text="errors?.phone?.message" role="alert" aria-live="polite"></div>
						<div class="tutor-help-text" x-show="!errors?.phone?.message">Phone must be 11 digits.</div>
					</div>
				</form>
			</div>

			<!-- onSubmit Mode -->
			<div>
				<h3 class="tutor-font-semibold tutor-mb-3">onSubmit Mode</h3>
				<form 
					x-data="tutorForm({ id: 'onSubmit-form', mode: 'onSubmit' })"
					x-bind="getFormBindings()"
					@submit="handleSubmit(_, (errors) => console.log(errors))($event)"
					class="tutor-space-y-4"
				>
					<div class="tutor-input-field" :class="{
						'tutor-input-field-error': errors.website,
					}">
						<label for="website" class="tutor-label">Website URL</label>
						<div class="tutor-input-wrapper">
							<input 
								type="url"
								id="website"
								placeholder="Submit to validate"
								class="tutor-input tutor-input-content-clear"
								x-bind="register('website', { 
									required: 'Website is required', 
									pattern: { value: /^https?:\/\/.+/, message: 'Must be a valid URL' } 
								})"
							>
							<button 
								type="button"
								class="tutor-input-clear-button"
								x-show="values.website && String(values.website).length > 0"
								@click="setValue('website', '')"
								aria-label="Clear input"
							>
								<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ) ); ?>
							</button>
						</div>
						<div class="tutor-error-text" x-show="errors.website" x-text="errors?.website?.message" role="alert" aria-live="polite"></div>
						<div class="tutor-help-text" x-show="!errors?.website?.message">Must be a valid URL.</div>
					</div>
					<button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-small">Submit</button>
				</form>
			</div>
		</div>
	</div>

	<!-- API Methods -->
	<div class="tutor-mb-12">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">API Methods</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Demonstrate react-hook-form compatible API methods.
		</p>
		
		<div>
			<div x-data="tutorForm({ id: 'api-demo-form', mode: 'onBlur' })">
				<div class="tutor-max-w-md">
					<!-- Form Fields -->
					<div>
						<div class="tutor-input-field tutor-mb-4" :class="{
							'tutor-input-field-error': errors.demo_name,
						}">
							<label for="demo_name" class="tutor-label">Name</label>
							<div class="tutor-input-wrapper">
								<input 
									type="text"
									id="demo_name"
									placeholder="Enter name"
									class="tutor-input tutor-input-content-clear"
									x-bind="register('demo_name', { required: 'Name is required' })"
								>
								<button 
									type="button"
									class="tutor-input-clear-button"
									x-cloak
									x-show="values.demo_name && String(values.demo_name).length > 0"
									@click="setValue('demo_name', '')"
									aria-label="Clear input"
								>
									<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ) ); ?>
								</button>
							</div>
							<div class="tutor-error-text" x-show="errors.demo_name" x-text="errors?.demo_name?.message" role="alert" aria-live="polite"></div>
						</div>

						<div class="tutor-input-field" :class="{
							'tutor-input-field-error': errors.demo_email,
						}">
							<label for="demo_email" class="tutor-label">Email</label>
							<div class="tutor-input-wrapper">
								<input 
									type="email"
									id="demo_email"
									placeholder="Enter email"
									class="tutor-input tutor-input-content-left tutor-input-content-clear"
									x-bind="register('demo_email', { 
										required: 'Email is required',
										pattern: { value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, message: 'Invalid email' }
									})"
								>
								<div class="tutor-input-content tutor-input-content-left">
									<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::EMAIL, 16, 16 ) ); ?>
								</div>
								<button 
									type="button"
									class="tutor-input-clear-button"
									x-cloak
									x-show="values.demo_email && String(values.demo_email).length > 0"
									@click="setValue('demo_email', '')"
									aria-label="Clear input"
								>
									<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ) ); ?>
								</button>
							</div>
							<div class="tutor-error-text" x-cloak x-show="errors.demo_email" x-text="errors?.demo_email?.message" role="alert" aria-live="polite"></div>
						</div>
					</div>

					<!-- API Controls -->
					<div>
						<h4 class="tutor-font-semibold tutor-mb-3">API Methods</h4>
						<p class="tutor-text-sm tutor-text-gray-600 tutor-mb-3">Test the react-hook-form compatible API methods. Watch the form state and input fields update:</p>
						<div class="tutor-flex tutor-flex-column tutor-gap-3 tutor-max-w-md">
							<button 
								@click="setValue('demo_name', 'John Doe', { shouldValidate: true })"
								class="tutor-btn tutor-btn-small"
							>
								setValue('demo_name', 'John Doe')
							</button>
							<button 
								@click="setValue('demo_email', 'john@example.com', { shouldValidate: true })"
								class="tutor-btn"
							>
								setValue('demo_email', 'john@example.com')
							</button>
							<button 
								@click="setFocus('demo_name')"
								class="tutor-btn"
							>
								setFocus('demo_name')
							</button>
							<button 
								@click="trigger()"
								class="tutor-btn"
							>
								trigger() - Validate All
							</button>
							<button 
								@click="clearErrors()"
								class="tutor-btn"
							>
								clearErrors()
							</button>
							<button 
								@click="reset()"
								class="tutor-btn"
							>
								reset()
							</button>
						</div>

						<div class="tutor-mt-4 tutor-p-3 tutor-bg-white tutor-rounded tutor-text-sm">
							<div><strong>watch():</strong> <span x-text="JSON.stringify(watch(), null, 2)"></span></div>
							<div><strong>errors:</strong> <span x-text="JSON.stringify(errors, null, 2)"></span></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Form Service API -->
<div class="tutor-mb-12">
	<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Form Service API (TutorCore.form)</h2>
	<p class="tutor-text-gray-600 tutor-mb-4">
		Access and control forms programmatically from outside Alpine components using the global <code class="tutor-bg-gray-200 tutor-px-2 tutor-py-1 tutor-rounded">TutorCore.form</code> API. This is useful for integrating forms with other parts of your application.
	</p>
	
	<div>
		<div class="tutor-max-w-md">
			<!-- Form Section -->
			<div>
				<h3 class="tutor-font-semibold tutor-mb-4">User Profile Form</h3>
				<form 
					x-data="tutorForm({ id: 'profile-form', mode: 'onBlur' })"
					x-bind="getFormBindings()"
					@submit="handleSubmit(
						(data) => { 
							alert('Profile saved!\\n' + JSON.stringify(data, null, 2)); 
						}
					)($event)"
				>
					<!-- First Name -->
					<div class="tutor-input-field tutor-mb-4" :class="{
						'tutor-input-field-error': errors.firstName,
					}">
						<label for="firstName" class="tutor-label tutor-label-required">First Name</label>
						<div class="tutor-input-wrapper">
							<input 
								type="text"
								id="firstName"
								placeholder="Enter first name"
								class="tutor-input tutor-input-content-clear"
								x-bind="register('firstName', { 
									required: 'First name is required',
									minLength: { value: 2, message: 'Must be at least 2 characters' }
								})"
							>
							<button 
								type="button"
								class="tutor-input-clear-button"
								x-cloak
								x-show="values.firstName && String(values.firstName).length > 0"
								@click="setValue('firstName', '')"
								aria-label="Clear input"
							>
								<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ) ); ?>
							</button>
						</div>
						<div class="tutor-error-text" x-cloak x-show="errors.firstName" x-text="errors?.firstName?.message" role="alert" aria-live="polite"></div>
					</div>

					<!-- Last Name -->
					<div class="tutor-input-field tutor-mb-4" :class="{
						'tutor-input-field-error': errors.lastName,
					}">
						<label for="lastName" class="tutor-label tutor-label-required">Last Name</label>
						<div class="tutor-input-wrapper">
							<input 
								type="text"
								id="lastName"
								placeholder="Enter last name"
								class="tutor-input tutor-input-content-clear"
								x-bind="register('lastName', { 
									required: 'Last name is required',
									minLength: { value: 2, message: 'Must be at least 2 characters' }
								})"
							>
							<button 
								type="button"
								class="tutor-input-clear-button"
								x-cloak
								x-show="values.lastName && String(values.lastName).length > 0"
								@click="setValue('lastName', '')"
								aria-label="Clear input"
							>
								<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ) ); ?>
							</button>
						</div>
						<div class="tutor-error-text" x-cloak x-show="errors.lastName" x-text="errors?.lastName?.message" role="alert" aria-live="polite"></div>
					</div>

					<!-- Email -->
					<div class="tutor-input-field tutor-mb-4" :class="{
						'tutor-input-field-error': errors.profileEmail,
					}">
						<label for="profileEmail" class="tutor-label tutor-label-required">Email</label>
						<div class="tutor-input-wrapper">
							<input 
								type="email"
								id="profileEmail"
								placeholder="Enter email"
								class="tutor-input tutor-input-content-clear"
								x-bind="register('profileEmail', { 
									required: 'Email is required',
									pattern: { value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, message: 'Invalid email address' }
								})"
							>
							<button 
								type="button"
								class="tutor-input-clear-button"
								x-cloak
								x-show="values.profileEmail && String(values.profileEmail).length > 0"
								@click="setValue('profileEmail', '')"
								aria-label="Clear input"
							>
								<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ) ); ?>
							</button>
						</div>
						<div class="tutor-error-text" x-cloak x-show="errors.profileEmail" x-text="errors?.profileEmail?.message" role="alert" aria-live="polite"></div>
					</div>

					<!-- Bio -->
					<div class="tutor-input-field tutor-mb-4" :class="{
						'tutor-input-field-error': errors.bio,
					}">
						<label for="bio" class="tutor-label">Bio</label>
						<textarea 
							id="bio"
							placeholder="Tell us about yourself"
							rows="3"
							class="tutor-input tutor-text-area"
							x-bind="register('bio', { 
								maxLength: { value: 200, message: 'Bio must be less than 200 characters' }
							})"
						></textarea>
						<div class="tutor-error-text" x-cloak x-show="errors.bio" x-text="errors?.bio?.message" role="alert" aria-live="polite"></div>
						<div class="tutor-help-text" x-show="!errors?.bio?.message">Maximum 200 characters</div>
					</div>

					<div class="tutor-flex tutor-gap-3">
						<button 
							type="submit" 
							class="tutor-btn tutor-btn-primary"
							:disabled="isSubmitting"
							:class="{ 'tutor-btn-loading': isSubmitting }"
						>
							<span>Save Profile</span>
						</button>
						<button 
							type="button" 
							@click="reset()"
							class="tutor-btn tutor-btn-outline"
						>
							Cancel
						</button>
					</div>
				</form>
			</div>

			<!-- External Controls Section -->
			<div>
				<h3 class="tutor-font-semibold tutor-mb-4">External Form Controls</h3>
				<p class="tutor-text-sm tutor-text-gray-600 tutor-mb-4">
					These buttons control the form using <code class="tutor-bg-gray-200 tutor-px-1 tutor-rounded">TutorCore.form</code> API from outside the Alpine component. Open browser console to see the outputs.
				</p>

				<div class="tutor-flex tutor-flex-column tutor-gap-3 tutor-max-w-md">
					<!-- Populate Form -->
					<div>
						<button 
							onclick="TutorCore.form.setValues('profile-form', {
								firstName: 'Jane',
								lastName: 'Smith',
								profileEmail: 'jane.smith@example.com',
								bio: 'Full-stack developer with 5 years of experience.'
							}, { shouldValidate: true })"
							class="tutor-btn tutor-btn-outline tutor-btn-small tutor-w-full"
						>
							📝 Populate Form
						</button>
						<p class="tutor-text-xs tutor-text-gray-500 tutor-mt-1">
							<code>TutorCore.form.setValues('profile-form', {...})</code>
						</p>
					</div>

					<!-- Get All Values -->
					<div>
						<button 
							onclick="console.log('Form Values:', TutorCore.form.getValues('profile-form'))"
							class="tutor-btn tutor-btn-outline tutor-btn-small tutor-w-full"
						>
							📋 Get All Values
						</button>
						<p class="tutor-text-xs tutor-text-gray-500 tutor-mt-1">
							<code>TutorCore.form.getValues('profile-form')</code>
						</p>
					</div>

					<!-- Get Single Value -->
					<div>
						<button 
							onclick="console.log('Email:', TutorCore.form.getValue('profile-form', 'profileEmail'))"
							class="tutor-btn tutor-btn-outline tutor-btn-small tutor-w-full"
						>
							📧 Get Email Value
						</button>
						<p class="tutor-text-xs tutor-text-gray-500 tutor-mt-1">
							<code>TutorCore.form.getValue('profile-form', 'profileEmail')</code>
						</p>
					</div>

					<!-- Set Single Value -->
					<div>
						<button 
							onclick="TutorCore.form.setValue('profile-form', 'firstName', 'John', { shouldValidate: true })"
							class="tutor-btn tutor-btn-outline tutor-btn-small tutor-w-full"
						>
							✏️ Set First Name to "John"
						</button>
						<p class="tutor-text-xs tutor-text-gray-500 tutor-mt-1">
							<code>TutorCore.form.setValue('profile-form', 'firstName', 'John')</code>
						</p>
					</div>

					<!-- Validate Form -->
					<div>
						<button 
							onclick="TutorCore.form.trigger('profile-form').then(isValid => console.log('Form is valid:', isValid))"
							class="tutor-btn tutor-btn-outline tutor-btn-small tutor-w-full"
						>
							✅ Validate All Fields
						</button>
						<p class="tutor-text-xs tutor-text-gray-500 tutor-mt-1">
							<code>await TutorCore.form.trigger('profile-form')</code>
						</p>
					</div>

					<!-- Validate Single Field -->
					<div>
						<button 
							onclick="TutorCore.form.trigger('profile-form', 'profileEmail').then(isValid => console.log('Email is valid:', isValid))"
							class="tutor-btn tutor-btn-outline tutor-btn-small tutor-w-full"
						>
							✉️ Validate Email Only
						</button>
						<p class="tutor-text-xs tutor-text-gray-500 tutor-mt-1">
							<code>await TutorCore.form.trigger('profile-form', 'profileEmail')</code>
						</p>
					</div>

					<!-- Set Custom Error -->
					<div>
						<button 
							onclick="TutorCore.form.setError('profile-form', 'profileEmail', { type: 'server', message: 'Email already exists' })"
							class="tutor-btn tutor-btn-outline tutor-btn-small tutor-w-full"
						>
							⚠️ Set Email Error
						</button>
						<p class="tutor-text-xs tutor-text-gray-500 tutor-mt-1">
							<code>TutorCore.form.setError('profile-form', 'profileEmail', {...})</code>
						</p>
					</div>

					<!-- Clear Errors -->
					<div>
						<button 
							onclick="TutorCore.form.clearErrors('profile-form')"
							class="tutor-btn tutor-btn-outline tutor-btn-small tutor-w-full"
						>
							🧹 Clear All Errors
						</button>
						<p class="tutor-text-xs tutor-text-gray-500 tutor-mt-1">
							<code>TutorCore.form.clearErrors('profile-form')</code>
						</p>
					</div>

					<!-- Focus Field -->
					<div>
						<button 
							onclick="TutorCore.form.setFocus('profile-form', 'firstName', { shouldSelect: true })"
							class="tutor-btn tutor-btn-outline tutor-btn-small tutor-w-full"
						>
							🎯 Focus First Name
						</button>
						<p class="tutor-text-xs tutor-text-gray-500 tutor-mt-1">
							<code>TutorCore.form.setFocus('profile-form', 'firstName')</code>
						</p>
					</div>

					<!-- Get Form State -->
					<div>
						<button 
							onclick="console.log('Form State:', TutorCore.form.getFormState('profile-form'))"
							class="tutor-btn tutor-btn-outline tutor-btn-small tutor-w-full"
						>
							📊 Get Form State
						</button>
						<p class="tutor-text-xs tutor-text-gray-500 tutor-mt-1">
							<code>TutorCore.form.getFormState('profile-form')</code>
						</p>
					</div>

					<!-- Reset Form -->
					<div>
						<button 
							onclick="TutorCore.form.reset('profile-form')"
							class="tutor-btn tutor-btn-outline tutor-btn-small tutor-w-full"
						>
							🔄 Reset Form
						</button>
						<p class="tutor-text-xs tutor-text-gray-500 tutor-mt-1">
							<code>TutorCore.form.reset('profile-form')</code>
						</p>
					</div>

					<!-- Reset with Custom Values -->
					<div>
						<button 
							onclick="TutorCore.form.reset('profile-form', {
								firstName: 'Admin',
								lastName: 'User',
								profileEmail: 'admin@example.com',
								bio: ''
							})"
							class="tutor-btn tutor-btn-outline tutor-btn-small tutor-w-full"
						>
							🔄 Reset to Admin Profile
						</button>
						<p class="tutor-text-xs tutor-text-gray-500 tutor-mt-1">
							<code>TutorCore.form.reset('profile-form', {...})</code>
						</p>
					</div>
				</div>

				<!-- API Reference -->
				<div class="tutor-mt-6 tutor-p-4 tutor-bg-white tutor-rounded-lg tutor-text-sm">
					<h4 class="tutor-font-semibold tutor-mb-2">Available API Methods:</h4>
					<ul class="tutor-list-disc tutor-list-inside tutor-space-y-1 tutor-text-gray-700">
						<li><code>getValues(formId)</code></li>
						<li><code>getValue(formId, name)</code></li>
						<li><code>setValue(formId, name, value, options)</code></li>
						<li><code>setValues(formId, values, options)</code></li>
						<li><code>reset(formId, values?)</code></li>
						<li><code>trigger(formId, name?)</code></li>
						<li><code>clearErrors(formId, name?)</code></li>
						<li><code>setError(formId, name, error)</code></li>
						<li><code>setFocus(formId, name, options)</code></li>
						<li><code>getFormState(formId)</code></li>
						<li><code>watch(formId, name)</code></li>
						<li><code>hasForm(formId)</code></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
</section>
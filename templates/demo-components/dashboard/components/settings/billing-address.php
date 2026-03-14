<?php
/**
 * Settings Billing Address
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

?>

<section class="tutor-flex tutor-flex-column tutor-gap-4">
	<h5 class="tutor-h5 tutor-sm-hidden"><?php echo esc_html__( 'Billing Address', 'tutor' ); ?></h5>

	<div class="tutor-surface-l1 tutor-rounded-2xl tutor-p-6 tutor-flex tutor-flex-column tutor-gap-5 tutor-border">
		<form
			id="billing-address-form"
			x-data="tutorForm({ id: 'billing-address-form', mode: 'onBlur', shouldFocusError: true })"
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
			class="tutor-flex tutor-flex-column tutor-gap-2"
		>
			<!-- First Name & Last Name Row -->
			<div class="tutor-grid tutor-md-grid-cols-1 tutor-grid-cols-2 tutor-gap-5">
				<!-- First Name -->
				<div class="tutor-input-field" :class="{
					'tutor-input-field-error': errors.firstName,
				}">
					<label for="firstName" class="tutor-label"><?php echo esc_html__( 'First Name', 'tutor' ); ?></label>
					<input 
						type="text"
						id="firstName"
						placeholder="<?php echo esc_html__( 'Enter your first name', 'tutor' ); ?>"
						class="tutor-input"
						x-bind="register('firstName', { 
							required: true,
						})"
					>
					<div class="tutor-error-text" x-cloak x-show="errors.firstName" x-text="errors?.firstName?.message" role="alert" aria-live="polite"></div>
				</div>

				<!-- Last Name -->
				<div class="tutor-input-field" :class="{
					'tutor-input-field-error': errors.lastName,
				}">
					<label for="lastName" class="tutor-label"><?php echo esc_html__( 'Last Name', 'tutor' ); ?></label>
					<input 
						type="text"
						id="lastName"
						placeholder="<?php echo esc_html__( 'Enter your last name', 'tutor' ); ?>"
						class="tutor-input"
						x-bind="register('lastName', { 
							required: true,
						})"
					>
					<div class="tutor-error-text" x-cloak x-show="errors.lastName" x-text="errors?.lastName?.message" role="alert" aria-live="polite"></div>
				</div>
			</div>

			<!-- Email Address -->
			<div class="tutor-input-field" :class="{
				'tutor-input-field-error': errors.emailAddress,
			}">
				<label for="emailAddress" class="tutor-label"><?php echo esc_html__( 'Email Address', 'tutor' ); ?></label>
				<input 
					type="email"
					id="emailAddress"
					placeholder="<?php echo esc_html__( 'Enter your email address', 'tutor' ); ?>"
					class="tutor-input"
					x-bind="register('emailAddress', { 
						required: true,
						pattern: { 
							value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, 
							message: '<?php echo esc_html__( 'Please enter a valid email address', 'tutor' ); ?>'
						}
					})"
				>
				<div class="tutor-error-text" x-cloak x-show="errors.emailAddress" x-text="errors?.emailAddress?.message" role="alert" aria-live="polite"></div>
			</div>

			<!-- Country -->
			<div class="tutor-input-field" :class="{
				'tutor-input-field-error': errors.country,
			}">
				<label for="country" class="tutor-label"><?php echo esc_html__( 'Country', 'tutor' ); ?></label>
				<input 
					type="text"
					id="country"
					placeholder="<?php echo esc_html__( 'Enter your country', 'tutor' ); ?>"
					class="tutor-input"
					x-bind="register('country', { 
						required: true
					})"
				>
				<div class="tutor-error-text" x-cloak x-show="errors.country" x-text="errors?.country?.message" role="alert" aria-live="polite"></div>
			</div>

			<!-- Street -->
			<div class="tutor-input-field" :class="{
				'tutor-input-field-error': errors.street,
			}">
				<label for="street" class="tutor-label"><?php echo esc_html__( 'Street', 'tutor' ); ?></label>
				<input 
					type="text"
					id="street"
					placeholder="<?php echo esc_html__( 'Enter your street', 'tutor' ); ?>"
					class="tutor-input"
					x-bind="register('street', { 
						required: true
					})"
				>
				<div class="tutor-error-text" x-cloak x-show="errors.street" x-text="errors?.street?.message" role="alert" aria-live="polite"></div>
			</div>

			<!-- City & Postcode Row -->
			<div class="tutor-grid tutor-md-grid-cols-1 tutor-grid-cols-2 tutor-gap-5">
				<!-- City -->
				<div class="tutor-input-field" :class="{
					'tutor-input-field-error': errors.city,
				}">
					<label for="city" class="tutor-label"><?php echo esc_html__( 'City', 'tutor' ); ?></label>
					<input 
						type="text"
						id="city"
						placeholder="<?php echo esc_html__( 'Enter your city', 'tutor' ); ?>"
						class="tutor-input"
						x-bind="register('city', { 
							required: true,
						})"
					>
					<div class="tutor-error-text" x-cloak x-show="errors.city" x-text="errors?.city?.message" role="alert" aria-live="polite"></div>
				</div>

				<!-- Postcode/Zip -->
				<div class="tutor-input-field" :class="{
					'tutor-input-field-error': errors.postcode,
				}">
					<label for="postcode" class="tutor-label"><?php echo esc_html__( 'Postcode/ Zip', 'tutor' ); ?></label>
					<input 
						type="text"
						id="postcode"
						placeholder="<?php echo esc_html__( 'Enter your postcode/ zip', 'tutor' ); ?>"
						class="tutor-input"
						x-bind="register('postcode', { 
							required: true,
							pattern: { 
								value: /^[A-Z0-9\s-]+$/i, 
								message: 'Please enter a valid postcode' 
							}
						})"
					>
					<div class="tutor-error-text" x-cloak x-show="errors.postcode" x-text="errors?.postcode?.message" role="alert" aria-live="polite"></div>
				</div>
			</div>

			<!-- Address -->
			<div class="tutor-input-field" :class="{
				'tutor-input-field-error': errors.address,
			}">
				<label for="address" class="tutor-label"><?php echo esc_html__( 'Address', 'tutor' ); ?></label>
				<input 
					id="address"
					placeholder="<?php echo esc_html__( 'Enter your address', 'tutor' ); ?>"
					rows="3"
					class="tutor-input"
					x-bind="register('address')"
				>
				<div class="tutor-error-text" x-cloak x-show="errors.address" x-text="errors?.address?.message" role="alert" aria-live="polite"></div>
			</div>
			</div>
		</form>
	</div>
</section>
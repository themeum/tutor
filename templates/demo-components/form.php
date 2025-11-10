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

	<!-- Number Only Example -->
	<div class="tutor-mb-12">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Number Only Input</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			The <code class="tutor-bg-gray-200 tutor-px-2 tutor-py-1 tutor-rounded">numberOnly</code> validation rule prevents users from typing or pasting letters, or special characters. Only numbers and decimals are allowed by default. You can also allow negative numbers and allow whole numbers.
		</p>
		
		<form 
			x-data="tutorForm({ id: 'number-form', mode: 'onBlur' })"
			x-bind="getFormBindings()"
			@submit="handleSubmit(
				(data) => { 
					alert('Form submitted!\\nPrice: $' + data.price + '\\nQuantity: ' + data.quantity); 
				}
			)($event)"
			class="tutor-max-w-md"
		>
			<!-- Price Field -->
			<div class="tutor-input-field" :class="{
				'tutor-input-field-error': errors.price,
			}">
				<label for="price" class="tutor-label tutor-label-required">Product Price</label>
				<div class="tutor-input-wrapper">
					<input 
						type="text"
						id="price"
						placeholder="0.00"
						class="tutor-input tutor-input-content-clear"
						x-bind="register('price', { 
							required: 'Price is required',
							numberOnly: { allowNegative: true },
						})"
					>
					<button 
						type="button"
						class="tutor-input-clear-button"
						x-show="values.price && String(values.price).length > 0"
						x-cloak
						@click="setValue('price', '')"
						aria-label="Clear input"
					>
						<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ) ); ?>
					</button>
				</div>
				<div class="tutor-error-text" x-cloak x-show="errors.price" x-text="errors?.price?.message" role="alert" aria-live="polite"></div>
				<div class="tutor-help-text" x-show="!errors?.price?.message">
					Try typing letters, negative signs, or special characters - they will be blocked!
				</div>
			</div>

			<!-- Quantity Field -->
			<div class="tutor-input-field" :class="{
				'tutor-input-field-error': errors.quantity,
			}">
				<label for="quantity" class="tutor-label tutor-label-required">Quantity</label>
				<div class="tutor-input-wrapper">
					<input 
						type="text"
						id="quantity"
						placeholder="0"
						class="tutor-input tutor-input-content-clear"
						x-bind="register('quantity', { 
							required: 'Quantity is required',
							numberOnly: { whole: true },
							min: { value: 1, message: 'Quantity must be at least 1' }
						})"
					>
					<button 
						type="button"
						class="tutor-input-clear-button"
						x-show="values.quantity && String(values.quantity).length > 0"
						x-cloak
						@click="setValue('quantity', '')"
						aria-label="Clear input"
					>
						<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ) ); ?>
					</button>
				</div>
				<div class="tutor-error-text" x-cloak x-show="errors.quantity" x-text="errors?.quantity?.message" role="alert" aria-live="polite"></div>
				<div class="tutor-help-text" x-show="!errors?.quantity?.message">Only whole numbers allowed</div>
			</div>

			<!-- Age Field (Optional) -->
			<div class="tutor-input-field" :class="{
				'tutor-input-field-error': errors.age,
			}">
				<label for="age" class="tutor-label">Age (Optional)</label>
				<div class="tutor-input-wrapper">
					<input 
						type="text"
						id="age"
						placeholder="Enter age"
						class="tutor-input tutor-input-content-clear"
						x-bind="register('age', { 
							numberOnly: true,
							min: { value: 0, message: 'Age cannot be negative' },
							max: { value: 150, message: 'Please enter a valid age' }
						})"
					>
					<button 
						type="button"
						class="tutor-input-clear-button"
						x-show="values.age && String(values.age).length > 0"
						x-cloak
						@click="setValue('age', '')"
						aria-label="Clear input"
					>
						<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ) ); ?>
					</button>
				</div>
				<div class="tutor-error-text" x-cloak x-show="errors.age" x-text="errors?.age?.message" role="alert" aria-live="polite"></div>
				<div class="tutor-help-text" x-show="!errors?.age?.message">
					No required rule, so empty values are allowed
				</div>
			</div>

			<div class="tutor-flex tutor-gap-3">
				<button 
					type="submit" 
					class="tutor-btn tutor-btn-primary"
					:disabled="isSubmitting"
				>
					<span>Submit</span>
				</button>
				<button 
					type="button" 
					@click="reset()"
					class="tutor-btn tutor-btn-outline"
				>
					Reset
				</button>
			</div>

			<!-- What's Blocked -->
			<div class="tutor-mt-6 tutor-p-4 tutor-bg-blue-50 tutor-rounded-lg tutor-text-sm">
				<h4 class="tutor-font-semibold tutor-mb-2">What's Blocked:</h4>
				<ul class="tutor-list-disc tutor-list-inside tutor-space-y-1">
					<li>❌ Plus sign: <code>+</code></li>
					<li>❌ Letters: <code>a-z, A-Z</code></li>
					<li>❌ Exponential notation: <code>e, E</code></li>
					<li>❌ Multiple decimal points: <code>1.2.3</code></li>
					<li>❌ Special characters: <code>!@#$%^&*()</code></li>
				</ul>
				<h4 class="tutor-font-semibold tutor-mt-4 tutor-mb-2">What's Allowed:</h4>
				<ul class="tutor-list-disc tutor-list-inside tutor-space-y-1">
					<li>✅ Numbers: <code>123, -456</code></li>
					<li>✅ Decimal numbers: <code>12.34, 0.5</code></li>
					<li>✅ Zero: <code>0</code></li>
				</ul>
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

	<!-- API Reference Table -->
	<div class="tutor-mb-12">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">API Reference</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Complete reference for all available methods and validation rules.
		</p>

		<!-- Form Methods -->
		<div class="tutor-mb-8">
			<h3 class="tutor-text-lg tutor-font-semibold tutor-mb-3">Form Methods (Inside Alpine Component)</h3>
			<div class="tutor-overflow-x-auto">
				<table class="tutor-table tutor-table-bordered">
					<thead>
						<tr>
							<th class="tutor-px-4 tutor-py-2">Method</th>
							<th class="tutor-px-4 tutor-py-2">Parameters</th>
							<th class="tutor-px-4 tutor-py-2">Returns</th>
							<th class="tutor-px-4 tutor-py-2">Description</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>register(name, rules?)</code></td>
							<td class="tutor-px-4 tutor-py-2">name: string<br>rules?: ValidationRules</td>
							<td class="tutor-px-4 tutor-py-2">Object</td>
							<td class="tutor-px-4 tutor-py-2">Registers a field with validation rules. Use with x-bind.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>watch(name?)</code></td>
							<td class="tutor-px-4 tutor-py-2">name?: string</td>
							<td class="tutor-px-4 tutor-py-2">any | object</td>
							<td class="tutor-px-4 tutor-py-2">Gets field value or all values if no name provided.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>setValue(name, value, options?)</code></td>
							<td class="tutor-px-4 tutor-py-2">name: string<br>value: any<br>options?: SetValueOptions</td>
							<td class="tutor-px-4 tutor-py-2">void</td>
							<td class="tutor-px-4 tutor-py-2">Sets field value programmatically.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>getValue(name)</code></td>
							<td class="tutor-px-4 tutor-py-2">name: string</td>
							<td class="tutor-px-4 tutor-py-2">any</td>
							<td class="tutor-px-4 tutor-py-2">Gets specific field value.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>setFocus(name, options?)</code></td>
							<td class="tutor-px-4 tutor-py-2">name: string<br>options?: FocusOptions</td>
							<td class="tutor-px-4 tutor-py-2">void</td>
							<td class="tutor-px-4 tutor-py-2">Sets focus to a field.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>trigger(name?)</code></td>
							<td class="tutor-px-4 tutor-py-2">name?: string | string[]</td>
							<td class="tutor-px-4 tutor-py-2">Promise&lt;boolean&gt;</td>
							<td class="tutor-px-4 tutor-py-2">Manually triggers validation.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>clearErrors(name?)</code></td>
							<td class="tutor-px-4 tutor-py-2">name?: string | string[]</td>
							<td class="tutor-px-4 tutor-py-2">void</td>
							<td class="tutor-px-4 tutor-py-2">Clears errors for fields.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>setError(name, error)</code></td>
							<td class="tutor-px-4 tutor-py-2">name: string<br>error: FieldError</td>
							<td class="tutor-px-4 tutor-py-2">void</td>
							<td class="tutor-px-4 tutor-py-2">Sets custom error for a field.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>reset(values?)</code></td>
							<td class="tutor-px-4 tutor-py-2">values?: object</td>
							<td class="tutor-px-4 tutor-py-2">void</td>
							<td class="tutor-px-4 tutor-py-2">Resets form to default or provided values.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>handleSubmit(onValid, onInvalid?)</code></td>
							<td class="tutor-px-4 tutor-py-2">onValid: Function<br>onInvalid?: Function</td>
							<td class="tutor-px-4 tutor-py-2">Function</td>
							<td class="tutor-px-4 tutor-py-2">Returns submit handler function.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>getFormState()</code></td>
							<td class="tutor-px-4 tutor-py-2">-</td>
							<td class="tutor-px-4 tutor-py-2">FormState</td>
							<td class="tutor-px-4 tutor-py-2">Gets complete form state (validates before returning).</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<!-- Global API Methods -->
		<div class="tutor-mb-8">
			<h3 class="tutor-text-lg tutor-font-semibold tutor-mb-3">Global API Methods (TutorCore.form)</h3>
			<div class="tutor-overflow-x-auto">
				<table class="tutor-table tutor-table-bordered">
					<thead>
						<tr>
							<th class="tutor-px-4 tutor-py-2">Method</th>
							<th class="tutor-px-4 tutor-py-2">Parameters</th>
							<th class="tutor-px-4 tutor-py-2">Returns</th>
							<th class="tutor-px-4 tutor-py-2">Description</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>getValues(formId)</code></td>
							<td class="tutor-px-4 tutor-py-2">formId: string</td>
							<td class="tutor-px-4 tutor-py-2">object</td>
							<td class="tutor-px-4 tutor-py-2">Gets all values from a form.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>getValue(formId, name)</code></td>
							<td class="tutor-px-4 tutor-py-2">formId: string<br>name: string</td>
							<td class="tutor-px-4 tutor-py-2">any</td>
							<td class="tutor-px-4 tutor-py-2">Gets specific field value.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>setValue(formId, name, value, options?)</code></td>
							<td class="tutor-px-4 tutor-py-2">formId: string<br>name: string<br>value: any<br>options?: SetValueOptions</td>
							<td class="tutor-px-4 tutor-py-2">void</td>
							<td class="tutor-px-4 tutor-py-2">Sets single field value.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>setValues(formId, values, options?)</code></td>
							<td class="tutor-px-4 tutor-py-2">formId: string<br>values: object<br>options?: SetValueOptions</td>
							<td class="tutor-px-4 tutor-py-2">void</td>
							<td class="tutor-px-4 tutor-py-2">Sets multiple field values at once.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>trigger(formId, name?)</code></td>
							<td class="tutor-px-4 tutor-py-2">formId: string<br>name?: string | string[]</td>
							<td class="tutor-px-4 tutor-py-2">Promise&lt;boolean&gt;</td>
							<td class="tutor-px-4 tutor-py-2">Triggers validation externally.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>clearErrors(formId, name?)</code></td>
							<td class="tutor-px-4 tutor-py-2">formId: string<br>name?: string | string[]</td>
							<td class="tutor-px-4 tutor-py-2">void</td>
							<td class="tutor-px-4 tutor-py-2">Clears errors externally.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>setError(formId, name, error)</code></td>
							<td class="tutor-px-4 tutor-py-2">formId: string<br>name: string<br>error: FieldError</td>
							<td class="tutor-px-4 tutor-py-2">void</td>
							<td class="tutor-px-4 tutor-py-2">Sets error externally.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>setFocus(formId, name, options?)</code></td>
							<td class="tutor-px-4 tutor-py-2">formId: string<br>name: string<br>options?: FocusOptions</td>
							<td class="tutor-px-4 tutor-py-2">void</td>
							<td class="tutor-px-4 tutor-py-2">Sets focus externally.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>reset(formId, values?)</code></td>
							<td class="tutor-px-4 tutor-py-2">formId: string<br>values?: object</td>
							<td class="tutor-px-4 tutor-py-2">void</td>
							<td class="tutor-px-4 tutor-py-2">Resets form externally.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>getFormState(formId)</code></td>
							<td class="tutor-px-4 tutor-py-2">formId: string</td>
							<td class="tutor-px-4 tutor-py-2">FormState</td>
							<td class="tutor-px-4 tutor-py-2">Gets complete form state.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>watch(formId, name?)</code></td>
							<td class="tutor-px-4 tutor-py-2">formId: string<br>name?: string</td>
							<td class="tutor-px-4 tutor-py-2">any | object</td>
							<td class="tutor-px-4 tutor-py-2">Watches field value(s) externally.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>hasForm(formId)</code></td>
							<td class="tutor-px-4 tutor-py-2">formId: string</td>
							<td class="tutor-px-4 tutor-py-2">boolean</td>
							<td class="tutor-px-4 tutor-py-2">Checks if form exists.</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<!-- Validation Rules -->
		<div class="tutor-mb-8">
			<h3 class="tutor-text-lg tutor-font-semibold tutor-mb-3">Validation Rules</h3>
			<div class="tutor-overflow-x-auto">
				<table class="tutor-table tutor-table-bordered">
					<thead>
						<tr>
							<th class="tutor-px-4 tutor-py-2">Rule</th>
							<th class="tutor-px-4 tutor-py-2">Type</th>
							<th class="tutor-px-4 tutor-py-2">Example</th>
							<th class="tutor-px-4 tutor-py-2">Description</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>required</code></td>
							<td class="tutor-px-4 tutor-py-2">boolean | string</td>
							<td class="tutor-px-4 tutor-py-2"><code>required: true</code><br><code>required: 'Field is required'</code></td>
							<td class="tutor-px-4 tutor-py-2">Field must have a value.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>minLength</code></td>
							<td class="tutor-px-4 tutor-py-2">number | object</td>
							<td class="tutor-px-4 tutor-py-2"><code>minLength: 3</code><br><code>minLength: { value: 3, message: 'Min 3 chars' }</code></td>
							<td class="tutor-px-4 tutor-py-2">Minimum string length.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>maxLength</code></td>
							<td class="tutor-px-4 tutor-py-2">number | object</td>
							<td class="tutor-px-4 tutor-py-2"><code>maxLength: 50</code><br><code>maxLength: { value: 50, message: 'Max 50 chars' }</code></td>
							<td class="tutor-px-4 tutor-py-2">Maximum string length.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>min</code></td>
							<td class="tutor-px-4 tutor-py-2">number | object</td>
							<td class="tutor-px-4 tutor-py-2"><code>min: 0</code><br><code>min: { value: 0, message: 'Must be positive' }</code></td>
							<td class="tutor-px-4 tutor-py-2">Minimum numeric value.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>max</code></td>
							<td class="tutor-px-4 tutor-py-2">number | object</td>
							<td class="tutor-px-4 tutor-py-2"><code>max: 100</code><br><code>max: { value: 100, message: 'Max is 100' }</code></td>
							<td class="tutor-px-4 tutor-py-2">Maximum numeric value.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>pattern</code></td>
							<td class="tutor-px-4 tutor-py-2">RegExp | object</td>
							<td class="tutor-px-4 tutor-py-2"><code>pattern: /^\d+$/</code><br><code>pattern: { value: /^\d+$/, message: 'Numbers only' }</code></td>
							<td class="tutor-px-4 tutor-py-2">RegExp pattern matching.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>numberOnly</code></td>
							<td class="tutor-px-4 tutor-py-2">boolean | object</td>
							<td class="tutor-px-4 tutor-py-2"><code>numberOnly: true</code><br><code>numberOnly: { allowNegative: true; whole: true }</code></td>
							<td class="tutor-px-4 tutor-py-2">Blocks typing/pasting non-numeric chars.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>validate</code></td>
							<td class="tutor-px-4 tutor-py-2">Function</td>
							<td class="tutor-px-4 tutor-py-2"><code>validate: (v) => v === 'test' || 'Must be test'</code></td>
							<td class="tutor-px-4 tutor-py-2">Custom validation function.</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<!-- Configuration Options -->
		<div class="tutor-mb-8">
			<h3 class="tutor-text-lg tutor-font-semibold tutor-mb-3">Form Configuration Options</h3>
			<div class="tutor-overflow-x-auto">
				<table class="tutor-table tutor-table-bordered">
					<thead>
						<tr>
							<th class="tutor-px-4 tutor-py-2">Option</th>
							<th class="tutor-px-4 tutor-py-2">Type</th>
							<th class="tutor-px-4 tutor-py-2">Default</th>
							<th class="tutor-px-4 tutor-py-2">Description</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>id</code></td>
							<td class="tutor-px-4 tutor-py-2">string</td>
							<td class="tutor-px-4 tutor-py-2">undefined</td>
							<td class="tutor-px-4 tutor-py-2">Unique form ID for global API access.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>mode</code></td>
							<td class="tutor-px-4 tutor-py-2">'onChange' | 'onBlur' | 'onSubmit'</td>
							<td class="tutor-px-4 tutor-py-2">'onBlur'</td>
							<td class="tutor-px-4 tutor-py-2">When validation should trigger initially.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>shouldFocusError</code></td>
							<td class="tutor-px-4 tutor-py-2">boolean</td>
							<td class="tutor-px-4 tutor-py-2">true</td>
							<td class="tutor-px-4 tutor-py-2">Auto-focus first error field on submit.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>shouldScrollToError</code></td>
							<td class="tutor-px-4 tutor-py-2">boolean</td>
							<td class="tutor-px-4 tutor-py-2">true</td>
							<td class="tutor-px-4 tutor-py-2">Auto-scroll to error field when focused.</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<!-- Type Definitions -->
		<div class="tutor-mb-8">
			<h3 class="tutor-text-lg tutor-font-semibold tutor-mb-3">Type Definitions</h3>
			<div class="tutor-overflow-x-auto">
				<table class="tutor-table tutor-table-bordered">
					<thead>
						<tr>
							<th class="tutor-px-4 tutor-py-2">Type</th>
							<th class="tutor-px-4 tutor-py-2">Properties</th>
							<th class="tutor-px-4 tutor-py-2">Description</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>SetValueOptions</code></td>
							<td class="tutor-px-4 tutor-py-2">
								<code>shouldValidate?: boolean</code><br>
								<code>shouldTouch?: boolean</code><br>
								<code>shouldDirty?: boolean</code>
							</td>
							<td class="tutor-px-4 tutor-py-2">Options for setValue method.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>FocusOptions</code></td>
							<td class="tutor-px-4 tutor-py-2">
								<code>shouldSelect?: boolean</code>
							</td>
							<td class="tutor-px-4 tutor-py-2">Options for setFocus method.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>FieldError</code></td>
							<td class="tutor-px-4 tutor-py-2">
								<code>type: string</code><br>
								<code>message: string</code>
							</td>
							<td class="tutor-px-4 tutor-py-2">Error object structure.</td>
						</tr>
						<tr>
							<td class="tutor-px-4 tutor-py-2"><code>FormState</code></td>
							<td class="tutor-px-4 tutor-py-2">
								<code>values: object</code><br>
								<code>errors: object</code><br>
								<code>touchedFields: object</code><br>
								<code>dirtyFields: object</code><br>
								<code>isValid: boolean</code><br>
								<code>isSubmitting: boolean</code><br>
								<code>isValidating: boolean</code>
							</td>
							<td class="tutor-px-4 tutor-py-2">Complete form state object.</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<!-- Form Service API Example -->
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
				<div class="tutor-mt-8">
					<h3 class="tutor-font-semibold tutor-mb-4">External Form Controls</h3>
					<p class="tutor-text-sm tutor-text-gray-600 tutor-mb-4">
						These buttons control the form using <code class="tutor-bg-gray-200 tutor-px-1 tutor-rounded">TutorCore.form</code> API from outside the Alpine component. Open browser console to see the outputs.
					</p>

					<div class="tutor-grid tutor-grid-cols-1 md:tutor-grid-cols-2 tutor-gap-3">
						<!-- Populate Form -->
						<div>
							<button 
								onclick="TutorCore.form.setValues('profile-form', {
									firstName: 'Jane',
									lastName: 'Smith',
									profileEmail: 'jane.smith@example.com'
								}, { shouldValidate: true })"
								class="tutor-btn tutor-btn-outline tutor-btn-small tutor-w-full"
							>
								📝 Populate Form
							</button>
						</div>

						<!-- Get All Values -->
						<div>
							<button 
								onclick="console.log('Form Values:', TutorCore.form.getValues('profile-form'))"
								class="tutor-btn tutor-btn-outline tutor-btn-small tutor-w-full"
							>
								📋 Get All Values
							</button>
						</div>

						<!-- Set Single Value -->
						<div>
							<button 
								onclick="TutorCore.form.setValue('profile-form', 'firstName', 'John', { shouldValidate: true })"
								class="tutor-btn tutor-btn-outline tutor-btn-small tutor-w-full"
							>
								✏️ Set First Name
							</button>
						</div>

						<!-- Validate Form -->
						<div>
							<button 
								onclick="TutorCore.form.trigger('profile-form').then(isValid => console.log('Valid:', isValid))"
								class="tutor-btn tutor-btn-outline tutor-btn-small tutor-w-full"
							>
								✅ Validate All
							</button>
						</div>

						<!-- Set Error -->
						<div>
							<button 
								onclick="TutorCore.form.setError('profile-form', 'profileEmail', { type: 'server', message: 'Email already exists' })"
								class="tutor-btn tutor-btn-outline tutor-btn-small tutor-w-full"
							>
								⚠️ Set Email Error
							</button>
						</div>

						<!-- Clear Errors -->
						<div>
							<button 
								onclick="TutorCore.form.clearErrors('profile-form')"
								class="tutor-btn tutor-btn-outline tutor-btn-small tutor-w-full"
							>
								🧹 Clear Errors
							</button>
						</div>

						<!-- Focus Field -->
						<div>
							<button 
								onclick="TutorCore.form.setFocus('profile-form', 'firstName', { shouldSelect: true })"
								class="tutor-btn tutor-btn-outline tutor-btn-small tutor-w-full"
							>
								🎯 Focus First Name
							</button>
						</div>

						<!-- Reset Form -->
						<div>
							<button 
								onclick="TutorCore.form.reset('profile-form')"
								class="tutor-btn tutor-btn-outline tutor-btn-small tutor-w-full"
							>
								🔄 Reset Form
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
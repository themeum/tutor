<?php
/**
 * Button component documentation
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

?>
<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<h1 class="tutor-text-2xl tutor-font-bold tutor-mb-6">Buttons</h1>

	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Button Variants</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Button variants for different use cases and visual hierarchy.
		</p>
		<div class="tutor-p-6 tutor-bg-gray-50 tutor-rounded-md">
			<div class="tutor-flex tutor-items-center tutor-gap-3 tutor-flex-wrap">
				<button class="tutor-btn tutor-btn-primary">Primary</button>
				<button class="tutor-btn tutor-btn-primary-soft">Primary Soft</button>
				<button class="tutor-btn tutor-btn-destructive">Destructive</button>
				<button class="tutor-btn tutor-btn-destructive-soft">Destructive Soft</button>
				<button class="tutor-btn tutor-btn-secondary">Secondary</button>
				<button class="tutor-btn tutor-btn-outline">Outline</button>
				<button class="tutor-btn tutor-btn-ghost">Ghost</button>
				<button class="tutor-btn tutor-btn-ghost-brand">Ghost Brand</button>
				<button class="tutor-btn tutor-btn-link">Link</button>
				<button class="tutor-btn tutor-btn-link-gray">Link Gray</button>
				<button class="tutor-btn tutor-btn-link-destructive">Link Destructive</button>
			</div>
		</div>
	</div>
	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Icon Buttons</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Icon button with variants.
		</p>
		<div class="tutor-p-6 tutor-bg-gray-50 tutor-rounded-md">
			<div class="tutor-flex tutor-gap-3 tutor-flex-wrap">
			<div class="tutor-flex tutor-gap-3 tutor-flex-wrap">
				<button class="tutor-btn tutor-btn-primary tutor-btn-icon">
					<svg width="20" height="20" viewBox="0 0 20 20" fill="none"
						xmlns="http://www.w3.org/2000/svg">
						<path
							d="M11.617 5.28089C12.9779 6.05395 14.0472 6.66146 14.809 7.21795C15.576 7.77827 16.1434 8.36389 16.3465 9.13605C16.4955 9.70222 16.4955 10.2979 16.3465 10.8641C16.1434 11.6362 15.576 12.2218 14.809 12.7821C14.0472 13.3386 12.9779 13.9461 11.6171 14.7192C10.3026 15.466 9.19413 16.0957 8.35263 16.4537C7.50438 16.8145 6.73103 16.9974 5.97943 16.7844C5.42706 16.6278 4.92447 16.3307 4.51959 15.9222C3.97012 15.3679 3.74955 14.6016 3.6452 13.6796C3.54161 12.7641 3.54162 11.5659 3.54163 10.0418V9.9583C3.54162 8.43422 3.54161 7.23596 3.6452 6.32059C3.74955 5.39847 3.97012 4.63223 4.51959 4.07784C4.92447 3.66936 5.42706 3.37227 5.97943 3.21574C6.73103 3.00276 7.50438 3.18563 8.35263 3.54643C9.19413 3.90435 10.3026 4.53409 11.617 5.28089Z"
							fill="currentColor" />
					</svg>
				</button>
				<button class="tutor-btn tutor-btn-primary-soft"><svg width="20" height="20"
						viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M11.617 5.28089C12.9779 6.05395 14.0472 6.66146 14.809 7.21795C15.576 7.77827 16.1434 8.36389 16.3465 9.13605C16.4955 9.70222 16.4955 10.2979 16.3465 10.8641C16.1434 11.6362 15.576 12.2218 14.809 12.7821C14.0472 13.3386 12.9779 13.9461 11.6171 14.7192C10.3026 15.466 9.19413 16.0957 8.35263 16.4537C7.50438 16.8145 6.73103 16.9974 5.97943 16.7844C5.42706 16.6278 4.92447 16.3307 4.51959 15.9222C3.97012 15.3679 3.74955 14.6016 3.6452 13.6796C3.54161 12.7641 3.54162 11.5659 3.54163 10.0418V9.9583C3.54162 8.43422 3.54161 7.23596 3.6452 6.32059C3.74955 5.39847 3.97012 4.63223 4.51959 4.07784C4.92447 3.66936 5.42706 3.37227 5.97943 3.21574C6.73103 3.00276 7.50438 3.18563 8.35263 3.54643C9.19413 3.90435 10.3026 4.53409 11.617 5.28089Z"
							fill="currentColor" />
					</svg>
				</button>
				<button class="tutor-btn tutor-btn-destructive tutor-btn-icon">
					<svg width="20" height="20" viewBox="0 0 20 20" fill="none"
						xmlns="http://www.w3.org/2000/svg">
						<path
							d="M11.617 5.28089C12.9779 6.05395 14.0472 6.66146 14.809 7.21795C15.576 7.77827 16.1434 8.36389 16.3465 9.13605C16.4955 9.70222 16.4955 10.2979 16.3465 10.8641C16.1434 11.6362 15.576 12.2218 14.809 12.7821C14.0472 13.3386 12.9779 13.9461 11.6171 14.7192C10.3026 15.466 9.19413 16.0957 8.35263 16.4537C7.50438 16.8145 6.73103 16.9974 5.97943 16.7844C5.42706 16.6278 4.92447 16.3307 4.51959 15.9222C3.97012 15.3679 3.74955 14.6016 3.6452 13.6796C3.54161 12.7641 3.54162 11.5659 3.54163 10.0418V9.9583C3.54162 8.43422 3.54161 7.23596 3.6452 6.32059C3.74955 5.39847 3.97012 4.63223 4.51959 4.07784C4.92447 3.66936 5.42706 3.37227 5.97943 3.21574C6.73103 3.00276 7.50438 3.18563 8.35263 3.54643C9.19413 3.90435 10.3026 4.53409 11.617 5.28089Z"
							fill="currentColor" />
					</svg>
				</button>
				<button class="tutor-btn tutor-btn-destructive-soft"><svg width="20" height="20"
						viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M11.617 5.28089C12.9779 6.05395 14.0472 6.66146 14.809 7.21795C15.576 7.77827 16.1434 8.36389 16.3465 9.13605C16.4955 9.70222 16.4955 10.2979 16.3465 10.8641C16.1434 11.6362 15.576 12.2218 14.809 12.7821C14.0472 13.3386 12.9779 13.9461 11.6171 14.7192C10.3026 15.466 9.19413 16.0957 8.35263 16.4537C7.50438 16.8145 6.73103 16.9974 5.97943 16.7844C5.42706 16.6278 4.92447 16.3307 4.51959 15.9222C3.97012 15.3679 3.74955 14.6016 3.6452 13.6796C3.54161 12.7641 3.54162 11.5659 3.54163 10.0418V9.9583C3.54162 8.43422 3.54161 7.23596 3.6452 6.32059C3.74955 5.39847 3.97012 4.63223 4.51959 4.07784C4.92447 3.66936 5.42706 3.37227 5.97943 3.21574C6.73103 3.00276 7.50438 3.18563 8.35263 3.54643C9.19413 3.90435 10.3026 4.53409 11.617 5.28089Z"
							fill="currentColor" />
					</svg>
				</button>
				<button class="tutor-btn tutor-btn-secondary"><svg width="20" height="20"
						viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M11.617 5.28089C12.9779 6.05395 14.0472 6.66146 14.809 7.21795C15.576 7.77827 16.1434 8.36389 16.3465 9.13605C16.4955 9.70222 16.4955 10.2979 16.3465 10.8641C16.1434 11.6362 15.576 12.2218 14.809 12.7821C14.0472 13.3386 12.9779 13.9461 11.6171 14.7192C10.3026 15.466 9.19413 16.0957 8.35263 16.4537C7.50438 16.8145 6.73103 16.9974 5.97943 16.7844C5.42706 16.6278 4.92447 16.3307 4.51959 15.9222C3.97012 15.3679 3.74955 14.6016 3.6452 13.6796C3.54161 12.7641 3.54162 11.5659 3.54163 10.0418V9.9583C3.54162 8.43422 3.54161 7.23596 3.6452 6.32059C3.74955 5.39847 3.97012 4.63223 4.51959 4.07784C4.92447 3.66936 5.42706 3.37227 5.97943 3.21574C6.73103 3.00276 7.50438 3.18563 8.35263 3.54643C9.19413 3.90435 10.3026 4.53409 11.617 5.28089Z"
							fill="currentColor" />
					</svg>
				</button>
				<button class="tutor-btn tutor-btn-outline"><svg width="20" height="20"
						viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M11.617 5.28089C12.9779 6.05395 14.0472 6.66146 14.809 7.21795C15.576 7.77827 16.1434 8.36389 16.3465 9.13605C16.4955 9.70222 16.4955 10.2979 16.3465 10.8641C16.1434 11.6362 15.576 12.2218 14.809 12.7821C14.0472 13.3386 12.9779 13.9461 11.6171 14.7192C10.3026 15.466 9.19413 16.0957 8.35263 16.4537C7.50438 16.8145 6.73103 16.9974 5.97943 16.7844C5.42706 16.6278 4.92447 16.3307 4.51959 15.9222C3.97012 15.3679 3.74955 14.6016 3.6452 13.6796C3.54161 12.7641 3.54162 11.5659 3.54163 10.0418V9.9583C3.54162 8.43422 3.54161 7.23596 3.6452 6.32059C3.74955 5.39847 3.97012 4.63223 4.51959 4.07784C4.92447 3.66936 5.42706 3.37227 5.97943 3.21574C6.73103 3.00276 7.50438 3.18563 8.35263 3.54643C9.19413 3.90435 10.3026 4.53409 11.617 5.28089Z"
							fill="currentColor" />
					</svg>
				</button>
				<button class="tutor-btn tutor-btn-ghost"><svg width="20" height="20"
						viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M11.617 5.28089C12.9779 6.05395 14.0472 6.66146 14.809 7.21795C15.576 7.77827 16.1434 8.36389 16.3465 9.13605C16.4955 9.70222 16.4955 10.2979 16.3465 10.8641C16.1434 11.6362 15.576 12.2218 14.809 12.7821C14.0472 13.3386 12.9779 13.9461 11.6171 14.7192C10.3026 15.466 9.19413 16.0957 8.35263 16.4537C7.50438 16.8145 6.73103 16.9974 5.97943 16.7844C5.42706 16.6278 4.92447 16.3307 4.51959 15.9222C3.97012 15.3679 3.74955 14.6016 3.6452 13.6796C3.54161 12.7641 3.54162 11.5659 3.54163 10.0418V9.9583C3.54162 8.43422 3.54161 7.23596 3.6452 6.32059C3.74955 5.39847 3.97012 4.63223 4.51959 4.07784C4.92447 3.66936 5.42706 3.37227 5.97943 3.21574C6.73103 3.00276 7.50438 3.18563 8.35263 3.54643C9.19413 3.90435 10.3026 4.53409 11.617 5.28089Z"
							fill="currentColor" />
					</svg>
				</button>
				<button class="tutor-btn tutor-btn-link"><svg width="20" height="20" viewBox="0 0 20 20"
						fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M11.617 5.28089C12.9779 6.05395 14.0472 6.66146 14.809 7.21795C15.576 7.77827 16.1434 8.36389 16.3465 9.13605C16.4955 9.70222 16.4955 10.2979 16.3465 10.8641C16.1434 11.6362 15.576 12.2218 14.809 12.7821C14.0472 13.3386 12.9779 13.9461 11.6171 14.7192C10.3026 15.466 9.19413 16.0957 8.35263 16.4537C7.50438 16.8145 6.73103 16.9974 5.97943 16.7844C5.42706 16.6278 4.92447 16.3307 4.51959 15.9222C3.97012 15.3679 3.74955 14.6016 3.6452 13.6796C3.54161 12.7641 3.54162 11.5659 3.54163 10.0418V9.9583C3.54162 8.43422 3.54161 7.23596 3.6452 6.32059C3.74955 5.39847 3.97012 4.63223 4.51959 4.07784C4.92447 3.66936 5.42706 3.37227 5.97943 3.21574C6.73103 3.00276 7.50438 3.18563 8.35263 3.54643C9.19413 3.90435 10.3026 4.53409 11.617 5.28089Z"
							fill="currentColor" />
					</svg>
				</button>
			</div>
		</div>
	</div>

	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Button Sizes</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Button sizes to fit different contexts and layouts.
		</p>
		<div class="tutor-p-6 tutor-bg-gray-50 tutor-rounded-md">
			<div class="tutor-flex tutor-gap-3 tutor-items-center tutor-flex-wrap">
				<button class="tutor-btn tutor-btn-primary tutor-btn-x-small">X-Small</button>
				<button class="tutor-btn tutor-btn-primary tutor-btn-small">Small</button>
				<button class="tutor-btn tutor-btn-primary tutor-btn-medium">Medium</button>
				<button class="tutor-btn tutor-btn-primary tutor-btn-large">Large</button>
			</div>
			<div class="tutor-flex tutor-gap-3 tutor-items-center tutor-flex-wrap tutor-mt-5">
				<button class="tutor-btn tutor-btn-primary tutor-btn-x-small tutor-btn-icon">
					<svg width="16" height="16" viewBox="0 0 20 20" fill="none"
						xmlns="http://www.w3.org/2000/svg">
						<path
							d="M11.617 5.28089C12.9779 6.05395 14.0472 6.66146 14.809 7.21795C15.576 7.77827 16.1434 8.36389 16.3465 9.13605C16.4955 9.70222 16.4955 10.2979 16.3465 10.8641C16.1434 11.6362 15.576 12.2218 14.809 12.7821C14.0472 13.3386 12.9779 13.9461 11.6171 14.7192C10.3026 15.466 9.19413 16.0957 8.35263 16.4537C7.50438 16.8145 6.73103 16.9974 5.97943 16.7844C5.42706 16.6278 4.92447 16.3307 4.51959 15.9222C3.97012 15.3679 3.74955 14.6016 3.6452 13.6796C3.54161 12.7641 3.54162 11.5659 3.54163 10.0418V9.9583C3.54162 8.43422 3.54161 7.23596 3.6452 6.32059C3.74955 5.39847 3.97012 4.63223 4.51959 4.07784C4.92447 3.66936 5.42706 3.37227 5.97943 3.21574C6.73103 3.00276 7.50438 3.18563 8.35263 3.54643C9.19413 3.90435 10.3026 4.53409 11.617 5.28089Z"
							fill="currentColor" />
					</svg>
				</button>
				<button class="tutor-btn tutor-btn-primary tutor-btn-small tutor-btn-icon">
					<svg width="16" height="16" viewBox="0 0 20 20" fill="none"
						xmlns="http://www.w3.org/2000/svg">
						<path
							d="M11.617 5.28089C12.9779 6.05395 14.0472 6.66146 14.809 7.21795C15.576 7.77827 16.1434 8.36389 16.3465 9.13605C16.4955 9.70222 16.4955 10.2979 16.3465 10.8641C16.1434 11.6362 15.576 12.2218 14.809 12.7821C14.0472 13.3386 12.9779 13.9461 11.6171 14.7192C10.3026 15.466 9.19413 16.0957 8.35263 16.4537C7.50438 16.8145 6.73103 16.9974 5.97943 16.7844C5.42706 16.6278 4.92447 16.3307 4.51959 15.9222C3.97012 15.3679 3.74955 14.6016 3.6452 13.6796C3.54161 12.7641 3.54162 11.5659 3.54163 10.0418V9.9583C3.54162 8.43422 3.54161 7.23596 3.6452 6.32059C3.74955 5.39847 3.97012 4.63223 4.51959 4.07784C4.92447 3.66936 5.42706 3.37227 5.97943 3.21574C6.73103 3.00276 7.50438 3.18563 8.35263 3.54643C9.19413 3.90435 10.3026 4.53409 11.617 5.28089Z"
							fill="currentColor" />
					</svg>
				</button>
				<button class="tutor-btn tutor-btn-primary tutor-btn-medium tutor-btn-icon">
					<svg width="20" height="20" viewBox="0 0 20 20" fill="none"
						xmlns="http://www.w3.org/2000/svg">
						<path
							d="M11.617 5.28089C12.9779 6.05395 14.0472 6.66146 14.809 7.21795C15.576 7.77827 16.1434 8.36389 16.3465 9.13605C16.4955 9.70222 16.4955 10.2979 16.3465 10.8641C16.1434 11.6362 15.576 12.2218 14.809 12.7821C14.0472 13.3386 12.9779 13.9461 11.6171 14.7192C10.3026 15.466 9.19413 16.0957 8.35263 16.4537C7.50438 16.8145 6.73103 16.9974 5.97943 16.7844C5.42706 16.6278 4.92447 16.3307 4.51959 15.9222C3.97012 15.3679 3.74955 14.6016 3.6452 13.6796C3.54161 12.7641 3.54162 11.5659 3.54163 10.0418V9.9583C3.54162 8.43422 3.54161 7.23596 3.6452 6.32059C3.74955 5.39847 3.97012 4.63223 4.51959 4.07784C4.92447 3.66936 5.42706 3.37227 5.97943 3.21574C6.73103 3.00276 7.50438 3.18563 8.35263 3.54643C9.19413 3.90435 10.3026 4.53409 11.617 5.28089Z"
							fill="currentColor" />
					</svg>
				</button>
				<button class="tutor-btn tutor-btn-primary tutor-btn-large tutor-btn-icon">
					<svg width="20" height="20" viewBox="0 0 20 20" fill="none"
						xmlns="http://www.w3.org/2000/svg">
						<path
							d="M11.617 5.28089C12.9779 6.05395 14.0472 6.66146 14.809 7.21795C15.576 7.77827 16.1434 8.36389 16.3465 9.13605C16.4955 9.70222 16.4955 10.2979 16.3465 10.8641C16.1434 11.6362 15.576 12.2218 14.809 12.7821C14.0472 13.3386 12.9779 13.9461 11.6171 14.7192C10.3026 15.466 9.19413 16.0957 8.35263 16.4537C7.50438 16.8145 6.73103 16.9974 5.97943 16.7844C5.42706 16.6278 4.92447 16.3307 4.51959 15.9222C3.97012 15.3679 3.74955 14.6016 3.6452 13.6796C3.54161 12.7641 3.54162 11.5659 3.54163 10.0418V9.9583C3.54162 8.43422 3.54161 7.23596 3.6452 6.32059C3.74955 5.39847 3.97012 4.63223 4.51959 4.07784C4.92447 3.66936 5.42706 3.37227 5.97943 3.21574C6.73103 3.00276 7.50438 3.18563 8.35263 3.54643C9.19413 3.90435 10.3026 4.53409 11.617 5.28089Z"
							fill="currentColor" />
					</svg>
				</button>
			</div>
		</div>
	</div>

	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Button States</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Different button states for various interaction scenarios.
		</p>
		<div class="tutor-p-6 tutor-bg-gray-50 tutor-rounded-md">
			<div class="tutor-flex tutor-gap-3 tutor-flex-wrap">
				<button class="tutor-btn tutor-btn-primary">Normal</button>
				<button class="tutor-btn tutor-btn-primary" disabled>Disabled</button>
				<button class="tutor-btn tutor-btn-primary tutor-btn-loading">Loading</button>
			</div>
		</div>
	</div>

	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Button Group</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Button horizontal group
		</p>
		<div class="tutor-p-6 tutor-bg-gray-50 tutor-rounded-md">
			<div class="tutor-btn-group">
				<button class="tutor-btn tutor-btn-primary">Primary</button>
				<button class="tutor-btn tutor-btn-primary-soft">Primary Soft</button>
				<button class="tutor-btn tutor-btn-destructive">Destructive</button>
				<button class="tutor-btn tutor-btn-destructive-soft">Destructive Soft</button>
				<button class="tutor-btn tutor-btn-secondary">Secondary</button>
				<button class="tutor-btn tutor-btn-outline">Outline</button>
			</div>
		</div>
	</div>

	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Button Group Vertical</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Button vertical group
		</p>
		<div class="tutor-p-6 tutor-bg-gray-50 tutor-rounded-md">
			<div class="tutor-btn-group-vertical">
				<button class="tutor-btn tutor-btn-primary">Primary</button>
				<button class="tutor-btn tutor-btn-primary-soft">Primary Soft</button>
				<button class="tutor-btn tutor-btn-destructive">Destructive</button>
				<button class="tutor-btn tutor-btn-destructive-soft">Destructive Soft</button>
				<button class="tutor-btn tutor-btn-secondary">Secondary</button>
				<button class="tutor-btn tutor-btn-outline">Outline</button>
			</div>
		</div>
	</div>
	</div>

	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Button Block</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Full width button
		</p>
		<div class="tutor-p-6 tutor-bg-gray-50 tutor-rounded-md">
			<div class="tutor-btn-group-vertical">
				<button class="tutor-btn tutor-btn-primary tutor-btn-large tutor-btn-block">Primary</button>
			</div>
		</div>
	</div>
</section>

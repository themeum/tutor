<?php
/**
 * Card component documentation
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

?>
<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<h1 class="tutor-text-2xl tutor-font-bold tutor-mb-6">Cards</h1>

	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Basic Card</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Basic card component with default styling.
		</p>
		<div class="tutor-p-6 tutor-bg-gray-50 tutor-rounded-md">
			<div class="tutor-card">
				<h3>Card Title</h3>
				<p>
					This is a basic card component with default padding and elevation. It demonstrates the base card styles.
				</p>
			</div>
		</div>
	</div>

	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Card Padding Sizes</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Different padding sizes for cards using modifier classes.
		</p>
		<div class="tutor-p-6 tutor-bg-gray-50 tutor-rounded-md">
			<div class="tutor-flex tutor-gap-4 tutor-flex-wrap">
				<div class="tutor-card tutor-card-padding-small">
					<h4>Padding Small</h4>
					<p>8px padding</p>
				</div>
				<div class="tutor-card">
					<h4>Padding Medium</h4>
					<p>16px padding (default)</p>
				</div>
				<div class="tutor-card tutor-card-padding-large">
					<h4>Padding Large</h4>
					<p>24px padding</p>
				</div>
			</div>
		</div>
	</div>

	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Card Border Radius Variations</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Different border radius sizes for cards using modifier classes.
		</p>
		<div class="tutor-p-6 tutor-bg-gray-50 tutor-rounded-md">
			<div class="tutor-flex tutor-gap-4 tutor-flex-wrap">
				<div class="tutor-card tutor-card-rounded-md">
					<h4 class="tutor-mt-0 tutor-mb-4">Rounded MD</h4>
					<p class="tutor-mb-0 tutor-text-small">Medium radius</p>
				</div>
				<div class="tutor-card">
					<h4 class="tutor-mt-0 tutor-mb-4">Rounded LG</h4>
					<p class="tutor-mb-0 tutor-text-small">Large radius (default)</p>
				</div>
				<div class="tutor-card tutor-card-rounded-2xl">
					<h4 class="tutor-mt-0 tutor-mb-4">Rounded 2XL</h4>
					<p class="tutor-mb-0 tutor-text-small">Extra large radius</p>
				</div>
			</div>
		</div>
	</div>
</section>

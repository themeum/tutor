<?php
/**
 * Tabs
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.conm>
 * @link https://themeum.com
 * @since 4.0.0
 */

?>

<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<h1 class="tutor-text-2xl tutor-font-bold tutor-mb-6">Statics</h1>
	<p class="tutor-text-gray-600 tutor-mb-4">
		Statics with two sizes: medium and large. There are three types of statics: progress, complete and locked. Control the animation by passing `animation` to the config. Label can be hidden by passing `showLabel` to the config.
	</p>

	<h2 class="tutor-text-xl tutor-font-bold tutor-mb-6">Small</h2>
	<div class="tutor-mb-8 tutor-flex tutor-gap-8">
		<div x-data="tutorStatics({ value: 0, type: 'progress' })">
			<div x-html="render()" ></div>
		</div>
		<div x-data="tutorStatics({ value: 75, type: 'progress' })">
			<div x-html="render()" ></div>
		</div>
		<div x-data="tutorStatics({ type: 'complete' })">
			<div x-html="render()" ></div>
		</div>
		<div x-data="tutorStatics({ type: 'locked' })">
			<div x-html="render()" ></div>
		</div>
	</div>

	<h2 class="tutor-text-xl tutor-font-bold tutor-mb-6">Medium</h2>
	<div class="tutor-mb-8 tutor-flex tutor-gap-8">
		<div x-data="tutorStatics({ value: 0, size: 'medium', type: 'progress' })">
			<div x-html="render()" ></div>
		</div>
		<div x-data="tutorStatics({ value: 75, size: 'medium', type: 'progress' })">
			<div x-html="render()" ></div>
		</div>
		<div x-data="tutorStatics({ size: 'medium', type: 'complete' })">
			<div x-html="render()" ></div>
		</div>
		<div x-data="tutorStatics({ size: 'medium', type: 'locked' })">
			<div x-html="render()" ></div>
		</div>
	</div>

	<h2 class="tutor-text-xl tutor-font-bold tutor-mb-6">Large</h2>
	<div class="tutor-mb-8 tutor-flex tutor-gap-8">
		<div x-data="tutorStatics({ value: 0, size: 'large', type: 'progress' })">
			<div x-html="render()" ></div>
		</div>
		<div x-data="tutorStatics({ value: 75, size: 'large', type: 'progress' })">
			<div x-html="render()" ></div>
		</div>
		<div x-data="tutorStatics({ size: 'large', type: 'complete' })">
			<div x-html="render()" ></div>
		</div>
		<div x-data="tutorStatics({ size: 'large', type: 'locked' })">
			<div x-html="render()" ></div>
		</div>
	</div>

	<h2 class="tutor-text-xl tutor-font-bold tutor-mb-6">Without label and animated</h2>
	<div class="tutor-mb-8 tutor-flex tutor-gap-4">
		<div x-data="tutorStatics({ value: 0, size: 'large', type: 'progress', showLabel: false })">
			<div x-html="render()" ></div>
		</div>
		<div x-data="tutorStatics({ value: 75, size: 'large', type: 'progress', animated: true })">
			<div x-html="render()" ></div>
		</div>
	</div>
</section>

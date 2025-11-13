<?php
/**
 * Skeleton
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.conm>
 * @link https://themeum.com
 * @since 4.0.0
 */

?>

<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<h1 class="tutor-text-2xl tutor-font-bold tutor-mb-6">Skeleton</h1>
	<p class="tutor-text-gray-600 tutor-mb-4">
		Skeleton component with rounded corners and animations. Animation duration can be customized with CSS variable <code>--tutor-animation-duration</code>. Use width 
	</p>

	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Rounded</h2>
		<div class="tutor-skeleton tutor-skeleton-round tutor-mb-4" style="width: 200px; height: 200px;"></div>
	</div>

	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Square</h2>
		<div class="tutor-skeleton tutor-mb-4" style="width: 200px; height: 200px;"></div>
	</div>

	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Customization</h2>
		<div class="tutor-flex tutor-flex-column tutor-gap-4">
			<div class="tutor-flex tutor-items-center tutor-gap-4">
				<div class="tutor-skeleton tutor-skeleton-round" style="--tutor-animation-duration: 0.5s; width: 100px; height: 100px;"></div>
				<div class="tutor-skeleton" style="width: 500px; height: 50px;"></div>
			</div>
			<div class="tutor-flex tutor-flex-column tutor-mb-4 tutor-gap-4">
				<div class="tutor-skeleton" style="--tutor-animation-duration: 0.5s; width: 610px; height: 50px;"></div>
				<div class="tutor-skeleton" style="width: 610px; height: 50px;"></div>
		</div>
	</div>
</section>
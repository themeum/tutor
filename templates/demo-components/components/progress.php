<?php
/**
 * Progress Bar
 *
 * @package     Tutor\Templates
 * @author      Themeum <themeum@blindword.org>
 * @link        https://themeum.com/
 */

?>

<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<h1 class="tutor-text-2xl tutor-font-bold tutor-mb-6">Progress Bar</h1>
	<p class="tutor-text-gray-600 tutor-mb-8">
		Progress Bar with two variants. Use `data-tutor-animated` attribute to animate the progress bar along with `--tutor-progress-width` variable to set the width of the progress bar.
	</p>
	<div class="tutor-mb-8">
		<div class="tutor-progress-bar" data-tutor-animated>
			<div class="tutor-progress-bar-fill" style="--tutor-progress-width: 75%;"></div>
		</div>
	</div>
	<div class="tutor-mb-8">
		<div class="tutor-progress-bar tutor-progress-bar-brand">
			<div class="tutor-progress-bar-fill" style="width: 30%"></div>
		</div>
	</div>
</section>
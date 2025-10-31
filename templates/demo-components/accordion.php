<?php
/**
 * Accordion core component.
 *
 * @package TutorLMS\Templates
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
?>

<div x-data="TutorCore.accordion({ multiple: true, defaultOpen: [0] })" x-init="initializeClasses()" class="tutor-accordion">
	<div class="tutor-accordion-item">
		<button @click="toggle(0)" @keydown="handleKeydown($event, 0)" :aria-expanded="isOpen(0)" class="tutor-accordion-header tutor-accordion-trigger" aria-controls="tutor-acc-panel-0" id="tutor-acc-trigger-0">
			<span class="tutor-accordion-title">About this Course</span>
			<span class="tutor-accordion-icon" aria-hidden="true">
				<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_DOWN, 24, 24 ); ?>
			</span>
		</button>
		<div id="tutor-acc-panel-0" role="region" aria-labelledby="tutor-acc-trigger-0" class="tutor-accordion-content">
			<div class="tutor-accordion-body">
				<p class="tutor-p1">Overview of the course, structure, and outcomes.</p>
				<p class="tutor-p1">Overview of the course, structure, and outcomes.</p>
				<p class="tutor-p1">Overview of the course, structure, and outcomes.</p>
				<p class="tutor-p1">Overview of the course, structure, and outcomes.</p>
			</div>
		</div>
	</div>

	<div class="tutor-accordion-item">
		<button @click="toggle(1)" @keydown="handleKeydown($event, 1)" :aria-expanded="isOpen(1)" class="tutor-accordion-header tutor-accordion-trigger" aria-controls="tutor-acc-panel-1" id="tutor-acc-trigger-1">
			<span class="tutor-accordion-title">What you'll learn</span>
			<span class="tutor-accordion-icon" aria-hidden="true">
				<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_DOWN, 24, 24 ); ?>
			</span>
		</button>
		<div id="tutor-acc-panel-1" role="region" aria-labelledby="tutor-acc-trigger-1" class="tutor-accordion-content">
			<div class="tutor-accordion-body">
				<ul>
					<li class="tutor-p2">Master Figma basics and essential tools.</li>
					<li class="tutor-p2">Build practical projects for your portfolio.</li>
				</ul>
			</div>
		</div>
	</div>
</div>
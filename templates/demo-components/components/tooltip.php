<?php
/**
 * Tooltip component documentation
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

?>
<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm tutor-mt-8">
	<h1 class="tutor-text-2xl tutor-font-bold tutor-mb-6">Tooltip Component</h1>

	<!-- Basic Tooltip -->
	<div class="tutor-mb-10">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Basic Tooltip</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Standard tooltip with hover interaction and top placement.
		</p>

		<div x-data="tutorTooltip({ placement: 'top' })" class="tutor-tooltip-wrap">
			<button x-ref="trigger" class="tutor-btn tutor-btn-primary">Hover for Tooltip</button>
			<div x-ref="content" x-show="open" x-cloak x-transition class="tutor-tooltip">This is a helpful tooltip!</div>
		</div>
	</div>

	<!-- Placement Variations -->
	<div class="tutor-mb-10">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Placement Variations</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Position the tooltip relative to the trigger.
		</p>

		<div class="tutor-flex tutor-gap-4 tutor-flex-wrap tutor-py-6">
			<!-- Top -->
			<div x-data="tutorTooltip({ placement: 'top' })" class="tutor-tooltip-wrap">
				<button x-ref="trigger" class="tutor-btn tutor-btn-secondary">Top</button>
				<div x-ref="content" x-show="open" x-cloak x-transition class="tutor-tooltip">Top Tooltip</div>
			</div>

			<!-- Bottom -->
			<div x-data="tutorTooltip({ placement: 'bottom' })" class="tutor-tooltip-wrap">
				<button x-ref="trigger" class="tutor-btn tutor-btn-secondary">Bottom</button>
				<div x-ref="content" x-show="open" x-cloak x-transition class="tutor-tooltip">Bottom Tooltip</div>
			</div>

			<!-- Start -->
			<div x-data="tutorTooltip({ placement: 'start' })" class="tutor-tooltip-wrap">
				<button x-ref="trigger" class="tutor-btn tutor-btn-secondary">Start (Left)</button>
				<div x-ref="content" x-show="open" x-cloak x-transition class="tutor-tooltip">Start Tooltip</div>
			</div>

			<!-- End -->
			<div x-data="tutorTooltip({ placement: 'end' })" class="tutor-tooltip-wrap">
				<button x-ref="trigger" class="tutor-btn tutor-btn-secondary">End (Right)</button>
				<div x-ref="content" x-show="open" x-cloak x-transition class="tutor-tooltip">End Tooltip</div>
			</div>
		</div>
	</div>

	<!-- Size Variations -->
	<div class="tutor-mb-10">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Size Variations</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Choice of two sizes: small (default) for brief hints, or large for more detailed descriptions.
		</p>

		<div class="tutor-flex tutor-gap-4 tutor-flex-wrap">
			<!-- Small (Default) -->
			<div x-data="tutorTooltip({ size: 'small' })" class="tutor-tooltip-wrap">
				<button x-ref="trigger" class="tutor-btn tutor-btn-outline-primary">Small (Default)</button>
				<div x-ref="content" x-show="open" x-cloak x-transition class="tutor-tooltip">Standard size tooltip.</div>
			</div>

			<!-- Large -->
			<div x-data="tutorTooltip({ size: 'large', arrow: 'center' })" class="tutor-tooltip-wrap">
				<button x-ref="trigger" class="tutor-btn tutor-btn-outline-primary">Large Tooltip</button>
				<div x-ref="content" x-show="open" x-cloak x-transition class="tutor-tooltip">This is a larger tooltip variant for more descriptive content that needs more width and padding to breathe.</div>
			</div>
		</div>
	</div>

	<!-- Arrow Alignment -->
	<div class="tutor-mb-10">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Arrow Alignment</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Align the pointer arrow relative to the tooltip (for Top/Bottom placements).
		</p>

		<div class="tutor-flex tutor-gap-4 tutor-flex-wrap">
			<!-- Arrow Start -->
			<div x-data="tutorTooltip({ arrow: 'start' })" class="tutor-tooltip-wrap">
				<button x-ref="trigger" class="tutor-btn tutor-btn-outline-primary">Arrow Start (Default)</button>
				<div x-ref="content" x-show="open" x-cloak x-transition class="tutor-tooltip">Arrow at the start edge.</div>
			</div>

			<!-- Arrow Center -->
			<div x-data="tutorTooltip({ arrow: 'center' })" class="tutor-tooltip-wrap">
				<button x-ref="trigger" class="tutor-btn tutor-btn-outline-primary">Arrow Center</button>
				<div x-ref="content" x-show="open" x-cloak x-transition class="tutor-tooltip">Arrow perfectly centered.</div>
			</div>

			<!-- Arrow End -->
			<div x-data="tutorTooltip({ arrow: 'end' })" class="tutor-tooltip-wrap">
				<button x-ref="trigger" class="tutor-btn tutor-btn-outline-primary">Arrow End</button>
				<div x-ref="content" x-show="open" x-cloak x-transition class="tutor-tooltip">Arrow at the end edge.</div>
			</div>
		</div>
	</div>

	<!-- Trigger Variations -->
	<div class="tutor-mb-10">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Trigger Variations</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Define how the tooltip is activated.
		</p>

		<div class="tutor-flex tutor-gap-4 tutor-flex-wrap">
			<!-- Hover (Default) -->
			<div x-data="tutorTooltip({ trigger: 'hover' })" class="tutor-tooltip-wrap">
				<button x-ref="trigger" class="tutor-btn tutor-btn-ghost">Hover (Default)</button>
				<div x-ref="content" x-show="open" x-cloak x-transition class="tutor-tooltip">Activated by mouse hover.</div>
			</div>

			<!-- Click -->
			<div x-data="tutorTooltip({ trigger: 'click' })" class="tutor-tooltip-wrap">
				<button x-ref="trigger" class="tutor-btn tutor-btn-ghost">Click Trigger</button>
				<div x-ref="content" x-show="open" x-cloak x-transition class="tutor-tooltip">Activated by mouse click.</div>
			</div>

			<!-- Focus -->
			<div x-data="tutorTooltip({ trigger: 'focus' })" class="tutor-tooltip-wrap">
				<input x-ref="trigger" type="text" class="tutor-form-control" placeholder="Focus me..." style="width: 150px;" />
				<div x-ref="content" x-show="open" x-cloak x-transition class="tutor-tooltip">Activated by keyboard focus.</div>
			</div>
		</div>
	</div>

	<!-- Disabled Element Tooltip -->
	<div class="tutor-mb-10">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Tooltip on Disabled Elements</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Since disabled elements don't trigger mouse events, use the <code>.tutor-tooltip-wrap</code> wrapper to capture interactions.
		</p>

		<div x-data="tutorTooltip({ placement: 'top' })" class="tutor-tooltip-wrap">
			<button x-ref="trigger" class="tutor-btn tutor-btn-primary" disabled>
				Disabled Action
			</button>
			<div x-ref="content" x-show="open" x-cloak x-transition class="tutor-tooltip">
				You must complete the previous lesson first.
			</div>
		</div>
	</div>

	<!-- Usage Example -->
	<div>
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Configuration</h2>
		<div class="tutor-bg-gray-50 tutor-p-4 tutor-rounded-lg">
			<pre class="tutor-text-sm tutor-text-gray-700"><code>&lt;div x-data="tutorTooltip({
	placement: 'top',
	size: 'large',
	arrow: 'center',
	trigger: 'hover'
})" class="tutor-tooltip-wrap"&gt;
	&lt;button x-ref="trigger" class="tutor-btn tutor-btn-primary"&gt;
		Button with Tooltip
	&lt;/button&gt;

	&lt;div x-ref="content" x-show="open" x-cloak x-transition class="tutor-tooltip"&gt;
		Detailed information goes here.
	&lt;/div&gt;
&lt;/div&gt;</code></pre>
		</div>
	</div>
</section>

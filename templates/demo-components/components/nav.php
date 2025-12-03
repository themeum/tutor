<?php
/**
 * Tutor navigation component.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$items = array(
	array(
		'type'    => 'dropdown',
		'icon'    => Icon::ENROLLED,
		'active'  => true,
		'options' => array(
			array(
				'label'  => 'Active',
				'icon'   => Icon::PLAY_LINE,
				'url'    => '#',
				'active' => false,
			),
			array(
				'label'  => 'Enrolled',
				'icon'   => Icon::ENROLLED,
				'url'    => '#',
				'active' => true,
			),
		),
	),
	array(
		'type'   => 'link',
		'label'  => 'Wishlist',
		'icon'   => Icon::WISHLIST,
		'url'    => '#',
		'active' => false,
	),
	array(
		'type'   => 'quiz-attempts',
		'label'  => 'Quiz Attempts',
		'icon'   => Icon::QUIZ_2,
		'url'    => '#',
		'active' => false,
	),
);

?>

<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<h1 class="tutor-text-2xl tutor-font-bold tutor-mb-6">Nav Component</h1>
	<p class="tutor-text-gray-600 tutor-mb-8">
		Navigation component with support for links, dropdowns, size variants, and style variants.
	</p>

	<!-- Primary Variant - All Sizes -->
	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-4">Primary Variant</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Primary variant uses background colors for active states.
		</p>

		<h3 class="tutor-text-lg tutor-font-medium tutor-mb-3">Large Size</h3>
		<?php
		tutor_load_template(
			'core-components.nav',
			array(
				'items'   => $items,
				'size'    => 'lg',
				'variant' => 'primary',
			)
		);
		?>

		<h3 class="tutor-text-lg tutor-font-medium tutor-mb-3 tutor-mt-6">Medium Size (Default)</h3>
		<?php
		tutor_load_template(
			'core-components.nav',
			array(
				'items'   => $items,
				'size'    => 'md',
				'variant' => 'primary',
			)
		);
		?>

		<h3 class="tutor-text-lg tutor-font-medium tutor-mb-3 tutor-mt-6">Small Size</h3>
		<?php
		tutor_load_template(
			'core-components.nav',
			array(
				'items'   => $items,
				'size'    => 'sm',
				'variant' => 'primary',
			)
		);
		?>
	</div>

	<!-- Secondary Variant - All Sizes -->
	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-4">Secondary Variant</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Secondary variant uses border-bottom for active states, providing a cleaner tab-style look.
		</p>

		<h3 class="tutor-text-lg tutor-font-medium tutor-mb-3">Large Size</h3>
		<?php
		tutor_load_template(
			'core-components.nav',
			array(
				'items'   => $items,
				'size'    => 'lg',
				'variant' => 'secondary',
			)
		);
		?>

		<h3 class="tutor-text-lg tutor-font-medium tutor-mb-3 tutor-mt-6">Medium Size (Default)</h3>
		<?php
		tutor_load_template(
			'core-components.nav',
			array(
				'items'   => $items,
				'size'    => 'md',
				'variant' => 'secondary',
			)
		);
		?>

		<h3 class="tutor-text-lg tutor-font-medium tutor-mb-3 tutor-mt-6">Small Size</h3>
		<?php
		tutor_load_template(
			'core-components.nav',
			array(
				'items'   => $items,
				'size'    => 'sm',
				'variant' => 'secondary',
			)
		);
		?>
	</div>

	<!-- Usage Example -->
	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Usage</h2>
		<div class="tutor-bg-gray-50 tutor-p-4 tutor-rounded-lg">
			<pre class="tutor-text-sm tutor-text-gray-700"><code>&lt;?php
tutor_load_template(
	'core-components.nav',
	array(
		'items' => array(
			array(
				'type'   => 'link',
				'label'  => 'Wishlist',
				'icon'   => Icon::WISHLIST,
				'url'    => '#',
				'active' => false,
			),
			array(
				'type'    => 'dropdown',
				'icon'    => Icon::ENROLLED,
				'active'  => true,
				'options' => array(
					array(
						'label'  => 'Active',
						'icon'   => Icon::PLAY_LINE,
						'url'    => '#',
						'active' => false,
					),
					array(
						'label'  => 'Enrolled',
						'icon'   => Icon::ENROLLED,
						'url'    => '#',
						'active' => true,
					),
				),
			),
		),
		'size'    => 'md',      // 'sm', 'md', or 'lg' (default: 'md')
		'variant' => 'primary', // 'primary' or 'secondary' (default: 'primary')
	)
);
?&gt;</code></pre>
		</div>
	</div>

	<!-- Props Documentation -->
	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Props</h2>
		<div class="tutor-bg-gray-50 tutor-p-4 tutor-rounded-lg">
			<table class="tutor-w-full tutor-text-sm">
				<thead>
					<tr class="tutor-border-b">
						<th class="tutor-text-left tutor-pb-2">Prop</th>
						<th class="tutor-text-left tutor-pb-2">Type</th>
						<th class="tutor-text-left tutor-pb-2">Default</th>
						<th class="tutor-text-left tutor-pb-2">Description</th>
					</tr>
				</thead>
				<tbody>
					<tr class="tutor-border-b">
						<td class="tutor-py-2"><code>items</code></td>
						<td>array</td>
						<td>required</td>
						<td>Array of navigation items (links or dropdowns)</td>
					</tr>
					<tr class="tutor-border-b">
						<td class="tutor-py-2"><code>size</code></td>
						<td>string</td>
						<td>'md'</td>
						<td>Size variant: 'sm', 'md', or 'lg'</td>
					</tr>
					<tr>
						<td class="tutor-py-2"><code>variant</code></td>
						<td>string</td>
						<td>'primary'</td>
						<td>Style variant: 'primary' (background) or 'secondary' (border-bottom)</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</section>

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
	<h1 class="tutor-text-2xl tutor-font-bold tutor-mb-6">Nav</h1>

	<h2 class="tutor-text-xl tutor-font-bold tutor-mb-6">Size</h2>
	<?php tutor_load_template( 'core-components.nav', array( 'items' => $items ) ); ?>

	<div class="tutor-mb-8 tutor-mt-12">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Usage</h2>
		<div class="tutor-bg-gray-50 tutor-p-4 tutor-rounded-lg"></div>
	</div>
</section>

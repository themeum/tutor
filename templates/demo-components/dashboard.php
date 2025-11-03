<?php
/**
 * Template for displaying dashboard demo components
 *
 * @package Tutor
 * @since 1.0.0
 */

use TUTOR\Icon;

?>

<h2>Dashboard</h2>

<div class="tutor-bg-white tutor-py-6 tutor-px-8">
	<h2>Pagination</h2>
	<div class="tutor-rounded-lg tutor-shadow-sm">
		<nav class="tutor-pagination" aria-label="Pagination Navigation">
			<a href="#" class="tutor-pagination-item tutor-pagination-item-prev" aria-label="Previous page">
				<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_LEFT ); ?>
			</a>
			<a href="#" class="tutor-pagination-item">1</a>
			<a href="#" class="tutor-pagination-item tutor-pagination-item-active" aria-current="page">2</a>
			<a href="#" class="tutor-pagination-item">3</a>
			<span class="tutor-pagination-ellipsis">...</span>
			<a href="#" class="tutor-pagination-item">6</a>
			<a href="#" class="tutor-pagination-item">7</a>
			<a href="#" class="tutor-pagination-item tutor-pagination-item-next" aria-label="Next page">
				<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_RIGHT ); ?>
			</a>
		</nav>
	</div>
</div>
<?php
/**
 * Template for displaying dashboard demo components
 *
 * @package Tutor
 * @since 1.0.0
 */

use TUTOR\Icon;
?>

<section class="tutor-bg-white tutor-py-6 tutor-px-8">
	<h2>Pagination</h2>
	<div class="tutor-rounded-lg">
		<nav class="tutor-pagination" role="navigation" aria-label="Pagination Navigation">
			<span class="tutor-pagination-info" aria-live="polite">
				Page <span class="tutor-pagination-current">3</span> of <span class="tutor-pagination-total">12</span>
			</span>

			<ul class="tutor-pagination-list">
				<li>
					<a class="tutor-pagination-item tutor-pagination-item-prev" aria-label="Previous page" aria-disabled="true">
						<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_LEFT, 14, 14 ); ?>
					</a>
				</li>

				<li><a class="tutor-pagination-item">1</a></li>
				<li>
					<a class="tutor-pagination-item tutor-pagination-item-active" aria-current="page">2</a>
				</li>
				<li><a class="tutor-pagination-item">3</a></li>
				<li><span class="tutor-pagination-ellipsis" aria-hidden="true">…</span></li>
				<li><a class="tutor-pagination-item">6</a></li>
				<li><a class="tutor-pagination-item">7</a></li>

				<li>
					<a class="tutor-pagination-item tutor-pagination-item-next" aria-label="Next page">
						<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_RIGHT, 14, 14 ); ?>
					</a>
				</li>
			</ul>
		</nav>
	</div>
</section>
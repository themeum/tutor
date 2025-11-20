<?php
/**
 * Announcement Notice
 *
 * @package Tutor
 * @since 1.0.0
 */

use TUTOR\Icon;
?>
<!-- Announcement  -->
<div class="tutor-announcement-notice tutor-mb-7">
	<div class="tutor-announcement-icon">
		<svg width="112" height="112" viewBox="0 0 112 112" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M24.9588 80.7563C25.4806 81.6604 26.1489 82.6301 26.7792 83.486C27.9366 85.0602 29.9309 85.7077 31.8288 85.2358C39.9389 83.2087 62.9101 77.1895 81.9857 70.6657C81.9857 70.6657 79.6744 64.4553 72.8905 53.0799C66.1049 41.7045 62.2195 36.4258 62.2195 36.4258C47.0345 49.6853 30.3253 66.5581 24.5162 72.5705C23.1556 73.9758 22.7216 76.0287 23.5052 77.8181C23.9324 78.7912 24.437 79.8555 24.9588 80.758V80.7563Z" fill="#2C85F3"/>
			<path d="M70.0349 74.5493C74.036 73.3111 78.0193 72.0165 81.9838 70.6657C81.9838 70.6657 79.6726 64.4553 72.8904 53.0799C66.1048 41.7045 62.2177 36.4258 62.2177 36.4258C59.0218 39.2226 55.8658 42.0646 52.7505 44.9509C53.2706 45.3263 53.7459 45.7603 54.1558 46.2632C55.699 48.1577 58.6216 52.0913 62.6172 59.0095C67.5979 67.638 69.3925 72.4999 69.8851 73.9999C69.9448 74.1813 69.9947 74.3633 70.0349 74.5493Z" fill="#EB9900"/>
			<path d="M88.8852 47.9441H83.7185M30.329 47.9441H35.4957M59.6071 18.666V23.8327M38.9041 27.2428L42.5552 30.8974M80.3085 27.2428L76.6539 30.8974M61.865 36.6238C46.6765 49.8816 30.3238 66.5564 24.5147 72.567C23.1542 73.9741 22.7202 76.0253 23.5038 77.8164C23.9309 78.7895 24.4355 79.8521 24.9573 80.7563C25.4792 81.6604 26.1474 82.6283 26.7778 83.4843C27.9351 85.0601 29.9295 85.7077 31.8274 85.2341C39.9374 83.207 62.5556 77.3841 81.6311 70.8603M60.5475 34.3367C60.5475 34.3367 64.6757 38.8507 72.889 53.0781C81.1024 67.3056 82.9487 73.1371 82.9487 73.1371" stroke="#DBE6FF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M52.911 44.832C52.911 44.832 56.5122 48.435 62.6175 59.0095C68.7229 69.5841 70.0421 74.5045 70.0421 74.5045M54.7779 79.5593L55.8009 83.3792C56.0696 84.3823 56.1381 85.4284 56.0025 86.458C55.8668 87.4875 55.5298 88.4802 55.0105 89.3795C54.4912 90.2787 53.7999 91.0669 52.976 91.699C52.1521 92.3311 51.2118 92.7947 50.2088 93.0634C49.2057 93.3321 48.1596 93.4006 47.13 93.265C46.1005 93.1293 45.1078 92.7923 44.2085 92.273C43.3093 91.7537 42.5211 91.0624 41.889 90.2385C41.2569 89.4146 40.7933 88.4743 40.5246 87.4713L39.6738 84.2886" stroke="#DBE6FF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
	</div>
	<div class="tutor-announcement-content">
		<h3 class="tutor-announcement-title tutor-font-size-medium tutor-font-weight-semibold tutor-line-height-medium">
			Apni kursi ki peti bandhlo, Tutor 4.w0w a raha hain!
		</h3>
		<p class="tutor-announcement-message tutor-p3 tutor-mt-2">
			Please note that due to some emergency situations we have decided to postpone the next live class. After the current phase
		</p>
		<div class="tutor-announcement-author tutor-mt-5">
			<img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" alt="Annathoms" class="tutor-announcement-avatar">
			<span class="tutor-announcement-author-name tutor-text-small">Annathoms</span>
			<span class="tutor-announcement-separator">•</span>
			<span class="tutor-announcement-course tutor-text-tiny">Camera Skills & Photo Theory</span>
		</div>
	</div>
	<button class="tutor-announcement-close" aria-label="Close announcement">
		<?php tutor_utils()->render_svg_icon( Icon::CROSS, 20, 20 ); ?>
	</button>
</div>
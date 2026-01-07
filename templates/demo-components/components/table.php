<?php
/**
 * Table component
 *
 * @package Tutor
 * @since 1.0.0
 */

use TUTOR\Icon;
?>

<section class="tutor-bg-white tutor-py-6 tutor-px-8">
	<h2>Table</h2>
	
	<div class="tutor-table-wrapper tutor-table-bordered tutor-table-column-borders">
		<table class="tutor-table">
			<thead>
				<tr>
					<th class="">Quiz info</th>
					<th>Marks</th>
					<th>Time</th>
					<th>Result</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<div class="tutor-flex tutor-gap-3 tutor-items-center">
							<?php tutor_utils()->render_svg_icon( Icon::QUESTION_CIRCLE ); ?>
							Questions
						</div>
					</td>
					<td>20</td>
					<td>
						<span class="tutor-text-secondary">1:15</span>
					</td>
					<td>
						<span class="tutor-badge tutor-badge-completed tutor-badge-circle">
							Passed
						</span>
					</td>
				</tr>
				<tr>
					<td>
						<div class="tutor-flex tutor-gap-3 tutor-items-center">
							<?php tutor_utils()->render_svg_icon( Icon::CLOCK ); ?>
							Quiz time
						</div>
					</td>
					<td>20 Minutes</td>
					<td>
						<span class="tutor-text-secondary">2:15</span>
					</td>
					<td>
						<span class="tutor-badge tutor-badge-exception tutor-badge-circle">
							Bundle
						</span>
					</td>
				</tr>
				<tr>
					<td>
						<div class="tutor-flex tutor-gap-3 tutor-items-center">
							<?php tutor_utils()->render_svg_icon( Icon::CERTIFICATE ); ?>
							Passing Grade
						</div>
					</td>
					<td>80%</td>
					<td>
						<span class="tutor-text-secondary">1:15</span>
					</td>
					<td>
						<span class="tutor-badge tutor-badge-completed tutor-badge-circle">
							Passed
						</span>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</section>

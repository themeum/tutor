<?php
/**
 * Table component
 *
 * @package Tutor
 * @since 1.0.0
 */

use TUTOR\Icon;
?>

<div class="tutor-bg-white tutor-py-6 tutor-px-8">
	<h2>Table</h2>
	<div class="tutor-rounded-lg tutor-shadow-sm">
		<table class="tutor-table">
			<thead class="tutor-table-head">
				<tr class="tutor-table-row">
					<th class="tutor-table-header">
						<input type="checkbox" class="tutor-table-checkbox" aria-label="Select all">
					</th>
					<th class="tutor-table-header tutor-table-header-sortable" aria-sort="none">
						Name
						<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_DOWN, 14, 14 ); ?>
					</th>
					<th class="tutor-table-header">Email</th>
					<th class="tutor-table-header tutor-table-header-sortable" aria-sort="ascending">
						Status
						<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_UP, 14, 14 ); ?>
					</th>
					<th class="tutor-table-header">Role</th>
					<th class="tutor-table-header">Actions</th>
				</tr>
			</thead>
			<tbody class="tutor-table-body">
				<tr class="tutor-table-row">
					<td class="tutor-table-cell">
						<input type="checkbox" class="tutor-table-checkbox" aria-label="Select row">
					</td>
					<td class="tutor-table-cell">
						<div class="tutor-table-user">
							<div class="tutor-table-avatar">JD</div>
							<span class="tutor-table-name">John Doe</span>
						</div>
					</td>
					<td class="tutor-table-cell">john.doe@example.com</td>
					<td class="tutor-table-cell">
						<span class="tutor-badge tutor-badge-success">Active</span>
					</td>
					<td class="tutor-table-cell">Administrator</td>
					<td class="tutor-table-cell">
						<div class="tutor-table-actions">
							<button class="tutor-table-action" aria-label="Edit user">
								<?php tutor_utils()->render_svg_icon( Icon::EDIT, 16, 16 ); ?>
							</button>
							<button class="tutor-table-action" aria-label="Delete user">
								<?php tutor_utils()->render_svg_icon( Icon::DELETE, 16, 16 ); ?>
							</button>
						</div>
					</td>
				</tr>
				<tr class="tutor-table-row tutor-table-row-selected">
					<td class="tutor-table-cell">
						<input type="checkbox" class="tutor-table-checkbox" checked aria-label="Select row">
					</td>
					<td class="tutor-table-cell">
						<div class="tutor-table-user">
							<div class="tutor-table-avatar">JS</div>
							<span class="tutor-table-name">Jane Smith</span>
						</div>
					</td>
					<td class="tutor-table-cell">jane.smith@example.com</td>
					<td class="tutor-table-cell">
						<span class="tutor-badge tutor-badge-warning">Pending</span>
					</td>
					<td class="tutor-table-cell">Editor</td>
					<td class="tutor-table-cell">
						<div class="tutor-table-actions">
							<button class="tutor-table-action" aria-label="Edit user">
								<?php tutor_utils()->render_svg_icon( Icon::EDIT, 16, 16 ); ?>
							</button>
							<button class="tutor-table-action" aria-label="Delete user">
								<?php tutor_utils()->render_svg_icon( Icon::DELETE, 16, 16 ); ?>
							</button>
						</div>
					</td>
				</tr>
				<tr class="tutor-table-row">
					<td class="tutor-table-cell">
						<input type="checkbox" class="tutor-table-checkbox" aria-label="Select row">
					</td>
					<td class="tutor-table-cell">
						<div class="tutor-table-user">
							<div class="tutor-table-avatar">MB</div>
							<span class="tutor-table-name">Mike Brown</span>
						</div>
					</td>
					<td class="tutor-table-cell">mike.brown@example.com</td>
					<td class="tutor-table-cell">
						<span class="tutor-badge tutor-badge-danger">Inactive</span>
					</td>
					<td class="tutor-table-cell">Subscriber</td>
					<td class="tutor-table-cell">
						<div class="tutor-table-actions">
							<button class="tutor-table-action" aria-label="Edit user">
								<?php tutor_utils()->render_svg_icon( Icon::EDIT, 16, 16 ); ?>
							</button>
							<button class="tutor-table-action" aria-label="Delete user">
								<?php tutor_utils()->render_svg_icon( Icon::DELETE, 16, 16 ); ?>
							</button>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
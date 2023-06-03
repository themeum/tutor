<?php
/**
 * Tutor pages
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$tokens = tutor_utils()->tutor_pages();
?>

<div id="tools-tutor-pages" class="tools-tutor-pages">
    <button class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-mb-12">
        + <?php esc_html_e( 'Add New', 'tutor' ); ?>
    </button>

	<table class="tutor-table tutor-pages-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Token', 'tutor' ); ?></th>
				<th><?php esc_html_e( 'Permission', 'tutor' ); ?></th>
				<th><?php esc_html_e( 'Expire At', 'tutor' ); ?></th>
				<th><?php esc_html_e( 'Status', 'tutor' ); ?></th>
				<th><?php esc_html_e( 'Action', 'tutor' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ( $tokens as $token ) {
				?>
					<tr>
						<td>
						</td>
						<td>
						</td>
						<td>
						</td>
						<td>
						</td>
						<td>
                            <button class="tutor-btn tutor-btn-sm tutor-btn-danger">
                                <?php esc_html_e( 'Revoke', 'tutor' ); ?>
                            </button>
						</td>
					</tr>
				<?php
			}
			?>
		</tbody>
	</table>
</div>

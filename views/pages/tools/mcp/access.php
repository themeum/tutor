<?php
/**
 * Tools → MCP → Access.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Tools
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.3.0
 */

use Tutor\Mcp\Support\Permissions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var string $overview_url
 */

$allowed_users = get_users(
	array(
		'meta_key'   => Permissions::META_ALLOWED, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'meta_value' => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		'number'     => 100,
		'orderby'    => 'display_name',
	)
);
?>
<div class="tutor-mb-24">
	<a class="tutor-btn tutor-btn-ghost tutor-btn-sm" href="<?php echo esc_url( $overview_url ); ?>">
		<span class="tutor-icon-angle-left tutor-mr-8" aria-hidden="true"></span>
		<?php esc_html_e( 'Back to MCP', 'tutor' ); ?>
	</a>
	<div class="tutor-fs-7 tutor-color-muted tutor-mt-8">
		<?php esc_html_e( 'Allow instructors/students to connect. Site administrators are always allowed. Revoke blocks MCP even if credentials remain.', 'tutor' ); ?>
	</div>
</div>

<div class="tutor-option-single-item tutor-mb-32">
	<div class="tutor-option-group-title tutor-mb-16">
		<div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Grant access', 'tutor' ); ?></div>
	</div>
	<div class="item-wrapper">
		<form method="post" class="tutor-d-flex tutor-align-end tutor-gap-2">
			<?php wp_nonce_field( 'tutor_mcp_access' ); ?>
			<input type="hidden" name="tutor_mcp_action" value="allow_access">
			<input type="text" name="tutor_mcp_user" class="tutor-form-control" placeholder="<?php esc_attr_e( 'Username, email, or user ID', 'tutor' ); ?>" required>
			<button type="submit" class="tutor-btn tutor-btn-primary"><?php esc_html_e( 'Allow', 'tutor' ); ?></button>
		</form>
	</div>
</div>

<div class="tutor-option-single-item tutor-mb-32">
	<div class="tutor-option-group-title tutor-mb-16">
		<div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Allowlist', 'tutor' ); ?></div>
	</div>
	<div class="item-wrapper">
		<?php if ( empty( $allowed_users ) ) : ?>
			<div class="tutor-fs-7 tutor-color-muted"><?php esc_html_e( 'No users allowlisted yet.', 'tutor' ); ?></div>
		<?php else : ?>
			<table class="tutor-table tutor-table-middle">
				<thead>
					<tr>
						<th><?php esc_html_e( 'User', 'tutor' ); ?></th>
						<th><?php esc_html_e( 'Email', 'tutor' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $allowed_users as $u ) : ?>
						<tr>
							<td><?php echo esc_html( $u->display_name . ' (' . $u->user_login . ')' ); ?></td>
							<td><?php echo esc_html( $u->user_email ); ?></td>
							<td class="tutor-text-right">
								<form method="post">
									<?php wp_nonce_field( 'tutor_mcp_access' ); ?>
									<input type="hidden" name="tutor_mcp_action" value="revoke_access">
									<input type="hidden" name="tutor_mcp_user_id" value="<?php echo esc_attr( (string) $u->ID ); ?>">
									<button type="submit" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm"><?php esc_html_e( 'Revoke', 'tutor' ); ?></button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>

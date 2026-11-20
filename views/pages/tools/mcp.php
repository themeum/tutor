<?php
/**
 * Tools → MCP page.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Tools
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.3.0
 */

use Tutor\Core\Input;
use Tutor\Mcp\Registrar;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have permission to access this page.', 'tutor' ), 403 );
}

$enabled           = Registrar::is_enabled();
$read_only         = Registrar::is_read_only();
$instructor_access = Registrar::is_instructor_access_enabled();
$endpoint          = Registrar::endpoint();
$enabled_tools     = Registrar::enabled_tools();
$catalog           = Registrar::tool_catalog();
$total_tools       = count( Registrar::known_tools() );
$on_count          = count( $enabled_tools );
$updated           = (bool) Input::get( 'updated', false, Input::TYPE_BOOL );
$mcp_view          = Input::get( 'mcp_view', '' );
$abilities_url     = add_query_arg(
	array(
		'page'     => 'tutor-tools',
		'sub_page' => 'tutor_mcp',
		'mcp_view' => 'abilities',
	),
	admin_url( 'admin.php' )
);
$overview_url      = add_query_arg(
	array(
		'page'     => 'tutor-tools',
		'sub_page' => 'tutor_mcp',
	),
	admin_url( 'admin.php' )
);
?>
<div
	class="tutor-mcp"
	id="tutor-mcp-root"
	data-has-adapter="<?php echo Registrar::has_adapter() ? '1' : '0'; ?>"
>
	<form method="post" id="tutor-mcp-form">
		<?php wp_nonce_field( 'tutor_mcp_tools_save' ); ?>
		<input type="hidden" name="tutor_mcp_action" value="save_settings">
		<input type="hidden" name="tutor_mcp_view" value="<?php echo esc_attr( 'abilities' === $mcp_view ? 'abilities' : '' ); ?>">

		<div class="tutor-option-main-title tutor-d-flex tutor-align-center tutor-justify-between">
			<div class="tutor-fs-4 tutor-fw-medium tutor-color-black">
				<?php echo 'abilities' === $mcp_view ? esc_html__( 'Abilities', 'tutor' ) : esc_html__( 'MCP', 'tutor' ); ?>
			</div>
			<div class="tutor-d-flex tutor-align-center tutor-gap-2">
				<span class="tutor-fs-7 tutor-color-warning tutor-d-none" data-mcp-dirty>
					<?php esc_html_e( 'Unsaved changes', 'tutor' ); ?>
				</span>
				<button type="submit" class="tutor-btn tutor-btn-primary" data-mcp-save disabled>
					<?php esc_html_e( 'Save Changes', 'tutor' ); ?>
				</button>
			</div>
		</div>

		<?php if ( $updated ) : ?>
			<div class="tutor-alert tutor-success tutor-mb-24">
				<?php esc_html_e( 'MCP settings saved.', 'tutor' ); ?>
			</div>
		<?php endif; ?>

		<?php if ( ! Registrar::has_abilities_api() ) : ?>
			<div class="tutor-alert tutor-danger tutor-mb-24">
				<div class="tutor-alert-text">
					<span class="tutor-alert-icon tutor-fs-4 tutor-icon-circle-times-line tutor-mr-12"></span>
					<span>
						<strong><?php esc_html_e( 'Abilities API missing.', 'tutor' ); ?></strong>
						<?php esc_html_e( 'WordPress 6.9+ is required for Tutor MCP.', 'tutor' ); ?>
					</span>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $enabled && ! Registrar::has_adapter() ) : ?>
			<div class="tutor-alert tutor-warning tutor-mb-24">
				<div class="tutor-alert-text">
					<span class="tutor-alert-icon tutor-fs-4 tutor-icon-warning tutor-mr-12"></span>
					<span>
						<strong><?php esc_html_e( 'MCP Adapter required.', 'tutor' ); ?></strong>
						<?php esc_html_e( 'Tutor LMS MCP is on, but the WordPress MCP Adapter plugin is missing or inactive. Install or activate it to use MCP.', 'tutor' ); ?>
						<a href="<?php echo esc_url( Registrar::adapter_plugin_action_url() ); ?>">
							<?php
							echo Registrar::is_adapter_plugin_installed()
								? esc_html__( 'Activate plugin', 'tutor' )
								: esc_html__( 'Install plugin', 'tutor' );
							?>
						</a>
					</span>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( 'abilities' === $mcp_view ) : ?>
			<?php require __DIR__ . '/mcp/abilities.php'; ?>
		<?php else : ?>

			<div class="tutor-option-single-item tutor-mb-32">
				<div class="tutor-option-group-title tutor-mb-16">
					<div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Configuration', 'tutor' ); ?></div>
				</div>
				<div class="item-wrapper">
					<div class="tutor-option-field-row">
						<div class="tutor-option-field-label">
							<div class="tutor-fs-6 tutor-fw-medium"><?php esc_html_e( 'Tutor LMS MCP', 'tutor' ); ?></div>
							<div class="tutor-fs-7 tutor-color-muted tutor-mt-8">
								<?php esc_html_e( 'Let AI apps manage your eLearning platform through the Model Context Protocol.', 'tutor' ); ?>
							</div>
						</div>
						<div class="tutor-option-field-input">
							<label class="tutor-form-toggle">
								<input type="checkbox" class="tutor-form-toggle-input" name="tutor_mcp_enabled" value="1" <?php checked( $enabled ); ?> data-mcp-enable>
								<span class="tutor-form-toggle-control"></span>
							</label>
						</div>
					</div>

					<div class="tutor-option-field-row <?php echo $enabled ? '' : 'tutor-d-none'; ?>" data-mcp-when-enabled>
						<div class="tutor-option-field-label">
							<div class="tutor-fs-6 tutor-fw-medium"><?php esc_html_e( 'Server URL', 'tutor' ); ?></div>
							<div class="tutor-fs-7 tutor-color-muted tutor-mt-8">
								<?php esc_html_e( 'AI apps connect to this address with a WordPress username and Application Password.', 'tutor' ); ?>
							</div>
						</div>
						<div class="tutor-option-field-input">
							<div class="tutor-d-flex tutor-align-center tutor-gap-1">
								<input type="text" id="tutor-mcp-srv-url" class="tutor-form-control" value="<?php echo esc_attr( $endpoint ); ?>" readonly>
								<button type="button" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm tutor-flex-shrink-0" data-tutor-clipboard-copy-target="tutor-mcp-srv-url">
									<span class="tutor-icon-copy tutor-mr-8" aria-hidden="true"></span>
									<?php esc_html_e( 'Copy', 'tutor' ); ?>
								</button>
							</div>
						</div>
					</div>

					<div class="tutor-option-field-row <?php echo $enabled ? '' : 'tutor-d-none'; ?>" data-mcp-when-enabled>
						<div class="tutor-option-field-label">
							<div class="tutor-fs-6 tutor-fw-medium"><?php esc_html_e( 'Read-only mode', 'tutor' ); ?></div>
							<div class="tutor-fs-7 tutor-color-muted tutor-mt-8">
								<?php esc_html_e( 'Agents can look things up but can’t create, change or delete anything. Applies to every connection.', 'tutor' ); ?>
							</div>
						</div>
						<div class="tutor-option-field-input">
							<label class="tutor-form-toggle">
								<input type="checkbox" class="tutor-form-toggle-input" name="tutor_mcp_read_only" value="1" <?php checked( $read_only ); ?>>
								<span class="tutor-form-toggle-control"></span>
							</label>
						</div>
					</div>

					<div class="tutor-option-field-row <?php echo $enabled ? '' : 'tutor-d-none'; ?>" data-mcp-when-enabled>
						<div class="tutor-option-field-label">
							<div class="tutor-fs-6 tutor-fw-medium"><?php esc_html_e( 'Allow instructors', 'tutor' ); ?></div>
							<div class="tutor-fs-7 tutor-color-muted tutor-mt-8">
								<?php esc_html_e( 'Let instructors and co-instructors connect to MCP. They can only use tools within their normal Tutor LMS permissions. Admins always have access.', 'tutor' ); ?>
							</div>
						</div>
						<div class="tutor-option-field-input">
							<label class="tutor-form-toggle">
								<input type="checkbox" class="tutor-form-toggle-input" name="tutor_mcp_instructor_access" value="1" <?php checked( $instructor_access ); ?>>
								<span class="tutor-form-toggle-control"></span>
							</label>
						</div>
					</div>

					<div class="tutor-option-field-row <?php echo $enabled ? '' : 'tutor-d-none'; ?>" data-mcp-when-enabled>
						<div class="tutor-option-field-label">
							<div class="tutor-fs-6 tutor-fw-medium"><?php esc_html_e( 'Abilities', 'tutor' ); ?></div>
							<div class="tutor-fs-7 tutor-color-muted tutor-mt-8">
								<?php esc_html_e( 'Choose what agents are allowed to do on this site.', 'tutor' ); ?>
							</div>
						</div>
						<div class="tutor-option-field-input">
							<a class="tutor-btn tutor-btn-outline-primary tutor-btn-sm" href="<?php echo esc_url( $abilities_url ); ?>">
								<?php
								printf(
									/* translators: 1: enabled count, 2: total count */
									esc_html__( '%1$d of %2$d on', 'tutor' ),
									(int) $on_count,
									(int) $total_tools
								);
								?>
								<span class="tutor-icon-angle-right tutor-ml-8" aria-hidden="true"></span>
							</a>
						</div>
					</div>
				</div>
			</div>

			<?php if ( $enabled && $read_only ) : ?>
				<div class="tutor-alert tutor-warning tutor-mb-24" data-mcp-when-enabled>
					<div class="tutor-alert-text">
						<span class="tutor-alert-icon tutor-fs-4 tutor-icon-warning tutor-mr-12"></span>
						<span>
							<strong><?php esc_html_e( 'Read-only mode is on.', 'tutor' ); ?></strong>
							<?php esc_html_e( 'Write and delete tools are blocked for every connection.', 'tutor' ); ?>
						</span>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php
		if ( 'abilities' !== $mcp_view ) :
			foreach ( $enabled_tools as $tool_id ) :
				?>
				<input type="hidden" name="tutor_mcp_enabled_tools[]" value="<?php echo esc_attr( $tool_id ); ?>">
				<?php
			endforeach;
		endif;
		?>
	</form>

	<?php require __DIR__ . '/mcp/adapter-modal.php'; ?>
</div>

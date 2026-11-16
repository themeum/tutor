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
use Tutor\Mcp\Support\Permissions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! Permissions::can_manage_settings() ) {
	wp_die( esc_html__( 'You do not have permission to access this page.', 'tutor' ), 403 );
}

$enabled       = Registrar::is_enabled();
$read_only     = Registrar::is_read_only();
$endpoint      = Registrar::endpoint();
$enabled_tools = Registrar::enabled_tools();
$catalog       = Registrar::tool_catalog();
$total_tools   = count( Registrar::TOOLS );
$on_count      = count( $enabled_tools );
$updated       = (bool) Input::get( 'updated', false, Input::TYPE_BOOL );
$mcp_view      = Input::get( 'mcp_view', '' );
$agent         = get_user_by( 'login', Registrar::AGENT_USER );
$abilities_url = add_query_arg(
	array(
		'page'     => 'tutor-tools',
		'sub_page' => 'tutor_mcp',
		'mcp_view' => 'abilities',
	),
	admin_url( 'admin.php' )
);
$overview_url  = add_query_arg(
	array(
		'page'     => 'tutor-tools',
		'sub_page' => 'tutor_mcp',
	),
	admin_url( 'admin.php' )
);

$client_logos = array(
	'claude'  => array(
		'tint' => '#D97757',
		'path' => 'm4.7144 15.9555 4.7174-2.6471.079-.2307-.079-.1275h-.2307l-.7893-.0486-2.6956-.0729-2.3375-.0971-2.2646-.1214-.5707-.1215-.5343-.7042.0546-.3522.4797-.3218.686.0608 1.5179.1032 2.2767.1578 1.6514.0972 2.4468.255h.3886l.0546-.1579-.1336-.0971-.1032-.0972L6.973 9.8356l-2.55-1.6879-1.3356-.9714-.7225-.4918-.3643-.4614-.1578-1.0078.6557-.7225.8803.0607.2246.0607.8925.686 1.9064 1.4754 2.4893 1.8336.3643.3035.1457-.1032.0182-.0728-.164-.2733-1.3539-2.4467-1.445-2.4893-.6435-1.032-.17-.6194c-.0607-.255-.1032-.4674-.1032-.7285L6.287.1335 6.6997 0l.9957.1336.419.3642.6192 1.4147 1.0018 2.2282 1.5543 3.0296.4553.8985.2429.8318.091.255h.1579v-.1457l.1275-1.706.2368-2.0947.2307-2.6957.0789-.7589.3764-.9107.7468-.4918.5828.2793.4797.686-.0668.4433-.2853 1.8517-.5586 2.9021-.3643 1.9429h.2125l.2429-.2429.9835-1.3053 1.6514-2.0643.7286-.8196.85-.9046.5464-.4311h1.0321l.759 1.1293-.34 1.1657-1.0625 1.3478-.8804 1.1414-1.2628 1.7-.7893 1.36.0729.1093.1882-.0183 2.8535-.607 1.5421-.2794 1.8396-.3157.8318.3886.091.3946-.3278.8075-1.967.4857-2.3072.4614-3.4364.8136-.0425.0304.0486.0607 1.5482.1457.6618.0364h1.621l3.0175.2247.7892.522.4736.6376-.079.4857-1.2142.6193-1.6393-.3886-3.825-.9107-1.3113-.3279h-.1822v.1093l1.0929 1.0686 2.0035 1.8092 2.5075 2.3314.1275.5768-.3218.4554-.34-.0486-2.2039-1.6575-.85-.7468-1.9246-1.621h-.1275v.17l.4432.6496 2.3436 3.5214.1214 1.0807-.17.3521-.6071.2125-.6679-.1214-1.3721-1.9246L14.38 17.959l-1.1414-1.9428-.1397.079-.674 7.2552-.3156.3703-.7286.2793-.6071-.4614-.3218-.7468.3218-1.4753.3886-1.9246.3157-1.53.2853-1.9004.17-.6314-.0121-.0425-.1397.0182-1.4328 1.9672-2.1796 2.9446-1.7243 1.8456-.4128.164-.7164-.3704.0667-.6618.4008-.5889 2.386-3.0357 1.4389-1.882.929-1.0868-.0062-.1579h-.0546l-6.3385 4.1164-1.1293.1457-.4857-.4554.0608-.7467.2307-.2429 1.9064-1.3114Z',
	),
	'cursor'  => array(
		'tint' => 'currentColor',
		'path' => 'M11.503.131 1.891 5.678a.84.84 0 0 0-.42.726v11.188c0 .3.162.575.42.724l9.609 5.55a1 1 0 0 0 .998 0l9.61-5.55a.84.84 0 0 0 .42-.724V6.404a.84.84 0 0 0-.42-.726L12.497.131a1.01 1.01 0 0 0-.996 0M2.657 6.338h18.55c.263 0 .43.287.297.515L12.23 22.918c-.062.107-.229.064-.229-.06V12.335a.59.59 0 0 0-.295-.51l-9.11-5.257c-.109-.063-.064-.23.061-.23',
	),
	'copilot' => array(
		'tint' => 'currentColor',
		'path' => 'M23.922 16.997C23.061 18.492 18.063 22.02 12 22.02 5.937 22.02.939 18.492.078 16.997A.641.641 0 0 1 0 16.741v-2.869a.883.883 0 0 1 .053-.22c.372-.935 1.347-2.292 2.605-2.656.167-.429.414-1.055.644-1.517a10.098 10.098 0 0 1-.052-1.086c0-1.331.282-2.499 1.132-3.368.397-.406.89-.717 1.474-.952C7.255 2.937 9.248 1.98 11.978 1.98c2.731 0 4.767.957 6.166 2.093.584.235 1.077.546 1.474.952.85.869 1.132 2.037 1.132 3.368 0 .368-.014.733-.052 1.086.23.462.477 1.088.644 1.517 1.258.364 2.233 1.721 2.605 2.656a.841.841 0 0 1 .053.22v2.869a.641.641 0 0 1-.078.256Zm-11.75-5.992h-.344a4.359 4.359 0 0 1-.355.508c-.77.947-1.918 1.492-3.508 1.492-1.725 0-2.989-.359-3.782-1.259a2.137 2.137 0 0 1-.085-.104L4 11.746v6.585c1.435.779 4.514 2.179 8 2.179 3.486 0 6.565-1.4 8-2.179v-6.585l-.098-.104s-.033.045-.085.104c-.793.9-2.057 1.259-3.782 1.259-1.59 0-2.738-.545-3.508-1.492a4.359 4.359 0 0 1-.355-.508Zm2.328 3.25c.549 0 1 .451 1 1v2c0 .549-.451 1-1 1-.549 0-1-.451-1-1v-2c0-.549.451-1 1-1Zm-5 0c.549 0 1 .451 1 1v2c0 .549-.451 1-1 1-.549 0-1-.451-1-1v-2c0-.549.451-1 1-1Zm3.313-6.185c.136 1.057.403 1.913.878 2.497.442.544 1.134.938 2.344.938 1.573 0 2.292-.337 2.657-.751.384-.435.558-1.15.558-2.361 0-1.14-.243-1.847-.705-2.319-.477-.488-1.319-.862-2.824-1.025-1.487-.161-2.192.138-2.533.529-.269.307-.437.808-.438 1.578v.021c0 .265.021.562.063.893Zm-1.626 0c.042-.331.063-.628.063-.894v-.02c-.001-.77-.169-1.271-.438-1.578-.341-.391-1.046-.69-2.533-.529-1.505.163-2.347.537-2.824 1.025-.462.472-.705 1.179-.705 2.319 0 1.211.175 1.926.558 2.361.365.414 1.084.751 2.657.751 1.21 0 1.902-.394 2.344-.938.475-.584.742-1.44.878-2.497Z',
	),
	'gemini'  => array(
		'tint' => '#8E75B2',
		'path' => 'M11.04 19.32Q12 21.51 12 24q0-2.49.93-4.68.96-2.19 2.58-3.81t3.81-2.55Q21.51 12 24 12q-2.49 0-4.68-.93a12.3 12.3 0 0 1-3.81-2.58 12.3 12.3 0 0 1-2.58-3.81Q12 2.49 12 0q0 2.49-.96 4.68-.93 2.19-2.55 3.81a12.3 12.3 0 0 1-3.81 2.58Q2.49 12 0 12q2.49 0 4.68.96 2.19.93 3.81 2.55t2.55 3.81',
	),
);

/**
 * Render a client logo tile.
 *
 * @param string $key  Logo key.
 * @param string $size Size class suffix.
 * @return void
 */
$render_logo = static function ( string $key, string $size = 'sm' ) use ( $client_logos ): void {
	if ( ! isset( $client_logos[ $key ] ) ) {
		return;
	}
	$logo  = $client_logos[ $key ];
	$class = 'tutor-mcp-logo';
	if ( 'sm' === $size ) {
		$class .= ' tutor-is-sm';
	} elseif ( 'lg' === $size ) {
		$class .= ' tutor-is-lg';
	}
	printf(
		'<span class="%1$s" style="color:%2$s" aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor"><path d="%3$s"/></svg></span>',
		esc_attr( $class ),
		esc_attr( $logo['tint'] ),
		esc_attr( $logo['path'] )
	);
};

$mcp_client = array(
	'endpoint'   => $endpoint,
	'abspath'    => untrailingslashit( ABSPATH ),
	'agentUser'  => Registrar::AGENT_USER,
	'serverId'   => Registrar::SERVER_ID,
	'hasAdapter' => Registrar::has_adapter(),
);

?>
<div
	class="tutor-mcp"
	id="tutor-mcp-root"
	data-mcp-client="<?php echo esc_attr( wp_json_encode( $mcp_client ) ); ?>"
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

		<?php if ( $enabled && ! $agent ) : ?>
			<div class="tutor-alert tutor-warning tutor-mb-24">
				<div class="tutor-alert-text">
					<span class="tutor-alert-icon tutor-fs-4 tutor-icon-warning tutor-mr-12"></span>
					<span>
						<strong><?php esc_html_e( 'Agent user missing.', 'tutor' ); ?></strong>
						<?php esc_html_e( 'Create or wait for provisioning of', 'tutor' ); ?>
						<code><?php echo esc_html( Registrar::AGENT_USER ); ?></code>.
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
								<?php esc_html_e( 'Let AI apps like Claude, Cursor, VS Code and Gemini manage your eLearning platform through the Model Context Protocol.', 'tutor' ); ?>
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
								<?php esc_html_e( 'AI apps connect to this address. Remote clients also need an Application Password for the agent user.', 'tutor' ); ?>
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

			<div class="tutor-option-single-item tutor-mb-32 <?php echo $enabled ? '' : 'tutor-d-none'; ?>" data-mcp-conn-live>
				<div class="tutor-option-group-title tutor-mb-16 tutor-d-flex tutor-align-center tutor-justify-between">
					<div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Connections', 'tutor' ); ?></div>
					<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-sm" data-tutor-modal-target="tutor-mcp-connect-modal">
						<span class="tutor-icon-plus-o tutor-mr-8" aria-hidden="true"></span>
						<?php esc_html_e( 'Connect app', 'tutor' ); ?>
					</button>
				</div>
				<div class="item-wrapper">
					<div class="tutor-fs-6 tutor-fw-medium tutor-mb-8"><?php esc_html_e( 'Connect your first AI app', 'tutor' ); ?></div>
					<div class="tutor-fs-7 tutor-color-muted tutor-mb-16">
						<?php esc_html_e( 'Pick the app you use. We show STDIO and HTTP configs for that app — the same setup pattern WooCommerce documents for MCP Adapter.', 'tutor' ); ?>
					</div>
					<div class="tutor-mcp-logos tutor-mb-16">
						<?php
						$render_logo( 'claude' );
						$render_logo( 'cursor' );
						$render_logo( 'copilot' );
						$render_logo( 'gemini' );
						?>
					</div>
					<button type="button" class="tutor-btn tutor-btn-primary tutor-btn-sm" data-tutor-modal-target="tutor-mcp-connect-modal">
						<span class="tutor-icon-plus-o tutor-mr-8" aria-hidden="true"></span>
						<?php esc_html_e( 'Connect app', 'tutor' ); ?>
					</button>
				</div>
			</div>

			<div class="tutor-option-single-item tutor-mb-32 <?php echo $enabled ? 'tutor-d-none' : ''; ?>" data-mcp-conn-locked>
				<div class="tutor-option-group-title tutor-mb-16">
					<div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Connections', 'tutor' ); ?></div>
				</div>
				<div class="item-wrapper">
					<div class="tutor-fs-6 tutor-fw-medium tutor-mb-8"><?php esc_html_e( 'Connect your first AI app', 'tutor' ); ?></div>
					<div class="tutor-fs-7 tutor-color-muted tutor-mb-16">
						<?php esc_html_e( 'Pick the app you use. We show STDIO and HTTP configs for that app — the same setup pattern WooCommerce documents for MCP Adapter.', 'tutor' ); ?>
					</div>
					<div class="tutor-mcp-logos tutor-mb-16 tutor-mcp-logos-locked">
						<?php
						$render_logo( 'claude' );
						$render_logo( 'cursor' );
						$render_logo( 'copilot' );
						$render_logo( 'gemini' );
						?>
					</div>
					<div class="tutor-fs-7 tutor-color-muted">
						<span class="tutor-icon-lock-line tutor-mr-4" aria-hidden="true"></span>
						<?php esc_html_e( 'Turn on Tutor LMS MCP above to connect an app.', 'tutor' ); ?>
					</div>
				</div>
			</div>
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

	<?php require __DIR__ . '/mcp/connect-modal.php'; ?>
	<?php require __DIR__ . '/mcp/adapter-modal.php'; ?>
</div>

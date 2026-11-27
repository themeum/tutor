<?php
/**
 * Tools → MCP — install / activate MCP Adapter modal.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Tools
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.3.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Mcp\Registrar;

$adapter_installed = Registrar::is_adapter_plugin_installed();
$action_url        = Registrar::adapter_plugin_action_url();
$action_label      = $adapter_installed
	? __( 'Activate MCP Adapter', 'tutor' )
	: __( 'Install MCP Adapter', 'tutor' );
?>
<div id="tutor-mcp-adapter-modal" class="tutor-modal" role="dialog" aria-modal="true" aria-labelledby="tutor-mcp-adapter-title" aria-hidden="true">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window tutor-modal-window-sm">
		<div class="tutor-modal-content tutor-modal-content-white">
			<button type="button" class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close aria-label="<?php esc_attr_e( 'Close', 'tutor' ); ?>">
				<span class="tutor-icon-times" aria-hidden="true"></span>
			</button>

			<div class="tutor-modal-body tutor-text-center tutor-py-24 tutor-px-32">
				<div class="tutor-mt-24">
					<span class="tutor-icon-warning tutor-fs-1 tutor-color-warning" aria-hidden="true"></span>
				</div>

				<div id="tutor-mcp-adapter-title" class="tutor-fs-3 tutor-fw-medium tutor-color-black tutor-mt-16 tutor-mb-12">
					<?php
					echo $adapter_installed
						? esc_html__( 'Activate MCP Adapter', 'tutor' )
						: esc_html__( 'Install MCP Adapter', 'tutor' );
					?>
				</div>

				<div class="tutor-fs-6 tutor-color-muted tutor-mb-24">
					<?php
					if ( $adapter_installed ) {
						esc_html_e( 'The WordPress MCP Adapter plugin is installed but not active. Activate it to turn on Tutor LMS MCP.', 'tutor' );
					} else {
						esc_html_e( 'Tutor LMS MCP requires the WordPress MCP Adapter plugin. Install and activate it, then turn MCP on again.', 'tutor' );
					}
					?>
				</div>

				<div class="tutor-d-flex tutor-justify-center tutor-gap-2 tutor-mb-16">
					<button type="button" class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
						<?php esc_html_e( 'Cancel', 'tutor' ); ?>
					</button>
					<a class="tutor-btn tutor-btn-primary" href="<?php echo esc_url( $action_url ); ?>">
						<?php echo esc_html( $action_label ); ?>
					</a>
				</div>

				<p class="tutor-fs-7 tutor-color-muted tutor-mb-8">
					<a href="<?php echo esc_url( Registrar::ADAPTER_GITHUB_URL ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'View on GitHub', 'tutor' ); ?>
					</a>
				</p>
			</div>
		</div>
	</div>
</div>

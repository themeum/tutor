<?php
/**
 * Tools → MCP connect modal.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Tools
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Variables from parent:
 *
 * @var callable $render_logo
 */
?>
<div id="tutor-mcp-connect-modal" class="tutor-modal tutor-modal-scrollable" role="dialog" aria-modal="true" aria-labelledby="tutor-mcp-connect-title" aria-hidden="true">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window">
		<div class="tutor-modal-content">
			<div class="tutor-modal-header">
				<div>
					<div id="tutor-mcp-connect-title" class="tutor-modal-title">
						<?php esc_html_e( 'Connect an AI app', 'tutor' ); ?>
					</div>
					<div id="tutor-mcp-dlg-sub" class="tutor-fs-7 tutor-color-muted tutor-mt-4 tutor-d-none"></div>
				</div>
				<button type="button" class="tutor-iconic-btn tutor-modal-close" data-tutor-modal-close aria-label="<?php esc_attr_e( 'Close', 'tutor' ); ?>">
					<span class="tutor-icon-times" aria-hidden="true"></span>
				</button>
			</div>

			<div class="tutor-modal-body">
				<div id="tutor-mcp-picker">
					<div class="tutor-mcp-client-grid">
						<button type="button" class="tutor-mcp-client-tile" data-mcp-pick="claude-desktop">
							<?php $render_logo( 'claude', 'md' ); ?>
							<span class="tutor-fs-6 tutor-fw-medium"><?php esc_html_e( 'Claude Desktop', 'tutor' ); ?></span>
						</button>
						<button type="button" class="tutor-mcp-client-tile" data-mcp-pick="cursor">
							<?php $render_logo( 'cursor', 'md' ); ?>
							<span class="tutor-fs-6 tutor-fw-medium"><?php esc_html_e( 'Cursor', 'tutor' ); ?></span>
						</button>
						<button type="button" class="tutor-mcp-client-tile" data-mcp-pick="vscode">
							<?php $render_logo( 'copilot', 'md' ); ?>
							<span class="tutor-fs-6 tutor-fw-medium"><?php esc_html_e( 'VS Code', 'tutor' ); ?></span>
							<span class="tutor-fs-7 tutor-color-muted"><?php esc_html_e( 'GitHub Copilot', 'tutor' ); ?></span>
						</button>
						<button type="button" class="tutor-mcp-client-tile" data-mcp-pick="gemini-cli">
							<?php $render_logo( 'gemini', 'md' ); ?>
							<span class="tutor-fs-6 tutor-fw-medium"><?php esc_html_e( 'Gemini CLI', 'tutor' ); ?></span>
						</button>
					</div>
					<p class="tutor-fs-7 tutor-color-muted tutor-mt-16">
						<?php esc_html_e( 'Using a different MCP client?', 'tutor' ); ?>
						<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-sm" data-mcp-pick="other">
							<?php esc_html_e( 'Connect it manually', 'tutor' ); ?>
						</button>
					</p>
				</div>
				<div id="tutor-mcp-setup" class="tutor-d-none"></div>
			</div>

			<div id="tutor-mcp-dlg-foot" class="tutor-modal-footer tutor-d-none">
				<button type="button" class="tutor-btn tutor-btn-outline-primary" data-mcp-w-back>
					<?php esc_html_e( 'Back', 'tutor' ); ?>
				</button>
				<button type="button" class="tutor-btn tutor-btn-primary" data-tutor-modal-close>
					<?php esc_html_e( 'Done', 'tutor' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>

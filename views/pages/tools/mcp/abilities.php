<?php
/**
 * Tools → MCP → Abilities.
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
 * Variables expected from parent mcp.php:
 *
 * @var string   $overview_url
 * @var int      $abilities_on
 * @var int      $abilities_total
 * @var bool     $enabled
 * @var bool     $read_only
 * @var bool     $instructor_access
 * @var array    $catalog
 * @var string[] $enabled_tools
 */
?>
<div class="tutor-mb-24 tutor-d-flex tutor-align-center tutor-justify-between tutor-flex-wrap tutor-gap-2">
	<div>
		<a class="tutor-btn tutor-btn-ghost tutor-btn-sm" href="<?php echo esc_url( $overview_url ); ?>">
			<span class="tutor-icon-angle-left tutor-mr-8" aria-hidden="true"></span>
			<?php
			printf(
				/* translators: 1: enabled ability count, 2: total abilities */
				esc_html__( 'Abilities (%1$d/%2$d)', 'tutor' ),
				(int) $abilities_on,
				(int) $abilities_total
			);
			?>
		</a>
	</div>
	<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-sm" data-mcp-reset-default>
		<span class="tutor-icon-refresh tutor-mr-8" aria-hidden="true"></span>
		<?php esc_html_e( 'Reset to default', 'tutor' ); ?>
	</button>
</div>

<?php if ( ! $enabled ) : ?>
	<div class="tutor-alert tutor-primary tutor-mb-24">
		<div class="tutor-alert-text">
			<span class="tutor-alert-icon tutor-fs-4 tutor-icon-circle-info-circle tutor-mr-12"></span>
			<span>
				<strong><?php esc_html_e( 'MCP is off.', 'tutor' ); ?></strong>
				<?php esc_html_e( 'You can set up abilities now. They apply once MCP is on.', 'tutor' ); ?>
			</span>
		</div>
	</div>
<?php endif; ?>

<?php if ( $read_only ) : ?>
	<div class="tutor-alert tutor-warning tutor-mb-24">
		<div class="tutor-alert-text">
			<span class="tutor-alert-icon tutor-fs-4 tutor-icon-warning tutor-mr-12"></span>
			<span>
				<strong><?php esc_html_e( 'Read-only mode is on.', 'tutor' ); ?></strong>
				<?php esc_html_e( 'Write and delete tools are blocked everywhere.', 'tutor' ); ?>
			</span>
		</div>
	</div>
<?php endif; ?>

<?php foreach ( $catalog as $group ) : ?>
	<?php
	$group_abilities = isset( $group['abilities'] ) && is_array( $group['abilities'] ) ? $group['abilities'] : array();
	$group_total     = count( $group_abilities );
	$group_on        = 0;
	foreach ( $group_abilities as $ability ) {
		$tools = isset( $ability['tools'] ) && is_array( $ability['tools'] ) ? $ability['tools'] : array();
		foreach ( $tools as $tool ) {
			if ( isset( $tool['n'] ) && in_array( $tool['n'], $enabled_tools, true ) ) {
				++$group_on;
				break;
			}
		}
	}
	?>
	<div class="tutor-option-single-item tutor-mb-32">
		<div class="tutor-option-group-title tutor-mb-16">
			<div class="tutor-fs-6 tutor-color-muted">
				<?php echo esc_html( $group['name'] ); ?>
				<span class="tutor-ml-8">(<?php echo esc_html( (string) $group_on ); ?>/<?php echo esc_html( (string) $group_total ); ?>)</span>
			</div>
		</div>
		<div class="item-wrapper">
			<?php foreach ( $group_abilities as $ability ) : ?>
				<?php
				$tools    = isset( $ability['tools'] ) && is_array( $ability['tools'] ) ? $ability['tools'] : array();
				$tools_on = 0;
				foreach ( $tools as $tool ) {
					if ( isset( $tool['n'] ) && in_array( $tool['n'], $enabled_tools, true ) ) {
						++$tools_on;
					}
				}
				$ability_on = $tools_on > 0;
				$is_pro     = ! empty( $ability['pro'] );
				?>
				<div class="tutor-mcp-ability-row">
					<div class="tutor-option-field-row">
						<div class="tutor-option-field-label">
							<div class="tutor-d-flex tutor-align-center tutor-gap-1">
								<div class="tutor-fs-6 tutor-fw-medium"><?php echo esc_html( $ability['name'] ); ?></div>
								<?php if ( $is_pro ) : ?>
									<span class="tutor-pro-badge"><?php esc_html_e( 'Pro', 'tutor' ); ?></span>
								<?php endif; ?>
							</div>
							<div class="tutor-fs-7 tutor-color-muted tutor-mt-4"><?php echo esc_html( $ability['desc'] ); ?></div>
						</div>
						<div class="tutor-option-field-input">
							<label class="tutor-form-toggle">
								<input type="checkbox" class="tutor-form-toggle-input" data-mcp-ability-toggle <?php checked( $ability_on ); ?>>
								<span class="tutor-form-toggle-control"></span>
							</label>
						</div>
					</div>
					<div class="tutor-mcp-tools tutor-d-none" aria-hidden="true">
						<?php foreach ( $tools as $tool ) : ?>
							<input
								type="checkbox"
								class="tutor-form-toggle-input"
								name="tutor_mcp_enabled_tools[]"
								value="<?php echo esc_attr( $tool['n'] ); ?>"
								<?php checked( in_array( $tool['n'], $enabled_tools, true ) ); ?>
							>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
<?php endforeach; ?>

<?php if ( $enabled ) : ?>
	<input type="hidden" name="tutor_mcp_enabled" value="1">
<?php endif; ?>
<?php if ( $read_only ) : ?>
	<input type="hidden" name="tutor_mcp_read_only" value="1">
<?php endif; ?>
<?php if ( ! empty( $instructor_access ) ) : ?>
	<input type="hidden" name="tutor_mcp_instructor_access" value="1">
<?php endif; ?>

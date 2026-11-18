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
 * @var int      $on_count
 * @var int      $total_tools
 * @var bool     $enabled
 * @var bool     $read_only
 * @var array    $catalog
 * @var string[] $enabled_tools
 */
?>
<div class="tutor-mb-24">
	<a class="tutor-btn tutor-btn-ghost tutor-btn-sm" href="<?php echo esc_url( $overview_url ); ?>">
		<span class="tutor-icon-angle-left tutor-mr-8" aria-hidden="true"></span>
		<?php esc_html_e( 'Back to MCP', 'tutor' ); ?>
	</a>
	<div class="tutor-fs-7 tutor-color-muted tutor-mt-8">
		<?php
		printf(
			/* translators: 1: enabled count, 2: total count */
			esc_html__( '%1$d of %2$d tools enabled', 'tutor' ),
			(int) $on_count,
			(int) $total_tools
		);
		?>
	</div>
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
	$group_on    = 0;
	$group_total = 0;
	foreach ( $group['abilities'] as $ability ) {
		$group_total += count( $ability['tools'] );
		foreach ( $ability['tools'] as $tool ) {
			if ( in_array( $tool['n'], $enabled_tools, true ) ) {
				++$group_on;
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
			<?php foreach ( $group['abilities'] as $ability ) : ?>
				<?php
				$tools_on = 0;
				foreach ( $ability['tools'] as $tool ) {
					if ( in_array( $tool['n'], $enabled_tools, true ) ) {
						++$tools_on;
					}
				}
				$ability_on = $tools_on > 0;
				?>
				<div class="tutor-mcp-ability-row">
					<div class="tutor-option-field-row">
						<div class="tutor-option-field-label tutor-d-flex tutor-align-start tutor-gap-1">
							<button type="button" class="tutor-iconic-btn tutor-flex-shrink-0" data-mcp-ab-toggle aria-expanded="false" aria-label="<?php esc_attr_e( 'Show tools', 'tutor' ); ?>">
								<span class="tutor-icon-angle-right" aria-hidden="true"></span>
							</button>
							<div>
								<div class="tutor-fs-6 tutor-fw-medium"><?php echo esc_html( $ability['name'] ); ?></div>
								<div class="tutor-fs-7 tutor-color-muted tutor-mt-4"><?php echo esc_html( $ability['desc'] ); ?></div>
								<div class="tutor-fs-7 tutor-color-muted tutor-mt-4">
									<?php
									if ( $tools_on < count( $ability['tools'] ) && $ability_on ) {
										printf(
											/* translators: 1: enabled tools, 2: total tools */
											esc_html__( '%1$d of %2$d tools on', 'tutor' ),
											(int) $tools_on,
											count( $ability['tools'] )
										);
									} else {
										printf(
											/* translators: %d: tool count */
											esc_html( _n( '%d tool', '%d tools', count( $ability['tools'] ), 'tutor' ) ),
											count( $ability['tools'] )
										);
									}
									?>
									· <?php esc_html_e( 'read', 'tutor' ); ?>
								</div>
							</div>
						</div>
						<div class="tutor-option-field-input">
							<label class="tutor-form-toggle">
								<input type="checkbox" class="tutor-form-toggle-input" data-mcp-ability-toggle <?php checked( $ability_on ); ?>>
								<span class="tutor-form-toggle-control"></span>
							</label>
						</div>
					</div>
					<div class="tutor-mcp-tools tutor-d-none <?php echo $ability_on ? '' : 'tutor-mcp-dimmed'; ?>">
						<?php foreach ( $ability['tools'] as $tool ) : ?>
							<div class="tutor-option-field-row">
								<div class="tutor-option-field-label">
									<code class="tutor-fs-7"><?php echo esc_html( $tool['n'] ); ?></code>
									<div class="tutor-fs-7 tutor-color-muted tutor-mt-4"><?php echo esc_html( $tool['l'] ); ?></div>
								</div>
								<div class="tutor-option-field-input">
									<label class="tutor-form-toggle">
										<input
											type="checkbox"
											class="tutor-form-toggle-input"
											name="tutor_mcp_enabled_tools[]"
											value="<?php echo esc_attr( $tool['n'] ); ?>"
											<?php checked( in_array( $tool['n'], $enabled_tools, true ) ); ?>
										>
										<span class="tutor-form-toggle-control"></span>
									</label>
								</div>
							</div>
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

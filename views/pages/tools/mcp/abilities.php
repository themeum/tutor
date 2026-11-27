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

defined( 'ABSPATH' ) || exit;

use Tutor\Mcp\Registrar;

/**
 * Variables expected from parent mcp.php:
 *
 * @var bool     $enabled
 * @var bool     $read_only
 * @var bool     $instructor_access
 * @var array    $catalog
 * @var string[] $enabled_tools
 */
?>
<?php if ( ! $enabled ) : ?>
	<div class="tutor-alert tutor-primary tutor-mb-24">
		<div class="tutor-alert-text">
			<span class="tutor-alert-icon tutor-fs-4 tutor-icon-circle-info tutor-mr-12"></span>
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
	$group_counts    = Registrar::catalog_group_ability_counts( $group, $enabled_tools );
	$group_total     = $group_counts['total'];
	$group_on        = $group_counts['on'];
	?>
	<div class="tutor-option-single-item tutor-mcp-abilities tutor-mb-24">
		<div class="tutor-option-group-title tutor-mb-12">
			<div class="tutor-fs-6 tutor-color-subdued">
				<?php echo esc_html( $group['name'] ); ?>
				<span class="tutor-ml-8" data-mcp-group-count>(<?php echo esc_html( (string) $group_on ); ?>/<?php echo esc_html( (string) $group_total ); ?>)</span>
			</div>
		</div>
		<div class="tutor-mcp-ability-fields">
			<?php foreach ( $group_abilities as $ability ) : ?>
				<?php
				$tools      = isset( $ability['tools'] ) && is_array( $ability['tools'] ) ? $ability['tools'] : array();
				$ability_on = Registrar::is_catalog_ability_on( $ability, $enabled_tools );
				$is_pro     = ! empty( $ability['pro'] );
				?>
				<div class="tutor-mcp-ability-row">
					<div class="tutor-option-field-label">
						<div class="tutor-d-flex tutor-align-center tutor-gap-1">
							<div class="tutor-mcp-field-title"><?php echo esc_html( $ability['name'] ); ?></div>
							<?php if ( $is_pro ) : ?>
								<span class="tutor-pro-badge tutor-mcp-pro-badge"><?php esc_html_e( 'Pro', 'tutor' ); ?></span>
							<?php endif; ?>
						</div>
						<div class="tutor-mcp-field-desc"><?php echo esc_html( $ability['desc'] ); ?></div>
					</div>
					<div class="tutor-option-field-input">
						<label class="tutor-form-toggle">
							<input type="checkbox" class="tutor-form-toggle-input" data-mcp-ability-toggle <?php checked( $ability_on ); ?>>
							<span class="tutor-form-toggle-control"></span>
						</label>
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

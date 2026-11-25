<?php
/**
 * Ration input for tutor settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Group field settings.
 *
 * @var string $input_name
 * @var mixed  $input_value
 * @var string $label
 * @var array  $group_field
 */
?>

<label for="<?php echo esc_attr( $group_field['id'] ?? '' ); ?>" class="revenue-percentage">
	<span><?php echo esc_html( $label ); ?></span>
	<input type="number" class="tutor-form-control" placeholder="0 %" name="<?php echo esc_attr( $input_name ); ?>" id="<?php echo esc_attr( $group_field['id'] ?? '' ); ?>" min="0" max="100" value="<?php echo esc_attr( $input_value ); ?>">
</label>

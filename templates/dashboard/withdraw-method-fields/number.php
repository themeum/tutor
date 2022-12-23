<?php
/**
 * Withdraw method - number field
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Withdraw_Method_Fields
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @version 1.4.3
 */

?>
<input type="number" name="withdraw_method_field[<?php echo esc_attr( $method_id ); ?>][<?php echo esc_attr( $field_name ); ?>]" value="<?php echo esc_attr( $old_value ); ?>">

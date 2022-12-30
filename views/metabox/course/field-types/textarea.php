<?php
/**
 * Textarea field
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

?>

<textarea name="_tutor_course_settings[<?php echo esc_attr( $instructor->ID ); ?>]" rows="10"><?php echo esc_textarea( $this->get( $field['field_key'] ) ); ?></textarea>

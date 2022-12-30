<?php
/**
 * Template for Instructor Settings
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="tutor-option-field-row">
	<div class="tutor-option-field-label">
		<label for=""><?php esc_html_e( 'Instructor List Layout', 'tutor' ); ?></label>
	</div>
	<div class="tutor-option-field">
		<div class="instructor-layout-templates-fields">
			<?php
			$url_base = tutor()->url . 'assets/images/instructor-layout/';

			foreach ( $templates as $template ) {
				$img               = $url_base . $template . '.jpg';
				$selected_template = tutor_utils()->get_option( 'instructor_list_layout' );
				?>
					<label class="instructor-layout-template <?php echo $template === $selected_template ? 'selected-template' : ''; ?> ">
						<img src="<?php echo esc_url( $img ); ?>" />
						<input type="radio" name="tutor_option[instructor_list_layout]" value="<?php echo esc_attr( $template ); ?>" <?php checked( $template, $selected_template ); ?> style="display: none;" >
					</label>
					<?php
			}
			?>
		</div>
		<p class="desc">
			<?php esc_html_e( 'Selected one will be used if layout is not defined as shortcode attribute.', 'tutor' ); ?>
		</p>
	</div>
</div>


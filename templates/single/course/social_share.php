<?php
/**
 * Template for displaying social share
 *
 * @package Tutor\Templates
 * @subpackage Single\Course
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.4
 */

$share_config = array(
	'title' => get_the_title(),
	'text'  => get_the_excerpt(),
	'image' => get_tutor_course_thumbnail( 'post-thumbnail', true ),
);
?>

<div class="tutor-social-share-wrap" role="group" aria-label="<?php esc_attr_e( 'Share on social media', 'tutor' ); ?>" data-social-share-config="<?php echo esc_attr( wp_json_encode( $share_config ) ); ?>">
	<?php
	foreach ( $tutor_social_share_icons as $icon ) {
		$share_text = isset( $icon['text'] ) ? $icon['text'] : '';
		$share_name = '' !== $share_text ? '' : ( isset( $icon['label'] ) ? $icon['label'] : '' );
		$aria_label = '' !== $share_name ? ' aria-label="' . esc_attr( $share_name ) . '"' : '';
		echo '<button type="button" class="tutor_share ' . esc_attr( $icon['share_class'] ) . '"' . $aria_label . '><span aria-hidden="true">' . $icon['icon_html'] . '</span>'; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon HTML is provided by tutor_social_share_icons.
		if ( '' !== $share_text ) {
			echo '<span>' . esc_html( $share_text ) . '</span>';
		}
		echo '</button>';
	}
	?>
</div>

<?php
/**
 * Theme-aware Tutor brand logo.
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 *
 * @since 4.0.4
 */

defined( 'ABSPATH' ) || exit;

$sidebar_logo_visibility = get_tutor_option( 'sidebar_logo_visibility', true );

if ( ! $sidebar_logo_visibility ) {
	return;
}

$site_name      = get_bloginfo( 'name' );
$custom_logo_id = absint( get_theme_mod( 'custom_logo' ) );
$logo_ids       = array(
	'light' => absint( get_tutor_option( 'brand_logo_light', 0 ) ),
	'dark'  => absint( get_tutor_option( 'brand_logo_dark', 0 ) ),
);

/**
 * Resolve the first valid image URL from a list of candidate attachment IDs,
 * checked in priority order.
 *
 * @param int[] $attachment_ids Candidate attachment IDs, highest priority first.
 * @return string|false Image URL, or false if none of the candidates are valid images.
 */
$resolve_logo_url = static function ( array $attachment_ids ) {
	foreach ( $attachment_ids as $attachment_id ) {
		if ( $attachment_id && wp_attachment_is_image( $attachment_id ) ) {
			return wp_get_attachment_image_url( $attachment_id, 'full' );
		}
	}
	return false;
};
?>
<span class="tutor-brand-logo" aria-label="<?php echo esc_attr( $site_name ); ?>">
	<?php foreach ( $logo_ids as $theme => $logo_id ) : ?>
		<?php $image_url = $resolve_logo_url( array( $logo_id, $custom_logo_id ) ); ?>
		<span class="tutor-brand-logo-<?php echo esc_attr( $theme ); ?>">
			<?php if ( $image_url ) : ?>
				<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $site_name ); ?>">
			<?php else : ?>
				<span class="site-title"><?php echo esc_html( $site_name ); ?></span>
			<?php endif; ?>
		</span>
	<?php endforeach; ?>
</span>
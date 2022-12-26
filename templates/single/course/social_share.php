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

<div class="tutor-social-share-wrap" data-social-share-config="<?php echo esc_attr( json_encode( $share_config ) ); ?>">
	<?php
	foreach ( $tutor_social_share_icons as $icon ) {
		echo '<button class="tutor_share ' . $icon['share_class'] . '"> ' . $icon['icon_html'] . ' </button>'; //phpcs:ignore
	}
	?>
</div>

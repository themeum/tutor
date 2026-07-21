<?php
/**
 * Render a single nav item for learning area sidebar.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.2
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\SvgIcon;

$item         = $item ?? null;
$active       = $active ?? false;
$can_access   = $can_access ?? false;
$is_completed = $is_completed ?? false;
$type_label   = $type_label ?? '';
$icon         = $icon ?? '';
$status_class = $status_class ?? '';

if ( ! $item || ! is_a( $item, 'WP_Post' ) ) {
	return;
}

$active_class = $active ? 'active' : '';
$item_title   = $item->post_title;
$allowed_html = array(
	'span' => array(
		'class' => array(),
	),
);
?>

<a
	href="<?php echo esc_url( get_permalink( $item->ID ) ); ?>"
	title="<?php echo esc_attr( $item_title ); ?>"
	class="<?php echo esc_attr( sprintf( 'tutor-learning-nav-item %s %s', $active_class, $status_class ) ); ?>"
>
	<?php SvgIcon::make()->name( $icon )->size( 20 )->render(); ?>
	<div class="tutor-overflow-hidden">
		<div><?php echo esc_html( $item_title ); ?></div>
		<div class="tutor-tiny-2 tutor-text-subdued"><?php echo wp_kses( $type_label, $allowed_html ); ?></div>
	</div>
</a>

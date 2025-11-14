<?php
/**
 * Accordion Component
 *
 * @package TutorLMS\Templates
 */

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

// Default values.
$items        = isset( $items ) ? $items : array();
$multiple     = isset( $multiple ) ? (bool) $multiple : true;
$default_open = isset( $default_open ) ? $default_open : array();

// Ensure default_open is an array.
if ( ! is_array( $default_open ) ) {
	$default_open = array();
}

?>

<div
	x-data='tutorAccordion({
		multiple: <?php echo $multiple ? 'true' : 'false'; ?>,
		defaultOpen: <?php echo wp_json_encode( $default_open ); ?>
	})'
	class="tutor-accordion"
>
	<?php foreach ( $items as $index => $item ) : ?>
		<?php
		$item_title   = isset( $item['title'] ) ? $item['title'] : '';
		$item_content = isset( $item['content'] ) ? $item['content'] : '';
		$item_icon    = isset( $item['icon'] ) ? $item['icon'] : Icon::CHEVRON_DOWN;
		$panel_id     = 'tutor-acc-panel-' . $index;
		$trigger_id   = 'tutor-acc-trigger-' . $index;
		?>
		<div class="tutor-accordion-item">
			<button
				@click="toggle(<?php echo esc_attr( $index ); ?>)"
				@keydown="handleKeydown($event, <?php echo esc_attr( $index ); ?>)"
				:aria-expanded="isOpen(<?php echo esc_attr( $index ); ?>)"
				class="tutor-accordion-header tutor-accordion-trigger"
				aria-controls="<?php echo esc_attr( $panel_id ); ?>"
				id="<?php echo esc_attr( $trigger_id ); ?>"
			>
				<span class="tutor-accordion-title"><?php echo esc_html( $item_title ); ?></span>
				<span class="tutor-accordion-icon" aria-hidden="true">
					<?php tutor_utils()->render_svg_icon( $item_icon, 24, 24 ); ?>
				</span>
			</button>
			<div
				id="<?php echo esc_attr( $panel_id ); ?>"
				role="region"
				aria-labelledby="<?php echo esc_attr( $trigger_id ); ?>"
				class="tutor-accordion-content"
				x-show="isOpen(<?php echo esc_attr( $index ); ?>)"
				x-collapse.duration.350ms
			>
				<div class="tutor-accordion-body">
					<?php echo wp_kses_post( $item_content ); ?>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>


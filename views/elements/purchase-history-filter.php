<?php
/**
 * Purchase history filter
 *
 * @package Tutor\Views
 * @subpackage Tutor\ViewElements
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

use TUTOR\Input;

?>
<div class="tutor-wp-dashboard-filter tutor-d-flex tutor-flex-xl-nowrap tutor-flex-wrap tutor-align-center tutor-justify-between tutor-pb-40">
	<?php
		$active     = Input::get( 'period', '' );
		$start_date = Input::get( 'start_date', '' );
		$end_date   = Input::get( 'end_date', '' );
	?>

	<?php if ( count( $data['filter_period'] ) ) : ?>

		<div class="tutor-d-flex tutor-align-center tutor-justify-between">
			<?php foreach ( $data['filter_period'] as $key => $value ) : ?>
				<?php $active_class = $active === $value['type'] ? 'primary' : 'outline-primary'; ?>
				<a href="<?php echo esc_url( $value['url'] ); ?>" class="tutor-btn tutor-btn-<?php echo esc_attr( $active_class ); ?> tutor-mr-16">
					<?php echo esc_html( $value['title'] ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( $data['filter_calendar'] ) : ?>
		<div class="tutor-v2-date-range-picker " style="flex-basis:40%;"></div>
	<?php endif; ?>
</div>

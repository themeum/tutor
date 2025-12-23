<?php
/**
 * Tutor learning area footer.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

?>
<div class="tutor-flex tutor-items-center tutor-justify-between tutor-mt-11">
	<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-small">
		<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_LEFT_2 ); ?>
		<?php esc_html_e( 'Previous', 'tutor' ); ?>
	</button>
	<button type="button" class="tutor-btn tutor-btn-secondary tutor-btn-large tutor-rounded-full tutor-gap-5">
		<?php esc_html_e( 'Mark as complete', 'tutor' ); ?>
		<?php
		tutor_utils()->render_svg_icon(
			Icon::CHECK_2,
			20,
			20,
			array(
				'class' => 'tutor-icon-secondary',
			)
		);
		?>
	</button>
	<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-small">
		<?php esc_html_e( 'Next', 'tutor' ); ?>
		<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_RIGHT_2 ); ?>
	</button>
</div>

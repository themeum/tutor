<?php
/**
 * Template for displaying alert demo components
 *
 * @package Tutor
 * @since 4.0.0
 */

use TUTOR\Icon;
?>
<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-flex tutor-flex-column tutor-gap-4">
	<h2>Alerts</h2>
	<div class="tutor-flex tutor-flex-column tutor-gap-4">
		<!-- Default Alert -->
		<div class="tutor-alert tutor-alert-default">
			<div class="tutor-alert-content">
				<div class="tutor-alert-icon">
					<?php tutor_utils()->render_svg_icon( Icon::INFO ); ?>
				</div>
				<div class="tutor-alert-text">
					Default alert: This is a neutral alert message for general information.
				</div>
			</div>
			<div class="tutor-alert-action">
				<button class="tutor-btn tutor-btn-ghost tutor-btn-icon tutor-btn-x-small">
					<?php tutor_utils()->render_svg_icon( Icon::CROSS_2 ); ?>
				</button>
			</div>
		</div>

		<!-- Info Alert -->
		<div class="tutor-alert tutor-alert-info">
			<div class="tutor-alert-content">
				<div class="tutor-alert-icon">
					<?php tutor_utils()->render_svg_icon( Icon::INFO_FILL ); ?>
				</div>
				<div class="tutor-alert-text">
					Info alert: This is an informational alert message using brand colors.
				</div>
			</div>
		</div>

		<!-- Success Alert -->
		<div class="tutor-alert tutor-alert-success">
			<div class="tutor-alert-content">
				<div class="tutor-alert-icon">
					<?php tutor_utils()->render_svg_icon( Icon::PRIME_CHECK_CIRCLE ); ?>
				</div>
				<div class="tutor-alert-text">
					Success alert: Your action was completed successfully.
				</div>
			</div>
		</div>

		<!-- Warning Alert -->
		<div class="tutor-alert tutor-alert-warning">
			<div class="tutor-alert-content">
				<div class="tutor-alert-icon">
					<?php tutor_utils()->render_svg_icon( Icon::WARNING_LINE ); ?>
				</div>
				<div class="tutor-alert-text">
					Warning alert: Please be careful with this action.
				</div>
			</div>
		</div>

		<!-- Warning Alert with CTA -->
		<div class="tutor-alert tutor-alert-warning">
			<div class="tutor-alert-content">
				<div class="tutor-alert-icon">
					<?php tutor_utils()->render_svg_icon( Icon::WARNING_LINE ); ?>
				</div>
				<div class="tutor-alert-text">
					Your plan will be cancelled on 15 Feb, 2026, 06:30 am. You’ll have access until then and can resume anytime before that.
				</div>
			</div>
			<div class="tutor-alert-action">
				<button class="tutor-btn tutor-btn-primary tutor-btn-x-small">Resume plan</button>
			</div>
		</div>

		<!-- Error Alert -->
		<div class="tutor-alert tutor-alert-error">
			<div class="tutor-alert-content">
				<div class="tutor-alert-icon">
					<?php tutor_utils()->render_svg_icon( Icon::ALERT ); ?>
				</div>
				<div class="tutor-alert-text">
					Error alert: Something went wrong, please try again.
				</div>
			</div>
		</div>
	</div>
</section>

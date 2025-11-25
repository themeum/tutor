<?php
/**
 * Tutor dashboard discussions.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

?>
<div class="tutor-text-h3 tutor-color-black tutor-p-8">
	<?php esc_html_e( 'Welcome to TutorLMS Discussions', 'tutor' ); ?>

	<div class="tutor-card tutor-flex tutor-flex-column tutor-gap-4">
		<?php
		tutor_load_template(
			'demo-components.dashboard.components.qna-card',
			array(
				'is_unread' => true,
			)
		);
		?>
		<?php
		tutor_load_template(
			'demo-components.dashboard.components.qna-card',
			array(
				'is_unread' => false,
			)
		);
		?>
	</div>
</div>

<?php
/**
 * Trashed course empty state views
 *
 * @package Tutor\Views
 * @subpackage Tutor\ViewElements
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.7.0
 */

?>
<div class="tutor-divider tutor-radius-12 tutor-overflow-hidden">
	<div class="tutor-px-32 tutor-py-64 tutor-bg-white tutor-text-center">
		<h6 class="tutor-fs-6 tutor-fw-bold tutor-mt-0 tutor-mb-8 tutor-mt-32">
			<?php esc_html_e( 'No Courses Found.', 'tutor' ); ?>
		</h6>
		<p class="tutor-d-flex tutor-align-center tutor-justify-center tutor-gap-4px tutor-fs-7 tutor-color-danger tutor-m-0">
			<?php
			$message = sprintf(
				/* translators: %1$s: number of courses, %2$s: the anchor tag */
				_n(
					'You have %1$s course in Trash %2$s',
					'You have %1$s courses in Trash %2$s',
					$data['trashed_courses_count'],
					'tutor'
				),
				number_format_i18n( $data['trashed_courses_count'] ),
				'<a href="' . esc_url( $data['trashed_courses_url'] ) . '" class="tutor-btn tutor-btn-link">' . esc_html__( 'View Trash', 'tutor' ) . '</a>'
			);
			echo wp_kses(
				$message,
				array(
					'a' => array(
						'href'  => true,
						'class' => true,
					),
				)
			);
			?>
		</p>
	</div>
</div>

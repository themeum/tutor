<?php
/**
 * Certificate Card Single Item Component
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

// @TODO: Replace this with dynamic data.
$certificate = array(
	'type'            => 'Course',
	'title'           => 'Drawing for Beginners Level -2',
	'course_url'      => '#',
	'certificate_url' => '#',
	'is_bundle'       => false,
);
?>

<div class="tutor-surface-l1 tutor-rounded-lg tutor-border tutor-px-5 tutor-py-4 tutor-flex tutor-items-center tutor-gap-4">
	<!-- Certificate Icon -->
	<div class="tutor-flex-shrink-0 tutor-surface-l2 tutor-rounded-sm tutor-flex-center tutor-gap-4 tutor-icon-idle" style="width: 40px; height: 40px;">
		<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::CERTIFICATE_2, 24, 24 ) ); ?>
	</div>

	<!-- Certificate Info -->
	<div class="tutor-flex-1 tutor-flex tutor-flex-column tutor-gap-1 tutor-min-w-0">
		<!-- Type Badge -->
		<?php if ( $certificate['is_bundle'] ) : ?>
			<div class="tutor-p3 tutor-text-exception2">
				<?php echo esc_html__( 'Bundle', 'tutor' ); ?>
			</div>
		<?php else : ?>
			<div class="tutor-p3 tutor-text-secondary">
				<?php echo esc_html__( 'Course', 'tutor' ); ?>
			</div>
		<?php endif; ?>

		<!-- Title -->
		<div class="tutor-p1 tutor-font-medium tutor-truncate">
			<?php echo esc_html( $certificate['title'] ); ?>
		</div>
	</div>

	<!-- Action Links -->
	<div class="tutor-flex-shrink-0 tutor-flex tutor-items-center">
		<a 
			href="<?php echo esc_url( $certificate['course_url'] ); ?>" 
			class="tutor-btn tutor-btn-link tutor-btn-x-small tutor-text-brand"
		>
			<?php echo esc_html__( 'Course', 'tutor' ); ?>
		</a>
		<hr class="tutor-section-separator-vertical" />
		<a 
			href="<?php echo esc_url( $certificate['certificate_url'] ); ?>" 
			class="tutor-btn tutor-btn-link tutor-btn-x-small tutor-text-brand"
		>
			<?php echo esc_html__( 'View Certificate', 'tutor' ); ?>
		</a>
	</div>
</div>

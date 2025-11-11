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

<div class="tutor-certificate-card">
	<!-- Certificate Icon -->
	<div class="tutor-certificate-icon">
		<?php echo esc_html( tutor_utils()->render_svg_icon( Icon::CERTIFICATE_2, 24, 24 ) ); ?>
	</div>

	<!-- Certificate Info -->
	<div class="tutor-certificate-info">
		<!-- Type Badge -->
		<?php if ( $certificate['is_bundle'] ) : ?>
			<div class="tutor-certificate-type tutor-certificate-type-bundle">
				<?php echo esc_html__( 'Bundle', 'tutor' ); ?>
			</div>
		<?php else : ?>
			<div class="tutor-certificate-type">
				<?php echo esc_html__( 'Course', 'tutor' ); ?>
			</div>
		<?php endif; ?>

		<!-- Title -->
		<div class="tutor-certificate-title">
			<?php echo esc_html( $certificate['title'] ); ?>
		</div>
	</div>

	<!-- Action Links -->
	<div class="tutor-certificate-actions">
		<a 
			href="<?php echo esc_url( $certificate['course_url'] ); ?>" 
			class="tutor-certificate-actions-button"
		>
			<?php echo esc_html__( 'Course', 'tutor' ); ?>
		</a>
		<hr class="tutor-section-separator-vertical" />
		<a 
			href="<?php echo esc_url( $certificate['certificate_url'] ); ?>" 
			class="tutor-certificate-actions-button"
		>
			<?php echo esc_html__( 'View Certificate', 'tutor' ); ?>
		</a>
	</div>
</div>

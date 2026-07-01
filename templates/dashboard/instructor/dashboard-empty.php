<?php
/**
 * Frontend Empty Dashboard Template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use TUTOR\Icon;
use TUTOR\Instructors_List;

$instructor_status = tutor_utils()->instructor_status( 0, false );
$instructor_status = is_string( $instructor_status ) ? strtolower( $instructor_status ) : '';

?>

<div class="tutor-dashboard-welcome-card">
	<?php if ( Instructors_List::STATUS_APPROVED === $instructor_status ) : ?>
		<div class="tutor-dashboard-welcome-content">
			<h3 class="tutor-h3 tutor-my-4">
				<?php esc_html_e( 'You\'re ready to start teaching!', 'tutor' ); ?>
			</h3>
			<p class="tutor-p2 tutor-text-secondary tutor-mb-8">
				<?php esc_html_e( 'Create your first course and share your knowledge with learners around the world.', 'tutor' ); ?>
			</p>
			<?php
			Button::make()
				->label( __( 'Create Your First Course', 'tutor' ) )
				->variant( Variant::PRIMARY )
				->size( Size::MEDIUM )
				->icon( Icon::ARROW_RIGHT_2, 'right', 20 )
				->attr( 'class', 'tutor-create-new-course' )
				->render();
			?>
		</div>
		<div class="tutor-dashboard-welcome-banner">
			<?php tutor_utils()->render_themed_svg( 'images/illustrations/instructor-approved.svg' ); ?>
		</div>
	<?php else : ?>
		<div class="tutor-dashboard-welcome-content" data-tutor-instructor-status="pending">
			<h3 class="tutor-h3">
				<?php esc_html_e( 'Thank you for applying to become an Instructor!', 'tutor' ); ?>
			</h3>
			<p class="tutor-p2 tutor-text-secondary tutor-mt-4">
				<?php esc_html_e( 'Our team is reviewing your application. Once approved, you\'ll be able to create and publish courses.', 'tutor' ); ?>
			</p>
		</div>
		<div class="tutor-dashboard-welcome-banner">
			<?php tutor_utils()->render_themed_svg( 'images/illustrations/instructor-pending.svg' ); ?>
		</div>
	<?php endif; ?>
</div>

<?php
/**
 * Tutor learning area header.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Constants\Size;
use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use TUTOR\Course;

// Globals inherited from learning-area/index.php template.
global $tutor_course_id,
$tutor_course,
$current_user_id,
$tutor_course_progress,
$tutor_can_complete_course,
$tutor_can_retake_course,
$course_complete_modal_id,
$course_retake_modal_id;
?>
<div class="tutor-learning-header">
	<div class="tutor-learning-header-inner">
		<div class="tutor-learning-header-content">
			<a class="tutor-learning-header-back" href="<?php echo esc_url( tutor_utils()->tutor_dashboard_url() ); ?>">
				<?php SvgIcon::make()->name( Icon::LEFT )->size( 20 )->render(); ?>
			</a>
			<h5 class="tutor-learning-header-title tutor-my-none">
				<?php echo esc_html( $tutor_course->post_title ); ?>
			</h5>

			<div class="tutor-flex tutor-gap-2 tutor-items-center tutor-ml-auto tutor-pr-4 tutor-whitespace-nowrap tutor-md-hidden">
				<?php
				if ( $tutor_can_complete_course ) {
					Course::render_course_complete_btn( $course_complete_modal_id, $tutor_course_id, $tutor_course_progress, Size::SMALL );
				}
				if ( $tutor_can_retake_course ) {
					Course::render_course_retake_btn( $course_retake_modal_id, Size::SMALL );
				}
				?>
			</div>
		</div>
		<button class="tutor-learning-header-toggle-mobile" @click.stop="sidebarOpen = !sidebarOpen">
			<?php SvgIcon::make()->name( Icon::MENU )->size( 20 )->render(); ?>
		</button>
	</div>
</div>

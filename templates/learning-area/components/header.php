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

use TUTOR\Icon;
use Tutor\Components\SvgIcon;

global $tutor_course;
?>
<div class="tutor-learning-header">
	<div class="tutor-learning-header-inner">
		<a class="tutor-learning-header-back" href="<?php echo esc_url( tutor_utils()->tutor_dashboard_url() ); ?>">
			<?php SvgIcon::make()->name( Icon::LEFT )->size( 20 )->render(); ?>
		</a>
		<h5 class="tutor-learning-header-title">
			<?php echo esc_html( $tutor_course->post_title ); ?>
		</h5>
		<button class="tutor-learning-header-toggle-mobile" @click.stop="sidebarOpen = !sidebarOpen">
			<?php SvgIcon::make()->name( Icon::MENU )->size( 20 )->render(); ?>
		</button>
	</div>
</div>

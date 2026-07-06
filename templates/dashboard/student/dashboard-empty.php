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

use Tutor\Components\Button;
use Tutor\Components\Constants\Color;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\SvgIcon;
use Tutor\Helpers\UrlHelper;
use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

$quick_tips = array(
	__( 'Use notes to save key ideas as you watch', 'tutor' ),
	__( 'Check the calendar for upcoming live sessions', 'tutor' ),
	__( 'Discussions are a great place to ask questions', 'tutor' ),
	__( 'You can pause and resume courses any time', 'tutor' ),
);
?>
<div class="tutor-dashboard-welcome-card">
	<div class="tutor-dashboard-welcome-content">
		<div class="tutor-dashboard-welcome-badge">
			<span>
				<?php
				/* translators: %s: site title */
				echo esc_html( sprintf( __( 'Welcome to %s', 'tutor' ), get_bloginfo( 'name' ) ) );
				?>
			</span>
			<img src="<?php echo esc_attr( UrlHelper::asset( 'images/illustrations/confetti.svg' ) ); ?>" alt="<?php esc_html_e( 'Confetti', 'tutor' ); ?>" style="width: 16px; height: 16px;" />
		</div>
		<h3 class="tutor-h3 tutor-my-4">
			<?php
				printf(
					// translators: %s: <br />.
					esc_html__( 'You haven\'t enrolled in %s a course yet.', 'tutor' ),
					'<br />'
				);
				?>
		</h3>
		<p class="tutor-p2 tutor-mb-8">
			<?php
				printf(
					// translators: %s: <br />.
					esc_html__( 'Explore course and start building your %s skills today.', 'tutor' ),
					'<br />'
				);
				?>
		</p>
		<?php
		Button::make()
			->label( __( 'Explore Courses', 'tutor' ) )
			->variant( Variant::PRIMARY )
			->size( Size::X_SMALL )
			->icon( Icon::ARROW_RIGHT_2, 'right' )
			->flip_rtl()
			->tag( 'a' )
			->attr( 'href', tutor_utils()->course_archive_page_url() )
			->render();
		?>
	</div>
	<div class="tutor-dashboard-welcome-banner">
		<?php tutor_utils()->render_themed_svg( 'images/illustrations/dashboard-empty.svg' ); ?>
	</div>
</div>

<div class="tutor-dashboard-quick-tips-card">
	<div class="tutor-flex tutor-items-start tutor-gap-5">
		<div class="tutor-flex tutor-p-4 tutor-surface-brand-tertiary tutor-rounded-lg">
			<?php SvgIcon::make()->name( Icon::BULB_LINE )->size( Size::SIZE_32 )->color( Color::BRAND )->render(); ?>
		</div>
		<div class="tutor-flex tutor-flex-column tutor-gap-2" style="max-width: 220px;">
			<div class="tutor-medium tutor-font-semibold">
				<?php esc_html_e( 'Quick Tips', 'tutor' ); ?>
			</div>
			<div class="tutor-small tutor-text-secondary">
				<?php esc_html_e( 'Make the most of your learning experience', 'tutor' ); ?>
			</div>
		</div>
	</div>
	<div class="tutor-flex tutor-flex-column tutor-gap-4">
		<?php foreach ( $quick_tips as $key => $value ) : ?>
		<div class="tutor-flex tutor-items-start tutor-gap-4">
			<?php SvgIcon::make()->name( Icon::CHECK_2 )->color( Color::BRAND )->attr( 'class', 'tutor-mt-1' )->render(); ?>
			<div class="tutor-small tutor-text-secondary">
				<?php echo esc_html( $value ); ?>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
</div>

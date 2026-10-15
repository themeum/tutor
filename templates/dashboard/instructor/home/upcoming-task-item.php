<?php
/**
 * Upcoming Task Item Component
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\Badge;
use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\SvgIcon;
use TUTOR\Utils;

$label = __( 'Live Session', 'tutor' );
?>

<div class="tutor-dashboard-home-task">
	<div class="tutor-dashboard-home-task-icon">
		<?php SvgIcon::make()->name( Icon::VIDEO_CAMERA )->render(); ?>
	</div>
	<div class="tutor-flex tutor-flex-column tutor-mt-1">
		<div class="tutor-flex tutor-items-center tutor-gap-2 tutor-tiny tutor-text-secondary">
			<span class="tutor-text-secondary">
				<?php if ( gmdate( 'Y-m-d' ) === gmdate( 'Y-m-d', strtotime( $item['date'] ) ) ) : ?>
					<?php esc_html_e( 'Today', 'tutor' ); ?>
				<?php else : ?>
					<?php echo esc_html( gmdate( 'F d, Y', strtotime( $item['date'] ) ) ); ?>
				<?php endif; ?>
			</span>
			<span class="tutor-icon-secondary">•</span>
			<span class="tutor-text-secondary">
				<?php echo esc_html( gmdate( 'h:i A', strtotime( $item['date'] ) ) ); ?>
			</span>
		</div>
		<div class="tutor-small tutor-font-medium">
			<?php echo esc_html( $item['name'] ); ?>
		</div>
		<div class="tutor-dashboard-home-task-live-tag" data-meta>
			<div class="tutor-dashboard-home-task-live-tag-badge">
				<?php
				Badge::make()
					->icon( Utils::get_icon_by_post_type( $item['post_type'] ) )
					->label( $label )
					->rounded()
					->render();
				?>
			</div>
		</div>
		<?php
		Button::make()
			->tag( 'a' )
			->label( __( 'Open', 'tutor' ) )
			->variant( Variant::LINK )
			->size( Size::X_SMALL )
			->icon( Icon::CHEVRON_RIGHT_2, 'right' )
			->flip_rtl()
			->attr( 'href', $item['url'] )
			->attr( 'class', 'tutor-dashboard-home-task-link' )
			->attr( 'data-link', '' )
			->render();
		?>
	</div>
</div>

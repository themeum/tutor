<?php
/**
 * Instructor Request Alert Template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\SvgIcon;

$variant         = isset( $variant ) ? $variant : 'warning';
$alert_title     = __( 'Application Under Review', 'tutor' );
$alert_text      = __( 'Thank you for applying to become an instructor. Our team is reviewing your application.', 'tutor' );
$bg_class        = 'tutor-surface-warning-hover-2';
$icon_class      = 'tutor-icon-warning-secondary';
$icon            = Icon::INFO_OCTAGON_FILL;
$hide_notice_url = '';

if ( 'success' === $variant ) {
	$alert_title     = __( 'Application Approved', 'tutor' );
	$alert_text      = __( 'Your application has been approved. You can now create and publish courses.', 'tutor' );
	$bg_class        = 'tutor-surface-success';
	$icon_class      = 'tutor-icon-success-secondary';
	$icon            = Icon::BADGE_CHECK;
	$hide_notice_url = add_query_arg( 'tutor_action', 'hide_instructor_approval_notice' );
}

?>
<div class="<?php echo esc_attr( $bg_class ); ?> tutor-instructor-request-alert tutor-flex tutor-rounded-2xl tutor-gap-5 tutor-mb-7 tutor-py-5 tutor-px-4">
	<div class="tutor-flex-center">
		<?php
			SvgIcon::make()
				->name( $icon )
				->size( 40 )
				->attr( 'class', $icon_class )
				->render();
		?>
	</div>
	<div class="tutor-flex-1 tutor-flex tutor-flex-column tutor-gap-2">
		<div class="tutor-small tutor-text-secondary tutor-font-medium">
			<?php echo esc_html( $alert_title ); ?>
		</div>
		<div class="tutor-small tutor-text-secondary">
			<?php echo esc_html( $alert_text ); ?>
		</div>
	</div>
	<?php if ( 'success' === $variant ) : ?>
		<div class="tutor-flex-center">
			<?php
				Button::make()
					->label( __( 'Dismiss', 'tutor' ) )
					->variant( Variant::GHOST )
					->size( Size::X_SMALL )
					->icon( Icon::CROSS_2, 'left', 20 )
					->tag( 'a' )
					->icon_only()
					->attr( 'href', $hide_notice_url )
					->attr( 'aria-label', __( 'Dismiss', 'tutor' ) )
					->render();
			?>
		</div>
	<?php endif; ?>
</div>
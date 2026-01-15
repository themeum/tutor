<?php
/**
 * Base Template for Account
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;

defined( 'ABSPATH' ) || exit;

?>
<div class="tutor-account-header">
	<div class="tutor-account-container">
		<div class="tutor-flex tutor-items-center tutor-justify-between">
			<?php
				Button::make()
					->label( __( 'Back', 'tutor' ) )
					->variant( Variant::GHOST )
					->size( Size::X_SMALL )
					->tag( 'a' )
					->icon( Icon::LEFT, 'left', 20, 20 )
					->icon_only()
					->attr( 'href', esc_url( $back_url ) )
					->render();
			?>
			<h4 class="tutor-account-header-title">
				<?php echo esc_html( $page_data['title'] ?? '' ); ?>
			</h4>
			<?php
				Button::make()
					->variant( Variant::GHOST )
					->size( Size::X_SMALL )
					->tag( 'a' )
					->icon( Icon::CROSS, 'left', 20, 20 )
					->icon_only()
					->attr( 'href', esc_url( $back_url ) )
					->render();
			?>
		</div>
	</div>
</div>

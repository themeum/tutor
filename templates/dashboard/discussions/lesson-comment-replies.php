<?php
/**
 * Lesson comment replies template.
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;
?>

<div class="tutor-p-6 tutor-border-b">
	<div class="tutor-flex tutor-justify-between">
		<div>
			<?php
			Button::make()
			->variant( Variant::SECONDARY )
			->tag( 'a' )
			->attr( 'href', $discussion_url )
			->label( __( 'Back', 'tutor' ) )
			->icon( Icon::BACK )
			->size( Size::SM )
			->render();
			?>
		</div>
		<div></div>
	</div>
</div>

<div class="tutor-sm-border tutor-sm-rounded-2xl tutor-sm-mt-4">
</div>

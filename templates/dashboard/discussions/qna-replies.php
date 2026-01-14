<?php
/**
 * Q&A replies template.
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
use Tutor\Components\Nav;
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
			->icon( Icon::BACK, 'left' )
			->size( Size::SM )
			->render();
			?>
		</div>
		<?php
		Nav::make()->items(
			array(
				array(
					'type'  => 'link',
					'label' => __( 'Solved', 'tutor' ),
					'icon'  => Icon::CHECK,
					'url'   => '#',
				),
				array(
					'type'  => 'link',
					'label' => __( 'Important', 'tutor' ),
					'icon'  => Icon::BOOKMARK,
					'url'   => '#',
				),
			)
		)->render();
		?>
	</div>

<div class="tutor-sm-border tutor-sm-rounded-2xl tutor-sm-mt-4">
</div>

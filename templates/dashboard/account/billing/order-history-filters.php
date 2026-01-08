<?php
/**
 * Order History Filters
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\DateFilter;
use Tutor\Components\Sorting;
use Tutor\Ecommerce\OrderController;
use TUTOR\Icon;

$filter_options = ( new OrderController( false ) )->tabs_key_value( 'dashboard' );

$selected = array_filter(
	$filter_options,
	function( $item ) use ( $selected_filter ) {
		return $item['key'] === $selected_filter || ( empty( $item['key'] ) && 'all' === $selected_filter );
	}
);

$selected = count( $selected ) ? reset( $selected ) : $filter_options[0];
?>
<div x-data="tutorPopover({ placement: 'bottom-start', offset: 4 })">
		<button x-ref="trigger" @click="toggle()" class="tutor-btn tutor-btn-link tutor-btn-x-small tutor-p-none tutor-gap-2">
			<?php echo esc_html( $selected['title'] . ' (' . $selected['value'] . ')' ); ?>
			<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_DOWN, 16, 16, array( 'class' => 'tutor-icon-secondary' ) ); ?>
		</button>

		<div 
			x-ref="content"
			x-cloak 
			x-show="open" 
			@click.outside="handleClickOutside()" 
			class="tutor-popover" 
		>
			<div class="tutor-popover-menu" style="width: 120px;">
				<?php foreach ( $filter_options as $filter ) : ?>
					<a href="<?php echo esc_url( $filter['url'] ); ?>" class="tutor-popover-menu-item tutor-popover-menu-item-active">
						<?php
							printf(
								// translators: %1$s - Filter label, %2$d - Number of orders.
								esc_html__( '%1$s (%2$d)', 'tutor' ),
								esc_html( $filter['title'] ),
								esc_html( $filter['value'] )
							);
						?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<div class="tutor-qna-filter-right">
		<div class="tutor-flex tutor-items-center tutor-gap-3">
			<?php DateFilter::make()->type( DateFilter::TYPE_RANGE )->placement( 'bottom-end' )->render(); ?>
			<?php Sorting::make()->order( $order_filter )->render(); ?>
		</div>
	</div>
</div>

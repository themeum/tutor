<?php
/**
 * Billing Filters
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$filter_options = array(
	array(
		'id'    => 'all',
		'label' => __( 'All', 'tutor' ),
		'count' => 33,
	),
	array(
		'id'    => 'pending',
		'label' => __( 'Pending', 'tutor' ),
		'count' => 22,
	),
	array(
		'id'    => 'active',
		'label' => __( 'Active', 'tutor' ),
		'count' => 11,
	),
	array(
		'id'    => 'on-hold',
		'label' => __( 'On Hold', 'tutor' ),
		'count' => 5,
	),
	array(
		'id'    => 'expired',
		'label' => __( 'Expired', 'tutor' ),
		'count' => 1,
	),
	array(
		'id'    => 'cancelled',
		'label' => __( 'Cancelled', 'tutor' ),
		'count' => 1,
	),
);

$sort_options = array(
	array(
		'id'    => 'newest',
		'label' => __( 'Newest First', 'tutor' ),
	),
	array(
		'id'    => 'oldest',
		'label' => __( 'Oldest First', 'tutor' ),
	),
);

$selected_sort = get_query_var( 'sort' );

?>

<div class="tutor-billing-filters">
	<div x-data="tutorPopover({ placement: 'bottom-start', offset: 4 })">
		<button x-ref="trigger" @click="toggle()" class="tutor-btn tutor-btn-link tutor-btn-x-small tutor-p-none tutor-gap-2">
			<?php esc_html_e( 'All (33)', 'tutor' ); ?>
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
					<a href="#" class="tutor-popover-menu-item tutor-popover-menu-item-active">
						<?php
							printf(
								// translators: %1$s - Filter label, %2$d - Number of orders.
								esc_html__( '%1$s (%2$d)', 'tutor' ),
								esc_html( $filter['label'] ),
								esc_html( $filter['count'] )
							);
						?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	
	<!-- Sort and Date range -->
	<div class="tutor-flex tutor-justify-between tutor-gap-3">
		<!-- Sort -->
		<div x-data="tutorPopover({ placement: 'bottom-end', offset: 4 })">
			<button x-ref="trigger" @click="toggle()" class="tutor-btn tutor-btn-outline tutor-btn-icon tutor-btn-x-small tutor-icon-secondary">
				<?php tutor_utils()->render_svg_icon( Icon::STEPPER ); ?>
			</button>

			<div 
				x-ref="content"
				x-cloak 
				x-show="open" 
				@click.outside="handleClickOutside()" 
				class="tutor-popover"
			>
				<div class="tutor-popover-menu" style="width: 140px;">
					<?php foreach ( $sort_options as $sort ) : ?>
						<a href="#" class="tutor-popover-menu-item tutor-popover-menu-item-active tutor-flex tutor-justify-between tutor-items-center">
							<?php echo esc_html( $sort['label'] ); ?>

							<?php if ( $selected_sort === $sort['id'] ) : ?>
								<?php tutor_utils()->render_svg_icon( Icon::CHECK_2 ); ?>
							<?php endif; ?>
						</a>
					<?php endforeach; ?>
					</div>
			</div>
		</div>

		<!-- @TODO: Need to implement date range -->
		<!-- Date range -->
		<button class="tutor-btn tutor-btn-outline tutor-btn-icon tutor-btn-x-small tutor-icon-secondary">
			<?php tutor_utils()->render_svg_icon( Icon::CALENDAR_2 ); ?>
		</button>
	</div>
</div>
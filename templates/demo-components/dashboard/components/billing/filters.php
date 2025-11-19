<?php
/**
 * Filters
 *
 * @package TutorPress\Templates\Demo_Components\Dashboard\Components\Billing
 * @since 1.0.0
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

?>

<div class="tutor-billing-filters">
	<div x-data="tutorPopover({ placement: 'bottom-start', offset: 4 })">
		<button x-ref="trigger" @click="toggle()" class="tutor-btn tutor-btn-link tutor-btn-x-small tutor-p-none tutor-gap-2">
			<?php esc_html_e( 'All (33)', 'tutor' ); ?>
			<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_DOWN, 16, 16, array( 'class' => 'tutor-icon-secondary' ) ); ?>
		</button>

		<div x-ref="content" x-cloak x-show="open" @click.outside="handleClickOutside()" class="tutor-popover tutor-py-4" style="width: 120px;">
			<ul>
				<?php foreach ( $filter_options as $filter ) : ?>
					<li class="tutor-m-none">
						<a href="#" class="tutor-px-5 tutor-py-4 tutor-flex tutor-w-full">
							<?php
							printf(
								// translators: %d - Number of orders.
								esc_html__( '%1$s (%2$d)', 'tutor' ),
								esc_html( $filter['label'] ),
								esc_html( $filter['count'] )
							);
							?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	
	<!-- Sort and Date range -->
	<div class="tutor-flex tutor-justify-between tutor-gap-3">
		<!-- Sort -->
		<div x-data="tutorPopover({ placement: 'bottom-end', offset: 4 })">
			<button x-ref="trigger" @click="toggle()" class="tutor-btn tutor-btn-outline tutor-btn-icon tutor-btn-x-small tutor-icon-secondary">
				<?php tutor_utils()->render_svg_icon( Icon::STEPPER ); ?>
			</button>

			<div x-ref="content" x-cloak x-show="open" @click.outside="handleClickOutside()" class="tutor-popover tutor-py-4" style="width: 140px;">
				<ul>
					<?php foreach ( $sort_options as $sort ) : ?>
						<li class="tutor-m-none">
							<a href="#" class="tutor-px-5 tutor-py-4 tutor-flex tutor-w-full">
								<?php echo esc_html( $sort['label'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>

		<!-- @TODO: Need to implement date range -->
		<!-- Date range -->
		<button class="tutor-btn tutor-btn-outline tutor-btn-icon tutor-btn-x-small tutor-icon-secondary">
			<?php tutor_utils()->render_svg_icon( Icon::CALENDAR_2 ); ?>
		</button>
	</div>
</div>
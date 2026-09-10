<?php
/**
 * Dashboard page: Home for Instructor.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\DashboardSectionManager;
use TUTOR\Icon;
use Tutor\Components\Constants\InputType;
use Tutor\Components\DateFilter;
use Tutor\Components\InputField;
use Tutor\Components\Skeleton;
use Tutor\Components\SvgIcon;

$tutor_pro_enabled = tutor_utils()->is_plugin_active( 'tutor-pro/tutor-pro.php' );
$is_pro_reports    = $tutor_pro_enabled && tutor_utils()->is_addon_enabled( 'tutor-report' );

$user_id          = get_current_user_id();
$saved_order      = get_user_meta( $user_id, '_tutor_instructor_home_sections_order', true );
$saved_visibility = get_user_meta( $user_id, '_tutor_instructor_home_sections_visibility', true );

$saved_order      = is_array( $saved_order ) ? $saved_order : array();
$saved_visibility = is_array( $saved_visibility ) ? $saved_visibility : array();

$sortable_sections = array(
	array(
		'id'             => 'current_stats',
		'label'          => esc_html__( 'Current Stats', 'tutor' ),
		'is_active'      => isset( $saved_visibility['current_stats'] ) ? (bool) $saved_visibility['current_stats'] : true,
		'order'          => $saved_order['current_stats'] ?? 0,
		'skeleton'       => Skeleton::TYPE_STAT_CARD,
		'count'          => 4,
		'date_dependent' => true,
		'sort_dependent' => false,
		'condition'      => true,
	),
	array(
		'id'             => 'overview_chart',
		'label'          => esc_html__( 'Earnings Over Time', 'tutor' ),
		'is_active'      => isset( $saved_visibility['overview_chart'] ) ? (bool) $saved_visibility['overview_chart'] : true,
		'order'          => $saved_order['overview_chart'] ?? 1,
		'skeleton'       => Skeleton::TYPE_CHART,
		'count'          => 1,
		'date_dependent' => true,
		'sort_dependent' => false,
		'condition'      => $is_pro_reports,
	),
	array(
		'id'             => 'course_completion_and_leader',
		'label'          => esc_html__( 'Course Completion Rate', 'tutor' ),
		'is_active'      => isset( $saved_visibility['course_completion_and_leader'] ) ? (bool) $saved_visibility['course_completion_and_leader'] : true,
		'order'          => $saved_order['course_completion_and_leader'] ?? 2,
		'skeleton'       => Skeleton::TYPE_COMPLETION_CHART,
		'count'          => 1,
		'date_dependent' => false,
		'sort_dependent' => false,
		'condition'      => true,
	),
	array(
		'id'             => 'top_performing_courses',
		'label'          => esc_html__( 'Top Performing Courses', 'tutor' ),
		'is_active'      => isset( $saved_visibility['top_performing_courses'] ) ? (bool) $saved_visibility['top_performing_courses'] : true,
		'order'          => $saved_order['top_performing_courses'] ?? 3,
		'skeleton'       => Skeleton::TYPE_TOP_COURSES,
		'count'          => 4,
		'date_dependent' => true,
		'sort_dependent' => true,
		'condition'      => true,
	),
	array(
		'id'             => 'upcoming_tasks_and_activity',
		'label'          => esc_html__( 'Upcoming Tasks', 'tutor' ),
		'is_active'      => isset( $saved_visibility['upcoming_tasks_and_activity'] ) ? (bool) $saved_visibility['upcoming_tasks_and_activity'] : true,
		'order'          => $saved_order['upcoming_tasks_and_activity'] ?? 4,
		'skeleton'       => Skeleton::TYPE_UPCOMING_TASKS,
		'count'          => 3,
		'date_dependent' => false,
		'sort_dependent' => false,
		'condition'      => $tutor_pro_enabled,
	),
	array(
		'id'             => 'recent_reviews',
		'label'          => esc_html__( 'Recent Student Reviews', 'tutor' ),
		'is_active'      => isset( $saved_visibility['recent_reviews'] ) ? (bool) $saved_visibility['recent_reviews'] : true,
		'order'          => $saved_order['recent_reviews'] ?? 5,
		'skeleton'       => Skeleton::TYPE_REVIEWS,
		'count'          => 3,
		'date_dependent' => true,
		'sort_dependent' => false,
		'condition'      => true,
	),
);

// Filter out sections where condition is not met (e.g. Pro required).
$sortable_sections = array_filter(
	$sortable_sections,
	fn( $section ) => $section['condition']
);

usort(
	$sortable_sections,
	function ( $a, $b ) {
		return ( $a['order'] ?? 0 ) <=> ( $b['order'] ?? 0 );
	}
);

$sortable_sections_defaults = array_reduce(
	$sortable_sections,
	function ( $carry, $section ) {
		$carry[ $section['id'] ] = $section['is_active'] ?? false;
		return $carry;
	},
	array()
);

$sortable_sections_ids = array_values( array_column( $sortable_sections, 'id' ) );
?>
<form x-data='tutorForm({
		id: "sortable-sections",
		mode: "onBlur",
		defaultValues: <?php echo wp_json_encode( $sortable_sections_defaults ); ?>
	})' 
	x-bind="getFormBindings()"
	class="tutor-flex tutor-flex-column tutor-gap-6"
	data-tutor-ajax-dashboard="true"
>
	<!-- Filters -->
	<div class="tutor-flex tutor-justify-between tutor-items-center">
		<?php if ( $tutor_pro_enabled ) : ?>
			<?php
			DateFilter::make()
				->type( DateFilter::TYPE_RANGE )
				->ajax_mode( true )
				->render();
			?>
		<?php endif; ?>

		<div class="tutor-dashboard-home-sort" x-data="tutorPopover({ placement: '<?php echo esc_attr( $tutor_pro_enabled ? 'bottom-end' : 'bottom-start' ); ?>' })">
			<button
				type="button"
				x-ref="trigger"
				@click="toggle()"
				class="tutor-btn tutor-btn-outline tutor-btn-small tutor-btn-icon"
				aria-label="<?php esc_attr_e( 'Filter dashboard sections', 'tutor' ); ?>"
			>
				<?php SvgIcon::make()->name( Icon::FILTER_2 )->render(); ?>
			</button>

			<div
				x-ref="content"
				x-show="open"
				x-cloak
				x-transition.origin.top.right
				@click.outside="handleClickOutside()"
				class="tutor-popover tutor-popover-bottom"
			>
				<div 
					class="tutor-popover-menu"
					x-data='tutorSortableSections(
							<?php echo wp_json_encode( $sortable_sections_ids ); ?>
						)'
				>
					<?php foreach ( $sortable_sections as $section ) : ?>
						<div
							data-id="<?php echo esc_attr( $section['id'] ); ?>"
							class="tutor-popover-menu-item"						
						>
							<button type="button" data-grab>
								<?php SvgIcon::make()->name( Icon::DRAG_VERTICAL )->size( 16 )->render(); ?>
							</button>
							<?php
								InputField::make()
									->type( InputType::CHECKBOX )
									->name( "$section[id]" )
									->label( $section['label'] )
									->checked( ! empty( $section['is_active'] ) )
									->attr( 'x-bind', "\$el.closest('[data-dnd-placeholder]') ? {} : register('{$section['id']}')" )
									->attr( '@click.stop', 'handleCheckboxClick(event)' )
									->render();
							?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>

	<!-- Lazyloaded Dashboard Sections -->
	<?php foreach ( $sortable_sections as $section ) : ?>
		<div 
			data-section-id="<?php echo esc_attr( $section['id'] ); ?>"
			x-show="watch('<?php echo esc_attr( $section['id'] ); ?>')"
			x-cloak
			x-data="tutorLazySection({
				section: '<?php echo esc_attr( $section['id'] ); ?>',
				dateDependent: <?php echo ! empty( $section['date_dependent'] ) ? 'true' : 'false'; ?>,
				sortDependent: <?php echo ! empty( $section['sort_dependent'] ) ? 'true' : 'false'; ?>
			})"
		>
			<!-- Skeleton Placeholder (Visible during initial load & filter changes) -->
			<div x-show="isLoading">
				<?php
				Skeleton::make()
					->type( $section['skeleton'] )
					->count( $section['count'] ?? 1 )
					->render();
				?>
			</div>

			<!-- Hydrated Content (Injected via AJAX) -->
			<div x-show="!isLoading && hasData" x-html="content" x-ref="contentContainer"></div>
		</div>
	<?php endforeach; ?>
</form>
